package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strconv"
	"strings"

	"projet/structures"
)

func Interventions_prestataire(database *sql.DB) http.HandlerFunc {
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

		rows, err := database.Query(
			`SELECT
				i.id_intervention,
				IFNULL(s.nom, ''),
				IFNULL(s.tarif, 0),
				IFNULL(u.nom, ''),
				IFNULL(u.prenom, ''),
				IFNULL(DATE_FORMAT(rdv.date_debut, '%Y-%m-%dT%H:%i:%s'), ''),
				IFNULL(DATE_FORMAT(rdv.date_fin, '%Y-%m-%dT%H:%i:%s'), ''),
				IFNULL(rdv.type, ''),
				IFNULL(i.statut, ''),
				IFNULL(i.montant, 0)
			FROM intervention i
			LEFT JOIN service s ON s.id_service = i.id_service
			LEFT JOIN utilisateur u ON u.id_utilisateur = i.id_utilisateur
			LEFT JOIN rendez_vous rdv ON rdv.id_rdv = i.id_rdv
			WHERE i.id_prestataire = ?
			ORDER BY rdv.date_debut DESC`,
			idPrestataire,
		)
		if err != nil {
			http.Error(response, "Erreur lecture interventions", http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		var liste []structures.SuiviIntervention
		for rows.Next() {
			var s structures.SuiviIntervention
			if err := rows.Scan(
				&s.ID,
				&s.NomService,
				&s.TarifService,
				&s.NomUtilisateur,
				&s.PrenomUtilisateur,
				&s.DateDebut,
				&s.DateFin,
				&s.TypeRdv,
				&s.Statut,
				&s.Montant,
			); err != nil {
				http.Error(response, "Erreur lecture ligne intervention", http.StatusInternalServerError)
				return
			}
			liste = append(liste, s)
		}

		response.Header().Set("Content-Type", "application/json")
		if len(liste) == 0 {
			json.NewEncoder(response).Encode([]structures.SuiviIntervention{})
			return
		}
		json.NewEncoder(response).Encode(liste)
	}
}

func Maj_statut_intervention(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodPatch {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		token := request.Header.Get("Token")
		idPrestataire, err := prestataireIDFromToken(database, token)
		if err != nil {
			http.Error(response, "Authentification requise", http.StatusUnauthorized)
			return
		}

		idStr := request.PathValue("id")
		id, err := strconv.Atoi(idStr)
		if err != nil || id <= 0 {
			http.Error(response, "ID invalide", http.StatusBadRequest)
			return
		}

		var payload struct {
			Statut string `json:"statut"`
		}
		if err := json.NewDecoder(request.Body).Decode(&payload); err != nil {
			http.Error(response, "Corps de requête invalide", http.StatusBadRequest)
			return
		}

		statuts := map[string]bool{
			"en_attente": true,
			"en_cours":   true,
			"terminé":    true,
			"annulé":     true,
		}
		statut := strings.TrimSpace(payload.Statut)
		if !statuts[statut] {
			http.Error(response, "Statut invalide", http.StatusBadRequest)
			return
		}

		res, err := database.Exec(
			"UPDATE intervention SET statut = ? WHERE id_intervention = ? AND id_prestataire = ?",
			statut, id, idPrestataire,
		)
		if err != nil {
			http.Error(response, "Erreur mise à jour statut", http.StatusInternalServerError)
			return
		}

		n, _ := res.RowsAffected()
		if n == 0 {
			http.Error(response, "Intervention introuvable ou accès refusé", http.StatusNotFound)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Statut mis à jour",
			Value:   1,
		})
	}
}
