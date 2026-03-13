package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strconv"

	"projet/structures"
)

func Services(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query("SELECT id_service, nom, description, tarif FROM service")
		if err != nil {
			http.Error(response, "Erreur lors de la selection des services de la base de données", http.StatusInternalServerError)
			return
		} else {
			var services []structures.Service

			for rows.Next() {
				var s structures.Service
				var id int

				err := rows.Scan(&id, &s.Nom, &s.Description, &s.Tarif)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des services : "+err.Error(), http.StatusInternalServerError)
					return
				}
				var rej int
				var rej2 int
				auth := request.Header.Get("Token")
				userrequest, err := database.Prepare("SELECT rs.id_service FROM reference_service rs JOIN utilisateur u ON u.id_utilisateur = rs.id_utilisateur WHERE u.token = ? AND rs.id_service = ?")
				if err != nil {
					http.Error(response, "Erreur lors des jointures des services: "+err.Error(), http.StatusInternalServerError)
					return
				}
				otherrequest, err := database.Prepare("SELECT id_service FROM reference_service WHERE id_service = ?")
				if err != nil {
					http.Error(response, "Erreur lors de la préparation de la requête des services: "+err.Error(), http.StatusInternalServerError)
					return
				}
				rowsuser := userrequest.QueryRow(auth, id)
				err = rowsuser.Scan(&rej)
				rowsother := otherrequest.QueryRow(id)
				err2 := rowsother.Scan(&rej2)
				if err != nil {
					if err2 != nil {
						s.Rejoindre = "Rejoindre"
					} else {
						s.Rejoindre = "Indisponible"
					}
				} else {
					s.Rejoindre = "Quitter"
				}

				s.ID = id

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

func Services_patch(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		id, conversionError := strconv.Atoi(request.PathValue("id"))

		if conversionError != nil {
			http.Error(response, "Erreur lors de la récupération de l'identifiant du service", http.StatusInternalServerError)
			return
		}

		selectstatement, selecterr := database.Prepare("SELECT id_utilisateur FROM utilisateur WHERE token = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'utilisateur", http.StatusInternalServerError)
			return
		}
		var id_user int
		selectstatement.QueryRow(request.Header.Get("Token")).Scan(&id_user)
		var etat structures.Etat
		json.NewDecoder(request.Body).Decode(&etat)
		verifstatement, veriferr := database.Prepare("SELECT id_utilisateur FROM reference_service WHERE id_service = ?")
		if veriferr != nil {
			http.Error(response, "Erreur lors de la vérification de l'état du service pour l'utilisateur", http.StatusInternalServerError)
			return
		}

		var id_user_verif int
		err := verifstatement.QueryRow(id).Scan(&id_user_verif)
		var state string

		if err != nil {
			if (err == sql.ErrNoRows) && (etat.State == "join") {
				insertstatement, inserterr := database.Prepare("INSERT INTO reference_service (id_utilisateur, id_service) VALUES (?, ?)")
				if inserterr != nil {
					http.Error(response, "Erreur lors de l'insertion de la référence du service pour l'utilisateur", http.StatusInternalServerError)
					return
				}
				_, err = insertstatement.Exec(id_user, id)
				if err != nil {
					http.Error(response, "Erreur lors de l'insertion de la référence du service pour l'utilisateur", http.StatusInternalServerError)
					return
				}
				state = "rejoint"
			}
		} else if etat.State == "leave" {
			deletestatement, deleteerr := database.Prepare("DELETE FROM reference_service WHERE id_utilisateur = ? AND id_service = ?")
			if deleteerr != nil {
				http.Error(response, "Erreur lors de la suppression de la référence du service pour l'utilisateur", http.StatusInternalServerError)
				return
			}
			_, err = deletestatement.Exec(id_user, id)
			if err != nil {
				http.Error(response, "Erreur lors de la suppression de la référence du service pour l'utilisateur", http.StatusInternalServerError)
				return
			}
			state = "quitté"
		} else {
			return
		}
		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Service " + state + " avec succès",
		})

	}
}
