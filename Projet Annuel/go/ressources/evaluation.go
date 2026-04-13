package ressources

import (
	"database/sql"
	"encoding/json"
	"errors"
	"net/http"
	"strconv"
	"strings"

	"projet/structures"
)

func EvaluationsService(database *sql.DB) http.HandlerFunc {
	type payload struct {
		Note        int    `json:"note"`
		Commentaire string `json:"commentaire"`
	}

	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		response.Header().Set("Content-Type", "application/json")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		serviceID, err := strconv.Atoi(request.PathValue("id"))
		if err != nil || serviceID <= 0 {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Service invalide"})
			return
		}

		token := strings.TrimSpace(request.Header.Get("Token"))
		if token == "" {
			response.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(response).Encode(map[string]string{"message": "Token manquant"})
			return
		}

		var idUser int
		var role string
		err = database.QueryRow("SELECT id_utilisateur, role FROM utilisateur WHERE token = ?", token).Scan(&idUser, &role)
		if err != nil {
			response.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(response).Encode(map[string]string{"message": "Utilisateur introuvable"})
			return
		}

		if request.Method == http.MethodGet {
			resume, statusCode, err := construireResumeEvaluation(database, serviceID, idUser, role)
			if err != nil {
				response.WriteHeader(statusCode)
				json.NewEncoder(response).Encode(map[string]string{"message": err.Error()})
				return
			}
			json.NewEncoder(response).Encode(resume)
			return
		}

		if request.Method != http.MethodPost {
			response.WriteHeader(http.StatusMethodNotAllowed)
			json.NewEncoder(response).Encode(map[string]string{"message": "Méthode non autorisée"})
			return
		}

		if role != "adherant" {
			response.WriteHeader(http.StatusForbidden)
			json.NewEncoder(response).Encode(map[string]string{"message": "Seuls les seniors peuvent laisser une évaluation"})
			return
		}

		canReview, err := userCanReviewService(database, idUser, serviceID)
		if err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur lors de la vérification du droit d'évaluer"})
			return
		}
		if !canReview {
			response.WriteHeader(http.StatusForbidden)
			json.NewEncoder(response).Encode(map[string]string{"message": "Cette prestation ne peut pas encore être évaluée"})
			return
		}

		var body payload
		if err := json.NewDecoder(request.Body).Decode(&body); err != nil {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Corps de requête invalide"})
			return
		}

		commentaire := strings.TrimSpace(body.Commentaire)
		if body.Note < 1 || body.Note > 5 {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "La note doit être comprise entre 1 et 5"})
			return
		}
		if len(commentaire) > 1000 {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Le commentaire est trop long"})
			return
		}

		var existingID int
		err = database.QueryRow(
			"SELECT id_evaluation FROM evaluation WHERE id_utilisateur = ? AND id_service = ? ORDER BY id_evaluation DESC LIMIT 1",
			idUser, serviceID,
		).Scan(&existingID)
		if err != nil && err != sql.ErrNoRows {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur lecture évaluation"})
			return
		}

		if err == sql.ErrNoRows {
			_, err = database.Exec(
				"INSERT INTO evaluation (id_utilisateur, id_service, note, commentaire, date) VALUES (?, ?, ?, ?, CURDATE())",
				idUser, serviceID, body.Note, commentaire,
			)
		} else {
			_, err = database.Exec(
				"UPDATE evaluation SET note = ?, commentaire = ?, date = CURDATE() WHERE id_evaluation = ?",
				body.Note, commentaire, existingID,
			)
		}
		if err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur enregistrement évaluation"})
			return
		}

		resume, statusCode, err := construireResumeEvaluation(database, serviceID, idUser, role)
		if err != nil {
			response.WriteHeader(statusCode)
			json.NewEncoder(response).Encode(map[string]string{"message": err.Error()})
			return
		}

		json.NewEncoder(response).Encode(resume)
	}
}

func construireResumeEvaluation(database *sql.DB, serviceID int, idUser int, role string) (structures.EvaluationResume, int, error) {
	var resume structures.EvaluationResume
	resume.IDService = serviceID
	
	var serviceExists int
	if err := database.QueryRow("SELECT COUNT(*) FROM service WHERE id_service = ?", serviceID).Scan(&serviceExists); err != nil {
		return resume, http.StatusInternalServerError, err
	}
	if serviceExists == 0 {
		return resume, http.StatusNotFound, errors.New("Service introuvable")
	}

	if err := database.QueryRow("SELECT IFNULL(AVG(note), 0), COUNT(*) FROM evaluation WHERE id_service = ? AND note IS NOT NULL", serviceID).Scan(&resume.AverageRating, &resume.TotalReviews); err != nil {
		return resume, http.StatusInternalServerError, err
	}

	resume.CanReview = role == "adherant"
	if resume.CanReview {
		canReview, err := userCanReviewService(database, idUser, serviceID)
		if err != nil {
			return resume, http.StatusInternalServerError, err
		}
		resume.CanReview = canReview
	}

	var userReview structures.Evaluation
	userReviewErr := database.QueryRow("SELECT e.id_evaluation, e.id_utilisateur, CONCAT(IFNULL(u.prenom, ''), ' ', IFNULL(u.nom, '')), IFNULL(e.note, 0), IFNULL(e.commentaire, ''), IFNULL(DATE_FORMAT(e.date, '%Y-%m-%d'), '') FROM evaluation e JOIN utilisateur u ON u.id_utilisateur = e.id_utilisateur WHERE e.id_utilisateur = ? AND e.id_service = ? ORDER BY e.id_evaluation DESC LIMIT 1", idUser, serviceID).Scan(&userReview.IDEvaluation, &userReview.IDUtilisateur, &userReview.NomAuteur, &userReview.Note, &userReview.Commentaire, &userReview.Date)
	if userReviewErr == nil {
		resume.UserReview = &userReview
	} else if userReviewErr != sql.ErrNoRows {
		return resume, http.StatusInternalServerError, userReviewErr
	}

	rows, err := database.Query("SELECT e.id_evaluation, e.id_utilisateur, CONCAT(IFNULL(u.prenom, ''), ' ', IFNULL(u.nom, '')), IFNULL(e.note, 0), IFNULL(e.commentaire, ''), IFNULL(DATE_FORMAT(e.date, '%Y-%m-%d'), '') FROM evaluation e JOIN utilisateur u ON u.id_utilisateur = e.id_utilisateur WHERE e.id_service = ? ORDER BY e.date DESC, e.id_evaluation DESC LIMIT 10", serviceID)
	if err != nil {
		return resume, http.StatusInternalServerError, err
	}
	defer rows.Close()

	for rows.Next() {
		var review structures.Evaluation
		if err := rows.Scan(&review.IDEvaluation, &review.IDUtilisateur, &review.NomAuteur, &review.Note, &review.Commentaire, &review.Date); err != nil {
			return resume, http.StatusInternalServerError, err
		}
		resume.Reviews = append(resume.Reviews, review)
	}

	return resume, http.StatusOK, nil
}

func userCanReviewService(database *sql.DB, idUser int, serviceID int) (bool, error) {
	var count int
	err := database.QueryRow("SELECT COUNT(*) FROM devis d JOIN intervention i ON i.id_intervention = d.id_intervention LEFT JOIN rendez_vous rdv ON rdv.id_rdv = i.id_rdv WHERE d.id_utilisateur = ? AND i.id_service = ? AND (LOWER(IFNULL(i.statut, '')) IN ('termine', 'terminé') OR (rdv.date_fin IS NOT NULL AND rdv.date_fin <= NOW()))", idUser, serviceID).Scan(&count)
	if err != nil {
		return false, err
	}
	return count > 0, nil
}

func EvaluationsPrestataire(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		response.Header().Set("Content-Type", "application/json")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}
		if request.Method != http.MethodGet {
			response.WriteHeader(http.StatusMethodNotAllowed)
			json.NewEncoder(response).Encode(map[string]string{"message": "Méthode non autorisée"})
			return
		}

		token := strings.TrimSpace(request.Header.Get("Token"))
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

		if role != "prestataire" && role != "admin" {
			response.WriteHeader(http.StatusForbidden)
			json.NewEncoder(response).Encode(map[string]string{"message": "Accès réservé aux prestataires"})
			return
		}

		var idPrestataire int
		err = database.QueryRow("SELECT id_prestataire FROM prestataire WHERE id_utilisateur = ?", idUser).Scan(&idPrestataire)
		if err != nil {
			response.WriteHeader(http.StatusNotFound)
			json.NewEncoder(response).Encode(map[string]string{"message": "Profil prestataire introuvable"})
			return
		}

		rows, err := database.Query("SELECT s.id_service, s.nom, IFNULL(AVG(e.note), 0), COUNT(e.id_evaluation) FROM service s LEFT JOIN evaluation e ON e.id_service = s.id_service WHERE s.id_prestataire = ? GROUP BY s.id_service, s.nom ORDER BY s.nom", idPrestataire)
		if err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur lecture services"})
			return
		}
		defer rows.Close()

		result := []structures.EvaluationParService{}
		for rows.Next() {
			var eps structures.EvaluationParService
			if err := rows.Scan(&eps.IDService, &eps.NomService, &eps.AverageRating, &eps.TotalReviews); err != nil {
				response.WriteHeader(http.StatusInternalServerError)
				json.NewEncoder(response).Encode(map[string]string{"message": "Erreur lecture données"})
				return
			}
			result = append(result, eps)
		}

		for i, eps := range result {
			reviewRows, err := database.Query("SELECT e.id_evaluation, e.id_utilisateur,CONCAT(IFNULL(u.prenom, ''), ' ', IFNULL(u.nom, '')), IFNULL(e.note, 0), IFNULL(e.commentaire, ''), IFNULL(DATE_FORMAT(e.date, '%Y-%m-%d'), '') FROM evaluation e JOIN utilisateur u ON u.id_utilisateur = e.id_utilisateur WHERE e.id_service = ? ORDER BY e.date DESC, e.id_evaluation DESC", eps.IDService)
			if err != nil {
				response.WriteHeader(http.StatusInternalServerError)
				json.NewEncoder(response).Encode(map[string]string{"message": "Erreur lecture avis"})
				return
			}
			for reviewRows.Next() {
				var rev structures.Evaluation
				if err := reviewRows.Scan(&rev.IDEvaluation, &rev.IDUtilisateur, &rev.NomAuteur, &rev.Note, &rev.Commentaire, &rev.Date); err != nil {
					reviewRows.Close()
					response.WriteHeader(http.StatusInternalServerError)
					json.NewEncoder(response).Encode(map[string]string{"message": "Erreur lecture avis détail"})
					return
				}
				result[i].Reviews = append(result[i].Reviews, rev)
			}
			reviewRows.Close()
		}
		json.NewEncoder(response).Encode(result)
	}
}
