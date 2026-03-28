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
			Name_abonnement      string  `json:"name_abonnement"`
			Prix_mois_abonnement float64 `json:"prix_mois_abonnement"`
			Prix_an_abonnement   float64 `json:"prix_an_abonnement"`
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

		if data.Name_abonnement == "" || data.Prix_mois_abonnement == 0 || data.Prix_an_abonnement == 0 || data.Ai1 == "" || data.Ai2 == "" || data.Ai3 == "" || data.Ai4 == "" {
			http.Error(response, "Veillez remplir tout les champs pour ajouter un abonnement", http.StatusBadRequest)
			return
		}

		toFlag := func(v string) int {
			if strings.Contains(v, "✅") {
				return 1
			}
			return 0
		}

		insertStatement, err := database.Prepare(`
			INSERT INTO abonnement (type, prix_mois, prix_an, statut, type_prestataire, Locaux_prestation, Trajet_offert, offre_repas, mis_en_avant)
			VALUES (?, ?, ?, 'actif', 1, ?, ?, ?, ?)
		`)
		if err != nil {
			http.Error(response, "Erreur lors de la preparation de la requete", http.StatusInternalServerError)
			return
		}
		defer insertStatement.Close()

		_, err = insertStatement.Exec(
			data.Name_abonnement,
			data.Prix_mois_abonnement,
			int(data.Prix_an_abonnement),
			toFlag(data.Ai1),
			toFlag(data.Ai2),
			toFlag(data.Ai3),
			toFlag(data.Ai4),
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
