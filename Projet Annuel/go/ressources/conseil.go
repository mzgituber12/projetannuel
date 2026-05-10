package ressources

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"projet/structures"
)

func Conseils(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query("SELECT id_conseil, titre, contenu, image, date_publication FROM conseil")
		if err != nil {
			http.Error(response, "Erreur lors de la selection des conseils de la base de données", http.StatusInternalServerError)
			return
		} else {
			var conseils []structures.Conseil

			for rows.Next() {
				var c structures.Conseil

				var dateSQL string
				err := rows.Scan(&c.ID, &c.Titre, &c.Contenu, &c.Image, &dateSQL)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des conseils", http.StatusInternalServerError)
					return
				}

				t, err := parseDateTimeFlexible(dateSQL)
				if err != nil {
					http.Error(response, "Erreur lors de la selection de la date de création des conseils", http.StatusInternalServerError)
					return
				}
				c.Date = t.Format("02/01/2006 15:04")
				conseils = append(conseils, c)
			}
			if len(conseils) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun conseil pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Conseil: conseils,
			})
		}
	}
}

func Conseil_id(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		id := request.PathValue("id")

		selectstatement, selecterr := database.Prepare("SELECT id_conseil, titre, contenu, image, date_publication FROM conseil WHERE id_conseil = ? LIMIT 1")
		if selecterr != nil {
			http.Error(response, "Erreur lors de la récupération du conseil", http.StatusInternalServerError)
			return
		}

		var c structures.Conseil
		var dateSQL string
		err := selectstatement.QueryRow(id).Scan(&c.ID, &c.Titre, &c.Contenu, &c.Image, &dateSQL)
		if err != nil {
			if err == sql.ErrNoRows {
				http.Error(response, "Conseil introuvable", http.StatusNotFound)
				return
			}
			http.Error(response, "Erreur lors de la récupération du conseil", http.StatusInternalServerError)
			return
		}

		t, err := parseDateTimeFlexible(dateSQL)
		if err != nil {
			http.Error(response, "Erreur lors du traitement de la date", http.StatusInternalServerError)
			return
		}
		c.Date = t.Format("02/01/2006 15:04")

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(c)
	}
}

func ConseilNote(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var id int
		var token = request.Header.Get("Token")
		selected, err := database.Prepare("SELECT id_utilisateur FROM utilisateur WHERE token = ?")
		if err != nil {
			http.Error(response, "Erreur de requete", http.StatusNotFound)
			return
		}
		err = selected.QueryRow(token).Scan(&id)

		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusNotFound)
			return
		}

		var note structures.Note

		json.NewDecoder(request.Body).Decode(&note)

		var id_conseil = note.ID_conseil
		var n = note.Note

		var nbr float64
		var somme float64

		err = database.QueryRow(`SELECT COALESCE(SUM(note), 0) FROM conseil_note WHERE id_conseil = ?`, id_conseil).Scan(&somme)
		err2 := database.QueryRow(`SELECT COALESCE(COUNT(note), 0) FROM conseil_note WHERE id_conseil = ?`, id_conseil).Scan(&nbr)

		if err != nil {
			if err != sql.ErrNoRows {
				http.Error(response, "Erreur de calcul de la moyenne", http.StatusInternalServerError)
				return
			} else {
				somme = 0
			}
		}

		if err2 != nil {
			if err2 != sql.ErrNoRows {
				http.Error(response, "Erreur de calcul de la moyenne", http.StatusInternalServerError)
				return
			} else {
				nbr = 0
			}
		}

		var moyenne float64

		if nbr == 0 {
			moyenne = -1
		} else {
			moyenne = somme / nbr
		}

		var verif int
		err = database.QueryRow(`SELECT note FROM conseil_note WHERE id_utilisateur = ? AND id_conseil = ?`, id, id_conseil).Scan(&verif)
		if err != nil && err != sql.ErrNoRows {
			http.Error(response, "Erreur de requete lors du select", http.StatusInternalServerError)
			return
		}

		if err == nil {

			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Note{
				Message: "L'utilisateur a déjà voté",
				Note:    verif,
				Moyenne: moyenne,
			})
			return
		}

		if n < 0 {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Note{
				Message: "L'utilisateur n'a pas voté",
				Moyenne: moyenne,
			})
			return
		}

		avis, err := database.Prepare("INSERT INTO conseil_note (id_utilisateur, id_conseil, note) VALUES (?, ?, ?)")
		if err != nil {
			http.Error(response, "Erreur de requete lors de l'insert", http.StatusInternalServerError)
			return
		}
		avis.Exec(id, id_conseil, n)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Note{
			Message: "Vote envoyé avec succès",
			Moyenne: moyenne,
		})
	}
}

func AnnulerNote(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "DELETE, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		var note structures.Note

		json.NewDecoder(request.Body).Decode(&note)

		var token = request.Header.Get("token")
		var id_user int

		err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&id_user)
		if err != nil {
			http.Error(response, "Erreur de requete lors du select", http.StatusInternalServerError)
			return
		}

		del, err := database.Prepare("DELETE FROM conseil_note WHERE id_utilisateur = ? AND id_conseil = ?")
		if err != nil {
			http.Error(response, "Erreur de requete lors du delete", http.StatusInternalServerError)
			return
		}
		del.Exec(id_user, note.ID_conseil)

		response.Header().Set("content-type", "application/json")
	}
}
