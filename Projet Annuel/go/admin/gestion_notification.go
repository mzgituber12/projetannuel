package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strconv"

	"projet/ressources"
	"projet/structures"
)

type notificationPayload struct {
	IDDestinataire int    `json:"id_destinataire"`
	RoleCible      string `json:"role_cible"`
	Titre          string `json:"titre"`
	Contenu        string `json:"contenu"`
}

func recupererAdmin(database *sql.DB, token string) (int, error) {
	var idAdmin int
	var role string
	err := database.QueryRow("SELECT id_utilisateur, role FROM utilisateur WHERE token = ?", token).Scan(&idAdmin, &role)
	if err != nil {
		return 0, err
	}
	if role != "admin" {
		return 0, sql.ErrNoRows
	}
	return idAdmin, nil
}

func Gestion_notifications(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		_, err := recupererAdmin(database, token)
		if err != nil {
			http.Error(response, "Vous n'êtes pas administrateur", http.StatusForbidden)
			return
		}

		rows, err := database.Query(`
			SELECT n.id_notification,
			       IFNULL(n.id_expediteur, 0),
			       IFNULL(n.id_destinataire, 0),
			       IFNULL(n.Titre, ''),
			       IFNULL(n.contenu, ''),
			       IFNULL(n.date_envoie, NOW()),
			       IFNULL(n.lu, 0),
			       IFNULL(CONCAT(ue.prenom, ' ', ue.nom), 'Système') AS expediteur,
			       IFNULL(CONCAT(ud.prenom, ' ', ud.nom), '') AS destinataire
			FROM notification n
			LEFT JOIN utilisateur ue ON ue.id_utilisateur = n.id_expediteur
			LEFT JOIN utilisateur ud ON ud.id_utilisateur = n.id_destinataire
			ORDER BY n.date_envoie DESC, n.id_notification DESC
		`)
		if err != nil {
			http.Error(response, "Erreur lors de la récupération des notifications", http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		var notifications []structures.Notification
		for rows.Next() {
			var n structures.Notification
			var dateSQL string
			err = rows.Scan(&n.ID, &n.IDExpediteur, &n.IDDestinataire, &n.Titre, &n.Contenu, &dateSQL, &n.Lu, &n.Expediteur, &n.Destinataire)
			if err != nil {
				http.Error(response, "Erreur lors de la lecture des notifications", http.StatusInternalServerError)
				return
			}
			n.DateEnvoie = dateSQL
			notifications = append(notifications, n)
		}

		response.Header().Set("Content-Type", "application/json")
		if len(notifications) == 0 {
			json.NewEncoder(response).Encode(structures.Result{Message: "Aucune notification"})
			return
		}
		json.NewEncoder(response).Encode(structures.List{Notification: notifications})
	}
}

func Creer_notification(database *sql.DB) http.HandlerFunc {
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

		idAdmin, err := recupererAdmin(database, token)
		if err != nil {
			http.Error(response, "Vous n'êtes pas administrateur", http.StatusForbidden)
			return
		}

		var body notificationPayload
		err = json.NewDecoder(request.Body).Decode(&body)
		if err != nil {
			http.Error(response, "Payload invalide", http.StatusBadRequest)
			return
		}

		if body.Titre == "" || body.Contenu == "" {
			http.Error(response, "Titre et contenu obligatoires", http.StatusBadRequest)
			return
		}

		if body.IDDestinataire > 0 {
			_, err = database.Exec(
				"INSERT INTO notification (id_expediteur, id_destinataire, Titre, contenu, date_envoie, lu) VALUES (?, ?, ?, ?, NOW(), 0)",
				idAdmin,
				body.IDDestinataire,
				body.Titre,
				body.Contenu,
			)
			if err != nil {
				http.Error(response, "Erreur lors de l'envoi de la notification", http.StatusInternalServerError)
				return
			}

			_ = ressources.EnvoyerNotificationPushOneSignal(database, []int{body.IDDestinataire}, body.Titre, body.Contenu)

			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{Message: "Notification envoyée", Value: 1})
			return
		}

		role := body.RoleCible
		if role == "" {
			http.Error(response, "id_destinataire ou role_cible obligatoire", http.StatusBadRequest)
			return
		}

		var rows *sql.Rows
		if role == "all" {
			rows, err = database.Query("SELECT id_utilisateur FROM utilisateur")
		} else {
			rows, err = database.Query("SELECT id_utilisateur FROM utilisateur WHERE role = ?", role)
		}
		if err != nil {
			http.Error(response, "Erreur lors de la récupération des destinataires", http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		insertStmt, err := database.Prepare("INSERT INTO notification (id_expediteur, id_destinataire, Titre, contenu, date_envoie, lu) VALUES (?, ?, ?, ?, NOW(), 0)")
		if err != nil {
			http.Error(response, "Erreur lors de la préparation de l'envoi en masse", http.StatusInternalServerError)
			return
		}
		defer insertStmt.Close()

		count := 0
		var destinataires []int
		for rows.Next() {
			var idDest int
			if scanErr := rows.Scan(&idDest); scanErr != nil {
				http.Error(response, "Erreur lors de la lecture des destinataires", http.StatusInternalServerError)
				return
			}

			if _, execErr := insertStmt.Exec(idAdmin, idDest, body.Titre, body.Contenu); execErr != nil {
				http.Error(response, "Erreur envoi en masse", http.StatusInternalServerError)
				return
			}
			destinataires = append(destinataires, idDest)
			count++
		}

		if err = rows.Err(); err != nil {
			http.Error(response, "Erreur lors de la lecture des destinataires", http.StatusInternalServerError)
			return
		}

		if len(destinataires) > 0 {
			_ = ressources.EnvoyerNotificationPushOneSignal(database, destinataires, body.Titre, body.Contenu)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"message": "Notification(s) envoyée(s)",
			"value":   1,
			"count":   count,
		})
	}
}

func Modifier_notification(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		_, err := recupererAdmin(database, token)
		if err != nil {
			http.Error(response, "Vous n'êtes pas administrateur", http.StatusForbidden)
			return
		}

		idNotif, convErr := strconv.Atoi(request.PathValue("id"))
		if convErr != nil || idNotif <= 0 {
			http.Error(response, "Notification invalide", http.StatusBadRequest)
			return
		}

		var body notificationPayload
		err = json.NewDecoder(request.Body).Decode(&body)
		if err != nil {
			http.Error(response, "Payload invalide", http.StatusBadRequest)
			return
		}

		if body.Titre == "" || body.Contenu == "" {
			http.Error(response, "Titre et contenu obligatoires", http.StatusBadRequest)
			return
		}

		result, err := database.Exec("UPDATE notification SET Titre = ?, contenu = ? WHERE id_notification = ?", body.Titre, body.Contenu, idNotif)
		if err != nil {
			http.Error(response, "Erreur lors de la modification", http.StatusInternalServerError)
			return
		}

		rowsAffected, _ := result.RowsAffected()
		if rowsAffected == 0 {
			http.Error(response, "Notification introuvable", http.StatusNotFound)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: "Notification modifiée", Value: 1})
	}
}

func Supprimer_notification(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "DELETE, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		_, err := recupererAdmin(database, token)
		if err != nil {
			http.Error(response, "Vous n'êtes pas administrateur", http.StatusForbidden)
			return
		}

		idNotif, convErr := strconv.Atoi(request.PathValue("id"))
		if convErr != nil || idNotif <= 0 {
			http.Error(response, "Notification invalide", http.StatusBadRequest)
			return
		}

		result, err := database.Exec("DELETE FROM notification WHERE id_notification = ?", idNotif)
		if err != nil {
			http.Error(response, "Erreur lors de la suppression", http.StatusInternalServerError)
			return
		}

		rowsAffected, _ := result.RowsAffected()
		if rowsAffected == 0 {
			http.Error(response, "Notification introuvable", http.StatusNotFound)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: "Notification supprimée", Value: 1})
	}
}

func Gestion_modeles(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		_, err := recupererAdmin(database, token)
		if err != nil {
			http.Error(response, "Vous n'êtes pas administrateur", http.StatusForbidden)
			return
		}

		rows, err := database.Query("SELECT id_modele, cle, titre, contenu FROM modele_notification ORDER BY id_modele")
		if err != nil {
			http.Error(response, "Erreur lors de la récupération des modèles", http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		var modeles []structures.ModeleNotification
		for rows.Next() {
			var m structures.ModeleNotification
			if scanErr := rows.Scan(&m.ID, &m.Cle, &m.Titre, &m.Contenu); scanErr != nil {
				http.Error(response, "Erreur lecture modèles", http.StatusInternalServerError)
				return
			}
			modeles = append(modeles, m)
		}

		response.Header().Set("Content-Type", "application/json")
		if len(modeles) == 0 {
			json.NewEncoder(response).Encode(structures.Result{Message: "Aucun modèle"})
			return
		}
		json.NewEncoder(response).Encode(structures.List{ModeleNotification: modeles})
	}
}

func Modifier_modele(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		_, err := recupererAdmin(database, token)
		if err != nil {
			http.Error(response, "Vous n'êtes pas administrateur", http.StatusForbidden)
			return
		}

		idModele, convErr := strconv.Atoi(request.PathValue("id"))
		if convErr != nil || idModele <= 0 {
			http.Error(response, "Modèle invalide", http.StatusBadRequest)
			return
		}

		var body structures.ModeleNotification
		if err = json.NewDecoder(request.Body).Decode(&body); err != nil {
			http.Error(response, "Payload invalide", http.StatusBadRequest)
			return
		}

		if body.Titre == "" || body.Contenu == "" {
			http.Error(response, "Titre et contenu obligatoires", http.StatusBadRequest)
			return
		}

		if len(body.Titre) > 50 {
			http.Error(response, "Le titre ne peut pas dépasser 50 caractères", http.StatusBadRequest)
			return
		}

		result, err := database.Exec(
			"UPDATE modele_notification SET titre = ?, contenu = ? WHERE id_modele = ?",
			body.Titre, body.Contenu, idModele,
		)
		if err != nil {
			http.Error(response, "Erreur lors de la modification du modèle", http.StatusInternalServerError)
			return
		}

		rowsAffected, _ := result.RowsAffected()
		if rowsAffected == 0 {
			http.Error(response, "Modèle introuvable", http.StatusNotFound)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: "Modèle mis à jour", Value: 1})
	}
}
