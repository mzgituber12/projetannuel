package ressources

import (
	"database/sql"
	"encoding/json"
	"io"
	"net/http"
	"os"
	"strconv"
	"strings"
	"time"

	"projet/structures"

	"github.com/stripe/stripe-go/v83"
	checkoutsession "github.com/stripe/stripe-go/v83/checkout/session"
	"github.com/stripe/stripe-go/v83/customer"
	stripesubscription "github.com/stripe/stripe-go/v83/subscription"
	"github.com/stripe/stripe-go/v83/webhook"
)

func creerContratAdherent(database *sql.DB, idUser int, idAbonnement int, typePaiement string) error {
	var typePrestataire int
	var typeAbonnement string
	err := database.QueryRow("SELECT IFNULL(type_prestataire, 0), IFNULL(type, '') FROM abonnement WHERE id_abonnement = ?", idAbonnement).Scan(&typePrestataire, &typeAbonnement)
	if err != nil {
		return err
	}

	if typePrestataire != 0 {
		return nil
	}

	typePaiement = strings.TrimSpace(strings.ToLower(typePaiement))
	if typePaiement != "an" {
		typePaiement = "mois"
	}

	now := time.Now()
	dateDebut := now.Format("2006-01-02")
	dateFin := now.AddDate(0, 1, 0)
	if typePaiement == "an" {
		dateFin = now.AddDate(1, 0, 0)
	}

	var existingID int
	err = database.QueryRow(`
		SELECT id_contrat
		FROM contrat
		WHERE id_utilisateur = ?
		  AND type_contrat = 'site'
		  AND nom = ?
		  AND date_debut = ?
		ORDER BY id_contrat DESC
		LIMIT 1
	`, idUser, "Contrat abonnement "+typeAbonnement, dateDebut).Scan(&existingID)
	if err == nil {
		return nil
	}
	if err != sql.ErrNoRows {
		return err
	}

	_, err = database.Exec(`
		INSERT INTO contrat (id_devis, id_utilisateur, id_prestataire, date_debut, date_fin, nom, type_paiement, type_contrat)
		VALUES (NULL, ?, NULL, ?, ?, ?, ?, 'site')
	`, idUser, dateDebut, dateFin.Format("2006-01-02"), "Contrat abonnement "+typeAbonnement, typePaiement)

	return err
}

func supprimerContratAdherent(database *sql.DB, idUser int, idAbonnement int) error {
	var typePrestataire int
	var typeAbonnement string
	err := database.QueryRow("SELECT IFNULL(type_prestataire, 0), IFNULL(type, '') FROM abonnement WHERE id_abonnement = ?", idAbonnement).Scan(&typePrestataire, &typeAbonnement)
	if err != nil {
		return err
	}

	if typePrestataire != 0 {
		return nil
	}

	_, err = database.Exec(`
		DELETE FROM contrat
		WHERE id_utilisateur = ?
		  AND type_contrat = 'site'
		  AND nom = ?
		ORDER BY id_contrat DESC
		LIMIT 1
	`, idUser, "Contrat abonnement "+typeAbonnement)

	return err
}

func ListAbonnements(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query(`SELECT id_abonnement, type, prix_mois, prix_an, statut,
			IFNULL(Locaux_prestation,0), IFNULL(Trajet_offert,0), IFNULL(offre_repas,0), IFNULL(mis_en_avant,0)
			FROM abonnement WHERE statut = 'actif' AND type_prestataire != 1`)
		if err != nil {
			response.Header().Set("Content-Type", "application/json")
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur récupération abonnements"})
			return
		}

		var abonnements []structures.Abonnement

		for rows.Next() {
			var a structures.Abonnement
			err := rows.Scan(&a.ID, &a.Type, &a.PrixMois, &a.PrixAn, &a.Statut,
				&a.LocauxPrestation, &a.TrajetOffert, &a.OffreRepas, &a.MisEnAvant)
			if err != nil {
				response.Header().Set("Content-Type", "application/json")
				response.WriteHeader(http.StatusInternalServerError)
				json.NewEncoder(response).Encode(map[string]string{"message": "Erreur lecture abonnements"})
				return
			}
			abonnements = append(abonnements, a)
		}

		if len(abonnements) == 0 {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{
				Message: "Aucun abonnement disponible",
			})
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.List{
			Abonnement: abonnements,
		})
	}
}

func SouscrireAbonnement(database *sql.DB) http.HandlerFunc {
	type subscribePayload struct {
		IDAbonnement int    `json:"id_abonnement"`
		TypePaiement string `json:"type_paiement"`
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

		var payload subscribePayload
		err := json.NewDecoder(request.Body).Decode(&payload)
		if err != nil || payload.IDAbonnement <= 0 {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Données invalides"})
			return
		}

		if payload.TypePaiement != "mois" && payload.TypePaiement != "an" {
			payload.TypePaiement = "mois"
		}

		var idUser int
		err = database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			response.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(response).Encode(map[string]string{"message": "Utilisateur introuvable"})
			return
		}

		var prixMois, prixAn float64
		var typeName string
		err = database.QueryRow("SELECT type, prix_mois, prix_an FROM abonnement WHERE id_abonnement = ? AND statut = 'actif'", payload.IDAbonnement).Scan(&typeName, &prixMois, &prixAn)
		if err != nil {
			response.WriteHeader(http.StatusNotFound)
			json.NewEncoder(response).Encode(map[string]string{"message": "Abonnement introuvable"})
			return
		}

		var existingSubID int
		err = database.QueryRow("SELECT id_souscrit FROM souscris_abonnement WHERE id_utilisateur = ? AND validite = 1", idUser).Scan(&existingSubID)
		if err == nil {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Vous avez déjà un abonnement actif"})
			return
		}

		stripeSecret := os.Getenv("STRIPE_SECRET_KEY")
		if stripeSecret == "" {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Configuration Stripe manquante (SECRET_KEY)"})
			return
		}
		stripe.Key = stripeSecret

		baseURL := os.Getenv("APP_BASE_URL")
		if baseURL == "" {
			baseURL = "http://localhost"
		}

		priceIDMois := os.Getenv("STRIPE_PRICE_ID_MOIS")
		priceIDAn := os.Getenv("STRIPE_PRICE_ID_AN")

		if priceIDMois == "" || priceIDAn == "" {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Configuration Stripe des prix manquante (PRICE_ID_MOIS ou PRICE_ID_AN)"})
			return
		}

		var stripeCustomerID string
		err = database.QueryRow("SELECT stripe_customer_id FROM souscris_abonnement WHERE id_utilisateur = ? LIMIT 1", idUser).Scan(&stripeCustomerID)
		if err != nil && err != sql.ErrNoRows {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur lecture client Stripe"})
			return
		}

		if err == sql.ErrNoRows || stripeCustomerID == "" {
			var userEmail string
			var firstName, lastName string
			userErr := database.QueryRow("SELECT email, prenom, nom FROM utilisateur WHERE id_utilisateur = ?", idUser).Scan(&userEmail, &firstName, &lastName)
			if userErr != nil {
				response.WriteHeader(http.StatusInternalServerError)
				json.NewEncoder(response).Encode(map[string]string{"message": "Erreur lecture profil utilisateur"})
				return
			}

			params := &stripe.CustomerParams{
				Email: stripe.String(userEmail),
				Name:  stripe.String(firstName + " " + lastName),
				Metadata: map[string]string{
					"user_id": strconv.Itoa(idUser),
				},
			}
			cust, stripeErr := customer.New(params)
			if stripeErr != nil {
				response.WriteHeader(http.StatusInternalServerError)
				json.NewEncoder(response).Encode(map[string]string{"message": "Erreur création client Stripe: " + stripeErr.Error()})
				return
			}
			stripeCustomerID = cust.ID
		}

		selectedPriceID := priceIDMois
		if payload.TypePaiement == "an" {
			selectedPriceID = priceIDAn
		}

		successURL := baseURL + "/mon_abonnement.php?checkout=success&session_id={CHECKOUT_SESSION_ID}"
		cancelURL := baseURL + "/abonnement.php?checkout=cancel"

		sessionParams := &stripe.CheckoutSessionParams{
			Mode:              stripe.String(string(stripe.CheckoutSessionModeSubscription)),
			Customer:          stripe.String(stripeCustomerID),
			ClientReferenceID: stripe.String(strconv.Itoa(idUser)),
			SuccessURL:        stripe.String(successURL),
			CancelURL:         stripe.String(cancelURL),
			LineItems: []*stripe.CheckoutSessionLineItemParams{
				{
					Price:    stripe.String(selectedPriceID),
					Quantity: stripe.Int64(1),
				},
			},
			SubscriptionData: &stripe.CheckoutSessionSubscriptionDataParams{
				Metadata: map[string]string{
					"subscription_type": payload.TypePaiement,
					"abonnement_id":     strconv.Itoa(payload.IDAbonnement),
					"user_id":           strconv.Itoa(idUser),
				},
			},
			Metadata: map[string]string{
				"subscription_type": payload.TypePaiement,
				"abonnement_id":     strconv.Itoa(payload.IDAbonnement),
				"user_id":           strconv.Itoa(idUser),
			},
		}

		checkoutSession, stripeErr := checkoutsession.New(sessionParams)
		if stripeErr != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur création session Stripe Checkout: " + stripeErr.Error()})
			return
		}

		_, dbErr := database.Exec(`
			INSERT INTO souscris_abonnement (id_utilisateur, id_abonnement, type_paiement, validite, stripe_customer_id, stripe_subscription_id)
			VALUES (?, ?, ?, 1, ?, NULL)
		`, idUser, payload.IDAbonnement, payload.TypePaiement, stripeCustomerID)
		if dbErr != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur sauvegarde souscription"})
			return
		}

		montant := prixMois
		if payload.TypePaiement == "an" {
			montant = prixAn
		}

		_, dbErr = database.Exec(`
			INSERT INTO paiement_abonnement (id_abonnement, montant, date, mode, statut)
			VALUES (?, ?, NOW(), 'stripe', ?)
		`, payload.IDAbonnement, montant, "pending")
		if dbErr != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur création paiement"})
			return
		}

		_ = creerContratAdherent(database, idUser, payload.IDAbonnement, payload.TypePaiement)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"message":       "Session Checkout créée",
			"checkout_url":  checkoutSession.URL,
			"checkout_id":   checkoutSession.ID,
			"type_paiement": payload.TypePaiement,
			"montant":       montant,
		})
	}
}

func MonAbonnement(database *sql.DB) http.HandlerFunc {
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

		var souscription structures.Souscription
		var abonnement structures.Abonnement
		var validiteInt int

		err = database.QueryRow(`
			SELECT sa.id_souscrit, sa.id_utilisateur, sa.id_abonnement, sa.date_souscription, 
				   IFNULL(sa.date_expiration, ''), sa.type_paiement, sa.validite, 
				   IFNULL(sa.stripe_customer_id, ''), IFNULL(sa.stripe_subscription_id, ''),
				   a.type, a.prix_mois, a.prix_an
			FROM souscris_abonnement sa
			JOIN abonnement a ON a.id_abonnement = sa.id_abonnement
			WHERE sa.id_utilisateur = ?
			ORDER BY sa.date_souscription DESC LIMIT 1
		`, idUser).Scan(
			&souscription.ID, &souscription.IDUtilisateur, &souscription.IDAbonnement,
			&souscription.DateSouscription, &souscription.DateExpiration, &souscription.TypePaiement,
			&validiteInt, &souscription.StripeCustomerID, &souscription.StripeSubID,
			&abonnement.Type, &abonnement.PrixMois, &abonnement.PrixAn,
		)

		souscription.Validite = validiteInt == 1

		if err != nil {
			if err == sql.ErrNoRows {
				response.WriteHeader(http.StatusNotFound)
				json.NewEncoder(response).Encode(map[string]string{"message": "Aucun abonnement actif"})
				return
			}
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur lecture abonnement"})
			return
		}

		if souscription.Validite {
			_ = creerContratAdherent(database, idUser, souscription.IDAbonnement, souscription.TypePaiement)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"souscription": souscription,
			"abonnement":   abonnement,
		})
	}
}

func WebhookAbonnement(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Stripe-Signature")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		webhookSecret := os.Getenv("STRIPE_WEBHOOK_SECRET")
		if webhookSecret == "" {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Configuration webhook Stripe manquante"})
			return
		}

		payload, err := io.ReadAll(request.Body)
		if err != nil {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Impossible de lire le body webhook"})
			return
		}

		signatureHeader := request.Header.Get("Stripe-Signature")
		if signatureHeader == "" {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Signature webhook manquante"})
			return
		}

		event, err := webhook.ConstructEvent(payload, signatureHeader, webhookSecret)
		if err != nil {
			response.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(response).Encode(map[string]string{"message": "Signature webhook invalide"})
			return
		}

		switch event.Type {
		case "checkout.session.completed":
			var checkoutSession struct {
				ID                string            `json:"id"`
				Customer          string            `json:"customer"`
				Subscription      string            `json:"subscription"`
				ClientReferenceID string            `json:"client_reference_id"`
				Metadata          map[string]string `json:"metadata"`
			}
			err = json.Unmarshal(event.Data.Raw, &checkoutSession)
			if err != nil {
				response.WriteHeader(http.StatusBadRequest)
				json.NewEncoder(response).Encode(map[string]string{"message": "Événement checkout invalide"})
				return
			}

			userIDStr := checkoutSession.ClientReferenceID
			if userIDStr == "" && checkoutSession.Metadata != nil {
				userIDStr = checkoutSession.Metadata["user_id"]
			}

			if checkoutSession.Customer != "" && checkoutSession.Subscription != "" {
				abonnementID := ""
				typePaiement := "mois"
				if checkoutSession.Metadata != nil {
					abonnementID = checkoutSession.Metadata["abonnement_id"]
					typePaiement = checkoutSession.Metadata["subscription_type"]
				}

				if userIDInt, convErr := strconv.Atoi(userIDStr); convErr == nil {
					database.Exec(`
						UPDATE souscris_abonnement
						SET stripe_subscription_id = ?, validite = 1
						WHERE id_utilisateur = ? AND stripe_customer_id = ? AND validite = 0
						ORDER BY id_souscrit DESC
						LIMIT 1
					`, checkoutSession.Subscription, userIDInt, checkoutSession.Customer)

					if abonnementIDInt, convAboErr := strconv.Atoi(abonnementID); convAboErr == nil {
						_ = creerContratAdherent(database, userIDInt, abonnementIDInt, typePaiement)
					}
				} else {
					database.Exec(`
						UPDATE souscris_abonnement
						SET stripe_subscription_id = ?, validite = 1
						WHERE stripe_customer_id = ? AND validite = 0
						ORDER BY id_souscrit DESC
						LIMIT 1
					`, checkoutSession.Subscription, checkoutSession.Customer)
				}

				if checkoutSession.Metadata != nil {
					if abonnementID := checkoutSession.Metadata["abonnement_id"]; abonnementID != "" {
						database.Exec(`
							UPDATE paiement_abonnement
							SET statut = 'active', date = NOW()
							WHERE id_abonnement = ? AND statut = 'pending'
							ORDER BY id_paiement_abonnement DESC
							LIMIT 1
						`, abonnementID)
					}
				}
			}

		case "customer.subscription.created":
			var sub stripe.Subscription
			if jsonErr := json.Unmarshal(event.Data.Raw, &sub); jsonErr != nil {
				response.WriteHeader(http.StatusBadRequest)
				json.NewEncoder(response).Encode(map[string]string{"message": "Événement invalide"})
				return
			}

			userID := sub.Metadata["user_id"]
			abonnementID := sub.Metadata["abonnement_id"]
			subType := sub.Metadata["subscription_type"]

			if sub.Customer != nil {
				database.Exec(`
					UPDATE souscris_abonnement
					SET stripe_subscription_id = ?, validite = 1
					WHERE stripe_customer_id = ? AND validite = 0
					ORDER BY id_souscrit DESC
					LIMIT 1
				`, sub.ID, sub.Customer.ID)
			}

			if userID != "" && abonnementID != "" {
				database.Exec(`
					UPDATE paiement_abonnement 
					SET statut = 'active', date = NOW()
					WHERE id_abonnement = ? AND statut = 'pending'
					ORDER BY id_paiement_abonnement DESC
					LIMIT 1
				`, abonnementID)

				userIDInt, userConvErr := strconv.Atoi(userID)
				abonnementIDInt, aboConvErr := strconv.Atoi(abonnementID)
				if userConvErr == nil && aboConvErr == nil {
					_ = creerContratAdherent(database, userIDInt, abonnementIDInt, subType)

					titreNotif, contenuNotif := LireTemplate(database, "abonnement_active", map[string]string{
						"type": subType,
					})
					_ = creerNotification(database, userIDInt, titreNotif, contenuNotif)
				}
			}

		case "customer.subscription.updated":
			var sub stripe.Subscription
			if jsonErr := json.Unmarshal(event.Data.Raw, &sub); jsonErr != nil {
				response.WriteHeader(http.StatusBadRequest)
				json.NewEncoder(response).Encode(map[string]string{"message": "Événement invalide"})
				return
			}

			if sub.CancelAtPeriodEnd {
				database.Exec(`
					UPDATE souscris_abonnement 
					SET validite = 0
					WHERE stripe_subscription_id = ?
				`, sub.ID)
			}

		case "customer.subscription.deleted":
			var sub stripe.Subscription
			if jsonErr := json.Unmarshal(event.Data.Raw, &sub); jsonErr != nil {
				response.WriteHeader(http.StatusBadRequest)
				json.NewEncoder(response).Encode(map[string]string{"message": "Événement invalide"})
				return
			}

			var idUser int
			var idAbonnement int
			_ = database.QueryRow(`SELECT id_utilisateur, id_abonnement FROM souscris_abonnement WHERE stripe_subscription_id = ? ORDER BY id_souscrit DESC LIMIT 1`, sub.ID).Scan(&idUser, &idAbonnement)

			database.Exec(`
				UPDATE souscris_abonnement 
				SET validite = 0, date_expiration = NOW()
				WHERE stripe_subscription_id = ?
			`, sub.ID)

			if idUser > 0 && idAbonnement > 0 {
				_ = supprimerContratAdherent(database, idUser, idAbonnement)
			}

			database.Exec(`
				UPDATE paiement_abonnement 
				SET statut = 'canceled'
				WHERE statut = 'active'
			`)

		case "invoice.payment_succeeded":
			database.Exec(`
				UPDATE paiement_abonnement 
				SET statut = 'paid', date = NOW()
				WHERE statut = 'pending' OR statut = 'active'
			`)

		case "invoice.payment_failed":
			database.Exec(`
				UPDATE paiement_abonnement 
				SET statut = 'failed'
				WHERE statut = 'pending' OR statut = 'active'
			`)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: "Webhook traité", Value: 1})
	}
}

func NotifPushBienvenueAbonnement(database *sql.DB) http.HandlerFunc {
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
		if err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser); err != nil {
			response.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(response).Encode(map[string]string{"message": "Utilisateur introuvable"})
			return
		}

		var subType string
		err := database.QueryRow(`
			SELECT a.type
			FROM souscris_abonnement sa
			JOIN abonnement a ON a.id_abonnement = sa.id_abonnement
			WHERE sa.id_utilisateur = ? AND sa.validite = 1
			  AND sa.date_souscription > NOW() - INTERVAL 24 HOUR
			ORDER BY sa.id_souscrit DESC LIMIT 1
		`, idUser).Scan(&subType)
		if err != nil {
			json.NewEncoder(response).Encode(map[string]any{"message": "Aucun abonnement récent", "value": 0})
			return
		}

		var activePushCount int
		if err = database.QueryRow(`
			SELECT COUNT(*)
			FROM abonnement_push
			WHERE id_utilisateur = ? AND actif = 1
		`, idUser).Scan(&activePushCount); err != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]any{"message": "Erreur lecture abonnement push", "value": 0})
			return
		}

		if activePushCount == 0 {
			json.NewEncoder(response).Encode(map[string]any{"message": "Aucune subscription push active", "value": 0})
			return
		}

		titre, contenu := LireTemplate(database, "abonnement_active", map[string]string{
			"type": subType,
		})
		_ = EnvoyerNotificationPushOneSignal(database, []int{idUser}, titre, contenu)

		json.NewEncoder(response).Encode(map[string]any{"message": "Push envoyé", "value": 1})
	}
}

func CancelAbonnement(database *sql.DB) http.HandlerFunc {
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

		var stripeSubID string
		var idSouscrit int
		var idAbonnement int
		err = database.QueryRow(`
			SELECT id_souscrit, id_abonnement, IFNULL(stripe_subscription_id, '')
			FROM souscris_abonnement
			WHERE id_utilisateur = ?
			ORDER BY id_souscrit DESC LIMIT 1
		`, idUser).Scan(&idSouscrit, &idAbonnement, &stripeSubID)
		if err != nil {
			response.WriteHeader(http.StatusNotFound)
			json.NewEncoder(response).Encode(map[string]string{"message": "Aucun abonnement trouvé"})
			return
		}

		stripeSecret := os.Getenv("STRIPE_SECRET_KEY")
		if stripeSecret == "" {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Configuration Stripe manquante"})
			return
		}
		stripe.Key = stripeSecret

		if stripeSubID != "" {
			_, stripeErr := stripesubscription.Cancel(stripeSubID, nil)
			if stripeErr != nil {
				response.WriteHeader(http.StatusInternalServerError)
				json.NewEncoder(response).Encode(map[string]string{"message": "Erreur annulation Stripe: " + stripeErr.Error()})
				return
			}
		}

		_, dbErr := database.Exec(`
			UPDATE souscris_abonnement
			SET validite = 0, date_expiration = NOW()
			WHERE id_souscrit = ?
		`, idSouscrit)
		if dbErr != nil {
			response.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(response).Encode(map[string]string{"message": "Erreur mise à jour base de données"})
			return
		}

		database.Exec(`
			UPDATE paiement_abonnement pa
			JOIN souscris_abonnement sa ON sa.id_abonnement = pa.id_abonnement
			SET pa.statut = 'canceled'
			WHERE sa.id_souscrit = ? AND pa.statut IN ('active', 'pending')
		`, idSouscrit)

		if idAbonnement > 0 {
			_ = supprimerContratAdherent(database, idUser, idAbonnement)
		}

		json.NewEncoder(response).Encode(map[string]string{"message": "Abonnement annulé avec succès"})
	}
}
