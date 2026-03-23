package ressources

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"
	"strconv"
	"strings"
	"time"

	"projet/structures"
)

func Services(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		searchText := strings.TrimSpace(request.URL.Query().Get("q"))
		categoryFilter := strings.TrimSpace(request.URL.Query().Get("categorie"))
		prestataireFilter := strings.TrimSpace(request.URL.Query().Get("prestataire"))
		minTarif := strings.TrimSpace(request.URL.Query().Get("min_tarif"))
		maxTarif := strings.TrimSpace(request.URL.Query().Get("max_tarif"))

		query := "SELECT " +
			"s.id_service, " +
			"s.nom, " +
			"s.description, " +
			"s.tarif, " +
			"IFNULL(s.image, '') AS image, " +
			"s.id_categorie, " +
			"c.nom AS categorie_nom, " +
			"CONCAT(u.prenom, ' ', u.nom) AS prestataire_nom " +
			"FROM service s " +
			"LEFT JOIN categorie c ON c.id_categorie = s.id_categorie " +
			"LEFT JOIN prestataire p ON p.id_prestataire = s.id_prestataire " +
			"LEFT JOIN utilisateur u ON u.id_utilisateur = p.id_utilisateur " +
			"WHERE 1 = 1"

		args := make([]any, 0)

		if searchText != "" {
			query += " AND (s.nom LIKE ? OR s.description LIKE ? OR c.nom LIKE ?)"
			like := "%" + searchText + "%"
			args = append(args, like, like, like)
		}

		if categoryFilter != "" {
			query += " AND (c.nom = ? OR s.id_categorie = ?)"
			args = append(args, categoryFilter, categoryFilter)
		}

		if prestataireFilter != "" {
			query += " AND CONCAT(u.prenom, ' ', u.nom) LIKE ?"
			args = append(args, "%"+prestataireFilter+"%")
		}

		if minTarif != "" {
			query += " AND s.tarif >= ?"
			args = append(args, minTarif)
		}

		if maxTarif != "" {
			query += " AND s.tarif <= ?"
			args = append(args, maxTarif)
		}

		query += " ORDER BY s.nom"

		rows, err := database.Query(query, args...)
		if err != nil {
			http.Error(response, "Erreur lors de la selection des services de la base de données", http.StatusInternalServerError)
			return
		} else {
			defer rows.Close()
			var services []structures.Service

			for rows.Next() {
				var s structures.Service
				var id int
				var idCategorie sql.NullInt64
				var categorieNom sql.NullString
				var prestataireNom sql.NullString

				err := rows.Scan(&id, &s.Nom, &s.Description, &s.Tarif, &s.Image, &idCategorie, &categorieNom, &prestataireNom)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des services : "+err.Error(), http.StatusInternalServerError)
					return
				}

				if idCategorie.Valid {
					s.IdCategorie = int(idCategorie.Int64)
				} else {
					s.IdCategorie = 0
				}

				if categorieNom.Valid {
					s.Categorie = categorieNom.String
				} else {
					s.Categorie = ""
				}

				if prestataireNom.Valid {
					s.Prestataire = prestataireNom.String
				} else {
					s.Prestataire = ""
				}
				var rej int
				auth := request.Header.Get("Token")
				userrequest, err := database.Prepare("SELECT rs.id_service FROM reference_service rs JOIN utilisateur u ON u.id_utilisateur = rs.id_utilisateur WHERE u.token = ? AND rs.id_service = ?")
				if err != nil {
					http.Error(response, "Erreur lors des jointures des services: "+err.Error(), http.StatusInternalServerError)
					return
				}
				defer userrequest.Close()

				rowsuser := userrequest.QueryRow(auth, id)
				err = rowsuser.Scan(&rej)
				if err != nil {
					if err == sql.ErrNoRows {
						s.Rejoindre = "Rejoindre"
					} else {
						http.Error(response, "Erreur lors de la vérification du service : "+err.Error(), http.StatusInternalServerError)
						return
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
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Service: services,
			})
		}
	}
}

func Services_patch(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		id, conversionError := strconv.Atoi(request.PathValue("id"))

		if conversionError != nil {
			http.Error(response, "Erreur lors de la récupération de l'identifiant du service", http.StatusInternalServerError)
			return
		}

		var idPrestataire int
		var serviceName string
		err := database.QueryRow("SELECT id_prestataire, nom FROM service WHERE id_service = ?", id).Scan(&idPrestataire, &serviceName)
		if err != nil {
			http.Error(response, "Service introuvable", http.StatusNotFound)
			return
		}

		selectstatement, selecterr := database.Prepare("SELECT id_utilisateur FROM utilisateur WHERE token = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération des informations de l'utilisateur", http.StatusInternalServerError)
			return
		}
		var id_user int
		selectstatement.QueryRow(request.Header.Get("Token")).Scan(&id_user)
		var etat structures.Etat
		json.NewDecoder(request.Body).Decode(&etat)
		verifstatement, veriferr := database.Prepare("SELECT id_utilisateur FROM reference_service WHERE id_service = ?")
		if veriferr != nil {
			http.Error(response, "Erreur lors de la vérification de l'état du service pour l'utilisateur", http.StatusInternalServerError)
			return
		}

		var id_user_verif int
		err = verifstatement.QueryRow(id).Scan(&id_user_verif)
		var state string

		if err != nil {
			if (err == sql.ErrNoRows) && (etat.State == "join") {
				insertstatement, inserterr := database.Prepare("INSERT INTO reference_service (id_utilisateur, id_service) VALUES (?, ?)")
				if inserterr != nil {
					http.Error(response, "Erreur lors de l'insertion de la référence du service pour l'utilisateur", http.StatusInternalServerError)
					return
				}
				_, err = insertstatement.Exec(id_user, id)
				if err != nil {
					http.Error(response, "Erreur lors de l'insertion de la référence du service pour l'utilisateur", http.StatusInternalServerError)
					return
				}
				state = "rejoint"
			}
		} else if etat.State == "leave" {

			deleteStatement, deleteerr := database.Prepare("DELETE FROM reference_service WHERE id_utilisateur = ? AND id_service = ?")
			if deleteerr != nil {
				http.Error(response, "Erreur lors de la suppression de la référence du service pour l'utilisateur", http.StatusInternalServerError)
				return
			}
			_, err = deleteStatement.Exec(id_user, id)
			if err != nil {
				http.Error(response, "Erreur lors de la suppression de la référence du service pour l'utilisateur", http.StatusInternalServerError)
				return
			}

			var startStr string
			var endStr string

			err := database.QueryRow("SELECT date_debut, date_fin FROM rendez_vous WHERE id_prestataire = ? AND id_utilisateur = ? LIMIT 1", idPrestataire, id_user).Scan(&startStr, &endStr)

			if err != nil {
				http.Error(response, "Erreur récupération rendez-vous", http.StatusInternalServerError)
				return
			}
			startTime, _ := time.Parse("2006-01-02 15:04:05", startStr)
			endTime, _ := time.Parse("2006-01-02 15:04:05", endStr)

			date := startTime.Format("2006-01-02")
			heureDebut := startTime.Format("15:04:05")
			heureFin := endTime.Format("15:04:05")

			selectDispo, errDispo := database.Prepare("SELECT id_disponibilite FROM disponibilite WHERE id_prestataire = ? AND statut = 'indisponible' AND date = ? AND heure_debut = ? AND heure_fin = ?")

			if errDispo != nil {
				http.Error(response, "Erreur préparation requête", http.StatusInternalServerError)
				return
			}

			var idDispo int
			err = selectDispo.QueryRow(idPrestataire, date, heureDebut, heureFin).Scan(&idDispo)

			if err != nil {
				if err == sql.ErrNoRows {
					fmt.Println("Aucune indisponibilité trouvée")
				} else {
					http.Error(response, "Erreur requête", http.StatusInternalServerError)
				}
				return
			}

			deleteDispo, errDispo := database.Prepare("DELETE FROM disponibilite WHERE id_disponibilite = ?")
			if errDispo == nil {
				_, _ = deleteDispo.Exec(idDispo)
			}

			deleteRdv, errRdv := database.Prepare("DELETE FROM rendez_vous WHERE id_utilisateur = ? AND id_prestataire = ? AND type = ?")
			if errRdv == nil {
				_, _ = deleteRdv.Exec(id_user, idPrestataire, serviceName)
			}
			state = "quitté"

		} else {
			return
		}
		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Service " + state + " avec succès",
		})

	}
}
func Service_disponible(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		id, err := strconv.Atoi(request.URL.Query().Get("id"))
		if err != nil || id <= 0 {
			http.Error(response, "Identifiant de service invalide", http.StatusBadRequest)
			return
		}

		var idPrestataire int
		err = database.QueryRow("SELECT id_prestataire FROM service WHERE id_service = ?", id).Scan(&idPrestataire)
		if err != nil {
			http.Error(response, "Service introuvable", http.StatusNotFound)
			return
		}

		rows, err := database.Query("SELECT date, heure_debut, heure_fin, type_regle FROM disponibilite WHERE id_prestataire = ? AND date >= CURRENT_DATE() ORDER BY date, heure_debut", idPrestataire)
		if err != nil {
			http.Error(response, "Erreur lors de la récupération des disponibilités", http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		type slot struct {
			Start string `json:"start"`
			End   string `json:"end"`
			Type  string `json:"type"`
		}

		slots := make([]slot, 0)
		for rows.Next() {
			var dateStr, startStr, endStr, typeRegle string
			if err := rows.Scan(&dateStr, &startStr, &endStr, &typeRegle); err != nil {
				http.Error(response, "Erreur lors de la lecture des disponibilités", http.StatusInternalServerError)
				return
			}

			startTime, err := time.Parse("15:04:05", startStr)
			if err != nil {
				startTime, err = time.Parse("15:04", startStr)
				if err != nil {
					continue
				}
			}
			endTime, err := time.Parse("15:04:05", endStr)
			if err != nil {
				endTime, err = time.Parse("15:04", endStr)
				if err != nil {
					continue
				}
			}

			start := fmt.Sprintf("%s %s", dateStr, startTime.Format("15:04"))
			end := fmt.Sprintf("%s %s", dateStr, endTime.Format("15:04"))
			slots = append(slots, slot{Start: start, End: end, Type: typeRegle})
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(slots)
	}
}

func Reservation_service(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		type payload struct {
			ID    int    `json:"id_service"`
			Start string `json:"start"`
		}
		var p payload
		var err error
		err = json.NewDecoder(request.Body).Decode(&p)
		if err != nil {
			http.Error(response, "Payload invalide", http.StatusBadRequest)
			return
		}

		selectstatement, selecterr := database.Prepare("SELECT id_utilisateur FROM utilisateur WHERE token = ?")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération de l'utilisateur", http.StatusInternalServerError)
			return
		}
		var idUser int
		if err := selectstatement.QueryRow(request.Header.Get("Token")).Scan(&idUser); err != nil {
			http.Error(response, "Token invalide", http.StatusUnauthorized)
			return
		}

		var idPrestataire int
		var serviceName string
		if err := database.QueryRow("SELECT nom, id_prestataire FROM service WHERE id_service = ?", p.ID).Scan(&serviceName, &idPrestataire); err != nil {
			http.Error(response, "Service introuvable", http.StatusNotFound)
			return
		}

		p.Start = strings.TrimSpace(p.Start)
		normalized := strings.ReplaceAll(p.Start, "T", " ")
		if idx := strings.Index(normalized, "."); idx != -1 {
			normalized = normalized[:idx]
		}

		parseLayouts := []string{
			"2006-01-02 15:04:05",
			"2006-01-02 15:04",
			"2006-01-02 15",
			"2006-01-02",
		}
		var start time.Time
		var parseErr error
		for _, layout := range parseLayouts {
			start, parseErr = time.Parse(layout, normalized)
			if parseErr == nil {
				break
			}
		}

		if parseErr != nil {
			http.Error(response, "Format de date/heure invalide", http.StatusBadRequest)
			return
		}
		end := start.Add(time.Hour)

		var count int
		err = database.QueryRow("SELECT COUNT(*) FROM disponibilite WHERE id_prestataire = ? AND (statut = 'disponible' OR statut IS NULL) AND type_regle = 'disponible' AND date = ? AND heure_debut <= ? AND heure_fin >= ?", idPrestataire, start.Format("2006-01-02"), start.Format("15:04:00"), end.Format("15:04:00")).Scan(&count)
		if err != nil {
			http.Error(response, "Erreur lors de la vérification des disponibilités", http.StatusInternalServerError)
			return
		}
		if count == 0 {
			http.Error(response, "Créneau non disponible", http.StatusBadRequest)
			return
		}

		err = database.QueryRow(
			"SELECT COUNT(*) FROM rendez_vous WHERE id_prestataire = ? AND NOT (date_fin <= ? OR date_debut >= ?)",
			idPrestataire, start.Format("2006-01-02 15:04:05"), end.Format("2006-01-02 15:04:05"),
		).Scan(&count)
		if err != nil {
			http.Error(response, "Erreur lors de la vérification des rendez-vous existants", http.StatusInternalServerError)
			return
		}
		if count > 0 {
			http.Error(response, "Ce créneau est déjà réservé", http.StatusBadRequest)
			return
		}

		insertRef, err := database.Prepare("INSERT INTO reference_service (id_utilisateur, id_service) VALUES (?, ?)")
		if err == nil {
			_, _ = insertRef.Exec(idUser, p.ID)
		}

		insertRdv, err := database.Prepare("INSERT INTO rendez_vous (id_utilisateur, id_prestataire, date_debut, date_fin, type, statut) VALUES (?, ?, ?, ?, ?, 'confirmé')")
		if err != nil {
			http.Error(response, "Erreur lors de la création du rendez-vous", http.StatusInternalServerError)
			return
		}
		_, err = insertRdv.Exec(idUser, idPrestataire, start.Format("2006-01-02 15:04:05"), end.Format("2006-01-02 15:04:05"), serviceName)
		if err != nil {
			http.Error(response, "Erreur lors de l'insertion du rendez-vous", http.StatusInternalServerError)
			return
		}

		insertDispo, err := database.Prepare("INSERT INTO disponibilite (id_prestataire, date, heure_debut, heure_fin, statut, type_regle, recurrence) VALUES (?, ?, ?, ?, 'indisponible', 'indisponible', 'unique')")
		if err == nil {
			_, _ = insertDispo.Exec(idPrestataire, start.Format("2006-01-02"), start.Format("15:04:00"), end.Format("15:04:00"))
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: "Réservation confirmée"})
	}
}
