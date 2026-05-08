package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strconv"

	"projet/structures"
)

func List_categories(database *sql.DB) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Access-Control-Allow-Origin", "*")
		w.Header().Set("Access-Control-Allow-Headers", "Token")
		w.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if r.Method == http.MethodOptions {
			w.WriteHeader(http.StatusOK)
			return
		}
		if r.Method != http.MethodGet {
			http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}
		
		rows, err := database.Query("SELECT id_categorie, nom, COALESCE(valide_admin, 0) FROM categorie ORDER BY nom")
		if err != nil {
			http.Error(w, "Erreur lecture catégories", http.StatusInternalServerError)
			return
		}
		defer rows.Close()
		list := make([]structures.Categorie, 0)
		for rows.Next() {
			var c structures.Categorie
			if err := rows.Scan(&c.ID, &c.Nom, &c.ValideAdmin); err != nil {
				http.Error(w, "Erreur lecture catégories", http.StatusInternalServerError)
				return
			}
			list = append(list, c)
		}
		w.Header().Set("Content-Type", "application/json; charset=utf-8")
		_ = json.NewEncoder(w).Encode(map[string]any{"categorie": list})
	}
}

func Modifier_categorie(database *sql.DB) http.HandlerFunc {
	type payload struct {
		ValideAdmin int `json:"valide_admin"`
	}
	return func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Access-Control-Allow-Origin", "*")
		w.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		w.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if r.Method == http.MethodOptions {
			w.WriteHeader(http.StatusOK)
			return
		}
		if r.Method != http.MethodPatch {
			http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}
		id, err := strconv.Atoi(r.PathValue("id"))
		if err != nil || id <= 0 {
			http.Error(w, "ID invalide", http.StatusBadRequest)
			return
		}
		var body payload
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			http.Error(w, "JSON invalide", http.StatusBadRequest)
			return
		}
		v := 0
		if body.ValideAdmin != 0 {
			v = 1
		}
		if _, err := database.Exec("UPDATE categorie SET valide_admin = ? WHERE id_categorie = ?", v, id); err != nil {
			http.Error(w, "Erreur mise à jour", http.StatusInternalServerError)
			return
		}
		w.Header().Set("Content-Type", "application/json; charset=utf-8")
		_ = json.NewEncoder(w).Encode(structures.Result{Message: "Catégorie mise à jour", Value: 1})
	}
}
