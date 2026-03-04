-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  jeu. 29 mai 2025 à 10:38
-- Version du serveur :  5.7.26
-- Version de PHP :  7.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `unisecureid_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `access_logs`
--

DROP TABLE IF EXISTS `access_logs`;
CREATE TABLE IF NOT EXISTS `access_logs` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `nom_complet` varchar(100) NOT NULL,
  `role` enum('agent','enseignant','etudiant','admin','financier') NOT NULL,
  `type_acces` varchar(50) NOT NULL,
  `point_entree` varchar(100) DEFAULT NULL,
  `horodatage` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `agents`
--

DROP TABLE IF EXISTS `agents`;
CREATE TABLE IF NOT EXISTS `agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `fonction` varchar(100) DEFAULT NULL,
  `id_biometrie` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_biometrie` (`id_biometrie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `biometrie`
--

DROP TABLE IF EXISTS `biometrie`;
CREATE TABLE IF NOT EXISTS `biometrie` (
  `id_biometrie` int(11) NOT NULL AUTO_INCREMENT,
  `id_etudiant` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `fichier` varchar(255) NOT NULL,
  `date_capture` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_biometrie`),
  KEY `id_etudiant` (`id_etudiant`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cours`
--

DROP TABLE IF EXISTS `cours`;
CREATE TABLE IF NOT EXISTS `cours` (
  `id_cours` int(11) NOT NULL AUTO_INCREMENT,
  `nom_cours` varchar(100) NOT NULL,
  `description` text,
  `id_enseignant` int(11) DEFAULT NULL,
  `id_option` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_cours`),
  KEY `id_enseignant` (`id_enseignant`),
  KEY `id_option` (`id_option`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `cours`
--

INSERT INTO `cours` (`id_cours`, `nom_cours`, `description`, `id_enseignant`, `id_option`) VALUES
(1, 'Langage de  programmation', 'bien', 3, 2);

-- --------------------------------------------------------

--
-- Structure de la table `departements`
--

DROP TABLE IF EXISTS `departements`;
CREATE TABLE IF NOT EXISTS `departements` (
  `id_departement` int(11) NOT NULL AUTO_INCREMENT,
  `nom_departement` varchar(255) NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_departement`),
  UNIQUE KEY `nom_departement` (`nom_departement`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `departements`
--

INSERT INTO `departements` (`id_departement`, `nom_departement`, `date_creation`) VALUES
(2, 'Informatique de gestion', '2025-05-19 13:50:34');

-- --------------------------------------------------------

--
-- Structure de la table `enseignants`
--

DROP TABLE IF EXISTS `enseignants`;
CREATE TABLE IF NOT EXISTS `enseignants` (
  `id_enseignant` int(11) NOT NULL AUTO_INCREMENT,
  `nom_complet` text NOT NULL,
  `telephone` text NOT NULL,
  `email` text NOT NULL,
  PRIMARY KEY (`id_enseignant`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `enseignants`
--

INSERT INTO `enseignants` (`id_enseignant`, `nom_complet`, `telephone`, `email`) VALUES
(3, 'GEORGES MALOANI SAIDI', '0997749350', 'lucien@gmail.com');

-- --------------------------------------------------------

--
-- Structure de la table `etudiants`
--

DROP TABLE IF EXISTS `etudiants`;
CREATE TABLE IF NOT EXISTS `etudiants` (
  `id_etudiant` int(11) NOT NULL AUTO_INCREMENT,
  `nomcomplet` varchar(100) NOT NULL,
  `sexe` varchar(10) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `matricule` varchar(50) NOT NULL,
  `departement` varchar(100) NOT NULL,
  `options` varchar(100) NOT NULL,
  `photo` varchar(100) NOT NULL,
  `date_enregistrement` datetime DEFAULT CURRENT_TIMESTAMP,
  `statut_fingerprint` enum('Non capturé','Capturé') NOT NULL DEFAULT 'Non capturé',
  `empreinte_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_etudiant`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `etudiants`
--

INSERT INTO `etudiants` (`id_etudiant`, `nomcomplet`, `sexe`, `telephone`, `matricule`, `departement`, `options`, `photo`, `date_enregistrement`, `statut_fingerprint`, `empreinte_path`) VALUES
(7, 'MUBANGWA SAIDI Delphin', 'Masculin', '0979987876', 'ETD-5152A5D6', 'Informatique de gestion', 'Conception de systèmes d\'informations', 'etudiant_7.jpg', '2025-05-16 06:34:50', 'Capturé', 'empreintes/empreinte_7.bin'),
(5, 'LALA', 'Masculin', '0819023253', 'ETD-09421EE1', 'IG', 'IIS', 'etudiant_5.jpg', '2025-04-24 10:06:09', 'Capturé', 'empreintes/empreinte_5.bin');

-- --------------------------------------------------------

--
-- Structure de la table `examens`
--

DROP TABLE IF EXISTS `examens`;
CREATE TABLE IF NOT EXISTS `examens` (
  `id_examen` int(11) NOT NULL AUTO_INCREMENT,
  `id_option` int(11) NOT NULL,
  `nom_matiere` varchar(255) NOT NULL,
  `date_examen` date NOT NULL,
  `heure_examen` time NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_examen`),
  KEY `id_option` (`id_option`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `examens`
--

INSERT INTO `examens` (`id_examen`, `id_option`, `nom_matiere`, `date_examen`, `heure_examen`, `date_creation`) VALUES
(1, 2, 'Langage de programmation', '2025-05-19', '09:53:00', '2025-05-19 14:51:14');

-- --------------------------------------------------------

--
-- Structure de la table `options`
--

DROP TABLE IF EXISTS `options`;
CREATE TABLE IF NOT EXISTS `options` (
  `id_option` int(11) NOT NULL AUTO_INCREMENT,
  `nom_option` varchar(255) NOT NULL,
  `id_departement` int(11) NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_option`),
  KEY `id_departement` (`id_departement`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `options`
--

INSERT INTO `options` (`id_option`, `nom_option`, `id_departement`, `date_creation`) VALUES
(2, 'BAC 1', 2, '2025-05-19 13:58:31'),
(3, 'Bac 2', 2, '2025-05-19 14:08:58'),
(4, 'Bac 3', 2, '2025-05-19 14:09:05'),
(5, 'L1 ancien système', 2, '2025-05-19 14:09:18'),
(6, 'L2 ancien système', 2, '2025-05-19 14:09:28');

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
CREATE TABLE IF NOT EXISTS `paiements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_etudiant` int(11) NOT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `date_paiement` datetime DEFAULT CURRENT_TIMESTAMP,
  `motif` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_etudiant` (`id_etudiant`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id`, `id_etudiant`, `montant`, `date_paiement`, `motif`) VALUES
(2, 7, '100.00', '2025-05-20 08:58:08', 'Frais d\'inscription'),
(3, 5, '100.00', '2025-05-20 08:58:49', 'Frais d\'inscription');

-- --------------------------------------------------------

--
-- Structure de la table `personnels`
--

DROP TABLE IF EXISTS `personnels`;
CREATE TABLE IF NOT EXISTS `personnels` (
  `id_personnel` int(11) NOT NULL AUTO_INCREMENT,
  `nom_complet` varchar(100) NOT NULL,
  `poste` varchar(100) NOT NULL,
  `telephone` varchar(100) NOT NULL,
  `photo` varchar(100) NOT NULL,
  `date_ajout` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_personnel`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `personnels`
--

INSERT INTO `personnels` (`id_personnel`, `nom_complet`, `poste`, `telephone`, `photo`, `date_ajout`) VALUES
(20, 'Maloani Saidi Georges', 'Prof', '0819036309', 'agent_20.jpg', '2025-04-24 07:06:53'),
(21, 'sisi', 'prefet', '000', 'agent_21.jpg', '2025-05-01 15:13:32'),
(22, 'SIFA SAIDI Jeanne', 'Comptable', '0819087876', 'agent_22.jpg', '2025-05-14 10:43:59'),
(23, 'kk', 'kk', 'ui', 'pending.jpg', '2025-05-14 10:49:45');

-- --------------------------------------------------------

--
-- Structure de la table `presences`
--

DROP TABLE IF EXISTS `presences`;
CREATE TABLE IF NOT EXISTS `presences` (
  `id_presence` int(11) NOT NULL AUTO_INCREMENT,
  `id_personnel` int(11) NOT NULL,
  `nom_complet` varchar(100) NOT NULL,
  `date_heure` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_depart` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_presence`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `presences`
--

INSERT INTO `presences` (`id_presence`, `id_personnel`, `nom_complet`, `date_heure`, `date_depart`) VALUES
(26, 20, 'Maloani Saidi Georges', '2025-04-24 02:07:20', '2025-05-19 08:34:50'),
(27, 22, 'SIFA SAIDI Jeanne', '2025-05-14 06:04:08', '2025-05-19 08:34:50'),
(28, 22, 'SIFA SAIDI Jeanne', '2025-05-19 04:31:52', '2025-05-19 08:34:50');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','agent','enseignant','etudiant','financier') NOT NULL,
  `nom_complet` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) NOT NULL DEFAULT 'Désactiver',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `nom_complet`, `created_at`, `status`) VALUES
(1, 'Admin', 'Admin', 'admin', 'MALOANI SAIDI Georges', '2025-04-14 12:29:47', 'activer'),
(2, 'kaka', 'kaka', 'agent', 'Kibonge Saidi', '2025-04-14 14:37:06', 'activer'),
(3, 'AA@98', '123abc@çç', 'enseignant', 'ASSANI KIKUNI', '2025-05-14 10:36:34', 'activer'),
(4, 'MART', 'MART', 'etudiant', 'MARTHE OPENGE', '2025-05-17 11:35:06', 'activer'),
(5, 'julienne', 'julienne', 'financier', 'Julienne Maloani', '2025-05-20 08:14:19', 'activer');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `examens`
--
ALTER TABLE `examens`
  ADD CONSTRAINT `examens_ibfk_1` FOREIGN KEY (`id_option`) REFERENCES `options` (`id_option`) ON DELETE CASCADE;

--
-- Contraintes pour la table `options`
--
ALTER TABLE `options`
  ADD CONSTRAINT `options_ibfk_1` FOREIGN KEY (`id_departement`) REFERENCES `departements` (`id_departement`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
