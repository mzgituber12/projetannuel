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

func Inscription(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodPost {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		var req structures.User
		err := json.NewDecoder(request.Body).Decode(&req)
		if err != nil {
			http.Error(response, "Erreur lors de la sauvegarde des données", http.StatusInternalServerError)
			return
		}

		if len(req.Password) < 8 {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Le mot de passe est trop court",
			})
			return
		}

		var existingEmail string
		selecterr := database.QueryRow("SELECT email FROM utilisateur WHERE email = ?", req.Email).Scan(&existingEmail)
		if selecterr == nil {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Cet email est déjà utilisé",
			})
			return
		}

		if selecterr != sql.ErrNoRows {
			http.Error(response, "Erreur lors de la selection de l'utilisateur", http.StatusInternalServerError)
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

		insert, err := database.Prepare("INSERT INTO utilisateur (email, password, prenom, nom, age, token) VALUES (?, ?, ?, ?, ?, ?)")
		if err != nil {
			http.Error(response, "Erreur lors de l'insertion de l'utilisateur dans la base de données", http.StatusInternalServerError)
			return
		}

		_, err = insert.Exec(req.Email, hashedPassword, req.Prenom, req.Nom, req.Age, token)
		if err != nil {
			http.Error(response, "Erreur lors de l'insertion dans la base de données", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusCreated)
		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Inscription réussie !",
			Token:   token,
		})
	}
}
