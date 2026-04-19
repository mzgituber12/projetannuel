package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"projet/structures"
	"strconv"
	"strings"
)

func prestataireCategorieFK(database *sql.DB, idCategorie int) (sql.NullInt64, error) {
	if idCategorie <= 0 {
		return sql.NullInt64{}, nil
	}
	var check int
	err := database.QueryRow("SELECT id_categorie FROM categorie WHERE id_categorie = ?", idCategorie).Scan(&check)
	if err != nil {
		if err == sql.ErrNoRows {
			return sql.NullInt64{}, err
		}
		return sql.NullInt64{}, err
	}
	return sql.NullInt64{Int64: int64(idCategorie), Valid: true}, nil
}

func corsPrestataireService(w http.ResponseWriter) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
	w.Header().Set("Access-Control-Allow-Methods", "GET, POST, PATCH, DELETE, OPTIONS")
}


func Prestataire_mes_services_collection(database *sql.DB) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		corsPrestataireService(w)
		if r.Method == http.MethodOptions {
			w.WriteHeader(http.StatusOK)
			return
		}
		idPresta, err := prestataireIDFromToken(database, r.Header.Get("Token"))
		if err != nil {
			http.Error(w, "Accès prestataire requis", http.StatusUnauthorized)
			return
		}
		switch r.Method {
		case http.MethodGet:
			rows, err := database.Query(
				"SELECT s.id_service, s.nom, s.description, s.tarif, IFNULL(s.image, '') AS image, s.id_categorie, c.nom AS categorie_nom "+
					"FROM service s LEFT JOIN categorie c ON c.id_categorie = s.id_categorie WHERE s.id_prestataire = ? ORDER BY s.id_service DESC",
				idPresta,
			)
			if err != nil {
				http.Error(w, "Erreur lors de la lecture des services", http.StatusInternalServerError)
				return
			}
			defer rows.Close()
			services := make([]structures.Service, 0)
			for rows.Next() {
				var s structures.Service
				var id int
				var idCat sql.NullInt64
				var catNom sql.NullString
				if err := rows.Scan(&id, &s.Nom, &s.Description, &s.Tarif, &s.Image, &idCat, &catNom); err != nil {
					http.Error(w, "Erreur lors du scan des services", http.StatusInternalServerError)
					return
				}
				s.ID = id
				if idCat.Valid {
					s.IdCategorie = int(idCat.Int64)
				}
				if catNom.Valid {
					s.Categorie = catNom.String
				}
				services = append(services, s)
			}
			w.Header().Set("Content-Type", "application/json")
			json.NewEncoder(w).Encode(structures.List{Service: services})

		case http.MethodPost:
			var service structures.Service
			if err := json.NewDecoder(r.Body).Decode(&service); err != nil {
				http.Error(w, "JSON invalide", http.StatusBadRequest)
				return
			}
			service.Nom = strings.TrimSpace(service.Nom)
			if service.Nom == "" {
				http.Error(w, "Le nom du service est requis", http.StatusBadRequest)
				return
			}
			var dup int
			err = database.QueryRow("SELECT id_service FROM service WHERE nom = ? AND id_prestataire = ?", service.Nom, idPresta).Scan(&dup)
			if err == nil {
				w.Header().Set("Content-Type", "application/json")
				w.WriteHeader(http.StatusConflict)
				json.NewEncoder(w).Encode(structures.Result{Message: "Vous avez déjà un service avec ce nom", Value: 0})
				return
			}
			if err != sql.ErrNoRows {
				http.Error(w, "Erreur lors de la vérification du nom", http.StatusInternalServerError)
				return
			}
			idCatFK, errFK := prestataireCategorieFK(database, service.IdCategorie)
			if errFK != nil {
				http.Error(w, "Catégorie invalide", http.StatusBadRequest)
				return
			}
			res, err := database.Exec(
				"INSERT INTO service (nom, description, tarif, image, id_categorie, id_prestataire) VALUES (?, ?, ?, ?, ?, ?)",
				service.Nom, service.Description, service.Tarif, service.Image, idCatFK, idPresta,
			)
			if err != nil {
				http.Error(w, "Erreur lors de la création du service", http.StatusInternalServerError)
				return
			}
			_, _ = res.LastInsertId()
			w.WriteHeader(http.StatusCreated)
			w.Header().Set("Content-Type", "application/json")
			json.NewEncoder(w).Encode(structures.Result{Message: "Service créé avec succès", Value: 1})

		default:
			http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		}
	}
}


func Prestataire_mes_services_item(database *sql.DB) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		corsPrestataireService(w)
		if r.Method == http.MethodOptions {
			w.WriteHeader(http.StatusOK)
			return
		}
		idPresta, err := prestataireIDFromToken(database, r.Header.Get("Token"))
		if err != nil {
			http.Error(w, "Accès prestataire requis", http.StatusUnauthorized)
			return
		}
		id, err := strconv.Atoi(r.PathValue("id"))
		if err != nil {
			http.Error(w, "ID invalide", http.StatusBadRequest)
			return
		}
		switch r.Method {
		case http.MethodGet:
			var s structures.Service
			var idCat sql.NullInt64
			var catNom sql.NullString
			err = database.QueryRow(
				"SELECT s.id_service, s.nom, s.description, s.tarif, IFNULL(s.image, '') AS image, s.id_categorie, c.nom AS categorie_nom "+
					"FROM service s LEFT JOIN categorie c ON c.id_categorie = s.id_categorie WHERE s.id_service = ? AND s.id_prestataire = ?",
				id, idPresta,
			).Scan(&s.ID, &s.Nom, &s.Description, &s.Tarif, &s.Image, &idCat, &catNom)
			if err == sql.ErrNoRows {
				http.Error(w, "Service introuvable", http.StatusNotFound)
				return
			}
			if err != nil {
				http.Error(w, "Erreur lors de la lecture du service", http.StatusInternalServerError)
				return
			}
			if idCat.Valid {
				s.IdCategorie = int(idCat.Int64)
			}
			if catNom.Valid {
				s.Categorie = catNom.String
			}
			w.Header().Set("Content-Type", "application/json")
			json.NewEncoder(w).Encode(s)

		case http.MethodPatch:
			var serv structures.Service
			if err := json.NewDecoder(r.Body).Decode(&serv); err != nil {
				http.Error(w, "JSON invalide", http.StatusBadRequest)
				return
			}
			serv.Nom = strings.TrimSpace(serv.Nom)
			if serv.Nom == "" {
				http.Error(w, "Le nom du service est requis", http.StatusBadRequest)
				return
			}
			var autre int
			err = database.QueryRow(
				"SELECT id_service FROM service WHERE nom = ? AND id_prestataire = ? AND id_service <> ?",
				serv.Nom, idPresta, id,
			).Scan(&autre)
			if err == nil {
				w.Header().Set("Content-Type", "application/json")
				w.WriteHeader(http.StatusConflict)
				json.NewEncoder(w).Encode(structures.Result{Message: "Vous avez déjà un autre service avec ce nom", Value: 0})
				return
			}
			if err != sql.ErrNoRows {
				http.Error(w, "Erreur lors de la vérification du nom", http.StatusInternalServerError)
				return
			}
			idCatFK, errFK := prestataireCategorieFK(database, serv.IdCategorie)
			if errFK != nil {
				http.Error(w, "Catégorie invalide", http.StatusBadRequest)
				return
			}
			res, err := database.Exec(
				"UPDATE service SET nom = ?, description = ?, tarif = ?, image = ?, id_categorie = ? WHERE id_service = ? AND id_prestataire = ?",
				serv.Nom, serv.Description, serv.Tarif, serv.Image, idCatFK, id, idPresta,
			)
			if err != nil {
				http.Error(w, "Erreur lors de la mise à jour", http.StatusInternalServerError)
				return
			}
			n, _ := res.RowsAffected()
			if n == 0 {
				http.Error(w, "Service introuvable", http.StatusNotFound)
				return
			}
			w.Header().Set("Content-Type", "application/json")
			json.NewEncoder(w).Encode(structures.Result{Message: "Service mis à jour", Value: 1})

		case http.MethodDelete:
			res, err := database.Exec("DELETE FROM service WHERE id_service = ? AND id_prestataire = ?", id, idPresta)
			if err != nil {
				http.Error(w, "Erreur lors de la suppression", http.StatusInternalServerError)
				return
			}
			n, _ := res.RowsAffected()
			if n == 0 {
				http.Error(w, "Service introuvable", http.StatusNotFound)
				return
			}
			w.WriteHeader(http.StatusNoContent)

		default:
			http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		}
	}
}

func Creer_categorie_prestataire(database *sql.DB) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		corsPrestataireService(w)
		if r.Method == http.MethodOptions {
			w.WriteHeader(http.StatusOK)
			return
		}
		if r.Method != http.MethodPost {
			http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}
		idPresta, err := prestataireIDFromToken(database, r.Header.Get("Token"))
		if err != nil {
			http.Error(w, "Accès prestataire requis", http.StatusUnauthorized)
			return
		}
		var body struct {
			Nom string `json:"nom"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			http.Error(w, "JSON invalide", http.StatusBadRequest)
			return
		}
		nom := strings.TrimSpace(body.Nom)
		if nom == "" {
			http.Error(w, "Le nom de la catégorie est requis", http.StatusBadRequest)
			return
		}
		res, err := database.Exec("INSERT INTO categorie (nom, id_prestataire) VALUES (?, ?)", nom, idPresta)
		var avecProprietaire bool
		if err != nil && isMissingCategoriePrestataireColumn(err) {
			res, err = database.Exec("INSERT INTO categorie (nom) VALUES (?)", nom)
			avecProprietaire = false
		} else if err == nil {
			avecProprietaire = true
		}
		if err != nil {
			http.Error(w, "Erreur lors de la création (nom peut-être déjà utilisé)", http.StatusInternalServerError)
			return
		}
		newID, err := res.LastInsertId()
		if err != nil {
			http.Error(w, "Erreur lors de la récupération de l'identifiant", http.StatusInternalServerError)
			return
		}
		out := structures.Categorie{ID: int(newID), Nom: nom}
		if avecProprietaire {
			idPtr := idPresta
			out.IdPrestataire = &idPtr
		}
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusCreated)
		json.NewEncoder(w).Encode(out)
	}
}
