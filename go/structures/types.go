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
}

type Contrat struct {
	Nom string `json:"nom"`
}

type Conseil struct {
	Titre   string `json:"titre"`
	Contenu string `json:"contenu"`
	Date    string `json:"date"`
}

type Evenement struct {
	ID          int     `json:"id"`
	Nom         string  `json:"nom"`
	Date        string  `json:"date"`
	Description string  `json:"description"`
	Tarif       float64 `json:"tarif"`
	Rejoindre   string  `json:"rejoindre"`
}

type Service struct {
	ID          int     `json:"id"`
	Nom         string  `json:"nom"`
	Description string  `json:"description"`
	Tarif       float64 `json:"tarif"`
	Rejoindre   string  `json:"rejoindre"`
}

type Article struct {
	Nom         string  `json:"nom"`
	Description string  `json:"description"`
	Prix        float64 `json:"prix"`
}

type List struct {
	Contrat   []Contrat   `json:"contrat"`
	Conseil   []Conseil   `json:"conseil"`
	Evenement []Evenement `json:"evenement"`
	Service   []Service   `json:"service"`
	Article   []Article   `json:"article"`
}

type Etat struct {
	State string `json:"state"`
}
