package main

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"time"
)

func contrats(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")

		sel, err := database.Prepare("SELECT c.nom FROM contrat c JOIN utilisateur u on c.id_utilisateur = u.id_utilisateur WHERE token = ?")
		if err != nil {
			http.Error(response, "Erreur de préparation de la requête", http.StatusInternalServerError)
			return
		}
		rows, err := sel.Query(token)
		if err != nil {
			http.Error(response, "Erreur lors de la selection des contrats de la base de données", http.StatusInternalServerError)
			return
		} else {
			var contrats []contrat

			for rows.Next() {
				var c contrat

				err := rows.Scan(&c.Nom)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des contrats : "+err.Error(), http.StatusInternalServerError)
					return
				}

				contrats = append(contrats, c)
			}
			if len(contrats) == 0 {
				json.NewEncoder(response).Encode(result{
					Message: "Aucun contrat pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(list{
				Contrat: contrats,
			})
		}
	}
}

func conseils(database *sql.DB) http.HandlerFunc {
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
			var conseils []conseil

			for rows.Next() {
				var c conseil

				var dateSQL string
				err := rows.Scan(&c.Titre, &c.Contenu, &dateSQL)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des conseils : "+err.Error(), http.StatusInternalServerError)
					return
				}

				t, err := time.Parse("2006-01-02 15:04:05", dateSQL)
				if err != nil {
					http.Error(response, "Erreur lors de la selection de la date de création des conseils : "+err.Error(), http.StatusInternalServerError)
					return
				}
				c.Date = t.Format("02/01/2006 15:04")
				conseils = append(conseils, c)
			}
			if len(conseils) == 0 {
				json.NewEncoder(response).Encode(result{
					Message: "Aucun conseil pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(list{
				Conseil: conseils,
			})
		}
	}
}

func planning_evenements(database *sql.DB) http.HandlerFunc {
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
			http.Error(response, "Erreur de préparation de la requête", http.StatusInternalServerError)
			return
		}
		rows, err := sel.Query(token)
		if err != nil {
			http.Error(response, "Erreur lors de la selection des evenements de la base de données", http.StatusInternalServerError)
			return
		} else {
			var evenements []evenement

			for rows.Next() {
				var e evenement

				err := rows.Scan(&e.Nom, &e.Date, &e.Description, &e.Tarif)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des evenements : "+err.Error(), http.StatusInternalServerError)
					return
				}

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

func planning_services(database *sql.DB) http.HandlerFunc {
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
			http.Error(response, "Erreur de préparation de la requête", http.StatusInternalServerError)
			return
		}
		rows, err := sel.Query(token)
		if err != nil {
			http.Error(response, "Erreur lors de la selection des services de la base de données", http.StatusInternalServerError)
			return
		} else {
			var services []service

			for rows.Next() {
				var s service

				err := rows.Scan(&s.Nom, &s.Description, &s.Tarif)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des services : "+err.Error(), http.StatusInternalServerError)
					return
				}

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
