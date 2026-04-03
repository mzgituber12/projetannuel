package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func Contrats(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		sel, err := database.Prepare(`
			SELECT c.id_contrat, IFNULL(c.nom, ''), IFNULL(c.date_debut, ''), IFNULL(c.date_fin, ''), IFNULL(c.type_paiement, ''), IFNULL(c.type_contrat, '')
			FROM contrat c JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur WHERE u.token = ? ORDER BY c.id_contrat DESC`)
		if err != nil {
			http.Error(response, "Erreur de préparation de la requête des contrats", http.StatusInternalServerError)
			return
		}
		defer sel.Close()
		rows, err := sel.Query(token)
		if err != nil {
			http.Error(response, "Erreur lors de la selection des contrats de la base de données", http.StatusInternalServerError)
			return
		} else {
			defer rows.Close()
			var contrats []structures.Contrat

			for rows.Next() {
				var c structures.Contrat

				err := rows.Scan(&c.ID, &c.Nom, &c.DateDebut, &c.DateFin, &c.TypePaiement, &c.TypeContrat)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des contrats : "+err.Error(), http.StatusInternalServerError)
					return
				}

				contrats = append(contrats, c)
			}
			if len(contrats) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun contrat pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Contrat: contrats,
			})
		}
	}
}
