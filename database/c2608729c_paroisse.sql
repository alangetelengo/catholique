-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mer. 26 août 2026 à 17:08
-- Version du serveur : 11.4.10-MariaDB-cll-lve
-- Version de PHP : 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `c2608729c_paroisse`
--

-- --------------------------------------------------------

--
-- Structure de la table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('paroisse-cache-config__monnaie', 's:4:\"FCFA\";', 1787744391),
('paroisse-cache-config_1_monnaie', 's:4:\"FCFA\";', 1787574955),
('paroisse-cache-config_1_pdf_header_address', 'N;', 1787574954),
('paroisse-cache-config_1_pdf_header_bg_color', 's:7:\"#003366\";', 1787574955),
('paroisse-cache-config_1_pdf_header_custom_text', 'N;', 1787574955),
('paroisse-cache-config_1_pdf_header_email', 'N;', 1787574955),
('paroisse-cache-config_1_pdf_header_logo', 'N;', 1787574954),
('paroisse-cache-config_1_pdf_header_logo_width', 's:2:\"80\";', 1787574954),
('paroisse-cache-config_1_pdf_header_phone', 'N;', 1787574955),
('paroisse-cache-config_1_pdf_header_show_logo', 'b:1;', 1787574954),
('paroisse-cache-config_1_pdf_header_subtitle', 'N;', 1787574954),
('paroisse-cache-config_1_pdf_header_text_color', 's:7:\"#FFFFFF\";', 1787574955),
('paroisse-cache-config_1_pdf_header_title', 'N;', 1787574954),
('paroisse-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:6:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:18:\"libelle_permission\";s:1:\"d\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";s:1:\"j\";s:12:\"libelle_role\";}s:11:\"permissions\";a:55:{i:0;a:5:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"view_dashboard\";s:1:\"c\";s:23:\"Voir le tableau de bord\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:1;a:5:{s:1:\"a\";i:2;s:1:\"b\";s:12:\"view_members\";s:1:\"c\";s:16:\"Voir les membres\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:2;a:5:{s:1:\"a\";i:3;s:1:\"b\";s:14:\"create_members\";s:1:\"c\";s:18:\"Créer des membres\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:3;a:5:{s:1:\"a\";i:4;s:1:\"b\";s:12:\"edit_members\";s:1:\"c\";s:20:\"Modifier les membres\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:4;a:5:{s:1:\"a\";i:5;s:1:\"b\";s:14:\"delete_members\";s:1:\"c\";s:21:\"Supprimer des membres\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:5;a:5:{s:1:\"a\";i:6;s:1:\"b\";s:14:\"export_members\";s:1:\"c\";s:20:\"Exporter les membres\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:6;a:5:{s:1:\"a\";i:7;s:1:\"b\";s:14:\"import_members\";s:1:\"c\";s:20:\"Importer les membres\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:5:{s:1:\"a\";i:8;s:1:\"b\";s:13:\"view_baptisms\";s:1:\"c\";s:18:\"Voir les baptêmes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:8;a:5:{s:1:\"a\";i:9;s:1:\"b\";s:15:\"create_baptisms\";s:1:\"c\";s:20:\"Créer des baptêmes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:9;a:5:{s:1:\"a\";i:10;s:1:\"b\";s:13:\"edit_baptisms\";s:1:\"c\";s:22:\"Modifier les baptêmes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:10;a:5:{s:1:\"a\";i:11;s:1:\"b\";s:15:\"delete_baptisms\";s:1:\"c\";s:23:\"Supprimer des baptêmes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:5:{s:1:\"a\";i:12;s:1:\"b\";s:18:\"view_confirmations\";s:1:\"c\";s:22:\"Voir les confirmations\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:12;a:5:{s:1:\"a\";i:13;s:1:\"b\";s:20:\"create_confirmations\";s:1:\"c\";s:24:\"Créer des confirmations\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:13;a:5:{s:1:\"a\";i:14;s:1:\"b\";s:18:\"edit_confirmations\";s:1:\"c\";s:26:\"Modifier les confirmations\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:14;a:5:{s:1:\"a\";i:15;s:1:\"b\";s:20:\"delete_confirmations\";s:1:\"c\";s:27:\"Supprimer des confirmations\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:15;a:5:{s:1:\"a\";i:16;s:1:\"b\";s:15:\"view_communions\";s:1:\"c\";s:19:\"Voir les communions\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:16;a:5:{s:1:\"a\";i:17;s:1:\"b\";s:17:\"create_communions\";s:1:\"c\";s:21:\"Créer des communions\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:17;a:5:{s:1:\"a\";i:18;s:1:\"b\";s:15:\"edit_communions\";s:1:\"c\";s:23:\"Modifier les communions\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:18;a:5:{s:1:\"a\";i:19;s:1:\"b\";s:17:\"delete_communions\";s:1:\"c\";s:24:\"Supprimer des communions\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:19;a:5:{s:1:\"a\";i:20;s:1:\"b\";s:14:\"view_marriages\";s:1:\"c\";s:17:\"Voir les mariages\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:20;a:5:{s:1:\"a\";i:21;s:1:\"b\";s:16:\"create_marriages\";s:1:\"c\";s:19:\"Créer des mariages\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:21;a:5:{s:1:\"a\";i:22;s:1:\"b\";s:14:\"edit_marriages\";s:1:\"c\";s:21:\"Modifier les mariages\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:22;a:5:{s:1:\"a\";i:23;s:1:\"b\";s:16:\"delete_marriages\";s:1:\"c\";s:22:\"Supprimer des mariages\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:23;a:5:{s:1:\"a\";i:24;s:1:\"b\";s:13:\"view_funerals\";s:1:\"c\";s:18:\"Voir les obsèques\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:24;a:5:{s:1:\"a\";i:25;s:1:\"b\";s:15:\"create_funerals\";s:1:\"c\";s:20:\"Créer des obsèques\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:25;a:5:{s:1:\"a\";i:26;s:1:\"b\";s:13:\"edit_funerals\";s:1:\"c\";s:22:\"Modifier les obsèques\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:26;a:5:{s:1:\"a\";i:27;s:1:\"b\";s:15:\"delete_funerals\";s:1:\"c\";s:23:\"Supprimer des obsèques\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:27;a:5:{s:1:\"a\";i:28;s:1:\"b\";s:11:\"view_events\";s:1:\"c\";s:21:\"Voir les événements\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:28;a:5:{s:1:\"a\";i:29;s:1:\"b\";s:13:\"create_events\";s:1:\"c\";s:23:\"Créer des événements\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:29;a:5:{s:1:\"a\";i:30;s:1:\"b\";s:11:\"edit_events\";s:1:\"c\";s:25:\"Modifier les événements\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:30;a:5:{s:1:\"a\";i:31;s:1:\"b\";s:13:\"delete_events\";s:1:\"c\";s:26:\"Supprimer des événements\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:31;a:5:{s:1:\"a\";i:32;s:1:\"b\";s:25:\"manage_event_participants\";s:1:\"c\";s:40:\"Gérer les participants aux événements\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:32;a:5:{s:1:\"a\";i:33;s:1:\"b\";s:11:\"view_groups\";s:1:\"c\";s:16:\"Voir les groupes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:33;a:5:{s:1:\"a\";i:34;s:1:\"b\";s:13:\"create_groups\";s:1:\"c\";s:18:\"Créer des groupes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:34;a:5:{s:1:\"a\";i:35;s:1:\"b\";s:11:\"edit_groups\";s:1:\"c\";s:20:\"Modifier les groupes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:35;a:5:{s:1:\"a\";i:36;s:1:\"b\";s:13:\"delete_groups\";s:1:\"c\";s:21:\"Supprimer des groupes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:36;a:5:{s:1:\"a\";i:37;s:1:\"b\";s:20:\"manage_group_members\";s:1:\"c\";s:30:\"Gérer les membres des groupes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:37;a:5:{s:1:\"a\";i:38;s:1:\"b\";s:13:\"view_revenues\";s:1:\"c\";s:17:\"Voir les recettes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:38;a:5:{s:1:\"a\";i:39;s:1:\"b\";s:15:\"create_revenues\";s:1:\"c\";s:19:\"Créer des recettes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:39;a:5:{s:1:\"a\";i:40;s:1:\"b\";s:13:\"edit_revenues\";s:1:\"c\";s:21:\"Modifier les recettes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:40;a:5:{s:1:\"a\";i:41;s:1:\"b\";s:15:\"delete_revenues\";s:1:\"c\";s:22:\"Supprimer des recettes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:41;a:5:{s:1:\"a\";i:42;s:1:\"b\";s:17:\"validate_revenues\";s:1:\"c\";s:20:\"Valider les recettes\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:42;a:5:{s:1:\"a\";i:43;s:1:\"b\";s:13:\"view_expenses\";s:1:\"c\";s:18:\"Voir les dépenses\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:43;a:5:{s:1:\"a\";i:44;s:1:\"b\";s:15:\"create_expenses\";s:1:\"c\";s:20:\"Créer des dépenses\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:44;a:5:{s:1:\"a\";i:45;s:1:\"b\";s:13:\"edit_expenses\";s:1:\"c\";s:22:\"Modifier les dépenses\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:45;a:5:{s:1:\"a\";i:46;s:1:\"b\";s:15:\"delete_expenses\";s:1:\"c\";s:23:\"Supprimer des dépenses\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:46;a:5:{s:1:\"a\";i:47;s:1:\"b\";s:17:\"validate_expenses\";s:1:\"c\";s:21:\"Valider les dépenses\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:47;a:5:{s:1:\"a\";i:48;s:1:\"b\";s:22:\"view_financial_reports\";s:1:\"c\";s:28:\"Voir les rapports financiers\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:48;a:5:{s:1:\"a\";i:49;s:1:\"b\";s:26:\"generate_financial_reports\";s:1:\"c\";s:33:\"Générer des rapports financiers\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:49;a:5:{s:1:\"a\";i:50;s:1:\"b\";s:18:\"view_configuration\";s:1:\"c\";s:21:\"Voir la configuration\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:50;a:5:{s:1:\"a\";i:51;s:1:\"b\";s:18:\"edit_configuration\";s:1:\"c\";s:25:\"Modifier la configuration\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:51;a:5:{s:1:\"a\";i:52;s:1:\"b\";s:12:\"manage_users\";s:1:\"c\";s:23:\"Gérer les utilisateurs\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:52;a:5:{s:1:\"a\";i:53;s:1:\"b\";s:12:\"manage_roles\";s:1:\"c\";s:17:\"Gérer les rôles\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:53;a:5:{s:1:\"a\";i:54;s:1:\"b\";s:18:\"manage_permissions\";s:1:\"c\";s:22:\"Gérer les permissions\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:54;a:5:{s:1:\"a\";i:55;s:1:\"b\";s:16:\"manage_paroisses\";s:1:\"c\";s:20:\"Gérer les paroisses\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}}s:5:\"roles\";a:5:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super_admin\";s:1:\"j\";s:20:\"Super administrateur\";s:1:\"d\";s:3:\"web\";}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:14:\"paroisse_admin\";s:1:\"j\";s:26:\"Administrateur de paroisse\";s:1:\"d\";s:3:\"web\";}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:19:\"paroisse_secretaire\";s:1:\"j\";s:23:\"Secrétaire de paroisse\";s:1:\"d\";s:3:\"web\";}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:18:\"paroisse_tresorier\";s:1:\"j\";s:22:\"Trésorier de paroisse\";s:1:\"d\";s:3:\"web\";}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:16:\"paroisse_lecteur\";s:1:\"j\";s:22:\"Lecteur (consultation)\";s:1:\"d\";s:3:\"web\";}}}', 1787823196);

-- --------------------------------------------------------

--
-- Structure de la table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `configurations`
--

CREATE TABLE `configurations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paroisse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cle` varchar(255) NOT NULL,
  `valeur` text DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `events`
--

CREATE TABLE `events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paroisse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `titre` varchar(255) NOT NULL,
  `type` enum('messe','célébration','activité') NOT NULL DEFAULT 'activité',
  `date_evenement` date NOT NULL,
  `heure_evenement` time DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `celebre_par_id` bigint(20) UNSIGNED DEFAULT NULL,
  `intention` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `event_member`
--

CREATE TABLE `event_member` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `evenement_id` bigint(20) UNSIGNED NOT NULL,
  `membre_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paroisse_id` bigint(20) UNSIGNED NOT NULL,
  `revenue_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `expense_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `revenue_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_depense` date NOT NULL,
  `jour_semaine` varchar(20) DEFAULT NULL,
  `facture_reference` varchar(255) DEFAULT NULL,
  `piece_facture_path` varchar(255) DEFAULT NULL,
  `piece_recu_path` varchar(255) DEFAULT NULL,
  `piece_autre_path` varchar(255) DEFAULT NULL,
  `fournisseur` varchar(255) DEFAULT NULL,
  `methode_paiement` enum('especes','cheque','virement','carte','mobile_money') NOT NULL DEFAULT 'especes',
  `statut` enum('en_attente','valide','rejete') NOT NULL DEFAULT 'valide',
  `notes` text DEFAULT NULL,
  `libelle` varchar(500) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `validated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `expenses`
--

INSERT INTO `expenses` (`id`, `paroisse_id`, `revenue_category_id`, `expense_type_id`, `revenue_type_id`, `montant`, `date_depense`, `jour_semaine`, `facture_reference`, `piece_facture_path`, `piece_recu_path`, `piece_autre_path`, `fournisseur`, `methode_paiement`, `statut`, `notes`, `libelle`, `created_by`, `validated_by`, `validated_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 4, 6, NULL, 220000.00, '2026-08-05', 'mercredi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'salaire + congés maman Denise et sa fille', 3, NULL, NULL, '2026-08-19 13:10:46', '2026-08-19 13:10:46', NULL),
(2, 1, 4, 6, NULL, 50000.00, '2026-08-05', 'mercredi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'abonnement gpe electrogène', 3, NULL, NULL, '2026-08-19 13:12:58', '2026-08-19 13:12:58', NULL),
(3, 1, 4, 6, NULL, 70000.00, '2026-08-06', 'jeudi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', 'popote curé vacances', 'Popote curé', 3, NULL, NULL, '2026-08-19 13:21:07', '2026-08-19 13:21:07', NULL),
(4, 1, 4, 6, NULL, 70000.00, '2026-08-08', 'samedi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', 'Popote Abbé Abraham vacances', 'popote abbé Abraham', 3, NULL, NULL, '2026-08-19 13:23:04', '2026-08-19 13:23:04', NULL),
(5, 1, 4, 6, NULL, 150000.00, '2026-08-04', 'mardi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', 'Avance provision', 'Provision popote', 3, NULL, NULL, '2026-08-19 14:03:04', '2026-08-19 14:03:04', NULL),
(6, 1, 4, 6, NULL, 60000.00, '2026-08-09', 'dimanche', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', 'Abbé Arnaud Congés', 'popote abbé Arnaud', 3, NULL, NULL, '2026-08-19 14:04:26', '2026-08-19 14:04:26', NULL),
(7, 1, 4, 6, NULL, 55000.00, '2026-08-11', 'mardi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', 'abbé tisset Congés', 'Abbé Tisset', 3, NULL, NULL, '2026-08-19 14:15:34', '2026-08-19 14:15:34', NULL),
(8, 1, 4, 6, NULL, 75000.00, '2026-08-10', 'lundi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', 'Abbé Junior Congés + Anniversaire', 'Abbé Junior', 3, NULL, NULL, '2026-08-19 14:17:19', '2026-08-19 14:17:19', NULL),
(9, 1, 4, 6, NULL, 109000.00, '2026-08-12', 'mercredi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', 'Carburant', 'Carburant', 3, NULL, NULL, '2026-08-19 14:18:35', '2026-08-19 14:18:35', NULL),
(10, 1, 4, 6, NULL, 12000.00, '2026-08-12', 'mercredi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', 'Charbon Artificiel', 'Charbon articificiel', 3, NULL, NULL, '2026-08-19 14:24:08', '2026-08-19 14:24:08', NULL),
(11, 1, 4, 6, NULL, 20000.00, '2026-08-12', 'mercredi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', 'Hosties', 'Achat Hostie', 3, NULL, NULL, '2026-08-19 14:25:45', '2026-08-19 14:25:45', NULL),
(12, 1, 4, 6, NULL, 5000.00, '2026-08-13', 'jeudi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Eau Minérale', 3, NULL, NULL, '2026-08-19 14:27:26', '2026-08-19 14:27:26', NULL),
(13, 1, 4, 6, NULL, 25000.00, '2026-08-12', 'mercredi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', 'Stabilisateur sacristie', 'maintenace stabilsateur', 3, NULL, NULL, '2026-08-19 14:29:30', '2026-08-19 14:29:30', NULL),
(14, 1, 4, 6, NULL, 5000.00, '2026-08-12', 'mercredi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', 'Petit déjeuner', 'Petit déjeuner', 3, NULL, NULL, '2026-08-19 14:30:27', '2026-08-19 14:30:27', NULL),
(15, 1, 4, 6, NULL, 3000.00, '2026-08-12', 'mercredi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Repas', 3, NULL, NULL, '2026-08-19 14:33:28', '2026-08-19 14:33:28', NULL),
(16, 1, 4, 6, NULL, 3000.00, '2026-08-13', 'jeudi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Petit déjeuner', 3, NULL, NULL, '2026-08-19 14:35:39', '2026-08-19 14:35:39', NULL),
(17, 1, 4, 6, NULL, 17500.00, '2026-08-13', 'jeudi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', 'Changement de serrure', 'Clé  Maman Yoyo', 3, NULL, NULL, '2026-08-19 14:37:29', '2026-08-19 14:37:29', NULL),
(18, 1, 4, 6, NULL, 3000.00, '2026-08-13', 'jeudi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Repas', 3, NULL, NULL, '2026-08-19 14:39:03', '2026-08-19 14:39:03', NULL),
(19, 1, 4, 6, NULL, 4000.00, '2026-08-14', 'vendredi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Petit déjeuner', 3, NULL, NULL, '2026-08-19 14:40:07', '2026-08-19 14:40:07', NULL),
(20, 1, 4, 6, NULL, 18000.00, '2026-08-14', 'vendredi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Plomberie', 3, NULL, NULL, '2026-08-19 14:40:55', '2026-08-19 14:40:55', NULL),
(21, 1, 4, 6, NULL, 7000.00, '2026-08-15', 'samedi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Eau Javel', 3, NULL, NULL, '2026-08-19 14:42:18', '2026-08-19 14:42:18', NULL),
(22, 1, 4, 6, NULL, 8500.00, '2026-08-15', 'samedi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Repas', 3, NULL, NULL, '2026-08-19 14:43:33', '2026-08-19 14:43:33', NULL),
(23, 1, 4, 6, NULL, 2000.00, '2026-08-15', 'samedi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Petit déjeuner', 3, NULL, NULL, '2026-08-19 14:44:45', '2026-08-19 14:44:45', NULL),
(24, 1, 4, 6, NULL, 65000.00, '2026-08-17', 'lundi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Carburant curé', 3, NULL, NULL, '2026-08-19 14:49:42', '2026-08-19 14:49:42', NULL),
(25, 1, 4, 6, NULL, 34000.00, '2026-08-17', 'lundi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Carburant paroisse', 3, NULL, NULL, '2026-08-19 14:50:25', '2026-08-19 14:50:25', NULL),
(26, 1, 4, 6, NULL, 6000.00, '2026-08-17', 'lundi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Repas', 3, NULL, NULL, '2026-08-19 14:51:51', '2026-08-19 14:51:51', NULL),
(27, 1, 4, 6, NULL, 3000.00, '2026-08-17', 'lundi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Charbon + Encens', 3, NULL, NULL, '2026-08-19 14:53:06', '2026-08-19 14:53:06', NULL),
(28, 1, 4, 6, NULL, 40000.00, '2026-08-17', 'lundi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Popote Abbé Deo', 3, NULL, NULL, '2026-08-19 14:54:03', '2026-08-19 14:54:03', NULL),
(29, 1, 4, 6, NULL, 2000.00, '2026-08-18', 'mardi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Savon Liquide', 3, NULL, NULL, '2026-08-19 14:55:09', '2026-08-19 14:55:09', NULL),
(30, 1, 4, 6, NULL, 34000.00, '2026-08-18', 'mardi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Carburant paroisse', 3, NULL, NULL, '2026-08-19 14:56:39', '2026-08-19 14:56:39', NULL),
(31, 1, 4, 6, NULL, 2000.00, '2026-08-16', 'dimanche', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Décapsileur', 3, NULL, NULL, '2026-08-19 14:57:51', '2026-08-19 14:57:51', NULL),
(32, 1, 4, 6, NULL, 3000.00, '2026-08-17', 'lundi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Déplacement', 3, NULL, NULL, '2026-08-19 14:58:58', '2026-08-19 14:58:58', NULL),
(33, 1, 4, 6, NULL, 3000.00, '2026-08-18', 'mardi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Petit déjeuner', 3, NULL, NULL, '2026-08-19 15:00:10', '2026-08-19 15:00:10', NULL),
(34, 1, 4, 6, NULL, 5000.00, '2026-08-17', 'lundi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Repas', 3, NULL, NULL, '2026-08-19 15:01:36', '2026-08-19 15:01:36', NULL),
(35, 1, 4, 6, NULL, 5000.00, '2026-08-17', 'lundi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Déplacement Conseil', 3, NULL, NULL, '2026-08-19 15:04:25', '2026-08-19 15:04:25', NULL),
(36, 1, 4, 6, NULL, 3000.00, '2026-08-18', 'mardi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Petit déjeuner', 3, NULL, NULL, '2026-08-19 15:05:57', '2026-08-19 15:14:59', '2026-08-19 15:14:59'),
(37, 1, 4, 6, NULL, 150000.00, '2026-08-18', 'mardi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Gardiennage', 3, NULL, NULL, '2026-08-19 15:07:43', '2026-08-19 15:07:43', NULL),
(38, 1, 4, 6, NULL, 5000.00, '2026-08-19', 'mercredi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Ampoule', 3, NULL, NULL, '2026-08-19 15:08:27', '2026-08-19 15:08:27', NULL),
(39, 1, 4, 1, NULL, 5000.00, '2026-08-01', 'samedi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Petit déjeuné', 3, NULL, NULL, '2026-08-25 06:46:59', '2026-08-25 06:46:59', NULL),
(40, 1, 4, 1, NULL, 7000.00, '2026-08-01', 'samedi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Repas', 3, NULL, NULL, '2026-08-25 06:48:19', '2026-08-25 06:48:19', NULL),
(41, 1, 4, 1, NULL, 5000.00, '2026-08-03', 'lundi', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Petit déjeuné', 3, NULL, NULL, '2026-08-25 06:50:59', '2026-08-25 06:50:59', NULL),
(42, 1, 4, 1, NULL, 5000.00, '2026-08-02', 'dimanche', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Petit déjeuné', 3, NULL, NULL, '2026-08-25 06:53:07', '2026-08-25 06:53:07', NULL),
(43, 1, 4, 1, NULL, 5000.00, '2026-08-02', 'dimanche', NULL, NULL, NULL, NULL, NULL, 'especes', 'valide', NULL, 'Repas', 3, NULL, NULL, '2026-08-25 06:57:57', '2026-08-25 06:57:57', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `expense_funding_sources`
--

CREATE TABLE `expense_funding_sources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `expense_id` bigint(20) UNSIGNED NOT NULL,
  `revenue_type_id` bigint(20) UNSIGNED NOT NULL,
  `revenue_id` bigint(20) UNSIGNED DEFAULT NULL,
  `montant_alloue` decimal(15,2) NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `expense_funding_sources`
--

INSERT INTO `expense_funding_sources` (`id`, `expense_id`, `revenue_type_id`, `revenue_id`, `montant_alloue`, `ordre`, `created_at`, `updated_at`) VALUES
(1, 1, 22, 24, 220000.00, 1, '2026-08-19 13:10:46', '2026-08-19 13:10:46'),
(2, 2, 22, 24, 50000.00, 1, '2026-08-19 13:12:58', '2026-08-19 13:12:58'),
(3, 3, 22, 24, 70000.00, 1, '2026-08-19 13:21:07', '2026-08-19 13:21:07'),
(4, 4, 22, 24, 70000.00, 1, '2026-08-19 13:23:04', '2026-08-19 13:23:04'),
(5, 5, 22, 24, 150000.00, 1, '2026-08-19 14:03:04', '2026-08-19 14:03:04'),
(6, 6, 22, 24, 60000.00, 1, '2026-08-19 14:04:26', '2026-08-19 14:04:26'),
(7, 7, 22, 24, 55000.00, 1, '2026-08-19 14:15:34', '2026-08-19 14:15:34'),
(8, 8, 22, 24, 75000.00, 1, '2026-08-19 14:17:19', '2026-08-19 14:17:19'),
(9, 9, 22, 24, 109000.00, 1, '2026-08-19 14:18:35', '2026-08-19 14:18:35'),
(10, 10, 22, 24, 12000.00, 1, '2026-08-19 14:24:08', '2026-08-19 14:24:08'),
(11, 11, 22, 24, 20000.00, 1, '2026-08-19 14:25:45', '2026-08-19 14:25:45'),
(12, 12, 22, 24, 5000.00, 1, '2026-08-19 14:27:26', '2026-08-19 14:27:26'),
(13, 13, 22, 24, 25000.00, 1, '2026-08-19 14:29:30', '2026-08-19 14:29:30'),
(14, 14, 22, 24, 5000.00, 1, '2026-08-19 14:30:27', '2026-08-19 14:30:27'),
(15, 15, 22, 24, 3000.00, 1, '2026-08-19 14:33:28', '2026-08-19 14:33:28'),
(16, 16, 22, 24, 3000.00, 1, '2026-08-19 14:35:39', '2026-08-19 14:35:39'),
(17, 17, 22, 24, 17500.00, 1, '2026-08-19 14:37:29', '2026-08-19 14:37:29'),
(18, 18, 22, 24, 3000.00, 1, '2026-08-19 14:39:03', '2026-08-19 14:39:03'),
(19, 19, 22, 24, 4000.00, 1, '2026-08-19 14:40:07', '2026-08-19 14:40:07'),
(20, 20, 22, 24, 18000.00, 1, '2026-08-19 14:40:55', '2026-08-19 14:40:55'),
(21, 21, 22, 24, 7000.00, 1, '2026-08-19 14:42:18', '2026-08-19 14:42:18'),
(22, 22, 22, 24, 8500.00, 1, '2026-08-19 14:43:33', '2026-08-19 14:43:33'),
(23, 23, 22, 24, 2000.00, 1, '2026-08-19 14:44:45', '2026-08-19 14:44:45'),
(24, 24, 22, 24, 65000.00, 1, '2026-08-19 14:49:42', '2026-08-19 14:49:42'),
(25, 25, 22, 24, 34000.00, 1, '2026-08-19 14:50:25', '2026-08-19 14:50:25'),
(26, 26, 22, 24, 6000.00, 1, '2026-08-19 14:51:51', '2026-08-19 14:51:51'),
(27, 27, 22, 24, 3000.00, 1, '2026-08-19 14:53:06', '2026-08-19 14:53:06'),
(28, 28, 22, 24, 40000.00, 1, '2026-08-19 14:54:03', '2026-08-19 14:54:03'),
(29, 29, 22, 24, 2000.00, 1, '2026-08-19 14:55:09', '2026-08-19 14:55:09'),
(30, 30, 22, 24, 34000.00, 1, '2026-08-19 14:56:39', '2026-08-19 14:56:39'),
(31, 31, 22, 24, 2000.00, 1, '2026-08-19 14:57:51', '2026-08-19 14:57:51'),
(32, 32, 22, 24, 3000.00, 1, '2026-08-19 14:58:58', '2026-08-19 14:58:58'),
(33, 33, 22, 24, 3000.00, 1, '2026-08-19 15:00:10', '2026-08-19 15:00:10'),
(34, 34, 22, 24, 5000.00, 1, '2026-08-19 15:01:36', '2026-08-19 15:01:36'),
(35, 35, 22, 24, 5000.00, 1, '2026-08-19 15:04:25', '2026-08-19 15:04:25'),
(36, 36, 22, 24, 3000.00, 1, '2026-08-19 15:05:57', '2026-08-19 15:05:57'),
(37, 37, 22, 24, 150000.00, 1, '2026-08-19 15:07:43', '2026-08-19 15:07:43'),
(38, 38, 22, 24, 5000.00, 1, '2026-08-19 15:08:27', '2026-08-19 15:08:27'),
(39, 39, 22, 24, 5000.00, 1, '2026-08-25 06:46:59', '2026-08-25 06:46:59'),
(40, 40, 22, 24, 7000.00, 1, '2026-08-25 06:48:19', '2026-08-25 06:48:19'),
(41, 41, 22, 24, 5000.00, 1, '2026-08-25 06:50:59', '2026-08-25 06:50:59'),
(42, 42, 22, 24, 5000.00, 1, '2026-08-25 06:53:07', '2026-08-25 06:53:07'),
(43, 43, 22, 24, 5000.00, 1, '2026-08-25 06:57:57', '2026-08-25 06:57:57');

-- --------------------------------------------------------

--
-- Structure de la table `expense_types`
--

CREATE TABLE `expense_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `ordre` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `expense_types`
--

INSERT INTO `expense_types` (`id`, `code`, `nom`, `description`, `actif`, `ordre`, `created_at`, `updated_at`) VALUES
(1, 'alimentation_popote', 'Alimentation / popote', 'Achats alimentaires et frais de popote', 1, 1, '2026-08-20 07:36:53', '2026-08-20 07:36:53'),
(2, 'salaires', 'Salaires', 'Rémunérations et charges salariales', 1, 2, '2026-08-20 07:36:53', '2026-08-20 07:36:53'),
(3, 'carburant', 'Carburant', 'Carburant et frais de déplacement liés', 1, 3, '2026-08-20 07:36:53', '2026-08-20 07:36:53'),
(4, 'entretien_reparations', 'Entretien / réparations', 'Entretien des locaux, matériel et réparations', 1, 4, '2026-08-20 07:36:53', '2026-08-20 07:36:53'),
(5, 'factures', 'Factures (eau, électricité, internet…)', 'Factures et abonnements (eau, électricité, internet, gaz…)', 1, 5, '2026-08-20 07:36:53', '2026-08-20 07:36:53'),
(6, 'autre', 'Autre', 'Toute autre dépense non classée', 1, 99, '2026-08-20 07:36:53', '2026-08-20 07:36:53');

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `financial_reports`
--

CREATE TABLE `financial_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paroisse_id` bigint(20) UNSIGNED NOT NULL,
  `periode_type` enum('semaine','dimanche','total','revenues_by_category','charges_fixes','popote_subvention') NOT NULL DEFAULT 'total',
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `total_recettes` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_depenses` decimal(10,2) NOT NULL DEFAULT 0.00,
  `solde` decimal(10,2) NOT NULL DEFAULT 0.00,
  `details_recettes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details_recettes`)),
  `details_depenses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details_depenses`)),
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groups`
--

CREATE TABLE `groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paroisse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nom` varchar(255) NOT NULL,
  `type` enum('chorale','catéchisme','mouvement','autre') NOT NULL DEFAULT 'autre',
  `responsable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `group_member`
--

CREATE TABLE `group_member` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `groupe_id` bigint(20) UNSIGNED NOT NULL,
  `membre_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inventaire_magasin`
--

CREATE TABLE `inventaire_magasin` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paroisse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nom` varchar(255) NOT NULL,
  `categorie` varchar(255) DEFAULT NULL,
  `unite` varchar(50) NOT NULL DEFAULT 'unité',
  `quantite` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantite_min_alerte` decimal(12,2) DEFAULT NULL,
  `date_peremption` date DEFAULT NULL,
  `emplacement` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inventaire_patrimoine`
--

CREATE TABLE `inventaire_patrimoine` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paroisse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nom` varchar(255) NOT NULL,
  `categorie` varchar(255) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `valeur_estimee` decimal(14,2) DEFAULT NULL,
  `date_acquisition` date DEFAULT NULL,
  `etat` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inventories`
--

CREATE TABLE `inventories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paroisse_id` bigint(20) UNSIGNED NOT NULL,
  `designation` varchar(255) NOT NULL,
  `categorie` varchar(64) NOT NULL DEFAULT 'autre',
  `reference_inventaire` varchar(128) DEFAULT NULL,
  `quantite` decimal(12,2) NOT NULL DEFAULT 1.00,
  `unite` varchar(64) NOT NULL DEFAULT 'unité',
  `emplacement` varchar(255) DEFAULT NULL,
  `etat` varchar(32) NOT NULL DEFAULT 'bon',
  `date_acquisition` date DEFAULT NULL,
  `valeur_estimee` decimal(14,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `members`
--

CREATE TABLE `members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paroisse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `prenom` varchar(255) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `sexe` enum('M','F') NOT NULL DEFAULT 'M',
  `adresse` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `statut` enum('actif','inactif','décédé') NOT NULL DEFAULT 'actif',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_28_033055_create_permission_tables', 1),
(5, '2026_01_28_034935_create_members_table', 1),
(6, '2026_01_28_034945_create_paroisses_table', 1),
(7, '2026_01_28_034953_add_foreign_key_cure_to_paroisses_table', 1),
(8, '2026_01_28_035000_create_configurations_table', 1),
(9, '2026_01_28_035008_add_paroisse_id_to_users_table', 1),
(10, '2026_01_28_035056_add_paroisse_id_to_members_table', 1),
(11, '2026_01_28_083647_add_username_to_users_table', 1),
(12, '2026_01_28_092753_create_groups_table', 1),
(13, '2026_01_28_092754_create_group_member_table', 1),
(14, '2026_01_28_093546_add_sexe_to_members_table', 1),
(15, '2026_01_28_093548_create_events_table', 1),
(16, '2026_01_28_093549_add_paroisse_id_to_events_table', 1),
(17, '2026_01_28_093550_create_event_member_table', 1),
(18, '2026_01_28_112648_add_labels_to_roles_and_permissions_tables', 1),
(19, '2026_01_28_120000_create_financial_management_tables', 1),
(20, '2026_01_28_152153_add_attachments_to_expenses_table', 1),
(21, '2026_01_28_160000_add_paroisse_id_to_revenue_categories_and_types', 1),
(22, '2026_01_29_120000_add_paroisse_id_to_groups_table', 1),
(23, '2026_01_29_140000_create_sacraments_table', 1),
(24, '2026_01_29_150000_add_mois_location_to_revenues_table', 1),
(25, '2026_01_29_160000_add_alimentation_popote_to_expenses', 1),
(26, '2026_02_13_100000_add_donateur_to_revenues_table', 1),
(27, '2026_03_09_100000_create_inventaire_tables', 1),
(28, '2026_03_10_100000_add_revenues_by_category_to_financial_reports', 1),
(29, '2026_04_01_140000_add_soft_deletes_to_revenues_and_expenses', 1),
(30, '2026_04_01_170000_add_soft_deletes_to_financial_reports', 1),
(31, '2026_04_01_180000_add_charges_fixes_to_financial_reports_enum', 1),
(32, '2026_04_01_190000_add_popote_subvention_to_financial_reports_enum', 1),
(33, '2026_04_01_210000_create_inventories_table', 1),
(34, '2026_04_15_120000_extend_expenses_type_charge_enum', 1),
(35, '2026_04_15_160000_note_revenue_type_id_in_financial_report_details', 1),
(36, '2026_05_08_094353_remove_old_expense_columns_from_expenses_table', 1),
(37, '2026_05_08_094628_add_revenue_fields_to_expenses_table', 1),
(38, '2026_05_09_193229_add_piece_autre_path_to_expenses_table', 1),
(39, '2026_05_09_210036_create_expense_funding_sources_table', 1),
(40, '2026_05_09_211254_migrate_existing_expenses_to_funding_sources', 1),
(41, '2026_06_11_045544_add_mois_subvention_to_revenues_and_revenue_id_to_expense_funding_sources', 1),
(42, '2026_08_20_082917_create_expense_types_table', 2),
(43, '2026_08_20_082918_add_expense_type_id_to_expenses_table', 2);

-- --------------------------------------------------------

--
-- Structure de la table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(4, 'App\\Models\\User', 3);

-- --------------------------------------------------------

--
-- Structure de la table `paroisses`
--

CREATE TABLE `paroisses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `pays` varchar(255) NOT NULL DEFAULT 'République du Congo',
  `telephone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `code_paroisse` varchar(255) DEFAULT NULL,
  `curé_id` bigint(20) UNSIGNED DEFAULT NULL,
  `diocèse` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `paroisses`
--

INSERT INTO `paroisses` (`id`, `nom`, `adresse`, `ville`, `pays`, `telephone`, `email`, `code_paroisse`, `curé_id`, `diocèse`, `description`, `actif`, `created_at`, `updated_at`) VALUES
(1, 'SAINT-ESPRIT DE MOUNGALI', 'Avenue de la Paix, Moungali', 'Brazzaville', 'République du Congo', '+242 06 XXX XX XX', 'contact@saint-esprit-moungali.cg', 'SEM001', NULL, 'Archidiocèse de Brazzaville', 'Paroisse de référence pour les tests', 1, '2026-06-28 20:06:55', '2026-06-28 20:06:55');

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `libelle_permission` varchar(255) DEFAULT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `libelle_permission`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view_dashboard', 'Voir le tableau de bord', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(2, 'view_members', 'Voir les membres', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(3, 'create_members', 'Créer des membres', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(4, 'edit_members', 'Modifier les membres', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(5, 'delete_members', 'Supprimer des membres', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(6, 'export_members', 'Exporter les membres', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(7, 'import_members', 'Importer les membres', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(8, 'view_baptisms', 'Voir les baptêmes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(9, 'create_baptisms', 'Créer des baptêmes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(10, 'edit_baptisms', 'Modifier les baptêmes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(11, 'delete_baptisms', 'Supprimer des baptêmes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(12, 'view_confirmations', 'Voir les confirmations', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(13, 'create_confirmations', 'Créer des confirmations', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(14, 'edit_confirmations', 'Modifier les confirmations', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(15, 'delete_confirmations', 'Supprimer des confirmations', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(16, 'view_communions', 'Voir les communions', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(17, 'create_communions', 'Créer des communions', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(18, 'edit_communions', 'Modifier les communions', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(19, 'delete_communions', 'Supprimer des communions', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(20, 'view_marriages', 'Voir les mariages', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(21, 'create_marriages', 'Créer des mariages', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(22, 'edit_marriages', 'Modifier les mariages', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(23, 'delete_marriages', 'Supprimer des mariages', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(24, 'view_funerals', 'Voir les obsèques', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(25, 'create_funerals', 'Créer des obsèques', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(26, 'edit_funerals', 'Modifier les obsèques', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(27, 'delete_funerals', 'Supprimer des obsèques', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(28, 'view_events', 'Voir les événements', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(29, 'create_events', 'Créer des événements', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(30, 'edit_events', 'Modifier les événements', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(31, 'delete_events', 'Supprimer des événements', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(32, 'manage_event_participants', 'Gérer les participants aux événements', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(33, 'view_groups', 'Voir les groupes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(34, 'create_groups', 'Créer des groupes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(35, 'edit_groups', 'Modifier les groupes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(36, 'delete_groups', 'Supprimer des groupes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(37, 'manage_group_members', 'Gérer les membres des groupes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(38, 'view_revenues', 'Voir les recettes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(39, 'create_revenues', 'Créer des recettes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(40, 'edit_revenues', 'Modifier les recettes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(41, 'delete_revenues', 'Supprimer des recettes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(42, 'validate_revenues', 'Valider les recettes', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(43, 'view_expenses', 'Voir les dépenses', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(44, 'create_expenses', 'Créer des dépenses', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(45, 'edit_expenses', 'Modifier les dépenses', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(46, 'delete_expenses', 'Supprimer des dépenses', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(47, 'validate_expenses', 'Valider les dépenses', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(48, 'view_financial_reports', 'Voir les rapports financiers', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(49, 'generate_financial_reports', 'Générer des rapports financiers', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(50, 'view_configuration', 'Voir la configuration', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(51, 'edit_configuration', 'Modifier la configuration', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(52, 'manage_users', 'Gérer les utilisateurs', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(53, 'manage_roles', 'Gérer les rôles', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(54, 'manage_permissions', 'Gérer les permissions', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(55, 'manage_paroisses', 'Gérer les paroisses', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55');

-- --------------------------------------------------------

--
-- Structure de la table `revenues`
--

CREATE TABLE `revenues` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paroisse_id` bigint(20) UNSIGNED NOT NULL,
  `revenue_category_id` bigint(20) UNSIGNED NOT NULL,
  `revenue_type_id` bigint(20) UNSIGNED NOT NULL,
  `periode_messe` enum('semaine','dimanche') DEFAULT NULL,
  `jour_semaine` enum('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') DEFAULT NULL,
  `mois_location` varchar(7) DEFAULT NULL,
  `mois_subvention` varchar(7) DEFAULT NULL,
  `event_id` bigint(20) UNSIGNED DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_recette` date NOT NULL,
  `est_recurrent` tinyint(1) NOT NULL DEFAULT 0,
  `frequence_recurrence` enum('mensuel','trimestriel','annuel') DEFAULT NULL,
  `methode_paiement` enum('especes','cheque','virement','carte','mobile_money') NOT NULL DEFAULT 'especes',
  `reference_paiement` varchar(255) DEFAULT NULL,
  `statut` enum('en_attente','valide','rejete') NOT NULL DEFAULT 'valide',
  `notes` text DEFAULT NULL,
  `donateur_nom` varchar(255) DEFAULT NULL,
  `donateur_telephone` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `validated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `revenues`
--

INSERT INTO `revenues` (`id`, `paroisse_id`, `revenue_category_id`, `revenue_type_id`, `periode_messe`, `jour_semaine`, `mois_location`, `mois_subvention`, `event_id`, `montant`, `date_recette`, `est_recurrent`, `frequence_recurrence`, `methode_paiement`, `reference_paiement`, `statut`, `notes`, `donateur_nom`, `donateur_telephone`, `created_by`, `validated_by`, `validated_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 7, 33, 'semaine', 'mercredi', NULL, NULL, NULL, 1103000.00, '2026-07-22', 0, NULL, 'especes', 'REV-20260723052547-DYH3', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-07-23 03:25:47', '2026-08-01 07:57:23', '2026-08-01 07:57:23'),
(2, 1, 5, 23, 'semaine', 'samedi', NULL, NULL, NULL, 42000.00, '2026-07-25', 0, NULL, 'especes', 'REV-20260725100707-IZWN', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-07-25 08:07:07', '2026-08-01 07:57:31', '2026-08-01 07:57:31'),
(3, 1, 1, 1, 'semaine', 'samedi', NULL, NULL, NULL, 28025.00, '2026-08-01', 0, NULL, 'especes', 'REV-20260801095818-WX03', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-01 07:58:18', '2026-08-01 07:58:18', NULL),
(4, 1, 1, 2, 'dimanche', 'dimanche', NULL, NULL, NULL, 179950.00, '2026-08-02', 0, NULL, 'especes', 'REV-20260803083658-T7ZC', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-03 06:36:58', '2026-08-03 06:36:58', NULL),
(5, 1, 1, 1, 'semaine', 'lundi', NULL, NULL, NULL, 38900.00, '2026-08-03', 0, NULL, 'especes', 'REV-20260803083742-J76M', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-03 06:37:42', '2026-08-03 06:37:42', NULL),
(6, 1, 5, 28, 'semaine', 'mardi', NULL, NULL, NULL, 38550.00, '2026-08-04', 0, NULL, 'especes', 'REV-20260805073926-NYHV', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-05 05:39:26', '2026-08-25 10:15:07', NULL),
(7, 1, 1, 1, 'semaine', 'mercredi', NULL, NULL, NULL, 36325.00, '2026-08-05', 0, NULL, 'especes', 'REV-20260805074031-97N5', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-05 05:40:31', '2026-08-05 05:40:31', NULL),
(8, 1, 2, 4, 'semaine', 'jeudi', NULL, NULL, NULL, 8350.00, '2026-08-06', 0, NULL, 'especes', 'REV-20260810064223-HMUS', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-10 04:42:23', '2026-08-10 04:42:23', NULL),
(9, 1, 1, 1, 'semaine', 'vendredi', NULL, NULL, NULL, 34400.00, '2026-08-07', 0, NULL, 'especes', 'REV-20260810064249-GCYH', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-10 04:42:49', '2026-08-10 04:42:49', NULL),
(10, 1, 1, 1, 'semaine', 'samedi', NULL, NULL, NULL, 31525.00, '2026-08-08', 0, NULL, 'especes', 'REV-20260810064323-XUQV', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-10 04:43:23', '2026-08-10 04:43:23', NULL),
(11, 1, 1, 2, 'dimanche', 'dimanche', NULL, NULL, NULL, 84400.00, '2026-08-09', 0, NULL, 'especes', 'REV-20260810064405-BGIQ', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-10 04:44:05', '2026-08-10 04:44:05', NULL),
(12, 1, 1, 1, 'semaine', 'lundi', NULL, NULL, NULL, 48475.00, '2026-08-10', 0, NULL, 'especes', 'REV-20260810082924-A8N8', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-10 06:29:24', '2026-08-10 06:29:24', NULL),
(13, 1, 1, 1, 'semaine', 'mardi', NULL, NULL, NULL, 37125.00, '2026-08-11', 0, NULL, 'especes', 'REV-20260811172837-UM75', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-11 15:28:37', '2026-08-11 15:28:37', NULL),
(14, 1, 1, 1, 'semaine', 'mercredi', NULL, NULL, NULL, 36850.00, '2026-08-12', 0, NULL, 'especes', 'REV-20260812073215-UN0D', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-12 05:32:15', '2026-08-12 05:32:15', NULL),
(15, 1, 1, 1, 'semaine', 'jeudi', NULL, NULL, NULL, 38000.00, '2026-08-13', 0, NULL, 'especes', 'REV-20260813084734-VU4A', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-13 06:47:34', '2026-08-13 06:47:34', NULL),
(16, 1, 2, 4, 'semaine', 'mercredi', NULL, NULL, NULL, 7775.00, '2026-08-12', 0, NULL, 'especes', 'REV-20260813084909-L5VL', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-13 06:49:09', '2026-08-13 06:49:09', NULL),
(17, 1, 1, 1, 'semaine', 'samedi', NULL, NULL, NULL, 85025.00, '2026-08-15', 0, NULL, 'especes', 'REV-20260817074936-DIDT', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-17 05:49:36', '2026-08-17 05:49:36', NULL),
(18, 1, 1, 1, 'semaine', 'vendredi', NULL, NULL, NULL, 33825.00, '2026-08-14', 0, NULL, 'especes', 'REV-20260817075015-LOXT', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-17 05:50:15', '2026-08-17 05:50:15', NULL),
(19, 1, 1, 1, 'semaine', 'lundi', NULL, NULL, NULL, 42550.00, '2026-08-17', 0, NULL, 'especes', 'REV-20260817075112-Q1WU', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-17 05:51:12', '2026-08-17 05:51:12', NULL),
(20, 1, 1, 1, 'semaine', 'vendredi', NULL, NULL, NULL, 33825.00, '2026-08-14', 0, NULL, 'especes', 'REV-20260817075212-AJPX', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-17 05:52:12', '2026-08-17 05:52:27', '2026-08-17 05:52:27'),
(21, 1, 2, 3, 'semaine', 'vendredi', NULL, NULL, NULL, 17150.00, '2026-08-14', 0, NULL, 'especes', 'REV-20260817075546-3EAZ', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-17 05:55:46', '2026-08-17 05:55:46', NULL),
(22, 1, 2, 4, 'semaine', 'jeudi', NULL, NULL, NULL, 27025.00, '2026-08-13', 0, NULL, 'especes', 'REV-20260817075628-NJXQ', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-17 05:56:28', '2026-08-17 05:56:28', NULL),
(23, 1, 5, 28, 'semaine', 'mardi', NULL, NULL, NULL, 32925.00, '2026-08-18', 0, NULL, 'especes', 'REV-20260819063600-BANJ', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-19 04:36:00', '2026-08-26 05:25:11', NULL),
(24, 1, 4, 22, 'semaine', 'lundi', NULL, '2026-08', NULL, 500000.00, '2026-08-03', 0, NULL, 'especes', 'REV-20260819150659-H0ZF', 'valide', 'Avance popote du moi d\'aout', NULL, NULL, 3, NULL, NULL, '2026-08-19 13:06:59', '2026-08-19 13:06:59', NULL),
(25, 1, 4, 22, 'semaine', 'jeudi', NULL, '2026-08', NULL, 500000.00, '2026-08-06', 0, NULL, 'especes', 'REV-20260819155813-TUTG', 'valide', 'Complement de la recette du mois d\'août', NULL, NULL, 3, NULL, NULL, '2026-08-19 13:58:13', '2026-08-19 13:58:13', NULL),
(26, 1, 4, 22, 'dimanche', 'dimanche', NULL, '2026-08', NULL, 500000.00, '2026-08-16', 0, NULL, 'especes', 'REV-20260819164626-P9AS', 'valide', 'Approvisionnement', NULL, NULL, 3, NULL, NULL, '2026-08-19 14:46:26', '2026-08-19 14:46:26', NULL),
(27, 1, 1, 1, 'semaine', 'mercredi', NULL, NULL, NULL, 25200.00, '2026-08-19', 0, NULL, 'especes', 'REV-20260820122425-JPL3', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-20 10:24:25', '2026-08-20 10:24:25', NULL),
(28, 1, 2, 4, 'semaine', 'mardi', NULL, NULL, NULL, 12875.00, '2026-08-18', 0, NULL, 'especes', 'REV-20260820122509-PNDS', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-20 10:25:09', '2026-08-20 10:25:09', NULL),
(29, 1, 1, 1, 'semaine', 'jeudi', NULL, NULL, NULL, 35675.00, '2026-08-20', 0, NULL, 'especes', 'REV-20260820122540-KQVS', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-20 10:25:40', '2026-08-20 10:25:40', NULL),
(30, 1, 1, 1, 'semaine', 'vendredi', NULL, NULL, NULL, 35200.00, '2026-08-21', 0, NULL, 'especes', 'REV-20260824091004-JR6K', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-24 07:10:04', '2026-08-24 07:10:04', NULL),
(31, 1, 1, 1, 'semaine', 'samedi', NULL, NULL, NULL, 44225.00, '2026-08-22', 0, NULL, 'especes', 'REV-20260824091144-ZXNF', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-24 07:11:44', '2026-08-24 07:11:44', NULL),
(32, 1, 2, 5, 'semaine', 'samedi', NULL, NULL, NULL, 19375.00, '2026-08-22', 0, NULL, 'especes', 'REV-20260824091256-F6TL', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-24 07:12:56', '2026-08-24 07:12:56', NULL),
(33, 1, 1, 1, 'semaine', 'lundi', NULL, NULL, NULL, 44750.00, '2026-08-24', 0, NULL, 'especes', 'REV-20260824091525-BMAM', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-24 07:15:25', '2026-08-24 07:15:25', NULL),
(34, 1, 1, 1, 'semaine', 'mardi', NULL, NULL, NULL, 21325.00, '2026-08-25', 0, NULL, 'especes', 'REV-20260825083709-HNVG', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-25 06:37:09', '2026-08-25 06:37:09', NULL),
(35, 1, 1, 1, 'semaine', 'mercredi', NULL, NULL, NULL, 25750.00, '2026-08-26', 0, NULL, 'especes', 'REV-20260826065623-SDH2', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-26 04:56:23', '2026-08-26 04:56:23', NULL),
(36, 1, 1, 2, 'dimanche', 'dimanche', NULL, NULL, NULL, 181000.00, '2026-08-23', 0, NULL, 'especes', 'REV-20260826071906-NBRF', 'valide', NULL, NULL, NULL, 3, NULL, NULL, '2026-08-26 05:19:06', '2026-08-26 05:19:54', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `revenue_categories`
--

CREATE TABLE `revenue_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paroisse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(255) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `revenue_categories`
--

INSERT INTO `revenue_categories` (`id`, `paroisse_id`, `code`, `nom`, `description`, `actif`, `ordre`, `created_at`, `updated_at`) VALUES
(1, 1, 'quete_ordinaire', 'Quête Ordinaire', 'Messes de la semaine (Lundi à Samedi) et messe du dimanche', 1, 1, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(2, 1, 'quete_extraordinaire', 'Quête Extraordinaire', 'Mariage, obsèques, action de grâce, caritas, la grotte, etc.', 1, 2, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(3, 1, 'location', 'Location', 'Loyers (boutiques), salle de fête, chapiteaux, cour de la paroisse', 1, 3, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(4, 1, 'subvention', 'Subvention', 'Subventions mensuelles reçues de la hiérarchie (carburant, hosties, gardiennage, gaz, internet, eau, électricité, salaires, alimentation popote)', 1, 4, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(5, 1, 'procure', 'Procure', 'Dîmes, denier du culte, casuel (baptêmes des enfants)', 1, 5, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(6, 1, 'fete', 'Fête', 'Fêtes de la paroisse', 1, 6, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(7, 1, 'capita', 'BANQUE (économat Diocésain)', 'Capital à partir de ce solde que mes recettes et dépenses se feront.', 1, 1, '2026-07-22 11:53:45', '2026-08-26 08:51:16');

-- --------------------------------------------------------

--
-- Structure de la table `revenue_types`
--

CREATE TABLE `revenue_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `revenue_category_id` bigint(20) UNSIGNED NOT NULL,
  `paroisse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(255) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `revenue_types`
--

INSERT INTO `revenue_types` (`id`, `revenue_category_id`, `paroisse_id`, `code`, `nom`, `description`, `actif`, `ordre`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'messe_semaine', 'Messe Semaine', 'Messes du lundi au samedi', 1, 1, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(2, 1, 1, 'messe_dimanche', 'Messe Dimanche', 'Messe du dimanche', 1, 2, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(3, 2, 1, 'mariage', 'Mariage', 'Recette de mariage', 1, 1, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(4, 2, 1, 'obseques', 'Obsèques', 'Recette d\'obsèques', 1, 2, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(5, 2, 1, 'action_grace', 'Action de Grâce', 'Action de grâce (anniversaire, etc.)', 1, 3, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(6, 2, 1, 'caritas', 'Caritas', 'Recette Caritas', 1, 4, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(7, 2, 1, 'grotte', 'La Grotte', 'Recette de la grotte', 1, 5, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(8, 2, 1, 'autre_extraordinaire', 'Autre Extraordinaire', 'Autre recette extraordinaire', 1, 6, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(9, 2, 1, 'nsinsani', 'NSINSANI', NULL, 1, 10, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(10, 3, 1, 'loyer_boutique', 'Loyer Boutique', 'Loyer d\'une boutique', 1, 1, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(11, 3, 1, 'salle_fete', 'Salle de Fête', 'Location de salle de fête', 1, 2, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(12, 3, 1, 'chapiteau', 'Chapiteau', 'Location de chapiteau', 1, 3, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(13, 3, 1, 'cour_paroisse', 'Cour de la Paroisse', 'Location de la cour de la paroisse', 1, 4, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(14, 4, 1, 'subvention_carburant', 'Subvention Carburant', 'Subvention pour carburant', 1, 1, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(15, 4, 1, 'subvention_hosties', 'Subvention Hosties', 'Subvention pour hosties et matériel liturgique', 1, 2, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(16, 4, 1, 'subvention_gardiennage', 'Subvention Gardiennage', 'Subvention pour gardiennage', 1, 3, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(17, 4, 1, 'subvention_gaz', 'Subvention Gaz', 'Subvention pour gaz (fonctionnement + popote)', 1, 4, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(18, 4, 1, 'subvention_internet', 'Subvention Internet', 'Subvention pour internet et communication', 1, 5, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(19, 4, 1, 'subvention_eau', 'Subvention Eau', 'Subvention pour facture eau', 1, 6, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(20, 4, 1, 'subvention_electricite', 'Subvention Électricité', 'Subvention pour électricité', 1, 7, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(21, 4, 1, 'subvention_salaires', 'Subvention Salaires', 'Subvention pour salaires des ouvriers', 1, 8, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(22, 4, 1, 'subvention_popote', 'Subvention Popote', 'Subvention pour alimentation popote', 1, 9, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(23, 5, 1, 'dime', 'Dîme', 'Dîmes', 1, 1, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(24, 5, 1, 'denier_culte', 'Denier du Culte', 'Denier du culte', 1, 2, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(25, 5, 1, 'casuel_bapteme', 'Casuel (Baptêmes)', 'Casuel pour les baptêmes des enfants', 1, 3, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(26, 5, 1, 'don', 'Don', 'Don à la paroisse', 1, 4, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(27, 5, 1, 'nsinsani-d', 'NSINSANI DIOCESAIN', NULL, 1, 5, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(28, 5, 1, 'card', 'CARDINAL', NULL, 1, 10, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(29, 6, 1, 'fete-paroisse', 'Fête patronale paroissiale', 'Anniversaire de la paroisse', 1, 1, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(30, 6, 1, 'repas-doy', 'Repas du doyenné', NULL, 1, 2, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(31, 6, 1, 'renc-doy', 'Rencontre du doyenné', NULL, 1, 3, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(32, 6, 1, 'repas-natif', 'Repas des natifs', NULL, 1, 4, '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(33, 7, 1, 'rev-principal', 'REVENU PRINCIPAL', NULL, 1, 0, '2026-07-22 11:56:45', '2026-07-22 11:56:45');

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `libelle_role` varchar(255) DEFAULT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `libelle_role`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'Super administrateur', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(2, 'paroisse_admin', 'Administrateur de paroisse', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(3, 'paroisse_secretaire', 'Secrétaire de paroisse', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(4, 'paroisse_tresorier', 'Trésorier de paroisse', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55'),
(5, 'paroisse_lecteur', 'Lecteur (consultation)', 'web', '2026-06-28 20:06:55', '2026-06-28 20:06:55');

-- --------------------------------------------------------

--
-- Structure de la table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(1, 2),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(14, 2),
(15, 2),
(16, 2),
(17, 2),
(18, 2),
(19, 2),
(20, 2),
(21, 2),
(22, 2),
(23, 2),
(24, 2),
(25, 2),
(26, 2),
(27, 2),
(28, 2),
(29, 2),
(30, 2),
(31, 2),
(32, 2),
(33, 2),
(34, 2),
(35, 2),
(36, 2),
(37, 2),
(38, 2),
(39, 2),
(40, 2),
(41, 2),
(42, 2),
(43, 2),
(44, 2),
(45, 2),
(46, 2),
(47, 2),
(48, 2),
(49, 2),
(50, 2),
(51, 2),
(52, 2),
(53, 2),
(54, 2),
(55, 2),
(1, 3),
(2, 3),
(3, 3),
(4, 3),
(6, 3),
(8, 3),
(9, 3),
(10, 3),
(12, 3),
(13, 3),
(14, 3),
(16, 3),
(17, 3),
(18, 3),
(20, 3),
(21, 3),
(22, 3),
(24, 3),
(25, 3),
(26, 3),
(28, 3),
(29, 3),
(30, 3),
(32, 3),
(33, 3),
(34, 3),
(35, 3),
(37, 3),
(38, 3),
(39, 3),
(40, 3),
(43, 3),
(44, 3),
(45, 3),
(48, 3),
(50, 3),
(1, 4),
(2, 4),
(6, 4),
(28, 4),
(38, 4),
(39, 4),
(40, 4),
(41, 4),
(42, 4),
(43, 4),
(44, 4),
(45, 4),
(46, 4),
(47, 4),
(48, 4),
(49, 4),
(50, 4),
(1, 5),
(2, 5),
(8, 5),
(12, 5),
(16, 5),
(20, 5),
(24, 5),
(28, 5),
(33, 5),
(38, 5),
(43, 5),
(48, 5),
(50, 5);

-- --------------------------------------------------------

--
-- Structure de la table `sacraments`
--

CREATE TABLE `sacraments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paroisse_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('bapteme','confirmation','communion','mariage','obseques') NOT NULL,
  `date_celebration` date NOT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `celebrant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `beneficiary_name` varchar(255) DEFAULT NULL,
  `beneficiary_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('6Ajl1QoVHiN475zVF3HIvg8QXXYqG6fBOkwLHk7q', NULL, '188.166.68.82', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRUZhbThRNlJTWWloVEs3aEdyMHlOT1hFbEVvdXAyQmJBYjl0YnEzMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHBzOi8vd3d3LnBhcm9pc3NlLm1pc2FldG8uY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787738251),
('79NLwjCskAcLfCTvNLWEDkytEejXoDXpa8jEQQTT', NULL, '34.182.205.89', 'Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiS1dEV1VRdDBRd1E0R1E0YjNaWFlBbTFFZEFTWHpaU2pDSXpWRWZQbiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9wYXJvaXNzZS5taXNhZXRvLmNvbS9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1787745769),
('7jB4XdHc3PWmMpugYmn21Lm90FPKU5LBlo4w0W4x', NULL, '34.170.253.92', 'Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYnRvamkwdXl5bjA4RzdKRFdzVUNNdTVkOXUyWjV1U1NpOXZNNFA0QSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9wYXJvaXNzZS5taXNhZXRvLmNvbS9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1787751491),
('9cDY3Ga3okKtprzzPEyVPc1HOKXBCZSGLupyLbzx', NULL, '34.170.253.92', 'Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiOWZkV2s2NWJ6bE9zM2ozRUQ1QVZ0cVZDR0tMUU54ZEd6aVZRY0Z2WCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovL3Bhcm9pc3NlLm1pc2FldG8uY29tIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9wYXJvaXNzZS5taXNhZXRvLmNvbSI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787751489),
('aL6HlDDSYPdVqKvbbLszjaDQhqJ8MyYuhcEnBT7C', NULL, '187.14.55.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaXdVMlkySUJOc0RVS0pJc1J3Rk9kZUdidWVUcmNmcm05b2wxbU5GViI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyODoiaHR0cHM6Ly9wYXJvaXNzZS5taXNhZXRvLmNvbSI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM0OiJodHRwczovL3Bhcm9pc3NlLm1pc2FldG8uY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787724147),
('BQsVm8d1xykpPKsTu3uvNjW8Um4PY8RrARtWrvdj', NULL, '2a01:e5c0:9b59::2', 'Mozilla/5.0 (Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaUtub1hka3dISHVKVUx1Y3AxNVZoZzFZbFQ4dzZmaGxUUHlBUXI0bSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMjoiaHR0cHM6Ly93d3cucGFyb2lzc2UubWlzYWV0by5jb20iO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czozMjoiaHR0cHM6Ly93d3cucGFyb2lzc2UubWlzYWV0by5jb20iO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1787749373),
('ctIi97QOM34YK9xs838MkYTTxfE6UcNiYoXJKfn8', NULL, '188.166.68.82', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTmpyOEx2a0NEMVA3c0V6bzZmeGJ3eUNVYzJCd1ZCOThTYXIyb01FbSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMjoiaHR0cHM6Ly93d3cucGFyb2lzc2UubWlzYWV0by5jb20iO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czozMjoiaHR0cHM6Ly93d3cucGFyb2lzc2UubWlzYWV0by5jb20iO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1787738251),
('D79gjOHCwMnTMnyBFMII9oQYxiXJ35ngMvEdzzY4', 3, '197.214.238.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiSzRJNW50eXVtVDR4VlBDaWlpV3JrNzAyUHBNR2hKbkQ3Z200NUlIaSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ0OiJodHRwczovL3Bhcm9pc3NlLm1pc2FldG8uY29tL2V4cGVuc2VzL2NyZWF0ZSI7czo1OiJyb3V0ZSI7czoxNToiZXhwZW5zZXMuY3JlYXRlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MztzOjE4OiJhY3RpdmVfcGFyb2lzc2VfaWQiO2k6MTt9', 1787741732),
('eM9y8DckhpPoo1Iu6uAAOuTLprD5VXDa9OMzmkW2', NULL, '159.203.129.124', 'Mozilla/5.0 (X11; Linux x86_64; rv:142.0) Gecko/20100101 Firefox/142.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSk9WcVBiY2hDbXY4ZW5iNjNmaDV6S2RNbld4OW1XSFdHc0lWMnY5cCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovL3Bhcm9pc3NlLm1pc2FldG8uY29tIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9wYXJvaXNzZS5taXNhZXRvLmNvbS9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1787729658),
('f6UHRZEu5yvpvstsBbdu1FmSwIVCNkivUOIcnylj', 1, '197.214.238.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoibURwTjFBejFTYVdrTXhsMTd5TGwwQ3h5U3FSVUNWelVKQWRzRGhsRSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vcGFyb2lzc2UubWlzYWV0by5jb20vcmV2ZW51ZXMiO3M6NToicm91dGUiO3M6MTQ6InJldmVudWVzLmluZGV4Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE4OiJhY3RpdmVfcGFyb2lzc2VfaWQiO2k6MTt9', 1787749073),
('g1q7jPRzOSnJZnbVIW34iexTOvrnZ9LsU4cYawzQ', NULL, '193.183.107.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Agency/93.8.2357.5', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNXV0cjNDNHpOY0VFRjVHZXVOSXlMVEUzTUZvc284Y1doTDR3MWJBTiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyODoiaHR0cHM6Ly9wYXJvaXNzZS5taXNhZXRvLmNvbSI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI4OiJodHRwczovL3Bhcm9pc3NlLm1pc2FldG8uY29tIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1787737495),
('Gq6Un0eBMy9Znf47Cho2dthzEgfJ6PY92cZmHw7E', NULL, '193.235.141.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Agency/93.8.2357.5', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSlNBVmdQVnl1QjNMTEFNbksweGdXMkZENUU1WnVlaUtSVGtyTTVnMyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vcGFyb2lzc2UubWlzYWV0by5jb20vbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1787737495),
('hbkYLn1QlVHX4sWebtWTdlj1GQ8XIZA9w43hCZlW', NULL, '158.69.117.45', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMlZzUW00c09FU3A2bVhyTW41Wko4T3J0dlFTazlPTldndmlXMUI5eiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyODoiaHR0cHM6Ly9wYXJvaXNzZS5taXNhZXRvLmNvbSI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM0OiJodHRwczovL3Bhcm9pc3NlLm1pc2FldG8uY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787747299),
('ovvvzqD42nLtoE7Uk6mlVrGGLb9dOh5CENF0b83Q', NULL, '2001:4860:7:170f::ff', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiOGlsc2tQVHZwNzRXRFFGbmpVMk8yaUpGNGdxNnJObFliUnNPMktXUyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyODoiaHR0cHM6Ly9wYXJvaXNzZS5taXNhZXRvLmNvbSI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM0OiJodHRwczovL3Bhcm9pc3NlLm1pc2FldG8uY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787745069),
('romezzrT0Nrvo9WYXpw0BaIYdfhKSj1JJAj79fd0', NULL, '103.97.201.26', 'Mozilla/5.0 (next-filter)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWjZsbllZUnhDMGZjSldaWEJyU2FuUE1sQjhTbmlhZDY2SG9FSlAxRiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyODoiaHR0cHM6Ly9wYXJvaXNzZS5taXNhZXRvLmNvbSI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM0OiJodHRwczovL3Bhcm9pc3NlLm1pc2FldG8uY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787756735),
('sTOdDbe9uWUaco0M1JhN77SlFoqJU8qIPjvlwEeW', NULL, '34.182.205.89', 'Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiblBJbk1YdGtBQklxS0JBTTRVeGl2aWhPYk1JMjdTNzV0bkkyVmxmQiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovL3Bhcm9pc3NlLm1pc2FldG8uY29tIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9wYXJvaXNzZS5taXNhZXRvLmNvbSI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787745767),
('x4Vt88OJDL2LS1nWd6yFz0c4zF9nI5qDgxZkTpPp', 3, '2c0f:ef58:1509:d000:bd61:23fc:4fa7:5aa3', 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36 OPR/95.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiUEdISFJKTVd2aEtmcEcxTFhpUWk2VmNlbnFieEdxSEw0Z05MT2xvcCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwczovL3Bhcm9pc3NlLm1pc2FldG8uY29tL3JldmVudWVzIjtzOjU6InJvdXRlIjtzOjE0OiJyZXZlbnVlcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7czoxODoiYWN0aXZlX3Bhcm9pc3NlX2lkIjtpOjE7fQ==', 1787736807),
('YpuJm3kRBXAxzzvqZe1aRK2EtsUyiUFcnaz2b8Gp', NULL, '159.203.129.124', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUGhLMVMzd1ZpY3VQVmVLNEx0b2lLSW1pUWVZV1h3S0FPU1c0QjFmMiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyODoiaHR0cHM6Ly9wYXJvaXNzZS5taXNhZXRvLmNvbSI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM0OiJodHRwczovL3Bhcm9pc3NlLm1pc2FldG8uY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787729661);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(80) DEFAULT NULL,
  `paroisse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `username`, `paroisse_id`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Administrateur', 'admin@paroisse.cg', 'admin', 1, NULL, '$2y$12$yDT/5W4cscjpsZrPEYtyp.AVCwtbYcZ.f6YzosfifgSss5qGoUWAS', NULL, '2026-06-28 20:06:56', '2026-06-28 20:08:03'),
(2, 'Administrateur Paroisse', 'paroisse@paroisse.cg', 'paroisse', 1, NULL, '$2y$12$oY9oEMUd68iumbek9Gftbu8iHO9vRQ08VWOebGwmy0j0cYeV.63XO', NULL, '2026-06-28 20:06:56', '2026-06-28 20:06:56'),
(3, 'MASSAMBA', 'Crispin@catholique.cg', 'crispin', 1, NULL, '$2y$12$4fiHW5I/3svm4oXebQefsOY5P2Pbla2eGWOA4MwjIPznsV8nyUBfu', NULL, '2026-06-28 20:11:56', '2026-06-28 20:11:56');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Index pour la table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Index pour la table `configurations`
--
ALTER TABLE `configurations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `configurations_paroisse_id_cle_unique` (`paroisse_id`,`cle`);

--
-- Index pour la table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `events_celebre_par_id_foreign` (`celebre_par_id`),
  ADD KEY `events_paroisse_id_foreign` (`paroisse_id`);

--
-- Index pour la table `event_member`
--
ALTER TABLE `event_member`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_member_evenement_id_membre_id_unique` (`evenement_id`,`membre_id`),
  ADD KEY `event_member_membre_id_foreign` (`membre_id`);

--
-- Index pour la table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expenses_created_by_foreign` (`created_by`),
  ADD KEY `expenses_validated_by_foreign` (`validated_by`),
  ADD KEY `expenses_paroisse_id_date_depense_index` (`paroisse_id`,`date_depense`),
  ADD KEY `expenses_categorie_charge_date_depense_index` (`date_depense`),
  ADD KEY `expenses_type_charge_date_depense_index` (`date_depense`),
  ADD KEY `expenses_statut_index` (`statut`),
  ADD KEY `expenses_revenue_category_id_foreign` (`revenue_category_id`),
  ADD KEY `expenses_revenue_type_id_foreign` (`revenue_type_id`),
  ADD KEY `expenses_expense_type_id_foreign` (`expense_type_id`);

--
-- Index pour la table `expense_funding_sources`
--
ALTER TABLE `expense_funding_sources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expense_funding_sources_revenue_type_id_foreign` (`revenue_type_id`),
  ADD KEY `expense_funding_sources_expense_id_revenue_type_id_index` (`expense_id`,`revenue_type_id`),
  ADD KEY `efs_revenue_expense_idx` (`revenue_id`,`expense_id`);

--
-- Index pour la table `expense_types`
--
ALTER TABLE `expense_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `expense_types_code_unique` (`code`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `financial_reports`
--
ALTER TABLE `financial_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `financial_reports_created_by_foreign` (`created_by`),
  ADD KEY `financial_reports_paroisse_id_date_debut_date_fin_index` (`paroisse_id`,`date_debut`,`date_fin`),
  ADD KEY `financial_reports_periode_type_index` (`periode_type`);

--
-- Index pour la table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groups_responsable_id_foreign` (`responsable_id`),
  ADD KEY `groups_paroisse_id_foreign` (`paroisse_id`);

--
-- Index pour la table `group_member`
--
ALTER TABLE `group_member`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_member_groupe_id_membre_id_unique` (`groupe_id`,`membre_id`),
  ADD KEY `group_member_membre_id_foreign` (`membre_id`);

--
-- Index pour la table `inventaire_magasin`
--
ALTER TABLE `inventaire_magasin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventaire_magasin_paroisse_id_foreign` (`paroisse_id`);

--
-- Index pour la table `inventaire_patrimoine`
--
ALTER TABLE `inventaire_patrimoine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventaire_patrimoine_paroisse_id_foreign` (`paroisse_id`);

--
-- Index pour la table `inventories`
--
ALTER TABLE `inventories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventories_created_by_foreign` (`created_by`),
  ADD KEY `inventories_paroisse_id_categorie_index` (`paroisse_id`,`categorie`),
  ADD KEY `inventories_paroisse_id_etat_index` (`paroisse_id`,`etat`);

--
-- Index pour la table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Index pour la table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `members_paroisse_id_foreign` (`paroisse_id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Index pour la table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Index pour la table `paroisses`
--
ALTER TABLE `paroisses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `paroisses_code_paroisse_unique` (`code_paroisse`),
  ADD KEY `paroisses_curé_id_foreign` (`curé_id`);

--
-- Index pour la table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Index pour la table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Index pour la table `revenues`
--
ALTER TABLE `revenues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `revenues_event_id_foreign` (`event_id`),
  ADD KEY `revenues_created_by_foreign` (`created_by`),
  ADD KEY `revenues_validated_by_foreign` (`validated_by`),
  ADD KEY `revenues_paroisse_id_date_recette_index` (`paroisse_id`,`date_recette`),
  ADD KEY `revenues_revenue_category_id_date_recette_index` (`revenue_category_id`,`date_recette`),
  ADD KEY `revenues_revenue_type_id_date_recette_index` (`revenue_type_id`,`date_recette`),
  ADD KEY `revenues_periode_messe_date_recette_index` (`periode_messe`,`date_recette`),
  ADD KEY `revenues_est_recurrent_frequence_recurrence_index` (`est_recurrent`,`frequence_recurrence`),
  ADD KEY `revenues_statut_index` (`statut`),
  ADD KEY `revenues_mois_location_revenue_type_id_index` (`mois_location`,`revenue_type_id`),
  ADD KEY `revenues_popote_mois_idx` (`paroisse_id`,`revenue_type_id`,`mois_subvention`);

--
-- Index pour la table `revenue_categories`
--
ALTER TABLE `revenue_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `revenue_categories_paroisse_id_code_unique` (`paroisse_id`,`code`),
  ADD KEY `revenue_categories_code_index` (`code`),
  ADD KEY `revenue_categories_actif_index` (`actif`);

--
-- Index pour la table `revenue_types`
--
ALTER TABLE `revenue_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `revenue_types_paroisse_id_code_unique` (`paroisse_id`,`code`),
  ADD KEY `revenue_types_revenue_category_id_actif_index` (`revenue_category_id`,`actif`),
  ADD KEY `revenue_types_code_index` (`code`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Index pour la table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Index pour la table `sacraments`
--
ALTER TABLE `sacraments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sacraments_celebrant_id_foreign` (`celebrant_id`),
  ADD KEY `sacraments_beneficiary_id_foreign` (`beneficiary_id`),
  ADD KEY `sacraments_paroisse_id_type_date_celebration_index` (`paroisse_id`,`type`,`date_celebration`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD KEY `users_paroisse_id_foreign` (`paroisse_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `configurations`
--
ALTER TABLE `configurations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `event_member`
--
ALTER TABLE `event_member`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pour la table `expense_funding_sources`
--
ALTER TABLE `expense_funding_sources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pour la table `expense_types`
--
ALTER TABLE `expense_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `financial_reports`
--
ALTER TABLE `financial_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `group_member`
--
ALTER TABLE `group_member`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inventaire_magasin`
--
ALTER TABLE `inventaire_magasin`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inventaire_patrimoine`
--
ALTER TABLE `inventaire_patrimoine`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inventories`
--
ALTER TABLE `inventories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `members`
--
ALTER TABLE `members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pour la table `paroisses`
--
ALTER TABLE `paroisses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT pour la table `revenues`
--
ALTER TABLE `revenues`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT pour la table `revenue_categories`
--
ALTER TABLE `revenue_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `revenue_types`
--
ALTER TABLE `revenue_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `sacraments`
--
ALTER TABLE `sacraments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `configurations`
--
ALTER TABLE `configurations`
  ADD CONSTRAINT `configurations_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_celebre_par_id_foreign` FOREIGN KEY (`celebre_par_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `events_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `event_member`
--
ALTER TABLE `event_member`
  ADD CONSTRAINT `event_member_evenement_id_foreign` FOREIGN KEY (`evenement_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_member_membre_id_foreign` FOREIGN KEY (`membre_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `expenses_expense_type_id_foreign` FOREIGN KEY (`expense_type_id`) REFERENCES `expense_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expenses_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_revenue_category_id_foreign` FOREIGN KEY (`revenue_category_id`) REFERENCES `revenue_categories` (`id`),
  ADD CONSTRAINT `expenses_revenue_type_id_foreign` FOREIGN KEY (`revenue_type_id`) REFERENCES `revenue_types` (`id`),
  ADD CONSTRAINT `expenses_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `expense_funding_sources`
--
ALTER TABLE `expense_funding_sources`
  ADD CONSTRAINT `expense_funding_sources_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expense_funding_sources_revenue_id_foreign` FOREIGN KEY (`revenue_id`) REFERENCES `revenues` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expense_funding_sources_revenue_type_id_foreign` FOREIGN KEY (`revenue_type_id`) REFERENCES `revenue_types` (`id`);

--
-- Contraintes pour la table `financial_reports`
--
ALTER TABLE `financial_reports`
  ADD CONSTRAINT `financial_reports_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `financial_reports_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groups_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `members` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `group_member`
--
ALTER TABLE `group_member`
  ADD CONSTRAINT `group_member_groupe_id_foreign` FOREIGN KEY (`groupe_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_member_membre_id_foreign` FOREIGN KEY (`membre_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inventaire_magasin`
--
ALTER TABLE `inventaire_magasin`
  ADD CONSTRAINT `inventaire_magasin_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inventaire_patrimoine`
--
ALTER TABLE `inventaire_patrimoine`
  ADD CONSTRAINT `inventaire_patrimoine_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inventories`
--
ALTER TABLE `inventories`
  ADD CONSTRAINT `inventories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventories_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paroisses`
--
ALTER TABLE `paroisses`
  ADD CONSTRAINT `paroisses_curé_id_foreign` FOREIGN KEY (`curé_id`) REFERENCES `members` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `revenues`
--
ALTER TABLE `revenues`
  ADD CONSTRAINT `revenues_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `revenues_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `revenues_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `revenues_revenue_category_id_foreign` FOREIGN KEY (`revenue_category_id`) REFERENCES `revenue_categories` (`id`),
  ADD CONSTRAINT `revenues_revenue_type_id_foreign` FOREIGN KEY (`revenue_type_id`) REFERENCES `revenue_types` (`id`),
  ADD CONSTRAINT `revenues_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `revenue_categories`
--
ALTER TABLE `revenue_categories`
  ADD CONSTRAINT `revenue_categories_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `revenue_types`
--
ALTER TABLE `revenue_types`
  ADD CONSTRAINT `revenue_types_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `revenue_types_revenue_category_id_foreign` FOREIGN KEY (`revenue_category_id`) REFERENCES `revenue_categories` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sacraments`
--
ALTER TABLE `sacraments`
  ADD CONSTRAINT `sacraments_beneficiary_id_foreign` FOREIGN KEY (`beneficiary_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sacraments_celebrant_id_foreign` FOREIGN KEY (`celebrant_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sacraments_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_paroisse_id_foreign` FOREIGN KEY (`paroisse_id`) REFERENCES `paroisses` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
