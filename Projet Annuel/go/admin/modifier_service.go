package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"projet/structures"
	"strconv"
)

func categorieFK(database *sql.DB, idCategorie int) (sql.NullInt64, error) {
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

func Gestion_service_nom(database *sql.DB) http.HandlerFunc {
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

		selectstatement, selecterr := database.Prepare(
			"SELECT s.id_service, s.nom, s.description, s.tarif, IFNULL(s.image, '') AS image, s.id_categorie, c.nom AS categorie_nom " +
				"FROM service s LEFT JOIN categorie c ON c.id_categorie = s.id_categorie WHERE s.nom = ?",
		)
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations du service", http.StatusInternalServerError)
			return
		}
		var serv structures.Service
		var idCat sql.NullInt64
		var catNom sql.NullString
		selectstatement.QueryRow(nom).Scan(&serv.ID, &serv.Nom, &serv.Description, &serv.Tarif, &serv.Image, &idCat, &catNom)
		if idCat.Valid {
			serv.IdCategorie = int(idCat.Int64)
		}
		if catNom.Valid {
			serv.Categorie = catNom.String
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(serv)
	}
}

func Gestion_service_id(database *sql.DB) http.HandlerFunc {
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

		selectstatement, selecterr := database.Prepare(
			"SELECT s.id_service, s.nom, s.description, s.tarif, IFNULL(s.image, '') AS image, s.id_categorie, c.nom AS categorie_nom " +
				"FROM service s LEFT JOIN categorie c ON c.id_categorie = s.id_categorie WHERE s.id_service = ?",
		)
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations du service", http.StatusInternalServerError)
			return
		}
		var serv structures.Service
		var idCat sql.NullInt64
		var catNom sql.NullString
		selectstatement.QueryRow(id).Scan(&serv.ID, &serv.Nom, &serv.Description, &serv.Tarif, &serv.Image, &idCat, &catNom)
		if idCat.Valid {
			serv.IdCategorie = int(idCat.Int64)
		}
		if catNom.Valid {
			serv.Categorie = catNom.String
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(serv)
	}
}

func Modifier_service(database *sql.DB) http.HandlerFunc {
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

		var serv structures.Service
		err := json.NewDecoder(request.Body).Decode(&serv)
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des données du service", http.StatusBadRequest)
			return
		}

		id, err := strconv.Atoi(request.PathValue("id"))
		if err != nil {
			http.Error(response, "ID invalide", http.StatusBadRequest)
			return
		}

		idCatFK, errFK := categorieFK(database, serv.IdCategorie)
		if errFK != nil {
			http.Error(response, "Catégorie invalide", http.StatusBadRequest)
			return
		}

		updatestatement, updateerr := database.Prepare("UPDATE service SET nom = ?, description = ?, tarif = ?, id_categorie = ? WHERE id_service = ?")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requête de mise à jour", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(serv.Nom, serv.Description, serv.Tarif, idCatFK, id)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la mise à jour du service", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Service " + serv.Nom + " mis à jour avec succès",
			Value:   1,
		})
	}
}

func Creer_service(database *sql.DB) http.HandlerFunc {
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

		var service structures.Service
		err := json.NewDecoder(request.Body).Decode(&service)
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des données du service", http.StatusBadRequest)
			return
		}

		verifemail, err := database.Prepare("SELECT nom FROM service WHERE nom = ?")
		if err != nil {
			http.Error(response, "Erreur lors de la vérification de l'existence du nom", http.StatusInternalServerError)
			return
		}
		var existingName string
		err = verifemail.QueryRow(service.Nom).Scan(&existingName)
		if err == nil {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Un autre service a déjà ce nom",
				Value:   0,
			})
			return
		}

		idCatFK, errFK := categorieFK(database, service.IdCategorie)
		if errFK != nil {
			http.Error(response, "Catégorie invalide", http.StatusBadRequest)
			return
		}

		updatestatement, updateerr := database.Prepare("INSERT INTO service (nom, description, tarif, image, id_categorie) VALUES (?, ?, ?, ?, ?)")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requête de creation", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(service.Nom, service.Description, service.Tarif, service.Image, idCatFK)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la creation du service", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusCreated)
		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Service " + service.Nom + " crée avec succès",
			Value:   1,
		})
	}
}

func Supprimer_service(database *sql.DB) http.HandlerFunc {
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

		updatestatement, updateerr := database.Prepare("DELETE FROM service WHERE id_service = ?")
		if updateerr != nil {
			http.Error(response, "Erreur lors de la préparation de la requête de suppression", http.StatusInternalServerError)
			return
		}
		_, updateexecerr := updatestatement.Exec(id)
		if updateexecerr != nil {
			http.Error(response, "Erreur lors de la suppression du service", http.StatusInternalServerError)
			return
		}

		response.WriteHeader(http.StatusNoContent)
	}
}

func List_services(database *sql.DB) http.HandlerFunc {
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

		rows, err := database.Query(
			"SELECT s.id_service, s.nom, s.description, s.tarif, IFNULL(s.image, '') AS image, s.id_categorie, c.nom AS categorie_nom " +
				"FROM service s LEFT JOIN categorie c ON c.id_categorie = s.id_categorie",
		)
		if err != nil {
			http.Error(response, "Erreur lors de la selection des services de la base de données", http.StatusInternalServerError)
			return
		} else {
			var services []structures.Service

			for rows.Next() {
				var s structures.Service
				var id int
				var idCat sql.NullInt64
				var catNom sql.NullString

				err := rows.Scan(&id, &s.Nom, &s.Description, &s.Tarif, &s.Image, &idCat, &catNom)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des services : "+err.Error(), http.StatusInternalServerError)
					return
				}
				if idCat.Valid {
					s.IdCategorie = int(idCat.Int64)
				}
				if catNom.Valid {
					s.Categorie = catNom.String
				}
				var rej string
				var rej2 string
				auth := request.Header.Get("Token")
				userrequest, err := database.Prepare("SELECT rs.id_service FROM reference_service rs JOIN utilisateur u ON u.id_utilisateur = rs.id_utilisateur WHERE u.token = ? AND rs.id_service = ?")
				if err != nil {
					http.Error(response, "Erreur lors des jointures de services", http.StatusInternalServerError)
					return
				}
				otherrequest, err := database.Prepare("SELECT id_service FROM reference_service WHERE id_service = ?")
				if err != nil {
					http.Error(response, "Erreur lors des jointures de services (2)", http.StatusInternalServerError)
					return
				}
				rowsuser := userrequest.QueryRow(auth, id)
				err = rowsuser.Scan(&rej)
				rowsother := otherrequest.QueryRow(id)
				err2 := rowsother.Scan(&rej2)
				if err != nil {
					if err2 != nil {
						s.Rejoindre = "Rejoindre"
					} else {
						s.Rejoindre = "Indisponible"
					}
				} else {
					s.Rejoindre = "Quitter"
				}

				s.ID = id

				services = append(services, s)
			}
			if len(services) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun service pour le moment",
				})
				return
			}

			response.WriteHeader(http.StatusOK)
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Service: services,
			})
		}
	}
}
