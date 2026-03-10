package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
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

func Users(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query("SELECT id_utilisateur, nom, prenom, age, email, role, langue FROM utilisateur")

		if err != nil {
			http.Error(response, "Erreur lors de la selection des utilisateurs de la base de données", http.StatusInternalServerError)
			return
		} else {
			var utilisateurs []structures.User

			for rows.Next() {
				var u structures.User

				err := rows.Scan(&u.ID, &u.Nom, &u.Prenom, &u.Age, &u.Email, &u.Role, &u.Langue)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des utilisateurs : "+err.Error(), http.StatusInternalServerError)
					return
				}

				utilisateurs = append(utilisateurs, u)
			}
			if len(utilisateurs) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun utilisateur pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Utilisateur: utilisateurs,
			})
		}
	}
}
