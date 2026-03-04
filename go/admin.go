package main

import (
	"database/sql"
	"encoding/json"
	"net/http"
)

func gestion_user_email(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		email := request.PathValue("email")

		selectstatement, selecterr := database.Prepare("SELECT id_utilisateur, nom, prenom, age, email, role, langue FROM utilisateur WHERE email = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'utilisateur", http.StatusInternalServerError)
			return
		}
		var utilisateur user
		selectstatement.QueryRow(email).Scan(&utilisateur.Id, &utilisateur.Nom, &utilisateur.Prenom, &utilisateur.Age, &utilisateur.Email, &utilisateur.Role, &utilisateur.Langue)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(user{
			Id:     utilisateur.Id,
			Nom:    utilisateur.Nom,
			Prenom: utilisateur.Prenom,
			Age:    utilisateur.Age,
			Email:  utilisateur.Email,
			Role:   utilisateur.Role,
			Langue: utilisateur.Langue,
		})
	}
}

func gestion_user_id(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		id := request.PathValue("id")

		selectstatement, selecterr := database.Prepare("SELECT id_utilisateur, nom, prenom, age, email, role, langue FROM utilisateur WHERE id_utilisateur = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'utilisateur", http.StatusInternalServerError)
			return
		}
		var utilisateur user
		selectstatement.QueryRow(id).Scan(&utilisateur.Id, &utilisateur.Nom, &utilisateur.Prenom, &utilisateur.Age, &utilisateur.Email, &utilisateur.Role, &utilisateur.Langue)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(user{
			Id:     utilisateur.Id,
			Nom:    utilisateur.Nom,
			Prenom: utilisateur.Prenom,
			Age:    utilisateur.Age,
			Email:  utilisateur.Email,
			Role:   utilisateur.Role,
			Langue: utilisateur.Langue,
		})
	}
}
