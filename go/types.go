package main

type user struct {
	Id       int    `json:"id"`
	Nom      string `json:"nom"`
	Prenom   string `json:"prenom"`
	Age      int    `json:"age"`
	Email    string `json:"email"`
	Password string `json:"password"`
	Role     string `json:"role"`
	Langue   string `json:"langue"`
}

type result struct {
	Message  string `json:"message"`
	Role     string `json:"role"`
	Token    string `json:"token"`
	Tutoriel int    `json:"tutoriel"`
}

type contrat struct {
	Nom string `json:"nom"`
}

type conseil struct {
	Titre   string `json:"titre"`
	Contenu string `json:"contenu"`
	Date    string `json:"date"`
}

type evenement struct {
	ID          int     `json:"id"`
	Nom         string  `json:"nom"`
	Date        string  `json:"date"`
	Description string  `json:"description"`
	Tarif       float64 `json:"tarif"`
	Rejoindre   string  `json:"rejoindre"`
}

type service struct {
	ID          int     `json:"id"`
	Nom         string  `json:"nom"`
	Description string  `json:"description"`
	Tarif       float64 `json:"tarif"`
	Rejoindre   string  `json:"rejoindre"`
}

type article struct {
	Nom         string  `json:"nom"`
	Description string  `json:"description"`
	Prix        float64 `json:"prix"`
}

type list struct {
	Contrat   []contrat   `json:"contrat"`
	Conseil   []conseil   `json:"conseil"`
	Evenement []evenement `json:"evenement"`
	Service   []service   `json:"service"`
	Article   []article   `json:"article"`
}

type etat struct {
	State string `json:"state"`
}
