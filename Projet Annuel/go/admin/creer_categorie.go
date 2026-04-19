package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strings"

	"projet/structures"
)

func Creer_categorie(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodPost {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		var body struct {
			Nom string `json:"nom"`
		}
		if err := json.NewDecoder(request.Body).Decode(&body); err != nil {
			http.Error(response, "Erreur lors de la lecture des données", http.StatusBadRequest)
			return
		}

		nom := strings.TrimSpace(body.Nom)
		if nom == "" {
			http.Error(response, "Le nom de la catégorie est requis", http.StatusBadRequest)
			return
		}

		res, err := database.Exec("INSERT INTO categorie (nom) VALUES (?)", nom)
		if err != nil {
			http.Error(response, "Erreur lors de la création de la catégorie (nom peut-être déjà utilisé)", http.StatusInternalServerError)
			return
		}
		id, err := res.LastInsertId()
		if err != nil {
			http.Error(response, "Erreur lors de la récupération de l'identifiant", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		response.WriteHeader(http.StatusCreated)
		json.NewEncoder(response).Encode(structures.Categorie{
			ID:  int(id),
			Nom: nom,
		})
	}
}
