package ressources

import (
	"database/sql"
	"encoding/json"
	"errors"
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
		verifstatement, veriferr := database.Prepare("SELECT id_utilisateur FROM reference_service WHERE id_service = ? AND id_utilisateur = ?")
		if veriferr != nil {
			http.Error(response, "Erreur lors de la vérification de l'état du service pour l'utilisateur", http.StatusInternalServerError)
			return
		}

		var id_user_verif int
		err = verifstatement.QueryRow(id, id_user).Scan(&id_user_verif)
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

				titreNotif, contenuNotif := LireTemplate(database, "reservation_service", map[string]string{
					"service": serviceName,
					"date":    "créneau à confirmer",
				})
				_ = creerNotification(database, id_user, titreNotif, contenuNotif)
				state = "rejoint"
			}
			if err != sql.ErrNoRows {
				http.Error(response, "Erreur lors de la vérification de l'état du service pour l'utilisateur", http.StatusInternalServerError)
				return
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
			startTime, err := parseDateTimeFlexible(startStr)
			if err != nil {
				http.Error(response, "Erreur parsing date_debut du rendez-vous", http.StatusInternalServerError)
				return
			}

			endTime, err := parseDateTimeFlexible(endStr)
			if err != nil {
				http.Error(response, "Erreur parsing date_fin du rendez-vous", http.StatusInternalServerError)
				return
			}

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

			deleteDevis, errDevis := database.Prepare(`
				DELETE d
				FROM devis d
				INNER JOIN intervention i ON i.id_intervention = d.id_intervention
				INNER JOIN rendez_vous r ON r.id_rdv = i.id_rdv
				WHERE i.id_utilisateur = ?
				  AND i.id_service = ?
				  AND r.id_prestataire = ?
				  AND r.type = ?
			`)
			if errDevis != nil {
				http.Error(response, "Erreur lors de la préparation de la suppression du devis lié", http.StatusInternalServerError)
				return
			}
			if _, err = deleteDevis.Exec(id_user, id, idPrestataire, serviceName); err != nil {
				http.Error(response, "Erreur lors de la suppression du devis lié", http.StatusInternalServerError)
				return
			}

			deleteIntervention, errIntervention := database.Prepare(`
				DELETE i
				FROM intervention i
				INNER JOIN rendez_vous r ON r.id_rdv = i.id_rdv
				WHERE i.id_utilisateur = ?
				  AND i.id_service = ?
				  AND r.id_prestataire = ?
				  AND r.type = ?
			`)
			if errIntervention != nil {
				http.Error(response, "Erreur lors de la préparation de la suppression de l'intervention liée", http.StatusInternalServerError)
				return
			}
			if _, err = deleteIntervention.Exec(id_user, id, idPrestataire, serviceName); err != nil {
				http.Error(response, "Erreur lors de la suppression de l'intervention liée", http.StatusInternalServerError)
				return
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

			normalizedDate := dateStr
			parsedDate, err := parseDateTimeFlexible(dateStr)
			if err == nil {
				normalizedDate = parsedDate.Format("2006-01-02")
			} else {
				parsedDateOnly, dateOnlyErr := time.Parse("2006-01-02", dateStr)
				if dateOnlyErr == nil {
					normalizedDate = parsedDateOnly.Format("2006-01-02")
				}
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

			start := fmt.Sprintf("%s %s", normalizedDate, startTime.Format("15:04"))
			end := fmt.Sprintf("%s %s", normalizedDate, endTime.Format("15:04"))
			slots = append(slots, slot{Start: start, End: end, Type: typeRegle})
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(slots)
	}
}

func reserveServiceSlot(database *sql.DB, idUser int, idService int, startInput string) (string, int, error) {
	var idPrestataire int
	var serviceName string
	if err := database.QueryRow("SELECT nom, id_prestataire, IFNULL(tarif, 0) FROM service WHERE id_service = ?", idService).Scan(&serviceName, &idPrestataire, new(float64)); err != nil {
		return "", http.StatusNotFound, errors.New("service introuvable")
	}

	startInput = strings.TrimSpace(startInput)
	normalized := strings.ReplaceAll(startInput, "T", " ")
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
		return "", http.StatusBadRequest, errors.New("format de date/heure invalide")
	}
	end := start.Add(time.Hour)

	var count int
	err := database.QueryRow("SELECT COUNT(*) FROM disponibilite WHERE id_prestataire = ? AND (statut = 'disponible' OR statut IS NULL) AND type_regle = 'disponible' AND date = ? AND heure_debut <= ? AND heure_fin >= ?", idPrestataire, start.Format("2006-01-02"), start.Format("15:04:00"), end.Format("15:04:00")).Scan(&count)
	if err != nil {
		return "", http.StatusInternalServerError, errors.New("erreur lors de la vérification des disponibilités")
	}
	if count == 0 {
		return "", http.StatusBadRequest, errors.New("créneau non disponible")
	}

	err = database.QueryRow(
		"SELECT COUNT(*) FROM rendez_vous WHERE id_prestataire = ? AND NOT (date_fin <= ? OR date_debut >= ?)",
		idPrestataire, start.Format("2006-01-02 15:04:05"), end.Format("2006-01-02 15:04:05"),
	).Scan(&count)
	if err != nil {
		return "", http.StatusInternalServerError, errors.New("erreur lors de la vérification des rendez-vous existants")
	}
	if count > 0 {
		return "", http.StatusBadRequest, errors.New("ce créneau est déjà réservé")
	}

	insertRef, err := database.Prepare("INSERT INTO reference_service (id_utilisateur, id_service) VALUES (?, ?)")
	if err == nil {
		_, _ = insertRef.Exec(idUser, idService)
	}

	insertRdv, err := database.Prepare("INSERT INTO rendez_vous (id_utilisateur, id_prestataire, date_debut, date_fin, type, statut) VALUES (?, ?, ?, ?, ?, 'confirmé')")
	if err != nil {
		return "", http.StatusInternalServerError, errors.New("erreur lors de la création du rendez-vous")
	}
	_, err = insertRdv.Exec(idUser, idPrestataire, start.Format("2006-01-02 15:04:05"), end.Format("2006-01-02 15:04:05"), serviceName)
	if err != nil {
		return "", http.StatusInternalServerError, errors.New("erreur lors de l'insertion du rendez-vous")
	}

	insertDispo, err := database.Prepare("INSERT INTO disponibilite (id_prestataire, date, heure_debut, heure_fin, statut, type_regle, recurrence) VALUES (?, ?, ?, ?, 'indisponible', 'indisponible', 'unique')")
	if err == nil {
		_, _ = insertDispo.Exec(idPrestataire, start.Format("2006-01-02"), start.Format("15:04:00"), end.Format("15:04:00"))
	}

	titreNotif, contenuNotif := LireTemplate(database, "reservation_service", map[string]string{
		"service": serviceName,
		"date":    start.Format("02/01/2006 à 15:04"),
	})
	_ = creerNotification(database, idUser, titreNotif, contenuNotif)

	return "Réservation confirmée", http.StatusOK, nil
}

func ensureReferenceServiceTx(tx *sql.Tx, idUser int, idService int) error {
	var idRef int
	err := tx.QueryRow("SELECT id FROM reference_service WHERE id_utilisateur = ? AND id_service = ? LIMIT 1", idUser, idService).Scan(&idRef)
	if err == nil {
		return nil
	}
	if err != sql.ErrNoRows {
		return err
	}

	_, err = tx.Exec("INSERT INTO reference_service (id_utilisateur, id_service) VALUES (?, ?)", idUser, idService)
	return err
}

func CreerDevis(database *sql.DB) http.HandlerFunc {
	type payload struct {
		IDService int    `json:"id_service"`
		Start     string `json:"start"`
	}

	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		response.Header().Set("Content-Type", "application/json")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			response.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(response).Encode(map[string]string{"message": "Token manquant"})
			return
		}

		var idUser int
		err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			response.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(response).Encode(map[string]string{"message": "Utilisateur introuvable"})
			return
		}

		var body payload
		err = json.NewDecoder(request.Body).Decode(&body)
		if err != nil || body.IDService <= 0 || strings.TrimSpace(body.Start) == "" {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Données invalides"})
			return
		}

		var serviceName string
		var idPrestataire int
		var tarif float64
		err = database.QueryRow("SELECT nom, IFNULL(id_prestataire, 0), IFNULL(tarif, 0) FROM service WHERE id_service = ?", body.IDService).Scan(&serviceName, &idPrestataire, &tarif)
		if err != nil {
			response.WriteHeader(http.StatusNotFound)
			json.NewEncoder(response).Encode(map[string]string{"message": "Service introuvable"})
			return
		}

		normalized := strings.TrimSpace(strings.ReplaceAll(body.Start, "T", " "))
		if idx := strings.Index(normalized, "."); idx != -1 {
			normalized = normalized[:idx]
		}
		parseLayouts := []string{"2006-01-02 15:04:05", "2006-01-02 15:04", "2006-01-02 15", "2006-01-02"}
		var start time.Time
		var parseErr error
		for _, layout := range parseLayouts {
			start, parseErr = time.Parse(layout, normalized)
			if parseErr == nil {
				break
			}
		}
		if parseErr != nil {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Format de date invalide"})
			return
		}
		end := start.Add(time.Hour)

		var count int
		err = database.QueryRow(
			"SELECT COUNT(*) FROM disponibilite WHERE id_prestataire = ? AND (statut = 'disponible' OR statut IS NULL) AND type_regle = 'disponible' AND date = ? AND heure_debut <= ? AND heure_fin >= ?",
			idPrestataire, start.Format("2006-01-02"), start.Format("15:04:00"), end.Format("15:04:00"),
		).Scan(&count)
		if err != nil || count == 0 {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Créneau non disponible"})
			return
		}

		err = database.QueryRow(
			"SELECT COUNT(*) FROM rendez_vous WHERE id_prestataire = ? AND NOT (date_fin <= ? OR date_debut >= ?)",
			idPrestataire, start.Format("2006-01-02 15:04:05"), end.Format("2006-01-02 15:04:05"),
		).Scan(&count)
		if err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur vérification rendez-vous"})
			return
		}
		if count > 0 {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Ce créneau est déjà réservé"})
			return
		}

		tx, err := database.Begin()
		if err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur transaction"})
			return
		}
		defer tx.Rollback()

		if err = ensureReferenceServiceTx(tx, idUser, body.IDService); err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur création référence service"})
			return
		}

		res, err := tx.Exec(
			"INSERT INTO rendez_vous (id_utilisateur, id_prestataire, date_debut, date_fin, type, statut) VALUES (?, ?, ?, ?, ?, 'en_attente')",
			idUser, idPrestataire, start.Format("2006-01-02 15:04:05"), end.Format("2006-01-02 15:04:05"), serviceName,
		)
		if err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur création rendez-vous"})
			return
		}
		idRdv, _ := res.LastInsertId()

		var prestataireNullable sql.NullInt64
		if idPrestataire > 0 {
			prestataireNullable = sql.NullInt64{Int64: int64(idPrestataire), Valid: true}
		}
		resInter, err := tx.Exec(
			"INSERT INTO intervention (id_service, id_prestataire, id_utilisateur, id_rdv, statut, montant) VALUES (?, ?, ?, ?, 'en_attente', ?)",
			body.IDService, prestataireNullable, idUser, idRdv, tarif,
		)
		if err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur création intervention"})
			return
		}
		idIntervention, _ := resInter.LastInsertId()

		resDevis, err := tx.Exec(
			"INSERT INTO devis (id_utilisateur, id_prestataire, id_intervention, tarif_personalise, status) VALUES (?, ?, ?, ?, 'en_attente')",
			idUser, prestataireNullable, idIntervention, tarif,
		)
		if err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur création devis"})
			return
		}
		idDevis, _ := resDevis.LastInsertId()

		var idDispo int
		err = tx.QueryRow(
			"SELECT id_disponibilite FROM disponibilite WHERE id_prestataire = ? AND date = ? AND heure_debut = ? AND heure_fin = ? AND type_regle = 'indisponible' LIMIT 1",
			idPrestataire, start.Format("2006-01-02"), start.Format("15:04:00"), end.Format("15:04:00"),
		).Scan(&idDispo)
		if err != nil {
			if err == sql.ErrNoRows {
				if _, err = tx.Exec(
					"INSERT INTO disponibilite (id_prestataire, date, heure_debut, heure_fin, statut, type_regle, recurrence) VALUES (?, ?, ?, ?, 'indisponible', 'indisponible', 'unique')",
					idPrestataire, start.Format("2006-01-02"), start.Format("15:04:00"), end.Format("15:04:00"),
				); err != nil {
					response.WriteHeader(http.StatusInternalServerError)
					json.NewEncoder(response).Encode(map[string]string{"message": "Erreur création indisponibilité"})
					return
				}
			} else {
				response.WriteHeader(http.StatusInternalServerError)
				json.NewEncoder(response).Encode(map[string]string{"message": "Erreur vérification indisponibilité"})
				return
			}
		}

		if err = tx.Commit(); err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur validation transaction"})
			return
		}

		titreNotif, contenuNotif := LireTemplate(database, "reservation_service", map[string]string{
			"service": serviceName,
			"date":    start.Format("02/01/2006 à 15:04"),
		})
		_ = creerNotification(database, idUser, titreNotif, contenuNotif)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"message":  "Devis créé avec succès",
			"id_devis": idDevis,
		})
	}
}

func MesDevis(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		response.Header().Set("Content-Type", "application/json")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			response.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(response).Encode(map[string]string{"message": "Token manquant"})
			return
		}

		var idUser int
		err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			response.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(response).Encode(map[string]string{"message": "Utilisateur introuvable"})
			return
		}

		rows, err := database.Query(`
			SELECT d.id_devis, IFNULL(s.nom, ''), IFNULL(CONCAT(u.prenom, ' ', u.nom), ''), IFNULL(d.tarif_personalise, 0), IFNULL(d.status, ''), IFNULL(rdv.date_debut, ''), IFNULL(rdv.date_fin, '') FROM devis d
			JOIN intervention i ON i.id_intervention = d.id_intervention
			LEFT JOIN service s ON s.id_service = i.id_service
			LEFT JOIN prestataire p ON p.id_prestataire = d.id_prestataire
			LEFT JOIN utilisateur u ON u.id_utilisateur = p.id_utilisateur
			LEFT JOIN rendez_vous rdv ON rdv.id_rdv = i.id_rdv WHERE d.id_utilisateur = ? ORDER BY d.id_devis DESC`, idUser)
		if err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur récupération des devis"})
			return
		}
		defer rows.Close()

		devisList := make([]structures.Devis, 0)
		for rows.Next() {
			var d structures.Devis
			if err := rows.Scan(&d.ID, &d.NomService, &d.NomPrestataire, &d.Tarif, &d.Status, &d.DateDebut, &d.DateFin); err != nil {
				continue
			}
			devisList = append(devisList, d)
		}

		json.NewEncoder(response).Encode(structures.List{Devis: devisList})
	}
}

func DevisDetail(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		response.Header().Set("Content-Type", "application/json")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			response.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(response).Encode(map[string]string{"message": "Token manquant"})
			return
		}

		var idUser int
		var role string
		err := database.QueryRow("SELECT id_utilisateur, role FROM utilisateur WHERE token = ?", token).Scan(&idUser, &role)
		if err != nil {
			response.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(response).Encode(map[string]string{"message": "Utilisateur introuvable"})
			return
		}

		id, err := strconv.Atoi(request.PathValue("id"))
		if err != nil || id <= 0 {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Identifiant invalide"})
			return
		}

		var idPrestataireCaller int
		_ = database.QueryRow("SELECT id_prestataire FROM prestataire WHERE id_utilisateur = ?", idUser).Scan(&idPrestataireCaller)

		var d structures.Devis
		err = database.QueryRow(`
			SELECT d.id_devis, IFNULL(s.nom, ''), IFNULL(CONCAT(u.prenom, ' ', u.nom), ''), IFNULL(d.tarif_personalise, 0), IFNULL(d.status, ''), IFNULL(rdv.date_debut, ''), IFNULL(rdv.date_fin, ''), IFNULL(d.id_prestataire, 0) FROM devis d
			JOIN intervention i ON i.id_intervention = d.id_intervention LEFT JOIN service s ON s.id_service = i.id_service LEFT JOIN prestataire p ON p.id_prestataire = d.id_prestataire LEFT JOIN utilisateur u ON u.id_utilisateur = p.id_utilisateur LEFT JOIN rendez_vous rdv ON rdv.id_rdv = i.id_rdv WHERE d.id_devis = ?`, id).Scan(
			&d.ID, &d.NomService, &d.NomPrestataire, &d.Tarif, &d.Status, &d.DateDebut, &d.DateFin, new(int),
		)
		if err != nil {
			response.WriteHeader(http.StatusNotFound)
			json.NewEncoder(response).Encode(map[string]string{"message": "Devis introuvable"})
			return
		}

		var ownerID int
		var linkedPrestaID int
		_ = database.QueryRow("SELECT IFNULL(id_utilisateur, 0), IFNULL(id_prestataire, 0) FROM devis WHERE id_devis = ?", id).Scan(&ownerID, &linkedPrestaID)

		isOwner := ownerID == idUser
		isLinkedPresta := idPrestataireCaller > 0 && idPrestataireCaller == linkedPrestaID
		isAdmin := role == "admin"

		if !isOwner && !isLinkedPresta && !isAdmin {
			response.WriteHeader(http.StatusForbidden)
			json.NewEncoder(response).Encode(map[string]string{"message": "Accès refusé"})
			return
		}

		d.CanModify = (isOwner || isLinkedPresta || isAdmin) && d.Status == "en_attente"

		json.NewEncoder(response).Encode(d)
	}
}

func PatchDevis(database *sql.DB) http.HandlerFunc {
	type payload struct {
		Status string `json:"status"`
	}

	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "PATCH, OPTIONS")
		response.Header().Set("Content-Type", "application/json")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			response.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(response).Encode(map[string]string{"message": "Token manquant"})
			return
		}

		var idUser int
		var role string
		err := database.QueryRow("SELECT id_utilisateur, role FROM utilisateur WHERE token = ?", token).Scan(&idUser, &role)
		if err != nil {
			response.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(response).Encode(map[string]string{"message": "Utilisateur introuvable"})
			return
		}

		id, err := strconv.Atoi(request.PathValue("id"))
		if err != nil || id <= 0 {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Identifiant invalide"})
			return
		}

		var body payload
		if err := json.NewDecoder(request.Body).Decode(&body); err != nil || (body.Status != "accepté" && body.Status != "refusé") {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Statut invalide (accepté ou refusé attendu)"})
			return
		}

		var currentStatus string
		var linkedPrestaID int
		err = database.QueryRow("SELECT IFNULL(status, ''), IFNULL(id_prestataire, 0) FROM devis WHERE id_devis = ?", id).Scan(&currentStatus, &linkedPrestaID)
		if err != nil {
			response.WriteHeader(http.StatusNotFound)
			json.NewEncoder(response).Encode(map[string]string{"message": "Devis introuvable"})
			return
		}

		if currentStatus != "en_attente" {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Ce devis a déjà été traité"})
			return
		}

		var ownerID int
		_ = database.QueryRow("SELECT IFNULL(id_utilisateur, 0) FROM devis WHERE id_devis = ?", id).Scan(&ownerID)

		var idPrestataireCaller int
		_ = database.QueryRow("SELECT id_prestataire FROM prestataire WHERE id_utilisateur = ?", idUser).Scan(&idPrestataireCaller)

		isOwner := ownerID == idUser
		isLinkedPresta := idPrestataireCaller > 0 && idPrestataireCaller == linkedPrestaID
		isAdmin := role == "admin"

		if !isOwner && !isLinkedPresta && !isAdmin {
			response.WriteHeader(http.StatusForbidden)
			json.NewEncoder(response).Encode(map[string]string{"message": "Accès refusé"})
			return
		}

		_, err = database.Exec("UPDATE devis SET status = ? WHERE id_devis = ?", body.Status, id)
		if err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur lors de la mise à jour du devis"})
			return
		}

		newRdvStatut := "confirmé"
		newInterventionStatut := "confirmé"
		if body.Status == "refusé" {
			newRdvStatut = "annulé"
			newInterventionStatut = "annulé"
		}
		_, _ = database.Exec(
			"UPDATE rendez_vous rdv JOIN intervention i ON i.id_rdv = rdv.id_rdv JOIN devis d ON d.id_intervention = i.id_intervention SET rdv.statut = ? WHERE d.id_devis = ?",
			newRdvStatut, id,
		)
		_, _ = database.Exec(
			"UPDATE intervention i JOIN devis d ON d.id_intervention = i.id_intervention SET i.statut = ? WHERE d.id_devis = ?",
			newInterventionStatut, id,
		)

		json.NewEncoder(response).Encode(map[string]string{"message": "Devis " + body.Status + " avec succès"})
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

		message, statusCode, reservationErr := reserveServiceSlot(database, idUser, p.ID, p.Start)
		if reservationErr != nil {
			http.Error(response, reservationErr.Error(), statusCode)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: message})
	}
}
