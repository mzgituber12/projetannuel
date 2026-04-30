package authentification

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func Enligne(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Pas identifié",
			})
			return
		}

		catStmt, catError := database.Prepare(`
			SELECT 
				u.role, 
				u.tutoriel, 
				IFNULL(u.langue, 'fr'),
				IFNULL(u.statut_user, 'actif'),
				IFNULL(DATE_FORMAT(u.fin_susp, '%Y-%m-%d %H:%i:%s'), ''),
				IFNULL((
					SELECT s.motif
					FROM sanction s
					WHERE s.id_user = u.id_utilisateur AND s.active = 1
					ORDER BY s.date_crea DESC
					LIMIT 1
				), ''),
				IFNULL((
					SELECT s.type
					FROM sanction s
					WHERE s.id_user = u.id_utilisateur AND s.active = 1
					ORDER BY s.date_crea DESC
					LIMIT 1
				), '')
			FROM utilisateur u 
			WHERE u.token = ?
		`)
		if catError != nil {
			http.Error(response, "Impossible d'acceder a la base de donnée, veuillez reessayer plus tard", http.StatusInternalServerError)
			return
		}
		var role string
		var tutoriel int
		var langue string
		var statutUser string
		var finSusp string
		var motifSanction string
		var typeSanction string
		err := catStmt.QueryRow(token).Scan(&role, &tutoriel, &langue, &statutUser, &finSusp, &motifSanction, &typeSanction)
		if err != nil {
			if err == sql.ErrNoRows {
				response.Header().Set("Content-Type", "application/json")
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Pas identifié",
				})
				return
			}
			http.Error(response, "Erreur de requete de base de donnée", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Role:          role,
			Tutoriel:      tutoriel,
			Langue:        langue,
			StatutUser:    statutUser,
			FinSusp:       finSusp,
			MotifSanction: motifSanction,
			TypeSanction:  typeSanction,
			Message:       "Identifié",
		})
	}
}

func Get_tuto(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		var tutoValue int

		err := database.QueryRow("SELECT tutoriel FROM utilisateur WHERE token = ?", token).Scan(&tutoValue)

		if err != nil {
			http.Error(response, "Utilisateur non trouvé", http.StatusNotFound)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(tutoValue)
	}
}

func Fin_tuto(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")

		slect, err := database.Prepare("UPDATE utilisateur SET tutoriel = 0 WHERE token = ?")
		if err != nil {
			http.Error(response, "Utilisateur non trouvé", http.StatusNotFound)
			return
		}
		_, err = slect.Exec(token)

		if err != nil {
			http.Error(response, "Utilisateur non trouvé"+err.Error(), http.StatusNotFound)
			return
		}

		response.Header().Set("Content-Type", "application/json")
	}
}
