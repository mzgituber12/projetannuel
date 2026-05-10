package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"projet/structures"
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
		var prenom_user string
		err := database.QueryRow("SELECT id_utilisateur, prenom FROM utilisateur WHERE token = ?", token).Scan(&userID, &prenom_user)
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
				"user":        prenom_user,
			})
		}

		response.Header().Set("Content-Type", "application/json")
		if messages == nil {
			messages = []map[string]string{}
		}
		json.NewEncoder(response).Encode(messages)
	}
}

func AddContact(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "DELETE, OPTIONS")
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

		var data map[string]interface{}
		json.NewDecoder(request.Body).Decode(&data)

		var userID2 int
		err = database.QueryRow(`
			SELECT id_utilisateur
			FROM utilisateur
			WHERE LOWER(CONCAT(prenom, ' ', nom)) = LOWER(?)
		`, data["user"]).Scan(&userID2)

		if err != nil {
			if err == sql.ErrNoRows {
				response.Header().Set("Content-Type", "application/json")
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun Utilisateur trouvé",
				})
				return
			} else {
				http.Error(response, "Erreur lors de la selection", http.StatusInternalServerError)
				return
			}
		}

		var block string
		err = database.QueryRow(`
			SELECT state
			FROM lien_contact_state
			WHERE id_user1 = ? AND id_user2 = ?
		`, userID2, userID).Scan(&block)
		if err != nil && err != sql.ErrNoRows {
			http.Error(response, "Erreur lors de la selection de l'état", http.StatusInternalServerError)
			return
		}
		switch block {
		case "bloquer":
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Cet utilisateur vous a bloqué",
			})
			return
		case "demande_ami":
			_, err = database.Exec(`
			DELETE FROM lien_contact_state
			WHERE id_user1 = ? AND id_user2 = ?
			`, userID2, userID)
			if err != nil {
				http.Error(response, "Erreur lors de la suppression lié a la demande d'ami", http.StatusInternalServerError)
				return
			}
			_, err = database.Exec(`
			INSERT INTO lien_contact (id_user1, id_user2)
			VALUES (?, ?)
			`, userID, userID2)
			if err != nil {
				http.Error(response, "Erreur lors de l'insertion lié a la demande d'ami'", http.StatusInternalServerError)
				return
			}

			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Demande de contact acceptée",
			})
			return
		}

		var block2 string
		err = database.QueryRow(`
			SELECT state
			FROM lien_contact_state
			WHERE id_user1 = ? AND id_user2 = ?
		`, userID, userID2).Scan(&block2)
		if err != nil && err != sql.ErrNoRows {
			http.Error(response, "Erreur lors de la selection de l'etat (dans l'autre sens)", http.StatusInternalServerError)
			return
		}
		switch block2 {
		case "bloquer":
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Debloquez d'abord cet utilisateur",
			})
			return
		case "demande_ami":
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Demande de contact déjà envoyée",
			})
			return
		}

		var state string
		err = database.QueryRow(`
    	SELECT id_lien
    	FROM lien_contact
    	WHERE (id_user1 = ? AND id_user2 = ?)
		OR (id_user1 = ? AND id_user2 = ?)
		`, userID, userID2, userID2, userID).Scan(&state)

		if err != nil {
			if err != sql.ErrNoRows {
				http.Error(response, "Erreur lors de la sélection de l'amitié existante", http.StatusInternalServerError)
				return
			}
		}

		if err == nil {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Utilisateur déjà en contact",
			})
			return
		}

		_, err = database.Exec(`
			INSERT INTO lien_contact_state (id_user1, id_user2, state)
			VALUES (?, ?, "demande_ami")
			`, userID, userID2)
		if err != nil {
			http.Error(response, "Erreur lors de l'insertion'", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Demande de contact envoyée à " + data["user"].(string),
		})
	}
}

func DeleteContact(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "DELETE, OPTIONS")
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

		var userID2 = request.PathValue("id")

		_, err = database.Exec(`
			DELETE FROM lien_contact
				WHERE (id_user1 = ? AND id_user2 = ?)
			    OR (id_user2 = ? AND id_user1 = ?)
		`, userID, userID2, userID, userID2)

		if err != nil {
			http.Error(response, "Erreur lors de la suppression du contact", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusOK)
	}
}

func RemoveDemand(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "DELETE, OPTIONS")
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

		var userID2 = request.PathValue("id")

		_, err = database.Exec(`
			DELETE FROM lien_contact_state
			WHERE id_user1 = ? AND id_user2 = ? AND state = 'demande_ami'
		`, userID, userID2)

		if err != nil {
			http.Error(response, "Erreur lors de l'action d'annuler la demande", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusOK)
	}
}

func DenyDemand(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "DELETE, OPTIONS")
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

		var userID2 = request.PathValue("id")

		_, err = database.Exec(`
			DELETE FROM lien_contact_state
			WHERE id_user2 = ? AND id_user1 = ? AND state = 'demande_ami'
		`, userID, userID2)

		if err != nil {
			http.Error(response, "Erreur lors de l'action de refuser la demande", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusOK)
	}
}

func AcceptDemand(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "DELETE, OPTIONS")
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

		var userID2 = request.PathValue("id")

		_, err = database.Exec(`
			DELETE FROM lien_contact_state
			WHERE id_user2 = ? AND id_user1 = ? AND state = 'demande_ami'
		`, userID, userID2)

		if err != nil {
			http.Error(response, "Erreur lors de l'action de refuser la demande", http.StatusInternalServerError)
			return
		}

		_, err = database.Exec(`
			INSERT INTO lien_contact (id_user1, id_user2)
			VALUES (?, ?)
		`, userID, userID2)

		if err != nil {
			http.Error(response, "Erreur lors de l'action d'ajouter un contact", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusOK)
	}
}

func BlockContact(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
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

		var userID2 = request.PathValue("id")

		_, err = database.Exec(`
			INSERT INTO lien_contact_state (id_user1, id_user2, state)
			VALUES (?, ?, 'bloquer')
		`, userID, userID2)

		if err != nil {
			http.Error(response, "Erreur lors de l'action de bloquer le contact", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusOK)
	}
}

func DeblockContact(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
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

		var userID2 = request.PathValue("id")

		_, err = database.Exec(`
			DELETE FROM lien_contact_state
			WHERE id_user1 = ? AND id_user2 = ?
		`, userID, userID2)

		if err != nil {
			http.Error(response, "Erreur lors de l'action de débloquer le contact", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusOK)
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

func LoadContactsBlock(database *sql.DB) http.HandlerFunc {
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
			JOIN lien_contact_state lc ON (lc.id_user1 = ? AND lc.id_user2 = u.id_utilisateur)
			WHERE lc.state = "bloquer"
		`, userID)

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

func LoadContactsSend(database *sql.DB) http.HandlerFunc {
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
			JOIN lien_contact_state lc ON (lc.id_user1 = ? AND lc.id_user2 = u.id_utilisateur)
			WHERE lc.state = "demande_ami"
		`, userID)

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

func LoadContactsGet(database *sql.DB) http.HandlerFunc {
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
			JOIN lien_contact_state lc ON (lc.id_user2 = ? AND lc.id_user1 = u.id_utilisateur)
			WHERE lc.state = "demande_ami"
		`, userID)

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
