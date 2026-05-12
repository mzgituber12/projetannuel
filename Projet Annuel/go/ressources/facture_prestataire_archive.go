package ressources

import (
	"database/sql"
	"fmt"
	"net/http"
	"os"
	"projet/structures"
	"strconv"
	"strings"
	"github.com/jung-kurt/gofpdf"
)

const cheminDossierArchivesParDefaut = "/app/archives/factures_prestataire"
const requeteSqlLignesInterventionsPourPdf = `SELECT i.id_intervention, IFNULL(s.nom, ''), CONCAT(IFNULL(u.prenom, ''), ' ', IFNULL(u.nom, '')), IFNULL(DATE_FORMAT(rdv.date_debut, '%Y-%m-%d %H:%i:%s'), ''), IFNULL(i.statut, ''), IFNULL(i.montant, 0) FROM synthese_facture sf JOIN intervention i ON i.id_intervention = sf.id_intervention LEFT JOIN service s ON s.id_service = i.id_service LEFT JOIN utilisateur u ON u.id_utilisateur = i.id_utilisateur LEFT JOIN rendez_vous rdv ON rdv.id_rdv = i.id_rdv WHERE sf.id_facture = ? ORDER BY rdv.date_debut ASC, i.id_intervention ASC`

func nettoyerTextePDF(texte string) string {
	texte = strings.TrimSpace(texte)
	resultat := ""
	for _, caractere := range texte {
		if caractere >= 32 && caractere < 127 {
			resultat += string(caractere)
			continue
		}
		switch caractere {
		case 'é', 'è', 'ê', 'à', 'ù', 'ô', 'î', 'ï', 'ç':
			resultat += "_"
		}
	}
	if resultat == "" {
		return "-"
	}
	return resultat
}

func courtPourCellulePdf(texte string, longueurMax int) string {
	texte = nettoyerTextePDF(texte)
	if len(texte) <= longueurMax {
		return texte
	}
	if longueurMax <= 3 {
		return texte[:longueurMax]
	}
	return texte[:longueurMax-3] + "..."
}

func dossierArchivesFactures() string {
	if personnalise := os.Getenv("FACTURE_ARCHIVE_DIR"); personnalise != "" {
		return personnalise
	}
	return cheminDossierArchivesParDefaut
}

func cheminFichierArchivePDF(nomFichierEnBase string) (string, error) {
	nom := strings.TrimSpace(nomFichierEnBase)
	if nom == "" || strings.Contains(nom, "..") || strings.ContainsAny(nom, "/\\") {
		return "", fmt.Errorf("nom invalide")
	}
	return dossierArchivesFactures() + "/" + nom, nil
}

func ArchiverFacturePrestatairePDF(connexion *sql.DB, idFacture int64, idPrestataire int) error {
	dossier := dossierArchivesFactures()
	if erreurMkdir := os.MkdirAll(dossier, 0755); erreurMkdir != nil {
		return erreurMkdir
	}
	var mois string
	var montantTotal float64
	var dateGeneration string

	err := connexion.QueryRow("SELECT IFNULL(fp.mois,''), IFNULL(fp.montant_total,0), IFNULL(DATE_FORMAT(fp.date_generation,'%Y-%m-%d'),'') FROM facture_prestataire fp WHERE fp.id_facture = ? AND fp.id_prestataire = ?", idFacture, idPrestataire).Scan(&mois, &montantTotal, &dateGeneration)
	if err != nil {
		return err
	}
	interventions := make([]structures.FactureIntervention, 0)
	lignes, err := connexion.Query(requeteSqlLignesInterventionsPourPdf, idFacture)
	if err != nil {
		return err
	}
	defer lignes.Close()
	for lignes.Next() {
		var ligne structures.FactureIntervention
		if err := lignes.Scan(&ligne.IDIntervention, &ligne.Service, &ligne.Client, &ligne.DateRdv, &ligne.Statut, &ligne.Montant); err != nil {
			return err
		}
		interventions = append(interventions, ligne)
	}
	doc := gofpdf.New("P", "mm", "A4", "")
	doc.SetTitle(fmt.Sprintf("Facture %d Silver Happy", idFacture), false)
	doc.SetAuthor("Silver Happy", false)
	doc.AddPage()
	doc.SetFont("Helvetica", "B", 14)
	doc.CellFormat(0, 8, "Silver Happy - Facture prestataire (archive serveur)", "0", 1, "L", false, 0, "")
	doc.SetFont("Helvetica", "", 10)
	doc.CellFormat(0, 6, fmt.Sprintf("ID facture: %d | Prestataire ID: %d", idFacture, idPrestataire), "0", 1, "L", false, 0, "")
	doc.CellFormat(0, 6, fmt.Sprintf("Mois: %s | Date generation: %s", nettoyerTextePDF(mois), nettoyerTextePDF(dateGeneration)), "0", 1, "L", false, 0, "")
	doc.CellFormat(0, 6, fmt.Sprintf("Montant total: %.2f EUR", montantTotal), "0", 1, "L", false, 0, "")
	doc.Ln(4)
	doc.SetFont("Helvetica", "B", 9)
	doc.CellFormat(18, 6, nettoyerTextePDF("Interv."), "1", 0, "L", false, 0, "")
	doc.CellFormat(52, 6, nettoyerTextePDF("Service"), "1", 0, "L", false, 0, "")
	doc.CellFormat(45, 6, nettoyerTextePDF("Client"), "1", 0, "L", false, 0, "")
	doc.CellFormat(40, 6, nettoyerTextePDF("Date RDV"), "1", 0, "L", false, 0, "")
	doc.CellFormat(25, 6, nettoyerTextePDF("Montant"), "1", 1, "R", false, 0, "")
	doc.SetFont("Helvetica", "", 8)
	for _, intervention := range interventions {
		doc.CellFormat(18, 6, strconv.Itoa(intervention.IDIntervention), "1", 0, "L", false, 0, "")
		doc.CellFormat(52, 6, courtPourCellulePdf(intervention.Service, 32), "1", 0, "L", false, 0, "")
		doc.CellFormat(45, 6, courtPourCellulePdf(strings.TrimSpace(intervention.Client), 24), "1", 0, "L", false, 0, "")
		doc.CellFormat(40, 6, courtPourCellulePdf(intervention.DateRdv, 22), "1", 0, "L", false, 0, "")
		doc.CellFormat(25, 6, fmt.Sprintf("%.2f", intervention.Montant), "1", 1, "R", false, 0, "")
	}
	doc.Ln(2)
	doc.SetFont("Helvetica", "I", 8)
	doc.CellFormat(0, 5, nettoyerTextePDF("Document genere automatiquement et conserve en double sur le serveur."), "0", 1, "L", false, 0, "")
	moisSansSlash := strings.ReplaceAll(strings.TrimSpace(mois), "/", "-")
	nomFichier := fmt.Sprintf("facture_%d_%s.pdf", idFacture, moisSansSlash)
	if strings.TrimSuffix(nomFichier, ".pdf") == fmt.Sprintf("facture_%d_", idFacture) {
		nomFichier = fmt.Sprintf("facture_%d.pdf", idFacture)
	}
	cheminFinal := dossier + "/" + nomFichier
	cheminTemp := cheminFinal + ".tmp"
	if erreurEcriture := doc.OutputFileAndClose(cheminTemp); erreurEcriture != nil {
		return erreurEcriture
	}
	if erreurRenom := os.Rename(cheminTemp, cheminFinal); erreurRenom != nil {
		_ = os.Remove(cheminTemp)
		return erreurRenom
	}
	_, err = connexion.Exec(`UPDATE facture_prestataire SET fichier_pdf = ? WHERE id_facture = ?`, nomFichier, idFacture)
	return err
}

func telechargerArchivePdfHandler(connexion *sql.DB, accesAdministrateur bool) http.HandlerFunc {
	return func(reponse http.ResponseWriter, requete *http.Request) {
		if requete.Method != http.MethodGet {
			http.Error(reponse, "Methode non autorisee", http.StatusMethodNotAllowed)
			return
		}
		jeton := strings.TrimSpace(requete.Header.Get("Token"))
		var idPrestataire int
		if accesAdministrateur {
			var role string
			if err := connexion.QueryRow("SELECT role FROM utilisateur WHERE token = ?", jeton).Scan(&role); err != nil {
				http.Error(reponse, "Authentification requise", http.StatusUnauthorized)
				return
			}
			if role != "admin" {
				http.Error(reponse, "Acces reserve administrateur", http.StatusForbidden)
				return
			}
		} else {
			var err error
			idPrestataire, err = prestataireIDFromToken(connexion, jeton)
			if err != nil {
				http.Error(reponse, "Authentification requise", http.StatusUnauthorized)
				return
			}
		}
		idFacture, err := strconv.Atoi(requete.PathValue("id"))
		if err != nil || idFacture <= 0 {
			http.Error(reponse, "Identifiant facture invalide", http.StatusBadRequest)
			return
		}
		var nomPdf sql.NullString
		var errLecture error
		if accesAdministrateur {
			errLecture = connexion.QueryRow("SELECT fichier_pdf FROM facture_prestataire WHERE id_facture = ?", idFacture).Scan(&nomPdf)
		} else {
			errLecture = connexion.QueryRow("SELECT fichier_pdf FROM facture_prestataire WHERE id_facture = ? AND id_prestataire = ?", idFacture, idPrestataire).Scan(&nomPdf)
		}
		if errLecture != nil && errLecture != sql.ErrNoRows {
			http.Error(reponse, "Erreur lecture base", http.StatusInternalServerError)
			return
		}
		if errLecture == sql.ErrNoRows || !nomPdf.Valid || strings.TrimSpace(nomPdf.String) == "" {
			http.Error(reponse, "Archive PDF introuvable", http.StatusNotFound)
			return
		}
		cheminDisque, errChem := cheminFichierArchivePDF(nomPdf.String)
		if errChem != nil {
			http.Error(reponse, "Nom archive invalide", http.StatusBadRequest)
			return
		}
		contenu, errFichier := os.ReadFile(cheminDisque)
		if errFichier != nil {
			http.Error(reponse, "Fichier archive absent", http.StatusNotFound)
			return
		}
		nomTelechargement := strings.TrimSpace(nomPdf.String)
		reponse.Header().Set("Content-Type", "application/pdf")
		reponse.Header().Set("Content-Disposition", fmt.Sprintf(`attachment; filename="%s"`, nomTelechargement))
		_, _ = reponse.Write(contenu)
	}
}

func TelechargerArchivePDFFacturePrestataire(connexion *sql.DB) http.HandlerFunc {
	return telechargerArchivePdfHandler(connexion, false)
}

func TelechargerArchivePDFFactureAdmin(connexion *sql.DB) http.HandlerFunc {
	return telechargerArchivePdfHandler(connexion, true)
}

