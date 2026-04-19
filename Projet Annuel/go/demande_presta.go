package main

import (
	"database/sql"
	"encoding/json"
	"net/http"
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
		var data struct {
			Photo_profil         string            `json:"photo_de_profil"`
			Piece_identite_recto string            `json:"piece_identite_recto"`
			Piece_identite_verso string            `json:"piece_identite_verso"`
			Diplome              string            `json:"diplome"`
			Type                 string            `json:"type"`
			Autre                map[string]string `json:"autre"`
		}
		err := json.NewDecoder(request.Body).Decode(&data)

		if err != nil {
			http.Error(response, "JSON invalide", http.StatusBadRequest)
			return
		}

		var id_user int
		err = database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&id_user)
		if err != nil {
			http.Error(response, "Token invalide", http.StatusUnauthorized)
			return
		}

		tx, err := database.Begin()
		if err != nil {
			http.Error(response, "Erreur transaction", http.StatusInternalServerError)
			return
		}

		_, err = tx.Exec("INSERT INTO prestataire(id_utilisateur, type, photo_profil) VALUES(?,?,?)", id_user, data.Type, data.Photo_profil)
		if err != nil {
			tx.Rollback()
			http.Error(response, "SQL Error: "+err.Error(), http.StatusInternalServerError)
			return
		}
		filesToInsert := []struct {
			Name string
			Type string
		}{
			{data.Photo_profil, "PF"},
			{data.Piece_identite_recto, "CIR"},
			{data.Piece_identite_verso, "CIV"},
			{data.Diplome, "diplome"}}

		for _, f := range filesToInsert {
			if f.Name == "" || f.Name == "NULL" {
				http.Error(response, "Veillez remplir tout les champs pour valider votre demande", http.StatusBadRequest)
				return
			}

			_, err = tx.Exec(
				"INSERT INTO document (nom_fichier, id_utilisateur, type_document) VALUES (?, ?, ?)",
				f.Name, id_user, f.Type,
			)
			if err != nil {
				tx.Rollback()
				http.Error(response, "Erreur insertion document", http.StatusInternalServerError)
				return
			}
		}

		for _, valeur := range data.Autre {
			_, err := tx.Exec("INSERT INTO document(id_utilisateur, nom_fichier, type_document) VALUES(?,?,?)", id_user, valeur, "autre")
			if err != nil {
				tx.Rollback()
				http.Error(response, "Erreur Insert bdd", http.StatusInternalServerError)
				return
			}

		}

		_, err = tx.Exec("UPDATE utilisateur SET role = 'prestataire' WHERE id_utilisateur = ?", id_user)
		if err != nil {
			tx.Rollback()
			http.Error(response, "Erreur mise a jour du role", http.StatusInternalServerError)
			return
		}

		if err = tx.Commit(); err != nil {
			http.Error(response, "Erreur validation transaction", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]string{"message": "Demande envoyee"})
	}
}
