package main

import (
	"database/sql"
	"encoding/json"
	"net/http"

	_ "github.com/go-sql-driver/mysql"
	_ "modernc.org/sqlite"
)

func chargement_message(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query("SELECT contenu FROM message WHERE id_expediteur = 5 AND id_destinataire = 1 ORDER BY date_envoie ASC")

		if err != nil {
			http.Error(response, "Erreur lors de la récupération des messages", http.StatusInternalServerError)
			return
		}

		var messages []string

		for rows.Next() {
			var contenu string
			rows.Scan(&contenu)
			messages = append(messages, contenu)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(messages)
	}
}

func chargement_contact(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query("SELECT ")

		if err != nil {
			http.Error(response, "Erreur lors de la récupération des messages", http.StatusInternalServerError)
			return
		}

		var contact []string

		for rows.Next() {
			var id string
			var prenom string
			var nom string
			rows.Scan(&id, &prenom, &nom)
			contact = append(contact, id, prenom, nom)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(contact)
	}
}
