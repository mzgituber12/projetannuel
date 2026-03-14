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

				var rej int

				auth := request.Header.Get("Token")
				userrequest, err := database.Prepare("SELECT re.id_evenement FROM reference_evenement re JOIN utilisateur u ON u.id_utilisateur = re.id_utilisateur WHERE u.token = ? AND re.id_evenement = ?")
				if err != nil {
					http.Error(response, "Erreur lors de la jointure des evenements: "+err.Error(), http.StatusInternalServerError)
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
			var eventName string
			eventStmt, eventErr := database.Prepare("SELECT nom FROM evenement WHERE id_evenement = ?")
			if eventErr != nil {
				http.Error(response, "Erreur lors de la récupération de l'événement", http.StatusInternalServerError)
				return
			}
			_ = eventStmt.QueryRow(id).Scan(&eventName)

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

			deleteRdv, deleteRdvErr := database.Prepare("DELETE FROM rendez_vous WHERE id_utilisateur = ? AND type = ?")
			if deleteRdvErr == nil {
				_, _ = deleteRdv.Exec(id_user, eventName)
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
func Reservation_evenement(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var p structures.Payload
		if err := json.NewDecoder(request.Body).Decode(&p); err != nil {
			http.Error(response, "Payload invalide", http.StatusBadRequest)
			return
		}

		selectstatement, selecterr := database.Prepare("SELECT id_utilisateur FROM utilisateur WHERE token = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération de l'utilisateur", http.StatusInternalServerError)
			return
		}
		var idUser int
		if err := selectstatement.QueryRow(request.Header.Get("Token")).Scan(&idUser); err != nil {
			http.Error(response, "Token invalide", http.StatusUnauthorized)
			return
		}

		selEvent, err := database.Prepare("SELECT nom, date, description, tarif FROM evenement WHERE id_evenement = ?")
		if err != nil {
			http.Error(response, "Erreur lors de la récupération de l'événement", http.StatusInternalServerError)
			return
		}
		var nom, dateSQL, description string
		var tarif float64
		errSel := selEvent.QueryRow(p.ID).Scan(&nom, &dateSQL, &description, &tarif)
		if errSel != nil {
			http.Error(response, "Événement introuvable", http.StatusNotFound)
			return
		}

		start, err := time.Parse("2006-01-02 15:04:05", dateSQL)
		if err != nil {
			http.Error(response, "Erreur lors du parsing de la date de l'événement", http.StatusInternalServerError)
			return
		}
		end := start.Add(time.Hour)

		selVerif, err := database.Prepare("SELECT COUNT(*) FROM rendez_vous WHERE date_debut < ? AND date_fin > ? ")
		if err != nil {
			http.Error(response, "Erreur lors de la vérification des rendez-vous", http.StatusInternalServerError)
			return
		}
		defer selVerif.Close()

		var count int
		err = selVerif.QueryRow(end, start).Scan(&count)
		if err != nil {
			http.Error(response, "Erreur lors de la vérification des disponibilités", http.StatusInternalServerError)
			return
		}

		if count > 0 {
			http.Error(response, "Un rendez-vous existe déjà sur ce créneau", http.StatusConflict)
			return
		}
		insertRdv, err := database.Prepare("INSERT INTO rendez_vous (id_utilisateur, id_prestataire, date_debut, date_fin, type, statut) VALUES (?, NULL, ?, ?, ?, 'confirmé')")
		if err != nil {
			http.Error(response, "Erreur lors de la création du rendez-vous", http.StatusInternalServerError)
			return
		}
		_, err = insertRdv.Exec(idUser, start.Format("2006-01-02 15:04:05"), end.Format("2006-01-02 15:04:05"), nom)
		if err != nil {
			http.Error(response, "Erreur lors de l'insertion du rendez-vous", http.StatusInternalServerError)
			return
		}

		insertRef, err := database.Prepare("INSERT INTO reference_evenement (id_utilisateur, id_evenement) VALUES (?, ?)")
		if err != nil {
			http.Error(response, "Erreur lors de l'ajout du lien utilisateur/événement", http.StatusInternalServerError)
			return
		}
		_, errInsert := insertRef.Exec(idUser, p.ID)
		if errInsert != nil {
			http.Error(response, "Erreur lors de l'ajout du lien utilisateur/événement", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: "Rendez-vous créé et événement rejoint"})
	}
}
