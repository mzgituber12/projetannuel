package ressources

import (
	"database/sql"
	"encoding/json"
	"errors"
	"net/http"
	"projet/structures"
	"time"
)

func monthBounds(year int, month time.Month, location *time.Location) (time.Time, time.Time) {
	start := time.Date(year, month, 1, 0, 0, 0, 0, location)
	end := start.AddDate(0, 1, 0)
	return start, end
}

func genererFactureMensuelle(database *sql.DB, idPrestataire int, referenceDate time.Time, force bool) (bool, string, float64, error) {
	if !force && referenceDate.Day() != 1 {
		return false, "", 0, nil
	}

	location := referenceDate.Location()
	currentMonthStart, _ := monthBounds(referenceDate.Year(), referenceDate.Month(), location)
	targetStart := currentMonthStart.AddDate(0, -1, 0)
	targetEnd := currentMonthStart
	monthKey := targetStart.Format("2006-01")

	var factureID int
	err := database.QueryRow(
		"SELECT id_facture FROM facture_prestataire WHERE id_prestataire = ? AND mois = ? LIMIT 1",
		idPrestataire,
		monthKey,
	).Scan(&factureID)
	if err == nil {
		var existingTotal float64
		_ = database.QueryRow("SELECT IFNULL(montant_total, 0) FROM facture_prestataire WHERE id_facture = ?", factureID).Scan(&existingTotal)
		return false, monthKey, existingTotal, nil
	}
	if err != nil && !errors.Is(err, sql.ErrNoRows) {
		return false, "", 0, err
	}

	rows, err := database.Query(`
		SELECT i.id_intervention, IFNULL(i.montant, 0)
		FROM intervention i
		JOIN rendez_vous rdv ON rdv.id_rdv = i.id_rdv
		WHERE i.id_prestataire = ?
		  AND i.statut = 'terminé'
		  AND rdv.date_debut >= ?
		  AND rdv.date_debut < ?
		ORDER BY rdv.date_debut ASC
	`, idPrestataire, targetStart.Format("2006-01-02 15:04:05"), targetEnd.Format("2006-01-02 15:04:05"))
	if err != nil {
		return false, "", 0, err
	}
	defer rows.Close()

	interventionIDs := make([]int, 0)
	total := 0.0
	for rows.Next() {
		var interventionID int
		var montant float64
		if scanErr := rows.Scan(&interventionID, &montant); scanErr != nil {
			return false, "", 0, scanErr
		}
		interventionIDs = append(interventionIDs, interventionID)
		total += montant
	}

	if len(interventionIDs) == 0 {
		return false, monthKey, 0, nil
	}

	tx, err := database.Begin()
	if err != nil {
		return false, "", 0, err
	}
	defer func() { _ = tx.Rollback() }()

	insertFactureResult, err := tx.Exec(
		"INSERT INTO facture_prestataire (id_prestataire, mois, montant_total, date_generation) VALUES (?, ?, ?, CURDATE())",
		idPrestataire,
		monthKey,
		total,
	)
	if err != nil {
		return false, "", 0, err
	}

	factureID64, err := insertFactureResult.LastInsertId()
	if err != nil {
		return false, "", 0, err
	}

	for _, interventionID := range interventionIDs {
		_, err = tx.Exec(
			"INSERT INTO synthese_facture (id_facture, id_intervention) VALUES (?, ?)",
			factureID64,
			interventionID,
		)
		if err != nil {
			return false, "", 0, err
		}
	}

	if err = tx.Commit(); err != nil {
		return false, "", 0, err
	}

	_, _ = database.Exec("INSERT INTO virement (id_facture, date, montant, statut) VALUES (?, CURDATE(), ?, 'pending')", factureID64, total)
	return true, monthKey, total, nil
}

func listFacturesPrestataire(database *sql.DB, idPrestataire int) ([]structures.FacturePrestataire, error) {
	rows, err := database.Query(`SELECT fp.id_facture, IFNULL(fp.mois, ''), IFNULL(fp.montant_total, 0), IFNULL(DATE_FORMAT(fp.date_generation, '%Y-%m-%d'), ''), IFNULL(v.id_virement, 0), IFNULL(v.statut, ''), IFNULL(DATE_FORMAT(v.date, '%Y-%m-%d'), '')
		FROM facture_prestataire fp LEFT JOIN virement v ON v.id_facture = fp.id_facture WHERE fp.id_prestataire = ? ORDER BY fp.mois DESC, fp.id_facture DESC`, idPrestataire)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	factures := make([]structures.FacturePrestataire, 0)
	for rows.Next() {
		var f structures.FacturePrestataire
		if scanErr := rows.Scan(&f.IDFacture, &f.Mois, &f.MontantTotal, &f.DateGeneration, &f.IDVirement, &f.StatutVirement, &f.DateVirement); scanErr != nil {
			return nil, scanErr
		}

		interventionRows, qErr := database.Query(`SELECT i.id_intervention, IFNULL(s.nom, ''),CONCAT(IFNULL(u.prenom, ''), ' ', IFNULL(u.nom, '')), IFNULL(DATE_FORMAT(rdv.date_debut, '%Y-%m-%d %H:%i:%s'), ''), IFNULL(i.statut, ''), IFNULL(i.montant, 0) 
			FROM synthese_facture sf JOIN intervention i ON i.id_intervention = sf.id_intervention LEFT JOIN service s ON s.id_service = i.id_service LEFT JOIN utilisateur u ON u.id_utilisateur = i.id_utilisateur LEFT JOIN rendez_vous rdv ON rdv.id_rdv = i.id_rdv WHERE sf.id_facture = ? ORDER BY rdv.date_debut ASC, i.id_intervention ASC`, f.IDFacture)
		if qErr != nil {
			return nil, qErr
		}

		lines := make([]structures.FactureIntervention, 0)
		for interventionRows.Next() {
			var line structures.FactureIntervention
			if scanLineErr := interventionRows.Scan(&line.IDIntervention, &line.Service, &line.Client, &line.DateRdv, &line.Statut, &line.Montant); scanLineErr != nil {
				interventionRows.Close()
				return nil, scanLineErr
			}
			lines = append(lines, line)
		}
		interventionRows.Close()

		f.Interventions = lines
		factures = append(factures, f)
	}

	return factures, nil
}

func Factures_prestataire(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodGet {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		token := request.Header.Get("Token")
		idPrestataire, err := prestataireIDFromToken(database, token)
		if err != nil {
			http.Error(response, "Authentification requise", http.StatusUnauthorized)
			return
		}

		created, monthKey, totalGenerated, err := genererFactureMensuelle(database, idPrestataire, time.Now(), false)
		if err != nil {
			http.Error(response, "Erreur génération facture mensuelle", http.StatusInternalServerError)
			return
		}

		factures, err := listFacturesPrestataire(database, idPrestataire)
		if err != nil {
			http.Error(response, "Erreur récupération factures", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"factures": factures,
			"generation_auto": map[string]any{
				"created": created,
				"month":   monthKey,
				"total":   totalGenerated,
			},
		})
	}
}

func Simuler_generation_facture_prestataire(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodPost {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		token := request.Header.Get("Token")
		idPrestataire, err := prestataireIDFromToken(database, token)
		if err != nil {
			http.Error(response, "Authentification requise", http.StatusUnauthorized)
			return
		}

		now := time.Now()
		simulatedReference := time.Date(now.Year(), now.Month()+1, 1, 0, 0, 0, 0, now.Location())

		created, monthKey, totalGenerated, err := genererFactureMensuelle(database, idPrestataire, simulatedReference, true)
		if err != nil {
			http.Error(response, "Erreur simulation génération facture", http.StatusInternalServerError)
			return
		}

		message := "Aucune nouvelle facture créée (déjà générée ou aucune prestation terminée)"
		if created {
			message = "Facture mensuelle générée avec succès"
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"message": message,
			"created": created,
			"month":   monthKey,
			"total":   totalGenerated,
		})
	}
}
