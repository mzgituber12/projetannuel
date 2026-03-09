package admin

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"
	"strconv"
	"time"

	"projet/structures"
)

func Gestion_evenement_nom(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		nom := request.PathValue("nom")

		selectstatement, selecterr := database.Prepare("SELECT id_evenement, nom, DATE_FORMAT(date, '%Y-%m-%d %H:%i') AS date_sans_secondes, description, tarif FROM evenement WHERE nom = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'événement", http.StatusInternalServerError)
			return
		}
		var event structures.Evenement
		selectstatement.QueryRow(nom).Scan(&event.ID, &event.Nom, &event.Date, &event.Description, &event.Tarif)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Evenement{
			ID:          event.ID,
			Nom:         event.Nom,
			Date:        event.Date,
			Description: event.Description,
			Tarif:       event.Tarif,
		})
	}
}

func Gestion_evenement_id(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		id := request.PathValue("id")

		selectstatement, selecterr := database.Prepare("SELECT * FROM evenement WHERE id_evenement = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'événement", http.StatusInternalServerError)
			return
		}
		var event structures.Evenement
		selectstatement.QueryRow(id).Scan(&event.ID, &event.Nom, &event.Date, &event.Description, &event.Tarif)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Evenement{
			ID:          event.ID,
			Nom:         event.Nom,
			Date:        event.Date,
			Description: event.Description,
			Tarif:       event.Tarif,
		})
	}
}

func Modifier_evenement(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var event structures.Evenement
		err := json.NewDecoder(request.Body).Decode(&event)
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des données de l'événement", http.StatusBadRequest)
			return
		}

		id, err := strconv.Atoi(request.PathValue("id"))
		if err != nil {
			http.Error(response, "ID invalide", http.StatusBadRequest)
			return
		}

		verifemail, err := database.Prepare("SELECT nom FROM evenement WHERE nom = ? AND id_evenement != ?")
		if err != nil {
			http.Error(response, "Erreur lors de la vérification de l'existence du nom", http.StatusInternalServerError)
			return
		}
		var existingName string
		err = verifemail.QueryRow(event.Nom, id).Scan(&existingName)
		if err == nil {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Un autre événement a déjà ce nom",
				Value:   0,
			})
			return
		}

		loc, err := time.LoadLocation("Europe/Paris")
		eventDate, err := time.ParseInLocation("2006-01-02T15:04", event.Date, loc)
		if err != nil {
			http.Error(response, "Format de date invalide", http.StatusBadRequest)
			return
		}
		now := time.Now().In(loc)

		fmt.Println("EventDate :", eventDate)
		fmt.Println("Now       :", now)

		if eventDate.Before(now) {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "La date de l'événement est passée",
				Value:   0,
			})
			return
		}

		nextYear := now.AddDate(1, 0, 0)
		if eventDate.After(nextYear) {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "La date de l'événement est trop éloignée, Veuillez choisir une date dans l'année à venir",
				Value:   0,
			})
			return
		}

		updatestatement, updateerr := database.Prepare("UPDATE evenement SET nom = ?, date = ?, description = ?, tarif = ? WHERE id_evenement = ?")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requête de mise à jour", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(event.Nom, event.Date, event.Description, event.Tarif, id)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la mise à jour de l'événement", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Événement " + event.Nom + " mis à jour avec succès",
			Value:   1,
		})
	}
}
