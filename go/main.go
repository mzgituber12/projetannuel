package main

import (
	"database/sql"
	"fmt"
	"log"
	"net/http"

	"projet/admin"
	"projet/authentification"
	"projet/ressources"

	_ "github.com/go-sql-driver/mysql"
	_ "modernc.org/sqlite"
)

func main() {
	db, err := sql.Open("mysql", "root:root@tcp(mariadb:3306)/projet")
	if err != nil {
		log.Fatal("Erreur d'ouverture de la base de données :", err)
	}

	http.HandleFunc("/inscription", authentification.Inscription(db))
	http.HandleFunc("/connexion", authentification.Connexion(db))
	http.HandleFunc("/deconnexion", authentification.Deconnexion(db))
	http.HandleFunc("/enligne", authentification.Enligne(db))

	http.HandleFunc("/nous_contacter", nous_contacter(db))

	http.HandleFunc("/contrats", ressources.Contrats(db))
	http.HandleFunc("/conseils", ressources.Conseils(db))
	http.HandleFunc("/evenements", ressources.Evenements(db))
	http.HandleFunc("/evenements/{id}", ressources.Evenements_patch(db))
	http.HandleFunc("/services", ressources.Services(db))
	http.HandleFunc("/services/{id}", ressources.Services_patch(db))
	http.HandleFunc("/articles", ressources.Articles(db))
	http.HandleFunc("/planning_evenements", ressources.Planning_evenements(db))
	http.HandleFunc("/planning_services", ressources.Planning_services(db))

	http.HandleFunc("/admin", admin.Estadmin(db))
	http.HandleFunc("/gestion_user_email/{email}", admin.Gestion_user_email(db))
	http.HandleFunc("/gestion_user_id/{id}", admin.Gestion_user_id(db))
	http.HandleFunc("/modifier_user/{id}", admin.Modifier_user(db))
	http.HandleFunc("/gestion_evenement_nom/{nom}", admin.Gestion_evenement_nom(db))
	http.HandleFunc("/gestion_evenement_id/{id}", admin.Gestion_evenement_id(db))
	http.HandleFunc("/modifier_evenement/{id}", admin.Modifier_evenement(db))

	fmt.Println("Ouverture du serveur sur le port 9000...")
	listenError := http.ListenAndServe(":9000", nil)
	if listenError != nil {
		log.Fatal("Impossible d'ouvrir le serveur sur le port 9000 :", listenError)
	}
}
