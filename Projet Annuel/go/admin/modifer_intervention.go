package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"projet/structures"
	"strconv"
)

func Gestion_intervention_id(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		id := request.PathValue("id")

		selectstatement, selecterr := database.Prepare("SELECT id_intervention, id_service, id_prestataire, id_utilisateur, date, statut, montant FROM intervention WHERE id_intervention = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'intervention", http.StatusInternalServerError)
			return
		}
		var interv structures.Intervention
		selectstatement.QueryRow(id).Scan(&interv.ID, &interv.IdService, &interv.IdPrestataire, &interv.IdUtilisateur, &interv.Date, &interv.Statut, &interv.Montant)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Intervention{
			ID:            interv.ID,
			IdService:     interv.IdService,
			IdPrestataire: interv.IdPrestataire,
			IdUtilisateur: interv.IdUtilisateur,
			Date:          interv.Date,
			Statut:        interv.Statut,
			Montant:       interv.Montant,
		})
	}
}

func Modifier_intervention(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var interv structures.Intervention
		err := json.NewDecoder(request.Body).Decode(&interv)
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des données de l'intervention", http.StatusBadRequest)
			return
		}

		id, err := strconv.Atoi(request.PathValue("id"))
		if err != nil {
			http.Error(response, "ID invalide", http.StatusBadRequest)
			return
		}

		updatestatement, updateerr := database.Prepare("UPDATE intervention SET id_service = ?, id_prestataire = ?, id_utilisateur = ?, date = ?, statut = ?, montant = ? WHERE id_intervention = ?")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requête de mise à jour", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(interv.IdService, interv.IdPrestataire, interv.IdUtilisateur, interv.Date, interv.Statut, interv.Montant, id)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la mise à jour de l'intervention", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Intervention " + strconv.Itoa(id) + " mise à jour avec succès",
			Value:   1,
		})
	}
}
