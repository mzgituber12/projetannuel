package main

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func liste_abonnement_all(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query(`
			SELECT id_abonnement, type_prestataire, type, prix_mois, statut, prix_an,
			       Locaux_prestation, Trajet_offert, offre_repas, mis_en_avant
			FROM abonnement
			ORDER BY id_abonnement ASC
		`)
		if err != nil {
			http.Error(response, "Erreur bdd", http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		abonnements := make([]structures.Abonnement, 0)
		for rows.Next() {
			var a structures.Abonnement
			if scanErr := rows.Scan(&a.ID, &a.TypePrestataire, &a.Type, &a.PrixMois, &a.Statut, &a.PrixAn, &a.LocauxPrestation, &a.TrajetOffert, &a.OffreRepas, &a.MisEnAvant); scanErr != nil {
				http.Error(response, scanErr.Error(), http.StatusInternalServerError)
				return
			}
			abonnements = append(abonnements, a)
		}

		if err = rows.Err(); err != nil {
			http.Error(response, err.Error(), http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(abonnements)
	}
}
