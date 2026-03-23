package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func Categories(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query("SELECT id_categorie, nom FROM categorie ORDER BY nom")
		if err != nil {
			http.Error(response, "Erreur lors de la selection des categories de la base de donnees", http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		var categories []structures.Categorie
		for rows.Next() {
			var c structures.Categorie
			if err := rows.Scan(&c.ID, &c.Nom); err != nil {
				http.Error(response, "Erreur lors de la lecture des categories", http.StatusInternalServerError)
				return
			}
			categories = append(categories, c)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.List{Categorie: categories})
	}
}
