package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strings"

	"projet/structures"
)

// isMissingCategoriePrestataireColumn détecte l’absence de la colonne id_prestataire (schéma non migré).
func isMissingCategoriePrestataireColumn(err error) bool {
	if err == nil {
		return false
	}
	s := err.Error()
	return strings.Contains(s, "id_prestataire") &&
		(strings.Contains(s, "Unknown column") || strings.Contains(s, "no such column"))
}

func loadAllCategoriesLegacy(database *sql.DB) ([]structures.Categorie, error) {
	categories := make([]structures.Categorie, 0)
	rows, err := database.Query("SELECT id_categorie, nom FROM categorie")
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	for rows.Next() {
		var c structures.Categorie
		if err := rows.Scan(&c.ID, &c.Nom); err != nil {
			return nil, err
		}
		categories = append(categories, c)
	}
	return categories, rows.Err()
}

func loadAllCategories(database *sql.DB) ([]structures.Categorie, error) {
	categories := make([]structures.Categorie, 0)
	rows, err := database.Query("SELECT id_categorie, nom, id_prestataire FROM categorie")
	if err != nil {
		if isMissingCategoriePrestataireColumn(err) {
			return loadAllCategoriesLegacy(database)
		}
		return nil, err
	}
	defer rows.Close()
	for rows.Next() {
		var c structures.Categorie
		var idP sql.NullInt64
		if err := rows.Scan(&c.ID, &c.Nom, &idP); err != nil {
			return nil, err
		}
		if idP.Valid {
			v := int(idP.Int64)
			c.IdPrestataire = &v
		}
		categories = append(categories, c)
	}
	return categories, rows.Err()
}

func corsCategories(w http.ResponseWriter) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
	w.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
}

func Categories(database *sql.DB) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		corsCategories(w)
		if r.Method == http.MethodOptions {
			w.WriteHeader(http.StatusOK)
			return
		}
		if r.Method != http.MethodGet {
			http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}
		categories, err := loadAllCategories(database)
		if err != nil {
			http.Error(w, "Erreur lors de la selection des categories de la base de donnees", http.StatusInternalServerError)
			return
		}
		w.Header().Set("Content-Type", "application/json; charset=utf-8")
		_ = json.NewEncoder(w).Encode(map[string]any{"categorie": categories})
	}
}

func Prestataire_categories(database *sql.DB) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		corsCategories(w)
		if r.Method == http.MethodOptions {
			w.WriteHeader(http.StatusOK)
			return
		}
		if r.Method != http.MethodGet {
			http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}
		if _, err := prestataireIDFromToken(database, r.Header.Get("Token")); err != nil {
			http.Error(w, "Accès prestataire requis", http.StatusUnauthorized)
			return
		}
		categories, err := loadAllCategories(database)
		if err != nil {
			http.Error(w, "Erreur lors de la selection des categories de la base de donnees", http.StatusInternalServerError)
			return
		}
		w.Header().Set("Content-Type", "application/json; charset=utf-8")
		_ = json.NewEncoder(w).Encode(map[string]any{"categorie": categories})
	}
}
