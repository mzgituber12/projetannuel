package authentification

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func Deconnexion(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		del, err := database.Prepare("UPDATE utilisateur SET token = NULL, tutoriel = 0 WHERE token = ?")
		if err != nil {
			http.Error(response, "Erreur lors de la préparation de la suppression du token de la base de données", http.StatusInternalServerError)
			return
		}

		result, err := del.Exec(token)

		if err != nil {
			http.Error(response, "Erreur lors de la suppression du token de la base de données", http.StatusInternalServerError)
			return
		}

		rowsAffected, _ := result.RowsAffected()
		if rowsAffected == 0 {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Déconnexion réussie",
		})
	}
}
