package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strconv"

	"projet/structures"
)

func Gestion_article_nom(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodGet {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		nom := request.PathValue("nom")

		selectstatement, selecterr := database.Prepare("SELECT id_article, titre, COALESCE(image, '') AS image, description, prix FROM article WHERE titre = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'article", http.StatusInternalServerError)
			return
		}
		var event structures.Article
		selectstatement.QueryRow(nom).Scan(&event.ID, &event.Titre, &event.Image, &event.Description, &event.Prix)

		response.WriteHeader(http.StatusOK)
		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Article{
			ID:          event.ID,
			Titre:       event.Titre,
			Image:       event.Image,
			Description: event.Description,
			Prix:        event.Prix,
		})
	}
}

func Gestion_article_id(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodGet {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		id := request.PathValue("id")

		selectstatement, selecterr := database.Prepare("SELECT id_article, titre, COALESCE(image, '') AS image, description, prix FROM article WHERE id_article = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'article", http.StatusInternalServerError)
			return
		}
		var article structures.Article
		selectstatement.QueryRow(id).Scan(&article.ID, &article.Titre, &article.Image, &article.Description, &article.Prix)

		response.WriteHeader(http.StatusOK)
		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Article{
			ID:          article.ID,
			Titre:       article.Titre,
			Image:       article.Image,
			Description: article.Description,
			Prix:        article.Prix,
		})
	}
}

func Modifier_article(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodPatch {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		var article structures.Article
		err := json.NewDecoder(request.Body).Decode(&article)
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des données de l'article", http.StatusBadRequest)
			return
		}

		id, err := strconv.Atoi(request.PathValue("id"))
		if err != nil {
			http.Error(response, "ID invalide", http.StatusBadRequest)
			return
		}

		verifemail, err := database.Prepare("SELECT titre FROM article WHERE titre = ? AND id_article != ?")
		if err != nil {
			http.Error(response, "Erreur lors de la vérification de l'existence du titre", http.StatusInternalServerError)
			return
		}
		var existingName string
		err = verifemail.QueryRow(article.Titre, id).Scan(&existingName)
		if err == nil {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Un autre article a déjà ce nom",
				Value:   0,
			})
			return
		}

		updatestatement, updateerr := database.Prepare("UPDATE article SET titre = ?, description = ?, prix = ? WHERE id_article = ?")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requête de mise à jour", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(article.Titre, article.Description, article.Prix, id)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la mise à jour de l'article", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusOK)
		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Article " + article.Titre + " mis à jour avec succès",
			Value:   1,
		})
	}
}

func Creer_article(database *sql.DB) http.HandlerFunc {
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

		var article structures.Article
		err := json.NewDecoder(request.Body).Decode(&article)
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des données de l'article", http.StatusBadRequest)
			return
		}

		verifemail, err := database.Prepare("SELECT titre FROM article WHERE titre = ?")
		if err != nil {
			http.Error(response, "Erreur lors de la vérification de l'existence du titre", http.StatusInternalServerError)
			return
		}
		var existingName string
		err = verifemail.QueryRow(article.Titre).Scan(&existingName)
		if err == nil {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Un autre article a déjà ce nom",
				Value:   0,
			})
			return
		}

		updatestatement, updateerr := database.Prepare("INSERT INTO article (titre, image, description, prix) VALUES (?, ?, ?, ?)")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requête de creation", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(article.Titre, article.Image, article.Description, article.Prix)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la creation de l'article", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusCreated)
		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Article " + article.Titre + " crée avec succès",
			Value:   1,
		})
	}
}

func Supprimer_article(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type")
		response.Header().Set("Access-Control-Allow-Methods", "DELETE, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodDelete {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		id, err := strconv.Atoi(request.PathValue("id"))
		if err != nil {
			http.Error(response, "ID invalide", http.StatusBadRequest)
			return
		}

		updatestatement, updateerr := database.Prepare("DELETE FROM article WHERE id_article = ?")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requête de suppression", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(id)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la suppression de l'article", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusNoContent)
	}
}

func List_articles(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodGet {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		rows, err := database.Query("SELECT id_article, titre, COALESCE(image, '') AS image, description, prix FROM article")
		if err != nil {
			http.Error(response, "Erreur lors de la selection des articles de la base de données", http.StatusInternalServerError)
			return
		} else {
			var articles []structures.Article

			for rows.Next() {
				var a structures.Article

				err := rows.Scan(&a.ID, &a.Titre, &a.Image, &a.Description, &a.Prix)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des articles : "+err.Error(), http.StatusInternalServerError)
					return
				}

				articles = append(articles, a)
			}
			if len(articles) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun article pour le moment",
				})
				return
			}

			response.WriteHeader(http.StatusOK)
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Article: articles,
			})
		}
	}
}
