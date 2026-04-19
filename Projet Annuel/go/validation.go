package main

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"projet/structures"
)

func validation(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}
		id := request.URL.Query().Get("id")
		var f structures.FichePresta

		err := database.QueryRow("SELECT p.photo_profil, u.nom, u.prenom, u.date_naissance FROM prestataire p JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur WHERE u.id_utilisateur = ?", id).Scan(&f.Photo_profil, &f.Nom, &f.Prenom, &f.Date_naissance)
		rows, err := database.Query("SELECT nom_fichier, type_document FROM document where id_utilisateur = ?", id)
		if err != nil {
			http.Error(response, "Erreur lors de la recuperation des données", http.StatusInternalServerError)
		}

		for rows.Next() {
			var a structures.DocPresta
			err := rows.Scan(&a.Nom_fichier, &a.Type_document)
			if err != nil {
				http.Error(response, "Erreur lors de la recuperation des données", http.StatusInternalServerError)
			}
			f.Documents = append(f.Documents, a)

		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(f)

	}
}

func valider_presta(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		id := request.URL.Query().Get("id")

		selectstatement, err := database.Prepare("UPDATE prestataire SET valider = 1 WHERE id_utilisateur = ?")
		if err != nil {
			http.Error(response, "Erreur preparation requete"+err.Error(), http.StatusInternalServerError)
		}

		_, err = selectstatement.Exec(id)

		if err != nil {
			http.Error(response, "Erreur de l'update"+err.Error(), http.StatusInternalServerError)
		}

		response.Header().Set("Content-Type", "application/json")
		response.WriteHeader(http.StatusOK)
	}
}

func refuser_presta(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		//id := request.URL.Query().Get("id")

	}
}
