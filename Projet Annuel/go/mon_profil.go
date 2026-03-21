package main

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"projet/structures"

	_ "github.com/go-sql-driver/mysql"
	_ "modernc.org/sqlite"
)

func mon_profil(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")

		var utilisateur structures.User

		selectstatement, selecterr := database.Prepare("SELECT nom, prenom, age, email, langue FROM utilisateur WHERE token = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'utilisateur", http.StatusInternalServerError)
			return
		}

		err := selectstatement.QueryRow(token).Scan(&utilisateur.Nom, &utilisateur.Prenom, &utilisateur.Age, &utilisateur.Email, &utilisateur.Langue)

		if err != nil {
			http.Error(response, "Erreur lors du chargement du profil", http.StatusNotFound)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(utilisateur)
	}
}

func update_profil(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
		}

		token := request.Header.Get("Token")

		var data struct {
			Champ string `json:"champ"`
			Value string `json:"value"`
		}
		err := json.NewDecoder(request.Body).Decode(&data)

		if err != nil {
			http.Error(response, "JSON invalide", http.StatusBadRequest)
			return
		}

		if len(data.Value) == 0 {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Rien n'a été modifié",
			})

			return
		}

		if len(data.Value) < 3 && len(data.Value) > 0 && data.Champ != "age" {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Les champs sont trop cours, chaque champ nécéssite au moins 3 caractères",
			})
			return
		}

		if data.Champ == "email" {
			var existingEmail string
			selecterr := database.QueryRow("SELECT email FROM utilisateur WHERE email = ?", data.Value).Scan(&existingEmail)
			if selecterr == nil {
				response.Header().Set("Content-Type", "application/json")
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Cet email est déjà utilisé",
				})
				return
			}
		}

		switch data.Champ {
		case "email":
			_, err = database.Exec("UPDATE utilisateur SET email = ? WHERE token = ?", data.Value, token)
		case "password":
			_, err = database.Exec("UPDATE utilisateur SET password = ? WHERE token = ?", data.Value, token)
		case "age":
			_, err = database.Exec("UPDATE utilisateur SET age = ? WHERE token = ?", data.Value, token)
		case "prenom":
			_, err = database.Exec("UPDATE utilisateur SET prenom = ? WHERE token = ?", data.Value, token)
		case "nom":
			_, err = database.Exec("UPDATE utilisateur SET nom = ? WHERE token = ?", data.Value, token)
		default:
			http.Error(response, "Champ invalide", http.StatusBadRequest)
			return
		}

		if err != nil {
			http.Error(response, "Erreur update", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusOK)
		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Profil modifié avec succes",
		})
	}
}
