-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : mariadb
-- Généré le : dim. 10 mai 2026 à 17:03
-- Version du serveur : 11.8.6-MariaDB-ubu2404
-- Version de PHP : 8.3.31

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `abonnement`
--

INSERT INTO `abonnement` (`id_abonnement`, `id_prestataire`, `categorie`, `type_prestataire`, `type`, `prix_mois`, `statut`, `prix_an`, `Locaux_prestation`, `Trajet_offert`, `offre_repas`, `mis_en_avant`, `contenue1`, `contenue2`, `contenue3`, `contenue4`, `nb_avantage`) VALUES
(1, NULL, 'senior', 1, 'Basic', 4.00, 'actif', 40, 1, 1, 0, 0, '', '', '', '', 0),
(2, NULL, 'senior', 1, 'Standard', 5.00, 'actif', 55, 1, 1, 1, 0, '', '', '', '', 0),
(3, NULL, 'senior', 1, 'Premium', 6.00, 'actif', 72, 1, 1, 1, 1, 'avantage 1', 'avantage 2', 'avantage 3', 'avantage 4', 1),
(5, NULL, 'senior', 0, 'Premium', 30.00, 'actif', 300, 1, 1, 1, 1, '', '', '', '', 0);

-- --------------------------------------------------------

--
-- Structure de la table `abonnement_push`
--

CREATE TABLE `abonnement_push` (
  `id_subscription` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `subscription_id` varchar(191) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `abonnement_push`
--

INSERT INTO `abonnement_push` (`id_subscription`, `id_utilisateur`, `subscription_id`, `actif`, `updated_at`) VALUES
(68, 11, '907f7548-a91e-4908-918b-42c9e7166215', 0, '2026-04-29 19:03:36'),
(392, 5, '786b489a-25e1-4865-b604-45f530489fb8', 1, '2026-04-21 15:21:53'),
(1150, 12, 'ac90ee92-a6a1-4bc1-8a07-4ce33fef9463', 1, '2026-05-08 00:46:16'),
(1166, 2, '623b0473-9a60-40b2-bd5b-d5aba16a24f3', 1, '2026-05-06 00:04:47');

-- --------------------------------------------------------

--
-- Structure de la table `achat`
--

CREATE TABLE `achat` (
  `id_achat` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_panier` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `achat`
--

INSERT INTO `achat` (`id_achat`, `id_utilisateur`, `id_panier`, `date`) VALUES
(1, 1, 1, '2026-03-21 16:47:09'),
(13, 1, 2, '2026-03-21 17:22:41'),
(14, 1, 3, '2026-03-21 17:24:26'),
(15, 1, 4, '2026-03-21 17:26:43'),
(16, 1, 5, '2026-03-21 17:26:56'),
(17, 1, 6, '2026-03-21 20:49:37'),
(18, 1, 7, '2026-03-22 15:26:26'),
(19, 1, 8, '2026-03-22 15:26:49'),
(20, 1, 9, '2026-03-23 12:56:06'),
(21, 1, 10, '2026-03-23 22:04:23'),
(22, 1, 11, '2026-03-31 09:54:59');

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id_article` int(11) NOT NULL,
  `titre` varchar(150) DEFAULT NULL,
  `image` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id_article`, `titre`, `image`, `description`, `prix`) VALUES
(1, 'Chat', '', 'Des chats a vendre', 77.00),
(2, 'La dignité de Laurent', '', 'C\'est cadeau même si c\'est pas grand chose', 0.01),
(3, 'ZZZ22', 'article_1774182109.png', 'zzzz', 23.00);

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id_categorie` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `valide_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id_categorie`, `nom`, `valide_admin`) VALUES
(1, 'jardinage', 1),
(2, 'consultation medical', 1),
(3, 'categorie_test', 1),
(4, 'categorie de test 2', 1);

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`) VALUES
(1, 'Transport'),
(2, 'Soin et bien-etre'),
(3, 'Tourisme / Hebergement'),
(4, 'Service a domicile'),
(5, 'Loisirs & Sortie'),
(6, 'Shoping');

-- --------------------------------------------------------

--
-- Structure de la table `champs_supplementaires`
--

CREATE TABLE `champs_supplementaires` (
  `id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `input_id` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Déchargement des données de la table `champs_supplementaires`
--

INSERT INTO `champs_supplementaires` (`id`, `categorie_id`, `label`, `type`, `input_id`) VALUES
(1, 1, 'Permis de conduire', 'file', 'permis_de_conduire'),
(2, 1, 'Assurance véhicule', 'file', 'assurance_vehicule'),
(3, 2, 'Certification', 'file', 'certification'),
(4, 2, 'Spécialité', 'text', 'specialite'),
(5, 3, 'Type de logement', 'select', 'type_logement'),
(6, 3, 'Adresse', 'text', 'adresse'),
(7, 3, 'Photos', 'file', 'photos_logement'),
(8, 4, 'Permis de conduire', 'file', 'permis_service'),
(9, 4, 'Expérience', 'text', 'experience'),
(10, 5, 'Type d\'activité', 'text', 'type_activite'),
(11, 5, 'Description', 'text', 'description'),
(12, 5, 'Permis de conduire', 'file', 'permis_loisir'),
(13, 6, 'Preuve de livraison', 'file', 'livraison');

-- --------------------------------------------------------

--
-- Structure de la table `conseil`
--

CREATE TABLE `conseil` (
  `id_conseil` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `titre` varchar(150) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `contenu` text DEFAULT NULL,
  `date_publication` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `conseil`
--

INSERT INTO `conseil` (`id_conseil`, `id_utilisateur`, `titre`, `image`, `contenu`, `date_publication`) VALUES
(1, 1, 'Lave toi les mains', '', 'Mets du savon papi', '2026-02-27 00:00:00'),
(2, 1, 'Marche', '', 'Il faut de l\'exercice pour sa santé', '2026-02-19 00:00:00'),
(3, 1, 'la salade est benefique', 'conseil_1773920213220_AlexendreleGrand.png', 'eeeeeeeeeeeeee', '2026-03-19 11:36:53'),
(4, 1, 'la salade est benefique2', 'conseil_1773920483573_Cloptre.png', 'sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', '2026-03-19 11:41:23'),
(5, 1, 'la salade est benefique3', 'conseil_1773921332.png', 'ddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd', '2026-03-19 11:55:32'),
(6, 1, 'la salade est benefique 4', '', 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz', '2026-03-21 20:23:19');

-- --------------------------------------------------------

--
-- Structure de la table `conseil_note`
--

CREATE TABLE `conseil_note` (
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_conseil` int(11) DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Déchargement des données de la table `conseil_note`
--

INSERT INTO `conseil_note` (`id_utilisateur`, `id_conseil`, `note`, `id`) VALUES
(4, 3, 5, 20),
(8, 3, 1, 21),
(5, 3, 4, 22),
(1, 3, 1, 23),
(7, 3, 3, 24),
(12, 3, 5, 25);

-- --------------------------------------------------------

--
-- Structure de la table `consultation_conseil`
--

CREATE TABLE `consultation_conseil` (
  `id_utilisateur` int(11) NOT NULL,
  `id_conseil` int(11) NOT NULL,
  `date_consultation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `contact`
--

CREATE TABLE `contact` (
  `id_contact` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `contenu` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Déchargement des données de la table `contact`
--

INSERT INTO `contact` (`id_contact`, `id_utilisateur`, `contenu`) VALUES
(7, 1, 'lucas est un petit peu autiste'),
(8, 1, 'lucas est un petit peu autiste'),
(9, 1, 'lucas est un petit peu autiste'),
(10, 1, 'lucas est un petit peu autiste'),
(11, 12, 'teest'),
(12, 12, 'rfdzs'),
(13, 12, 'fdddd'),
(14, 12, 'eedddd'),
(15, 12, 'ffffff'),
(16, 12, 'ffffff');

-- --------------------------------------------------------

--
-- Structure de la table `contrat`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `contrat`
--

INSERT INTO `contrat` (`id_contrat`, `id_devis`, `id_utilisateur`, `id_prestataire`, `date_debut`, `date_fin`, `nom`, `type_paiement`, `type_contrat`) VALUES
(1, NULL, 1, NULL, NULL, NULL, 'contrat 1', NULL, 'presta'),
(2, NULL, 1, NULL, NULL, NULL, 'contrat2', NULL, 'presta'),
(3, NULL, 1, NULL, '2026-03-28', '2026-04-28', 'Contrat abonnement Premium', 'mois', 'site'),
(6, NULL, 5, NULL, '2026-04-03', '2026-05-03', 'Contrat abonnement Premium', 'mois', 'site'),
(7, NULL, 5, NULL, '2026-04-05', '2026-05-05', 'Contrat abonnement Premium', 'mois', 'site'),
(8, NULL, 5, NULL, '2026-04-19', '2026-05-19', 'Contrat abonnement Premium', 'mois', 'site'),
(9, NULL, 5, NULL, '2026-04-21', '2026-05-21', 'Contrat abonnement Premium', 'mois', 'site'),
(10, NULL, 5, NULL, '2026-05-01', '2026-06-01', 'Contrat abonnement Premium', 'mois', 'site');

-- --------------------------------------------------------

--
-- Structure de la table `devis`
--

CREATE TABLE `devis` (
  `id_devis` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `id_intervention` int(11) NOT NULL,
  `tarif_personalise` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `devis`
--

INSERT INTO `devis` (`id_devis`, `id_utilisateur`, `id_prestataire`, `id_intervention`, `tarif_personalise`, `status`) VALUES
(18, 5, 1, 19, 4.00, 'refusé'),
(19, 5, 1, 20, 3.00, 'en_attente'),
(20, 5, 1, 21, 4.00, 'accepté');

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
  `statut` varchar(50) DEFAULT NULL,
  `jour_semaine` enum('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') DEFAULT NULL,
  `type_regle` enum('disponible','indisponible') NOT NULL DEFAULT 'disponible',
  `recurrence` enum('unique','hebdomadaire') DEFAULT 'unique',
  `date_fin_regle` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `disponibilite`
--

INSERT INTO `disponibilite` (`id_disponibilite`, `id_prestataire`, `date`, `heure_debut`, `heure_fin`, `statut`, `jour_semaine`, `type_regle`, `recurrence`, `date_fin_regle`) VALUES
(1, 1, '2026-03-15', '10:00:00', '19:00:00', NULL, NULL, 'disponible', 'unique', NULL),
(2, 1, '2026-03-20', '10:00:00', '19:00:00', NULL, NULL, 'disponible', 'unique', NULL),
(3, 1, '2026-05-16', '10:00:00', '19:00:00', NULL, NULL, 'disponible', 'unique', NULL),
(46, 1, '2026-04-15', '10:00:00', '11:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(47, 1, '2026-04-15', '11:00:00', '12:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(48, 1, '2026-04-15', '12:00:00', '13:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(49, 1, '2026-04-15', '13:00:00', '14:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(51, 2, '2026-04-04', '07:00:00', '22:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(52, 2, '2026-04-03', '06:30:00', '07:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(53, 2, '2026-04-07', '07:00:00', '09:30:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(54, 2, '2026-04-03', '07:00:00', '11:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(55, 2, '2026-04-04', '22:00:00', '23:30:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(56, 2, '2026-04-05', '20:30:00', '22:30:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(57, 2, '2026-04-02', '09:00:00', '11:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(58, 2, '2026-04-05', '13:30:00', '15:30:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(59, 2, '2026-04-05', '07:00:00', '11:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(60, 2, '2026-04-05', '11:30:00', '13:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(61, 2, '2026-04-05', '16:00:00', '17:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(62, 2, '2026-04-07', '10:00:00', '13:30:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(63, 2, '2026-04-08', '08:30:00', '15:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(64, 2, '2026-04-09', '08:00:00', '15:30:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(67, 2, '2026-04-10', '11:00:00', '16:30:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(68, 2, '2026-04-12', '08:00:00', '12:30:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(69, 2, '2026-04-12', '15:00:00', '21:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(70, 2, '2026-04-07', '14:30:00', '19:30:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(71, 2, '2026-04-08', '15:30:00', '18:30:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(72, 2, '2026-04-09', '16:30:00', '20:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(73, 2, '2026-04-10', '17:30:00', '21:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(74, 2, '2026-04-11', '17:00:00', '20:30:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(75, 2, '2026-04-06', '09:00:00', '15:30:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(76, 2, '2026-04-06', '17:30:00', '23:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(77, 2, '2026-04-08', '20:30:00', '23:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(78, 2, '2026-04-09', '21:30:00', '23:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(79, 2, '2026-04-10', '21:30:00', '23:30:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(80, 2, '2026-04-11', '21:30:00', '23:30:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(81, 2, '2026-04-12', '22:00:00', '23:30:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(82, 2, '2026-04-08', '15:00:00', '15:30:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(83, 2, '2026-04-09', '15:30:00', '16:30:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(84, 2, '2026-04-10', '16:30:00', '17:30:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(85, 2, '2026-04-11', '15:00:00', '17:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(86, 2, '2026-04-12', '12:30:00', '14:30:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(87, 2, '2026-04-12', '14:30:00', '15:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(88, 2, '2026-04-10', '10:00:00', '11:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(90, 2, '2026-04-08', '07:00:00', '08:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(91, 2, '2026-04-09', '06:30:00', '07:30:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(93, 2, '2026-04-10', '06:30:00', '08:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(94, 2, '2026-04-11', '06:00:00', '07:30:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(95, 2, '2026-04-12', '06:00:00', '08:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(96, 2, '2026-04-06', '06:00:00', '09:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(98, 2, '2026-04-11', '07:30:00', '15:00:00', 'disponible', NULL, 'disponible', 'unique', NULL),
(99, 2, '2026-04-13', '08:00:00', '17:00:00', 'disponible', 'lundi', 'disponible', 'hebdomadaire', '2026-05-13'),
(100, 2, '2026-04-13', '06:00:00', '08:00:00', 'indisponible', NULL, 'indisponible', 'hebdomadaire', '2027-05-05'),
(101, 2, '2026-04-13', '17:00:00', '23:30:00', 'indisponible', NULL, 'indisponible', 'hebdomadaire', '2027-05-05'),
(103, 2, '2026-04-14', '09:00:00', '10:00:00', 'indisponible', 'mardi', 'indisponible', 'hebdomadaire', '2026-05-05'),
(104, 1, '2026-05-16', '10:00:00', '11:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(107, 2, '2026-04-24', '14:00:00', '17:00:00', 'indisponible', NULL, 'indisponible', 'unique', NULL),
(108, 2, '2026-04-22', '13:00:00', '14:30:00', 'indisponible', NULL, 'indisponible', 'hebdomadaire', '2028-08-21');

-- --------------------------------------------------------

--
-- Structure de la table `document`
--

CREATE TABLE `document` (
  `id_document` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `type_document` enum('PF','CIR','CIV','habilitation','diplome','autre') DEFAULT 'autre',
  `nom_fichier` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) DEFAULT NULL,
  `date_upload` datetime DEFAULT current_timestamp(),
  `valide` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Déchargement des données de la table `document`
--

INSERT INTO `document` (`id_document`, `id_utilisateur`, `type_document`, `nom_fichier`, `chemin_fichier`, `date_upload`, `valide`) VALUES
(1, 5, 'autre', 'recto_1776004115.png', NULL, '2026-04-12 14:28:35', 0),
(20, 7, 'PF', 'pf_1776615656.png', NULL, '2026-04-19 16:20:57', 0),
(21, 7, 'CIR', 'cir_1776615657.png', NULL, '2026-04-19 16:20:57', 0),
(22, 7, 'CIV', 'civ_1776615657.png', NULL, '2026-04-19 16:20:57', 0),
(23, 7, 'diplome', 'diplome_1776615657.png', NULL, '2026-04-19 16:20:57', 0),
(24, 7, 'autre', 'permis_1776615657.png', NULL, '2026-04-19 16:20:57', 0),
(25, 7, 'autre', 'assurance_1776615657.png', NULL, '2026-04-19 16:20:57', 0),
(26, 9, 'PF', 'pf_1776782996.png', NULL, '2026-04-21 14:49:56', 0),
(27, 9, 'CIR', 'cir_1776782996.png', NULL, '2026-04-21 14:49:56', 0),
(28, 9, 'CIV', 'civ_1776782996.png', NULL, '2026-04-21 14:49:56', 0),
(29, 9, 'diplome', 'diplome_1776782996.png', NULL, '2026-04-21 14:49:56', 0),
(30, 9, 'autre', 'permis_1776782996.png', NULL, '2026-04-21 14:49:56', 0),
(31, 9, 'autre', 'assurance_1776782996.png', NULL, '2026-04-21 14:49:56', 0);

-- --------------------------------------------------------

--
-- Structure de la table `document_txt`
--

CREATE TABLE `document_txt` (
  `id_document_txt` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `categorie_text` varchar(50) NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluation`
--

CREATE TABLE `evaluation` (
  `id_evaluation` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_service` int(11) DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `evaluation`
--

INSERT INTO `evaluation` (`id_evaluation`, `id_utilisateur`, `id_service`, `note`, `commentaire`, `date`) VALUES
(2, 1, 2, 5, 'Excellent prestataire, très professionnel et ponctuel. Je recommande vivement !', '2026-03-10'),
(3, 3, 2, 4, 'Bon travail dans l ensemble, quelques petits détails à améliorer mais globalement très satisfait.', '2026-03-22'),
(4, 6, 2, 3, 'Correct, mais pas exceptionnel. Communication perfectible.', '2026-04-01');

-- --------------------------------------------------------

--
-- Structure de la table `evenement`
--

CREATE TABLE `evenement` (
  `id_evenement` int(11) NOT NULL,
  `nom` varchar(150) DEFAULT NULL,
  `image` varchar(30) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `tarif` decimal(10,2) DEFAULT NULL,
  `lieu` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `evenement`
--

INSERT INTO `evenement` (`id_evenement`, `nom`, `image`, `date`, `description`, `tarif`, `lieu`) VALUES
(1, 'Manger', '', '2026-07-10 14:09:00', 'Allez manger un déjeuné équilibré pour votre santé', 10.00, 'A Bangkok'),
(2, 'Machine a laver 2', '', '2026-07-27 23:43:00', 'Allez courir sur une machine a laver géante : age conseillé : moins de 66 ans sinon crise cardiaque assurée', 15.00, 'Chez M. Delon'),
(3, 'eeee', 'evenement_1774126024.png', '2026-08-18 07:00:00', 'zzzzz', 20.00, 'Dans la caverne aux mysteres'),
(4, 'test', '', '2026-10-10 19:30:00', 'un evenement test', 0.00, 'Bientot'),
(5, 'testtt', '', '2026-05-16 00:13:00', 'test', 7.00, 'JSP wola'),
(6, 'moché', '', '2026-05-29 00:13:00', 'léone', 74.00, 'Dans la tour BigBen');

-- --------------------------------------------------------

--
-- Structure de la table `facture_prestataire`
--

CREATE TABLE `facture_prestataire` (
  `id_facture` int(11) NOT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `mois` varchar(20) DEFAULT NULL,
  `montant_total` decimal(10,2) DEFAULT NULL,
  `date_generation` date DEFAULT NULL,
  `fichier_pdf` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `facture_prestataire`
--

INSERT INTO `facture_prestataire` (`id_facture`, `id_prestataire`, `mois`, `montant_total`, `date_generation`, `fichier_pdf`) VALUES
(1, 2, '2026-04', 4.00, '2026-04-06', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `intervention`
--

CREATE TABLE `intervention` (
  `id_intervention` int(11) NOT NULL,
  `id_service` int(11) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_rdv` int(11) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `intervention`
--

INSERT INTO `intervention` (`id_intervention`, `id_service`, `id_prestataire`, `id_utilisateur`, `id_rdv`, `statut`, `montant`) VALUES
(19, 1, 1, 5, 80, 'annulé', 4.00),
(20, 1, 1, 5, 83, 'devis', 3.00),
(21, 1, 1, 5, 85, 'confirmé', 4.00),
(22, 1, 2, 5, 84, 'terminé', 4.00),
(23, 1, 1, 8, 86, 'confirmé', 4.00);

-- --------------------------------------------------------

--
-- Structure de la table `lien_contact`
--

CREATE TABLE `lien_contact` (
  `id_lien` int(11) NOT NULL,
  `id_user1` int(11) NOT NULL,
  `id_user2` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `lien_contact_state`
--

CREATE TABLE `lien_contact_state` (
  `id` int(11) NOT NULL,
  `id_user1` int(11) DEFAULT NULL,
  `id_user2` int(11) DEFAULT NULL,
  `state` enum('bloquer','demande_ami') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `id_message` int(11) NOT NULL,
  `id_expediteur` int(11) DEFAULT NULL,
  `id_destinataire` int(11) DEFAULT NULL,
  `contenu` text DEFAULT NULL,
  `date_envoie` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `message`
--

INSERT INTO `message` (`id_message`, `id_expediteur`, `id_destinataire`, `contenu`, `date_envoie`) VALUES
(1, 1, 5, 'salut', '2026-03-29 20:28:29'),
(2, 1, 12, 'ca va', '2026-03-29 20:29:03'),
(3, 12, 5, 'test', '2026-03-31 09:34:03'),
(4, 12, 3, 'f', '2026-05-05 11:11:43'),
(5, 12, 3, 'ff', '2026-05-05 11:11:45'),
(7, 3, 12, 'nigga', '2026-05-05 11:42:47'),
(12, 12, 3, 'ee', '2026-05-05 11:34:11'),
(13, 3, 12, 'bb', '2026-05-05 12:04:29'),
(14, 12, 3, 'ff', '2026-05-05 12:05:35'),
(15, 3, 12, 'tt', '2026-05-05 17:03:55'),
(16, 12, 3, 'tg', '2026-05-05 17:08:04'),
(17, 12, 3, 'nigga', '2026-05-05 17:08:15'),
(18, 12, 3, 'ff', '2026-05-05 23:50:04'),
(19, 2, 12, 'CC', '2026-05-06 00:05:52'),
(20, 12, 2, 'KK', '2026-05-06 00:06:04'),
(21, 12, 2, 'dd', '2026-05-06 19:49:51'),
(22, 12, 4, 'yo', '2026-05-06 20:07:32'),
(23, 12, 4, 'cc', '2026-05-06 20:07:36'),
(24, 12, 2, 'jsp', '2026-05-06 20:07:41'),
(25, 12, 4, 'cc', '2026-05-06 20:18:56'),
(26, 12, 2, 'oo', '2026-05-06 20:19:01'),
(27, 12, 2, 'oo', '2026-05-06 20:19:03'),
(28, 12, 1, 'nigga', '2026-05-06 22:17:49'),
(29, 12, 1, 'tg', '2026-05-06 22:35:55'),
(30, 2, 12, 'hhhhhhhhhhhh', '2026-05-08 00:45:25'),
(31, 2, 12, 'frrrrrrrrrrrrrrrrrr', '2026-05-08 00:45:30'),
(32, 2, 12, 'tg', '2026-05-08 00:45:42'),
(33, 12, 2, 'kk', '2026-05-08 00:47:39');

-- --------------------------------------------------------

--
-- Structure de la table `modele_notification`
--

CREATE TABLE `modele_notification` (
  `id_modele` int(11) NOT NULL,
  `cle` varchar(100) NOT NULL,
  `titre` varchar(50) NOT NULL,
  `contenu` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `modele_notification`
--

INSERT INTO `modele_notification` (`id_modele`, `cle`, `titre`, `contenu`) VALUES
(1, 'reservation_service', 'Réservation confirmée', 'Votre réservation pour le service \"{service}\" est confirmée le {date}.'),
(2, 'reservation_evenement', 'Réservation confirmée', 'Votre réservation pour l\'événement \"{evenement}\" est confirmée.'),
(3, 'commande_creee', 'Commande créée', 'Votre commande #{id} a été créée via {mode}.'),
(4, 'paiement_mise_a_jour', 'Paiement mis à jour', 'Votre paiement pour la commande #{id} est maintenant : {statut}.'),
(5, 'contact_recu', 'Nouveau message de contact', 'Un utilisateur vous a envoyé un nouveau message de contact.'),
(6, 'abonnement_active', 'Abonnement activé', 'Votre abonnement {type} est maintenant actif. Profitez de tous les avantages inclus !');

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
  `id_notification` int(11) NOT NULL,
  `id_expediteur` int(11) DEFAULT NULL,
  `id_destinataire` int(11) DEFAULT NULL,
  `Titre` varchar(50) NOT NULL,
  `contenu` text DEFAULT NULL,
  `date_envoie` datetime DEFAULT NULL,
  `lu` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `notification`
--

INSERT INTO `notification` (`id_notification`, `id_expediteur`, `id_destinataire`, `Titre`, `contenu`, `date_envoie`, `lu`) VALUES
(14, 5, 5, 'Réservation confirmée', 'Votre réservation pour l\'événement \"eeee\" est confirmée.', '2026-03-25 19:24:46', 1),
(15, 5, 5, 'Réservation confirmée', 'Votre réservation pour l\'événement \"Machine a laver 2\" est confirmée.', '2026-03-25 19:25:46', 1),
(16, 5, 5, 'Réservation confirmée', 'Votre réservation pour le service \"Faire chier Laurent\" est confirmée le 15/04/2026 à 11:00.', '2026-03-25 19:26:57', 1),
(17, 5, 5, 'Réservation confirmée', 'Votre réservation pour l\'événement \"eeee\" est confirmée.', '2026-03-25 19:31:31', 1),
(21, 1, 5, 'Nouveau message de contact', 'Un utilisateur vous a envoyé un nouveau message de contact.', '2026-03-25 19:51:56', 1),
(22, 1, 5, 'Nouveau message de contact', 'Un utilisateur vous a envoyé un nouveau message de contact.', '2026-03-25 19:57:53', 1),
(23, 5, 1, 'Réservation confirmée', 'Votre réservation pour le service \"Faire chier Laurent\" est confirmée le 15/04/2026 à 10:00.', '2026-03-28 12:11:45', 1),
(24, 5, 1, 'Réservation confirmée', 'Votre réservation pour le service \"Faire chier Laurent\" est confirmée le 15/04/2026 à 10:00.', '2026-03-28 12:15:57', 1),
(25, 5, 1, 'Réservation confirmée', 'Votre réservation pour le service \"Faire chier Laurent\" est confirmée le 15/04/2026 à 10:00.', '2026-03-28 15:11:03', 1),
(26, 5, 1, 'Réservation confirmée', 'Votre réservation pour le service \"Faire chier Laurent\" est confirmée le 15/04/2026 à 10:00.', '2026-03-28 16:03:51', 1),
(27, 5, 1, 'Réservation confirmée', 'Votre réservation pour le service \"Faire chier Laurent\" est confirmée le 15/04/2026 à 10:00.', '2026-03-28 16:08:57', 1),
(28, 5, 1, 'Réservation confirmée', 'Votre réservation pour le service \"Faire chier Laurent\" est confirmée le 15/04/2026 à 10:00.', '2026-03-28 16:20:10', 1),
(29, 5, 1, 'Commande créée', 'Votre commande #22 a été créée via stripe.', '2026-03-31 09:54:59', 1),
(30, NULL, 5, 'Réservation confirmée', 'Votre réservation pour le service \"Faire chier Laurent\" est confirmée le 15/04/2026 à 10:00.', '2026-03-31 20:47:50', 1),
(31, NULL, 5, 'Réservation confirmée', 'Votre réservation pour l\'événement \"Manger\" est confirmée.', '2026-03-31 20:57:58', 1),
(32, NULL, 5, 'Réservation confirmée', 'Votre réservation pour l\'événement \"test\" est confirmée.', '2026-04-02 20:35:45', 1),
(33, NULL, 5, 'Réservation confirmée', 'Votre réservation pour l\'événement \"eeee\" est confirmée.', '2026-04-02 20:35:54', 1),
(34, NULL, 5, 'Réservation confirmée', 'Votre réservation pour le service \"Faire chier Laurent\" est confirmée le 15/04/2026 à 10:00.', '2026-04-02 20:41:18', 1),
(35, NULL, 5, 'Réservation confirmée', 'Votre réservation pour l\'événement \"test\" est confirmée.', '2026-04-02 20:41:46', 1),
(36, NULL, 5, 'Réservation confirmée', 'Votre réservation pour l\'événement \"eeee\" est confirmée.', '2026-04-02 20:53:41', 1),
(37, NULL, 5, 'Réservation confirmée', 'Votre réservation pour le service \"Faire chier Laurent\" est confirmée le 15/04/2026 à 11:00.', '2026-04-02 21:24:29', 1),
(38, NULL, 5, 'Réservation confirmée', 'Votre réservation pour le service \"Faire chier Laurent\" est confirmée le 15/04/2026 à 12:00.', '2026-04-02 21:37:03', 1),
(39, NULL, 5, 'Réservation confirmée', 'Votre réservation pour le service \"Faire chier Laurent\" est confirmée le 15/04/2026 à 13:00.', '2026-04-02 21:41:51', 1),
(40, NULL, 8, 'Réservation confirmée', 'Votre réservation pour le service \"Faire chier Laurent\" est confirmée le 16/05/2026 à 10:00.', '2026-04-21 07:53:09', 1),
(41, NULL, 12, 'Réservation confirmée', 'Votre réservation pour l\'événement \"moché\" est confirmée.', '2026-05-09 22:20:35', 1),
(42, NULL, 12, 'Réservation confirmée', 'Votre réservation pour l\'événement \"Machine a laver 2\" est confirmée.', '2026-05-09 22:50:08', 1);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `paiement`
--

INSERT INTO `paiement` (`id_paiement`, `id_achat`, `montant`, `date`, `mode`, `statut`) VALUES
(1, 1, 77.01, '2026-03-21 17:21:29', 'transfer', 'pending_transfer'),
(2, 13, 77.00, '2026-03-21 17:22:41', 'stripe', 'pending'),
(3, 14, 77.00, '2026-03-21 17:24:26', 'transfer', 'pending_transfer'),
(4, 15, 0.01, '2026-03-21 17:26:43', 'transfer', 'pending_transfer'),
(5, 16, 77.00, '2026-03-21 17:26:56', 'stripe', 'paid'),
(6, 17, 0.01, '2026-03-21 20:49:37', 'stripe', 'paid'),
(7, 18, 23.00, '2026-03-22 15:26:26', 'transfer', 'pending_transfer'),
(8, 19, 23.00, '2026-03-22 15:26:49', 'stripe', 'pending'),
(9, 20, 23.00, '2026-03-23 12:56:06', 'stripe', 'pending'),
(10, 21, 77.00, '2026-03-23 22:04:23', 'stripe', 'pending'),
(11, 22, 23.00, '2026-03-31 09:54:59', 'stripe', 'pending');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `paiement_abonnement`
--

INSERT INTO `paiement_abonnement` (`id_paiement_abonnement`, `id_abonnement`, `montant`, `date`, `mode`, `statut`) VALUES
(9, 2, 5.00, '2026-03-28', 'stripe', 'pending'),
(10, 3, 6.00, '2026-03-28', 'stripe', 'pending'),
(11, 5, 300.00, '2026-03-28', 'stripe', 'canceled'),
(12, 5, 30.00, '2026-03-28', 'stripe', 'canceled'),
(13, 5, 30.00, '2026-03-31', 'stripe', 'canceled'),
(14, 5, 30.00, '2026-03-31', 'stripe', 'canceled'),
(15, 5, 30.00, '2026-03-31', 'stripe', 'canceled'),
(16, 5, 30.00, '2026-03-31', 'stripe', 'canceled'),
(17, 5, 30.00, '2026-03-31', 'stripe', 'canceled'),
(18, 5, 30.00, '2026-03-31', 'stripe', 'canceled'),
(19, 5, 30.00, '2026-03-31', 'stripe', 'canceled'),
(20, 5, 30.00, '2026-04-02', 'stripe', 'canceled'),
(21, 5, 30.00, '2026-04-03', 'stripe', 'pending');

-- --------------------------------------------------------

--
-- Structure de la table `panier`
--

CREATE TABLE `panier` (
  `id_panier` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `date_creation` datetime DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `panier`
--

INSERT INTO `panier` (`id_panier`, `id_utilisateur`, `date_creation`, `statut`) VALUES
(1, 1, '2026-03-21 16:47:09', 'pending_transfer'),
(2, 1, '2026-03-21 17:22:41', 'pending_stripe'),
(3, 1, '2026-03-21 17:24:18', 'pending_transfer'),
(4, 1, '2026-03-21 17:26:36', 'pending_transfer'),
(5, 1, '2026-03-21 17:26:51', 'paid'),
(6, 1, '2026-03-21 20:49:30', 'paid'),
(7, 1, '2026-03-22 15:26:19', 'pending_transfer'),
(8, 1, '2026-03-22 15:26:42', 'pending_stripe'),
(9, 1, '2026-03-23 12:54:08', 'pending_stripe'),
(10, 1, '2026-03-23 22:04:18', 'pending_stripe'),
(11, 1, '2026-03-31 09:54:39', 'pending_stripe');

-- --------------------------------------------------------

--
-- Structure de la table `prestataire`
--

CREATE TABLE `prestataire` (
  `id_prestataire` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `photo_profil` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `valider` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `prestataire`
--

INSERT INTO `prestataire` (`id_prestataire`, `id_utilisateur`, `type`, `photo_profil`, `telephone`, `valider`) VALUES
(1, 4, 'coordonnier', NULL, '32', 1),
(2, 5, 'sportif', NULL, '34', 1),
(12, 7, 'Transport', 'pf_1776615656.png', NULL, 1),
(13, 9, 'Transport', 'pf_1776782996.png', NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `reference_article`
--

CREATE TABLE `reference_article` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_panier` int(11) NOT NULL,
  `id_article` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Déchargement des données de la table `reference_article`
--

INSERT INTO `reference_article` (`id`, `id_utilisateur`, `id_panier`, `id_article`) VALUES
(2, 1, 1, 1),
(3, 1, 1, 2),
(4, 1, 2, 1),
(5, 1, 3, 1),
(6, 1, 4, 2),
(7, 1, 5, 1),
(8, 1, 6, 2),
(9, 1, 7, 3),
(10, 1, 8, 3),
(11, 1, 9, 3),
(12, 1, 10, 1),
(13, 1, 11, 3);

-- --------------------------------------------------------

--
-- Structure de la table `reference_evenement`
--

CREATE TABLE `reference_evenement` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_evenement` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `reference_evenement`
--

INSERT INTO `reference_evenement` (`id`, `id_utilisateur`, `id_evenement`) VALUES
(49, 1, 2),
(50, 5, 2),
(52, 5, 1),
(55, 5, 4),
(56, 5, 3);

-- --------------------------------------------------------

--
-- Structure de la table `reference_service`
--

CREATE TABLE `reference_service` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_service` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Déchargement des données de la table `reference_service`
--

INSERT INTO `reference_service` (`id`, `id_utilisateur`, `id_service`) VALUES
(48, 5, 1),
(51, 8, 1);

-- --------------------------------------------------------

--
-- Structure de la table `rendez_vous`
--

CREATE TABLE `rendez_vous` (
  `id_rdv` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `date_debut` datetime DEFAULT NULL,
  `date_fin` datetime NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `statut` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `rendez_vous`
--

INSERT INTO `rendez_vous` (`id_rdv`, `id_utilisateur`, `id_prestataire`, `date_debut`, `date_fin`, `type`, `statut`) VALUES
(1, 2, 1, '2026-03-06 14:09:00', '2026-03-06 15:09:00', 'rendez-vous pour des chaussures', 'attente'),
(55, 1, NULL, '2026-07-27 23:43:00', '2026-07-28 00:43:00', 'Machine a laver 2', 'confirmé'),
(62, 5, NULL, '2026-07-27 23:43:00', '2026-07-28 00:43:00', 'Machine a laver 2', 'confirmé'),
(77, 5, NULL, '2026-03-06 14:09:00', '2026-03-06 15:09:00', 'Manger', 'confirmé'),
(80, 5, 1, '2026-04-15 10:00:00', '2026-04-15 11:00:00', 'Faire chier Laurent', 'annulé'),
(81, 5, NULL, '2026-03-31 19:30:00', '2026-03-31 20:30:00', 'test', 'confirmé'),
(82, 5, NULL, '2026-03-29 07:00:00', '2026-03-29 08:00:00', 'eeee', 'confirmé'),
(83, 5, 1, '2026-04-15 11:00:00', '2026-04-15 12:00:00', 'Faire chier Laurent', 'annulé'),
(84, 5, 1, '2026-04-15 12:00:00', '2026-04-15 13:00:00', 'Faire chier Laurent', 'confirmé'),
(85, 5, 2, '2026-04-15 13:00:00', '2026-04-15 14:00:00', 'Faire chier Laurent', 'confirmé'),
(86, 8, 1, '2026-05-16 10:00:00', '2026-05-16 11:00:00', 'Faire chier Laurent', 'confirmé');

-- --------------------------------------------------------

--
-- Structure de la table `sanction`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `sanction`
--

INSERT INTO `sanction` (`id`, `id_user`, `type`, `motif`, `date_crea`, `date_fin`, `date_levee`, `par_admin`, `levee_par`, `active`) VALUES
(1, 11, 'perm', 'est juste un nuisible a a societe', '2026-04-29 19:04:52', NULL, '2026-04-30 18:23:40', 5, 5, 0),
(2, 1, 'temp', 'Non respect des règles', '2026-05-10 13:54:59', '2026-05-17 13:54:59', '2026-05-10 13:55:02', 12, 12, 0),
(3, 1, 'temp', 'Non respect des règles', '2026-05-10 13:56:24', '2026-05-17 13:56:24', '2026-05-10 13:56:26', 12, 12, 0);

-- --------------------------------------------------------

--
-- Structure de la table `service`
--

CREATE TABLE `service` (
  `id_service` int(11) NOT NULL,
  `nom` varchar(150) DEFAULT NULL,
  `image` varchar(30) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `tarif` decimal(10,2) DEFAULT NULL,
  `id_prestataire` int(11) DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL,
  `valide_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `service`
--

INSERT INTO `service` (`id_service`, `nom`, `image`, `description`, `tarif`, `id_prestataire`, `id_categorie`, `valide_admin`) VALUES
(1, 'rencontrez Laurent', '', 'venez profitez de cette super expérience en sa compagnie', 4.00, 1, 1, 1),
(2, 'Cirage de cheveux', '', 'vous voulez que l\'on prennent soin de vos cheveux venez profitez de cette super expérience', 44.40, 2, 2, 1),
(3, 'test_service3', 'service_1774126058.png', 'zzzzzzzzzz', 23.00, NULL, NULL, 1),
(4, 'service de test', 'service_1776782283.png', 'un super service inutile', 19.00, 2, 4, 1);

-- --------------------------------------------------------

--
-- Structure de la table `souscris_abonnement`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Déchargement des données de la table `souscris_abonnement`
--

INSERT INTO `souscris_abonnement` (`id_souscrit`, `date_souscription`, `date_expiration`, `validite`, `type_paiement`, `id_utilisateur`, `id_abonnement`, `stripe_customer_id`, `stripe_subscription_id`) VALUES
(9, '2026-03-28 10:32:04', NULL, 0, 'mois', 1, 2, 'cus_UEMtadIAa7D0sa', NULL),
(10, '2026-03-28 10:32:13', NULL, 0, 'mois', 1, 3, 'cus_UEMtadIAa7D0sa', NULL),
(11, '2026-03-28 10:46:22', NULL, 0, 'an', 1, 5, 'cus_UEMtadIAa7D0sa', NULL),
(12, '2026-03-28 11:28:31', NULL, 1, 'mois', 1, 5, 'cus_UEMtadIAa7D0sa', NULL),
(13, '2026-03-31 20:58:36', '2026-03-31 21:16:00', 0, 'mois', 5, 5, 'cus_UFegbIU5G4W4cl', NULL),
(14, '2026-03-31 21:16:09', '2026-03-31 21:26:11', 0, 'mois', 5, 5, 'cus_UFegbIU5G4W4cl', NULL),
(15, '2026-03-31 21:26:16', '2026-03-31 21:27:01', 0, 'mois', 5, 5, 'cus_UFegbIU5G4W4cl', NULL),
(16, '2026-03-31 21:27:06', '2026-03-31 21:30:50', 0, 'mois', 5, 5, 'cus_UFegbIU5G4W4cl', NULL),
(17, '2026-03-31 21:30:59', '2026-03-31 21:31:52', 0, 'mois', 5, 5, 'cus_UFegbIU5G4W4cl', NULL),
(18, '2026-03-31 21:31:58', '2026-03-31 21:32:33', 0, 'mois', 5, 5, 'cus_UFegbIU5G4W4cl', NULL),
(19, '2026-03-31 21:32:43', '2026-04-02 21:49:15', 0, 'mois', 5, 5, 'cus_UFegbIU5G4W4cl', NULL),
(20, '2026-04-02 21:49:21', '2026-04-02 21:56:31', 0, 'mois', 5, 5, 'cus_UFegbIU5G4W4cl', NULL),
(21, '2026-04-03 17:37:54', NULL, 1, 'mois', 5, 5, 'cus_UFegbIU5G4W4cl', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `synthese_facture`
--

CREATE TABLE `synthese_facture` (
  `id_facture` int(11) NOT NULL,
  `id_intervention` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `synthese_facture`
--

INSERT INTO `synthese_facture` (`id_facture`, `id_intervention`) VALUES
(1, 22);

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
  `utiliser` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `date_naissance`, `telephone`, `password`, `token`, `role`, `image`, `langue`, `taille_police`, `tutoriel`, `verifier`, `abonnée`, `statut_user`, `fin_susp`) VALUES
(1, 'Laurent', 'Voillot', 'test@example.com', '2026-05-10', NULL, '$2a$10$FQ8LUXWRx6HEtCNmzTYeQ.RVaSE8qTp06AYWJdFogUWJQLILGEi6y', NULL, 'prestataire', '', 'fr', '1', 0, 0, 0, 'actif', NULL),
(2, 'Marc', 'Claude', 'bb@bb', '0000-00-00', NULL, '$2a$10$ComWff4hrpcLJ96fFXH/e.DGMX5mFGi8Gc1l5f/f3rvp6ZRT.hJwS', NULL, 'adherant', '', 'en', '1', 0, 0, 0, 'actif', NULL),
(3, 'bb', 'bb', 'cc@cc', '0000-00-00', NULL, '$2a$10$5A2yFwC/TmJeJfEbutmqi.tM.3KmGBrGtKZ54C5Dy9lQFeFNsjBAy', NULL, 'adherant', '', 'fr', '1', 0, 0, 0, 'actif', NULL),
(4, 'cc', 'ac', 'cc@ccc', '0000-00-00', NULL, '$2a$10$fX.X2TUOz0xBn23ZFsOvkOBVVbTkgiMpAyro6aBVakEUjLzLvTp/y', NULL, 'adherant', '', 'fr', '1', 0, 0, 0, 'actif', NULL),
(5, 'admin', 'admin (le mdp est admin123)', 'aa@aa.com', '0000-00-00', NULL, '$2a$10$C0KrezVhxOcjsqJWx.74kOpBhL4.ajZEJvhohCR5gEBFcJb5KL8ry', 'yJP6-1Wyl3vfhsex1KrxZzYcuTHABFmxmEiFgwy28t4', 'adherant', '', 'fr', '1', 0, 0, 0, 'actif', NULL),
(6, 'edodolf', 'hilan', 'hittalan@gmail.com', '1950-02-23', NULL, '$2a$10$qJONOgnpXpCNY.bjSMBbVOjCg18V2KcaEiSABb6Aik43z8fSZCR06', 'MJcizSUF0XWcnD7I13_Au5a_vpF2tIHhZ_CgxeW9OKc', 'adherant', NULL, 'fr', '1', 1, 0, 0, 'actif', NULL),
(7, 'azertyio', 'ma', 'ss@ss', '2008-04-10', NULL, '$2a$10$n3//8dPCjL8ZEr9cDRyovORKCnRGUWA2N6vV4lU9Y.SRjOyjVh86S', NULL, 'adherant', NULL, 'fr', '1', 0, 0, 0, 'actif', NULL),
(8, 'm', 'm', 'aaa@aaa', '2008-04-02', NULL, '$2a$10$aPoWGhEkVFqpYHUjfjxVDe3ONXUFGnA3TjG1JywXArSBNqZTnuzEa', NULL, 'adherant', NULL, 'fr', '1', 0, 0, 0, 'actif', NULL),
(9, 's', 'monsieur', 'aaaa@aaaa', '2008-04-09', NULL, '$2a$10$lfcnGlVXDgPcU5yUVU5PTO98ylE7KEzan4mEIVu.6QCopyVLs79Ve', NULL, 'prestataire', NULL, 'fr', '1', 0, 0, 0, 'actif', NULL),
(10, 'a', 'a', 'aaaaa@aaaaa', '2008-04-03', NULL, '$2a$10$ijmloKyAy06JDLhB31ls0eiTZYt1m/Zpxx6UsWgY2gIRzGSF5M9Li', NULL, 'adherant', NULL, 'fr', '1', 0, 0, 0, 'actif', NULL),
(11, 'test', 'test', 'aa@aat', '2008-04-03', NULL, '$2a$10$YsE3dd7sQvcM1K2liGeVHeI5NKb9fhyIejSji3w6SAoic3YbfVIH2', 'Ad_DPrQ7-l5EBf-Az2dw4j2XUDdIhrRAjsmJ32xverU', 'adherant', NULL, 'fr', '1', 0, 0, 0, 'actif', NULL),
(12, 'Benayoun', 'Lucas', 'aa@aa', '2006-04-25', NULL, '$2a$10$AcNiNZkUh1kuGjdnp2lvPuF8.v2jokVmMkdv3I5t8mUVCoSExexDG', 'iGysaoZ6liSjIsDjYchH9CZCibeZW40xKFEmQwdY5Sg', 'admin', NULL, 'fr', '1', 0, 0, 0, 'actif', NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `abonnement`
--
ALTER TABLE `abonnement`
  ADD PRIMARY KEY (`id_abonnement`),
  ADD KEY `id_prestataire` (`type_prestataire`),
  ADD KEY `idx_id_prestataire` (`id_prestataire`);

--
-- Index pour la table `abonnement_push`
--
ALTER TABLE `abonnement_push`
  ADD PRIMARY KEY (`id_subscription`),
  ADD UNIQUE KEY `uniq_subscription_id` (`subscription_id`),
  ADD KEY `idx_push_user` (`id_utilisateur`);

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
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id_categorie`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `champs_supplementaires`
--
ALTER TABLE `champs_supplementaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categorie_id` (`categorie_id`);

--
-- Index pour la table `conseil`
--
ALTER TABLE `conseil`
  ADD PRIMARY KEY (`id_conseil`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `conseil_note`
--
ALTER TABLE `conseil_note`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_conseil` (`id_conseil`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `consultation_conseil`
--
ALTER TABLE `consultation_conseil`
  ADD PRIMARY KEY (`id_utilisateur`,`id_conseil`),
  ADD KEY `id_conseil` (`id_conseil`);

--
-- Index pour la table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id_contact`),
  ADD KEY `fk_user` (`id_utilisateur`);

--
-- Index pour la table `contrat`
--
ALTER TABLE `contrat`
  ADD PRIMARY KEY (`id_contrat`),
  ADD UNIQUE KEY `id_devis` (`id_devis`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_prestataire` (`id_prestataire`);

--
-- Index pour la table `devis`
--
ALTER TABLE `devis`
  ADD PRIMARY KEY (`id_devis`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_prestataire` (`id_prestataire`),
  ADD KEY `id_intervention` (`id_intervention`);

--
-- Index pour la table `disponibilite`
--
ALTER TABLE `disponibilite`
  ADD PRIMARY KEY (`id_disponibilite`),
  ADD KEY `id_prestataire` (`id_prestataire`);

--
-- Index pour la table `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`id_document`),
  ADD KEY `fk_document_user` (`id_utilisateur`);

--
-- Index pour la table `document_txt`
--
ALTER TABLE `document_txt`
  ADD PRIMARY KEY (`id_document_txt`),
  ADD KEY `fk_utilisateur_txt` (`id_utilisateur`);

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
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_rdv` (`id_rdv`);

--
-- Index pour la table `lien_contact`
--
ALTER TABLE `lien_contact`
  ADD PRIMARY KEY (`id_lien`),
  ADD KEY `id_user1` (`id_user1`),
  ADD KEY `id_user2` (`id_user2`);

--
-- Index pour la table `lien_contact_state`
--
ALTER TABLE `lien_contact_state`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user1` (`id_user1`),
  ADD KEY `id_user2` (`id_user2`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `id_expediteur` (`id_expediteur`),
  ADD KEY `id_destinataire` (`id_destinataire`);

--
-- Index pour la table `modele_notification`
--
ALTER TABLE `modele_notification`
  ADD PRIMARY KEY (`id_modele`),
  ADD UNIQUE KEY `cle` (`cle`);

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
-- Index pour la table `prestataire`
--
ALTER TABLE `prestataire`
  ADD PRIMARY KEY (`id_prestataire`),
  ADD UNIQUE KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `reference_article`
--
ALTER TABLE `reference_article`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_panier_article` (`id_utilisateur`,`id_panier`,`id_article`),
  ADD KEY `idx_article` (`id_article`),
  ADD KEY `idx_panier` (`id_panier`);

--
-- Index pour la table `reference_evenement`
--
ALTER TABLE `reference_evenement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_even` (`id_evenement`),
  ADD KEY `fk_util` (`id_utilisateur`);

--
-- Index pour la table `reference_service`
--
ALTER TABLE `reference_service`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_utilisateur` (`id_utilisateur`,`id_service`),
  ADD KEY `id_service` (`id_service`);

--
-- Index pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  ADD PRIMARY KEY (`id_rdv`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_prestataire` (`id_prestataire`);

--
-- Index pour la table `sanction`
--
ALTER TABLE `sanction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sanc_user_date` (`id_user`,`date_crea`),
  ADD KEY `idx_sanc_active_fin` (`active`,`date_fin`),
  ADD KEY `idx_sanc_admin` (`par_admin`),
  ADD KEY `fk_sanc_levee` (`levee_par`);

--
-- Index pour la table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`id_service`),
  ADD KEY `id_prestataire` (`id_prestataire`),
  ADD KEY `id_categorie` (`id_categorie`);

--
-- Index pour la table `souscris_abonnement`
--
ALTER TABLE `souscris_abonnement`
  ADD PRIMARY KEY (`id_souscrit`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_abonnement` (`id_abonnement`);

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
  MODIFY `id_abonnement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `abonnement_push`
--
ALTER TABLE `abonnement_push`
  MODIFY `id_subscription` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3054;

--
-- AUTO_INCREMENT pour la table `achat`
--
ALTER TABLE `achat`
  MODIFY `id_achat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `id_article` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `champs_supplementaires`
--
ALTER TABLE `champs_supplementaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `conseil`
--
ALTER TABLE `conseil`
  MODIFY `id_conseil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `conseil_note`
--
ALTER TABLE `conseil_note`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `contact`
--
ALTER TABLE `contact`
  MODIFY `id_contact` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `contrat`
--
ALTER TABLE `contrat`
  MODIFY `id_contrat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `devis`
--
ALTER TABLE `devis`
  MODIFY `id_devis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `disponibilite`
--
ALTER TABLE `disponibilite`
  MODIFY `id_disponibilite` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT pour la table `document`
--
ALTER TABLE `document`
  MODIFY `id_document` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT pour la table `document_txt`
--
ALTER TABLE `document_txt`
  MODIFY `id_document_txt` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `evaluation`
--
ALTER TABLE `evaluation`
  MODIFY `id_evaluation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `evenement`
--
ALTER TABLE `evenement`
  MODIFY `id_evenement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `facture_prestataire`
--
ALTER TABLE `facture_prestataire`
  MODIFY `id_facture` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `intervention`
--
ALTER TABLE `intervention`
  MODIFY `id_intervention` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `lien_contact`
--
ALTER TABLE `lien_contact`
  MODIFY `id_lien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT pour la table `lien_contact_state`
--
ALTER TABLE `lien_contact_state`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `modele_notification`
--
ALTER TABLE `modele_notification`
  MODIFY `id_modele` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `notification`
--
ALTER TABLE `notification`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT pour la table `paiement`
--
ALTER TABLE `paiement`
  MODIFY `id_paiement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `paiement_abonnement`
--
ALTER TABLE `paiement_abonnement`
  MODIFY `id_paiement_abonnement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `panier`
--
ALTER TABLE `panier`
  MODIFY `id_panier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `prestataire`
--
ALTER TABLE `prestataire`
  MODIFY `id_prestataire` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `reference_article`
--
ALTER TABLE `reference_article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `reference_evenement`
--
ALTER TABLE `reference_evenement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT pour la table `reference_service`
--
ALTER TABLE `reference_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  MODIFY `id_rdv` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT pour la table `sanction`
--
ALTER TABLE `sanction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `service`
--
ALTER TABLE `service`
  MODIFY `id_service` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `souscris_abonnement`
--
ALTER TABLE `souscris_abonnement`
  MODIFY `id_souscrit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `token`
--
ALTER TABLE `token`
  MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `virement`
--
ALTER TABLE `virement`
  MODIFY `id_virement` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `abonnement_push`
--
ALTER TABLE `abonnement_push`
  ADD CONSTRAINT `fk_push_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `achat`
--
ALTER TABLE `achat`
  ADD CONSTRAINT `achat_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `achat_ibfk_2` FOREIGN KEY (`id_panier`) REFERENCES `panier` (`id_panier`) ON DELETE SET NULL;

--
-- Contraintes pour la table `champs_supplementaires`
--
ALTER TABLE `champs_supplementaires`
  ADD CONSTRAINT `champs_supplementaires_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `conseil`
--
ALTER TABLE `conseil`
  ADD CONSTRAINT `conseil_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `conseil_note`
--
ALTER TABLE `conseil_note`
  ADD CONSTRAINT `conseil_note_ibfk_1` FOREIGN KEY (`id_conseil`) REFERENCES `conseil` (`id_conseil`) ON DELETE CASCADE,
  ADD CONSTRAINT `conseil_note_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `consultation_conseil`
--
ALTER TABLE `consultation_conseil`
  ADD CONSTRAINT `consultation_conseil_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultation_conseil_ibfk_2` FOREIGN KEY (`id_conseil`) REFERENCES `conseil` (`id_conseil`) ON DELETE CASCADE;

--
-- Contraintes pour la table `contact`
--
ALTER TABLE `contact`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `contrat`
--
ALTER TABLE `contrat`
  ADD CONSTRAINT `contrat_ibfk_1` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`id_devis`) ON DELETE CASCADE,
  ADD CONSTRAINT `contrat_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `contrat_ibfk_3` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`);

--
-- Contraintes pour la table `devis`
--
ALTER TABLE `devis`
  ADD CONSTRAINT `devis_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `devis_ibfk_2` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE SET NULL,
  ADD CONSTRAINT `devis_ibfk_3` FOREIGN KEY (`id_intervention`) REFERENCES `intervention` (`id_intervention`) ON DELETE CASCADE;

--
-- Contraintes pour la table `disponibilite`
--
ALTER TABLE `disponibilite`
  ADD CONSTRAINT `disponibilite_ibfk_1` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE CASCADE;

--
-- Contraintes pour la table `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `fk_document_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `document_txt`
--
ALTER TABLE `document_txt`
  ADD CONSTRAINT `fk_utilisateur_txt` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `intervention_ibfk_3` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `intervention_ibfk_4` FOREIGN KEY (`id_rdv`) REFERENCES `rendez_vous` (`id_rdv`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lien_contact`
--
ALTER TABLE `lien_contact`
  ADD CONSTRAINT `lien_contact_ibfk_1` FOREIGN KEY (`id_user1`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `lien_contact_ibfk_2` FOREIGN KEY (`id_user2`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `lien_contact_state`
--
ALTER TABLE `lien_contact_state`
  ADD CONSTRAINT `lien_contact_state_ibfk_1` FOREIGN KEY (`id_user1`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `lien_contact_state_ibfk_2` FOREIGN KEY (`id_user2`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `reference_service_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `reference_service_ibfk_2` FOREIGN KEY (`id_service`) REFERENCES `service` (`id_service`) ON DELETE CASCADE;

--
-- Contraintes pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  ADD CONSTRAINT `rendez_vous_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `rendez_vous_ibfk_2` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE SET NULL;

--
-- Contraintes pour la table `sanction`
--
ALTER TABLE `sanction`
  ADD CONSTRAINT `fk_sanc_admin` FOREIGN KEY (`par_admin`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sanc_levee` FOREIGN KEY (`levee_par`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sanc_user` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `service`
--
ALTER TABLE `service`
  ADD CONSTRAINT `service_ibfk_1` FOREIGN KEY (`id_prestataire`) REFERENCES `prestataire` (`id_prestataire`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_ibfk_2` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`);

--
-- Contraintes pour la table `souscris_abonnement`
--
ALTER TABLE `souscris_abonnement`
  ADD CONSTRAINT `souscris_abonnement_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `souscris_abonnement_ibfk_2` FOREIGN KEY (`id_abonnement`) REFERENCES `abonnement` (`id_abonnement`);

--
-- Contraintes pour la table `synthese_facture`
--
ALTER TABLE `synthese_facture`
  ADD CONSTRAINT `synthese_facture_ibfk_1` FOREIGN KEY (`id_facture`) REFERENCES `facture_prestataire` (`id_facture`) ON DELETE CASCADE,
  ADD CONSTRAINT `synthese_facture_ibfk_2` FOREIGN KEY (`id_intervention`) REFERENCES `intervention` (`id_intervention`);

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
