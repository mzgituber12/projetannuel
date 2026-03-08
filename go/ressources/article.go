package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func Articles(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query("SELECT titre, description, prix FROM article")
		if err != nil {
			http.Error(response, "Erreur lors de la selection des articles de la base de données", http.StatusInternalServerError)
			return
		} else {
			var articles []structures.Article

			for rows.Next() {
				var a structures.Article

				err := rows.Scan(&a.Nom, &a.Description, &a.Prix)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des articles : "+err.Error(), http.StatusInternalServerError)
					return
				}

				articles = append(articles, a)
			}
			if len(articles) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun article pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Article: articles,
			})
		}
	}
}
