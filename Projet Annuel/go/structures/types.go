package structures

type User struct {
	ID       int    `json:"id"`
	Nom      string `json:"nom"`
	Prenom   string `json:"prenom"`
	Age      int    `json:"age"`
	Email    string `json:"email"`
	Password string `json:"password"`
	Role     string `json:"role"`
	Langue   string `json:"langue"`
}

type Result struct {
	Message  string `json:"message"`
	Value    int    `json:"value"`
	Role     string `json:"role"`
	Token    string `json:"token"`
	Tutoriel int    `json:"tutoriel"`
	Langue   string `json:"langue"`
}

type Contrat struct {
	Nom string `json:"nom"`
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
}

type Categorie struct {
	ID  int    `json:"id"`
	Nom string `json:"nom"`
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
	PrixMois         float64 `json:"prix_mois"`
	PrixAn           float64 `json:"prix_an"`
	Statut           string  `json:"statut"`
	TypePrestataire  int     `json:"type_prestataire"`
	Description      string  `json:"description"`
	LocauxPrestation bool    `json:"locaux_prestation"`
	TrajetOffert     bool    `json:"trajet_offert"`
	OffreRepas       bool    `json:"offre_repas"`
	MisEnAvant       bool    `json:"mis_en_avant"`
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
	NomService     string  `json:"nom_service"`
	NomPrestataire string  `json:"nom_prestataire"`
	Tarif          float64 `json:"tarif"`
	Status         string  `json:"status"`
	DateDebut      string  `json:"date_debut"`
	DateFin        string  `json:"date_fin"`
	CanModify      bool    `json:"can_modify"`
}
