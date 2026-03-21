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

		rows, err := database.Query("SELECT id_conseil, titre, contenu, image, date_publication FROM conseil")
		if err != nil {
			http.Error(response, "Erreur lors de la selection des conseils de la base de données", http.StatusInternalServerError)
			return
		} else {
			var conseils []structures.Conseil

			for rows.Next() {
				var c structures.Conseil

				var dateSQL string
				err := rows.Scan(&c.ID, &c.Titre, &c.Contenu, &c.Image, &dateSQL)
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

func Conseil_id(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		id := request.PathValue("id")

		selectstatement, selecterr := database.Prepare("SELECT id_conseil, titre, contenu, image, date_publication FROM conseil WHERE id_conseil = ? LIMIT 1")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération du conseil", http.StatusInternalServerError)
			return
		}

		var c structures.Conseil
		var dateSQL string
		err := selectstatement.QueryRow(id).Scan(&c.ID, &c.Titre, &c.Contenu, &c.Image, &dateSQL)
		if err != nil {
			if err == sql.ErrNoRows {
				http.Error(response, "Conseil introuvable", http.StatusNotFound)
				return
			}
			http.Error(response, "Erreur lors de la récupération du conseil", http.StatusInternalServerError)
			return
		}

		t, err := time.Parse("2006-01-02 15:04:05", dateSQL)
		if err != nil {
			http.Error(response, "Erreur lors du traitement de la date", http.StatusInternalServerError)
			return
		}
		c.Date = t.Format("02/01/2006 15:04")

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(c)
	}
}
