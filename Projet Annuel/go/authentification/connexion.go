package authentification

import (
	"crypto/rand"
	"database/sql"
	"encoding/base64"
	"encoding/json"
	"net/http"

	"projet/structures"

	"golang.org/x/crypto/bcrypt"
)

func Connexion(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var req structures.User
		err := json.NewDecoder(request.Body).Decode(&req)

		var User structures.User
		selecterr := database.QueryRow("SELECT email, password FROM utilisateur WHERE email = ?", req.Email).Scan(&User.Email, &User.Password)
		if selecterr != nil {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "L'email ou le mot de passe est incorrect",
			})
			return
		}

		bcryptError := bcrypt.CompareHashAndPassword([]byte(User.Password), []byte(req.Password))
		if bcryptError != nil {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "L'email ou le mot de passe est incorrect",
			})
			return
		}

		bytes := make([]byte, 32)
		_, randerr := rand.Read(bytes)
		if randerr != nil {
			http.Error(response, "Erreur lors de la génération du token", http.StatusInternalServerError)
			return
		}
		token := base64.RawURLEncoding.EncodeToString(bytes)

		insert, err := database.Prepare("UPDATE utilisateur SET token = ? WHERE email = ?")
		if err != nil {
			http.Error(response, "Erreur lors de la préparation de la requete d'insertion", http.StatusInternalServerError)
			return
		}

		_, err = insert.Exec(token, req.Email)
		if err != nil {
			http.Error(response, "Erreur lors de l'insertion dans la base de données", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Connexion réussie !",
			Token:   token,
		})
	}
}
