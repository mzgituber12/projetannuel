package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func Gestion_contact(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query("SELECT u.email, c.contenu from contact c join utilisateur u on c.id_utilisateur = u.id_utilisateur order by c.id_contact DESC")
		if err != nil {
			http.Error(response, "Erreur lors de la récupération des contacts", http.StatusInternalServerError)
			return
		} else {
			var contact []structures.Contact

			for rows.Next() {
				var c structures.Contact

				err := rows.Scan(&c.Email, &c.Contenu)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des messages de contacts", http.StatusInternalServerError)
					return
				}

				contact = append(contact, c)
			}
			if len(contact) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Personne n'a contacté les administrateurs pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Contact: contact,
			})
		}
	}
}
