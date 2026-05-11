package ressources

import (
	"database/sql"
	"encoding/json"
	"errors"
	"fmt"
	"math"
	"net/http"
	"os"
	"strconv"
	"strings"
	"time"

	"projet/structures"

	"github.com/stripe/stripe-go/v83"
	stripesession "github.com/stripe/stripe-go/v83/checkout/session"
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
			"CONCAT(u.prenom, ' ', u.nom) AS prestataire_nom, " +
			"IFNULL(s.valide_admin, 0) AS valide_admin " +
			"FROM service s " +
			"LEFT JOIN categorie c ON c.id_categorie = s.id_categorie " +
			"LEFT JOIN prestataire p ON p.id_prestataire = s.id_prestataire " +
			"LEFT JOIN utilisateur u ON u.id_utilisateur = p.id_utilisateur " +
			"WHERE IFNULL(s.valide_admin, 0) = 1 " +
			"AND (s.id_categorie IS NULL OR IFNULL(c.valide_admin, 0) = 1)"

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

				err := rows.Scan(&id, &s.Nom, &s.Description, &s.Tarif, &s.Image, &idCategorie, &categorieNom, &prestataireNom, &s.ValideAdmin)
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
		var valideS, valideC int
		err := database.QueryRow(`
			SELECT IFNULL(s.id_prestataire, 0), s.nom, IFNULL(s.valide_admin, 0), IFNULL(c.valide_admin, 1)
			FROM service s
			LEFT JOIN categorie c ON c.id_categorie = s.id_categorie
			WHERE s.id_service = ?`, id).Scan(&idPrestataire, &serviceName, &valideS, &valideC)
		if err != nil {
			http.Error(response, "Service introuvable", http.StatusNotFound)
			return
		}
		if valideS == 0 || valideC == 0 {
			http.Error(response, "Service introuvable", http.StatusNotFound)
			return
		}
		if idPrestataire <= 0 {
			http.Error(response, "Aucun prestataire assigné à ce service", http.StatusBadRequest)
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
		var valideS, valideC int
		err = database.QueryRow(`SELECT IFNULL(s.id_prestataire, 0), IFNULL(s.valide_admin, 0), IFNULL(c.valide_admin, 1) FROM service s LEFT JOIN categorie c ON c.id_categorie = s.id_categorie WHERE s.id_service = ?`, id).Scan(&idPrestataire, &valideS, &valideC)
		if err != nil {
			http.Error(response, "Service introuvable", http.StatusNotFound)
			return
		}
		if valideS == 0 || valideC == 0 {
			http.Error(response, "Service introuvable", http.StatusNotFound)
			return
		}
		if idPrestataire <= 0 {
			http.Error(response, "Aucun prestataire assigné à ce service", http.StatusBadRequest)
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

type serviceReservationPrecheck struct {
	ServiceName          string
	FinalTarif           float64
	IDPrestataire        int
	LinkedDevisID        int
	LinkedInterventionID int
	Start                time.Time
}

func precheckServiceReservation(database *sql.DB, idUser int, idService int, startInput string) (*serviceReservationPrecheck, int, error) {
	var idPrestataire int
	var serviceName string
	var serviceTarif float64
	var valideS, valideC int
	if err := database.QueryRow(`
		SELECT s.nom, s.id_prestataire, IFNULL(s.tarif, 0), IFNULL(s.valide_admin, 0), IFNULL(c.valide_admin, 1)
		FROM service s
		LEFT JOIN categorie c ON c.id_categorie = s.id_categorie
		WHERE s.id_service = ?`, idService).Scan(&serviceName, &idPrestataire, &serviceTarif, &valideS, &valideC); err != nil {
		return nil, http.StatusNotFound, errors.New("service introuvable")
	}
	if valideS == 0 || valideC == 0 {
		return nil, http.StatusNotFound, errors.New("service introuvable")
	}
	if idPrestataire <= 0 {
		return nil, http.StatusBadRequest, errors.New("aucun prestataire assigné à ce service")
	}

	finalTarif := serviceTarif
	var linkedDevisID int
	var linkedInterventionID int
	var devisTarif sql.NullFloat64
	err := database.QueryRow(`
		SELECT d.id_devis, d.id_intervention, d.tarif_personalise
		FROM devis d
		JOIN intervention i ON i.id_intervention = d.id_intervention
		WHERE d.id_utilisateur = ?
		  AND i.id_service = ?
		  AND IFNULL(d.status, '') = 'accepté'
		  AND i.id_rdv IS NULL
		ORDER BY d.id_devis DESC
		LIMIT 1
	`, idUser, idService).Scan(&linkedDevisID, &linkedInterventionID, &devisTarif)
	if err == nil && devisTarif.Valid {
		finalTarif = devisTarif.Float64
	} else if err != nil && err != sql.ErrNoRows {
		return nil, http.StatusInternalServerError, errors.New("erreur lors de la récupération du devis")
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
		return nil, http.StatusBadRequest, errors.New("format de date/heure invalide")
	}
	end := start.Add(time.Hour)

	var count int
	err = database.QueryRow("SELECT COUNT(*) FROM disponibilite WHERE id_prestataire = ? AND (statut = 'disponible' OR statut IS NULL) AND type_regle = 'disponible' AND date = ? AND heure_debut <= ? AND heure_fin >= ?", idPrestataire, start.Format("2006-01-02"), start.Format("15:04:00"), end.Format("15:04:00")).Scan(&count)
	if err != nil {
		return nil, http.StatusInternalServerError, errors.New("erreur lors de la vérification des disponibilités")
	}
	if count == 0 {
		return nil, http.StatusBadRequest, errors.New("créneau non disponible")
	}

	err = database.QueryRow(
		"SELECT COUNT(*) FROM rendez_vous WHERE id_prestataire = ? AND NOT (date_fin <= ? OR date_debut >= ?)",
		idPrestataire, start.Format("2006-01-02 15:04:05"), end.Format("2006-01-02 15:04:05"),
	).Scan(&count)
	if err != nil {
		return nil, http.StatusInternalServerError, errors.New("erreur lors de la vérification des rendez-vous existants")
	}
	if count > 0 {
		return nil, http.StatusBadRequest, errors.New("ce créneau est déjà réservé")
	}

	return &serviceReservationPrecheck{
		ServiceName:          serviceName,
		FinalTarif:           finalTarif,
		IDPrestataire:        idPrestataire,
		LinkedDevisID:        linkedDevisID,
		LinkedInterventionID: linkedInterventionID,
		Start:                start,
	}, http.StatusOK, nil
}

func reservationAlreadyExists(database *sql.DB, idUser int, idService int, start time.Time) (bool, error) {
	var count int
	err := database.QueryRow(`
		SELECT COUNT(*)
		FROM rendez_vous rv
		JOIN intervention i ON i.id_rdv = rv.id_rdv
		WHERE rv.id_utilisateur = ? AND i.id_service = ? AND rv.date_debut = ?
	`, idUser, idService, start.Format("2006-01-02 15:04:05")).Scan(&count)
	if err != nil {
		return false, err
	}
	return count > 0, nil
}

func reserveServiceSlot(database *sql.DB, idUser int, idService int, startInput string) (string, int, error) {
	pc, status, err := precheckServiceReservation(database, idUser, idService, startInput)
	if err != nil {
		return "", status, err
	}

	serviceName := pc.ServiceName
	finalTarif := pc.FinalTarif
	idPrestataire := pc.IDPrestataire
	linkedDevisID := pc.LinkedDevisID
	linkedInterventionID := pc.LinkedInterventionID
	start := pc.Start
	end := start.Add(time.Hour)

	insertRef, err := database.Prepare("INSERT INTO reference_service (id_utilisateur, id_service) VALUES (?, ?)")
	if err == nil {
		_, _ = insertRef.Exec(idUser, idService)
	}

	insertRdv, err := database.Prepare("INSERT INTO rendez_vous (id_utilisateur, id_prestataire, date_debut, date_fin, type, statut) VALUES (?, ?, ?, ?, ?, 'confirmé')")
	if err != nil {
		return "", http.StatusInternalServerError, errors.New("erreur lors de la création du rendez-vous")
	}
	rdvResult, err := insertRdv.Exec(idUser, idPrestataire, start.Format("2006-01-02 15:04:05"), end.Format("2006-01-02 15:04:05"), serviceName)
	if err != nil {
		return "", http.StatusInternalServerError, errors.New("erreur lors de l'insertion du rendez-vous")
	}

	idRdv, err := rdvResult.LastInsertId()
	if err != nil {
		return "", http.StatusInternalServerError, errors.New("erreur lors de la récupération du rendez-vous")
	}

	var prestataireNullable sql.NullInt64
	if idPrestataire > 0 {
		prestataireNullable = sql.NullInt64{Int64: int64(idPrestataire), Valid: true}
	}

	insertIntervention, err := database.Prepare("INSERT INTO intervention (id_service, id_prestataire, id_utilisateur, id_rdv, statut, montant) VALUES (?, ?, ?, ?, 'confirmé', ?)")
	if err != nil {
		return "", http.StatusInternalServerError, errors.New("erreur lors de la création de l'intervention")
	}
	if linkedInterventionID > 0 {
		_, err = database.Exec(
			"UPDATE intervention SET id_rdv = ?, statut = 'confirmé', montant = ?, id_prestataire = ? WHERE id_intervention = ?",
			idRdv,
			finalTarif,
			prestataireNullable,
			linkedInterventionID,
		)
		if err != nil {
			return "", http.StatusInternalServerError, errors.New("erreur lors de la liaison de l'intervention au rendez-vous")
		}

		_, err = database.Exec("UPDATE devis SET status = 'accepté' WHERE id_devis = ?", linkedDevisID)
		if err != nil {
			return "", http.StatusInternalServerError, errors.New("erreur lors de la mise à jour du devis lié")
		}
	} else {
		_, err = insertIntervention.Exec(idService, prestataireNullable, idUser, idRdv, finalTarif)
		if err != nil {
			return "", http.StatusInternalServerError, errors.New("erreur lors de l'insertion de l'intervention")
		}
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
		IDService int `json:"id_service"`
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
		if err != nil || body.IDService <= 0 {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Données invalides"})
			return
		}

		var serviceName string
		var idPrestataire int
		var tarif float64
		var valideS, valideC int
		err = database.QueryRow(`
			SELECT s.nom, IFNULL(s.id_prestataire, 0), IFNULL(s.tarif, 0), IFNULL(s.valide_admin, 0), IFNULL(c.valide_admin, 1)
			FROM service s
			LEFT JOIN categorie c ON c.id_categorie = s.id_categorie
			WHERE s.id_service = ?`, body.IDService).Scan(&serviceName, &idPrestataire, &tarif, &valideS, &valideC)
		if err != nil {
			response.WriteHeader(http.StatusNotFound)
			json.NewEncoder(response).Encode(map[string]string{"message": "Service introuvable"})
			return
		}
		if valideS == 0 || valideC == 0 {
			response.WriteHeader(http.StatusNotFound)
			json.NewEncoder(response).Encode(map[string]string{"message": "Service introuvable"})
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

		var prestataireNullable sql.NullInt64
		if idPrestataire > 0 {
			prestataireNullable = sql.NullInt64{Int64: int64(idPrestataire), Valid: true}
		}

		var existingDevisID int
		err = tx.QueryRow(`
			SELECT d.id_devis
			FROM devis d
			JOIN intervention i ON i.id_intervention = d.id_intervention
			WHERE d.id_utilisateur = ?
			  AND i.id_service = ?
			  AND IFNULL(d.status, '') = 'en_attente'
			  AND i.id_rdv IS NULL
			ORDER BY d.id_devis DESC
			LIMIT 1
		`, idUser, body.IDService).Scan(&existingDevisID)
		if err == nil {
			response.WriteHeader(http.StatusConflict)
			json.NewEncoder(response).Encode(map[string]any{"message": "Un devis est déjà en attente pour ce service", "id_devis": existingDevisID})
			return
		}
		if err != nil && !errors.Is(err, sql.ErrNoRows) {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur vérification devis existant"})
			return
		}

		resInter, err := tx.Exec(
			"INSERT INTO intervention (id_service, id_prestataire, id_utilisateur, id_rdv, statut, montant) VALUES (?, ?, ?, NULL, 'devis', ?)",
			body.IDService, prestataireNullable, idUser, tarif,
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

		if err = tx.Commit(); err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur validation transaction"})
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"message":  "Devis créé avec succès",
			"id_devis": idDevis,
			"service":  serviceName,
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
		var role string
		err := database.QueryRow("SELECT id_utilisateur, role FROM utilisateur WHERE token = ?", token).Scan(&idUser, &role)
		if err != nil {
			response.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(response).Encode(map[string]string{"message": "Utilisateur introuvable"})
			return
		}

		var idPrestataireCaller int
		if role == "prestataire" {
			_ = database.QueryRow("SELECT id_prestataire FROM prestataire WHERE id_utilisateur = ?", idUser).Scan(&idPrestataireCaller)
		}

		baseQuery := `
			SELECT d.id_devis, IFNULL(i.id_service, 0), IFNULL(s.nom, ''), IFNULL(CONCAT(u.prenom, ' ', u.nom), ''), IFNULL(d.tarif_personalise, 0), IFNULL(d.status, ''),
			IFNULL(COALESCE(DATE_FORMAT(rdv.date_debut, '%Y-%m-%d %H:%i:%s'), (SELECT DATE_FORMAT(rdv2.date_debut, '%Y-%m-%d %H:%i:%s') FROM intervention i2 INNER JOIN rendez_vous rdv2 ON rdv2.id_rdv = i2.id_rdv WHERE i2.id_utilisateur = d.id_utilisateur AND i2.id_service = i.id_service ORDER BY rdv2.date_debut DESC LIMIT 1)), ''),
			IFNULL(COALESCE(DATE_FORMAT(COALESCE(rdv.date_fin, DATE_ADD(rdv.date_debut, INTERVAL 1 HOUR)), '%Y-%m-%d %H:%i:%s'), (SELECT IFNULL(DATE_FORMAT(COALESCE(rdv2.date_fin, DATE_ADD(rdv2.date_debut, INTERVAL 1 HOUR)), '%Y-%m-%d %H:%i:%s'), '') FROM intervention i2 INNER JOIN rendez_vous rdv2 ON rdv2.id_rdv = i2.id_rdv WHERE i2.id_utilisateur = d.id_utilisateur AND i2.id_service = i.id_service ORDER BY rdv2.date_debut DESC LIMIT 1)), '')
			FROM devis d
			JOIN intervention i ON i.id_intervention = d.id_intervention
			LEFT JOIN service s ON s.id_service = i.id_service
			LEFT JOIN prestataire p ON p.id_prestataire = d.id_prestataire
			LEFT JOIN utilisateur u ON u.id_utilisateur = p.id_utilisateur
			LEFT JOIN rendez_vous rdv ON rdv.id_rdv = i.id_rdv`

		query := baseQuery + " WHERE d.id_utilisateur = ? ORDER BY d.id_devis DESC"
		queryArg := any(idUser)
		if role == "prestataire" && idPrestataireCaller > 0 {
			query = baseQuery + " WHERE d.id_prestataire = ? ORDER BY d.id_devis DESC"
			queryArg = idPrestataireCaller
		}

		rows, err := database.Query(query, queryArg)
		if err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur récupération des devis"})
			return
		}
		defer rows.Close()

		devisList := make([]structures.Devis, 0)
		for rows.Next() {
			var d structures.Devis
			if err := rows.Scan(&d.ID, &d.IDService, &d.NomService, &d.NomPrestataire, &d.Tarif, &d.Status, &d.DateDebut, &d.DateFin); err != nil {
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
			SELECT d.id_devis, IFNULL(i.id_service, 0), IFNULL(s.nom, ''), IFNULL(CONCAT(u.prenom, ' ', u.nom), ''), IFNULL(d.tarif_personalise, 0), IFNULL(d.status, ''),
			IFNULL(COALESCE(DATE_FORMAT(rdv.date_debut, '%Y-%m-%d %H:%i:%s'), (SELECT DATE_FORMAT(rdv2.date_debut, '%Y-%m-%d %H:%i:%s') FROM intervention i2 INNER JOIN rendez_vous rdv2 ON rdv2.id_rdv = i2.id_rdv WHERE i2.id_utilisateur = d.id_utilisateur AND i2.id_service = i.id_service ORDER BY rdv2.date_debut DESC LIMIT 1)), ''),
			IFNULL(COALESCE(DATE_FORMAT(COALESCE(rdv.date_fin, DATE_ADD(rdv.date_debut, INTERVAL 1 HOUR)), '%Y-%m-%d %H:%i:%s'), (SELECT IFNULL(DATE_FORMAT(COALESCE(rdv2.date_fin, DATE_ADD(rdv2.date_debut, INTERVAL 1 HOUR)), '%Y-%m-%d %H:%i:%s'), '') FROM intervention i2 INNER JOIN rendez_vous rdv2 ON rdv2.id_rdv = i2.id_rdv WHERE i2.id_utilisateur = d.id_utilisateur AND i2.id_service = i.id_service ORDER BY rdv2.date_debut DESC LIMIT 1)), '')
			FROM devis d
			JOIN intervention i ON i.id_intervention = d.id_intervention LEFT JOIN service s ON s.id_service = i.id_service LEFT JOIN prestataire p ON p.id_prestataire = d.id_prestataire LEFT JOIN utilisateur u ON u.id_utilisateur = p.id_utilisateur LEFT JOIN rendez_vous rdv ON rdv.id_rdv = i.id_rdv WHERE d.id_devis = ?`, id).Scan(
			&d.ID, &d.IDService, &d.NomService, &d.NomPrestataire, &d.Tarif, &d.Status, &d.DateDebut, &d.DateFin,
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

		d.CanModify = (isOwner || isAdmin) && d.Status == "en_attente"
		d.CanEditTarif = (isLinkedPresta || isAdmin) && d.Status == "refusé"

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
		isAdmin := role == "admin"

		if !isOwner && !isAdmin {
			response.WriteHeader(http.StatusForbidden)
			json.NewEncoder(response).Encode(map[string]string{"message": "Seul le client peut accepter ou refuser ce devis"})
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

		if body.Status == "accepté" {
			_, _ = database.Exec(
				"UPDATE intervention i JOIN devis d ON d.id_intervention = i.id_intervention SET i.montant = IFNULL(d.tarif_personalise, i.montant) WHERE d.id_devis = ?",
				id,
			)
		}

		json.NewEncoder(response).Encode(map[string]string{"message": "Devis " + body.Status + " avec succès"})
	}
}

func PatchDevisTarif(database *sql.DB) http.HandlerFunc {
	type payload struct {
		Tarif float64 `json:"tarif"`
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
		if err := json.NewDecoder(request.Body).Decode(&body); err != nil || body.Tarif <= 0 {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Tarif invalide"})
			return
		}

		var idPrestataireCaller int
		_ = database.QueryRow("SELECT id_prestataire FROM prestataire WHERE id_utilisateur = ?", idUser).Scan(&idPrestataireCaller)

		var currentStatus string
		var linkedPrestaID int
		var interventionID int
		var ownerID int
		err = database.QueryRow("SELECT IFNULL(status, ''), IFNULL(id_prestataire, 0), IFNULL(id_intervention, 0), IFNULL(id_utilisateur, 0) FROM devis WHERE id_devis = ?", id).Scan(&currentStatus, &linkedPrestaID, &interventionID, &ownerID)
		if err != nil {
			response.WriteHeader(http.StatusNotFound)
			json.NewEncoder(response).Encode(map[string]string{"message": "Devis introuvable"})
			return
		}

		isLinkedPresta := idPrestataireCaller > 0 && idPrestataireCaller == linkedPrestaID
		isAdmin := role == "admin"
		if !isLinkedPresta && !isAdmin {
			response.WriteHeader(http.StatusForbidden)
			json.NewEncoder(response).Encode(map[string]string{"message": "Accès refusé"})
			return
		}

		if currentStatus != "refusé" {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Seuls les devis refusés peuvent être modifiés"})
			return
		}

		tx, err := database.Begin()
		if err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur transaction"})
			return
		}
		defer tx.Rollback()

		_, err = tx.Exec("UPDATE devis SET tarif_personalise = ?, status = 'en_attente' WHERE id_devis = ?", body.Tarif, id)
		if err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur mise à jour du devis"})
			return
		}

		if interventionID > 0 {
			_, err = tx.Exec("UPDATE intervention SET montant = ?, statut = 'devis' WHERE id_intervention = ?", body.Tarif, interventionID)
			if err != nil {
				response.WriteHeader(http.StatusInternalServerError)
				json.NewEncoder(response).Encode(map[string]string{"message": "Erreur mise à jour de l'intervention"})
				return
			}
		}

		if err = tx.Commit(); err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur validation transaction"})
			return
		}

		if ownerID > 0 {
			var serviceName string
			if interventionID > 0 {
				_ = database.QueryRow("SELECT IFNULL(s.nom, '') FROM intervention i LEFT JOIN service s ON s.id_service = i.id_service WHERE i.id_intervention = ?", interventionID).Scan(&serviceName)
			}
			var prestataireNom string
			_ = database.QueryRow("SELECT IFNULL(CONCAT(prenom, ' ', nom), '') FROM utilisateur WHERE id_utilisateur = ?", idUser).Scan(&prestataireNom)

			titreNotif, contenuNotif := LireTemplate(database, "devis_modifie_prestataire", map[string]string{
				"service":     serviceName,
				"tarif":       fmt.Sprintf("%.2f €", body.Tarif),
				"prestataire": prestataireNom,
			})
			if titreNotif == "devis_modifie_prestataire" && contenuNotif == "" {
				titreNotif = "Votre devis a été modifié"
				contenuNotif = fmt.Sprintf("Le prestataire a proposé un nouveau tarif de %.2f € pour votre devis%s.", body.Tarif, func() string {
					if strings.TrimSpace(serviceName) == "" {
						return ""
					}
					return " (" + serviceName + ")"
				}())
			}
			_ = creerNotification(database, ownerID, titreNotif, contenuNotif)
		}

		json.NewEncoder(response).Encode(map[string]string{"message": "Devis modifié et renvoyé en attente"})
	}
}

func finalizePaidServiceReservation(database *sql.DB, idUser int, idService int, startCanonical string) (string, int, error) {
	pc, status, err := precheckServiceReservation(database, idUser, idService, startCanonical)
	if err != nil {
		return "", status, err
	}
	already, err := reservationAlreadyExists(database, idUser, idService, pc.Start)
	if err != nil {
		return "", http.StatusInternalServerError, errors.New("erreur lors de la vérification de la réservation")
	}
	if already {
		return "Réservation déjà enregistrée", http.StatusOK, nil
	}
	return reserveServiceSlot(database, idUser, idService, startCanonical)
}

func ReservationServicePay(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var body structures.RequetePaiementReservationService
		if err := json.NewDecoder(request.Body).Decode(&body); err != nil {
			http.Error(response, "Payload invalide", http.StatusBadRequest)
			return
		}

		pm := strings.ToLower(strings.TrimSpace(body.PaymentMethod))
		if pm == "" {
			pm = "stripe"
		}
		if pm != "stripe" && pm != "transfer" {
			http.Error(response, "Mode de paiement invalide (stripe ou transfer)", http.StatusBadRequest)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		var idUser int
		if err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser); err != nil {
			http.Error(response, "Token invalide", http.StatusUnauthorized)
			return
		}

		if pm == "transfer" {
			message, statusCode, reservationErr := reserveServiceSlot(database, idUser, body.IDService, body.Start)
			if reservationErr != nil {
				http.Error(response, reservationErr.Error(), statusCode)
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(map[string]any{
				"message":        message,
				"payment_method": "transfer",
				"virement": map[string]string{
					"iban":      "FR76 1234 5678 9012 3456 7890 123",
					"reference": "RESV-" + strconv.Itoa(idUser) + "-" + strconv.Itoa(body.IDService),
				},
			})
			return
		}

		pc, statusCode, err := precheckServiceReservation(database, idUser, body.IDService, body.Start)
		if err != nil {
			http.Error(response, err.Error(), statusCode)
			return
		}

		if pc.FinalTarif <= 0 {
			message, code, reservationErr := reserveServiceSlot(database, idUser, body.IDService, body.Start)
			if reservationErr != nil {
				http.Error(response, reservationErr.Error(), code)
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(map[string]any{
				"message":        message,
				"payment_method": "none",
				"montant":        0,
			})
			return
		}

		stripeSecret := os.Getenv("STRIPE_SECRET_KEY")
		if stripeSecret == "" {
			http.Error(response, "Configuration Stripe manquante", http.StatusInternalServerError)
			return
		}
		stripe.Key = stripeSecret

		baseURL := strings.TrimRight(os.Getenv("APP_BASE_URL"), "/")
		if baseURL == "" {
			baseURL = "http://localhost"
		}

		startCanonical := pc.Start.Format("2006-01-02 15:04:05")
		amountCents := int64(math.Round(pc.FinalTarif * 100))
		if amountCents < 1 {
			amountCents = 1
		}

		sessionParams := &stripe.CheckoutSessionParams{
			Mode:       stripe.String(string(stripe.CheckoutSessionModePayment)),
			SuccessURL: stripe.String(baseURL + "/reservation_success.php?session_id={CHECKOUT_SESSION_ID}"),
			CancelURL:  stripe.String(baseURL + "/cancel.php"),
			Metadata: map[string]string{
				"reservation_kind": "service",
				"id_utilisateur":   strconv.Itoa(idUser),
				"id_service":       strconv.Itoa(body.IDService),
				"start":            startCanonical,
			},
			LineItems: []*stripe.CheckoutSessionLineItemParams{
				{
					Quantity: stripe.Int64(1),
					PriceData: &stripe.CheckoutSessionLineItemPriceDataParams{
						Currency:   stripe.String("eur"),
						UnitAmount: stripe.Int64(amountCents),
						ProductData: &stripe.CheckoutSessionLineItemPriceDataProductDataParams{
							Name: stripe.String("Réservation : " + pc.ServiceName),
						},
					},
				},
			},
		}

		checkoutSession, stripeErr := stripesession.New(sessionParams)
		if stripeErr != nil {
			http.Error(response, "Erreur création session Stripe: "+stripeErr.Error(), http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"message":        "Redirection vers le paiement",
			"payment_method": "stripe",
			"url":            checkoutSession.URL,
			"session_id":     checkoutSession.ID,
			"montant":        pc.FinalTarif,
			"service":        pc.ServiceName,
			"start":          startCanonical,
		})
	}
}

func ConfirmReservationStripe(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var body structures.RequeteConfirmationReservationStripe
		if err := json.NewDecoder(request.Body).Decode(&body); err != nil || strings.TrimSpace(body.SessionID) == "" {
			http.Error(response, "session_id invalide", http.StatusBadRequest)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		var idUser int
		if err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser); err != nil {
			http.Error(response, "Token invalide", http.StatusUnauthorized)
			return
		}

		stripeSecret := os.Getenv("STRIPE_SECRET_KEY")
		if stripeSecret == "" {
			http.Error(response, "Configuration Stripe manquante", http.StatusInternalServerError)
			return
		}
		stripe.Key = stripeSecret

		sess, err := stripesession.Get(body.SessionID, nil)
		if err != nil {
			http.Error(response, "Session Stripe introuvable", http.StatusBadRequest)
			return
		}

		if sess.Metadata == nil || sess.Metadata["reservation_kind"] != "service" {
			http.Error(response, "Session invalide pour une réservation service", http.StatusBadRequest)
			return
		}

		idUserMeta, err1 := strconv.Atoi(sess.Metadata["id_utilisateur"])
		idSvc, err2 := strconv.Atoi(sess.Metadata["id_service"])
		start := strings.TrimSpace(sess.Metadata["start"])
		if err1 != nil || err2 != nil || start == "" {
			http.Error(response, "Métadonnées de réservation invalides", http.StatusBadRequest)
			return
		}

		if idUserMeta != idUser {
			http.Error(response, "Session ne correspond pas à l'utilisateur", http.StatusForbidden)
			return
		}

		if string(sess.PaymentStatus) != string(stripe.CheckoutSessionPaymentStatusPaid) {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(map[string]any{
				"message":          "Paiement non finalisé",
				"payment_status":   sess.PaymentStatus,
				"reservation_done": false,
			})
			return
		}

		message, statusCode, reservationErr := finalizePaidServiceReservation(database, idUserMeta, idSvc, start)
		if reservationErr != nil {
			http.Error(response, reservationErr.Error(), statusCode)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"message":          message,
			"reservation_done": true,
		})
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

		pc, statusCode, precheckErr := precheckServiceReservation(database, idUser, p.ID, p.Start)
		if precheckErr != nil {
			http.Error(response, precheckErr.Error(), statusCode)
			return
		}
		if pc.FinalTarif > 0 {
			http.Error(response, "Ce service est payant : utilisez le paiement (carte ou virement) depuis la page de réservation.", http.StatusPaymentRequired)
			return
		}

		message, code, reservationErr := reserveServiceSlot(database, idUser, p.ID, p.Start)
		if reservationErr != nil {
			http.Error(response, reservationErr.Error(), code)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: message})
	}
}
