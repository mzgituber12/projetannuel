package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func Prestataires(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query("SELECT p.id_prestataire, u.nom, u.prenom, p.type, p.telephone FROM prestataire p JOIN utilisateur u ON u.id_utilisateur = p.id_utilisateur ORDER BY u.nom, u.prenom")
		if err != nil {
			http.Error(response, "Erreur lors de la selection des prestataires", http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		var prestataires []structures.Prestataire
		for rows.Next() {
			var p structures.Prestataire
			var typeNullable sql.NullString
			var telephoneNullable sql.NullString
			if err := rows.Scan(&p.ID, &p.Nom, &p.Prenom, &typeNullable, &telephoneNullable); err != nil {
				http.Error(response, "Erreur lors de la lecture des prestataires", http.StatusInternalServerError)
				return
			}
			if typeNullable.Valid {
				p.Type = typeNullable.String
			}
			if telephoneNullable.Valid {
				p.Telephone = telephoneNullable.String
			}
			prestataires = append(prestataires, p)
		}

		if len(prestataires) == 0 {
			json.NewEncoder(response).Encode(structures.Result{Message: "Aucun prestataire pour le moment"})
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.List{Prestataire: prestataires})
	}
}
