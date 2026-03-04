package main

import (
	"crypto/rand"
	"database/sql"
	"encoding/base64"
	"encoding/json"
	"net/http"

	"golang.org/x/crypto/bcrypt"
)

func inscription(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var req user
		err := json.NewDecoder(request.Body).Decode(&req)

		if len(req.Password) < 8 {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(result{
				Message: "Le mot de passe est trop court",
			})
			return
		}

		var existingEmail string
		selecterr := database.QueryRow("SELECT email FROM utilisateur WHERE email = ?", req.Email).Scan(&existingEmail)
		if selecterr == nil {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(result{
				Message: "Cet email est déjà utilisé",
			})
			return
		}

		password, bcryptError := bcrypt.GenerateFromPassword([]byte(req.Password), 10)
		if bcryptError != nil {
			http.Error(response, "Erreur lors de la génération du mot de passe chiffré", http.StatusInternalServerError)
			return
		}
		hashedPassword := string(password)
		bytes := make([]byte, 32)
		_, randerr := rand.Read(bytes)
		if randerr != nil {
			http.Error(response, "Erreur lors de la génération du token", http.StatusInternalServerError)
			return
		}
		token := base64.RawURLEncoding.EncodeToString(bytes)

		insert, _ := database.Prepare("INSERT INTO utilisateur (email, password, token) VALUES (?, ?, ?)")
		_, err = insert.Exec(req.Email, hashedPassword, token)
		if err != nil {
			http.Error(response, "Erreur lors de l'insertion dans la base de données", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(result{
			Message: "Inscription réussie !",
			Token:   token,
		})
	}
}

func connexion(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var req user
		err := json.NewDecoder(request.Body).Decode(&req)

		var User user
		selecterr := database.QueryRow("SELECT email, password FROM utilisateur WHERE email = ?", req.Email).Scan(&User.Email, &User.Password)
		if selecterr != nil {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(result{
				Message: "L'email ou le mot de passe est incorrect",
			})
			return
		}

		bcryptError := bcrypt.CompareHashAndPassword([]byte(User.Password), []byte(req.Password))
		if bcryptError != nil {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(result{
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

		insert, _ := database.Prepare("UPDATE utilisateur SET token = ? WHERE email = ?")
		_, err = insert.Exec(token, req.Email)
		if err != nil {
			http.Error(response, "Erreur lors de l'insertion dans la base de données", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(result{
			Message: "Connexion réussie !",
			Token:   token,
		})
	}
}

func deconnexion(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		del, _ := database.Prepare("UPDATE utilisateur SET token = NULL WHERE token = ?")
		_, err := del.Exec(token)
		if err != nil {
			http.Error(response, "Erreur lors de la suppression du token de la base de données", http.StatusInternalServerError)
			return
		}
	}
}
