package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strings"

	_ "github.com/go-sql-driver/mysql"
)

func LoadMessages(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		contactID := request.URL.Query().Get("contact_id")

		var userID int
		err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&userID)
		if err != nil {
			http.Error(response, "Token invalide", http.StatusUnauthorized)
			return
		}

		rows, err := database.Query(`
			SELECT m.contenu, m.date_envoie, u.prenom
			FROM message m
			JOIN utilisateur u ON m.id_expediteur = u.id_utilisateur
			WHERE (m.id_expediteur = ? AND m.id_destinataire = ?)
			   OR (m.id_expediteur = ? AND m.id_destinataire = ?)
			ORDER BY m.date_envoie ASC
		`, userID, contactID, contactID, userID)

		if err != nil {
			http.Error(response, "Erreur lors de la récupération des messages", http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		var messages []map[string]string

		for rows.Next() {
			var contenu, date, prenom string
			rows.Scan(&contenu, &date, &prenom)
			messages = append(messages, map[string]string{
				"contenu":     contenu,
				"date_envoie": date,
				"auteur":      prenom,
			})
		}

		response.Header().Set("Content-Type", "application/json")
		if messages == nil {
			messages = []map[string]string{}
		}
		json.NewEncoder(response).Encode(messages)
	}
}

func LoadContacts(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")

		var userID int
		err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&userID)
		if err != nil {
			http.Error(response, "Token invalide", http.StatusUnauthorized)
			return
		}

		rows, err := database.Query(`
			SELECT u.id_utilisateur, u.prenom, u.nom
			FROM utilisateur u
			JOIN lien_contact lc ON (lc.id_user1 = ? AND lc.id_user2 = u.id_utilisateur)
			                     OR (lc.id_user2 = ? AND lc.id_user1 = u.id_utilisateur)
		`, userID, userID)

		if err != nil {
			http.Error(response, "Erreur lors de la récupération des contacts", http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		var contacts []map[string]any

		for rows.Next() {
			var id int
			var prenom string
			var nom string
			rows.Scan(&id, &prenom, &nom)
			contacts = append(contacts, map[string]any{
				"id":     id,
				"prenom": prenom,
				"nom":    nom,
			})
		}

		response.Header().Set("Content-Type", "application/json")
		if contacts == nil {
			contacts = []map[string]any{}
		}
		json.NewEncoder(response).Encode(contacts)
	}
}

func SendMessage(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")

		var data struct {
			DestinataireID int    `json:"destinataire_id"`
			Contenu        string `json:"contenu"`
		}

		err := json.NewDecoder(request.Body).Decode(&data)
		if err != nil {
			http.Error(response, "JSON invalide", http.StatusBadRequest)
			return
		}

		contenu := strings.TrimSpace(data.Contenu)
		if len(contenu) < 1 || len(contenu) > 5000 {
			http.Error(response, "Le message doit contenir entre 1 et 5000 caractères", http.StatusBadRequest)
			return
		}

		var userID int
		err = database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&userID)
		if err != nil {
			http.Error(response, "Token invalide", http.StatusUnauthorized)
			return
		}

		_, err = database.Exec(
			"INSERT INTO message (id_expediteur, id_destinataire, contenu, date_envoie) VALUES (?, ?, ?, NOW())",
			userID, data.DestinataireID, contenu,
		)

		if err != nil {
			http.Error(response, "Erreur lors de l'envoi du message", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]string{"message": "Message envoyé avec succès"})
	}
}
