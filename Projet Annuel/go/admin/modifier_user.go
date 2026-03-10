package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strconv"

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

		email := request.PathValue("email")

		selectstatement, selecterr := database.Prepare("SELECT id_utilisateur, nom, prenom, age, email, role, langue FROM utilisateur WHERE email = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'utilisateur", http.StatusInternalServerError)
			return
		}
		var utilisateur structures.User
		selectstatement.QueryRow(email).Scan(&utilisateur.ID, &utilisateur.Nom, &utilisateur.Prenom, &utilisateur.Age, &utilisateur.Email, &utilisateur.Role, &utilisateur.Langue)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.User{
			ID:     utilisateur.ID,
			Nom:    utilisateur.Nom,
			Prenom: utilisateur.Prenom,
			Age:    utilisateur.Age,
			Email:  utilisateur.Email,
			Role:   utilisateur.Role,
			Langue: utilisateur.Langue,
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

		id := request.PathValue("id")

		selectstatement, selecterr := database.Prepare("SELECT id_utilisateur, nom, prenom, age, email, role, langue FROM utilisateur WHERE id_utilisateur = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'utilisateur", http.StatusInternalServerError)
			return
		}
		var utilisateur structures.User
		selectstatement.QueryRow(id).Scan(&utilisateur.ID, &utilisateur.Nom, &utilisateur.Prenom, &utilisateur.Age, &utilisateur.Email, &utilisateur.Role, &utilisateur.Langue)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.User{
			ID:     utilisateur.ID,
			Nom:    utilisateur.Nom,
			Prenom: utilisateur.Prenom,
			Age:    utilisateur.Age,
			Email:  utilisateur.Email,
			Role:   utilisateur.Role,
			Langue: utilisateur.Langue,
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

		updatestatement, updateerr := database.Prepare("UPDATE utilisateur SET nom = ?, prenom = ?, age = ?, email = ?, role = ?, langue = ? WHERE id_utilisateur = ?")
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
