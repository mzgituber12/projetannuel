package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"projet/structures"
	"strconv"
)

func Factures_prestataires_admin(database *sql.DB) http.HandlerFunc {
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
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		var role string
		err := database.QueryRow("SELECT role FROM utilisateur WHERE token = ?", token).Scan(&role)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}
		if role != "admin" {
			http.Error(response, "Vous n'êtes pas administrateur", http.StatusForbidden)
			return
		}

		rows, err := database.Query("SELECT fp.id_facture, IFNULL(fp.id_prestataire, 0), CONCAT(IFNULL(u.prenom, ''), ' ', IFNULL(u.nom, '')), IFNULL(fp.mois, ''), IFNULL(fp.montant_total, 0), IFNULL(DATE_FORMAT(fp.date_generation, '%Y-%m-%d'), ''), IFNULL(v.id_virement, 0), IFNULL(v.statut, ''), IFNULL(DATE_FORMAT(v.date, '%Y-%m-%d'), ''), IFNULL(fp.fichier_pdf, '') FROM facture_prestataire fp LEFT JOIN prestataire p ON p.id_prestataire = fp.id_prestataire LEFT JOIN utilisateur u ON u.id_utilisateur = p.id_utilisateur LEFT JOIN virement v ON v.id_facture = fp.id_facture ORDER BY fp.mois DESC, fp.id_facture DESC")
		if err != nil {
			http.Error(response, "Erreur lecture factures", http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		factures := make([]structures.AdminFacturePrestataire, 0)
		for rows.Next() {
			var f structures.AdminFacturePrestataire
			if scanErr := rows.Scan(
				&f.IDFacture,
				&f.IDPrestataire,
				&f.NomPrestataire,
				&f.Mois,
				&f.MontantTotal,
				&f.DateGeneration,
				&f.IDVirement,
				&f.StatutVirement,
				&f.DateVirement,
				&f.FichierPDF,
			); scanErr != nil {
				http.Error(response, "Erreur lecture facture", http.StatusInternalServerError)
				return
			}

			interventionRows, qErr := database.Query(`SELECT i.id_intervention, IFNULL(s.nom, ''), CONCAT(IFNULL(uc.prenom, ''), ' ', IFNULL(uc.nom, '')), IFNULL(DATE_FORMAT(rdv.date_debut, '%Y-%m-%d %H:%i:%s'), ''), IFNULL(i.statut, ''), IFNULL(i.montant, 0)
				FROM synthese_facture sf JOIN intervention i ON i.id_intervention = sf.id_intervention LEFT JOIN service s ON s.id_service = i.id_service LEFT JOIN utilisateur uc ON uc.id_utilisateur = i.id_utilisateur LEFT JOIN rendez_vous rdv ON rdv.id_rdv = i.id_rdv WHERE sf.id_facture = ? ORDER BY rdv.date_debut ASC, i.id_intervention ASC`, f.IDFacture)
			if qErr != nil {
				http.Error(response, "Erreur lecture interventions", http.StatusInternalServerError)
				return
			}

			lines := make([]structures.FactureIntervention, 0)
			for interventionRows.Next() {
				var line structures.FactureIntervention
				if scanLineErr := interventionRows.Scan(
					&line.IDIntervention, &line.Service, &line.Client,
					&line.DateRdv, &line.Statut, &line.Montant,
				); scanLineErr != nil {
					interventionRows.Close()
					http.Error(response, "Erreur lecture intervention", http.StatusInternalServerError)
					return
				}
				lines = append(lines, line)
			}
			interventionRows.Close()

			f.Interventions = lines
			factures = append(factures, f)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"factures": factures,
		})
	}
}

func Confirmer_virement(database *sql.DB) http.HandlerFunc {
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
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		var role string
		err := database.QueryRow("SELECT role FROM utilisateur WHERE token = ?", token).Scan(&role)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}
		if role != "admin" {
			http.Error(response, "Vous n'êtes pas administrateur", http.StatusForbidden)
			return
		}

		idStr := request.PathValue("id_facture")
		idFacture, convErr := strconv.Atoi(idStr)
		if convErr != nil || idFacture <= 0 {
			http.Error(response, "ID facture invalide", http.StatusBadRequest)
			return
		}

		var idVirement int
		err = database.QueryRow("SELECT id_virement FROM virement WHERE id_facture = ? LIMIT 1", idFacture).Scan(&idVirement)
		if err == sql.ErrNoRows {
			http.Error(response, "Aucun virement associé à cette facture", http.StatusNotFound)
			return
		}
		if err != nil {
			http.Error(response, "Erreur vérification virement", http.StatusInternalServerError)
			return
		}

		_, err = database.Exec("UPDATE virement SET statut = 'paid', date = CURDATE() WHERE id_virement = ?", idVirement)
		if err != nil {
			http.Error(response, "Erreur confirmation virement", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"message": "Virement confirmé",
		})
	}
}
