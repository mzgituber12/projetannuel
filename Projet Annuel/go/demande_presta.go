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
			Autre_txt            map[string]string `json:"autre_txt"`
		}
		err := json.NewDecoder(request.Body).Decode(&data)

		if err != nil {
			http.Error(response, "JSON invalide", http.StatusBadRequest)
			return
		}

		var id_user int
		var currentRole string
		err = database.QueryRow("SELECT id_utilisateur, role FROM utilisateur WHERE token = ?", token).Scan(&id_user, &currentRole)
		if err != nil {
			http.Error(response, "Token invalide", http.StatusUnauthorized)
			return
		}
		if currentRole == "prestataire" {
			http.Error(response, "Vous etes deja prestataire", http.StatusBadRequest)
			return
		}

		tx, err := database.Begin()
		if err != nil {
			http.Error(response, "Erreur transaction", http.StatusInternalServerError)
			return
		}

		_, err = tx.Exec("INSERT INTO prestataire(id_utilisateur, type, photo_profil) VALUES(?,?,?)", id_user, data.Type, data.Photo_profil)
		if err != nil {
			http.Error(response, "Error sql"+err.Error(), http.StatusInternalServerError)
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
				http.Error(response, "Erreur insertion document", http.StatusInternalServerError)
				return
			}
		}

		for _, valeur := range data.Autre {
			_, err := tx.Exec("INSERT INTO document(id_utilisateur, nom_fichier, type_document) VALUES(?,?,?)", id_user, valeur, "autre")
			if err != nil {
				http.Error(response, "Erreur insertion bdd", http.StatusInternalServerError)
				return
			}

		}

		for cle, valeur := range data.Autre_txt {
			if valeur == "" {
				http.Error(response, "Veillez remplir tout les champs pour valider votre demande", http.StatusBadRequest)
			}

			_, err := tx.Exec(
				"INSERT INTO document_txt (id_utilisateur, categorie_text, contenu) VALUES (?, ?, ?)",
				id_user,
				cle,
				valeur,
			)

			if err != nil {
				tx.Rollback()
				http.Error(response, "Erreur lors de l'enregistrement des informations textuelles", http.StatusInternalServerError)
				return
			}
		}

		if err = tx.Commit(); err != nil {
			http.Error(response, "Erreur validation transaction", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]string{"message": "Demande envoyee"})
	}
}

type Champ struct {
	Label string `json:"label"`
	ID    string `json:"id"`
	Type  string `json:"type"`
}

func get_champs_postuler(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Content-Type", "application/json")

		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query("SELECT id, nom FROM categories")
		if err != nil {
			http.Error(response, "Erreur recupere categorie"+err.Error(), http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		result := make(map[string][]Champ)

		for rows.Next() {
			var categorie_iD int
			var categorie_nom string
			err := rows.Scan(&categorie_iD, &categorie_nom)
			if err != nil {
				http.Error(response, "Erreur scan"+err.Error(), http.StatusInternalServerError)
				return
			}

			fieldRows, err := database.Query("SELECT label, type, input_id FROM champs_supplementaires WHERE categorie_id = ?", categorie_iD)
			if err != nil {
				http.Error(response, err.Error(), http.StatusInternalServerError)
				return
			}

			var champs []Champ
			for fieldRows.Next() {
				var label, champType, inputID string
				err := fieldRows.Scan(&label, &champType, &inputID)
				if err != nil {
					fieldRows.Close()
					http.Error(response, "Erreur du scan"+err.Error(), http.StatusInternalServerError)
					return
				}
				champs = append(champs, Champ{
					Label: label,
					ID:    inputID,
					Type:  champType,
				})
			}
			fieldRows.Close()
			result[categorie_nom] = champs
		}

		err = json.NewEncoder(response).Encode(result)
		if err != nil {
			http.Error(response, "Erreur envoie des champs"+err.Error(), http.StatusInternalServerError)
		}
	}
}

func new_champs_postuler(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Content-Type", "application/json")

		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		type Champ struct {
			Label    string `json:"label"`
			Type     string `json:"type"`
			ID_input string `json:"input_id"`
		}

		var data struct {
			Nom_categorie string  `json:"nom_categorie"`
			Champs        []Champ `json:"champs"`
		}

		var nom string

		err := json.NewDecoder(request.Body).Decode(&data)

		if err != nil {
			http.Error(response, "JSON invalide", http.StatusBadRequest)
			return
		}

		for _, champ := range data.Champs {
			if champ.Label == "" || champ.Type == "" || champ.ID_input == "" {
				http.Error(response, "Veuillez remplir tous les champs supplémentaires", http.StatusBadRequest)
				return
			}
		}

		if data.Nom_categorie == "" {
			http.Error(response, "Veuillez mettre un nom pour la categorie", http.StatusBadRequest)
			return
		}

		err = database.QueryRow("SELECT nom FROM categories WHERE nom = ?", data.Nom_categorie).Scan(&nom)
		if err == nil {
			http.Error(response, "Cette catégorie existe deja", http.StatusBadRequest)
			return

		}

		selectstatement, err := database.Prepare("INSERT INTO categories(nom) VALUES(?)")
		if err != nil {
			http.Error(response, "Erreur de préparation de la requete"+err.Error(), http.StatusInternalServerError)
			return
		}
		_, err = selectstatement.Exec(data.Nom_categorie)
		if err != nil {
			http.Error(response, "Erreur lors de l'insertion de la categorie "+err.Error(), http.StatusInternalServerError)
			return
		}
		var categorie_id int

		err = database.QueryRow("SELECT id FROM categories WHERE nom = ?", data.Nom_categorie).Scan(&categorie_id)
		if err != nil {
			http.Error(response, "Erreur pour récupérer l'id de la catégorie"+err.Error(), http.StatusInternalServerError)
			return
		}

		for _, champ := range data.Champs {
			stmt, err := database.Prepare("INSERT INTO champs_supplementaires(label, type, input_id, categorie_id) VALUES(?,?,?,?)")
			if err != nil {
				http.Error(response, "Erreur lors de la préparation de l'insertion", http.StatusInternalServerError)
				return
			}
			_, err = stmt.Exec(champ.Label, champ.Type, champ.ID_input, categorie_id)
			if err != nil {
				http.Error(response, "Erreur lors de l'ajout des champs: "+err.Error(), http.StatusInternalServerError)
				return
			}
		}
		response.WriteHeader(http.StatusOK)
	}
}
