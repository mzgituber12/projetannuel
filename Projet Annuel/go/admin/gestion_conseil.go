package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
	"strconv"
)

func Gestion_conseils(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodGet {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		token := request.Header.Get("Token")
		catStmt, catError := database.Prepare("SELECT role FROM utilisateur WHERE token = ?")
		if catError != nil {
			http.Error(response, "Impossible d'accéder à la base de données", http.StatusInternalServerError)
			return
		}
		var role string
		err := catStmt.QueryRow(token).Scan(&role)
		if err != nil {
			http.Error(response, "Erreur d'authentification", http.StatusInternalServerError)
			return
		}

		if role != "admin" {
			http.Error(response, "Vous n'êtes pas administrateur", http.StatusForbidden)
			return
		}

		rows, err := database.Query("SELECT id_conseil, titre, contenu, image, date_publication FROM conseil ORDER BY date_publication DESC")
		if err != nil {
			http.Error(response, "Erreur lors de la récupération des conseils", http.StatusInternalServerError)
			return
		} else {
			var conseils []structures.Conseil

			for rows.Next() {
				var c structures.Conseil

				var dateSQL string
				err := rows.Scan(&c.ID, &c.Titre, &c.Contenu, &c.Image, &dateSQL)
				if err != nil {
					http.Error(response, "Erreur lors de la récupération des conseils", http.StatusInternalServerError)
					return
				}

				t, err := parseDateTimeFlexible(dateSQL)
				if err != nil {
					http.Error(response, "Erreur lors du traitement de la date", http.StatusInternalServerError)
					return
				}
				c.Date = t.Format("02/01/2006 15:04")
				conseils = append(conseils, c)
			}
			if len(conseils) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun conseil pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Conseil: conseils,
			})
		}
	}
}

func Gestion_conseil_nom(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodGet {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		titre := request.PathValue("titre")

		selectstatement, selecterr := database.Prepare("SELECT id_conseil, titre, contenu, image, date_publication FROM conseil WHERE titre = ? LIMIT 1")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération du conseil", http.StatusInternalServerError)
			return
		}

		var c structures.Conseil
		var dateSQL string
		err := selectstatement.QueryRow(titre).Scan(&c.ID, &c.Titre, &c.Contenu, &c.Image, &dateSQL)
		if err != nil {
			if err == sql.ErrNoRows {
				response.Header().Set("Content-Type", "application/json")
				json.NewEncoder(response).Encode(structures.Conseil{
					ID: 0,
				})
				return
			}
			http.Error(response, "Erreur lors de la récupération du conseil", http.StatusInternalServerError)
			return
		}

		t, err := parseDateTimeFlexible(dateSQL)
		if err != nil {
			http.Error(response, "Erreur lors du traitement de la date", http.StatusInternalServerError)
			return
		}
		c.Date = t.Format("02/01/2006 15:04")

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(c)
	}
}

func Gestion_conseil_id(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodGet {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		id := request.PathValue("id")

		selectstatement, selecterr := database.Prepare("SELECT id_conseil, titre, contenu, image, date_publication FROM conseil WHERE id_conseil = ? LIMIT 1")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération du conseil", http.StatusInternalServerError)
			return
		}

		var c structures.Conseil
		var dateSQL string
		err := selectstatement.QueryRow(id).Scan(&c.ID, &c.Titre, &c.Contenu, &c.Image, &dateSQL)
		if err != nil {
			if err == sql.ErrNoRows {
				response.Header().Set("Content-Type", "application/json")
				json.NewEncoder(response).Encode(structures.Conseil{
					ID: 0,
				})
				return
			}
			http.Error(response, "Erreur lors de la récupération du conseil", http.StatusInternalServerError)
			return
		}

		t, err := parseDateTimeFlexible(dateSQL)
		if err != nil {
			http.Error(response, "Erreur lors du traitement de la date", http.StatusInternalServerError)
			return
		}
		c.Date = t.Format("02/01/2006 15:04")

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(c)
	}
}

func Creer_conseil(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		userStmt, userError := database.Prepare("SELECT id_utilisateur, role FROM utilisateur WHERE token = ?")
		if userError != nil {
			http.Error(response, "Impossible d'accéder à la base de données", http.StatusInternalServerError)
			return
		}
		var userID int
		var role string
		err := userStmt.QueryRow(token).Scan(&userID, &role)
		if err != nil {
			http.Error(response, "Erreur d'authentification", http.StatusUnauthorized)
			return
		}

		if role != "admin" {
			http.Error(response, "Vous n'êtes pas administrateur", http.StatusForbidden)
			return
		}

		var c structures.Conseil
		err = json.NewDecoder(request.Body).Decode(&c)
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des données", http.StatusBadRequest)
			return
		}

		titre := c.Titre
		contenu := c.Contenu
		image := c.Image

		if titre == "" || contenu == "" {
			http.Error(response, "Titre et contenu sont obligatoires", http.StatusBadRequest)
			return
		}

		insertstatement, inserterr := database.Prepare("INSERT INTO conseil (id_utilisateur, titre, contenu, image, date_publication) VALUES (?, ?, ?, ?, NOW())")
		if inserterr != nil {
			http.Error(response, "Erreur lors de la préparation de l'insertion", http.StatusInternalServerError)
			return
		}
		_, insertexecerr := insertstatement.Exec(userID, titre, contenu, image)
		if insertexecerr != nil {
			http.Error(response, "Erreur lors de la création du conseil", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Conseil \"" + titre + "\" créé avec succès",
			Value:   1,
		})
	}
}

func Modifier_conseil(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		userStmt, userError := database.Prepare("SELECT role FROM utilisateur WHERE token = ?")
		if userError != nil {
			http.Error(response, "Impossible d'accéder à la base de données", http.StatusInternalServerError)
			return
		}
		var role string
		err := userStmt.QueryRow(token).Scan(&role)
		if err != nil {
			http.Error(response, "Erreur d'authentification", http.StatusUnauthorized)
			return
		}

		if role != "admin" {
			http.Error(response, "Vous n'êtes pas administrateur", http.StatusForbidden)
			return
		}

		var c structures.Conseil
		err = json.NewDecoder(request.Body).Decode(&c)
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des données", http.StatusBadRequest)
			return
		}

		titre := c.Titre
		contenu := c.Contenu
		image := c.Image

		if titre == "" || contenu == "" {
			http.Error(response, "Titre et contenu sont obligatoires", http.StatusBadRequest)
			return
		}

		id, err := strconv.Atoi(request.PathValue("id"))
		if err != nil {
			http.Error(response, "ID invalide", http.StatusBadRequest)
			return
		}

		updatestatement, updateerr := database.Prepare("UPDATE conseil SET titre = ?, contenu = ?, image = ? WHERE id_conseil = ?")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la mise à jour", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(titre, contenu, image, id)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la mise à jour du conseil", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Conseil \"" + titre + "\" mis à jour avec succès",
			Value:   1,
		})
	}
}

func Supprimer_conseil(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "DELETE, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		userStmt, userError := database.Prepare("SELECT role FROM utilisateur WHERE token = ?")
		if userError != nil {
			http.Error(response, "Impossible d'accéder à la base de données", http.StatusInternalServerError)
			return
		}
		var role string
		err := userStmt.QueryRow(token).Scan(&role)
		if err != nil {
			http.Error(response, "Erreur d'authentification", http.StatusUnauthorized)
			return
		}

		if role != "admin" {
			http.Error(response, "Vous n'êtes pas administrateur", http.StatusForbidden)
			return
		}

		id := request.PathValue("id")

		deletestatement, deleteerr := database.Prepare("DELETE FROM conseil WHERE id_conseil = ?")
		if deleteerr != nil {
			http.Error(response, "Erreur lors de la préparation de la suppression", http.StatusInternalServerError)
			return
		}
		deleteresult, deleteexecerr := deletestatement.Exec(id)
		if deleteexecerr != nil {
			http.Error(response, "Erreur lors de la suppression du conseil", http.StatusInternalServerError)
			return
		}

		rowsAffected, err := deleteresult.RowsAffected()
		if err != nil || rowsAffected == 0 {
			http.Error(response, "Aucun conseil trouvé avec cet ID", http.StatusNotFound)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Conseil supprimé avec succès",
			Value:   1,
		})
	}
}
