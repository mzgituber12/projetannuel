package main

import (
	"database/sql"
	"fmt"
	"log"
	"net/http"

	"projet/admin"
	"projet/authentification"
	"projet/ressources"

	_ "time/tzdata"

	_ "github.com/go-sql-driver/mysql"
	_ "modernc.org/sqlite"
)

func main() {
	db, err := sql.Open("mysql", "root:root@tcp(mariadb:3306)/projet?charset=utf8mb4&collation=utf8mb4_unicode_ci&parseTime=true&loc=Local")
	if err != nil {
		log.Fatal("Erreur d'ouverture de la base de données :", err)
	}

	if _, err := db.Exec("SET NAMES utf8mb4"); err != nil {
		log.Fatal("Erreur configuration charset utf8mb4 :", err)
	}

	http.HandleFunc("/inscription", authentification.Inscription(db))
	http.HandleFunc("/connexion", authentification.Connexion(db))
	http.HandleFunc("/deconnexion", authentification.Deconnexion(db))
	http.HandleFunc("/enligne", authentification.Enligne(db))

	http.HandleFunc("/mon_profil", mon_profil(db))
	http.HandleFunc("/update_profil", update_profil(db))
	http.HandleFunc("/abonnement", ressources.ListAbonnements(db))

	http.HandleFunc("/subscribe", ressources.SouscrireAbonnement(db))
	http.HandleFunc("/mon-abonnement", ressources.MonAbonnement(db))
	http.HandleFunc("/cancel-subscription", ressources.CancelAbonnement(db))
	http.HandleFunc("/webhook-subscription", ressources.WebhookAbonnement(db))
	http.HandleFunc("/abonnement/notif-push-bienvenue", ressources.NotifPushBienvenueAbonnement(db))

	http.HandleFunc("/nous_contacter", nous_contacter(db))
	http.HandleFunc("/demande_presta", demande_presta(db))

	http.HandleFunc("/load-messages", ressources.LoadMessages(db))
	http.HandleFunc("/load-contacts", ressources.LoadContacts(db))
	http.HandleFunc("/send-message", ressources.SendMessage(db))

	http.HandleFunc("/contrats", ressources.Contrats(db))
	http.HandleFunc("/conseils", ressources.Conseils(db))
	http.HandleFunc("/conseils/{id}", ressources.Conseil_id(db))
	http.HandleFunc("/evenements", ressources.Evenements(db))
	http.HandleFunc("/evenements/{id}", ressources.Evenements_patch(db))
	http.HandleFunc("/services", ressources.Services(db))
	http.HandleFunc("/services/{id}", ressources.Services_patch(db))
	http.HandleFunc("/categories", ressources.Categories(db))
	http.HandleFunc("/prestataires", ressources.Prestataires(db))
	http.HandleFunc("/articles", ressources.Articles(db))
	http.HandleFunc("/articles/{id}", ressources.Article_id(db))
	http.HandleFunc("/panier_article", ressources.AjouterArticlePanier(db))
	http.HandleFunc("/panier_article/{id}", ressources.EtatArticlePanier(db))
	http.HandleFunc("/panier_article_toggle", ressources.BasculerArticlePanier(db))
	http.HandleFunc("/panier_articles", ressources.ArticlesPanier(db))
	http.HandleFunc("/create-order", ressources.CreerCommande(db))
	http.HandleFunc("/create-checkout-session", ressources.CreerSessionPaiement(db))
	http.HandleFunc("/webhook", ressources.WebhookPaiement(db))
	http.HandleFunc("/invoice/{id}", ressources.FactureAchat(db))
	http.HandleFunc("/planning_evenements", ressources.Planning_evenements(db))
	http.HandleFunc("/planning_services", ressources.Planning_services(db))
	http.HandleFunc("/planning_rdv", ressources.Planning_rdv(db))
	http.HandleFunc("/reservation_evenement", ressources.Reservation_evenement(db))
	http.HandleFunc("/service_disponible", ressources.Service_disponible(db))
	http.HandleFunc("/reservation_service", ressources.Reservation_service(db))
	http.HandleFunc("/creer_devis", ressources.CreerDevis(db))
	http.HandleFunc("/mes_devis", ressources.MesDevis(db))
	http.HandleFunc("/devis/{id}", ressources.DevisDetail(db))
	http.HandleFunc("/devis/{id}/statut", ressources.PatchDevis(db))
	http.HandleFunc("/notifications", ressources.NotificationsUtilisateur(db))
	http.HandleFunc("/notifications/unread-count", ressources.CompteurNotifications(db))
	http.HandleFunc("/notifications/read-all", ressources.MarquerToutesNotificationsLues(db))
	http.HandleFunc("/notifications/{id}/read", ressources.MarquerNotificationLue(db))
	http.HandleFunc("/notifications/{id}", ressources.SupprimerNotificationUtilisateur(db))
	http.HandleFunc("/push/subscription", ressources.EnregistrerAbonnementPush(db))

	http.HandleFunc("/admin", admin.Estadmin(db))

	http.HandleFunc("/list_users", admin.List_users(db))
	http.HandleFunc("/gestion_user_email/{email}", admin.Gestion_user_email(db))
	http.HandleFunc("/gestion_user_id/{id}", admin.Gestion_user_id(db))
	http.HandleFunc("/modifier_user/{id}", admin.Modifier_user(db))
	http.HandleFunc("/supprimer_user/{id}", admin.Supprimer_user(db))

	http.HandleFunc("/list_evenements", admin.List_evenements(db))
	http.HandleFunc("/creer_evenement", admin.Creer_evenement(db))
	http.HandleFunc("/gestion_evenement_nom/{nom}", admin.Gestion_evenement_nom(db))
	http.HandleFunc("/gestion_evenement_id/{id}", admin.Gestion_evenement_id(db))
	http.HandleFunc("/modifier_evenement/{id}", admin.Modifier_evenement(db))
	http.HandleFunc("/supprimer_evenement/{id}", admin.Supprimer_evenement(db))

	http.HandleFunc("/list_services", admin.List_services(db))
	http.HandleFunc("/creer_service", admin.Creer_service(db))
	http.HandleFunc("/gestion_service/{nom}", admin.Gestion_service_nom(db))
	http.HandleFunc("/gestion_service_id/{id}", admin.Gestion_service_id(db))
	http.HandleFunc("/modifier_service/{id}", admin.Modifier_service(db))
	http.HandleFunc("/supprimer_service/{id}", admin.Supprimer_service(db))

	http.HandleFunc("/list_articles", admin.List_articles(db))
	http.HandleFunc("/creer_article", admin.Creer_article(db))
	http.HandleFunc("/gestion_article/{nom}", admin.Gestion_article_nom(db))
	http.HandleFunc("/gestion_article_id/{id}", admin.Gestion_article_id(db))
	http.HandleFunc("/modifier_article/{id}", admin.Modifier_article(db))
	http.HandleFunc("/supprimer_article/{id}", admin.Supprimer_article(db))

	http.HandleFunc("/gestion_intervention/{id}", admin.Gestion_intervention_id(db))
	http.HandleFunc("/modifier_intervention/{id}", admin.Modifier_intervention(db))

	http.HandleFunc("/gestion_contact", admin.Gestion_contact(db))
	http.HandleFunc("/add_abonnement", admin.Abonnement_admin_creation(db))
	http.HandleFunc("/abonnement_all", liste_abonnement_all(db))
	http.HandleFunc("/gestion_notifications", admin.Gestion_notifications(db))
	http.HandleFunc("/creer_notification", admin.Creer_notification(db))
	http.HandleFunc("/modifier_notification/{id}", admin.Modifier_notification(db))
	http.HandleFunc("/supprimer_notification/{id}", admin.Supprimer_notification(db))
	http.HandleFunc("/modeles_notifications", admin.Gestion_modeles(db))
	http.HandleFunc("/modifier_modele/{id}", admin.Modifier_modele(db))

	http.HandleFunc("/gestion_conseils", admin.Gestion_conseils(db))
	http.HandleFunc("/gestion_conseil/{titre}", admin.Gestion_conseil_nom(db))
	http.HandleFunc("/gestion_conseil_id/{id}", admin.Gestion_conseil_id(db))
	http.HandleFunc("/creer_conseil", admin.Creer_conseil(db))
	http.HandleFunc("/modifier_conseil/{id}", admin.Modifier_conseil(db))
	http.HandleFunc("/supprimer_conseil/{id}", admin.Supprimer_conseil(db))

	fmt.Println("Ouverture du serveur sur le port 9000...")
	listenError := http.ListenAndServe(":9000", nil)
	if listenError != nil {
		log.Fatal("Impossible d'ouvrir le serveur sur le port 9000 :", listenError)
	}
}
