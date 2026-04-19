package main

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"projet/structures"
)

func liste_attente_validation(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		query := `
			SELECT u.id_utilisateur, u.nom, u.prenom, p.photo_profil, p.type FROM utilisateur u INNER JOIN prestataire p ON u.id_utilisateur = p.id_utilisateur WHERE p.valider = 0 GROUP BY u.id_utilisateur`

		rows, err := database.Query(query)
		if err != nil {
			http.Error(response, "Erreur lors de la récupération des demandes : "+err.Error(), http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		FichePresta := make([]structures.FichePresta, 0)

		for rows.Next() {
			var a structures.FichePresta
			err := rows.Scan(&a.Id, &a.Nom, &a.Prenom, &a.Photo_profil, &a.Categorie)
			if err != nil {
				http.Error(response, "Erreur de lecture des données : "+err.Error(), http.StatusInternalServerError)
				return
			}
			FichePresta = append(FichePresta, a)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(FichePresta)
	}
}
