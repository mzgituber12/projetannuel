package ressources

import (
	"bytes"
	"context"
	"database/sql"
	"encoding/json"
	"net/http"
	"os/exec"
	"projet/structures"
	"strings"
	"time"
)

func deriveSexeFromPrenom(prenom string) string {
	p := strings.TrimSpace(strings.ToLower(prenom))
	if p == "" {
		return "F"
	}
	if strings.HasSuffix(p, "e") || strings.HasSuffix(p, "a") {
		return "F"
	}
	return "H"
}

func calculerAgeSimple(dateNaissance string) int {
	dateNaissance = strings.TrimSpace(dateNaissance)
	if dateNaissance == "" || dateNaissance == "0000-00-00" {
		return 65
	}

	dateParse, err := time.Parse("2006-01-02", dateNaissance)
	if err != nil {
		return 65
	}

	now := time.Now()
	age := now.Year() - dateParse.Year()
	if now.Month() < dateParse.Month() || (now.Month() == dateParse.Month() && now.Day() < dateParse.Day()) {
		age--
	}
	if age < 0 {
		return 65
	}
	return age
}

func obtenirFeaturesUtilisateurPourML(database *sql.DB, token string) (int, structures.MLPredictPayload, int, error) {
	var payload structures.MLPredictPayload
	var userID int
	var prenom string
	var role string
	var langue string
	var abonne int
	var dateNaissance string
	err := database.QueryRow("SELECT id_utilisateur, IFNULL(prenom, ''), IFNULL(role, ''), IFNULL(langue, 'fr'), IFNULL(abonnée, 0), IFNULL(DATE_FORMAT(date_naissance, '%Y-%m-%d'), '') FROM utilisateur WHERE token = ?", token).Scan(&userID, &prenom, &role, &langue, &abonne, &dateNaissance)
	if err != nil {
		return 0, payload, 0, err
	}

	if role != "adherant" {
		return 0, payload, http.StatusForbidden, sql.ErrNoRows
	}

	payload.Age = float64(calculerAgeSimple(dateNaissance))
	payload.Sexe = deriveSexeFromPrenom(prenom)
	payload.Langue = langue
	payload.EstAbonne = float64(abonne)

	var typeAbonnement string
	err = database.QueryRow(`
		SELECT IFNULL(a.type, 'None')
		FROM souscris_abonnement sa
		JOIN abonnement a ON a.id_abonnement = sa.id_abonnement
		WHERE sa.id_utilisateur = ? AND IFNULL(sa.validite, 1) = 1
		ORDER BY sa.date_souscription DESC
		LIMIT 1
	`, userID).Scan(&typeAbonnement)
	if err != nil {
		typeAbonnement = "None"
	}
	payload.TypeAbonnement = typeAbonnement

	var anciennete sql.NullInt64
	_ = database.QueryRow("SELECT TIMESTAMPDIFF(MONTH, MIN(date_souscription), NOW()) FROM souscris_abonnement WHERE id_utilisateur = ? ", userID).Scan(&anciennete)
	if anciennete.Valid {
		payload.AncienneteMois = float64(anciennete.Int64)
	}

	var score sql.NullFloat64
	_ = database.QueryRow("SELECT AVG(IFNULL(note, 0)) FROM evaluation WHERE id_utilisateur = ?", userID).Scan(&score)
	if score.Valid {
		payload.ScoreSatisfaction = score.Float64
	} else {
		payload.ScoreSatisfaction = 4.0
	}

	var nbInterventions sql.NullInt64
	var nbAnnulees sql.NullInt64
	var depense sql.NullFloat64
	_ = database.QueryRow("SELECT COUNT(*), SUM(CASE WHEN LOWER(IFNULL(statut, '')) = 'annulé' OR LOWER(IFNULL(statut, '')) = 'annule' THEN 1 ELSE 0 END), SUM(IFNULL(montant, 0)) FROM intervention WHERE id_utilisateur = ?", userID).Scan(&nbInterventions, &nbAnnulees, &depense)
	if nbInterventions.Valid {
		payload.NbInterventionsTotales = float64(nbInterventions.Int64)
	}
	if depense.Valid {
		payload.DepenseTotaleEstimee = depense.Float64
	}
	if nbInterventions.Valid && nbInterventions.Int64 > 0 && nbAnnulees.Valid {
		payload.TauxAnnulation = float64(nbAnnulees.Int64) / float64(nbInterventions.Int64)
	}

	return userID, payload, http.StatusOK, nil
}

func appelerPredictionML(payload structures.MLPredictPayload) (*structures.MLPredictResponse, error) {
	body, err := json.Marshal(payload)
	if err != nil {
		return nil, err
	}

	contextePrediction, cancel := context.WithTimeout(context.Background(), 12*time.Second)
	defer cancel()
	cmd := exec.CommandContext(contextePrediction, "python3", "/app/ml/predict_cli.py")
	cmd.Stdin = bytes.NewReader(body)
	sortiePython, err := cmd.Output()
	if err != nil {
		return nil, err
	}

	var mlResp structures.MLPredictResponse
	if err := json.NewDecoder(bytes.NewReader(sortiePython)).Decode(&mlResp); err != nil {
		return nil, err
	}
	return &mlResp, nil
}

func ServicesRecommandes(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		response.Header().Set("Content-Type", "application/json")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}
		if request.Method != http.MethodGet {
			http.Error(response, "Méthode non autorisée", http.StatusMethodNotAllowed)
			return
		}

		token := strings.TrimSpace(request.Header.Get("Token"))
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		_, payload, statusCode, err := obtenirFeaturesUtilisateurPourML(database, token)
		if err != nil {
			switch statusCode {
			case http.StatusForbidden:
				http.Error(response, "Recommandation réservée aux adhérants", http.StatusForbidden)
			default:
				http.Error(response, "Impossible de récupérer les données utilisateur", http.StatusUnauthorized)
			}
			return
		}

		mlResp, err := appelerPredictionML(payload)
		if err != nil {
			http.Error(response, "Service de prédiction ML indisponible", http.StatusServiceUnavailable)
			return
		}

		if err := json.NewEncoder(response).Encode(map[string]any{
			"principal":    mlResp.Principal,
			"alternatives": mlResp.Alternatives,
			"fallback":     false,
			"features": map[string]any{
				"age":                     payload.Age,
				"sexe":                    payload.Sexe,
				"type_abonnement":         payload.TypeAbonnement,
				"langue":                  payload.Langue,
				"anciennete_mois":         payload.AncienneteMois,
				"score_satisfaction":      payload.ScoreSatisfaction,
				"taux_annulation":         payload.TauxAnnulation,
				"nb_interventions_totales": payload.NbInterventionsTotales,
				"depense_totale_estimee":  payload.DepenseTotaleEstimee,
				"est_abonne":              payload.EstAbonne,
			},
		}); err != nil {
			http.Error(response, "Réponse invalide", http.StatusInternalServerError)
			return
		}
	}
}

