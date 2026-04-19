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

		if request.Method != http.MethodGet {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		id := request.PathValue("id")

		selectstatement, selecterr := database.Prepare(`
			SELECT i.id_intervention,
			       i.id_service,
			       i.id_prestataire,
			       i.id_utilisateur,
			       IFNULL(DATE_FORMAT(r.date_debut, '%Y-%m-%d %H:%i:%s'), '') AS date,
			       i.statut,
			       i.montant
			FROM intervention i
			LEFT JOIN rendez_vous r ON r.id_rdv = i.id_rdv
			WHERE i.id_intervention = ?
		`)
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'intervention", http.StatusInternalServerError)
			return
		}
		var interv structures.Intervention
		err := selectstatement.QueryRow(id).Scan(&interv.ID, &interv.IdService, &interv.IdPrestataire, &interv.IdUtilisateur, &interv.Date, &interv.Statut, &interv.Montant)
		if err != nil {
			if err == sql.ErrNoRows {
				response.Header().Set("Content-Type", "application/json")
				json.NewEncoder(response).Encode(structures.Intervention{})
				return
			}
			http.Error(response, "Erreur lors de la sélection de l'intervention", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusOK)
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

func List_interventions(database *sql.DB) http.HandlerFunc {
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

		rows, err := database.Query("SELECT i.id_intervention, i.id_service, i.id_prestataire, i.id_utilisateur, IFNULL(DATE_FORMAT(r.date_debut, '%Y-%m-%d %H:%i:%s'), '') AS date, i.statut, i.montant FROM intervention i LEFT JOIN rendez_vous r ON r.id_rdv = i.id_rdv ORDER BY i.id_intervention DESC")
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des interventions", http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		interventions := make([]structures.Intervention, 0)
		for rows.Next() {
			var interv structures.Intervention
			if err := rows.Scan(&interv.ID, &interv.IdService, &interv.IdPrestataire, &interv.IdUtilisateur, &interv.Date, &interv.Statut, &interv.Montant); err != nil {
				http.Error(response, "Erreur lors du scan des interventions", http.StatusInternalServerError)
				return
			}
			interventions = append(interventions, interv)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(interventions)
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

		if request.Method != http.MethodPatch {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
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

		response.WriteHeader(http.StatusOK)
		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Intervention " + strconv.Itoa(id) + " mise à jour avec succès",
			Value:   1,
		})
	}
}
