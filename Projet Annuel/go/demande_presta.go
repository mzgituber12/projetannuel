package main

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func demande_presta(database *sql.DB) http.HandlerFunc {
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
		selectStatement, err := database.Prepare("SELECT nom, prenom, age, email, langue FROM utilisateur WHERE token = ?")
		if err != nil {
			http.Error(response, "Erreur lors de la recuperation des informations de l'utilisateur", http.StatusInternalServerError)
			return
		}

		err = selectStatement.QueryRow(token).Scan(&utilisateur.Nom, &utilisateur.Prenom, &utilisateur.Age, &utilisateur.Email, &utilisateur.Langue)
		if err != nil {
			http.Error(response, "Erreur lors du chargement du profil", http.StatusNotFound)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(utilisateur)
	}
}
