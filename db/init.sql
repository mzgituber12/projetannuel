-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/

-- Base de données : `ma_base`

-- Create database and select it (matches docker-compose MYSQL_DATABASE)
CREATE DATABASE IF NOT EXISTS `projet` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `projet`;
-- Version du serveur : 5.7.24
-- Version de PHP : 8.3.1

-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : lun. 02 mars 2026 à 22:02
-- Version du serveur : 5.7.24
-- Version de PHP : 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projet`
--

-- --------------------------------------------------------

--
-- Structure de la table `abonnement`
--

CREATE TABLE `abonnement` (
  `id_abonnement` int(11) NOT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `achat`
--

CREATE TABLE `achat` (
  `id_achat` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_panier` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id_article` int(11) NOT NULL,
  `titre` varchar(150) DEFAULT NULL,
  `description` text,
  `prix` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `conseil`
--

CREATE TABLE `conseil` (
  `id_conseil` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `titre` varchar(150) DEFAULT NULL,
  `contenu` text,
  `date_publication` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `conseil`
--

INSERT INTO `conseil` (`id_conseil`, `id_utilisateur`, `titre`, `contenu`, `date_publication`) VALUES
(1, 1, 'Lave toi les mains', 'Mets du savon papi', '2026-02-27 00:00:00'),
(2, 1, 'Marche', 'Faut marcher pour tes jambes papi', '2026-02-19 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `consultation_conseil`
--

CREATE TABLE `consultation_conseil` (
  `id_utilisateur` int(11) NOT NULL,
  `id_conseil` int(11) NOT NULL,
  `date_consultation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `contrat`
--

CREATE TABLE `contrat` (
  `id_contrat` int(11) NOT NULL,
  `id_devis` int(11) DEFAULT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `type_paiement` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `contrat`
--

INSERT INTO `contrat` (`id_contrat`, `id_devis`, `id_utilisateur`, `date_debut`, `date_fin`, `nom`, `type_paiement`) VALUES
(1, NULL, 1, NULL, NULL, 'contrat 1', NULL),
(2, NULL, 1, NULL, NULL, 'contrat2', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `devis`
--

CREATE TABLE `devis` (
  `id_devis` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `id_service` int(11) DEFAULT NULL,
  `tarif_personalise` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `disponibilite`
--

CREATE TABLE `disponibilite` (
  `id_disponibilite` int(11) NOT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `heure_debut` time DEFAULT NULL,
  `heure_fin` time DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `evaluation`
--

CREATE TABLE `evaluation` (
  `id_evaluation` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_service` int(11) DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `commentaire` text,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `evenement`
--

CREATE TABLE `evenement` (
  `id_evenement` int(11) NOT NULL,
  `nom` varchar(150) DEFAULT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `description` text,
  `tarif` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `evenement`
--

INSERT INTO `evenement` (`id_evenement`, `nom`, `date`, `description`, `tarif`) VALUES
(1, 'Manger des Nems', '2026-02-27 23:42:51', 'Allez manger des nems avec Maitre Kung et son ami Fu', '10.00'),
(2, 'Machine a laver', '2026-02-27 23:43:33', 'Allez courir sur une machine a laver géante : age conseillé : moins de 66 ans sinon crise cardiaque assurée', '15.00');

-- --------------------------------------------------------

--
-- Structure de la table `facture_prestataire`
--

CREATE TABLE `facture_prestataire` (
  `id_facture` int(11) NOT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `mois` varchar(20) DEFAULT NULL,
  `montant_total` decimal(10,2) DEFAULT NULL,
  `date_generation` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `intervention`
--

CREATE TABLE `intervention` (
  `id_intervention` int(11) NOT NULL,
  `id_service` int(11) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `id_message` int(11) NOT NULL,
  `id_expediteur` int(11) DEFAULT NULL,
  `id_destinataire` int(11) DEFAULT NULL,
  `contenu` text,
  `date_envoie` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
  `id_notification` int(11) NOT NULL,
  `id_expediteur` int(11) DEFAULT NULL,
  `id_destinataire` int(11) DEFAULT NULL,
  `contenu` text,
  `date_envoie` datetime DEFAULT NULL,
  `lu` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `paiement`
--

CREATE TABLE `paiement` (
  `id_paiement` int(11) NOT NULL,
  `id_achat` int(11) DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `mode` varchar(50) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `paiement_abonnement`
--

CREATE TABLE `paiement_abonnement` (
  `id_paiement_abonnement` int(11) NOT NULL,
  `id_abonnement` int(11) DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `mode` varchar(50) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `panier`
--

CREATE TABLE `panier` (
  `id_panier` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `date_creation` datetime DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `planning`
--

CREATE TABLE `planning` (
  `id_planning` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `id_service` int(11) DEFAULT NULL,
  `id_evenement` int(11) DEFAULT NULL,
  `id_rdv` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `prestataire`
--

CREATE TABLE `prestataire` (
  `id_prestataire` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `documentCI` varchar(255) DEFAULT NULL,
  `documentHA` varchar(255) DEFAULT NULL,
  `documentD` varchar(255) DEFAULT NULL,
  `valider` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `reference_evenement`
--

CREATE TABLE `reference_evenement` (
  `id_utilisateur` int(11) NOT NULL,
  `id_evenement` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `reference_service`
--

CREATE TABLE `reference_service` (
  `id_utilisateur` int(11) NOT NULL,
  `id_service` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `rendez_vous`
--

CREATE TABLE `rendez_vous` (
  `id_rdv` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `service`
--

CREATE TABLE `service` (
  `id_service` int(11) NOT NULL,
  `nom` varchar(150) DEFAULT NULL,
  `description` text,
  `tarif` decimal(10,2) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `service`
--

INSERT INTO `service` (`id_service`, `nom`, `description`, `tarif`, `id_prestataire`) VALUES
(1, 'Lavage de pieds', 'Lavez vous les pieds avec la langue de Mohamed ali', '88.45', NULL),
(2, 'Cirage de cheveux', 'Vous voulez devenir aussi chauve que the rock ? appelez moi', '44.40', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `synthese_facture`
--

CREATE TABLE `synthese_facture` (
  `id_facture` int(11) NOT NULL,
  `id_intervention` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `token`
--

CREATE TABLE `token` (
  `id_token` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `valeur` varchar(255) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `date_expiration` datetime DEFAULT NULL,
  `utiliser` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_utilisateur` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(1000) DEFAULT NULL,
  `token` varchar(1000) DEFAULT NULL,
  `role` enum('adherant','prestataire','admin') DEFAULT 'adherant',
  `langue` varchar(50) DEFAULT NULL,
  `taille_police` varchar(20) DEFAULT NULL,
  `tutoriel` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `age`, `email`, `password`, `token`, `role`, `langue`, `taille_police`, `tutoriel`) VALUES
(1, NULL, NULL, NULL, 'aa@aa', '$2a$10$FQ8LUXWRx6HEtCNmzTYeQ.RVaSE8qTp06AYWJdFogUWJQLILGEi6y', 'yhby_OVF40JFKdo1isXnARNTBZt_wCZnlSL5y1luGig', 'adherant', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `virement`
--

CREATE TABLE `virement` (
  `id_virement` int(11) NOT NULL,
  `id_facture` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `abonnement`
--
ALTER TABLE `abonnement`
  ADD PRIMARY KEY (`id_abonnement`),
  ADD KEY `id_prestataire` (`id_prestataire`);

--
-- Index pour la table `achat`
--
ALTER TABLE `achat`
  ADD PRIMARY KEY (`id_achat`),
  ADD UNIQUE KEY `id_panier` (`id_panier`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id_article`);

--
-- Index pour la table `conseil`
--
ALTER TABLE `conseil`
  ADD PRIMARY KEY (`id_conseil`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `consultation_conseil`
--
ALTER TABLE `consultation_conseil`
  ADD PRIMARY KEY (`id_utilisateur`,`id_conseil`),
  ADD KEY `id_conseil` (`id_conseil`);

--
-- Index pour la table `contrat`
--
ALTER TABLE `contrat`
  ADD PRIMARY KEY (`id_contrat`),
  ADD UNIQUE KEY `id_devis` (`id_devis`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `devis`
--
ALTER TABLE `devis`
  ADD PRIMARY KEY (`id_devis`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_prestataire` (`id_prestataire`),
  ADD KEY `id_service` (`id_service`);

--
-- Index pour la table `disponibilite`
--
ALTER TABLE `disponibilite`
  ADD PRIMARY KEY (`id_disponibilite`),
  ADD KEY `id_prestataire` (`id_prestataire`);

--
-- Index pour la table `evaluation`
--
ALTER TABLE `evaluation`
  ADD PRIMARY KEY (`id_evaluation`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_service` (`id_service`);

--
-- Index pour la table `evenement`
--
ALTER TABLE `evenement`
  ADD PRIMARY KEY (`id_evenement`);

--
-- Index pour la table `facture_prestataire`
--
ALTER TABLE `facture_prestataire`
  ADD PRIMARY KEY (`id_facture`),
  ADD KEY `id_prestataire` (`id_prestataire`);

--
-- Index pour la table `intervention`
--
ALTER TABLE `intervention`
  ADD PRIMARY KEY (`id_intervention`),
  ADD KEY `id_service` (`id_service`),
  ADD KEY `id_prestataire` (`id_prestataire`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `id_expediteur` (`id_expediteur`),
  ADD KEY `id_destinataire` (`id_destinataire`);

--
-- Index pour la table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id_notification`),
  ADD KEY `id_expediteur` (`id_expediteur`),
  ADD KEY `id_destinataire` (`id_destinataire`);

--
-- Index pour la table `paiement`
--
ALTER TABLE `paiement`
  ADD PRIMARY KEY (`id_paiement`),
  ADD UNIQUE KEY `id_achat` (`id_achat`);

--
-- Index pour la table `paiement_abonnement`
--
ALTER TABLE `paiement_abonnement`
  ADD PRIMARY KEY (`id_paiement_abonnement`),
  ADD KEY `id_abonnement` (`id_abonnement`);

--
-- Index pour la table `panier`
--
ALTER TABLE `panier`
  ADD PRIMARY KEY (`id_panier`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `planning`
--
ALTER TABLE `planning`
  ADD PRIMARY KEY (`id_planning`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_service` (`id_service`),
  ADD KEY `id_evenement` (`id_evenement`),
  ADD KEY `id_rdv` (`id_rdv`);

--
-- Index pour la table `prestataire`
--
ALTER TABLE `prestataire`
  ADD PRIMARY KEY (`id_prestataire`),
  ADD UNIQUE KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `reference_evenement`
--
ALTER TABLE `reference_evenement`
  ADD KEY `fk_even` (`id_evenement`),
  ADD KEY `fk_util` (`id_utilisateur`);

--
-- Index pour la table `reference_service`
--
ALTER TABLE `reference_service`
  ADD KEY `fk_serv` (`id_service`),
  ADD KEY `fk_util2` (`id_utilisateur`);

--
-- Index pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  ADD PRIMARY KEY (`id_rdv`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_prestataire` (`id_prestataire`);

--
-- Index pour la table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`id_service`),
  ADD KEY `id_prestataire` (`id_prestataire`);

--
-- Index pour la table `synthese_facture`
--
ALTER TABLE `synthese_facture`
  ADD PRIMARY KEY (`id_facture`,`id_intervention`),
  ADD KEY `id_intervention` (`id_intervention`);

--
-- Index pour la table `token`
--
ALTER TABLE `token`
  ADD PRIMARY KEY (`id_token`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `virement`
--
ALTER TABLE `virement`
  ADD PRIMARY KEY (`id_virement`),
  ADD KEY `id_facture` (`id_facture`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `abonnement`
--
ALTER TABLE `abonnement`
  MODIFY `id_abonnement` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `achat`
--
ALTER TABLE `achat`
  MODIFY `id_achat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `id_article` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `conseil`
--
ALTER TABLE `conseil`
  MODIFY `id_conseil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `contrat`
--
ALTER TABLE `contrat`
  MODIFY `id_contrat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `devis`
--
ALTER TABLE `devis`
  MODIFY `id_devis` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `disponibilite`
--
ALTER TABLE `disponibilite`
  MODIFY `id_disponibilite` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evaluation`
--
ALTER TABLE `evaluation`
  MODIFY `id_evaluation` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evenement`
--
ALTER TABLE `evenement`
  MODIFY `id_evenement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `facture_prestataire`
--
ALTER TABLE `facture_prestataire`
  MODIFY `id_facture` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `intervention`
--
ALTER TABLE `intervention`
  MODIFY `id_intervention` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notification`
--
ALTER TABLE `notification`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `paiement`
--
ALTER TABLE `paiement`
  MODIFY `id_paiement` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `paiement_abonnement`
--
ALTER TABLE `paiement_abonnement`
  MODIFY `id_paiement_abonnement` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `panier`
--
ALTER TABLE `panier`
  MODIFY `id_panier` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `planning`
--
ALTER TABLE `planning`
  MODIFY `id_planning` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `prestataire`
--
ALTER TABLE `prestataire`
  MODIFY `id_prestataire` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  MODIFY `id_rdv` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `service`
--
ALTER TABLE `service`
  MODIFY `id_service` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `token`
--
ALTER TABLE `token`
  MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `virement`
--
ALTER TABLE `virement`
  MODIFY `id_virement` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `abonnement`
--
ALTER TABLE `abonnement`
  ADD CONSTRAINT `abonnement_ibfk_1` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE CASCADE;

--
-- Contraintes pour la table `achat`
--
ALTER TABLE `achat`
  ADD CONSTRAINT `achat_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `achat_ibfk_2` FOREIGN KEY (`id_panier`) REFERENCES `panier` (`id_panier`) ON DELETE SET NULL;

--
-- Contraintes pour la table `conseil`
--
ALTER TABLE `conseil`
  ADD CONSTRAINT `conseil_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `consultation_conseil`
--
ALTER TABLE `consultation_conseil`
  ADD CONSTRAINT `consultation_conseil_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultation_conseil_ibfk_2` FOREIGN KEY (`id_conseil`) REFERENCES `conseil` (`id_conseil`) ON DELETE CASCADE;

--
-- Contraintes pour la table `contrat`
--
ALTER TABLE `contrat`
  ADD CONSTRAINT `contrat_ibfk_1` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`id_devis`) ON DELETE CASCADE,
  ADD CONSTRAINT `contrat_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `devis`
--
ALTER TABLE `devis`
  ADD CONSTRAINT `devis_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `devis_ibfk_2` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE SET NULL,
  ADD CONSTRAINT `devis_ibfk_3` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`) ON DELETE SET NULL;

--
-- Contraintes pour la table `disponibilite`
--
ALTER TABLE `disponibilite`
  ADD CONSTRAINT `disponibilite_ibfk_1` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE CASCADE;

--
-- Contraintes pour la table `evaluation`
--
ALTER TABLE `evaluation`
  ADD CONSTRAINT `evaluation_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluation_ibfk_2` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`) ON DELETE CASCADE;

--
-- Contraintes pour la table `facture_prestataire`
--
ALTER TABLE `facture_prestataire`
  ADD CONSTRAINT `facture_prestataire_ibfk_1` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE CASCADE;

--
-- Contraintes pour la table `intervention`
--
ALTER TABLE `intervention`
  ADD CONSTRAINT `intervention_ibfk_1` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`) ON DELETE SET NULL,
  ADD CONSTRAINT `intervention_ibfk_2` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE SET NULL,
  ADD CONSTRAINT `intervention_ibfk_3` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`id_expediteur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`id_destinataire`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`id_expediteur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_ibfk_2` FOREIGN KEY (`id_destinataire`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paiement`
--
ALTER TABLE `paiement`
  ADD CONSTRAINT `paiement_ibfk_1` FOREIGN KEY (`id_achat`) REFERENCES `achat` (`id_achat`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paiement_abonnement`
--
ALTER TABLE `paiement_abonnement`
  ADD CONSTRAINT `paiement_abonnement_ibfk_1` FOREIGN KEY (`id_abonnement`) REFERENCES `abonnement` (`id_abonnement`) ON DELETE CASCADE;

--
-- Contraintes pour la table `panier`
--
ALTER TABLE `panier`
  ADD CONSTRAINT `panier_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `planning`
--
ALTER TABLE `planning`
  ADD CONSTRAINT `planning_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `planning_ibfk_2` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`) ON DELETE SET NULL,
  ADD CONSTRAINT `planning_ibfk_3` FOREIGN KEY (`id_evenement`) REFERENCES `evenement` (`id_evenement`) ON DELETE SET NULL,
  ADD CONSTRAINT `planning_ibfk_4` FOREIGN KEY (`id_rdv`) REFERENCES `rendez_vous` (`id_rdv`) ON DELETE SET NULL;

--
-- Contraintes pour la table `prestataire`
--
ALTER TABLE `prestataire`
  ADD CONSTRAINT `prestataire_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reference_evenement`
--
ALTER TABLE `reference_evenement`
  ADD CONSTRAINT `fk_even` FOREIGN KEY (`id_evenement`) REFERENCES `evenement` (`id_evenement`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_util` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reference_service`
--
ALTER TABLE `reference_service`
  ADD CONSTRAINT `fk_serv` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`),
  ADD CONSTRAINT `fk_util2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  ADD CONSTRAINT `rendez_vous_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `rendez_vous_ibfk_2` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE SET NULL;

--
-- Contraintes pour la table `service`
--
ALTER TABLE `service`
  ADD CONSTRAINT `service_ibfk_1` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE SET NULL;

--
-- Contraintes pour la table `synthese_facture`
--
ALTER TABLE `synthese_facture`
  ADD CONSTRAINT `synthese_facture_ibfk_1` FOREIGN KEY (`id_facture`) REFERENCES `facture_prestataire` (`id_facture`) ON DELETE CASCADE,
  ADD CONSTRAINT `synthese_facture_ibfk_2` FOREIGN KEY (`id_intervention`) REFERENCES `intervention` (`id_intervention`) ON DELETE CASCADE;

--
-- Contraintes pour la table `token`
--
ALTER TABLE `token`
  ADD CONSTRAINT `token_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `virement`
--
ALTER TABLE `virement`
  ADD CONSTRAINT `virement_ibfk_1` FOREIGN KEY (`id_facture`) REFERENCES `facture_prestataire` (`id_facture`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
