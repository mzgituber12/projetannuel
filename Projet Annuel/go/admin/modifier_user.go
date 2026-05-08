package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strconv"
	"time"

	"projet/structures"
)

func Gestion_user_email(database *sql.DB) http.HandlerFunc {
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

		email := request.PathValue("email")

		selectstatement, selecterr := database.Prepare("SELECT id_utilisateur, nom, prenom, IFNULL(TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()), 0) AS age, email, role, langue, IFNULL(statut_user, 'actif'), IFNULL(DATE_FORMAT(fin_susp, '%Y-%m-%d %H:%i:%s'), '') FROM utilisateur WHERE email = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'utilisateur", http.StatusInternalServerError)
			return
		}
		var utilisateur structures.User
		selectstatement.QueryRow(email).Scan(&utilisateur.ID, &utilisateur.Nom, &utilisateur.Prenom, &utilisateur.Age, &utilisateur.Email, &utilisateur.Role, &utilisateur.Langue, &utilisateur.StatutUser, &utilisateur.FinSusp)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.User{
			ID:     utilisateur.ID,
			Nom:    utilisateur.Nom,
			Prenom: utilisateur.Prenom,
			Age:    utilisateur.Age,
			Email:  utilisateur.Email,
			Role:       utilisateur.Role,
			Langue:     utilisateur.Langue,
			StatutUser: utilisateur.StatutUser,
			FinSusp:    utilisateur.FinSusp,
		})
	}
}

func Gestion_user_id(database *sql.DB) http.HandlerFunc {
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

		id := request.PathValue("id")

		selectstatement, selecterr := database.Prepare("SELECT id_utilisateur, nom, prenom, IFNULL(TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()), 0) AS age, email, role, langue, IFNULL(statut_user, 'actif'), IFNULL(DATE_FORMAT(fin_susp, '%Y-%m-%d %H:%i:%s'), '') FROM utilisateur WHERE id_utilisateur = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'utilisateur", http.StatusInternalServerError)
			return
		}
		var utilisateur structures.User
		selectstatement.QueryRow(id).Scan(&utilisateur.ID, &utilisateur.Nom, &utilisateur.Prenom, &utilisateur.Age, &utilisateur.Email, &utilisateur.Role, &utilisateur.Langue, &utilisateur.StatutUser, &utilisateur.FinSusp)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.User{
			ID:     utilisateur.ID,
			Nom:    utilisateur.Nom,
			Prenom: utilisateur.Prenom,
			Age:    utilisateur.Age,
			Email:  utilisateur.Email,
			Role:       utilisateur.Role,
			Langue:     utilisateur.Langue,
			StatutUser: utilisateur.StatutUser,
			FinSusp:    utilisateur.FinSusp,
		})
	}
}

func Modifier_user(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodPatch {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		var utilisateur structures.User
		err := json.NewDecoder(request.Body).Decode(&utilisateur)
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des données de l'utilisateur"+err.Error(), http.StatusBadRequest)
			return
		}

		id, err := strconv.Atoi(request.PathValue("id"))
		if err != nil {
			http.Error(response, "ID invalide", http.StatusBadRequest)
			return
		}

		verifemail, err := database.Prepare("SELECT email FROM utilisateur WHERE email = ? AND id_utilisateur != ?")
		if err != nil {
			http.Error(response, "Erreur lors de la vérification de l'existence de l'email", http.StatusInternalServerError)
			return
		}
		var existingEmail string
		err = verifemail.QueryRow(utilisateur.Email, id).Scan(&existingEmail)
		if err == nil {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Un autre utilisateur a deja cette adresse email",
				Value:   0,
			})
			return
		}

		if utilisateur.Role != "admin" && utilisateur.Role != "adherant" && utilisateur.Role != "prestataire" {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "L'utilisateur ne peut avoir que les roles adherant, prestataire ou admin",
				Value:   0,
			})
			return
		}

		updatestatement, updateerr := database.Prepare("UPDATE utilisateur SET nom = ?, prenom = ?, date_naissance = DATE_SUB(CURDATE(), INTERVAL ? YEAR), email = ?, role = ?, langue = ? WHERE id_utilisateur = ?")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requête de mise à jour", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(utilisateur.Nom, utilisateur.Prenom, utilisateur.Age, utilisateur.Email, utilisateur.Role, utilisateur.Langue, id)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la mise à jour de l'utilisateur", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Utilisateur " + utilisateur.Email + " mis à jour avec succès",
			Value:   1,
		})
	}
}

func Supprimer_user(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type")
		response.Header().Set("Access-Control-Allow-Methods", "DELETE, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodDelete {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		id, err := strconv.Atoi(request.PathValue("id"))
		if err != nil {
			http.Error(response, "ID invalide", http.StatusBadRequest)
			return
		}

		updatestatement, updateerr := database.Prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requête de suppression", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(id)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la suppression de l'utilisateur", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusNoContent)
	}
}

func List_users(database *sql.DB) http.HandlerFunc {
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

		rows, err := database.Query("SELECT id_utilisateur, nom, prenom, IFNULL(TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()), 0) AS age, email, role, langue, IFNULL(statut_user, 'actif'), IFNULL(DATE_FORMAT(fin_susp, '%Y-%m-%d %H:%i:%s'), '') FROM utilisateur")

		if err != nil {
			http.Error(response, "Erreur lors de la selection des utilisateurs de la base de données", http.StatusInternalServerError)
			return
		} else {
			var utilisateurs []structures.User

			for rows.Next() {
				var u structures.User

				err := rows.Scan(&u.ID, &u.Nom, &u.Prenom, &u.Age, &u.Email, &u.Role, &u.Langue, &u.StatutUser, &u.FinSusp)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des utilisateurs : "+err.Error(), http.StatusInternalServerError)
					return
				}

				utilisateurs = append(utilisateurs, u)
			}
			if len(utilisateurs) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun utilisateur pour le moment",
				})
				return
			}

			response.WriteHeader(http.StatusOK)
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Utilisateur: utilisateurs,
			})
		}
	}
}

func Bannir_user(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodPatch {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		userID, err := strconv.Atoi(request.PathValue("id"))
		if err != nil {
			http.Error(response, "ID utilisateur invalide", http.StatusBadRequest)
			return
		}

		token := request.Header.Get("Token")
		var adminID int
		err = database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ? AND role = 'admin' LIMIT 1", token).Scan(&adminID)
		if err != nil {
			http.Error(response, "Action non autorisée", http.StatusForbidden)
			return
		}

		var payload struct {
			Type  string `json:"type"`
			Motif string `json:"motif"`
		}
		if err := json.NewDecoder(request.Body).Decode(&payload); err != nil {
			http.Error(response, "Corps de requête invalide", http.StatusBadRequest)
			return
		}
		if payload.Type == "" {
			payload.Type = "temp"
		}
		if payload.Type != "temp" && payload.Type != "perm" && payload.Type != "warn" {
			http.Error(response, "Type de sanction invalide", http.StatusBadRequest)
			return
		}
		if payload.Motif == "" {
			payload.Motif = "Non précisé"
		}

		tx, err := database.Begin()
		if err != nil {
			http.Error(response, "Erreur transaction", http.StatusInternalServerError)
			return
		}
		defer tx.Rollback()

		if _, err := tx.Exec("UPDATE sanction SET active = 0, date_levee = NOW(), levee_par = ? WHERE id_user = ? AND active = 1", adminID, userID); err != nil {
			http.Error(response, "Erreur mise à jour des sanctions", http.StatusInternalServerError)
			return
		}

		var statut string
		var dateFin interface{}
		if payload.Type == "perm" {
			statut = "banni"
			dateFin = nil
		} else if payload.Type == "warn" {
			statut = "actif"
			dateFin = nil
		} else {
			statut = "suspendu"
			dateFin = time.Now().AddDate(0, 0, 7)
		}

		if _, err := tx.Exec("INSERT INTO sanction (id_user, type, motif, date_fin, par_admin, active) VALUES (?, ?, ?, ?, ?, 1)", userID, payload.Type, payload.Motif, dateFin, adminID); err != nil {
			http.Error(response, "Erreur insertion sanction", http.StatusInternalServerError)
			return
		}

		if _, err := tx.Exec("UPDATE utilisateur SET statut_user = ?, fin_susp = ? WHERE id_utilisateur = ?", statut, dateFin, userID); err != nil {
			http.Error(response, "Erreur mise à jour utilisateur", http.StatusInternalServerError)
			return
		}

		if err := tx.Commit(); err != nil {
			http.Error(response, "Erreur validation transaction", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Sanction appliquée avec succès",
			Value:   1,
		})
	}
}

func Debannir_user(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodPatch {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		userID, err := strconv.Atoi(request.PathValue("id"))
		if err != nil {
			http.Error(response, "ID utilisateur invalide", http.StatusBadRequest)
			return
		}

		token := request.Header.Get("Token")
		var adminID int
		err = database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ? AND role = 'admin' LIMIT 1", token).Scan(&adminID)
		if err != nil {
			http.Error(response, "Action non autorisée", http.StatusForbidden)
			return
		}

		tx, err := database.Begin()
		if err != nil {
			http.Error(response, "Erreur transaction", http.StatusInternalServerError)
			return
		}
		defer tx.Rollback()

		if _, err := tx.Exec("UPDATE sanction SET active = 0, date_levee = NOW(), levee_par = ? WHERE id_user = ? AND active = 1", adminID, userID); err != nil {
			http.Error(response, "Erreur mise à jour des sanctions", http.StatusInternalServerError)
			return
		}

		if _, err := tx.Exec("UPDATE utilisateur SET statut_user = 'actif', fin_susp = NULL WHERE id_utilisateur = ?", userID); err != nil {
			http.Error(response, "Erreur mise à jour utilisateur", http.StatusInternalServerError)
			return
		}

		if err := tx.Commit(); err != nil {
			http.Error(response, "Erreur validation transaction", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Utilisateur débanni avec succès",
			Value:   1,
		})
	}
}
