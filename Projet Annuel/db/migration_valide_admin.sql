-- Validation admin des services et catégories (catalogue public).
-- Exécuter une fois sur une base déjà existante (phpMyAdmin, CLI mysql, etc.).
-- Si vous avez réimporté `init.sql` déjà à jour avec `valide_admin`, ne pas réexécuter ce script.

ALTER TABLE `service`
  ADD COLUMN `valide_admin` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = visible catalogue / validé' AFTER `id_categorie`;

ALTER TABLE `categorie`
  ADD COLUMN `valide_admin` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = visible filtres publics / validé' AFTER `nom`;

-- Conserver le comportement actuel pour les données déjà présentes
UPDATE `service` SET `valide_admin` = 1;
UPDATE `categorie` SET `valide_admin` = 1;
