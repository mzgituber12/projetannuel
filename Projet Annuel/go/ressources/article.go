package ressources

import (
	"database/sql"
	"encoding/json"
	"io"
	"math"
	"net/http"
	"os"
	"strconv"
	"strings"

	"projet/structures"

	"github.com/stripe/stripe-go/v83"
	"github.com/stripe/stripe-go/v83/checkout/session"
	"github.com/stripe/stripe-go/v83/webhook"
)

func verifierTableReferenceArticle(database *sql.DB) error {
	_, err := database.Exec(`
		CREATE TABLE IF NOT EXISTS reference_article (
			id INT AUTO_INCREMENT PRIMARY KEY,
			id_utilisateur INT NOT NULL,
			id_panier INT NOT NULL,
			id_article INT NOT NULL,
			UNIQUE KEY uniq_user_panier_article (id_utilisateur, id_panier, id_article)
		)
	`)
	return err
}

func Articles(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {

		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		rows, err := database.Query("SELECT id_article, titre, IFNULL(image, '') AS image, description, prix FROM article")
		if err != nil {
			http.Error(response, "Erreur lors de la selection des articles de la base de données", http.StatusInternalServerError)
			return
		} else {
			var articles []structures.Article

			for rows.Next() {
				var a structures.Article

				err := rows.Scan(&a.ID, &a.Titre, &a.Image, &a.Description, &a.Prix)
				if err != nil {
					http.Error(response, "Erreur lors de la selection des articles : "+err.Error(), http.StatusInternalServerError)
					return
				}

				articles = append(articles, a)
			}
			if len(articles) == 0 {
				json.NewEncoder(response).Encode(structures.Result{
					Message: "Aucun article pour le moment",
				})
				return
			}
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.List{
				Article: articles,
			})
		}
	}
}

func Article_id(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		id := request.PathValue("id")

		selectStatement, selectErr := database.Prepare("SELECT id_article, titre, IFNULL(image, '') AS image, description, prix FROM article WHERE id_article = ? ")
		if selectErr != nil {
			http.Error(response, "Erreur lors de la récupération de l'article", http.StatusInternalServerError)
			return
		}

		var a structures.Article
		err := selectStatement.QueryRow(id).Scan(&a.ID, &a.Titre, &a.Image, &a.Description, &a.Prix)
		if err != nil {
			if err == sql.ErrNoRows {
				http.Error(response, "Article introuvable", http.StatusNotFound)
				return
			}
			http.Error(response, "Erreur lors de la récupération de l'article", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(a)
	}
}

func AjouterArticlePanier(database *sql.DB) http.HandlerFunc {
	type addArticlePayload struct {
		IDArticle int `json:"id_article"`
	}

	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		var payload addArticlePayload
		err := json.NewDecoder(request.Body).Decode(&payload)
		if err != nil || payload.IDArticle <= 0 {
			http.Error(response, "Données invalides", http.StatusBadRequest)
			return
		}

		var idUser int
		err = database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}

		var articleExists int
		err = database.QueryRow("SELECT COUNT(*) FROM article WHERE id_article = ?", payload.IDArticle).Scan(&articleExists)
		if err != nil || articleExists == 0 {
			http.Error(response, "Article introuvable", http.StatusNotFound)
			return
		}

		err = verifierTableReferenceArticle(database)
		if err != nil {
			http.Error(response, "Erreur initialisation panier", http.StatusInternalServerError)
			return
		}

		var panierID int
		err = database.QueryRow("SELECT id_panier FROM panier WHERE id_utilisateur = ? AND statut = 'actif' ORDER BY id_panier DESC LIMIT 1", idUser).Scan(&panierID)
		if err != nil {
			if err != sql.ErrNoRows {
				http.Error(response, "Erreur panier", http.StatusInternalServerError)
				return
			}

			insertPanier, insertErr := database.Prepare("INSERT INTO panier (id_utilisateur, date_creation, statut) VALUES (?, NOW(), 'actif')")
			if insertErr != nil {
				http.Error(response, "Erreur création panier", http.StatusInternalServerError)
				return
			}
			insertResult, execErr := insertPanier.Exec(idUser)
			if execErr != nil {
				http.Error(response, "Erreur création panier", http.StatusInternalServerError)
				return
			}

			lastID, idErr := insertResult.LastInsertId()
			if idErr != nil {
				http.Error(response, "Erreur création panier", http.StatusInternalServerError)
				return
			}
			panierID = int(lastID)
		}

		insertRef, insertRefErr := database.Prepare("INSERT IGNORE INTO reference_article (id_utilisateur, id_panier, id_article) VALUES (?, ?, ?)")
		if insertRefErr != nil {
			http.Error(response, "Erreur ajout article panier", http.StatusInternalServerError)
			return
		}
		_, execRefErr := insertRef.Exec(idUser, panierID, payload.IDArticle)
		if execRefErr != nil {
			http.Error(response, "Erreur ajout article panier", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{
			Message: "Article ajouté au panier",
			Value:   1,
		})
	}
}

func EtatArticlePanier(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		articleID := request.PathValue("id")
		if articleID == "" {
			http.Error(response, "Article invalide", http.StatusBadRequest)
			return
		}

		err := verifierTableReferenceArticle(database)
		if err != nil {
			http.Error(response, "Erreur initialisation panier", http.StatusInternalServerError)
			return
		}

		var idUser int
		err = database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}

		var count int
		err = database.QueryRow(`
			SELECT COUNT(*)
			FROM reference_article ra
			JOIN panier p ON p.id_panier = ra.id_panier
			WHERE ra.id_utilisateur = ? AND ra.id_article = ? AND p.statut = 'actif'
		`, idUser, articleID).Scan(&count)
		if err != nil {
			http.Error(response, "Erreur lecture panier", http.StatusInternalServerError)
			return
		}

		value := 0
		message := "Article non présent dans le panier"
		if count > 0 {
			value = 1
			message = "Article présent dans le panier"
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: message, Value: value})
	}
}

func BasculerArticlePanier(database *sql.DB) http.HandlerFunc {
	type payload struct {
		IDArticle int `json:"id_article"`
	}

	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		var body payload
		err := json.NewDecoder(request.Body).Decode(&body)
		if err != nil || body.IDArticle <= 0 {
			http.Error(response, "Données invalides", http.StatusBadRequest)
			return
		}

		err = verifierTableReferenceArticle(database)
		if err != nil {
			http.Error(response, "Erreur initialisation panier", http.StatusInternalServerError)
			return
		}

		var idUser int
		err = database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}

		var panierID int
		err = database.QueryRow("SELECT id_panier FROM panier WHERE id_utilisateur = ? AND statut = 'actif' ORDER BY id_panier DESC LIMIT 1", idUser).Scan(&panierID)
		if err != nil {
			if err != sql.ErrNoRows {
				http.Error(response, "Erreur panier", http.StatusInternalServerError)
				return
			}

			insertPanier, insertErr := database.Prepare("INSERT INTO panier (id_utilisateur, date_creation, statut) VALUES (?, NOW(), 'actif')")
			if insertErr != nil {
				http.Error(response, "Erreur création panier", http.StatusInternalServerError)
				return
			}
			insertResult, execErr := insertPanier.Exec(idUser)
			if execErr != nil {
				http.Error(response, "Erreur création panier", http.StatusInternalServerError)
				return
			}
			lastID, idErr := insertResult.LastInsertId()
			if idErr != nil {
				http.Error(response, "Erreur création panier", http.StatusInternalServerError)
				return
			}
			panierID = int(lastID)
		}

		var refID int
		err = database.QueryRow("SELECT id FROM reference_article WHERE id_utilisateur = ? AND id_panier = ? AND id_article = ? LIMIT 1", idUser, panierID, body.IDArticle).Scan(&refID)
		if err == nil {
			deleteStmt, delErr := database.Prepare("DELETE FROM reference_article WHERE id = ?")
			if delErr != nil {
				http.Error(response, "Erreur suppression panier", http.StatusInternalServerError)
				return
			}
			_, delExecErr := deleteStmt.Exec(refID)
			if delExecErr != nil {
				http.Error(response, "Erreur suppression panier", http.StatusInternalServerError)
				return
			}

			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{Message: "Article retiré du panier", Value: 0})
			return
		}

		if err != sql.ErrNoRows {
			http.Error(response, "Erreur panier", http.StatusInternalServerError)
			return
		}

		insertStmt, insErr := database.Prepare("INSERT INTO reference_article (id_utilisateur, id_panier, id_article) VALUES (?, ?, ?)")
		if insErr != nil {
			http.Error(response, "Erreur ajout panier", http.StatusInternalServerError)
			return
		}
		_, insExecErr := insertStmt.Exec(idUser, panierID, body.IDArticle)
		if insExecErr != nil {
			http.Error(response, "Erreur ajout panier", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: "Article ajouté au panier", Value: 1})
	}
}

func ArticlesPanier(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		err := verifierTableReferenceArticle(database)
		if err != nil {
			http.Error(response, "Erreur initialisation panier", http.StatusInternalServerError)
			return
		}

		var idUser int
		err = database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}

		rows, queryErr := database.Query(`
			SELECT a.id_article, a.titre, a.description, a.prix
			FROM reference_article ra
			JOIN panier p ON p.id_panier = ra.id_panier
			JOIN article a ON a.id_article = ra.id_article
			WHERE ra.id_utilisateur = ? AND p.statut = 'actif'
			ORDER BY ra.id DESC
		`, idUser)
		if queryErr != nil {
			http.Error(response, "Erreur lecture panier", http.StatusInternalServerError)
			return
		}

		var articles []structures.Article
		for rows.Next() {
			var a structures.Article
			err = rows.Scan(&a.ID, &a.Titre, &a.Description, &a.Prix)
			if err != nil {
				http.Error(response, "Erreur lecture panier", http.StatusInternalServerError)
				return
			}
			articles = append(articles, a)
		}

		if len(articles) == 0 {
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{Message: "Votre panier est vide"})
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.List{Article: articles})
	}
}

func CreerCommande(database *sql.DB) http.HandlerFunc {
	type createOrderPayload struct {
		PaymentMethod string `json:"payment_method"`
	}

	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		var body createOrderPayload
		err := json.NewDecoder(request.Body).Decode(&body)
		if err != nil {
			http.Error(response, "Données invalides", http.StatusBadRequest)
			return
		}

		if body.PaymentMethod == "" {
			body.PaymentMethod = "transfer"
		}
		if body.PaymentMethod != "transfer" && body.PaymentMethod != "stripe" {
			http.Error(response, "Mode de paiement invalide", http.StatusBadRequest)
			return
		}

		err = verifierTableReferenceArticle(database)
		if err != nil {
			http.Error(response, "Erreur initialisation panier", http.StatusInternalServerError)
			return
		}

		var idUser int
		err = database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}

		var panierID int
		err = database.QueryRow("SELECT id_panier FROM panier WHERE id_utilisateur = ? AND statut = 'actif' ORDER BY id_panier DESC LIMIT 1", idUser).Scan(&panierID)
		if err != nil {
			http.Error(response, "Panier actif introuvable", http.StatusBadRequest)
			return
		}

		var total float64
		var itemCount int
		err = database.QueryRow(`
			SELECT IFNULL(SUM(a.prix), 0), COUNT(*)
			FROM reference_article ra
			JOIN article a ON a.id_article = ra.id_article
			WHERE ra.id_utilisateur = ? AND ra.id_panier = ?
		`, idUser, panierID).Scan(&total, &itemCount)
		if err != nil {
			http.Error(response, "Erreur lecture panier", http.StatusInternalServerError)
			return
		}
		if itemCount == 0 {
			http.Error(response, "Panier vide", http.StatusBadRequest)
			return
		}

		achatID := 0
		err = database.QueryRow("SELECT id_achat FROM achat WHERE id_panier = ?", panierID).Scan(&achatID)
		if err != nil {
			if err != sql.ErrNoRows {
				http.Error(response, "Erreur création commande", http.StatusInternalServerError)
				return
			}

			insertAchat, insAchatErr := database.Prepare("INSERT INTO achat (id_utilisateur, id_panier, date) VALUES (?, ?, NOW())")
			if insAchatErr != nil {
				http.Error(response, "Erreur création commande", http.StatusInternalServerError)
				return
			}
			achatResult, execAchatErr := insertAchat.Exec(idUser, panierID)
			if execAchatErr != nil {
				http.Error(response, "Erreur création commande", http.StatusInternalServerError)
				return
			}

			achatID64, lastIDErr := achatResult.LastInsertId()
			if lastIDErr != nil {
				http.Error(response, "Erreur création commande", http.StatusInternalServerError)
				return
			}
			achatID = int(achatID64)
		}

		paymentStatus := "pending"
		if body.PaymentMethod == "transfer" {
			paymentStatus = "pending_transfer"
		}

		var paymentID int
		err = database.QueryRow("SELECT id_paiement FROM paiement WHERE id_achat = ? LIMIT 1", achatID).Scan(&paymentID)
		if err != nil {
			if err != sql.ErrNoRows {
				http.Error(response, "Erreur création paiement", http.StatusInternalServerError)
				return
			}

			insertPaiement, insPayErr := database.Prepare("INSERT INTO paiement (id_achat, montant, date, mode, statut) VALUES (?, ?, NOW(), ?, ?)")
			if insPayErr != nil {
				http.Error(response, "Erreur création paiement", http.StatusInternalServerError)
				return
			}
			_, execPayErr := insertPaiement.Exec(achatID, total, body.PaymentMethod, paymentStatus)
			if execPayErr != nil {
				http.Error(response, "Erreur création paiement", http.StatusInternalServerError)
				return
			}
		} else {
			updatePaiement, updPayErr := database.Prepare("UPDATE paiement SET montant = ?, date = NOW(), mode = ?, statut = ? WHERE id_achat = ?")
			if updPayErr != nil {
				http.Error(response, "Erreur création paiement", http.StatusInternalServerError)
				return
			}
			_, execUpdPayErr := updatePaiement.Exec(total, body.PaymentMethod, paymentStatus, achatID)
			if execUpdPayErr != nil {
				http.Error(response, "Erreur création paiement", http.StatusInternalServerError)
				return
			}
		}

		panierStatus := "pending_transfer"
		if body.PaymentMethod == "stripe" {
			panierStatus = "pending_stripe"
		}
		_, _ = database.Exec("UPDATE panier SET statut = ? WHERE id_panier = ?", panierStatus, panierID)

		modePaiementLabel := "virement"
		if body.PaymentMethod == "stripe" {
			modePaiementLabel = "stripe"
		}
		titreNotif, contenuNotif := LireTemplate(database, "commande_creee", map[string]string{
			"id":   strconv.Itoa(achatID),
			"mode": modePaiementLabel,
		})
		_ = creerNotification(database, idUser, titreNotif, contenuNotif)

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]interface{}{
			"valeur":        1,
			"message":       "Commande créée",
			"id_commande":   achatID,
			"montant_total": total,
			"mode_paiement": body.PaymentMethod,
			"virement": map[string]string{
				"iban":      "FR76 1234 5678 9012 3456 7890 123",
				"reference": "CMD-" + strconv.Itoa(achatID),
			},
		})
	}
}

func CreerSessionPaiement(database *sql.DB) http.HandlerFunc {
	type createCheckoutPayload struct {
		OrderID int `json:"order_id"`
	}

	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		var idUser int
		err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}

		var body createCheckoutPayload
		err = json.NewDecoder(request.Body).Decode(&body)
		if err != nil || body.OrderID <= 0 {
			http.Error(response, "Commande invalide", http.StatusBadRequest)
			return
		}

		var total float64
		var mode string
		var status string
		err = database.QueryRow(`
			SELECT IFNULL(p.montant, 0), IFNULL(p.mode, ''), IFNULL(p.statut, '')
			FROM achat a
			LEFT JOIN paiement p ON p.id_achat = a.id_achat
			WHERE a.id_achat = ? AND a.id_utilisateur = ?
			ORDER BY p.id_paiement DESC
			LIMIT 1
		`, body.OrderID, idUser).Scan(&total, &mode, &status)
		if err != nil {
			http.Error(response, "Commande introuvable", http.StatusNotFound)
			return
		}

		if mode != "stripe" {
			http.Error(response, "Cette commande n'est pas en mode Stripe", http.StatusBadRequest)
			return
		}

		if status == "paid" {
			http.Error(response, "Commande déjà payée", http.StatusBadRequest)
			return
		}

		if total <= 0 {
			http.Error(response, "Montant invalide", http.StatusBadRequest)
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

		orderIDString := strconv.Itoa(body.OrderID)
		amountCents := int64(math.Round(total * 100))

		sessionParams := &stripe.CheckoutSessionParams{
			Mode:              stripe.String(string(stripe.CheckoutSessionModePayment)),
			SuccessURL:        stripe.String(baseURL + "/success.php?order_id=" + orderIDString + "&session_id={CHECKOUT_SESSION_ID}"),
			CancelURL:         stripe.String(baseURL + "/cancel.php?order_id=" + orderIDString),
			ClientReferenceID: stripe.String(orderIDString),
			Metadata: map[string]string{
				"order_id": orderIDString,
			},
			LineItems: []*stripe.CheckoutSessionLineItemParams{
				{
					Quantity: stripe.Int64(1),
					PriceData: &stripe.CheckoutSessionLineItemPriceDataParams{
						Currency:   stripe.String("eur"),
						UnitAmount: stripe.Int64(amountCents),
						ProductData: &stripe.CheckoutSessionLineItemPriceDataProductDataParams{
							Name: stripe.String("Commande #" + orderIDString),
						},
					},
				},
			},
		}

		checkoutSession, stripeErr := session.New(sessionParams)
		if stripeErr != nil {
			http.Error(response, "Erreur création session Stripe", http.StatusInternalServerError)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]interface{}{
			"message":    "Session Stripe créée",
			"session_id": checkoutSession.ID,
			"url":        checkoutSession.URL,
		})
	}
}

func WebhookPaiement(database *sql.DB) http.HandlerFunc {
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
			http.Error(response, "Configuration webhook Stripe manquante", http.StatusInternalServerError)
			return
		}

		payload, err := io.ReadAll(request.Body)
		if err != nil {
			http.Error(response, "Impossible de lire le body webhook", http.StatusBadRequest)
			return
		}

		signatureHeader := request.Header.Get("Stripe-Signature")
		if signatureHeader == "" {
			http.Error(response, "Signature webhook manquante", http.StatusBadRequest)
			return
		}

		event, err := webhook.ConstructEvent(payload, signatureHeader, webhookSecret)
		if err != nil {
			http.Error(response, "Signature webhook invalide", http.StatusBadRequest)
			return
		}

		orderID := 0
		status := ""

		switch event.Type {
		case "checkout.session.completed":
			var checkoutSession stripe.CheckoutSession
			if jsonErr := json.Unmarshal(event.Data.Raw, &checkoutSession); jsonErr != nil {
				http.Error(response, "Événement Stripe invalide", http.StatusBadRequest)
				return
			}

			orderIDString := checkoutSession.ClientReferenceID
			if orderIDString == "" {
				orderIDString = checkoutSession.Metadata["order_id"]
			}
			if orderIDString == "" {
				http.Error(response, "Commande introuvable dans l'événement", http.StatusBadRequest)
				return
			}

			parsedOrderID, convErr := strconv.Atoi(orderIDString)
			if convErr != nil || parsedOrderID <= 0 {
				http.Error(response, "Commande Stripe invalide", http.StatusBadRequest)
				return
			}

			orderID = parsedOrderID
			status = "paid"

		case "checkout.session.expired":
			var checkoutSession stripe.CheckoutSession
			if jsonErr := json.Unmarshal(event.Data.Raw, &checkoutSession); jsonErr != nil {
				http.Error(response, "Événement Stripe invalide", http.StatusBadRequest)
				return
			}

			orderIDString := checkoutSession.ClientReferenceID
			if orderIDString == "" {
				orderIDString = checkoutSession.Metadata["order_id"]
			}
			parsedOrderID, convErr := strconv.Atoi(orderIDString)
			if convErr != nil || parsedOrderID <= 0 {
				http.Error(response, "Commande Stripe invalide", http.StatusBadRequest)
				return
			}

			orderID = parsedOrderID
			status = "canceled"

		case "payment_intent.payment_failed":
			var paymentIntent stripe.PaymentIntent
			if jsonErr := json.Unmarshal(event.Data.Raw, &paymentIntent); jsonErr != nil {
				http.Error(response, "Événement Stripe invalide", http.StatusBadRequest)
				return
			}

			orderIDString := paymentIntent.Metadata["order_id"]
			parsedOrderID, convErr := strconv.Atoi(orderIDString)
			if convErr != nil || parsedOrderID <= 0 {
				http.Error(response, "Commande Stripe invalide", http.StatusBadRequest)
				return
			}

			orderID = parsedOrderID
			status = "failed"

		default:
			response.Header().Set("Content-Type", "application/json")
			json.NewEncoder(response).Encode(structures.Result{Message: "Événement ignoré", Value: 1})
			return
		}

		_, err = database.Exec("UPDATE paiement SET statut = ?, mode = 'stripe', date = NOW() WHERE id_achat = ?", status, orderID)
		if err != nil {
			http.Error(response, "Erreur mise à jour paiement", http.StatusInternalServerError)
			return
		}

		if status == "paid" {
			_, _ = database.Exec(`
				UPDATE panier p
				JOIN achat a ON a.id_panier = p.id_panier
				SET p.statut = 'paid'
				WHERE a.id_achat = ?
			`, orderID)
		}

		if status == "canceled" || status == "failed" {
			_, _ = database.Exec(`
				UPDATE panier p
				JOIN achat a ON a.id_panier = p.id_panier
				SET p.statut = 'actif'
				WHERE a.id_achat = ?
			`, orderID)
		}

		var idUser int
		if errUser := database.QueryRow("SELECT id_utilisateur FROM achat WHERE id_achat = ?", orderID).Scan(&idUser); errUser == nil {
			titreNotif, contenuNotif := LireTemplate(database, "paiement_mise_a_jour", map[string]string{
				"id":     strconv.Itoa(orderID),
				"statut": status,
			})
			_ = creerNotification(database, idUser, titreNotif, contenuNotif)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(structures.Result{Message: "Webhook traité", Value: 1})
	}
}

func FactureAchat(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		orderID := request.PathValue("id")
		if orderID == "" {
			http.Error(response, "Commande invalide", http.StatusBadRequest)
			return
		}

		var idUser int
		err := database.QueryRow("SELECT id_utilisateur FROM utilisateur WHERE token = ?", token).Scan(&idUser)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}

		var panierID int
		var orderDate string
		var mode string
		var status string
		var total float64

		err = database.QueryRow(`
			SELECT a.id_panier, a.date, IFNULL(p.mode,''), IFNULL(p.statut,''), IFNULL(p.montant,0)
			FROM achat a
			LEFT JOIN paiement p ON p.id_achat = a.id_achat
			WHERE a.id_achat = ? AND a.id_utilisateur = ?
			ORDER BY p.id_paiement DESC
			LIMIT 1
		`, orderID, idUser).Scan(&panierID, &orderDate, &mode, &status, &total)
		if err != nil {
			http.Error(response, "Facture introuvable", http.StatusNotFound)
			return
		}

		rows, queryErr := database.Query(`
			SELECT a.id_article, a.titre, a.description, a.prix
			FROM reference_article ra
			JOIN article a ON a.id_article = ra.id_article
			WHERE ra.id_utilisateur = ? AND ra.id_panier = ?
		`, idUser, panierID)
		if queryErr != nil {
			http.Error(response, "Erreur facture", http.StatusInternalServerError)
			return
		}

		var items []structures.Article
		for rows.Next() {
			var a structures.Article
			err = rows.Scan(&a.ID, &a.Titre, &a.Description, &a.Prix)
			if err != nil {
				http.Error(response, "Erreur facture", http.StatusInternalServerError)
				return
			}
			items = append(items, a)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]interface{}{
			"order_id": orderID,
			"date":     orderDate,
			"mode":     mode,
			"status":   status,
			"total":    total,
			"items":    items,
		})
	}
}
