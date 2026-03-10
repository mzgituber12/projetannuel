package authentification

import (
	"database/sql"
	"net/http"
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
		del, err := database.Prepare("UPDATE utilisateur SET token = NULL WHERE token = ?")
		if err != nil {
			http.Error(response, "Erreur lors de la préparation de la suppression du token de la base de données", http.StatusInternalServerError)
			return
		}

		_, err = del.Exec(token)

		if err != nil {
			http.Error(response, "Erreur lors de la suppression du token de la base de données", http.StatusInternalServerError)
			return
		}
	}
}
