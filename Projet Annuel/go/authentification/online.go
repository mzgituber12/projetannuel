package authentification

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func Enligne(database *sql.DB) http.HandlerFunc {
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
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Pas identifié",
			})
			return
		}

		catStmt, catError := database.Prepare("SELECT role, tutoriel FROM utilisateur WHERE token = ?")
		if catError != nil {
			http.Error(response, "Impossible d'acceder a la base de donnée, veuillez reessayer plus tard", http.StatusInternalServerError)
			return
		}
		var role string
		var tutoriel int
		err := catStmt.QueryRow(token).Scan(&role, &tutoriel)
		if err != nil {
			if err == sql.ErrNoRows {
				response.Header().Set("Content-Type", "application/json")
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Pas identifié",
				})
				return
			}
			http.Error(response, "Erreur de requete de base de donnée", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Role:     role,
			Tutoriel: tutoriel,
			Message:  "Identifié",
		})
	}
}
