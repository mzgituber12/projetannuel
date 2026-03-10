package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func Contrats(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")

		sel, err := database.Prepare("SELECT c.nom FROM contrat c JOIN utilisateur u on c.id_utilisateur = u.id_utilisateur WHERE token = ?")
		if err != nil {
			http.Error(response, "Erreur de préparation de la requête des contrats", http.StatusInternalServerError)
			return
		}
		rows, err := sel.Query(token)
		if err != nil {
			http.Error(response, "Erreur lors de la selection des contrats de la base de données", http.StatusInternalServerError)
			return
		} else {
			var contrats []structures.Contrat

			for rows.Next() {
				var c structures.Contrat

				err := rows.Scan(&c.Nom)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des contrats : "+err.Error(), http.StatusInternalServerError)
					return
				}

				contrats = append(contrats, c)
			}
			if len(contrats) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun contrat pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Contrat: contrats,
			})
		}
	}
}
