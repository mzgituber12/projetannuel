package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"projet/structures"
	"strconv"
)

func Gestion_service_nom(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		nom := request.PathValue("nom")

		selectstatement, selecterr := database.Prepare("SELECT id_service, nom, description, tarif FROM service WHERE nom = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations du service", http.StatusInternalServerError)
			return
		}
		var serv structures.Service
		selectstatement.QueryRow(nom).Scan(&serv.ID, &serv.Nom, &serv.Description, &serv.Tarif)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Service{
			ID:          serv.ID,
			Nom:         serv.Nom,
			Description: serv.Description,
			Tarif:       serv.Tarif,
		})
	}
}

func Gestion_service_id(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		id := request.PathValue("id")

		selectstatement, selecterr := database.Prepare("SELECT id_service, nom, description, tarif FROM service WHERE id_service = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations du service", http.StatusInternalServerError)
			return
		}
		var serv structures.Service
		selectstatement.QueryRow(id).Scan(&serv.ID, &serv.Nom, &serv.Description, &serv.Tarif)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Service{
			ID:          serv.ID,
			Nom:         serv.Nom,
			Description: serv.Description,
			Tarif:       serv.Tarif,
		})
	}
}

func Modifier_service(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var serv structures.Service
		err := json.NewDecoder(request.Body).Decode(&serv)
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des données du service", http.StatusBadRequest)
			return
		}

		id, err := strconv.Atoi(request.PathValue("id"))
		if err != nil {
			http.Error(response, "ID invalide", http.StatusBadRequest)
			return
		}

		updatestatement, updateerr := database.Prepare("UPDATE service SET nom = ?, description = ?, tarif = ? WHERE id_service = ?")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requête de mise à jour", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(serv.Nom, serv.Description, serv.Tarif, id)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la mise à jour du service", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Service " + serv.Nom + " mis à jour avec succès",
			Value:   1,
		})
	}
}
