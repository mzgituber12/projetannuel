package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func Planning_evenements(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")

		sel, err := database.Prepare("SELECT e.nom, e.date, e.description, e.tarif FROM evenement e JOIN reference_evenement r on e.id_evenement = r.id_evenement JOIN utilisateur u on r.id_utilisateur = u.id_utilisateur WHERE token = ?")
		if err != nil {
			http.Error(response, "Erreur lors de la préparation de la requête des evenements", http.StatusInternalServerError)
			return
		}
		rows, err := sel.Query(token)
		if err != nil {
			http.Error(response, "Erreur lors de la selection des evenements de la base de données", http.StatusInternalServerError)
			return
		} else {
			var evenements []structures.Evenement

			for rows.Next() {
				var e structures.Evenement

				err := rows.Scan(&e.Nom, &e.Date, &e.Description, &e.Tarif)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des evenements : "+err.Error(), http.StatusInternalServerError)
					return
				}

				evenements = append(evenements, e)
			}
			if len(evenements) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun evenement pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Evenement: evenements,
			})
		}
	}
}

func Planning_services(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")

		sel, err := database.Prepare("SELECT s.nom, s.description, s.tarif FROM service s JOIN reference_service r on s.id_service = r.id_service JOIN utilisateur u on r.id_utilisateur = u.id_utilisateur WHERE token = ?")
		if err != nil {
			http.Error(response, "Erreur lors de la préparation de la requête des services", http.StatusInternalServerError)
			return
		}
		rows, err := sel.Query(token)
		if err != nil {
			http.Error(response, "Erreur lors de la selection des services de la base de données", http.StatusInternalServerError)
			return
		} else {
			var services []structures.Service

			for rows.Next() {
				var s structures.Service

				err := rows.Scan(&s.Nom, &s.Description, &s.Tarif)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des services : "+err.Error(), http.StatusInternalServerError)
					return
				}

				services = append(services, s)
			}
			if len(services) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun service pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Service: services,
			})
		}
	}
}
