package ressources

import (
	"bytes"
	"database/sql"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"os"
	"strings"
)

func verifierTableAbonnementPush(database *sql.DB) error {
	_, err := database.Exec(`
		CREATE TABLE IF NOT EXISTS abonnement_push (
			id_subscription INT AUTO_INCREMENT PRIMARY KEY,
			id_utilisateur INT NOT NULL,
			subscription_id VARCHAR(191) NOT NULL,
			actif TINYINT(1) NOT NULL DEFAULT 1,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			UNIQUE KEY uniq_subscription_id (subscription_id),
			KEY idx_push_user (id_utilisateur),
			CONSTRAINT fk_push_user FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
		)
	`)
	return err
}

type pushSubscriptionPayload struct {
	SubscriptionID string `json:"subscription_id"`
	Actif          *bool  `json:"actif"`
}

func EnregistrerAbonnementPush(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		var idUser int
		err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}

		var body pushSubscriptionPayload
		if err = json.NewDecoder(request.Body).Decode(&body); err != nil {
			http.Error(response, "Payload invalide", http.StatusBadRequest)
			return
		}

		body.SubscriptionID = strings.TrimSpace(body.SubscriptionID)
		if body.SubscriptionID == "" {
			http.Error(response, "subscription_id obligatoire", http.StatusBadRequest)
			return
		}

		if err = verifierTableAbonnementPush(database); err != nil {
			http.Error(response, "Erreur initialisation push subscription", http.StatusInternalServerError)
			return
		}

		actif := 1
		if body.Actif != nil && !*body.Actif {
			actif = 0
		}

		_, err = database.Exec(`
			INSERT INTO abonnement_push (id_utilisateur, subscription_id, actif)
			VALUES (?, ?, ?)
			ON DUPLICATE KEY UPDATE id_utilisateur = ?, actif = ?
		`, idUser, body.SubscriptionID, actif, idUser, actif)
		if err != nil {
			http.Error(response, "Erreur enregistrement subscription", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"message": "Abonnement push enregistrée",
			"value":   1,
		})
	}
}

func EnvoyerNotificationPushOneSignal(database *sql.DB, userIDs []int, titre string, contenu string) error {
	if len(userIDs) == 0 {
		return nil
	}

	appID := strings.TrimSpace(os.Getenv("ONESIGNAL_APP_ID"))
	restAPIKey := strings.TrimSpace(os.Getenv("ONESIGNAL_REST_API_KEY"))

	if appID == "" || restAPIKey == "" {
		return nil
	}

	if err := verifierTableAbonnementPush(database); err != nil {
		return err
	}

	placeholders := make([]string, len(userIDs))
	args := make([]any, 0, len(userIDs))
	for i, id := range userIDs {
		placeholders[i] = "?"
		args = append(args, id)
	}

	query := `
		SELECT DISTINCT subscription_id
		FROM abonnement_push
		WHERE actif = 1 AND id_utilisateur IN (` + strings.Join(placeholders, ",") + `)
	`
	rows, err := database.Query(query, args...)
	if err != nil {
		return err
	}
	defer rows.Close()

	subscriptionIDs := make([]string, 0)
	for rows.Next() {
		var id string
		if scanErr := rows.Scan(&id); scanErr != nil {
			return scanErr
		}
		id = strings.TrimSpace(id)
		if id != "" {
			subscriptionIDs = append(subscriptionIDs, id)
		}
	}

	if len(subscriptionIDs) == 0 {
		return nil
	}

	payload := map[string]any{
		"app_id":                   appID,
		"include_subscription_ids": subscriptionIDs,
		"headings": map[string]string{
			"fr": titre,
			"en": titre,
		},
		"contents": map[string]string{
			"fr": contenu,
			"en": contenu,
		},
	}
	body, err := json.Marshal(payload)
	if err != nil {
		return err
	}

	req, err := http.NewRequest(http.MethodPost, "https://onesignal.com/api/v1/notifications", bytes.NewBuffer(body))
	if err != nil {
		return err
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Authorization", "Basic "+restAPIKey)

	resp, err := http.DefaultClient.Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	if resp.StatusCode < 200 || resp.StatusCode >= 300 {
		responseBody, _ := io.ReadAll(resp.Body)
		return fmt.Errorf("onesignal status %d: %s", resp.StatusCode, string(responseBody))
	}

	return nil
}
