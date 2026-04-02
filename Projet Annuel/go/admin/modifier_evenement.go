package admin

import (
	"database/sql"
	"encoding/json"
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

		if request.Method != http.MethodGet {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		nom := request.PathValue("nom")

		selectstatement, selecterr := database.Prepare("SELECT id_evenement, nom, DATE_FORMAT(date, '%Y-%m-%d %H:%i') AS date_sans_secondes, description, tarif, IFNULL(image, '') AS image FROM evenement WHERE nom = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'événement", http.StatusInternalServerError)
			return
		}
		var event structures.Evenement
		selectstatement.QueryRow(nom).Scan(&event.ID, &event.Nom, &event.Date, &event.Description, &event.Tarif, &event.Image)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Evenement{
			ID:          event.ID,
			Nom:         event.Nom,
			Date:        event.Date,
			Description: event.Description,
			Tarif:       event.Tarif,
			Image:       event.Image,
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

		if request.Method != http.MethodGet {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		id := request.PathValue("id")

		selectstatement, selecterr := database.Prepare("SELECT id_evenement, nom, DATE_FORMAT(date, '%Y-%m-%dT%H:%i') AS date_sans_secondes, description, tarif, IFNULL(image, '') AS image FROM evenement WHERE id_evenement = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'événement", http.StatusInternalServerError)
			return
		}
		var event structures.Evenement
		selectstatement.QueryRow(id).Scan(&event.ID, &event.Nom, &event.Date, &event.Description, &event.Tarif, &event.Image)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Evenement{
			ID:          event.ID,
			Nom:         event.Nom,
			Date:        event.Date,
			Description: event.Description,
			Tarif:       event.Tarif,
			Image:       event.Image,
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

		if request.Method != http.MethodPatch {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
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

		loc, locErr := time.LoadLocation("Europe/Paris")
		if locErr != nil {
			loc = time.UTC
		}
		eventDate, err := time.ParseInLocation("2006-01-02T15:04", event.Date, loc)
		if err != nil {
			http.Error(response, "Format de date invalide", http.StatusBadRequest)
			return
		}
		now := time.Now().In(loc)

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
		_, updateexecerr := updatestatement.Exec(event.Nom, eventDate.Format("2006-01-02 15:04:05"), event.Description, event.Tarif, id)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la mise à jour de l'événement : "+updateexecerr.Error(), http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Événement " + event.Nom + " mis à jour avec succès",
			Value:   1,
		})
	}
}

func Creer_evenement(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodPost {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		var event structures.Evenement
		err := json.NewDecoder(request.Body).Decode(&event)
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des données de l'événement", http.StatusBadRequest)
			return
		}

		verifemail, err := database.Prepare("SELECT nom FROM evenement WHERE nom = ?")
		if err != nil {
			http.Error(response, "Erreur lors de la vérification de l'existence du nom", http.StatusInternalServerError)
			return
		}
		var existingName string
		err = verifemail.QueryRow(event.Nom).Scan(&existingName)
		if err == nil {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Un autre événement a déjà ce nom",
				Value:   0,
			})
			return
		}

		loc, locErr := time.LoadLocation("Europe/Paris")
		if locErr != nil {
			loc = time.UTC
		}
		eventDate, err := time.ParseInLocation("2006-01-02T15:04", event.Date, loc)
		if err != nil {
			http.Error(response, "Format de date invalide", http.StatusBadRequest)
			return
		}
		now := time.Now().In(loc)

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

		updatestatement, updateerr := database.Prepare("INSERT INTO evenement (nom, date, description, tarif, image) VALUES (?, ?, ?, ?, ?)")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requête de creation", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(event.Nom, eventDate.Format("2006-01-02 15:04:05"), event.Description, event.Tarif, event.Image)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la creation de l'événement : "+updateexecerr.Error(), http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusCreated)
		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Événement " + event.Nom + " crée avec succès",
			Value:   1,
		})
	}
}

func Supprimer_evenement(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type")
		response.Header().Set("Access-Control-Allow-Methods", "DELETE, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodDelete {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		id, err := strconv.Atoi(request.PathValue("id"))
		if err != nil {
			http.Error(response, "ID invalide", http.StatusBadRequest)
			return
		}

		updatestatement, updateerr := database.Prepare("DELETE FROM evenement WHERE id_evenement = ?")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requête de suppression", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(id)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la suppression du evenement", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusNoContent)
	}
}

func List_evenements(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodGet {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		rows, err := database.Query("SELECT id_evenement, nom, date, description, tarif, IFNULL(image, '') AS image FROM evenement order by date")

		if err != nil {
			http.Error(response, "Erreur lors de la selection des evenements de la base de données", http.StatusInternalServerError)
			return
		} else {
			var evenements []structures.Evenement

			for rows.Next() {
				var e structures.Evenement
				var dateSQL string
				var id int

				err := rows.Scan(&id, &e.Nom, &dateSQL, &e.Description, &e.Tarif, &e.Image)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des evenements : "+err.Error(), http.StatusInternalServerError)
					return
				}
				t, err := parseDateTimeFlexible(dateSQL)
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

			response.WriteHeader(http.StatusOK)
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Evenement: evenements,
			})
		}
	}
}
