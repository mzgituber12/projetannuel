package ressources

import (
	"database/sql"
	"encoding/json"
	"errors"
	"log"
	"net/http"
	"projet/structures"
	"time"
)

func bornesMois(annee int, mois time.Month, fuseau *time.Location) (time.Time, time.Time) {
	debut := time.Date(annee, mois, 1, 0, 0, 0, 0, fuseau)
	fin := debut.AddDate(0, 1, 0)
	return debut, fin
}

func genererFactureMensuelle(bdd *sql.DB, idPrestataire int, dateReference time.Time, forcer bool) (bool, string, float64, error) {
	if !forcer && dateReference.Day() != 1 {
		return false, "", 0, nil
	}

	fuseau := dateReference.Location()
	debutMoisCourant, _ := bornesMois(dateReference.Year(), dateReference.Month(), fuseau)
	debutPeriode := debutMoisCourant.AddDate(0, -1, 0)
	finPeriode := debutMoisCourant
	cleMois := debutPeriode.Format("2006-01")

	var idFacture int
	erreur := bdd.QueryRow(
		"SELECT id_facture FROM facture_prestataire WHERE id_prestataire = ? AND mois = ? LIMIT 1",
		idPrestataire,
		cleMois,
	).Scan(&idFacture)
	if erreur == nil {
		var totalExistant float64
		_ = bdd.QueryRow("SELECT IFNULL(montant_total, 0) FROM facture_prestataire WHERE id_facture = ?", idFacture).Scan(&totalExistant)
		return false, cleMois, totalExistant, nil
	}
	if erreur != nil && !errors.Is(erreur, sql.ErrNoRows) {
		return false, "", 0, erreur
	}

	lignes, erreur := bdd.Query(`
		SELECT i.id_intervention, IFNULL(i.montant, 0)
		FROM intervention i
		JOIN rendez_vous rdv ON rdv.id_rdv = i.id_rdv
		WHERE i.id_prestataire = ?
		  AND i.statut = 'terminé'
		  AND rdv.date_debut >= ?
		  AND rdv.date_debut < ?
		ORDER BY rdv.date_debut ASC
	`, idPrestataire, debutPeriode.Format("2006-01-02 15:04:05"), finPeriode.Format("2006-01-02 15:04:05"))
	if erreur != nil {
		return false, "", 0, erreur
	}
	defer lignes.Close()

	idsInterventions := make([]int, 0)
	montantTotal := 0.0
	for lignes.Next() {
		var idIntervention int
		var montant float64
		if erreurScan := lignes.Scan(&idIntervention, &montant); erreurScan != nil {
			return false, "", 0, erreurScan
		}
		idsInterventions = append(idsInterventions, idIntervention)
		montantTotal += montant
	}

	if len(idsInterventions) == 0 {
		return false, cleMois, 0, nil
	}

	transaction, erreur := bdd.Begin()
	if erreur != nil {
		return false, "", 0, erreur
	}
	defer func() { _ = transaction.Rollback() }()

	resInsertionFacture, erreur := transaction.Exec(
		"INSERT INTO facture_prestataire (id_prestataire, mois, montant_total, date_generation) VALUES (?, ?, ?, CURDATE())",
		idPrestataire,
		cleMois,
		montantTotal,
	)
	if erreur != nil {
		return false, "", 0, erreur
	}

	idFactureInseree, erreur := resInsertionFacture.LastInsertId()
	if erreur != nil {
		return false, "", 0, erreur
	}

	for _, idIntervention := range idsInterventions {
		_, erreur = transaction.Exec(
			"INSERT INTO synthese_facture (id_facture, id_intervention) VALUES (?, ?)",
			idFactureInseree,
			idIntervention,
		)
		if erreur != nil {
			return false, "", 0, erreur
		}
	}

	if erreur = transaction.Commit(); erreur != nil {
		return false, "", 0, erreur
	}

	_, _ = bdd.Exec("INSERT INTO virement (id_facture, date, montant, statut) VALUES (?, CURDATE(), ?, 'pending')", idFactureInseree, montantTotal)

	if erreurArch := ArchiverFacturePrestatairePDF(bdd, idFactureInseree, idPrestataire); erreurArch != nil {
		log.Printf("[facture_prestataire] archive PDF facture %d: %v", idFactureInseree, erreurArch)
	}
	return true, cleMois, montantTotal, nil
}

func listFacturesPrestataire(bdd *sql.DB, idPrestataire int) ([]structures.FacturePrestataire, error) {
	lignes, erreur := bdd.Query(`SELECT fp.id_facture, IFNULL(fp.mois, ''), IFNULL(fp.montant_total, 0), IFNULL(DATE_FORMAT(fp.date_generation, '%Y-%m-%d'), ''), IFNULL(v.id_virement, 0), IFNULL(v.statut, ''), IFNULL(DATE_FORMAT(v.date, '%Y-%m-%d'), ''), IFNULL(fp.fichier_pdf, '')
		FROM facture_prestataire fp LEFT JOIN virement v ON v.id_facture = fp.id_facture WHERE fp.id_prestataire = ? ORDER BY fp.mois DESC, fp.id_facture DESC`, idPrestataire)
	if erreur != nil {
		return nil, erreur
	}
	defer lignes.Close()

	listeFactures := make([]structures.FacturePrestataire, 0)
	for lignes.Next() {
		var entreeFacture structures.FacturePrestataire
		if erreurScan := lignes.Scan(&entreeFacture.IDFacture, &entreeFacture.Mois, &entreeFacture.MontantTotal, &entreeFacture.DateGeneration, &entreeFacture.IDVirement, &entreeFacture.StatutVirement, &entreeFacture.DateVirement, &entreeFacture.FichierPDF); erreurScan != nil {
			return nil, erreurScan
		}

		lignesDetails, erreurRequete := bdd.Query(`SELECT i.id_intervention, IFNULL(s.nom, ''),CONCAT(IFNULL(u.prenom, ''), ' ', IFNULL(u.nom, '')), IFNULL(DATE_FORMAT(rdv.date_debut, '%Y-%m-%d %H:%i:%s'), ''), IFNULL(i.statut, ''), IFNULL(i.montant, 0) 
			FROM synthese_facture sf JOIN intervention i ON i.id_intervention = sf.id_intervention LEFT JOIN service s ON s.id_service = i.id_service LEFT JOIN utilisateur u ON u.id_utilisateur = i.id_utilisateur LEFT JOIN rendez_vous rdv ON rdv.id_rdv = i.id_rdv WHERE sf.id_facture = ? ORDER BY rdv.date_debut ASC, i.id_intervention ASC`, entreeFacture.IDFacture)
		if erreurRequete != nil {
			return nil, erreurRequete
		}

		detailsInterventions := make([]structures.FactureIntervention, 0)
		for lignesDetails.Next() {
			var ligneDetail structures.FactureIntervention
			if erreurScanLigne := lignesDetails.Scan(&ligneDetail.IDIntervention, &ligneDetail.Service, &ligneDetail.Client, &ligneDetail.DateRdv, &ligneDetail.Statut, &ligneDetail.Montant); erreurScanLigne != nil {
				lignesDetails.Close()
				return nil, erreurScanLigne
			}
			detailsInterventions = append(detailsInterventions, ligneDetail)
		}
		lignesDetails.Close()

		entreeFacture.Interventions = detailsInterventions
		listeFactures = append(listeFactures, entreeFacture)
	}

	return listeFactures, nil
}

func Factures_prestataire(bdd *sql.DB) http.HandlerFunc {
	return func(reponse http.ResponseWriter, requete *http.Request) {
		reponse.Header().Set("Access-Control-Allow-Origin", "*")
		reponse.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		reponse.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if requete.Method == http.MethodOptions {
			reponse.WriteHeader(http.StatusOK)
			return
		}

		if requete.Method != http.MethodGet {
			http.Error(reponse, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		jeton := requete.Header.Get("Token")
		idPrestataire, erreur := prestataireIDFromToken(bdd, jeton)
		if erreur != nil {
			http.Error(reponse, "Authentification requise", http.StatusUnauthorized)
			return
		}

		factureGeneree, cleMois, montantGenere, erreur := genererFactureMensuelle(bdd, idPrestataire, time.Now(), false)
		if erreur != nil {
			http.Error(reponse, "Erreur génération facture mensuelle", http.StatusInternalServerError)
			return
		}

		listeFactures, erreur := listFacturesPrestataire(bdd, idPrestataire)
		if erreur != nil {
			http.Error(reponse, "Erreur récupération factures", http.StatusInternalServerError)
			return
		}

		reponse.Header().Set("Content-Type", "application/json")
		json.NewEncoder(reponse).Encode(map[string]any{
			"factures": listeFactures,
			"generation_auto": map[string]any{
				"created": factureGeneree,
				"month":   cleMois,
				"total":   montantGenere,
			},
		})
	}
}

func Simuler_generation_facture_prestataire(bdd *sql.DB) http.HandlerFunc {
	return func(reponse http.ResponseWriter, requete *http.Request) {
		reponse.Header().Set("Access-Control-Allow-Origin", "*")
		reponse.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		reponse.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if requete.Method == http.MethodOptions {
			reponse.WriteHeader(http.StatusOK)
			return
		}

		if requete.Method != http.MethodPost {
			http.Error(reponse, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		jeton := requete.Header.Get("Token")
		idPrestataire, erreur := prestataireIDFromToken(bdd, jeton)
		if erreur != nil {
			http.Error(reponse, "Authentification requise", http.StatusUnauthorized)
			return
		}

		instantPresent := time.Now()
		dateReferenceSimulee := time.Date(instantPresent.Year(), instantPresent.Month()+1, 1, 0, 0, 0, 0, instantPresent.Location())

		factureGeneree, cleMois, montantGenere, erreur := genererFactureMensuelle(bdd, idPrestataire, dateReferenceSimulee, true)
		if erreur != nil {
			http.Error(reponse, "Erreur simulation génération facture", http.StatusInternalServerError)
			return
		}

		message := "Aucune nouvelle facture créée (déjà générée ou aucune prestation terminée)"
		if factureGeneree {
			message = "Facture mensuelle générée avec succès"
		}

		reponse.Header().Set("Content-Type", "application/json")
		json.NewEncoder(reponse).Encode(map[string]any{
			"message": message,
			"created": factureGeneree,
			"month":   cleMois,
			"total":   montantGenere,
		})
	}
}
