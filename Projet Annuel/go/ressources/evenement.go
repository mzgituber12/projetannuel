package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strconv"
	"time"

	"projet/structures"
)

func Evenements(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query("SELECT id_evenement, nom, date, description, tarif FROM evenement order by date")

		if err != nil {
			http.Error(response, "Erreur lors de la selection des evenements de la base de données", http.StatusInternalServerError)
			return
		} else {
			var evenements []structures.Evenement

			for rows.Next() {
				var e structures.Evenement
				var dateSQL string
				var id int

				err := rows.Scan(&id, &e.Nom, &dateSQL, &e.Description, &e.Tarif)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des evenements : "+err.Error(), http.StatusInternalServerError)
					return
				}
				t, err := time.Parse("2006-01-02 15:04:05", dateSQL)
				if err != nil {
					http.Error(response, "Erreur lors du parsing de la date : "+err.Error(), http.StatusInternalServerError)
					return
				}
				e.Date = t.Format("02/01/2006 || 15:04")

				var rej string

				auth := request.Header.Get("Token")
				userrequest, err := database.Prepare("SELECT re.id_evenement FROM reference_evenement re JOIN utilisateur u ON u.id_utilisateur = re.id_utilisateur WHERE u.token = ? AND re.id_evenement = ?")
				if err != nil {
					http.Error(response, "Erreur lors des jointures d'evenements", http.StatusInternalServerError)
					return
				}
				rowsuser := userrequest.QueryRow(auth, id)
				err = rowsuser.Scan(&rej)
				if err != nil {
					e.Rejoindre = "Rejoindre"
				} else {
					e.Rejoindre = "Quitter"
				}

				e.ID = id

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

func Evenements_patch(database *sql.DB) http.HandlerFunc {
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
			http.Error(response, "Erreur lors de la récupération de l'identifiant de l'événement", http.StatusInternalServerError)
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
		verifstatement, veriferr := database.Prepare("SELECT id_evenement FROM reference_evenement WHERE id_utilisateur = ? AND id_evenement = ?")
		if veriferr != nil {
			http.Error(response, "Erreur lors de la vérification de l'état de l'événement pour l'utilisateur", http.StatusInternalServerError)
			return
		}
		var id_verif int
		err := verifstatement.QueryRow(id_user, id).Scan(&id_verif)
		var state string

		if err != nil {
			if (err == sql.ErrNoRows) && (etat.State == "join") {
				insertstatement, inserterr := database.Prepare("INSERT INTO reference_evenement (id_utilisateur, id_evenement) VALUES (?, ?)")
				if inserterr != nil {
					http.Error(response, "Erreur lors de l'insertion de la référence de l'événement pour l'utilisateur"+err.Error(), http.StatusInternalServerError)
					return
				}
				_, err = insertstatement.Exec(id_user, id)
				if err != nil {
					http.Error(response, "Erreur lors de l'insertion de la référence de l'événement pour l'utilisateur"+err.Error(), http.StatusInternalServerError)
					return
				}
				state = "rejoint"
			}
		} else if etat.State == "leave" {
			deletestatement, deleteerr := database.Prepare("DELETE FROM reference_evenement WHERE id_utilisateur = ? AND id_evenement = ?")
			if deleteerr != nil {
				http.Error(response, "Erreur lors de la suppression de la référence de l'événement pour l'utilisateur", http.StatusInternalServerError)
				return
			}
			_, err = deletestatement.Exec(id_user, id)
			if err != nil {
				http.Error(response, "Erreur lors de la suppression de la référence de l'événement pour l'utilisateur", http.StatusInternalServerError)
				return
			}
			state = "quitté"
		} else {
			return
		}
		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Evenement " + state + " avec succès",
		})
	}
}
