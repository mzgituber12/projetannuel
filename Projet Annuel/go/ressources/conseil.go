package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"time"

	"projet/structures"
)

func Conseils(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query("SELECT titre, contenu, date_publication FROM conseil")
		if err != nil {
			http.Error(response, "Erreur lors de la selection des conseils de la base de données", http.StatusInternalServerError)
			return
		} else {
			var conseils []structures.Conseil

			for rows.Next() {
				var c structures.Conseil

				var dateSQL string
				err := rows.Scan(&c.Titre, &c.Contenu, &dateSQL)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des conseils", http.StatusInternalServerError)
					return
				}

				t, err := time.Parse("2006-01-02 15:04:05", dateSQL)
				if err != nil {
					http.Error(response, "Erreur lors de la selection de la date de création des conseils", http.StatusInternalServerError)
					return
				}
				c.Date = t.Format("02/01/2006 15:04")
				conseils = append(conseils, c)
			}
			if len(conseils) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun conseil pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Conseil: conseils,
			})
		}
	}
}
