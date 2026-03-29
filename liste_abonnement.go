package main

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"projet/structures"

	_ "github.com/go-sql-driver/mysql"
	_ "modernc.org/sqlite"
)

func liste_abonnement_all(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var abonnements []structures.Abonnement

		selectstatement, selecterr := database.Query("SELECT id_abonnement, type, prix_mois, prix_an, contenue1, contenue2, contenue3, contenue4 FROM abonnement")

		if selecterr != nil {
			http.Error(response, "Erreur bdd", http.StatusInternalServerError)
			return
		}

		for selectstatement.Next() {
			var a structures.Abonnement
			err := selectstatement.Scan(&a.Id, &a.Titre, &a.Prix_mois, &a.Prix_an, &a.Contenue1, &a.Contenue2, &a.Contenue3, &a.Contenue4)
			if err != nil {
				http.Error(response, err.Error(), http.StatusInternalServerError)
				return
			}
			abonnements = append(abonnements, a)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(abonnements)
	}
}
