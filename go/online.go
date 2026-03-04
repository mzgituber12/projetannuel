package main

import (
	"database/sql"
	"encoding/json"
	"errors"
	"net/http"
)

func IsAuthenticated(database *sql.DB, token string) (bool, error) {
	statement, prepareError := database.Prepare("SELECT email FROM utilisateur WHERE token = ?")

	if prepareError != nil {
		return false, prepareError
	}

	row := statement.QueryRow(token)
	user := user{}
	scanError := row.Scan(&user.Email)

	if scanError != nil {
		if errors.Is(scanError, sql.ErrNoRows) {
			return false, nil
		}

		return false, scanError
	}

	return true, nil
}

func enligne(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")

		authenticated, _ := IsAuthenticated(database, token)

		if !authenticated {
			response.Header().Add("Content-Type", "application/json")

			_ = json.NewEncoder(response).Encode(result{
				Message: "Pas identifié",
			})
			return
		}

		catStmt, catError := database.Prepare("SELECT role, tutoriel FROM utilisateur WHERE token = ?")
		if catError != nil {
			http.Error(response, "Impossible d'acceder a la base de donnée, veuillez reessayer plus tard", http.StatusInternalServerError)
			return
		}

		row := catStmt.QueryRow(token)
		var role string
		var tutoriel int
		_ = row.Scan(&role, &tutoriel)

		response.Header().Add("Content-Type", "application/json")
		_ = json.NewEncoder(response).Encode(result{
			Role:     role,
			Tutoriel: tutoriel,
			Message:  "Identifié",
		})
	}
}
