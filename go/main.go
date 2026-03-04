package main

import (
	"database/sql"
	"fmt"
	"log"
	"net/http"

	_ "github.com/go-sql-driver/mysql"
	_ "modernc.org/sqlite"
)

func main() {
	db, err := sql.Open("mysql", "root:root@tcp(mariadb:3306)/projet")
	if err != nil {
		log.Fatal("Erreur d'ouverture de la base de données :", err)
	}

	http.HandleFunc("/inscription", inscription(db))
	http.HandleFunc("/connexion", connexion(db))
	http.HandleFunc("/deconnexion", deconnexion(db))
	http.HandleFunc("/enligne", enligne(db))

	//http.HandleFunc("/nous_contacter", nous_contacter(db))

	http.HandleFunc("/contrats", contrats(db))
	http.HandleFunc("/conseils", conseils(db))

	http.HandleFunc("/evenements", evenements(db))
	http.HandleFunc("/evenements/{id}", evenements_patch(db))
	http.HandleFunc("/services", services(db))
	http.HandleFunc("/services/{id}", services_patch(db))
	http.HandleFunc("/articles", articles(db))

	http.HandleFunc("/planning_evenements", planning_evenements(db))
	http.HandleFunc("/planning_services", planning_services(db))

	http.HandleFunc("/gestion_user_email/{email}", gestion_user_email(db))
	http.HandleFunc("/gestion_user_id/{id}", gestion_user_id(db))

	fmt.Println("Ouverture du serveur sur le port 9000...")
	listenError := http.ListenAndServe(":9000", nil)
	if listenError != nil {
		log.Fatal("Impossible d'ouvrir le serveur sur le port 9000 :", listenError)
	}
}
