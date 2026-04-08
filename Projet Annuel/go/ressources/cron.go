package ressources

import (
	"database/sql"
	"log"
	"time"
)

func DemarrerCronFacturationMensuelle(database *sql.DB) {
	go func() {
		ticker := time.NewTicker(1 * time.Hour)
		defer ticker.Stop()

		for tick := range ticker.C {
			if tick.Day() != 1 {
				continue
			}

			rows, err := database.Query("SELECT id_prestataire FROM prestataire WHERE valider = 1")
			if err != nil {
				log.Printf("[cron] Erreur lecture prestataires : %v", err)
				continue
			}

			ids := make([]int, 0)
			for rows.Next() {
				var id int
				if scanErr := rows.Scan(&id); scanErr == nil {
					ids = append(ids, id)
				}
			}
			rows.Close()

			for _, id := range ids {
				created, monthKey, total, err := genererFactureMensuelle(database, id, tick, false)
				if err != nil {
					log.Printf("[cron] Erreur génération facture prestataire %d : %v", id, err)
					continue
				}
				if created {
					log.Printf("[cron] Facture générée — prestataire %d — mois %s — total %.2f EUR", id, monthKey, total)
				}
			}
		}
	}()
}
