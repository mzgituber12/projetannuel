package structures

type User struct {
	ID            int    `json:"id"`
	Nom           string `json:"nom"`
	Prenom        string `json:"prenom"`
	Age           int    `json:"age"`
	DateNaissance string `json:"date_naissance"`
	Telephone     string `json:"telephone"`
	Email         string `json:"email"`
	Password      string `json:"password"`
	Role          string `json:"role"`
	Langue        string `json:"langue"`
	StatutUser    string `json:"statut_user"`
	FinSusp       string `json:"fin_susp"`
}

type Note struct {
	ID_conseil int     `json:"id_conseil"`
	Note       int     `json:"note"`
	Message    string  `json:"message"`
	Moyenne    float64 `json:"moyenne"`
}

type Result struct {
	Message       string `json:"message"`
	Value         int    `json:"value"`
	Role          string `json:"role"`
	Token         string `json:"token"`
	Tutoriel      int    `json:"tutoriel"`
	Langue        string `json:"langue"`
	StatutUser    string `json:"statut_user"`
	FinSusp       string `json:"fin_susp"`
	MotifSanction string `json:"motif_sanction"`
	TypeSanction  string `json:"type_sanction"`
}

type Contrat struct {
	ID           int    `json:"id"`
	Nom          string `json:"nom"`
	DateDebut    string `json:"date_debut"`
	DateFin      string `json:"date_fin"`
	TypePaiement string `json:"type_paiement"`
	TypeContrat  string `json:"type_contrat"`
}

type Conseil struct {
	ID      int    `json:"id"`
	Titre   string `json:"titre"`
	Contenu string `json:"contenu"`
	Image   string `json:"image"`
	Date    string `json:"date"`
}

type Evenement struct {
	ID          int     `json:"id"`
	Nom         string  `json:"nom"`
	Date        string  `json:"date"`
	Description string  `json:"description"`
	Tarif       float64 `json:"tarif"`
	Image       string  `json:"image"`
	Rejoindre   string  `json:"rejoindre"`
	Lieu        string  `json:"lieu"`
}

type Service struct {
	ID          int     `json:"id"`
	Nom         string  `json:"nom"`
	Description string  `json:"description"`
	Tarif       float64 `json:"tarif"`
	Image       string  `json:"image"`
	IdCategorie int     `json:"id_categorie"`
	Categorie   string  `json:"categorie"`
	Prestataire string  `json:"prestataire"`
	Rejoindre   string  `json:"rejoindre"`
	ValideAdmin int     `json:"valide_admin"`
}

type Categorie struct {
	ID            int    `json:"id"`
	Nom           string `json:"nom"`
	IdPrestataire *int   `json:"id_prestataire,omitempty"`
	ValideAdmin   int    `json:"valide_admin"`
}

type Prestataire struct {
	ID        int    `json:"id"`
	Nom       string `json:"nom"`
	Prenom    string `json:"prenom"`
	Type      string `json:"type"`
	Telephone string `json:"telephone"`
}

type Intervention struct {
	ID            int     `json:"id"`
	IdService     int     `json:"id_service"`
	IdPrestataire int     `json:"id_prestataire"`
	IdUtilisateur int     `json:"id_utilisateur"`
	Date          string  `json:"date"`
	Statut        string  `json:"statut"`
	Montant       float64 `json:"montant"`
}

type Article struct {
	ID          int     `json:"id"`
	Titre       string  `json:"titre"`
	Image       string  `json:"image"`
	Description string  `json:"description"`
	Prix        float64 `json:"prix"`
}

type Contact struct {
	Contenu string `json:"contenu"`
	Email   string `json:"email"`
}

type Notification struct {
	ID             int    `json:"id"`
	IDExpediteur   int    `json:"id_expediteur"`
	IDDestinataire int    `json:"id_destinataire"`
	Titre          string `json:"titre"`
	Contenu        string `json:"contenu"`
	DateEnvoie     string `json:"date_envoie"`
	Lu             int    `json:"lu"`
	Expediteur     string `json:"expediteur"`
	Destinataire   string `json:"destinataire"`
}

type ModeleNotification struct {
	ID      int    `json:"id"`
	Cle     string `json:"cle"`
	Titre   string `json:"titre"`
	Contenu string `json:"contenu"`
}

type Rdv struct {
	ID    int    `json:"id"`
	Title string `json:"title"`
	Start string `json:"start"`
	End   string `json:"end"`
}

type RequetePaiementReservationService struct {
	IDService     int    `json:"id_service"`
	Start         string `json:"start"`
	PaymentMethod string `json:"payment_method"`
}

type RequeteConfirmationReservationStripe struct {
	SessionID string `json:"session_id"`
}

type List struct {
	Contrat            []Contrat            `json:"contrat"`
	Conseil            []Conseil            `json:"conseil"`
	Evenement          []Evenement          `json:"evenement"`
	Service            []Service            `json:"service"`
	Categorie          []Categorie          `json:"categorie"`
	Prestataire        []Prestataire        `json:"prestataire"`
	Article            []Article            `json:"article"`
	Contact            []Contact            `json:"contact"`
	Notification       []Notification       `json:"notification"`
	ModeleNotification []ModeleNotification `json:"modele_notification"`
	Utilisateur        []User               `json:"utilisateur"`
	Abonnement         []Abonnement         `json:"abonnement"`
	Devis              []Devis              `json:"devis"`
}

type Etat struct {
	State string `json:"state"`
}

type Abonnement struct {
	ID               int     `json:"id"`
	Type             string  `json:"type"`
	Titre            string  `json:"titre"`
	Categorie        string  `json:"categorie"`
	PrixMois         float64 `json:"prix_mois"`
	PrixAn           float64 `json:"prix_an"`
	Statut           string  `json:"statut"`
	TypePrestataire  int     `json:"type_prestataire"`
	Nb_avantage      int     `json:"nb_avantage"`
	Contenue1        string  `json:"contenue1"`
	Contenue2        string  `json:"contenue2"`
	Contenue3        string  `json:"contenue3"`
	Contenue4        string  `json:"contenue4"`
	Description      string  `json:"description"`
	LocauxPrestation bool    `json:"locaux_prestation"`
	TrajetOffert     bool    `json:"trajet_offert"`
	OffreRepas       bool    `json:"offre_repas"`
	MisEnAvant       bool    `json:"mis_en_avant"`
}

type FichePresta struct {
	Id             int             `json:"id"`
	Nom            string          `json:"nom"`
	Prenom         string          `json:"prenom"`
	Photo_profil   string          `json:"photo_profil"`
	Date_naissance string          `json:"date_naissance"`
	Documents      []DocPresta     `json:"documents"`
	Documents_txt  []DocPresta_txt `json:"documents_txt"`
	Categorie      string          `json:"categorie"`
}

type DocPresta struct {
	Nom_fichier   string `json:"nom_fichier"`
	Type_document string `json:"type_document"`
}

type DocPresta_txt struct {
	Categorie_text string `json:"categorie_text"`
	Contenu        string `json:"contenu"`
}

type Souscription struct {
	ID               int    `json:"id"`
	IDUtilisateur    int    `json:"id_utilisateur"`
	IDAbonnement     int    `json:"id_abonnement"`
	DateSouscription string `json:"date_souscription"`
	DateExpiration   string `json:"date_expiration"`
	TypePaiement     string `json:"type_paiement"`
	Validite         bool   `json:"validite"`
	StripeCustomerID string `json:"stripe_customer_id"`
	StripeSubID      string `json:"stripe_subscription_id"`
}

type PaiementAbonnement struct {
	ID           int     `json:"id"`
	IDAbonnement int     `json:"id_abonnement"`
	Montant      float64 `json:"montant"`
	Date         string  `json:"date"`
	Mode         string  `json:"mode"`
	Statut       string  `json:"statut"`
}

type Devis struct {
	ID             int     `json:"id"`
	IDService      int     `json:"id_service"`
	NomService     string  `json:"nom_service"`
	NomPrestataire string  `json:"nom_prestataire"`
	Tarif          float64 `json:"tarif"`
	Status         string  `json:"status"`
	DateDebut      string  `json:"date_debut"`
	DateFin        string  `json:"date_fin"`
	CanModify      bool    `json:"can_modify"`
	CanEditTarif   bool    `json:"can_edit_tarif"`
}

type Evaluation struct {
	IDEvaluation  int    `json:"id_evaluation"`
	IDUtilisateur int    `json:"id_utilisateur"`
	NomAuteur     string `json:"nom_auteur"`
	Note          int    `json:"note"`
	Commentaire   string `json:"commentaire"`
	Date          string `json:"date"`
}

type EvaluationResume struct {
	IDService     int          `json:"id_service"`
	AverageRating float64      `json:"average_rating"`
	TotalReviews  int          `json:"total_reviews"`
	CanReview     bool         `json:"can_review"`
	UserReview    *Evaluation  `json:"user_review"`
	Reviews       []Evaluation `json:"reviews"`
}

type EvaluationParService struct {
	IDService     int          `json:"id_service"`
	NomService    string       `json:"nom_service"`
	AverageRating float64      `json:"average_rating"`
	TotalReviews  int          `json:"total_reviews"`
	Reviews       []Evaluation `json:"reviews"`
}

type SuiviIntervention struct {
	ID                int     `json:"id"`
	NomService        string  `json:"nom_service"`
	TarifService      float64 `json:"tarif_service"`
	NomUtilisateur    string  `json:"nom_utilisateur"`
	PrenomUtilisateur string  `json:"prenom_utilisateur"`
	DateDebut         string  `json:"date_debut"`
	DateFin           string  `json:"date_fin"`
	TypeRdv           string  `json:"type_rdv"`
	Statut            string  `json:"statut"`
	Montant           float64 `json:"montant"`
}

type FinanceStats struct {
	OrdersTotal               int     `json:"orders_total"`
	ShopRevenueTotal          float64 `json:"shop_revenue_total"`
	ShopPaidTotal             float64 `json:"shop_paid_total"`
	ShopPendingTotal          float64 `json:"shop_pending_total"`
	SubscriptionRevenueTotal  float64 `json:"subscription_revenue_total"`
	SubscriptionPaidTotal     float64 `json:"subscription_paid_total"`
	SubscriptionPendingTotal  float64 `json:"subscription_pending_total"`
	SubscriptionCanceledTotal float64 `json:"subscription_canceled_total"`
	InterventionsTotal        int     `json:"interventions_total"`
	InterventionsAmountTotal  float64 `json:"interventions_amount_total"`
	GlobalRevenueTotal        float64 `json:"global_revenue_total"`
}

type FinanceOrder struct {
	IDAchat    int     `json:"id_achat"`
	IDPanier   int     `json:"id_panier"`
	DateAchat  string  `json:"date_achat"`
	Email      string  `json:"email"`
	NomComplet string  `json:"nom_complet"`
	Montant    float64 `json:"montant"`
	Mode       string  `json:"mode"`
	Statut     string  `json:"statut"`
	NbArticles int     `json:"nb_articles"`
}

type FinanceSubscriptionPayment struct {
	IDPaiementAbonnement int     `json:"id_paiement_abonnement"`
	IDAbonnement         int     `json:"id_abonnement"`
	Abonnement           string  `json:"abonnement"`
	Montant              float64 `json:"montant"`
	DatePaiement         string  `json:"date_paiement"`
	Mode                 string  `json:"mode"`
	Statut               string  `json:"statut"`
}

type FinanceIntervention struct {
	IDIntervention int     `json:"id_intervention"`
	Service        string  `json:"service"`
	Client         string  `json:"client"`
	Prestataire    string  `json:"prestataire"`
	Montant        float64 `json:"montant"`
	Statut         string  `json:"statut"`
	DateRDV        string  `json:"date_rdv"`
}

type FinanceMonthly struct {
	Month        string  `json:"month"`
	Shop         float64 `json:"shop"`
	Subscription float64 `json:"subscription"`
	Intervention float64 `json:"intervention"`
	Total        float64 `json:"total"`
}

type FactureIntervention struct {
	IDIntervention int     `json:"id_intervention"`
	Service        string  `json:"service"`
	Client         string  `json:"client"`
	DateRdv        string  `json:"date_rdv"`
	Statut         string  `json:"statut"`
	Montant        float64 `json:"montant"`
}

type FacturePrestataire struct {
	IDFacture      int                   `json:"id_facture"`
	Mois           string                `json:"mois"`
	MontantTotal   float64               `json:"montant_total"`
	DateGeneration string                `json:"date_generation"`
	Interventions  []FactureIntervention `json:"interventions"`
	IDVirement     int                   `json:"id_virement"`
	StatutVirement string                `json:"statut_virement"`
	DateVirement   string                `json:"date_virement"`
}

type AdminFacturePrestataire struct {
	IDFacture      int                   `json:"id_facture"`
	IDPrestataire  int                   `json:"id_prestataire"`
	NomPrestataire string                `json:"nom_prestataire"`
	Mois           string                `json:"mois"`
	MontantTotal   float64               `json:"montant_total"`
	DateGeneration string                `json:"date_generation"`
	Interventions  []FactureIntervention `json:"interventions"`
	IDVirement     int                   `json:"id_virement"`
	StatutVirement string                `json:"statut_virement"`
	DateVirement   string                `json:"date_virement"`
}

type MLPredictPayload struct {
	Age                    float64 `json:"age"`
	Sexe                   string  `json:"sexe"`
	TypeAbonnement         string  `json:"type_abonnement"`
	Langue                 string  `json:"langue"`
	AncienneteMois         float64 `json:"anciennete_mois"`
	ScoreSatisfaction      float64 `json:"score_satisfaction"`
	TauxAnnulation         float64 `json:"taux_annulation"`
	NbInterventionsTotales float64 `json:"nb_interventions_totales"`
	DepenseTotaleEstimee   float64 `json:"depense_totale_estimee"`
	EstAbonne              float64 `json:"est_abonne"`
}

type MLCandidate struct {
	ServiceTrouver string  `json:"service_trouver"`
	Score          float64 `json:"score"`
}

type MLPredictResponse struct {
	Principal    MLCandidate   `json:"principal"`
	Alternatives []MLCandidate `json:"alternatives"`
}
