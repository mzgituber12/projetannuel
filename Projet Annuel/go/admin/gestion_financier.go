package admin

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"projet/structures"
	"sort"
	"strings"
)

func monthKey(dateValue string) string {
	if len(dateValue) < 7 {
		return ""
	}
	return dateValue[:7]
}

func Gestion_financier(database *sql.DB) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Access-Control-Allow-Origin", "*")
		response.Header().Set("Access-Control-Allow-Headers", "Content-Type, Token")
		response.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		if request.Method == http.MethodOptions {
			response.WriteHeader(http.StatusOK)
			return
		}

		if request.Method != http.MethodGet {
			http.Error(response, "Methode non autorisee", http.StatusMethodNotAllowed)
			return
		}

		token := request.Header.Get("Token")
		if token == "" {
			http.Error(response, "Token manquant", http.StatusUnauthorized)
			return
		}

		var role string
		err := database.QueryRow("SELECT role FROM utilisateur WHERE token = ?", token).Scan(&role)
		if err != nil {
			http.Error(response, "Utilisateur introuvable", http.StatusUnauthorized)
			return
		}
		if role != "admin" {
			http.Error(response, "Vous n'etes pas administrateur", http.StatusForbidden)
			return
		}

		startDate := strings.TrimSpace(request.URL.Query().Get("start_date"))
		endDate := strings.TrimSpace(request.URL.Query().Get("end_date"))
		modeFilter := strings.TrimSpace(request.URL.Query().Get("mode"))
		statusFilter := strings.TrimSpace(request.URL.Query().Get("status"))

		ordersWhere := make([]string, 0)
		ordersArgs := make([]any, 0)
		if startDate != "" {
			ordersWhere = append(ordersWhere, "a.date >= ?")
			ordersArgs = append(ordersArgs, startDate+" 00:00:00")
		}
		if endDate != "" {
			ordersWhere = append(ordersWhere, "a.date <= ?")
			ordersArgs = append(ordersArgs, endDate+" 23:59:59")
		}
		if modeFilter != "" {
			ordersWhere = append(ordersWhere, "IFNULL(p.mode, '') = ?")
			ordersArgs = append(ordersArgs, modeFilter)
		}
		if statusFilter != "" {
			ordersWhere = append(ordersWhere, "IFNULL(p.statut, '') = ?")
			ordersArgs = append(ordersArgs, statusFilter)
		}

		ordersQuery := `
			SELECT
				a.id_achat,
				IFNULL(a.id_panier, 0),
				IFNULL(DATE_FORMAT(a.date, '%Y-%m-%d %H:%i:%s'), ''),
				IFNULL(u.email, ''),
				CONCAT(IFNULL(u.prenom, ''), ' ', IFNULL(u.nom, '')),
				IFNULL(p.montant, 0),
				IFNULL(p.mode, ''),
				IFNULL(p.statut, ''),
				IFNULL(ra_count.nb_articles, 0)
			FROM achat a
			LEFT JOIN utilisateur u ON u.id_utilisateur = a.id_utilisateur
			LEFT JOIN paiement p ON p.id_achat = a.id_achat
			LEFT JOIN (
				SELECT id_panier, COUNT(*) AS nb_articles
				FROM reference_article
				GROUP BY id_panier
			) ra_count ON ra_count.id_panier = a.id_panier
		`
		if len(ordersWhere) > 0 {
			ordersQuery += " WHERE " + strings.Join(ordersWhere, " AND ")
		}
		ordersQuery += " ORDER BY a.id_achat DESC"

		rowsOrders, err := database.Query(ordersQuery, ordersArgs...)
		if err != nil {
			http.Error(response, "Erreur lecture detail commandes", http.StatusInternalServerError)
			return
		}
		defer rowsOrders.Close()

		orders := make([]structures.FinanceOrder, 0)
		for rowsOrders.Next() {
			var o structures.FinanceOrder
			err = rowsOrders.Scan(
				&o.IDAchat,
				&o.IDPanier,
				&o.DateAchat,
				&o.Email,
				&o.NomComplet,
				&o.Montant,
				&o.Mode,
				&o.Statut,
				&o.NbArticles,
			)
			if err != nil {
				http.Error(response, "Erreur lecture detail commandes", http.StatusInternalServerError)
				return
			}
			orders = append(orders, o)
		}

		subscriptionWhere := make([]string, 0)
		subscriptionArgs := make([]any, 0)
		if startDate != "" {
			subscriptionWhere = append(subscriptionWhere, "pa.date >= ?")
			subscriptionArgs = append(subscriptionArgs, startDate)
		}
		if endDate != "" {
			subscriptionWhere = append(subscriptionWhere, "pa.date <= ?")
			subscriptionArgs = append(subscriptionArgs, endDate)
		}
		if modeFilter != "" {
			subscriptionWhere = append(subscriptionWhere, "IFNULL(pa.mode, '') = ?")
			subscriptionArgs = append(subscriptionArgs, modeFilter)
		}
		if statusFilter != "" {
			subscriptionWhere = append(subscriptionWhere, "IFNULL(pa.statut, '') = ?")
			subscriptionArgs = append(subscriptionArgs, statusFilter)
		}

		subscriptionQuery := `
			SELECT
				pa.id_paiement_abonnement,
				IFNULL(pa.id_abonnement, 0),
				IFNULL(ab.type, CONCAT('Abonnement #', IFNULL(pa.id_abonnement, 0))),
				IFNULL(pa.montant, 0),
				IFNULL(DATE_FORMAT(pa.date, '%Y-%m-%d'), ''),
				IFNULL(pa.mode, ''),
				IFNULL(pa.statut, '')
			FROM paiement_abonnement pa
			LEFT JOIN abonnement ab ON ab.id_abonnement = pa.id_abonnement
		`
		if len(subscriptionWhere) > 0 {
			subscriptionQuery += " WHERE " + strings.Join(subscriptionWhere, " AND ")
		}
		subscriptionQuery += " ORDER BY pa.id_paiement_abonnement DESC"

		rowsSubscription, err := database.Query(subscriptionQuery, subscriptionArgs...)
		if err != nil {
			http.Error(response, "Erreur lecture detail abonnements", http.StatusInternalServerError)
			return
		}
		defer rowsSubscription.Close()

		subscriptionPayments := make([]structures.FinanceSubscriptionPayment, 0)
		for rowsSubscription.Next() {
			var s structures.FinanceSubscriptionPayment
			err = rowsSubscription.Scan(
				&s.IDPaiementAbonnement,
				&s.IDAbonnement,
				&s.Abonnement,
				&s.Montant,
				&s.DatePaiement,
				&s.Mode,
				&s.Statut,
			)
			if err != nil {
				http.Error(response, "Erreur lecture detail abonnements", http.StatusInternalServerError)
				return
			}
			subscriptionPayments = append(subscriptionPayments, s)
		}

		interventionWhere := make([]string, 0)
		interventionArgs := make([]any, 0)
		if startDate != "" {
			interventionWhere = append(interventionWhere, "rv.date_debut >= ?")
			interventionArgs = append(interventionArgs, startDate+" 00:00:00")
		}
		if endDate != "" {
			interventionWhere = append(interventionWhere, "rv.date_debut <= ?")
			interventionArgs = append(interventionArgs, endDate+" 23:59:59")
		}
		if statusFilter != "" {
			interventionWhere = append(interventionWhere, "IFNULL(i.statut, '') = ?")
			interventionArgs = append(interventionArgs, statusFilter)
		}

		interventionsQuery := `
			SELECT
				i.id_intervention,
				IFNULL(s.nom, ''),
				TRIM(CONCAT(IFNULL(uc.prenom, ''), ' ', IFNULL(uc.nom, ''))),
				TRIM(CONCAT(IFNULL(up.prenom, ''), ' ', IFNULL(up.nom, ''))),
				IFNULL(i.montant, 0),
				IFNULL(i.statut, ''),
				IFNULL(DATE_FORMAT(rv.date_debut, '%Y-%m-%d %H:%i:%s'), '')
			FROM intervention i
			LEFT JOIN service s ON s.id_service = i.id_service
			LEFT JOIN utilisateur uc ON uc.id_utilisateur = i.id_utilisateur
			LEFT JOIN prestataire p ON p.id_prestataire = i.id_prestataire
			LEFT JOIN utilisateur up ON up.id_utilisateur = p.id_utilisateur
			LEFT JOIN rendez_vous rv ON rv.id_rdv = i.id_rdv
		`
		if len(interventionWhere) > 0 {
			interventionsQuery += " WHERE " + strings.Join(interventionWhere, " AND ")
		}
		interventionsQuery += " ORDER BY i.id_intervention DESC"

		rowsInterventions, err := database.Query(interventionsQuery, interventionArgs...)
		if err != nil {
			http.Error(response, "Erreur lecture detail interventions", http.StatusInternalServerError)
			return
		}
		defer rowsInterventions.Close()

		interventions := make([]structures.FinanceIntervention, 0)
		for rowsInterventions.Next() {
			var i structures.FinanceIntervention
			err = rowsInterventions.Scan(
				&i.IDIntervention,
				&i.Service,
				&i.Client,
				&i.Prestataire,
				&i.Montant,
				&i.Statut,
				&i.DateRDV,
			)
			if err != nil {
				http.Error(response, "Erreur lecture detail interventions", http.StatusInternalServerError)
				return
			}
			interventions = append(interventions, i)
		}

		stats := structures.FinanceStats{}
		stats.OrdersTotal = len(orders)
		stats.InterventionsTotal = len(interventions)

		for _, o := range orders {
			stats.ShopRevenueTotal += o.Montant
			if strings.EqualFold(o.Statut, "paid") {
				stats.ShopPaidTotal += o.Montant
			} else {
				stats.ShopPendingTotal += o.Montant
			}
		}

		for _, s := range subscriptionPayments {
			stats.SubscriptionRevenueTotal += s.Montant
			if strings.EqualFold(s.Statut, "paid") {
				stats.SubscriptionPaidTotal += s.Montant
			} else if strings.EqualFold(s.Statut, "pending") {
				stats.SubscriptionPendingTotal += s.Montant
			} else if strings.EqualFold(s.Statut, "canceled") {
				stats.SubscriptionCanceledTotal += s.Montant
			}
		}

		for _, i := range interventions {
			stats.InterventionsAmountTotal += i.Montant
		}

		stats.GlobalRevenueTotal = stats.ShopRevenueTotal + stats.SubscriptionRevenueTotal + stats.InterventionsAmountTotal

		monthlyMap := make(map[string]*structures.FinanceMonthly)
		addMonthly := func(dateValue string, update func(item *structures.FinanceMonthly)) {
			key := monthKey(dateValue)
			if key == "" {
				return
			}

			item, exists := monthlyMap[key]
			if !exists {
				item = &structures.FinanceMonthly{Month: key}
				monthlyMap[key] = item
			}

			update(item)
		}

		for _, o := range orders {
			addMonthly(o.DateAchat, func(item *structures.FinanceMonthly) {
				item.Shop += o.Montant
			})
		}

		for _, s := range subscriptionPayments {
			addMonthly(s.DatePaiement, func(item *structures.FinanceMonthly) {
				item.Subscription += s.Montant
			})
		}

		for _, i := range interventions {
			addMonthly(i.DateRDV, func(item *structures.FinanceMonthly) {
				item.Intervention += i.Montant
			})
		}

		monthlyKeys := make([]string, 0, len(monthlyMap))
		for k := range monthlyMap {
			monthlyKeys = append(monthlyKeys, k)
		}
		sort.Strings(monthlyKeys)

		monthly := make([]structures.FinanceMonthly, 0, len(monthlyKeys))
		for _, k := range monthlyKeys {
			item := monthlyMap[k]
			item.Total = item.Shop + item.Subscription + item.Intervention
			monthly = append(monthly, *item)
		}

		response.Header().Set("Content-Type", "application/json")
		json.NewEncoder(response).Encode(map[string]any{
			"stats":                 stats,
			"orders":                orders,
			"subscription_payments": subscriptionPayments,
			"interventions":         interventions,
			"monthly":               monthly,
		})
	}
}
