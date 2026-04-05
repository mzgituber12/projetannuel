package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"strconv"
	"strings"
	"time"

	"projet/structures"
)

func prestataireIDFromToken(database *sql.DB, token string) (int, error) {

	var idPrestataire int
	err := database.QueryRow(
		"SELECT p.id_prestataire FROM prestataire p JOIN utilisateur u ON u.id_utilisateur = p.id_utilisateur WHERE u.token = ?",
		token,
	).Scan(&idPrestataire)
	if err != nil {
		return 0, err
	}

	return idPrestataire, nil
}

func parseCalendarDateTime(value string) (time.Time, error) {
	value = strings.TrimSpace(value)
	layouts := []string{
		"2006-01-02 15:04:05",
		"2006-01-02T15:04:05",
		"2006-01-02T15:04",
		time.RFC3339,
		"2006-01-02 15:04",
	}

	var parsed time.Time
	var err error
	for _, layout := range layouts {
		parsed, err = time.Parse(layout, value)
		if err == nil {
			return parsed, nil
		}
	}

	return time.Time{}, err
}

func parseCalendarDate(value string) (time.Time, error) {
	value = strings.TrimSpace(value)
	layouts := []string{"2006-01-02", "2006-01-02T15:04:05", time.RFC3339}

	var parsed time.Time
	var err error
	for _, layout := range layouts {
		parsed, err = time.Parse(layout, value)
		if err == nil {
			return parsed, nil
		}
	}

	return time.Time{}, err
}

func normalizeFrenchWeekday(value string) string {
	v := strings.ToLower(strings.TrimSpace(value))
	switch v {
	case "lundi", "monday":
		return "lundi"
	case "mardi", "tuesday":
		return "mardi"
	case "mercredi", "wednesday":
		return "mercredi"
	case "jeudi", "thursday":
		return "jeudi"
	case "vendredi", "friday":
		return "vendredi"
	case "samedi", "saturday":
		return "samedi"
	case "dimanche", "sunday":
		return "dimanche"
	default:
		return ""
	}
}

func frenchWeekdayFromTime(weekday time.Weekday) string {
	switch weekday {
	case time.Monday:
		return "lundi"
	case time.Tuesday:
		return "mardi"
	case time.Wednesday:
		return "mercredi"
	case time.Thursday:
		return "jeudi"
	case time.Friday:
		return "vendredi"
	case time.Saturday:
		return "samedi"
	default:
		return "dimanche"
	}
}

func timeWeekdayFromFrench(day string) (time.Weekday, bool) {
	switch normalizeFrenchWeekday(day) {
	case "lundi":
		return time.Monday, true
	case "mardi":
		return time.Tuesday, true
	case "mercredi":
		return time.Wednesday, true
	case "jeudi":
		return time.Thursday, true
	case "vendredi":
		return time.Friday, true
	case "samedi":
		return time.Saturday, true
	case "dimanche":
		return time.Sunday, true
	default:
		return time.Sunday, false
	}
}

func Planning_evenements(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")

		sel, err := database.Prepare("SELECT e.nom, e.date, e.description, e.tarif FROM evenement e JOIN reference_evenement r on e.id_evenement = r.id_evenement JOIN utilisateur u on r.id_utilisateur = u.id_utilisateur WHERE token = ?")
		if err != nil {
			http.Error(response, "Erreur lors de la préparation de la requête des evenements", http.StatusInternalServerError)
			return
		}
		rows, err := sel.Query(token)
		if err != nil {
			http.Error(response, "Erreur lors de la selection des evenements de la base de données", http.StatusInternalServerError)
			return
		} else {
			var evenements []structures.Evenement

			for rows.Next() {
				var e structures.Evenement

				err := rows.Scan(&e.Nom, &e.Date, &e.Description, &e.Tarif)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des evenements : "+err.Error(), http.StatusInternalServerError)
					return
				}

				evenements = append(evenements, e)
			}
			if len(evenements) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun evenement pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Evenement: evenements,
			})
		}
	}
}

func Planning_services(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")

		sel, err := database.Prepare("SELECT s.nom, s.description, s.tarif FROM service s JOIN intervention i on s.id_service = i.id_service JOIN utilisateur u on i.id_utilisateur = u.id_utilisateur WHERE token = ?")
		if err != nil {
			http.Error(response, "Erreur lors de la préparation de la requête des services", http.StatusInternalServerError)
			return
		}
		rows, err := sel.Query(token)
		if err != nil {
			http.Error(response, "Erreur lors de la selection des services de la base de données", http.StatusInternalServerError)
			return
		} else {
			var services []structures.Service

			for rows.Next() {
				var s structures.Service

				err := rows.Scan(&s.Nom, &s.Description, &s.Tarif)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des services", http.StatusInternalServerError)
					return
				}

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

func Planning_rdv(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		sel, err := database.Prepare("SELECT id_rdv, date_debut, date_fin, type FROM rendez_vous")
		if err != nil {
			http.Error(response, "Erreur lors de la préparation de la requête des rendez-vous", http.StatusInternalServerError)
			return
		}

		rows, err := sel.Query()
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des rendez-vous", http.StatusInternalServerError)
			return
		}

		var events []structures.Rdv
		for rows.Next() {
			var e structures.Rdv
			if err := rows.Scan(&e.ID, &e.Start, &e.End, &e.Title); err != nil {
				http.Error(response, "Erreur lors du scan des rendez-vous", http.StatusInternalServerError)
				return
			}
			events = append(events, e)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(events)
	}
}

func Planning_rdv_prestataire(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		idPrestataire, err := prestataireIDFromToken(database, request.Header.Get("Token"))
		if err != nil {
			http.Error(response, "Accès prestataire requis", http.StatusUnauthorized)
			return
		}

		rows, err := database.Query(
			"SELECT id_rdv, date_debut, date_fin, IFNULL(type, 'Rendez-vous') FROM rendez_vous WHERE id_prestataire = ? ORDER BY date_debut",
			idPrestataire,
		)
		if err != nil {
			http.Error(response, "Erreur lors de la lecture des rendez-vous", http.StatusInternalServerError)
			return
		}
		defer rows.Close()

		events := make([]structures.Rdv, 0)
		for rows.Next() {
			var e structures.Rdv
			var start sql.NullTime
			var end sql.NullTime
			if err := rows.Scan(&e.ID, &start, &end, &e.Title); err != nil {
				http.Error(response, "Erreur lors du scan des rendez-vous", http.StatusInternalServerError)
				return
			}

			if start.Valid {
				e.Start = start.Time.Format("2006-01-02T15:04:05")
			}
			if end.Valid {
				e.End = end.Time.Format("2006-01-02T15:04:05")
			}

			events = append(events, e)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(events)
	}
}

func Disponibilites_prestataire(database *sql.DB) http.HandlerFunc {
	type slot struct {
		ID         int    `json:"id"`
		Start      string `json:"start"`
		End        string `json:"end"`
		Type       string `json:"type"`
		Recurrence string `json:"recurrence"`
	}

	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		idPrestataire, err := prestataireIDFromToken(database, request.Header.Get("Token"))
		if err != nil {
			http.Error(response, "Accès prestataire requis", http.StatusUnauthorized)
			return
		}

		rangeStart := time.Now().Truncate(24 * time.Hour)
		rangeEnd := rangeStart.AddDate(0, 3, 0)

		if startQuery := request.URL.Query().Get("start"); strings.TrimSpace(startQuery) != "" {
			parsedStart, err := parseCalendarDateTime(startQuery)
			if err == nil {
				rangeStart = time.Date(parsedStart.Year(), parsedStart.Month(), parsedStart.Day(), 0, 0, 0, 0, parsedStart.Location())
			}
		}
		if endQuery := request.URL.Query().Get("end"); strings.TrimSpace(endQuery) != "" {
			parsedEnd, err := parseCalendarDateTime(endQuery)
			if err == nil {
				rangeEnd = time.Date(parsedEnd.Year(), parsedEnd.Month(), parsedEnd.Day(), 0, 0, 0, 0, parsedEnd.Location())
			}
		}
		if rangeEnd.Before(rangeStart) {
			http.Error(response, "Intervalle de dates invalide", http.StatusBadRequest)
			return
		}

		rows, err := database.Query(
			`SELECT
				id_disponibilite,
				DATE_FORMAT(date, '%Y-%m-%d'),
				TIME_FORMAT(heure_debut, '%H:%i:%s'),
				TIME_FORMAT(heure_fin, '%H:%i:%s'),
				type_regle,
				IFNULL(recurrence, 'unique'),
				IFNULL(jour_semaine, ''),
				IFNULL(DATE_FORMAT(date_fin_regle, '%Y-%m-%d'), '')
			FROM disponibilite
			WHERE id_prestataire = ?
			AND (
				(IFNULL(recurrence, 'unique') = 'unique' AND date BETWEEN ? AND ?)
				OR
				(IFNULL(recurrence, 'unique') != 'unique' AND date <= ? AND (date_fin_regle IS NULL OR date_fin_regle >= ?))
			)
			ORDER BY date, heure_debut`,
			idPrestataire,
			rangeStart.Format("2006-01-02"),
			rangeEnd.Format("2006-01-02"),
			rangeEnd.Format("2006-01-02"),
			rangeStart.Format("2006-01-02"),
		)
		if err != nil {
			http.Error(response, "Erreur lors de la récupération des disponibilités", http.StatusInternalServerError)
			return
		}

		slots := make([]slot, 0)
		for rows.Next() {
			var id int
			var dateStr string
			var startStr string
			var endStr string
			var typeRegle string
			var recurrence string
			var jourSemaine string
			var dateFinRegle string
			if err := rows.Scan(&id, &dateStr, &startStr, &endStr, &typeRegle, &recurrence, &jourSemaine, &dateFinRegle); err != nil {
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

			baseDate, err := time.Parse("2006-01-02", dateStr)
			if err != nil {
				continue
			}

			recurrence = strings.ToLower(strings.TrimSpace(recurrence))
			if recurrence == "" {
				recurrence = "unique"
			}

			if recurrence == "unique" {
				if !baseDate.Before(rangeStart) && !baseDate.After(rangeEnd) {
					slots = append(slots, slot{
						ID:         id,
						Start:      baseDate.Format("2006-01-02") + " " + startTime.Format("15:04:05"),
						End:        baseDate.Format("2006-01-02") + " " + endTime.Format("15:04:05"),
						Type:       typeRegle,
						Recurrence: "unique",
					})
				}
				continue
			}

			ruleStart := baseDate
			if ruleStart.Before(rangeStart) {
				ruleStart = rangeStart
			}
			ruleEnd := rangeEnd
			if strings.TrimSpace(dateFinRegle) != "" {
				parsedEnd, err := time.Parse("2006-01-02", dateFinRegle)
				if err == nil && parsedEnd.Before(ruleEnd) {
					ruleEnd = parsedEnd
				}
			}
			if ruleEnd.Before(ruleStart) {
				continue
			}

			normalizedDay := normalizeFrenchWeekday(jourSemaine)
			if recurrence == "hebdomadaire" && normalizedDay != "" {
				targetWeekday, ok := timeWeekdayFromFrench(normalizedDay)
				if !ok {
					continue
				}

				d := ruleStart
				for !d.After(ruleEnd) {
					if d.Weekday() == targetWeekday {
						slots = append(slots, slot{
							ID:         id,
							Start:      d.Format("2006-01-02") + " " + startTime.Format("15:04:05"),
							End:        d.Format("2006-01-02") + " " + endTime.Format("15:04:05"),
							Type:       typeRegle,
							Recurrence: "hebdomadaire",
						})
					}
					d = d.AddDate(0, 0, 1)
				}
				continue
			}

			d := ruleStart
			for !d.After(ruleEnd) {
				slots = append(slots, slot{
					ID:         id,
					Start:      d.Format("2006-01-02") + " " + startTime.Format("15:04:05"),
					End:        d.Format("2006-01-02") + " " + endTime.Format("15:04:05"),
					Type:       typeRegle,
					Recurrence: "quotidienne",
				})
				d = d.AddDate(0, 0, 1)
			}
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(slots)
	}
}

func Creer_disponibilite_prestataire(database *sql.DB) http.HandlerFunc {
	type payload struct {
		Start        string `json:"start"`
		End          string `json:"end"`
		Type         string `json:"type"`
		Recurrence   string `json:"recurrence"`
		DateFinRegle string `json:"date_fin_regle"`
		JourSemaine  string `json:"jour_semaine"`
	}

	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		idPrestataire, err := prestataireIDFromToken(database, request.Header.Get("Token"))
		if err != nil {
			http.Error(response, "Accès prestataire requis", http.StatusUnauthorized)
			return
		}

		var body payload
		if err := json.NewDecoder(request.Body).Decode(&body); err != nil {
			http.Error(response, "Payload invalide", http.StatusBadRequest)
			return
		}

		typeRegle := strings.ToLower(strings.TrimSpace(body.Type))
		if typeRegle != "disponible" && typeRegle != "indisponible" {
			http.Error(response, "Type invalide (disponible ou indisponible)", http.StatusBadRequest)
			return
		}

		start, err := parseCalendarDateTime(body.Start)
		if err != nil {
			http.Error(response, "Date de début invalide", http.StatusBadRequest)
			return
		}
		end, err := parseCalendarDateTime(body.End)
		if err != nil {
			http.Error(response, "Date de fin invalide", http.StatusBadRequest)
			return
		}

		if !end.After(start) {
			http.Error(response, "La date de fin doit être après la date de début", http.StatusBadRequest)
			return
		}

		if start.Format("2006-01-02") != end.Format("2006-01-02") {
			http.Error(response, "Le créneau doit être sur une seule journée", http.StatusBadRequest)
			return
		}

		recurrenceInput := strings.ToLower(strings.TrimSpace(body.Recurrence))
		if recurrenceInput == "" {
			recurrenceInput = "unique"
		}
		if recurrenceInput != "unique" && recurrenceInput != "quotidienne" && recurrenceInput != "hebdomadaire" {
			http.Error(response, "Récurrence invalide (unique, quotidienne, hebdomadaire)", http.StatusBadRequest)
			return
		}

		recurrenceDB := "unique"
		var jourSemaine any = nil
		var dateFin any = nil

		if recurrenceInput != "unique" {
			dateFinRule, err := parseCalendarDate(body.DateFinRegle)
			if err != nil {
				http.Error(response, "Date de fin de règle invalide", http.StatusBadRequest)
				return
			}

			startDate := time.Date(start.Year(), start.Month(), start.Day(), 0, 0, 0, 0, start.Location())
			endDate := time.Date(dateFinRule.Year(), dateFinRule.Month(), dateFinRule.Day(), 0, 0, 0, 0, dateFinRule.Location())
			if endDate.Before(startDate) {
				http.Error(response, "La date de fin de règle doit être après la date de début", http.StatusBadRequest)
				return
			}

			recurrenceDB = "hebdomadaire"
			dateFin = endDate.Format("2006-01-02")

			if recurrenceInput == "hebdomadaire" {
				day := normalizeFrenchWeekday(body.JourSemaine)
				if day == "" {
					day = frenchWeekdayFromTime(start.Weekday())
				}
				jourSemaine = day
			}
		}

		var exists int
		err = database.QueryRow(
			"SELECT COUNT(*) FROM disponibilite WHERE id_prestataire = ? AND date = ? AND heure_debut = ? AND heure_fin = ? AND type_regle = ? AND IFNULL(recurrence, 'unique') = ? AND IFNULL(jour_semaine, '') = IFNULL(?, '') AND IFNULL(DATE_FORMAT(date_fin_regle, '%Y-%m-%d'), '') = IFNULL(?, '')",
			idPrestataire,
			start.Format("2006-01-02"),
			start.Format("15:04:05"),
			end.Format("15:04:05"),
			typeRegle,
			recurrenceDB,
			jourSemaine,
			dateFin,
		).Scan(&exists)
		if err != nil {
			http.Error(response, "Erreur lors de la vérification du créneau", http.StatusInternalServerError)
			return
		}
		if exists > 0 {
			http.Error(response, "Ce créneau existe déjà", http.StatusConflict)
			return
		}

		_, err = database.Exec(
			"INSERT INTO disponibilite (id_prestataire, date, heure_debut, heure_fin, statut, jour_semaine, type_regle, recurrence, date_fin_regle) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
			idPrestataire,
			start.Format("2006-01-02"),
			start.Format("15:04:05"),
			end.Format("15:04:05"),
			typeRegle,
			jourSemaine,
			typeRegle,
			recurrenceDB,
			dateFin,
		)
		if err != nil {
			http.Error(response, "Erreur lors de la création du créneau", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: "Créneau ajouté"})
	}
}

func Supprimer_disponibilite_prestataire(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "DELETE, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		idPrestataire, err := prestataireIDFromToken(database, request.Header.Get("Token"))
		if err != nil {
			http.Error(response, "Accès prestataire requis", http.StatusUnauthorized)
			return
		}

		idDispo, err := strconv.Atoi(request.PathValue("id"))
		if err != nil || idDispo <= 0 {
			http.Error(response, "Identifiant de disponibilité invalide", http.StatusBadRequest)
			return
		}

		result, err := database.Exec(
			"DELETE FROM disponibilite WHERE id_disponibilite = ? AND id_prestataire = ?",
			idDispo,
			idPrestataire,
		)
		if err != nil {
			http.Error(response, "Erreur lors de la suppression du créneau", http.StatusInternalServerError)
			return
		}

		affected, _ := result.RowsAffected()
		if affected == 0 {
			http.Error(response, "Créneau introuvable", http.StatusNotFound)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: "Créneau supprimé"})
	}
}
