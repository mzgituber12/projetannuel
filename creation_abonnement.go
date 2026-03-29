package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"

	_ "github.com/go-sql-driver/mysql"
	_ "modernc.org/sqlite"
)

func Abonnement_admin_creation(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var data struct {
			Categorie            string  `json:"categorie"`
			Name_abonnement      string  `json:"name_abonnement"`
			Prix_mois_abonnement float64 `json:"prix_mois_abonnement"`
			Prix_an_abonnement   float64 `json:"prix_an_abonnement"`
			Ai1                  string  `json:"ai1"`
			Ai2                  string  `json:"ai2"`
			Ai3                  string  `json:"ai3"`
			Ai4                  string  `json:"ai4"`
		}

		err := json.NewDecoder(request.Body).Decode(&data)

		if data.Categorie == "" || data.Name_abonnement == "" || data.Prix_mois_abonnement == 0 || data.Prix_an_abonnement == 0 || data.Ai1 == "" || data.Ai2 == "" || data.Ai3 == "" || data.Ai4 == "" {
			http.Error(response, "Veillez remplir tout les champs pour ajouter un abonnement", http.StatusBadRequest)
			return
		}

		if err != nil {
			http.Error(response, "JSON invalide", http.StatusBadRequest)
			return
		}

		selectstatement, selecrerr := database.Prepare("INSERT INTO abonnement (categorie, type, prix_mois, prix_an, contenue1, contenue2, contenue3, contenue4) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")

		if selecrerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requete", http.StatusInternalServerError)
			return
		}

		_, err = selectstatement.Exec(
			data.Categorie,
			data.Name_abonnement,
			data.Prix_mois_abonnement,
			data.Prix_an_abonnement,
			data.Ai1,
			data.Ai2,
			data.Ai3,
			data.Ai4,
		)

		if err != nil {
			http.Error(response, "Erreur dans la bdd", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusOK)

	}
}
