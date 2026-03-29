package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"projet/structures"

	_ "github.com/go-sql-driver/mysql"
	_ "modernc.org/sqlite"
)

func Modifier_abonnement(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		id := request.URL.Query().Get("id")
		var data structures.Abonnement

		selectstatement, selecterr := database.Prepare("SELECT categorie, type, prix_mois, prix_an, contenue1, contenue2, contenue3, contenue4 FROM abonnement WHERE id_abonnement = ?")

		if selecterr != nil {
			http.Error(response, "Erreur bdd", http.StatusInternalServerError)
			return
		}

		err := selectstatement.QueryRow(id).Scan(&data.Categorie, &data.Titre, &data.Prix_mois, &data.Prix_an, &data.Contenue1, &data.Contenue2, &data.Contenue3, &data.Contenue4)

		if err != nil {
			http.Error(response, "Erreur recuperation données", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(data)
	}
}

func Update_abonnement(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		id := request.URL.Query().Get("id")

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

		if err != nil {
			http.Error(response, "JSON invalide", http.StatusBadRequest)
			return
		}

		if data.Categorie == "" || data.Name_abonnement == "" || data.Prix_mois_abonnement == 0 || data.Prix_an_abonnement == 0 || data.Ai1 == "" || data.Ai2 == "" || data.Ai3 == "" || data.Ai4 == "" {
			http.Error(response, "Veillez remplir tout les champs pour ajouter un abonnement", http.StatusBadRequest)
			return
		}

		selectstm, err := database.Prepare("UPDATE abonnement set categorie = ?, type = ?, prix_mois = ?, prix_an = ?, contenue1 = ?, contenue2 = ?, contenue3 = ?, contenue4 = ? WHERE id_abonnement = ?")

		if err != nil {
			http.Error(response, "Erreur lors de la préparation de la requete", http.StatusInternalServerError)
			return
		}

		_, err = selectstm.Exec(
			data.Categorie,
			data.Name_abonnement,
			data.Prix_mois_abonnement,
			data.Prix_an_abonnement,
			data.Ai1,
			data.Ai2,
			data.Ai3,
			data.Ai4,
			id)

		if err != nil {
			http.Error(response, "Erreur lors de l'update de la bdd", http.StatusInternalServerError)
		}

		response.WriteHeader(http.StatusOK)

	}
}
