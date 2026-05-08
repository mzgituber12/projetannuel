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

		rowss, err := database.Query("SELECT categorie_text, contenu FROM document_txt where id_utilisateur = ?", id)
		if err != nil {
			http.Error(response, "Erreur lors de la recuperation des données", http.StatusInternalServerError)
		}

		for rowss.Next() {
			var a structures.DocPresta_txt
			err := rowss.Scan(&a.Categorie_text, &a.Contenu)
			if err != nil {
				http.Error(response, "Erreur lors de la recuperation des données texte", http.StatusInternalServerError)
			}
			f.Documents_txt = append(f.Documents_txt, a)

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
		if id == "" {
			http.Error(response, "ID utilisateur manquant", http.StatusBadRequest)
			return
		}

		tx, err := database.Begin()
		if err != nil {
			http.Error(response, "Erreur preparation requete"+err.Error(), http.StatusInternalServerError)
			return
		}

		updatePrestaStmt, err := tx.Prepare("UPDATE prestataire SET valider = 1 WHERE id_utilisateur = ?")
		if err != nil {
			tx.Rollback()
			http.Error(response, "Erreur preparation requete"+err.Error(), http.StatusInternalServerError)
			return
		}
		defer updatePrestaStmt.Close()

		_, err = updatePrestaStmt.Exec(id)
		if err != nil {
			tx.Rollback()
			http.Error(response, "Erreur de l'update"+err.Error(), http.StatusInternalServerError)
			return
		}

		updateRoleStmt, err := tx.Prepare("UPDATE utilisateur SET role = 'prestataire' WHERE id_utilisateur = ?")
		if err != nil {
			tx.Rollback()
			http.Error(response, "Erreur preparation update role"+err.Error(), http.StatusInternalServerError)
			return
		}
		defer updateRoleStmt.Close()

		_, err = updateRoleStmt.Exec(id)
		if err != nil {
			tx.Rollback()
			http.Error(response, "Erreur update role utilisateur"+err.Error(), http.StatusInternalServerError)
			return
		}

		err = tx.Commit()
		if err != nil {
			http.Error(response, "Erreur validation transaction", http.StatusInternalServerError)
			return
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

		id := request.URL.Query().Get("id")

		_, err := database.Exec("DELETE FROM document WHERE id_utilisateur = ?", id)
		if err != nil {
			http.Error(response, "Erreur lors de la suppression"+err.Error(), http.StatusInternalServerError)
			return
		}

		_, err = database.Exec("DELETE FROM document_txt WHERE id_utilisateur = ?", id)
		if err != nil {
			http.Error(response, "Erreur lors de la suppression"+err.Error(), http.StatusInternalServerError)
			return
		}

		_, err = database.Exec("DELETE FROM prestataire WHERE id_utilisateur = ?", id)
		if err != nil {
			http.Error(response, "Erreur lors de la suppression"+err.Error(), http.StatusInternalServerError)
			return
		}

	}
}
