package main

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func nous_contacter(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type,Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		var msg structures.Result
		err := json.NewDecoder(request.Body).Decode(&msg)
		if err != nil {
			http.Error(response, "Erreur lors de la lecture de la requête", http.StatusBadRequest)
			return
		}

		if (len(msg.Message) < 5) || (len(msg.Message) > 500) {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Le message doit contenir entre 5 et 500 caractères",
			})
			return
		}

		sel, err := database.Prepare("SELECT id_utilisateur FROM utilisateur WHERE token = ?")
		if err != nil {
			http.Error(response, "Erreur lors de la recupération de l'id de l'utilisateur", http.StatusInternalServerError)
			return
		}
		var id int
		err = sel.QueryRow(token).Scan(&id)
		if err != nil {
			http.Error(response, "Token invalide", http.StatusUnauthorized)
			return
		}

		envmsg, nil := database.Prepare("INSERT INTO contact (id_utilisateur, contenu) VALUES (?, ?)")
		if nil != err {
			http.Error(response, "Erreur lors de la préparation de la requête d'insertion du message", http.StatusInternalServerError)
			return
		}
		_, err = envmsg.Exec(id, msg.Message)
		if err != nil {
			http.Error(response, "Erreur lors de l'envoi du message", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Message envoyé avec succès, nous vous répondrons dans les plus brefs délais.",
		})
	}
}
