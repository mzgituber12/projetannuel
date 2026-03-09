package admin

import (
	"database/sql"
	"net/http"
)

func Estadmin(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")

		catStmt, catError := database.Prepare("SELECT role FROM utilisateur WHERE token = ?")
		if catError != nil {
			http.Error(response, "Impossible d'acceder a la base de donnée, veuillez reessayer plus tard", http.StatusInternalServerError)
			return
		}
		var role string
		err := catStmt.QueryRow(token).Scan(&role)
		if err != nil {
			http.Error(response, "Erreur de requete de base de donnée", http.StatusInternalServerError)
			return
		}

		if role != "admin" {
			http.Error(response, "Vous n'êtes pas administrateur", http.StatusForbidden)
			return
		}

		response.Header().Set("Content-Type", "application/json")
	}
}
