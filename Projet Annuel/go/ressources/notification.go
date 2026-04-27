package ressources

import (
	"database/sql"
	"encoding/json"
	"log"
	"net/http"
	"strconv"
	"strings"

	"projet/structures"
)

func recupererExpediteurSysteme(database *sql.DB) (int, error) {
	var idAdmin int
	err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE role = 'admin' ORDER BY id_utilisateur LIMIT 1").Scan(&idAdmin)
	if err != nil {
		return 0, err
	}
	return idAdmin, nil
}

func creerNotification(database *sql.DB, idDestinataire int, titre string, contenu string) error {
	idExpediteur, err := recupererExpediteurSysteme(database)

	var execErr error
	if err == nil {
		_, execErr = database.Exec(
			"INSERT INTO notification (id_expediteur, id_destinataire, Titre, contenu, date_envoie, lu) VALUES (?, ?, ?, ?, NOW(), 0)",
			idExpediteur,
			idDestinataire,
			titre,
			contenu,
		)
	} else {
		_, execErr = database.Exec(
			"INSERT INTO notification (id_expediteur, id_destinataire, Titre, contenu, date_envoie, lu) VALUES (NULL, ?, ?, ?, NOW(), 0)",
			idDestinataire,
			titre,
			contenu,
		)
	}

	if execErr != nil {
		log.Printf("[notification] insert failed destinataire=%d titre=%q err=%v", idDestinataire, titre, execErr)
		return execErr
	}

	log.Printf("[notification] notification enregistree destinataire=%d titre=%q", idDestinataire, titre)

	if pushErr := EnvoyerNotificationPushOneSignal(database, []int{idDestinataire}, titre, contenu); pushErr != nil {
		log.Printf("[notification] push failed destinataire=%d titre=%q err=%v", idDestinataire, titre, pushErr)
		return pushErr
	}

	log.Printf("[notification] push success destinataire=%d titre=%q", idDestinataire, titre)

	return nil
}

func NotifierTousLesAdmins(database *sql.DB, titre string, contenu string) {
	rows, err := database.Query("SELECT id_utilisateur FROM utilisateur WHERE role = 'admin'")
	if err != nil {
		log.Printf("[notification] liste admins: %v", err)
		return
	}
	defer rows.Close()
	for rows.Next() {
		var id int
		if err := rows.Scan(&id); err != nil {
			continue
		}
		_ = creerNotification(database, id, titre, contenu)
	}
}

func LireTemplate(db *sql.DB, cle string, vars map[string]string) (string, string) {
	var titre, contenu string
	err := db.QueryRow("SELECT titre, contenu FROM modele_notification WHERE cle = ?", cle).Scan(&titre, &contenu)
	if err != nil {
		return cle, ""
	}
	for k, v := range vars {
		titre = strings.ReplaceAll(titre, "{"+k+"}", v)
		contenu = strings.ReplaceAll(contenu, "{"+k+"}", v)
	}
	return titre, contenu
}

func NotificationsUtilisateur(database *sql.DB) http.HandlerFunc {
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

		var idUser int
		err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
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
			WHERE n.id_destinataire = ?
			ORDER BY n.date_envoie DESC, n.id_notification DESC
		`, idUser)
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

func CompteurNotifications(database *sql.DB) http.HandlerFunc {
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

		var idUser int
		err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}

		var count int
		err = database.QueryRow("SELECT COUNT(*) FROM notification WHERE id_destinataire = ? AND IFNULL(lu, 0) = 0", idUser).Scan(&count)
		if err != nil {
			http.Error(response, "Erreur lors du comptage des notifications", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]int{"non_lues": count})
	}
}

func MarquerNotificationLue(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
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

		idNotif, convErr := strconv.Atoi(request.PathValue("id"))
		if convErr != nil || idNotif <= 0 {
			http.Error(response, "Notification invalide", http.StatusBadRequest)
			return
		}

		var idUser int
		err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}

		result, err := database.Exec("UPDATE notification SET lu = 1 WHERE id_notification = ? AND id_destinataire = ?", idNotif, idUser)
		if err != nil {
			http.Error(response, "Erreur lors de la mise à jour de la notification", http.StatusInternalServerError)
			return
		}

		rowsAffected, _ := result.RowsAffected()
		if rowsAffected == 0 {
			http.Error(response, "Notification introuvable", http.StatusNotFound)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: "Notification marquée comme lue", Value: 1})
	}
}

func MarquerToutesNotificationsLues(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
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

		var idUser int
		err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}

		_, err = database.Exec("UPDATE notification SET lu = 1 WHERE id_destinataire = ? AND IFNULL(lu, 0) = 0", idUser)
		if err != nil {
			http.Error(response, "Erreur lors de la mise à jour des notifications", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: "Toutes les notifications ont été marquées comme lues", Value: 1})
	}
}

func SupprimerNotificationUtilisateur(database *sql.DB) http.HandlerFunc {
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

		idNotif, convErr := strconv.Atoi(request.PathValue("id"))
		if convErr != nil || idNotif <= 0 {
			http.Error(response, "Notification invalide", http.StatusBadRequest)
			return
		}

		var idUser int
		err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}

		result, err := database.Exec("DELETE FROM notification WHERE id_notification = ? AND id_destinataire = ?", idNotif, idUser)
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
