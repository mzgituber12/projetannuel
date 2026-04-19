package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strings"
)

func Abonnement_admin_creation(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var data struct {
			Categorie            string  `json:"categorie"`
			Name_abonnement      string  `json:"name_abonnement"`
			Prix_mois_abonnement float64 `json:"prix_mois_abonnement"`
			Prix_an_abonnement   float64 `json:"prix_an_abonnement"`
			Nb_avantage          int     `json:"nb_avantage"`
			Ai1                  string  `json:"ai1"`
			Ai2                  string  `json:"ai2"`
			Ai3                  string  `json:"ai3"`
			Ai4                  string  `json:"ai4"`
		}

		err := json.NewDecoder(request.Body).Decode(&data)
		if err != nil {
			http.Error(response, "JSON invalide", http.StatusBadRequest)
			return
		}

		if data.Categorie == "" || data.Name_abonnement == "" || data.Prix_mois_abonnement == 0 || data.Prix_an_abonnement == 0 || data.Ai1 == "" || data.Ai2 == "" || data.Ai3 == "" || data.Ai4 == "" {
			http.Error(response, "Veillez remplir tout les champs pour ajouter un abonnement", http.StatusBadRequest)
			return
		}

		if data.Categorie != "senior" && data.Categorie != "prestataire" {
			http.Error(response, "Categorie invalide", http.StatusBadRequest)
			return
		}

		if data.Prix_mois_abonnement*12 != data.Prix_an_abonnement {
			http.Error(response, "Le prix/an n'est pas bon !", http.StatusBadRequest)
			return
		}

		insertStatement, err := database.Prepare(`
			INSERT INTO abonnement (type, categorie, prix_mois, prix_an, statut, contenue1, contenue2, contenue3, contenue4, nb_avantage)
			VALUES (?, ?, ?, ?, 'actif', ?, ?, ?, ?, ?)
		`)
		if err != nil {
			http.Error(response, "Erreur lors de la preparation de la requete", http.StatusInternalServerError)
			return
		}
		defer insertStatement.Close()

		_, err = insertStatement.Exec(
			data.Name_abonnement,
			data.Categorie,
			data.Prix_mois_abonnement,
			data.Prix_an_abonnement,
			strings.TrimSpace(data.Ai1),
			strings.TrimSpace(data.Ai2),
			strings.TrimSpace(data.Ai3),
			strings.TrimSpace(data.Ai4),
			data.Nb_avantage,
		)
		if err != nil {
			http.Error(response, "Erreur dans la bdd", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"message": "Abonnement ajoute",
			"value":   1,
		})
	}
}
