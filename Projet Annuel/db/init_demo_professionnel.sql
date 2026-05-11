/* phpMyAdmin SQL Dump */
/* version 5.2.3 */
/* https://www.phpmyadmin.net/ */
/* */
/* Hôte : mariadb */
/* Généré le : dim. 10 mai 2026 à 17:03 */
/* Version du serveur : 11.8.6-MariaDB-ubu2404 */
/* Version de PHP : 8.3.31 */

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

/* */
/* Base de données : `projet` */
/* */

/* -------------------------------------------------------- */

/* */
/* Structure de la table `abonnement` */
/* */

CREATE TABLE `abonnement` (
  `id_abonnement` int(11) NOT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `categorie` enum('senior','prestataire') NOT NULL DEFAULT 'senior',
  `type_prestataire` tinyint(1) DEFAULT 0,
  `type` varchar(100) DEFAULT NULL,
  `prix_mois` decimal(10,2) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `prix_an` int(11) DEFAULT NULL,
  `Locaux_prestation` tinyint(1) DEFAULT NULL,
  `Trajet_offert` tinyint(1) DEFAULT NULL,
  `offre_repas` tinyint(1) DEFAULT NULL,
  `mis_en_avant` tinyint(1) DEFAULT NULL,
  `contenue1` varchar(30) NOT NULL DEFAULT '',
  `contenue2` varchar(30) NOT NULL DEFAULT '',
  `contenue3` varchar(30) NOT NULL DEFAULT '',
  `contenue4` varchar(30) NOT NULL DEFAULT '',
  `nb_avantage` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `abonnement` */
/* */

INSERT INTO `abonnement` (`id_abonnement`, `id_prestataire`, `categorie`, `type_prestataire`, `type`, `prix_mois`, `statut`, `prix_an`, `Locaux_prestation`, `Trajet_offert`, `offre_repas`, `mis_en_avant`, `contenue1`, `contenue2`, `contenue3`, `contenue4`, `nb_avantage`) VALUES
(1, NULL, 'senior', 0, 'Confort', 6.70, 'actif', 73, 1, 1, 1, 0, 'Option A', 'Option B', 'Option C', 'Option D', 1),
(2, NULL, 'senior', 1, 'Premium', 8.90, 'actif', 97, 1, 1, 0, 0, 'Option A', 'Option B', 'Option C', 'Option D', 2),
(3, NULL, 'senior', 0, 'Visibilite Pro', 11.10, 'actif', 122, 1, 0, 1, 0, 'Option A', 'Option B', 'Option C', 'Option D', 3),
(4, NULL, 'senior', 1, 'Pack Pro', 13.30, 'actif', 146, 1, 1, 0, 0, 'Option A', 'Option B', 'Option C', 'Option D', 4),
(5, NULL, 'senior', 0, 'Essentiel', 15.50, 'actif', 170, 1, 1, 1, 0, 'Option A', 'Option B', 'Option C', 'Option D', 0),
(6, NULL, 'senior', 1, 'Confort', 17.70, 'actif', 194, 1, 0, 0, 1, 'Option A', 'Option B', 'Option C', 'Option D', 1),
(7, NULL, 'senior', 0, 'Premium', 19.90, 'actif', 218, 1, 1, 1, 0, 'Option A', 'Option B', 'Option C', 'Option D', 2),
(8, NULL, 'senior', 1, 'Visibilite Pro', 4.50, 'actif', 49, 1, 1, 0, 0, 'Option A', 'Option B', 'Option C', 'Option D', 3),
(9, NULL, 'senior', 0, 'Pack Pro', 6.70, 'actif', 73, 1, 0, 1, 0, 'Option A', 'Option B', 'Option C', 'Option D', 4),
(10, NULL, 'senior', 1, 'Essentiel', 8.90, 'actif', 97, 1, 1, 0, 0, 'Option A', 'Option B', 'Option C', 'Option D', 0),
(11, NULL, 'senior', 0, 'Confort', 11.10, 'actif', 122, 1, 1, 1, 0, 'Option A', 'Option B', 'Option C', 'Option D', 1),
(12, NULL, 'senior', 1, 'Premium', 13.30, 'actif', 146, 1, 0, 0, 1, 'Option A', 'Option B', 'Option C', 'Option D', 2),
(13, NULL, 'senior', 0, 'Visibilite Pro', 15.50, 'actif', 170, 1, 1, 1, 0, 'Option A', 'Option B', 'Option C', 'Option D', 3),
(14, NULL, 'senior', 1, 'Pack Pro', 17.70, 'actif', 194, 1, 1, 0, 0, 'Option A', 'Option B', 'Option C', 'Option D', 4),
(15, NULL, 'prestataire', 0, 'Essentiel', 19.90, 'actif', 218, 1, 0, 1, 0, 'Option A', 'Option B', 'Option C', 'Option D', 0),
(16, NULL, 'prestataire', 1, 'Confort', 4.50, 'actif', 49, 1, 1, 0, 0, 'Option A', 'Option B', 'Option C', 'Option D', 1),
(17, NULL, 'prestataire', 0, 'Premium', 6.70, 'actif', 73, 1, 1, 1, 0, 'Option A', 'Option B', 'Option C', 'Option D', 2),
(18, NULL, 'prestataire', 1, 'Visibilite Pro', 8.90, 'actif', 97, 1, 0, 0, 1, 'Option A', 'Option B', 'Option C', 'Option D', 3),
(19, NULL, 'prestataire', 0, 'Pack Pro', 11.10, 'actif', 122, 1, 1, 1, 0, 'Option A', 'Option B', 'Option C', 'Option D', 4),
(20, NULL, 'prestataire', 1, 'Essentiel', 13.30, 'actif', 146, 1, 1, 0, 0, 'Option A', 'Option B', 'Option C', 'Option D', 0);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `abonnement_push` */
/* */

CREATE TABLE `abonnement_push` (
  `id_subscription` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `subscription_id` varchar(191) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* */
/* Déchargement des données de la table `abonnement_push` */
/* */

INSERT INTO `abonnement_push` (`id_subscription`, `id_utilisateur`, `subscription_id`, `actif`, `updated_at`) VALUES
(2001, 8, '00000001-aaaa-bbbb-cccc-000000000001', 1, '2026-05-02 12:00:00'),
(2002, 15, '00000002-aaaa-bbbb-cccc-000000000002', 1, '2026-05-03 12:00:00'),
(2003, 2, '00000003-aaaa-bbbb-cccc-000000000003', 0, '2026-05-04 12:00:00'),
(2004, 9, '00000004-aaaa-bbbb-cccc-000000000004', 1, '2026-05-05 12:00:00'),
(2005, 16, '00000005-aaaa-bbbb-cccc-000000000005', 1, '2026-05-06 12:00:00'),
(2006, 3, '00000006-aaaa-bbbb-cccc-000000000006', 0, '2026-05-07 12:00:00'),
(2007, 10, '00000007-aaaa-bbbb-cccc-000000000007', 1, '2026-05-08 12:00:00'),
(2008, 17, '00000008-aaaa-bbbb-cccc-000000000008', 1, '2026-05-09 12:00:00'),
(2009, 4, '00000009-aaaa-bbbb-cccc-000000000009', 0, '2026-05-10 12:00:00'),
(2010, 11, '0000000a-aaaa-bbbb-cccc-000000000010', 1, '2026-05-11 12:00:00'),
(2011, 18, '0000000b-aaaa-bbbb-cccc-000000000011', 1, '2026-05-12 12:00:00'),
(2012, 5, '0000000c-aaaa-bbbb-cccc-000000000012', 0, '2026-05-13 12:00:00'),
(2013, 12, '0000000d-aaaa-bbbb-cccc-000000000013', 1, '2026-05-14 12:00:00'),
(2014, 19, '0000000e-aaaa-bbbb-cccc-000000000014', 1, '2026-05-15 12:00:00'),
(2015, 6, '0000000f-aaaa-bbbb-cccc-000000000015', 0, '2026-05-16 12:00:00'),
(2016, 13, '00000010-aaaa-bbbb-cccc-000000000016', 1, '2026-05-17 12:00:00'),
(2017, 20, '00000011-aaaa-bbbb-cccc-000000000017', 1, '2026-05-18 12:00:00'),
(2018, 7, '00000012-aaaa-bbbb-cccc-000000000018', 0, '2026-05-19 12:00:00'),
(2019, 14, '00000013-aaaa-bbbb-cccc-000000000019', 1, '2026-05-20 12:00:00'),
(2020, 1, '00000014-aaaa-bbbb-cccc-000000000020', 1, '2026-05-21 12:00:00');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `achat` */
/* */

CREATE TABLE `achat` (
  `id_achat` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_panier` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `achat` */
/* */

INSERT INTO `achat` (`id_achat`, `id_utilisateur`, `id_panier`, `date`) VALUES
(1, 2, 1, '2026-04-02 13:00:00'),
(2, 3, 2, '2026-04-03 13:00:00'),
(3, 4, 3, '2026-04-04 13:00:00'),
(4, 5, 4, '2026-04-05 13:00:00'),
(5, 6, 5, '2026-04-06 13:00:00'),
(6, 7, 6, '2026-04-07 13:00:00'),
(7, 8, 7, '2026-04-08 13:00:00'),
(8, 9, 8, '2026-04-09 13:00:00'),
(9, 10, 9, '2026-04-10 13:00:00'),
(10, 11, 10, '2026-04-11 13:00:00'),
(11, 12, 11, '2026-04-12 13:00:00'),
(12, 13, 12, '2026-04-13 13:00:00'),
(13, 14, 13, '2026-04-14 13:00:00'),
(14, 15, 14, '2026-04-15 13:00:00'),
(15, 16, 15, '2026-04-16 13:00:00'),
(16, 17, 16, '2026-04-17 13:00:00'),
(17, 18, 17, '2026-04-18 13:00:00'),
(18, 19, 18, '2026-04-19 13:00:00'),
(19, 20, 19, '2026-04-20 13:00:00'),
(20, 1, 20, '2026-04-21 13:00:00');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `article` */
/* */

CREATE TABLE `article` (
  `id_article` int(11) NOT NULL,
  `titre` varchar(150) DEFAULT NULL,
  `image` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `article` */
/* */

INSERT INTO `article` (`id_article`, `titre`, `image`, `description`, `prix`) VALUES
(1, 'Produit boutique 1', 'art1.png', 'Description professionnelle article 1.', 13.49),
(2, 'Produit boutique 2', 'art2.png', 'Description professionnelle article 2.', 16.99),
(3, 'Produit boutique 3', 'art3.png', 'Description professionnelle article 3.', 20.49),
(4, 'Produit boutique 4', 'art4.png', 'Description professionnelle article 4.', 23.99),
(5, 'Produit boutique 5', 'art5.png', 'Description professionnelle article 5.', 27.49),
(6, 'Produit boutique 6', 'art6.png', 'Description professionnelle article 6.', 30.99),
(7, 'Produit boutique 7', 'art7.png', 'Description professionnelle article 7.', 34.49),
(8, 'Produit boutique 8', 'art8.png', 'Description professionnelle article 8.', 37.99),
(9, 'Produit boutique 9', 'art0.png', 'Description professionnelle article 9.', 41.49),
(10, 'Produit boutique 10', 'art1.png', 'Description professionnelle article 10.', 44.99),
(11, 'Produit boutique 11', 'art2.png', 'Description professionnelle article 11.', 48.49),
(12, 'Produit boutique 12', 'art3.png', 'Description professionnelle article 12.', 51.99),
(13, 'Produit boutique 13', 'art4.png', 'Description professionnelle article 13.', 55.49),
(14, 'Produit boutique 14', 'art5.png', 'Description professionnelle article 14.', 58.99),
(15, 'Produit boutique 15', 'art6.png', 'Description professionnelle article 15.', 62.49),
(16, 'Produit boutique 16', 'art7.png', 'Description professionnelle article 16.', 65.99),
(17, 'Produit boutique 17', 'art8.png', 'Description professionnelle article 17.', 69.49),
(18, 'Produit boutique 18', 'art0.png', 'Description professionnelle article 18.', 72.99),
(19, 'Produit boutique 19', 'art1.png', 'Description professionnelle article 19.', 76.49),
(20, 'Produit boutique 20', 'art2.png', 'Description professionnelle article 20.', 79.99);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `categorie` */
/* */

CREATE TABLE `categorie` (
  `id_categorie` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `valide_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* */
/* Déchargement des données de la table `categorie` */
/* */

INSERT INTO `categorie` (`id_categorie`, `nom`, `valide_admin`) VALUES
(1, 'Menage a domicile', 1),
(2, 'Jardinage', 1),
(3, 'Transport medical', 1),
(4, 'Aide aux courses', 1),
(5, 'Garde d''animaux', 1),
(6, 'Bricolage leger', 1),
(7, 'Informatique assistance', 1),
(8, 'Cours particuliers', 1),
(9, 'Massage bien-etre', 1),
(10, 'Coiffure a domicile', 1),
(11, 'Accompagnement sorties', 1),
(12, 'Lecture et loisirs', 1),
(13, 'Cuisine', 1),
(14, 'Administratif', 1),
(15, 'Yoga doux', 1),
(16, 'Photographie evenement', 1),
(17, 'Musique animation', 1),
(18, 'Repassage', 1),
(19, 'Plomberie legere', 1),
(20, 'Electricite legere', 1);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `categories` */
/* */

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* */
/* Déchargement des données de la table `categories` */
/* */

INSERT INTO `categories` (`id`, `nom`) VALUES
(1, 'Transport'),
(2, 'Soins et bien-etre'),
(3, 'Hebergement'),
(4, 'Services a domicile'),
(5, 'Loisirs'),
(6, 'Shopping livraison'),
(7, 'Aide numerique'),
(8, 'Petit bricolage'),
(9, 'Beaute'),
(10, 'Bien-etre senior'),
(11, 'Culture'),
(12, 'Sport adapte'),
(13, 'Alimentation'),
(14, 'Accompagnement medical'),
(15, 'Social'),
(16, 'Famille'),
(17, 'Education'),
(18, 'Artisanat'),
(19, 'Mobilite'),
(20, 'Evenementiel');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `champs_supplementaires` */
/* */

CREATE TABLE `champs_supplementaires` (
  `id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `input_id` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* */
/* Déchargement des données de la table `champs_supplementaires` */
/* */

INSERT INTO `champs_supplementaires` (`id`, `categorie_id`, `label`, `type`, `input_id`) VALUES
(1, 1, 'Champ metier 1', 'text', 'champ_extra_1'),
(2, 2, 'Champ metier 2', 'select', 'champ_extra_2'),
(3, 3, 'Champ metier 3', 'file', 'champ_extra_3'),
(4, 4, 'Champ metier 4', 'text', 'champ_extra_4'),
(5, 5, 'Champ metier 5', 'file', 'champ_extra_5'),
(6, 6, 'Champ metier 6', 'text', 'champ_extra_6'),
(7, 7, 'Champ metier 7', 'select', 'champ_extra_7'),
(8, 8, 'Champ metier 8', 'file', 'champ_extra_8'),
(9, 9, 'Champ metier 9', 'text', 'champ_extra_9'),
(10, 10, 'Champ metier 10', 'file', 'champ_extra_10'),
(11, 11, 'Champ metier 11', 'text', 'champ_extra_11'),
(12, 12, 'Champ metier 12', 'select', 'champ_extra_12'),
(13, 13, 'Champ metier 13', 'file', 'champ_extra_13'),
(14, 14, 'Champ metier 14', 'text', 'champ_extra_14'),
(15, 15, 'Champ metier 15', 'file', 'champ_extra_15'),
(16, 16, 'Champ metier 16', 'text', 'champ_extra_16'),
(17, 17, 'Champ metier 17', 'select', 'champ_extra_17'),
(18, 18, 'Champ metier 18', 'file', 'champ_extra_18'),
(19, 19, 'Champ metier 19', 'text', 'champ_extra_19'),
(20, 20, 'Champ metier 20', 'file', 'champ_extra_20');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `conseil` */
/* */

CREATE TABLE `conseil` (
  `id_conseil` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `titre` varchar(150) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `contenu` text DEFAULT NULL,
  `date_publication` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `conseil` */
/* */

INSERT INTO `conseil` (`id_conseil`, `id_utilisateur`, `titre`, `image`, `contenu`, `date_publication`) VALUES
(1, 5, 'Conseil sante 1', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 1.', '2026-03-02 10:00:00'),
(2, 6, 'Conseil sante 2', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 2.', '2026-03-03 10:00:00'),
(3, 7, 'Conseil sante 3', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 3.', '2026-03-04 10:00:00'),
(4, 8, 'Conseil sante 4', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 4.', '2026-03-05 10:00:00'),
(5, 9, 'Conseil sante 5', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 5.', '2026-03-06 10:00:00'),
(6, 10, 'Conseil sante 6', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 6.', '2026-03-07 10:00:00'),
(7, 11, 'Conseil sante 7', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 7.', '2026-03-08 10:00:00'),
(8, 12, 'Conseil sante 8', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 8.', '2026-03-09 10:00:00'),
(9, 13, 'Conseil sante 9', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 9.', '2026-03-10 10:00:00'),
(10, 14, 'Conseil sante 10', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 10.', '2026-03-11 10:00:00'),
(11, 15, 'Conseil sante 11', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 11.', '2026-03-12 10:00:00'),
(12, 16, 'Conseil sante 12', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 12.', '2026-03-13 10:00:00'),
(13, 17, 'Conseil sante 13', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 13.', '2026-03-14 10:00:00'),
(14, 18, 'Conseil sante 14', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 14.', '2026-03-15 10:00:00'),
(15, 19, 'Conseil sante 15', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 15.', '2026-03-16 10:00:00'),
(16, 20, 'Conseil sante 16', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 16.', '2026-03-17 10:00:00'),
(17, 1, 'Conseil sante 17', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 17.', '2026-03-18 10:00:00'),
(18, 2, 'Conseil sante 18', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 18.', '2026-03-19 10:00:00'),
(19, 3, 'Conseil sante 19', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 19.', '2026-03-20 10:00:00'),
(20, 4, 'Conseil sante 20', '', 'Hydratation, marche douce et equilibre alimentaire — fiche 20.', '2026-03-21 10:00:00');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `conseil_note` */
/* */

CREATE TABLE `conseil_note` (
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_conseil` int(11) DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* */
/* Déchargement des données de la table `conseil_note` */
/* */

INSERT INTO `conseil_note` (`id_utilisateur`, `id_conseil`, `note`, `id`) VALUES
(1, 2, 2, 201),
(2, 3, 3, 202),
(3, 4, 4, 203),
(4, 5, 5, 204),
(5, 6, 1, 205),
(6, 7, 2, 206),
(7, 8, 3, 207),
(8, 9, 4, 208),
(9, 10, 5, 209),
(10, 11, 1, 210),
(11, 12, 2, 211),
(12, 13, 3, 212),
(13, 14, 4, 213),
(14, 15, 5, 214),
(15, 1, 1, 215),
(16, 2, 2, 216),
(17, 3, 3, 217),
(18, 4, 4, 218),
(19, 5, 5, 219),
(20, 6, 1, 220);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `consultation_conseil` */
/* */

CREATE TABLE `consultation_conseil` (
  `id_utilisateur` int(11) NOT NULL,
  `id_conseil` int(11) NOT NULL,
  `date_consultation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* -------------------------------------------------------- */

/* */
/* Structure de la table `contact` */
/* */

CREATE TABLE `contact` (
  `id_contact` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `contenu` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* */
/* Déchargement des données de la table `contact` */
/* */

INSERT INTO `contact` (`id_contact`, `id_utilisateur`, `contenu`) VALUES
(1, 2, 'Message support 1 — demande d information.'),
(2, 3, 'Message support 2 — demande d information.'),
(3, 4, 'Message support 3 — demande d information.'),
(4, 5, 'Message support 4 — demande d information.'),
(5, 6, 'Message support 5 — demande d information.'),
(6, 7, 'Message support 6 — demande d information.'),
(7, 8, 'Message support 7 — demande d information.'),
(8, 9, 'Message support 8 — demande d information.'),
(9, 10, 'Message support 9 — demande d information.'),
(10, 11, 'Message support 10 — demande d information.'),
(11, 12, 'Message support 11 — demande d information.'),
(12, 13, 'Message support 12 — demande d information.'),
(13, 14, 'Message support 13 — demande d information.'),
(14, 15, 'Message support 14 — demande d information.'),
(15, 16, 'Message support 15 — demande d information.'),
(16, 17, 'Message support 16 — demande d information.'),
(17, 18, 'Message support 17 — demande d information.'),
(18, 19, 'Message support 18 — demande d information.'),
(19, 20, 'Message support 19 — demande d information.'),
(20, 1, 'Message support 20 — demande d information.');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `contrat` */
/* */

CREATE TABLE `contrat` (
  `id_contrat` int(11) NOT NULL,
  `id_devis` int(11) DEFAULT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `type_paiement` varchar(50) DEFAULT NULL,
  `type_contrat` enum('site','presta') NOT NULL DEFAULT 'presta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `contrat` */
/* */

INSERT INTO `contrat` (`id_contrat`, `id_devis`, `id_utilisateur`, `id_prestataire`, `date_debut`, `date_fin`, `nom`, `type_paiement`, `type_contrat`) VALUES
(1, 1, 2, 1, '2026-04-02', '2026-10-02', 'Contrat cadre 1', 'mois', 'presta'),
(2, NULL, 3, 2, '2026-04-03', '2026-10-03', 'Contrat cadre 2', 'mois', 'site'),
(3, 3, 4, 3, NULL, NULL, 'Contrat cadre 3', 'mois', 'presta'),
(4, NULL, 5, 4, '2026-04-05', '2026-10-05', 'Contrat cadre 4', 'mois', 'site'),
(5, 5, 6, 5, '2026-04-06', '2026-10-06', 'Contrat cadre 5', 'mois', 'presta'),
(6, NULL, 7, 1, NULL, NULL, 'Contrat cadre 6', 'mois', 'site'),
(7, 7, 8, 2, '2026-04-08', '2026-10-08', 'Contrat cadre 7', 'mois', 'presta'),
(8, NULL, 1, 3, '2026-04-09', '2026-10-09', 'Contrat cadre 8', 'mois', 'site'),
(9, 9, 2, 4, NULL, NULL, 'Contrat cadre 9', 'mois', 'presta'),
(10, NULL, 3, 5, '2026-04-11', '2026-10-11', 'Contrat cadre 10', 'mois', 'site'),
(11, 11, 4, 1, '2026-04-12', '2026-10-12', 'Contrat cadre 11', 'mois', 'presta'),
(12, NULL, 5, 2, NULL, NULL, 'Contrat cadre 12', 'mois', 'site'),
(13, 13, 6, 3, '2026-04-14', '2026-10-14', 'Contrat cadre 13', 'mois', 'presta'),
(14, NULL, 7, 4, '2026-04-15', '2026-10-15', 'Contrat cadre 14', 'mois', 'site'),
(15, 15, 8, 5, NULL, NULL, 'Contrat cadre 15', 'mois', 'presta'),
(16, NULL, 1, 1, '2026-04-17', '2026-10-17', 'Contrat cadre 16', 'mois', 'site'),
(17, 17, 2, 2, '2026-04-18', '2026-10-18', 'Contrat cadre 17', 'mois', 'presta'),
(18, NULL, 3, 3, NULL, NULL, 'Contrat cadre 18', 'mois', 'site'),
(19, 19, 4, 4, '2026-04-20', '2026-10-20', 'Contrat cadre 19', 'mois', 'presta'),
(20, NULL, 5, 5, '2026-04-21', '2026-10-21', 'Contrat cadre 20', 'mois', 'site');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `devis` */
/* */

CREATE TABLE `devis` (
  `id_devis` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `id_intervention` int(11) NOT NULL,
  `tarif_personalise` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `devis` */
/* */

INSERT INTO `devis` (`id_devis`, `id_utilisateur`, `id_prestataire`, `id_intervention`, `tarif_personalise`, `status`) VALUES
(1, 2, 1, 1, 29, 'accepté'),
(2, 3, 2, 2, 30, 'refusé'),
(3, 4, 3, 3, 31, 'en_attente'),
(4, 5, 4, 4, 32, 'accepté'),
(5, 6, 5, 5, 33, 'refusé'),
(6, 7, 1, 6, 34, 'en_attente'),
(7, 8, 2, 7, 35, 'accepté'),
(8, 9, 3, 8, 36, 'refusé'),
(9, 1, 4, 9, 37, 'en_attente'),
(10, 2, 5, 10, 38, 'accepté'),
(11, 3, 1, 11, 39, 'refusé'),
(12, 4, 2, 12, 40, 'en_attente'),
(13, 5, 3, 13, 41, 'accepté'),
(14, 6, 4, 14, 42, 'refusé'),
(15, 7, 5, 15, 43, 'en_attente'),
(16, 8, 1, 16, 44, 'accepté'),
(17, 9, 2, 17, 45, 'refusé'),
(18, 1, 3, 18, 46, 'en_attente'),
(19, 2, 4, 19, 47, 'accepté'),
(20, 3, 5, 20, 48, 'refusé');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `disponibilite` */
/* */

CREATE TABLE `disponibilite` (
  `id_disponibilite` int(11) NOT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `heure_debut` time DEFAULT NULL,
  `heure_fin` time DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `jour_semaine` enum('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') DEFAULT NULL,
  `type_regle` enum('disponible','indisponible') NOT NULL DEFAULT 'disponible',
  `recurrence` enum('unique','hebdomadaire') DEFAULT 'unique',
  `date_fin_regle` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `disponibilite` */
/* */

INSERT INTO `disponibilite` (`id_disponibilite`, `id_prestataire`, `date`, `heure_debut`, `heure_fin`, `statut`, `jour_semaine`, `type_regle`, `recurrence`, `date_fin_regle`) VALUES
(1, 1, '2026-07-02', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(2, 2, '2026-07-03', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(3, 3, '2026-07-04', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(4, 4, '2026-07-05', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(5, 5, '2026-07-06', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(6, 1, '2026-07-07', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(7, 2, '2026-07-08', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(8, 3, '2026-07-09', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(9, 4, '2026-07-10', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(10, 5, '2026-07-11', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(11, 1, '2026-07-12', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(12, 2, '2026-07-13', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(13, 3, '2026-07-14', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(14, 4, '2026-07-15', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(15, 5, '2026-07-16', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(16, 1, '2026-07-17', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(17, 2, '2026-07-18', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(18, 3, '2026-07-19', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(19, 4, '2026-07-20', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(20, 5, '2026-07-21', '08:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `document` */
/* */

CREATE TABLE `document` (
  `id_document` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `type_document` enum('PF','CIR','CIV','habilitation','diplome','autre') DEFAULT 'autre',
  `nom_fichier` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) DEFAULT NULL,
  `date_upload` datetime DEFAULT current_timestamp(),
  `valide` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* */
/* Déchargement des données de la table `document` */
/* */

INSERT INTO `document` (`id_document`, `id_utilisateur`, `type_document`, `nom_fichier`, `chemin_fichier`, `date_upload`, `valide`) VALUES
(1, 12, 'CIR', 'doc_1_cir.pdf', NULL, '2026-05-10 10:00:00', 0),
(2, 13, 'CIV', 'doc_2_civ.pdf', NULL, '2026-05-10 10:00:00', 0),
(3, 14, 'diplome', 'doc_3_diplome.pdf', NULL, '2026-05-10 10:00:00', 0),
(4, 15, 'autre', 'doc_4_autre.pdf', NULL, '2026-05-10 10:00:00', 0),
(5, 11, 'PF', 'doc_5_pf.pdf', NULL, '2026-05-10 10:00:00', 0),
(6, 12, 'CIR', 'doc_6_cir.pdf', NULL, '2026-05-10 10:00:00', 0),
(7, 13, 'CIV', 'doc_7_civ.pdf', NULL, '2026-05-10 10:00:00', 1),
(8, 14, 'diplome', 'doc_8_diplome.pdf', NULL, '2026-05-10 10:00:00', 0),
(9, 15, 'autre', 'doc_9_autre.pdf', NULL, '2026-05-10 10:00:00', 0),
(10, 11, 'PF', 'doc_10_pf.pdf', NULL, '2026-05-10 10:00:00', 0),
(11, 12, 'CIR', 'doc_11_cir.pdf', NULL, '2026-05-10 10:00:00', 0),
(12, 13, 'CIV', 'doc_12_civ.pdf', NULL, '2026-05-10 10:00:00', 0),
(13, 14, 'diplome', 'doc_13_diplome.pdf', NULL, '2026-05-10 10:00:00', 0),
(14, 15, 'autre', 'doc_14_autre.pdf', NULL, '2026-05-10 10:00:00', 1),
(15, 11, 'PF', 'doc_15_pf.pdf', NULL, '2026-05-10 10:00:00', 0),
(16, 12, 'CIR', 'doc_16_cir.pdf', NULL, '2026-05-10 10:00:00', 0),
(17, 13, 'CIV', 'doc_17_civ.pdf', NULL, '2026-05-10 10:00:00', 0),
(18, 14, 'diplome', 'doc_18_diplome.pdf', NULL, '2026-05-10 10:00:00', 0),
(19, 15, 'autre', 'doc_19_autre.pdf', NULL, '2026-05-10 10:00:00', 0),
(20, 11, 'PF', 'doc_20_pf.pdf', NULL, '2026-05-10 10:00:00', 0);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `document_txt` */
/* */

CREATE TABLE `document_txt` (
  `id_document_txt` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `categorie_text` varchar(50) NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* -------------------------------------------------------- */

/* */
/* Structure de la table `evaluation` */
/* */
/* */
/* Déchargement des données de la table `document_txt` */
/* */

INSERT INTO `document_txt` (`id_document_txt`, `id_utilisateur`, `categorie_text`, `contenu`) VALUES
(1, 4, 'experience', 'Plus de 6 ans dans le domaine.'),
(2, 5, 'experience', 'Plus de 7 ans dans le domaine.'),
(3, 6, 'experience', 'Plus de 8 ans dans le domaine.'),
(4, 7, 'experience', 'Plus de 9 ans dans le domaine.'),
(5, 8, 'experience', 'Plus de 10 ans dans le domaine.'),
(6, 9, 'experience', 'Plus de 11 ans dans le domaine.'),
(7, 10, 'experience', 'Plus de 12 ans dans le domaine.'),
(8, 11, 'experience', 'Plus de 13 ans dans le domaine.'),
(9, 12, 'experience', 'Plus de 14 ans dans le domaine.'),
(10, 13, 'experience', 'Plus de 15 ans dans le domaine.'),
(11, 14, 'experience', 'Plus de 16 ans dans le domaine.'),
(12, 15, 'experience', 'Plus de 17 ans dans le domaine.'),
(13, 16, 'experience', 'Plus de 18 ans dans le domaine.'),
(14, 17, 'experience', 'Plus de 19 ans dans le domaine.'),
(15, 18, 'experience', 'Plus de 20 ans dans le domaine.'),
(16, 19, 'experience', 'Plus de 21 ans dans le domaine.'),
(17, 20, 'experience', 'Plus de 22 ans dans le domaine.'),
(18, 1, 'experience', 'Plus de 23 ans dans le domaine.'),
(19, 2, 'experience', 'Plus de 24 ans dans le domaine.'),
(20, 3, 'experience', 'Plus de 25 ans dans le domaine.');


CREATE TABLE `evaluation` (
  `id_evaluation` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_service` int(11) DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `evaluation` */
/* */

INSERT INTO `evaluation` (`id_evaluation`, `id_utilisateur`, `id_service`, `note`, `commentaire`, `date`) VALUES
(1, 2, 1, 4, 'Avis client 1 — prestation conforme.', '2026-04-02'),
(2, 3, 2, 5, 'Avis client 2 — prestation conforme.', '2026-04-03'),
(3, 4, 3, 3, 'Avis client 3 — prestation conforme.', '2026-04-04'),
(4, 5, 4, 4, 'Avis client 4 — prestation conforme.', '2026-04-05'),
(5, 6, 5, 5, 'Avis client 5 — prestation conforme.', '2026-04-06'),
(6, 7, 6, 3, 'Avis client 6 — prestation conforme.', '2026-04-07'),
(7, 8, 7, 4, 'Avis client 7 — prestation conforme.', '2026-04-08'),
(8, 9, 8, 5, 'Avis client 8 — prestation conforme.', '2026-04-09'),
(9, 10, 9, 3, 'Avis client 9 — prestation conforme.', '2026-04-10'),
(10, 1, 10, 4, 'Avis client 10 — prestation conforme.', '2026-04-11'),
(11, 2, 11, 5, 'Avis client 11 — prestation conforme.', '2026-04-12'),
(12, 3, 12, 3, 'Avis client 12 — prestation conforme.', '2026-04-13'),
(13, 4, 13, 4, 'Avis client 13 — prestation conforme.', '2026-04-14'),
(14, 5, 14, 5, 'Avis client 14 — prestation conforme.', '2026-04-15'),
(15, 6, 15, 3, 'Avis client 15 — prestation conforme.', '2026-04-16'),
(16, 7, 16, 4, 'Avis client 16 — prestation conforme.', '2026-04-17'),
(17, 8, 17, 5, 'Avis client 17 — prestation conforme.', '2026-04-18'),
(18, 9, 18, 3, 'Avis client 18 — prestation conforme.', '2026-04-19'),
(19, 10, 19, 4, 'Avis client 19 — prestation conforme.', '2026-04-20'),
(20, 1, 20, 5, 'Avis client 20 — prestation conforme.', '2026-04-21');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `evenement` */
/* */

CREATE TABLE `evenement` (
  `id_evenement` int(11) NOT NULL,
  `nom` varchar(150) DEFAULT NULL,
  `image` varchar(30) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `tarif` decimal(10,2) DEFAULT NULL,
  `lieu` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `evenement` */
/* */

INSERT INTO `evenement` (`id_evenement`, `nom`, `image`, `date`, `description`, `tarif`, `lieu`) VALUES
(1, 'Atelier communautaire 1', '', '2026-07-11 14:00:00', 'Seance conviviale sur le theme 1.', 7.5, 'Centre social Lyon 2'),
(2, 'Atelier communautaire 2', '', '2026-08-12 14:00:00', 'Seance conviviale sur le theme 2.', 10.0, 'Centre social Lyon 3'),
(3, 'Atelier communautaire 3', '', '2026-09-13 14:00:00', 'Seance conviviale sur le theme 3.', 12.5, 'Centre social Lyon 4'),
(4, 'Atelier communautaire 4', '', '2026-10-14 14:00:00', 'Seance conviviale sur le theme 4.', 15.0, 'Centre social Lyon 5'),
(5, 'Atelier communautaire 5', '', '2026-11-15 14:00:00', 'Seance conviviale sur le theme 5.', 17.5, 'Centre social Lyon 1'),
(6, 'Atelier communautaire 6', '', '2026-06-16 14:00:00', 'Seance conviviale sur le theme 6.', 20.0, 'Centre social Lyon 2'),
(7, 'Atelier communautaire 7', '', '2026-07-17 14:00:00', 'Seance conviviale sur le theme 7.', 22.5, 'Centre social Lyon 3'),
(8, 'Atelier communautaire 8', '', '2026-08-18 14:00:00', 'Seance conviviale sur le theme 8.', 25.0, 'Centre social Lyon 4'),
(9, 'Atelier communautaire 9', '', '2026-09-19 14:00:00', 'Seance conviviale sur le theme 9.', 27.5, 'Centre social Lyon 5'),
(10, 'Atelier communautaire 10', '', '2026-10-20 14:00:00', 'Seance conviviale sur le theme 10.', 30.0, 'Centre social Lyon 1'),
(11, 'Atelier communautaire 11', '', '2026-11-21 14:00:00', 'Seance conviviale sur le theme 11.', 32.5, 'Centre social Lyon 2'),
(12, 'Atelier communautaire 12', '', '2026-06-22 14:00:00', 'Seance conviviale sur le theme 12.', 35.0, 'Centre social Lyon 3'),
(13, 'Atelier communautaire 13', '', '2026-07-23 14:00:00', 'Seance conviviale sur le theme 13.', 37.5, 'Centre social Lyon 4'),
(14, 'Atelier communautaire 14', '', '2026-08-24 14:00:00', 'Seance conviviale sur le theme 14.', 40.0, 'Centre social Lyon 5'),
(15, 'Atelier communautaire 15', '', '2026-09-25 14:00:00', 'Seance conviviale sur le theme 15.', 42.5, 'Centre social Lyon 1'),
(16, 'Atelier communautaire 16', '', '2026-10-26 14:00:00', 'Seance conviviale sur le theme 16.', 45.0, 'Centre social Lyon 2'),
(17, 'Atelier communautaire 17', '', '2026-11-27 14:00:00', 'Seance conviviale sur le theme 17.', 47.5, 'Centre social Lyon 3'),
(18, 'Atelier communautaire 18', '', '2026-06-10 14:00:00', 'Seance conviviale sur le theme 18.', 50.0, 'Centre social Lyon 4'),
(19, 'Atelier communautaire 19', '', '2026-07-11 14:00:00', 'Seance conviviale sur le theme 19.', 52.5, 'Centre social Lyon 5'),
(20, 'Atelier communautaire 20', '', '2026-08-12 14:00:00', 'Seance conviviale sur le theme 20.', 55.0, 'Centre social Lyon 1');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `facture_prestataire` */
/* */

CREATE TABLE `facture_prestataire` (
  `id_facture` int(11) NOT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `mois` varchar(20) DEFAULT NULL,
  `montant_total` decimal(10,2) DEFAULT NULL,
  `date_generation` date DEFAULT NULL,
  `fichier_pdf` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `facture_prestataire` */
/* */

INSERT INTO `facture_prestataire` (`id_facture`, `id_prestataire`, `mois`, `montant_total`, `date_generation`, `fichier_pdf`) VALUES
(1, 1, '2026-02', 157.5, '2026-05-02', NULL),
(2, 2, '2026-03', 195.0, '2026-05-03', NULL),
(3, 3, '2026-04', 232.5, '2026-05-04', NULL),
(4, 4, '2026-05', 270.0, '2026-05-05', NULL),
(5, 5, '2026-06', 307.5, '2026-05-06', NULL),
(6, 1, '2026-01', 345.0, '2026-05-07', NULL),
(7, 2, '2026-02', 382.5, '2026-05-08', NULL),
(8, 3, '2026-03', 420.0, '2026-05-09', NULL),
(9, 4, '2026-04', 457.5, '2026-05-10', NULL),
(10, 5, '2026-05', 495.0, '2026-05-11', NULL),
(11, 1, '2026-06', 532.5, '2026-05-12', NULL),
(12, 2, '2026-01', 570.0, '2026-05-13', NULL),
(13, 3, '2026-02', 607.5, '2026-05-14', NULL),
(14, 4, '2026-03', 645.0, '2026-05-15', NULL),
(15, 5, '2026-04', 682.5, '2026-05-16', NULL),
(16, 1, '2026-05', 720.0, '2026-05-17', NULL),
(17, 2, '2026-06', 757.5, '2026-05-18', NULL),
(18, 3, '2026-01', 795.0, '2026-05-19', NULL),
(19, 4, '2026-02', 832.5, '2026-05-20', NULL),
(20, 5, '2026-03', 870.0, '2026-05-21', NULL);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `intervention` */
/* */

CREATE TABLE `intervention` (
  `id_intervention` int(11) NOT NULL,
  `id_service` int(11) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_rdv` int(11) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `intervention` */
/* */

INSERT INTO `intervention` (`id_intervention`, `id_service`, `id_prestataire`, `id_utilisateur`, `id_rdv`, `statut`, `montant`) VALUES
(1, 1, 1, 2, 1, 'terminé', 32),
(2, 2, 2, 3, 2, 'devis', 34),
(3, 3, 3, 4, 3, 'annulé', 36),
(4, 4, 4, 5, 4, 'confirmé', 38),
(5, 5, 5, 6, 5, 'confirmé', 40),
(6, 6, 1, 7, 6, 'terminé', 42),
(7, 7, 2, 8, 7, 'devis', 44),
(8, 8, 3, 9, 8, 'annulé', 46),
(9, 9, 4, 1, 9, 'confirmé', 48),
(10, 10, 5, 2, 10, 'confirmé', 50),
(11, 11, 1, 3, 11, 'terminé', 52),
(12, 12, 2, 4, 12, 'devis', 54),
(13, 13, 3, 5, 13, 'annulé', 56),
(14, 14, 4, 6, 14, 'confirmé', 58),
(15, 15, 5, 7, 15, 'confirmé', 60),
(16, 16, 1, 8, 16, 'terminé', 62),
(17, 17, 2, 9, 17, 'devis', 64),
(18, 18, 3, 1, 18, 'annulé', 66),
(19, 19, 4, 2, 19, 'confirmé', 68),
(20, 20, 5, 3, 20, 'confirmé', 70);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `lien_contact` */
/* */

CREATE TABLE `lien_contact` (
  `id_lien` int(11) NOT NULL,
  `id_user1` int(11) NOT NULL,
  `id_user2` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* -------------------------------------------------------- */

/* */
/* Structure de la table `lien_contact_state` */
/* */
/* */
/* Déchargement des données de la table `lien_contact` */
/* */

INSERT INTO `lien_contact` (`id_lien`, `id_user1`, `id_user2`) VALUES
(1, 2, 15),
(2, 3, 16),
(3, 4, 17),
(4, 5, 18),
(5, 6, 19),
(6, 7, 20),
(7, 8, 11),
(8, 9, 12),
(9, 10, 13),
(10, 1, 14),
(11, 2, 15),
(12, 3, 16),
(13, 4, 17),
(14, 5, 18),
(15, 6, 19),
(16, 7, 20),
(17, 8, 11),
(18, 9, 12),
(19, 10, 13),
(20, 1, 14);


CREATE TABLE `lien_contact_state` (
  `id` int(11) NOT NULL,
  `id_user1` int(11) DEFAULT NULL,
  `id_user2` int(11) DEFAULT NULL,
  `state` enum('bloquer','demande_ami') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* -------------------------------------------------------- */

/* */
/* Structure de la table `message` */
/* */
/* */
/* Déchargement des données de la table `lien_contact_state` */
/* */

INSERT INTO `lien_contact_state` (`id`, `id_user1`, `id_user2`, `state`) VALUES
(1, 2, 15, 'demande_ami'),
(2, 3, 16, 'bloquer'),
(3, 4, 17, 'demande_ami'),
(4, 5, 18, 'bloquer'),
(5, 6, 19, 'demande_ami'),
(6, 7, 20, 'bloquer'),
(7, 8, 11, 'demande_ami'),
(8, 9, 12, 'bloquer'),
(9, 10, 13, 'demande_ami'),
(10, 1, 14, 'bloquer'),
(11, 2, 15, 'demande_ami'),
(12, 3, 16, 'bloquer'),
(13, 4, 17, 'demande_ami'),
(14, 5, 18, 'bloquer'),
(15, 6, 19, 'demande_ami'),
(16, 7, 20, 'bloquer'),
(17, 8, 11, 'demande_ami'),
(18, 9, 12, 'bloquer'),
(19, 10, 13, 'demande_ami'),
(20, 1, 14, 'bloquer');


CREATE TABLE `message` (
  `id_message` int(11) NOT NULL,
  `id_expediteur` int(11) DEFAULT NULL,
  `id_destinataire` int(11) DEFAULT NULL,
  `contenu` text DEFAULT NULL,
  `date_envoie` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `message` */
/* */

INSERT INTO `message` (`id_message`, `id_expediteur`, `id_destinataire`, `contenu`, `date_envoie`) VALUES
(1, 2, 7, 'Echange professionnel 1.', '2026-05-02 11:00:00'),
(2, 3, 8, 'Echange professionnel 2.', '2026-05-03 11:00:00'),
(3, 4, 9, 'Echange professionnel 3.', '2026-05-04 11:00:00'),
(4, 5, 10, 'Echange professionnel 4.', '2026-05-05 11:00:00'),
(5, 6, 1, 'Echange professionnel 5.', '2026-05-06 11:00:00'),
(6, 7, 2, 'Echange professionnel 6.', '2026-05-07 11:00:00'),
(7, 8, 3, 'Echange professionnel 7.', '2026-05-08 11:00:00'),
(8, 9, 4, 'Echange professionnel 8.', '2026-05-09 11:00:00'),
(9, 10, 5, 'Echange professionnel 9.', '2026-05-10 11:00:00'),
(10, 1, 6, 'Echange professionnel 10.', '2026-05-11 11:00:00'),
(11, 2, 7, 'Echange professionnel 11.', '2026-05-12 11:00:00'),
(12, 3, 8, 'Echange professionnel 12.', '2026-05-13 11:00:00'),
(13, 4, 9, 'Echange professionnel 13.', '2026-05-14 11:00:00'),
(14, 5, 10, 'Echange professionnel 14.', '2026-05-15 11:00:00'),
(15, 6, 1, 'Echange professionnel 15.', '2026-05-16 11:00:00'),
(16, 7, 2, 'Echange professionnel 16.', '2026-05-17 11:00:00'),
(17, 8, 3, 'Echange professionnel 17.', '2026-05-18 11:00:00'),
(18, 9, 4, 'Echange professionnel 18.', '2026-05-19 11:00:00'),
(19, 10, 5, 'Echange professionnel 19.', '2026-05-20 11:00:00'),
(20, 1, 6, 'Echange professionnel 20.', '2026-05-21 11:00:00');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `modele_notification` */
/* */

CREATE TABLE `modele_notification` (
  `id_modele` int(11) NOT NULL,
  `cle` varchar(100) NOT NULL,
  `titre` varchar(50) NOT NULL,
  `contenu` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* */
/* Déchargement des données de la table `modele_notification` */
/* */

INSERT INTO `modele_notification` (`id_modele`, `cle`, `titre`, `contenu`) VALUES
(1, 'reservation_service', 'Titre modele 1', 'Contenu modele numerote 1 avec variables __nom__.'),
(2, 'reservation_evenement', 'Titre modele 2', 'Contenu modele numerote 2 avec variables __nom__.'),
(3, 'commande_creee', 'Titre modele 3', 'Contenu modele numerote 3 avec variables __nom__.'),
(4, 'paiement_mise_a_jour', 'Titre modele 4', 'Contenu modele numerote 4 avec variables __nom__.'),
(5, 'contact_recu', 'Titre modele 5', 'Contenu modele numerote 5 avec variables __nom__.'),
(6, 'abonnement_active', 'Titre modele 6', 'Contenu modele numerote 6 avec variables __nom__.'),
(7, 'rdv_rappel', 'Titre modele 7', 'Contenu modele numerote 7 avec variables __nom__.'),
(8, 'devis_recu', 'Titre modele 8', 'Contenu modele numerote 8 avec variables __nom__.'),
(9, 'devis_accepte', 'Titre modele 9', 'Contenu modele numerote 9 avec variables __nom__.'),
(10, 'avis_demande', 'Titre modele 10', 'Contenu modele numerote 10 avec variables __nom__.'),
(11, 'contrat_renouv', 'Titre modele 11', 'Contenu modele numerote 11 avec variables __nom__.'),
(12, 'newsletter', 'Titre modele 12', 'Contenu modele numerote 12 avec variables __nom__.'),
(13, 'promo', 'Titre modele 13', 'Contenu modele numerote 13 avec variables __nom__.'),
(14, 'maintenance', 'Titre modele 14', 'Contenu modele numerote 14 avec variables __nom__.'),
(15, 'securite', 'Titre modele 15', 'Contenu modele numerote 15 avec variables __nom__.'),
(16, 'compte_valide', 'Titre modele 16', 'Contenu modele numerote 16 avec variables __nom__.'),
(17, 'doc_manquant', 'Titre modele 17', 'Contenu modele numerote 17 avec variables __nom__.'),
(18, 'facture_dispo', 'Titre modele 18', 'Contenu modele numerote 18 avec variables __nom__.'),
(19, 'message_systeme', 'Titre modele 19', 'Contenu modele numerote 19 avec variables __nom__.'),
(20, 'bienvenue', 'Titre modele 20', 'Contenu modele numerote 20 avec variables __nom__.');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `notification` */
/* */

CREATE TABLE `notification` (
  `id_notification` int(11) NOT NULL,
  `id_expediteur` int(11) DEFAULT NULL,
  `id_destinataire` int(11) DEFAULT NULL,
  `Titre` varchar(50) NOT NULL,
  `contenu` text DEFAULT NULL,
  `date_envoie` datetime DEFAULT NULL,
  `lu` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* */
/* Déchargement des données de la table `notification` */
/* */

INSERT INTO `notification` (`id_notification`, `id_expediteur`, `id_destinataire`, `Titre`, `contenu`, `date_envoie`, `lu`) VALUES
(1, 2, 2, 'Notification 1', 'Detail notification professionnelle 1.', '2026-05-02 09:30:00', 1),
(2, 3, 3, 'Notification 2', 'Detail notification professionnelle 2.', '2026-05-03 09:30:00', 0),
(3, 4, 4, 'Notification 3', 'Detail notification professionnelle 3.', '2026-05-04 09:30:00', 1),
(4, NULL, 5, 'Notification 4', 'Detail notification professionnelle 4.', '2026-05-05 09:30:00', 0),
(5, 6, 6, 'Notification 5', 'Detail notification professionnelle 5.', '2026-05-06 09:30:00', 1),
(6, 7, 7, 'Notification 6', 'Detail notification professionnelle 6.', '2026-05-07 09:30:00', 0),
(7, 8, 8, 'Notification 7', 'Detail notification professionnelle 7.', '2026-05-08 09:30:00', 1),
(8, NULL, 9, 'Notification 8', 'Detail notification professionnelle 8.', '2026-05-09 09:30:00', 0),
(9, 10, 10, 'Notification 9', 'Detail notification professionnelle 9.', '2026-05-10 09:30:00', 1),
(10, 1, 11, 'Notification 10', 'Detail notification professionnelle 10.', '2026-05-11 09:30:00', 0),
(11, 2, 12, 'Notification 11', 'Detail notification professionnelle 11.', '2026-05-12 09:30:00', 1),
(12, NULL, 13, 'Notification 12', 'Detail notification professionnelle 12.', '2026-05-13 09:30:00', 0),
(13, 4, 14, 'Notification 13', 'Detail notification professionnelle 13.', '2026-05-14 09:30:00', 1),
(14, 5, 15, 'Notification 14', 'Detail notification professionnelle 14.', '2026-05-15 09:30:00', 0),
(15, 6, 16, 'Notification 15', 'Detail notification professionnelle 15.', '2026-05-16 09:30:00', 1),
(16, NULL, 17, 'Notification 16', 'Detail notification professionnelle 16.', '2026-05-17 09:30:00', 0),
(17, 8, 18, 'Notification 17', 'Detail notification professionnelle 17.', '2026-05-18 09:30:00', 1),
(18, 9, 19, 'Notification 18', 'Detail notification professionnelle 18.', '2026-05-19 09:30:00', 0),
(19, 10, 20, 'Notification 19', 'Detail notification professionnelle 19.', '2026-05-20 09:30:00', 1),
(20, NULL, 1, 'Notification 20', 'Detail notification professionnelle 20.', '2026-05-21 09:30:00', 0);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `paiement` */
/* */

CREATE TABLE `paiement` (
  `id_paiement` int(11) NOT NULL,
  `id_achat` int(11) DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `mode` varchar(50) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `paiement` */
/* */

INSERT INTO `paiement` (`id_paiement`, `id_achat`, `montant`, `date`, `mode`, `statut`) VALUES
(1, 1, 25.5, '2026-04-02 14:00:00', 'stripe', 'paid'),
(2, 2, 31.0, '2026-04-03 14:00:00', 'stripe', 'pending'),
(3, 3, 36.5, '2026-04-04 14:00:00', 'stripe', 'paid'),
(4, 4, 42.0, '2026-04-05 14:00:00', 'stripe', 'pending'),
(5, 5, 47.5, '2026-04-06 14:00:00', 'stripe', 'paid'),
(6, 6, 53.0, '2026-04-07 14:00:00', 'stripe', 'pending'),
(7, 7, 58.5, '2026-04-08 14:00:00', 'stripe', 'paid'),
(8, 8, 64.0, '2026-04-09 14:00:00', 'stripe', 'pending'),
(9, 9, 69.5, '2026-04-10 14:00:00', 'stripe', 'paid'),
(10, 10, 75.0, '2026-04-11 14:00:00', 'stripe', 'pending'),
(11, 11, 80.5, '2026-04-12 14:00:00', 'stripe', 'paid'),
(12, 12, 86.0, '2026-04-13 14:00:00', 'stripe', 'pending'),
(13, 13, 91.5, '2026-04-14 14:00:00', 'stripe', 'paid'),
(14, 14, 97.0, '2026-04-15 14:00:00', 'stripe', 'pending'),
(15, 15, 102.5, '2026-04-16 14:00:00', 'stripe', 'paid'),
(16, 16, 108.0, '2026-04-17 14:00:00', 'stripe', 'pending'),
(17, 17, 113.5, '2026-04-18 14:00:00', 'stripe', 'paid'),
(18, 18, 119.0, '2026-04-19 14:00:00', 'stripe', 'pending'),
(19, 19, 124.5, '2026-04-20 14:00:00', 'stripe', 'paid'),
(20, 20, 130.0, '2026-04-21 14:00:00', 'stripe', 'pending');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `paiement_abonnement` */
/* */

CREATE TABLE `paiement_abonnement` (
  `id_paiement_abonnement` int(11) NOT NULL,
  `id_abonnement` int(11) DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `mode` varchar(50) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `paiement_abonnement` */
/* */

INSERT INTO `paiement_abonnement` (`id_paiement_abonnement`, `id_abonnement`, `montant`, `date`, `mode`, `statut`) VALUES
(101, 1, 32, '2026-04-02', 'stripe', 'paid'),
(102, 2, 34, '2026-04-03', 'stripe', 'paid'),
(103, 3, 36, '2026-04-04', 'stripe', 'paid'),
(104, 4, 38, '2026-04-05', 'stripe', 'paid'),
(105, 5, 40, '2026-04-06', 'stripe', 'paid'),
(106, 6, 42, '2026-04-07', 'stripe', 'paid'),
(107, 7, 44, '2026-04-08', 'stripe', 'paid'),
(108, 8, 46, '2026-04-09', 'stripe', 'paid'),
(109, 9, 48, '2026-04-10', 'stripe', 'paid'),
(110, 10, 50, '2026-04-11', 'stripe', 'paid'),
(111, 11, 52, '2026-04-12', 'stripe', 'paid'),
(112, 12, 54, '2026-04-13', 'stripe', 'paid'),
(113, 13, 56, '2026-04-14', 'stripe', 'paid'),
(114, 14, 58, '2026-04-15', 'stripe', 'paid'),
(115, 15, 60, '2026-04-16', 'stripe', 'paid'),
(116, 16, 62, '2026-04-17', 'stripe', 'paid'),
(117, 17, 64, '2026-04-18', 'stripe', 'paid'),
(118, 18, 66, '2026-04-19', 'stripe', 'paid'),
(119, 19, 68, '2026-04-20', 'stripe', 'paid'),
(120, 20, 70, '2026-04-21', 'stripe', 'paid');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `panier` */
/* */

CREATE TABLE `panier` (
  `id_panier` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `date_creation` datetime DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `panier` */
/* */

INSERT INTO `panier` (`id_panier`, `id_utilisateur`, `date_creation`, `statut`) VALUES
(1, 2, '2026-04-02 12:00:00', 'pending_stripe'),
(2, 3, '2026-04-03 12:00:00', 'pending_stripe'),
(3, 4, '2026-04-04 12:00:00', 'pending_stripe'),
(4, 5, '2026-04-05 12:00:00', 'pending_stripe'),
(5, 6, '2026-04-06 12:00:00', 'pending_stripe'),
(6, 7, '2026-04-07 12:00:00', 'pending_stripe'),
(7, 8, '2026-04-08 12:00:00', 'pending_stripe'),
(8, 9, '2026-04-09 12:00:00', 'pending_stripe'),
(9, 10, '2026-04-10 12:00:00', 'pending_stripe'),
(10, 11, '2026-04-11 12:00:00', 'pending_stripe'),
(11, 12, '2026-04-12 12:00:00', 'pending_stripe'),
(12, 13, '2026-04-13 12:00:00', 'pending_stripe'),
(13, 14, '2026-04-14 12:00:00', 'pending_stripe'),
(14, 15, '2026-04-15 12:00:00', 'pending_stripe'),
(15, 16, '2026-04-16 12:00:00', 'pending_stripe'),
(16, 17, '2026-04-17 12:00:00', 'pending_stripe'),
(17, 18, '2026-04-18 12:00:00', 'pending_stripe'),
(18, 19, '2026-04-19 12:00:00', 'pending_stripe'),
(19, 20, '2026-04-20 12:00:00', 'pending_stripe'),
(20, 1, '2026-04-21 12:00:00', 'pending_stripe');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `prestataire` */
/* */

CREATE TABLE `prestataire` (
  `id_prestataire` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `photo_profil` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `valider` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `prestataire` */
/* */

INSERT INTO `prestataire` (`id_prestataire`, `id_utilisateur`, `type`, `photo_profil`, `telephone`, `valider`) VALUES
(1, 11, 'Soins', 'profil_1.jpg', '07000000000', 1),
(2, 12, 'Transport', 'profil_2.jpg', '07000000001', 1),
(3, 13, 'Menage', 'profil_3.jpg', '07000000002', 1),
(4, 14, 'Jardinage', 'profil_4.jpg', '07000000003', 1),
(5, 15, 'Accompagnement', 'profil_5.jpg', '07000000004', 1);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `reference_article` */
/* */

CREATE TABLE `reference_article` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_panier` int(11) NOT NULL,
  `id_article` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* */
/* Déchargement des données de la table `reference_article` */
/* */

INSERT INTO `reference_article` (`id`, `id_utilisateur`, `id_panier`, `id_article`) VALUES
(1, 2, 1, 1),
(2, 3, 2, 2),
(3, 4, 3, 3),
(4, 5, 4, 4),
(5, 1, 5, 5),
(6, 2, 6, 6),
(7, 3, 7, 7),
(8, 4, 8, 8),
(9, 5, 9, 9),
(10, 1, 10, 10),
(11, 2, 11, 11),
(12, 3, 12, 12),
(13, 4, 13, 13),
(14, 5, 14, 14),
(15, 1, 15, 15),
(16, 2, 16, 16),
(17, 3, 17, 17),
(18, 4, 18, 18),
(19, 5, 19, 19),
(20, 1, 20, 20);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `reference_evenement` */
/* */

CREATE TABLE `reference_evenement` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_evenement` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `reference_evenement` */
/* */

INSERT INTO `reference_evenement` (`id`, `id_utilisateur`, `id_evenement`) VALUES
(101, 2, 2),
(102, 3, 3),
(103, 4, 4),
(104, 5, 5),
(105, 6, 6),
(106, 7, 7),
(107, 8, 8),
(108, 9, 9),
(109, 10, 10),
(110, 11, 11),
(111, 12, 12),
(112, 13, 13),
(113, 14, 14),
(114, 15, 15),
(115, 16, 16),
(116, 17, 17),
(117, 18, 18),
(118, 19, 19),
(119, 20, 20),
(120, 1, 1);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `reference_service` */
/* */

CREATE TABLE `reference_service` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_service` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* */
/* Déchargement des données de la table `reference_service` */
/* */

INSERT INTO `reference_service` (`id`, `id_utilisateur`, `id_service`) VALUES
(301, 2, 1),
(302, 3, 2),
(303, 4, 3),
(304, 5, 4),
(305, 6, 5),
(306, 7, 6),
(307, 8, 7),
(308, 9, 8),
(309, 10, 9),
(310, 11, 10),
(311, 12, 11),
(312, 13, 12),
(313, 14, 13),
(314, 15, 14),
(315, 16, 15),
(316, 17, 16),
(317, 18, 17),
(318, 19, 18),
(319, 20, 19),
(320, 1, 20);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `rendez_vous` */
/* */

CREATE TABLE `rendez_vous` (
  `id_rdv` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `date_debut` datetime DEFAULT NULL,
  `date_fin` datetime NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `statut` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `rendez_vous` */
/* */

INSERT INTO `rendez_vous` (`id_rdv`, `id_utilisateur`, `id_prestataire`, `date_debut`, `date_fin`, `type`, `statut`) VALUES
(1, 2, 1, '2026-06-02 09:00:00', '2026-06-02 10:30:00', 'Prestation 1', 'confirmé'),
(2, 3, 2, '2026-06-03 09:00:00', '2026-06-03 10:30:00', 'Prestation 2', 'confirmé'),
(3, 4, 3, '2026-06-04 09:00:00', '2026-06-04 10:30:00', 'Prestation 3', 'confirmé'),
(4, 5, NULL, '2026-06-05 09:00:00', '2026-06-05 10:30:00', 'Prestation 4', 'confirmé'),
(5, 6, 5, '2026-06-06 09:00:00', '2026-06-06 10:30:00', 'Prestation 5', 'confirmé'),
(6, 7, 1, '2026-06-07 09:00:00', '2026-06-07 10:30:00', 'Prestation 6', 'confirmé'),
(7, 8, 2, '2026-06-08 09:00:00', '2026-06-08 10:30:00', 'Prestation 7', 'confirmé'),
(8, 9, NULL, '2026-06-09 09:00:00', '2026-06-09 10:30:00', 'Prestation 8', 'confirmé'),
(9, 10, 4, '2026-06-10 09:00:00', '2026-06-10 10:30:00', 'Prestation 9', 'confirmé'),
(10, 1, 5, '2026-06-11 09:00:00', '2026-06-11 10:30:00', 'Prestation 10', 'confirmé'),
(11, 2, 1, '2026-06-12 09:00:00', '2026-06-12 10:30:00', 'Prestation 11', 'confirmé'),
(12, 3, NULL, '2026-06-13 09:00:00', '2026-06-13 10:30:00', 'Prestation 12', 'confirmé'),
(13, 4, 3, '2026-06-14 09:00:00', '2026-06-14 10:30:00', 'Prestation 13', 'confirmé'),
(14, 5, 4, '2026-06-15 09:00:00', '2026-06-15 10:30:00', 'Prestation 14', 'confirmé'),
(15, 6, 5, '2026-06-16 09:00:00', '2026-06-16 10:30:00', 'Prestation 15', 'confirmé'),
(16, 7, NULL, '2026-06-17 09:00:00', '2026-06-17 10:30:00', 'Prestation 16', 'confirmé'),
(17, 8, 2, '2026-06-18 09:00:00', '2026-06-18 10:30:00', 'Prestation 17', 'confirmé'),
(18, 9, 3, '2026-06-19 09:00:00', '2026-06-19 10:30:00', 'Prestation 18', 'confirmé'),
(19, 10, 4, '2026-06-20 09:00:00', '2026-06-20 10:30:00', 'Prestation 19', 'confirmé'),
(20, 1, NULL, '2026-06-21 09:00:00', '2026-06-21 10:30:00', 'Prestation 20', 'confirmé');

/* -------------------------------------------------------- */

/* */
/* Structure de la table `sanction` */
/* */

CREATE TABLE `sanction` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `type` enum('warn','temp','perm') NOT NULL,
  `motif` varchar(255) NOT NULL,
  `date_crea` datetime NOT NULL DEFAULT current_timestamp(),
  `date_fin` datetime DEFAULT NULL,
  `date_levee` datetime DEFAULT NULL,
  `par_admin` int(11) DEFAULT NULL,
  `levee_par` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `sanction` */
/* */

INSERT INTO `sanction` (`id`, `id_user`, `type`, `motif`, `date_crea`, `date_fin`, `date_levee`, `par_admin`, `levee_par`, `active`) VALUES
(1, 2, 'temp', 'Motif administratif 1', '2026-04-02 08:00:00', NULL, NULL, 20, 20, 1),
(2, 3, 'perm', 'Motif administratif 2', '2026-04-03 08:00:00', NULL, NULL, 20, 20, 1),
(3, 4, 'warn', 'Motif administratif 3', '2026-04-04 08:00:00', NULL, NULL, 20, 20, 1),
(4, 5, 'temp', 'Motif administratif 4', '2026-04-05 08:00:00', NULL, NULL, 20, 20, 0),
(5, 6, 'perm', 'Motif administratif 5', '2026-04-06 08:00:00', NULL, NULL, 20, 20, 1),
(6, 7, 'warn', 'Motif administratif 6', '2026-04-07 08:00:00', NULL, NULL, 20, 20, 1),
(7, 8, 'temp', 'Motif administratif 7', '2026-04-08 08:00:00', NULL, NULL, 20, 20, 1),
(8, 9, 'perm', 'Motif administratif 8', '2026-04-09 08:00:00', NULL, NULL, 20, 20, 0),
(9, 10, 'warn', 'Motif administratif 9', '2026-04-10 08:00:00', NULL, NULL, 20, 20, 1),
(10, 11, 'temp', 'Motif administratif 10', '2026-04-11 08:00:00', NULL, NULL, 20, 20, 1),
(11, 12, 'perm', 'Motif administratif 11', '2026-04-12 08:00:00', NULL, NULL, 20, 20, 1),
(12, 13, 'warn', 'Motif administratif 12', '2026-04-13 08:00:00', NULL, NULL, 20, 20, 0),
(13, 14, 'temp', 'Motif administratif 13', '2026-04-14 08:00:00', NULL, NULL, 20, 20, 1),
(14, 15, 'perm', 'Motif administratif 14', '2026-04-15 08:00:00', NULL, NULL, 20, 20, 1),
(15, 1, 'warn', 'Motif administratif 15', '2026-04-16 08:00:00', NULL, NULL, 20, 20, 1),
(16, 2, 'temp', 'Motif administratif 16', '2026-04-17 08:00:00', NULL, NULL, 20, 20, 0),
(17, 3, 'perm', 'Motif administratif 17', '2026-04-18 08:00:00', NULL, NULL, 20, 20, 1),
(18, 4, 'warn', 'Motif administratif 18', '2026-04-19 08:00:00', NULL, NULL, 20, 20, 1),
(19, 5, 'temp', 'Motif administratif 19', '2026-04-20 08:00:00', NULL, NULL, 20, 20, 1),
(20, 6, 'perm', 'Motif administratif 20', '2026-04-21 08:00:00', NULL, NULL, 20, 20, 0);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `service` */
/* */

CREATE TABLE `service` (
  `id_service` int(11) NOT NULL,
  `nom` varchar(150) DEFAULT NULL,
  `image` varchar(30) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `tarif` decimal(10,2) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL,
  `valide_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `service` */
/* */

INSERT INTO `service` (`id_service`, `nom`, `image`, `description`, `tarif`, `id_prestataire`, `id_categorie`, `valide_admin`) VALUES
(1, 'Prestation 1', '', 'Intervention professionnelle 1 avec suivi qualite.', 29.5, 1, 1, 1),
(2, 'Prestation 2', '', 'Intervention professionnelle 2 avec suivi qualite.', 34.0, 2, 2, 1),
(3, 'Prestation 3', '', 'Intervention professionnelle 3 avec suivi qualite.', 38.5, 3, 3, 1),
(4, 'Prestation 4', '', 'Intervention professionnelle 4 avec suivi qualite.', 43.0, 4, 4, 1),
(5, 'Prestation 5', '', 'Intervention professionnelle 5 avec suivi qualite.', 47.5, 5, 5, 1),
(6, 'Prestation 6', '', 'Intervention professionnelle 6 avec suivi qualite.', 52.0, 1, 6, 1),
(7, 'Prestation 7', '', 'Intervention professionnelle 7 avec suivi qualite.', 56.5, 2, 7, 1),
(8, 'Prestation 8', '', 'Intervention professionnelle 8 avec suivi qualite.', 61.0, 3, 8, 1),
(9, 'Prestation 9', '', 'Intervention professionnelle 9 avec suivi qualite.', 65.5, 4, 9, 1),
(10, 'Prestation 10', '', 'Intervention professionnelle 10 avec suivi qualite.', 70.0, 5, 10, 1),
(11, 'Prestation 11', '', 'Intervention professionnelle 11 avec suivi qualite.', 74.5, 1, 11, 1),
(12, 'Prestation 12', '', 'Intervention professionnelle 12 avec suivi qualite.', 79.0, 2, 12, 1),
(13, 'Prestation 13', '', 'Intervention professionnelle 13 avec suivi qualite.', 83.5, 3, 13, 1),
(14, 'Prestation 14', '', 'Intervention professionnelle 14 avec suivi qualite.', 88.0, 4, 14, 1),
(15, 'Prestation 15', '', 'Intervention professionnelle 15 avec suivi qualite.', 92.5, 5, 15, 1),
(16, 'Prestation 16', '', 'Intervention professionnelle 16 avec suivi qualite.', 97.0, 1, 16, 1),
(17, 'Prestation 17', '', 'Intervention professionnelle 17 avec suivi qualite.', 101.5, 2, 17, 1),
(18, 'Prestation 18', '', 'Intervention professionnelle 18 avec suivi qualite.', 106.0, 3, 18, 1),
(19, 'Prestation 19', '', 'Intervention professionnelle 19 avec suivi qualite.', 110.5, 4, 19, 1),
(20, 'Prestation 20', '', 'Intervention professionnelle 20 avec suivi qualite.', 115.0, 5, 20, 1);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `souscris_abonnement` */
/* */

CREATE TABLE `souscris_abonnement` (
  `id_souscrit` int(11) NOT NULL,
  `date_souscription` datetime DEFAULT current_timestamp(),
  `date_expiration` datetime DEFAULT NULL,
  `validite` tinyint(1) DEFAULT 1,
  `type_paiement` enum('an','mois') NOT NULL DEFAULT 'mois',
  `id_utilisateur` int(11) NOT NULL,
  `id_abonnement` int(11) NOT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `stripe_subscription_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/* */
/* Déchargement des données de la table `souscris_abonnement` */
/* */

INSERT INTO `souscris_abonnement` (`id_souscrit`, `date_souscription`, `date_expiration`, `validite`, `type_paiement`, `id_utilisateur`, `id_abonnement`, `stripe_customer_id`, `stripe_subscription_id`) VALUES
(1, '2026-03-02 10:00:00', NULL, 1, 'an', 2, 1, NULL, NULL),
(2, '2026-03-03 10:00:00', NULL, 1, 'mois', 3, 2, NULL, NULL),
(3, '2026-03-04 10:00:00', NULL, 1, 'an', 4, 3, NULL, NULL),
(4, '2026-03-05 10:00:00', NULL, 1, 'mois', 5, 4, NULL, NULL),
(5, '2026-03-06 10:00:00', NULL, 1, 'an', 6, 5, NULL, NULL),
(6, '2026-03-07 10:00:00', NULL, 1, 'mois', 7, 6, NULL, NULL),
(7, '2026-03-08 10:00:00', NULL, 1, 'an', 8, 7, NULL, NULL),
(8, '2026-03-09 10:00:00', NULL, 1, 'mois', 9, 8, NULL, NULL),
(9, '2026-03-10 10:00:00', NULL, 1, 'an', 10, 9, NULL, NULL),
(10, '2026-03-11 10:00:00', NULL, 1, 'mois', 11, 10, NULL, NULL),
(11, '2026-03-12 10:00:00', NULL, 1, 'an', 12, 11, NULL, NULL),
(12, '2026-03-13 10:00:00', NULL, 1, 'mois', 13, 12, NULL, NULL),
(13, '2026-03-14 10:00:00', NULL, 1, 'an', 14, 13, NULL, NULL),
(14, '2026-03-15 10:00:00', NULL, 1, 'mois', 15, 14, NULL, NULL),
(15, '2026-03-16 10:00:00', NULL, 1, 'an', 16, 15, NULL, NULL),
(16, '2026-03-17 10:00:00', NULL, 1, 'mois', 17, 16, NULL, NULL),
(17, '2026-03-18 10:00:00', NULL, 1, 'an', 18, 17, NULL, NULL),
(18, '2026-03-19 10:00:00', NULL, 1, 'mois', 19, 18, NULL, NULL),
(19, '2026-03-20 10:00:00', NULL, 1, 'an', 20, 19, NULL, NULL),
(20, '2026-03-21 10:00:00', NULL, 1, 'mois', 1, 20, NULL, NULL);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `synthese_facture` */
/* */

CREATE TABLE `synthese_facture` (
  `id_facture` int(11) NOT NULL,
  `id_intervention` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `synthese_facture` */
/* */

INSERT INTO `synthese_facture` (`id_facture`, `id_intervention`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6),
(7, 7),
(8, 8),
(9, 9),
(10, 10),
(11, 11),
(12, 12),
(13, 13),
(14, 14),
(15, 15),
(16, 16),
(17, 17),
(18, 18),
(19, 19),
(20, 20);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `token` */
/* */

CREATE TABLE `token` (
  `id_token` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `valeur` varchar(255) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `date_expiration` datetime DEFAULT NULL,
  `utiliser` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* -------------------------------------------------------- */

/* */
/* Structure de la table `utilisateur` */
/* */

CREATE TABLE `utilisateur` (
  `id_utilisateur` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `date_naissance` date NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `password` varchar(1000) DEFAULT NULL,
  `token` varchar(1000) DEFAULT NULL,
  `role` enum('adherant','prestataire','admin') DEFAULT 'adherant',
  `image` varchar(30) DEFAULT NULL,
  `langue` enum('fr','en','it','de','ru','uk','pt','pl','nl') DEFAULT 'fr',
  `taille_police` varchar(20) DEFAULT '1',
  `tutoriel` int(11) DEFAULT 1,
  `verifier` tinyint(4) NOT NULL DEFAULT 0,
  `abonnée` tinyint(4) NOT NULL DEFAULT 0,
  `statut_user` enum('actif','suspendu','banni') NOT NULL DEFAULT 'actif',
  `fin_susp` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `utilisateur` */
/* */

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `date_naissance`, `telephone`, `password`, `token`, `role`, `image`, `langue`, `taille_police`, `tutoriel`, `verifier`, `abonnée`, `statut_user`, `fin_susp`) VALUES
(1, 'Martin', 'Camille', 'camille.martin@silverhappy.demo', '1965-03-15', '0600000001', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'adherant', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(2, 'Bernard', 'Lucas', 'lucas.bernard@silverhappy.demo', '1965-03-15', '0600000002', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'adherant', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(3, 'Dubois', 'Emma', 'emma.dubois@silverhappy.demo', '1965-03-15', '0600000003', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'adherant', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(4, 'Thomas', 'Thomas', 'thomas.thomas@silverhappy.demo', '1965-03-15', '0600000004', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'adherant', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(5, 'Robert', 'Lea', 'lea.robert@silverhappy.demo', '1965-03-15', '0600000005', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', 'tok-demo-client-04-silverhappy', 'adherant', '', 'fr', '1', 0, 1, 1, 'actif', NULL),
(6, 'Richard', 'Hugo', 'hugo.richard@silverhappy.demo', '1965-03-15', '0600000006', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'adherant', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(7, 'Petit', 'Chloe', 'chloe.petit@silverhappy.demo', '1965-03-15', '0600000007', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'adherant', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(8, 'Durand', 'Nathan', 'nathan.durand@silverhappy.demo', '1965-03-15', '0600000008', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'adherant', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(9, 'Leroy', 'Ines', 'ines.leroy@silverhappy.demo', '1965-03-15', '0600000009', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'adherant', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(10, 'Moreau', 'Alexandre', 'alexandre.moreau@silverhappy.demo', '1982-07-22', '0600000010', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'adherant', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(11, 'Durand', 'Marie', 'marie.durand@silverhappy.demo', '1982-07-22', '0600000011', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', 'tok-demo-presta-marie', 'prestataire', '', 'fr', '1', 0, 1, 1, 'actif', NULL),
(12, 'Garcia', 'Paul', 'paul.garcia@silverhappy.demo', '1982-07-22', '0600000012', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'prestataire', '', 'fr', '1', 0, 1, 1, 'actif', NULL),
(13, 'Simon', 'Julie', 'julie.simon@silverhappy.demo', '1982-07-22', '0600000013', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'prestataire', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(14, 'Laurent', 'Antoine', 'antoine.laurent@silverhappy.demo', '1982-07-22', '0600000014', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'prestataire', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(15, 'Michel', 'Sarah', 'sarah.michel@silverhappy.demo', '1982-07-22', '0600000015', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'prestataire', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(16, 'Lefebvre', 'Claire', 'claire.lefebvre@silverhappy.demo', '1982-07-22', '0600000016', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'adherant', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(17, 'Roux', 'Victor', 'victor.roux@silverhappy.demo', '1982-07-22', '0600000017', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'adherant', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(18, 'David', 'Laura', 'laura.david@silverhappy.demo', '1982-07-22', '0600000018', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'adherant', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(19, 'Bertrand', 'Nicolas', 'nicolas.bertrand@silverhappy.demo', '1970-01-10', '0600000019', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', NULL, 'adherant', '', 'fr', '1', 0, 1, 0, 'actif', NULL),
(20, 'SilverHappy', 'Admin', 'admin@silverhappy.demo', '1970-01-10', '0600000020', '$2b$10$bFK1VHjw0a51b45snU0Cr.T5KCTAwJ8XUcyaeKXarcAuLUjAtpMeS', 'tok-demo-admin-silverhappy', 'admin', '', 'fr', '1', 0, 1, 0, 'actif', NULL);

/* -------------------------------------------------------- */

/* */
/* Structure de la table `virement` */
/* */

CREATE TABLE `virement` (
  `id_virement` int(11) NOT NULL,
  `id_facture` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

/* */
/* Déchargement des données de la table `token` */
/* */

INSERT INTO `token` (`id_token`, `id_utilisateur`, `valeur`, `date_creation`, `date_expiration`, `utiliser`) VALUES
(1, 2, 'reset-token-demo-0001', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(2, 3, 'reset-token-demo-0002', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(3, 4, 'reset-token-demo-0003', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(4, 5, 'reset-token-demo-0004', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(5, 6, 'reset-token-demo-0005', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(6, 7, 'reset-token-demo-0006', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(7, 8, 'reset-token-demo-0007', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(8, 9, 'reset-token-demo-0008', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(9, 10, 'reset-token-demo-0009', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(10, 11, 'reset-token-demo-0010', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(11, 12, 'reset-token-demo-0011', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(12, 13, 'reset-token-demo-0012', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(13, 14, 'reset-token-demo-0013', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(14, 15, 'reset-token-demo-0014', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(15, 16, 'reset-token-demo-0015', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(16, 17, 'reset-token-demo-0016', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(17, 18, 'reset-token-demo-0017', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(18, 19, 'reset-token-demo-0018', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(19, 20, 'reset-token-demo-0019', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0),
(20, 1, 'reset-token-demo-0020', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 0);

/* */
/* Déchargement des données de la table `consultation_conseil` */
/* */

INSERT INTO `consultation_conseil` (`id_utilisateur`, `id_conseil`, `date_consultation`) VALUES
(6, 1, '2026-04-01 15:00:00'),
(7, 2, '2026-04-02 15:00:00'),
(8, 3, '2026-04-03 15:00:00'),
(9, 4, '2026-04-04 15:00:00'),
(10, 5, '2026-04-05 15:00:00'),
(11, 6, '2026-04-06 15:00:00'),
(12, 7, '2026-04-07 15:00:00'),
(13, 8, '2026-04-08 15:00:00'),
(14, 9, '2026-04-09 15:00:00'),
(15, 10, '2026-04-10 15:00:00'),
(16, 11, '2026-04-11 15:00:00'),
(17, 12, '2026-04-12 15:00:00'),
(18, 13, '2026-04-13 15:00:00'),
(19, 14, '2026-04-14 15:00:00'),
(20, 15, '2026-04-15 15:00:00'),
(1, 16, '2026-04-16 15:00:00'),
(2, 17, '2026-04-17 15:00:00'),
(3, 18, '2026-04-18 15:00:00'),
(4, 19, '2026-04-19 15:00:00'),
(5, 20, '2026-04-20 15:00:00');

/* */
/* Déchargement des données de la table `virement` */
/* */

INSERT INTO `virement` (`id_virement`, `id_facture`, `date`, `montant`, `statut`) VALUES
(1, 1, '2026-05-02', 140, 'pending'),
(2, 2, '2026-05-03', 180, 'pending'),
(3, 3, '2026-05-04', 220, 'paid'),
(4, 4, '2026-05-05', 260, 'pending'),
(5, 5, '2026-05-06', 300, 'pending'),
(6, 6, '2026-05-07', 340, 'paid'),
(7, 7, '2026-05-08', 380, 'pending'),
(8, 8, '2026-05-09', 420, 'pending'),
(9, 9, '2026-05-10', 460, 'paid'),
(10, 10, '2026-05-11', 500, 'pending'),
(11, 11, '2026-05-12', 540, 'pending'),
(12, 12, '2026-05-13', 580, 'paid'),
(13, 13, '2026-05-14', 620, 'pending'),
(14, 14, '2026-05-15', 660, 'pending'),
(15, 15, '2026-05-16', 700, 'paid'),
(16, 16, '2026-05-17', 740, 'pending'),
(17, 17, '2026-05-18', 780, 'pending'),
(18, 18, '2026-05-19', 820, 'paid'),
(19, 19, '2026-05-20', 860, 'pending'),
(20, 20, '2026-05-21', 900, 'pending');

/* */
/* Index pour les tables déchargées */
/* */

/* */
/* Index pour la table `abonnement` */
/* */
ALTER TABLE `abonnement`
  ADD PRIMARY KEY (`id_abonnement`),
  ADD KEY `id_prestataire` (`type_prestataire`),
  ADD KEY `idx_id_prestataire` (`id_prestataire`);

/* */
/* Index pour la table `abonnement_push` */
/* */
ALTER TABLE `abonnement_push`
  ADD PRIMARY KEY (`id_subscription`),
  ADD UNIQUE KEY `uniq_subscription_id` (`subscription_id`),
  ADD KEY `idx_push_user` (`id_utilisateur`);

/* */
/* Index pour la table `achat` */
/* */
ALTER TABLE `achat`
  ADD PRIMARY KEY (`id_achat`),
  ADD UNIQUE KEY `id_panier` (`id_panier`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

/* */
/* Index pour la table `article` */
/* */
ALTER TABLE `article`
  ADD PRIMARY KEY (`id_article`);

/* */
/* Index pour la table `categorie` */
/* */
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id_categorie`),
  ADD UNIQUE KEY `nom` (`nom`);

/* */
/* Index pour la table `categories` */
/* */
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

/* */
/* Index pour la table `champs_supplementaires` */
/* */
ALTER TABLE `champs_supplementaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categorie_id` (`categorie_id`);

/* */
/* Index pour la table `conseil` */
/* */
ALTER TABLE `conseil`
  ADD PRIMARY KEY (`id_conseil`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

/* */
/* Index pour la table `conseil_note` */
/* */
ALTER TABLE `conseil_note`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_conseil` (`id_conseil`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

/* */
/* Index pour la table `consultation_conseil` */
/* */
ALTER TABLE `consultation_conseil`
  ADD PRIMARY KEY (`id_utilisateur`,`id_conseil`),
  ADD KEY `id_conseil` (`id_conseil`);

/* */
/* Index pour la table `contact` */
/* */
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id_contact`),
  ADD KEY `fk_user` (`id_utilisateur`);

/* */
/* Index pour la table `contrat` */
/* */
ALTER TABLE `contrat`
  ADD PRIMARY KEY (`id_contrat`),
  ADD UNIQUE KEY `id_devis` (`id_devis`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_prestataire` (`id_prestataire`);

/* */
/* Index pour la table `devis` */
/* */
ALTER TABLE `devis`
  ADD PRIMARY KEY (`id_devis`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_prestataire` (`id_prestataire`),
  ADD KEY `id_intervention` (`id_intervention`);

/* */
/* Index pour la table `disponibilite` */
/* */
ALTER TABLE `disponibilite`
  ADD PRIMARY KEY (`id_disponibilite`),
  ADD KEY `id_prestataire` (`id_prestataire`);

/* */
/* Index pour la table `document` */
/* */
ALTER TABLE `document`
  ADD PRIMARY KEY (`id_document`),
  ADD KEY `fk_document_user` (`id_utilisateur`);

/* */
/* Index pour la table `document_txt` */
/* */
ALTER TABLE `document_txt`
  ADD PRIMARY KEY (`id_document_txt`),
  ADD KEY `fk_utilisateur_txt` (`id_utilisateur`);

/* */
/* Index pour la table `evaluation` */
/* */
ALTER TABLE `evaluation`
  ADD PRIMARY KEY (`id_evaluation`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_service` (`id_service`);

/* */
/* Index pour la table `evenement` */
/* */
ALTER TABLE `evenement`
  ADD PRIMARY KEY (`id_evenement`);

/* */
/* Index pour la table `facture_prestataire` */
/* */
ALTER TABLE `facture_prestataire`
  ADD PRIMARY KEY (`id_facture`),
  ADD KEY `id_prestataire` (`id_prestataire`);

/* */
/* Index pour la table `intervention` */
/* */
ALTER TABLE `intervention`
  ADD PRIMARY KEY (`id_intervention`),
  ADD KEY `id_service` (`id_service`),
  ADD KEY `id_prestataire` (`id_prestataire`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_rdv` (`id_rdv`);

/* */
/* Index pour la table `lien_contact` */
/* */
ALTER TABLE `lien_contact`
  ADD PRIMARY KEY (`id_lien`),
  ADD KEY `id_user1` (`id_user1`),
  ADD KEY `id_user2` (`id_user2`);

/* */
/* Index pour la table `lien_contact_state` */
/* */
ALTER TABLE `lien_contact_state`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user1` (`id_user1`),
  ADD KEY `id_user2` (`id_user2`);

/* */
/* Index pour la table `message` */
/* */
ALTER TABLE `message`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `id_expediteur` (`id_expediteur`),
  ADD KEY `id_destinataire` (`id_destinataire`);

/* */
/* Index pour la table `modele_notification` */
/* */
ALTER TABLE `modele_notification`
  ADD PRIMARY KEY (`id_modele`),
  ADD UNIQUE KEY `cle` (`cle`);

/* */
/* Index pour la table `notification` */
/* */
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id_notification`),
  ADD KEY `id_expediteur` (`id_expediteur`),
  ADD KEY `id_destinataire` (`id_destinataire`);

/* */
/* Index pour la table `paiement` */
/* */
ALTER TABLE `paiement`
  ADD PRIMARY KEY (`id_paiement`),
  ADD UNIQUE KEY `id_achat` (`id_achat`);

/* */
/* Index pour la table `paiement_abonnement` */
/* */
ALTER TABLE `paiement_abonnement`
  ADD PRIMARY KEY (`id_paiement_abonnement`),
  ADD KEY `id_abonnement` (`id_abonnement`);

/* */
/* Index pour la table `panier` */
/* */
ALTER TABLE `panier`
  ADD PRIMARY KEY (`id_panier`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

/* */
/* Index pour la table `prestataire` */
/* */
ALTER TABLE `prestataire`
  ADD PRIMARY KEY (`id_prestataire`),
  ADD UNIQUE KEY `id_utilisateur` (`id_utilisateur`);

/* */
/* Index pour la table `reference_article` */
/* */
ALTER TABLE `reference_article`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_panier_article` (`id_utilisateur`,`id_panier`,`id_article`),
  ADD KEY `idx_article` (`id_article`),
  ADD KEY `idx_panier` (`id_panier`);

/* */
/* Index pour la table `reference_evenement` */
/* */
ALTER TABLE `reference_evenement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_even` (`id_evenement`),
  ADD KEY `fk_util` (`id_utilisateur`);

/* */
/* Index pour la table `reference_service` */
/* */
ALTER TABLE `reference_service`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_utilisateur` (`id_utilisateur`,`id_service`),
  ADD KEY `id_service` (`id_service`);

/* */
/* Index pour la table `rendez_vous` */
/* */
ALTER TABLE `rendez_vous`
  ADD PRIMARY KEY (`id_rdv`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_prestataire` (`id_prestataire`);

/* */
/* Index pour la table `sanction` */
/* */
ALTER TABLE `sanction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sanc_user_date` (`id_user`,`date_crea`),
  ADD KEY `idx_sanc_active_fin` (`active`,`date_fin`),
  ADD KEY `idx_sanc_admin` (`par_admin`),
  ADD KEY `fk_sanc_levee` (`levee_par`);

/* */
/* Index pour la table `service` */
/* */
ALTER TABLE `service`
  ADD PRIMARY KEY (`id_service`),
  ADD KEY `id_prestataire` (`id_prestataire`),
  ADD KEY `id_categorie` (`id_categorie`);

/* */
/* Index pour la table `souscris_abonnement` */
/* */
ALTER TABLE `souscris_abonnement`
  ADD PRIMARY KEY (`id_souscrit`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_abonnement` (`id_abonnement`);

/* */
/* Index pour la table `synthese_facture` */
/* */
ALTER TABLE `synthese_facture`
  ADD PRIMARY KEY (`id_facture`,`id_intervention`),
  ADD KEY `id_intervention` (`id_intervention`);

/* */
/* Index pour la table `token` */
/* */
ALTER TABLE `token`
  ADD PRIMARY KEY (`id_token`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

/* */
/* Index pour la table `utilisateur` */
/* */
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email` (`email`);

/* */
/* Index pour la table `virement` */
/* */
ALTER TABLE `virement`
  ADD PRIMARY KEY (`id_virement`),
  ADD KEY `id_facture` (`id_facture`);

/* */
/* AUTO_INCREMENT pour les tables déchargées */
/* */

/* */
/* AUTO_INCREMENT pour la table `abonnement` */
/* */
ALTER TABLE `abonnement`
  MODIFY `id_abonnement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `abonnement_push` */
/* */
ALTER TABLE `abonnement_push`
  MODIFY `id_subscription` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3021;

/* */
/* AUTO_INCREMENT pour la table `achat` */
/* */
ALTER TABLE `achat`
  MODIFY `id_achat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `article` */
/* */
ALTER TABLE `article`
  MODIFY `id_article` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `categorie` */
/* */
ALTER TABLE `categorie`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `categories` */
/* */
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `champs_supplementaires` */
/* */
ALTER TABLE `champs_supplementaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `conseil` */
/* */
ALTER TABLE `conseil`
  MODIFY `id_conseil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `conseil_note` */
/* */
ALTER TABLE `conseil_note`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

/* */
/* AUTO_INCREMENT pour la table `contact` */
/* */
ALTER TABLE `contact`
  MODIFY `id_contact` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `contrat` */
/* */
ALTER TABLE `contrat`
  MODIFY `id_contrat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `devis` */
/* */
ALTER TABLE `devis`
  MODIFY `id_devis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `disponibilite` */
/* */
ALTER TABLE `disponibilite`
  MODIFY `id_disponibilite` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `document` */
/* */
ALTER TABLE `document`
  MODIFY `id_document` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `document_txt` */
/* */
ALTER TABLE `document_txt`
  MODIFY `id_document_txt` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `evaluation` */
/* */
ALTER TABLE `evaluation`
  MODIFY `id_evaluation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `evenement` */
/* */
ALTER TABLE `evenement`
  MODIFY `id_evenement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `facture_prestataire` */
/* */
ALTER TABLE `facture_prestataire`
  MODIFY `id_facture` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `intervention` */
/* */
ALTER TABLE `intervention`
  MODIFY `id_intervention` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `lien_contact` */
/* */
ALTER TABLE `lien_contact`
  MODIFY `id_lien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `lien_contact_state` */
/* */
ALTER TABLE `lien_contact_state`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `message` */
/* */
ALTER TABLE `message`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `modele_notification` */
/* */
ALTER TABLE `modele_notification`
  MODIFY `id_modele` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `notification` */
/* */
ALTER TABLE `notification`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `paiement` */
/* */
ALTER TABLE `paiement`
  MODIFY `id_paiement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `paiement_abonnement` */
/* */
ALTER TABLE `paiement_abonnement`
  MODIFY `id_paiement_abonnement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

/* */
/* AUTO_INCREMENT pour la table `panier` */
/* */
ALTER TABLE `panier`
  MODIFY `id_panier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `prestataire` */
/* */
ALTER TABLE `prestataire`
  MODIFY `id_prestataire` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

/* */
/* AUTO_INCREMENT pour la table `reference_article` */
/* */
ALTER TABLE `reference_article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `reference_evenement` */
/* */
ALTER TABLE `reference_evenement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

/* */
/* AUTO_INCREMENT pour la table `reference_service` */
/* */
ALTER TABLE `reference_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=321;

/* */
/* AUTO_INCREMENT pour la table `rendez_vous` */
/* */
ALTER TABLE `rendez_vous`
  MODIFY `id_rdv` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `sanction` */
/* */
ALTER TABLE `sanction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `service` */
/* */
ALTER TABLE `service`
  MODIFY `id_service` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `souscris_abonnement` */
/* */
ALTER TABLE `souscris_abonnement`
  MODIFY `id_souscrit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `token` */
/* */
ALTER TABLE `token`
  MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `utilisateur` */
/* */
ALTER TABLE `utilisateur`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* AUTO_INCREMENT pour la table `virement` */
/* */
ALTER TABLE `virement`
  MODIFY `id_virement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

/* */
/* Contraintes pour les tables déchargées */
/* */

/* */
/* Contraintes pour la table `abonnement_push` */
/* */
ALTER TABLE `abonnement_push`
  ADD CONSTRAINT `fk_push_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `achat` */
/* */
ALTER TABLE `achat`
  ADD CONSTRAINT `achat_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `achat_ibfk_2` FOREIGN KEY (`id_panier`) REFERENCES `panier` (`id_panier`) ON DELETE SET NULL;

/* */
/* Contraintes pour la table `champs_supplementaires` */
/* */
ALTER TABLE `champs_supplementaires`
  ADD CONSTRAINT `champs_supplementaires_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `conseil` */
/* */
ALTER TABLE `conseil`
  ADD CONSTRAINT `conseil_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `conseil_note` */
/* */
ALTER TABLE `conseil_note`
  ADD CONSTRAINT `conseil_note_ibfk_1` FOREIGN KEY (`id_conseil`) REFERENCES `conseil` (`id_conseil`) ON DELETE CASCADE,
  ADD CONSTRAINT `conseil_note_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

/* */
/* Contraintes pour la table `consultation_conseil` */
/* */
ALTER TABLE `consultation_conseil`
  ADD CONSTRAINT `consultation_conseil_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultation_conseil_ibfk_2` FOREIGN KEY (`id_conseil`) REFERENCES `conseil` (`id_conseil`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `contact` */
/* */
ALTER TABLE `contact`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `contrat` */
/* */
ALTER TABLE `contrat`
  ADD CONSTRAINT `contrat_ibfk_1` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`id_devis`) ON DELETE CASCADE,
  ADD CONSTRAINT `contrat_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `contrat_ibfk_3` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`);

/* */
/* Contraintes pour la table `devis` */
/* */
ALTER TABLE `devis`
  ADD CONSTRAINT `devis_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `devis_ibfk_2` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE SET NULL,
  ADD CONSTRAINT `devis_ibfk_3` FOREIGN KEY (`id_intervention`) REFERENCES `intervention` (`id_intervention`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `disponibilite` */
/* */
ALTER TABLE `disponibilite`
  ADD CONSTRAINT `disponibilite_ibfk_1` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `document` */
/* */
ALTER TABLE `document`
  ADD CONSTRAINT `fk_document_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `document_txt` */
/* */
ALTER TABLE `document_txt`
  ADD CONSTRAINT `fk_utilisateur_txt` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `evaluation` */
/* */
ALTER TABLE `evaluation`
  ADD CONSTRAINT `evaluation_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluation_ibfk_2` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `facture_prestataire` */
/* */
ALTER TABLE `facture_prestataire`
  ADD CONSTRAINT `facture_prestataire_ibfk_1` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `intervention` */
/* */
ALTER TABLE `intervention`
  ADD CONSTRAINT `intervention_ibfk_1` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`) ON DELETE SET NULL,
  ADD CONSTRAINT `intervention_ibfk_2` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE SET NULL,
  ADD CONSTRAINT `intervention_ibfk_3` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `intervention_ibfk_4` FOREIGN KEY (`id_rdv`) REFERENCES `rendez_vous` (`id_rdv`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `lien_contact` */
/* */
ALTER TABLE `lien_contact`
  ADD CONSTRAINT `lien_contact_ibfk_1` FOREIGN KEY (`id_user1`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `lien_contact_ibfk_2` FOREIGN KEY (`id_user2`) REFERENCES `utilisateur` (`id_utilisateur`);

/* */
/* Contraintes pour la table `lien_contact_state` */
/* */
ALTER TABLE `lien_contact_state`
  ADD CONSTRAINT `lien_contact_state_ibfk_1` FOREIGN KEY (`id_user1`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `lien_contact_state_ibfk_2` FOREIGN KEY (`id_user2`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `message` */
/* */
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`id_expediteur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`id_destinataire`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `notification` */
/* */
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`id_expediteur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_ibfk_2` FOREIGN KEY (`id_destinataire`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `paiement` */
/* */
ALTER TABLE `paiement`
  ADD CONSTRAINT `paiement_ibfk_1` FOREIGN KEY (`id_achat`) REFERENCES `achat` (`id_achat`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `paiement_abonnement` */
/* */
ALTER TABLE `paiement_abonnement`
  ADD CONSTRAINT `paiement_abonnement_ibfk_1` FOREIGN KEY (`id_abonnement`) REFERENCES `abonnement` (`id_abonnement`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `panier` */
/* */
ALTER TABLE `panier`
  ADD CONSTRAINT `panier_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `prestataire` */
/* */
ALTER TABLE `prestataire`
  ADD CONSTRAINT `prestataire_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `reference_evenement` */
/* */
ALTER TABLE `reference_evenement`
  ADD CONSTRAINT `fk_even` FOREIGN KEY (`id_evenement`) REFERENCES `evenement` (`id_evenement`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_util` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `reference_service` */
/* */
ALTER TABLE `reference_service`
  ADD CONSTRAINT `reference_service_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `reference_service_ibfk_2` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `rendez_vous` */
/* */
ALTER TABLE `rendez_vous`
  ADD CONSTRAINT `rendez_vous_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `rendez_vous_ibfk_2` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE SET NULL;

/* */
/* Contraintes pour la table `sanction` */
/* */
ALTER TABLE `sanction`
  ADD CONSTRAINT `fk_sanc_admin` FOREIGN KEY (`par_admin`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sanc_levee` FOREIGN KEY (`levee_par`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sanc_user` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `service` */
/* */
ALTER TABLE `service`
  ADD CONSTRAINT `service_ibfk_1` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_ibfk_2` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`);

/* */
/* Contraintes pour la table `souscris_abonnement` */
/* */
ALTER TABLE `souscris_abonnement`
  ADD CONSTRAINT `souscris_abonnement_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `souscris_abonnement_ibfk_2` FOREIGN KEY (`id_abonnement`) REFERENCES `abonnement` (`id_abonnement`);

/* */
/* Contraintes pour la table `synthese_facture` */
/* */
ALTER TABLE `synthese_facture`
  ADD CONSTRAINT `synthese_facture_ibfk_1` FOREIGN KEY (`id_facture`) REFERENCES `facture_prestataire` (`id_facture`) ON DELETE CASCADE,
  ADD CONSTRAINT `synthese_facture_ibfk_2` FOREIGN KEY (`id_intervention`) REFERENCES `intervention` (`id_intervention`);

/* */
/* Contraintes pour la table `token` */
/* */
ALTER TABLE `token`
  ADD CONSTRAINT `token_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

/* */
/* Contraintes pour la table `virement` */
/* */
ALTER TABLE `virement`
  ADD CONSTRAINT `virement_ibfk_1` FOREIGN KEY (`id_facture`) REFERENCES `facture_prestataire` (`id_facture`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
