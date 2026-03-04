package main

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strconv"
	"time"
)

func evenements(database *sql.DB) http.HandlerFunc {
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
			var evenements []evenement

			for rows.Next() {
				var e evenement
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
				json.NewEncoder(response).Encode(result{
					Message: "Aucun evenement pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(list{
				Evenement: evenements,
			})
		}
	}
}

func services(database *sql.DB) http.HandlerFunc {
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
			var services []service

			for rows.Next() {
				var s service
				var id int

				err := rows.Scan(&id, &s.Nom, &s.Description, &s.Tarif)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des services : "+err.Error(), http.StatusInternalServerError)
					return
				}
				var rej string
				var rej2 string
				auth := request.Header.Get("Token")
				userrequest, err := database.Prepare("SELECT rs.id_service FROM reference_service rs JOIN utilisateur u ON u.id_utilisateur = rs.id_utilisateur WHERE u.token = ? AND rs.id_service = ?")
				if err != nil {
					http.Error(response, "Erreur lors des jointures d'evenements", http.StatusInternalServerError)
					return
				}
				otherrequest, err := database.Prepare("SELECT id_service FROM reference_service WHERE id_service = ?")
				if err != nil {
					http.Error(response, "Erreur lors des jointures d'evenements (2)", http.StatusInternalServerError)
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
				json.NewEncoder(response).Encode(result{
					Message: "Aucun service pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(list{
				Service: services,
			})
		}
	}
}

func articles(database *sql.DB) http.HandlerFunc {
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
			var articles []article

			for rows.Next() {
				var a article

				err := rows.Scan(&a.Nom, &a.Description, &a.Prix)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des articles : "+err.Error(), http.StatusInternalServerError)
					return
				}

				articles = append(articles, a)
			}
			if len(articles) == 0 {
				json.NewEncoder(response).Encode(result{
					Message: "Aucun article pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(list{
				Article: articles,
			})
		}
	}
}

func evenements_patch(database *sql.DB) http.HandlerFunc {
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
		var etat etat
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
					http.Error(response, "Erreur lors de l'insertion de la référence de l'événement pour l'utilisateur", http.StatusInternalServerError)
					return
				}
				_, err = insertstatement.Exec(id_user, id)
				if err != nil {
					http.Error(response, "Erreur lors de l'insertion de la référence de l'événement pour l'utilisateur", http.StatusInternalServerError)
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
		json.NewEncoder(response).Encode(result{
			Message: "Evenement " + state + " avec succès",
		})
	}
}

func services_patch(database *sql.DB) http.HandlerFunc {
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
		var etat etat
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
		json.NewEncoder(response).Encode(result{
			Message: "Service " + state + " avec succès",
		})

	}
}
