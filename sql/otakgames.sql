-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 17 oct. 2026 à 13:06
-- Version du serveur : 8.4.7
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `otakgames`
--

-- --------------------------------------------------------

--
-- Structure de la table `enchere`
--

DROP TABLE IF EXISTS `enchere`;
CREATE TABLE IF NOT EXISTS `enchere` (
  `id` int NOT NULL AUTO_INCREMENT,
  `partie_id` int NOT NULL,
  `personnage_id` int NOT NULL,
  `prix_adjuje` int DEFAULT '0',
  `id_gagnant` int DEFAULT NULL,
  `statut` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `partie_id` (`partie_id`),
  KEY `personnage_id` (`personnage_id`),
  KEY `id_gagnant` (`id_gagnant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `historique_partie`
--

DROP TABLE IF EXISTS `historique_partie`;
CREATE TABLE IF NOT EXISTS `historique_partie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `resultat` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `indice`
--

DROP TABLE IF EXISTS `indice`;
CREATE TABLE IF NOT EXISTS `indice` (
  `id` int NOT NULL AUTO_INCREMENT,
  `partie_joueur_id` int NOT NULL,
  `contenu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tour_numero` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `partie_joueur_id` (`partie_joueur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `indice`
--

INSERT INTO `indice` (`id`, `partie_joueur_id`, `contenu`, `tour_numero`) VALUES
(1, 8, 'nul', 1),
(2, 7, 'dire', 2),
(3, 8, 'fqddf', 2);

-- --------------------------------------------------------

--
-- Structure de la table `leaderboard`
--

DROP TABLE IF EXISTS `leaderboard`;
CREATE TABLE IF NOT EXISTS `leaderboard` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `points_totaux` int DEFAULT '0',
  `parties_jouees` int DEFAULT '0',
  `victoires` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `leaderboard`
--

INSERT INTO `leaderboard` (`id`, `utilisateur_id`, `points_totaux`, `parties_jouees`, `victoires`) VALUES
(1, 1, 0, 0, 0),
(2, 2, 0, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `mise`
--

DROP TABLE IF EXISTS `mise`;
CREATE TABLE IF NOT EXISTS `mise` (
  `id` int NOT NULL AUTO_INCREMENT,
  `enchere_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `montant` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `enchere_id` (`enchere_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `paire_undercover`
--

DROP TABLE IF EXISTS `paire_undercover`;
CREATE TABLE IF NOT EXISTS `paire_undercover` (
  `id` int NOT NULL AUTO_INCREMENT,
  `univers_id` int NOT NULL,
  `personnage_1_id` int NOT NULL,
  `personnage_2_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `univers_id` (`univers_id`),
  KEY `personnage_1_id` (`personnage_1_id`),
  KEY `personnage_2_id` (`personnage_2_id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `paire_undercover`
--

INSERT INTO `paire_undercover` (`id`, `univers_id`, `personnage_1_id`, `personnage_2_id`) VALUES
(1, 1, 1, 2),
(2, 1, 1, 13),
(3, 1, 3, 4),
(4, 1, 3, 2),
(5, 1, 5, 6),
(6, 1, 5, 7),
(7, 1, 8, 32),
(8, 1, 9, 10),
(9, 1, 11, 12),
(10, 1, 13, 14),
(11, 1, 15, 16),
(12, 1, 17, 18),
(13, 1, 19, 20),
(14, 1, 21, 22),
(15, 1, 21, 23),
(16, 1, 24, 22),
(17, 1, 25, 31),
(18, 1, 26, 28),
(19, 1, 27, 28),
(20, 1, 29, 30),
(21, 1, 33, 34),
(22, 1, 33, 35),
(23, 1, 36, 16),
(24, 1, 37, 38),
(25, 1, 39, 40),
(26, 1, 41, 42),
(27, 1, 43, 44),
(28, 1, 45, 46),
(29, 1, 47, 48),
(30, 1, 49, 50),
(31, 1, 51, 52),
(32, 1, 53, 54),
(33, 1, 55, 56),
(34, 1, 57, 58),
(35, 1, 59, 60),
(36, 1, 61, 62),
(37, 1, 63, 64),
(38, 1, 65, 66),
(39, 1, 67, 68),
(40, 1, 69, 70),
(41, 2, 71, 81),
(42, 2, 71, 72),
(43, 2, 72, 73),
(44, 2, 74, 75),
(45, 2, 76, 77),
(46, 2, 78, 80),
(47, 2, 79, 121),
(48, 2, 81, 82),
(49, 2, 83, 84),
(50, 2, 85, 86),
(51, 2, 87, 88),
(52, 2, 89, 90),
(53, 2, 91, 92),
(54, 2, 93, 94),
(55, 2, 95, 96),
(56, 2, 97, 98),
(57, 2, 99, 100),
(58, 2, 101, 102),
(59, 2, 103, 104),
(60, 2, 105, 106),
(61, 2, 107, 108),
(62, 2, 109, 110),
(63, 2, 111, 112),
(64, 2, 113, 114),
(65, 2, 115, 116),
(66, 2, 117, 118),
(67, 2, 119, 120),
(68, 2, 122, 123),
(69, 2, 124, 125),
(70, 2, 126, 127),
(71, 2, 128, 129),
(72, 2, 130, 131),
(73, 2, 132, 133),
(74, 2, 134, 135),
(75, 2, 136, 137),
(76, 2, 138, 139),
(77, 2, 140, 141),
(78, 2, 142, 143),
(79, 2, 144, 145),
(80, 2, 146, 147),
(81, 2, 148, 149),
(82, 2, 150, 151),
(83, 2, 152, 153),
(84, 2, 154, 155),
(85, 2, 156, 157),
(86, 2, 158, 103);

-- --------------------------------------------------------

--
-- Structure de la table `partie_joueur`
--

DROP TABLE IF EXISTS `partie_joueur`;
CREATE TABLE IF NOT EXISTS `partie_joueur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `partie_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `personnage_id` int DEFAULT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vivant` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `partie_id` (`partie_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `personnage_id` (`personnage_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `partie_joueur`
--

INSERT INTO `partie_joueur` (`id`, `partie_id`, `utilisateur_id`, `personnage_id`, `role`, `vivant`) VALUES
(1, 1, 1, NULL, NULL, 1),
(2, 2, 2, NULL, NULL, 1),
(3, 3, 1, NULL, NULL, 1),
(4, 3, 2, NULL, NULL, 1),
(5, 4, 1, 49, 'undercover', 1),
(6, 4, 2, 64, 'civil', 1),
(7, 5, 1, 6, 'civil', 1),
(8, 5, 2, 60, 'undercover', 1),
(9, 6, 1, NULL, NULL, 1),
(10, 7, 1, NULL, NULL, 1),
(11, 8, 1, NULL, NULL, 1),
(12, 8, 2, NULL, NULL, 1),
(13, 9, 1, 122, 'civil', 1),
(14, 9, 2, 73, 'undercover', 1),
(15, 10, 2, 5, 'civil', 1),
(16, 10, 1, 52, 'undercover', 1);

-- --------------------------------------------------------

--
-- Structure de la table `partie_undercover`
--

DROP TABLE IF EXISTS `partie_undercover`;
CREATE TABLE IF NOT EXISTS `partie_undercover` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code_room` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `univers_id` int NOT NULL,
  `mode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nb_tours` int DEFAULT NULL,
  `tours_vote` int DEFAULT NULL,
  `nb_undercover` int DEFAULT NULL,
  `joueur_actuel_id` int DEFAULT NULL,
  `tour_actuel` int DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_room` (`code_room`),
  KEY `univers_id` (`univers_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `partie_undercover`
--

INSERT INTO `partie_undercover` (`id`, `code_room`, `univers_id`, `mode`, `statut`, `nb_tours`, `tours_vote`, `nb_undercover`, `joueur_actuel_id`, `tour_actuel`) VALUES
(1, 'KYJ6N9', 1, 'Original', 'attente', NULL, NULL, NULL, NULL, 1),
(2, '6A5JKS', 1, 'Original', 'attente', NULL, NULL, NULL, NULL, 1),
(3, 'OE8CDK', 1, 'Original', 'attente', NULL, NULL, NULL, NULL, 1),
(4, 'ZG51KC', 1, 'Original', 'en_cours', 3, 2, 1, NULL, 1),
(5, 'UKXOZ1', 1, 'Original', 'en_cours', 2, 1, 1, 7, 3),
(6, 'PH482E', 1, 'Original', 'attente', 3, 1, 1, NULL, 1),
(7, 'NCFJO3', 1, 'Original', 'attente', 5, 1, 1, NULL, 1),
(8, 'WE5T42', 1, 'Original', 'attente', 4, 2, 2, NULL, 1),
(9, '5EBTN7', 2, 'Original', 'en_cours', 3, 2, 1, 14, 1),
(10, 'MO32FK', 1, 'Original', 'en_cours', 3, 3, 1, 16, 1);

-- --------------------------------------------------------

--
-- Structure de la table `personnage`
--

DROP TABLE IF EXISTS `personnage`;
CREATE TABLE IF NOT EXISTS `personnage` (
  `id` int NOT NULL AUTO_INCREMENT,
  `univers_id` int NOT NULL,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prix_base` int NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `univers_id` (`univers_id`)
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `personnage`
--

INSERT INTO `personnage` (`id`, `univers_id`, `nom`, `prix_base`, `image`) VALUES
(1, 1, 'Naruto', 45000, NULL),
(2, 1, 'Sasuke', 45000, NULL),
(3, 1, 'Itachi', 50000, NULL),
(4, 1, 'Shisui', 42000, NULL),
(5, 1, 'Jiraya', 38000, NULL),
(6, 1, 'Orochimaru', 40000, NULL),
(7, 1, 'Tsunade', 38000, NULL),
(8, 1, 'Sakura', 28000, NULL),
(9, 1, 'Kakashi', 42000, NULL),
(10, 1, 'Obito', 45000, NULL),
(11, 1, 'Hashirama', 50000, NULL),
(12, 1, 'Madara', 50000, NULL),
(13, 1, 'Minato', 48000, NULL),
(14, 1, 'Tobirama', 45000, NULL),
(15, 1, 'Deidara', 32000, NULL),
(16, 1, 'Sasori', 32000, NULL),
(17, 1, 'Hidan', 30000, NULL),
(18, 1, 'Kakuzu', 32000, NULL),
(19, 1, 'Pain', 45000, NULL),
(20, 1, 'Konan', 30000, NULL),
(21, 1, 'Gaara', 35000, NULL),
(22, 1, 'Kankuro', 22000, NULL),
(23, 1, 'Temari', 25000, NULL),
(24, 1, 'Chiyo', 25000, NULL),
(25, 1, 'Shikamaru', 32000, NULL),
(26, 1, 'Neji', 30000, NULL),
(27, 1, 'Gai', 40000, NULL),
(28, 1, 'Lee', 28000, NULL),
(29, 1, 'Kiba', 20000, NULL),
(30, 1, 'Shino', 22000, NULL),
(31, 1, 'Choji', 22000, NULL),
(32, 1, 'Ino', 20000, NULL),
(33, 1, 'Suigetsu', 25000, NULL),
(34, 1, 'Karin', 20000, NULL),
(35, 1, 'Jugo', 24000, NULL),
(36, 1, 'Kimimaro', 30000, NULL),
(37, 1, 'Zabuza', 28000, NULL),
(38, 1, 'Haku', 25000, NULL),
(39, 1, 'Ay', 42000, NULL),
(40, 1, 'Bee', 40000, NULL),
(41, 1, 'Darui', 30000, NULL),
(42, 1, 'Omoi', 20000, NULL),
(43, 1, 'Onoki', 38000, NULL),
(44, 1, 'Kurotsuchi', 25000, NULL),
(45, 1, 'Mei', 35000, NULL),
(46, 1, 'Chojuro', 24000, NULL),
(47, 1, 'Yugito', 28000, NULL),
(48, 1, 'Yagura', 32000, NULL),
(49, 1, 'Danzo', 35000, NULL),
(50, 1, 'Sai', 25000, NULL),
(51, 1, 'Kabuto', 38000, NULL),
(52, 1, 'Anko', 22000, NULL),
(53, 1, 'Hiruzen', 38000, NULL),
(54, 1, 'Asuma', 28000, NULL),
(55, 1, 'Kaguya', 50000, NULL),
(56, 1, 'Hagoromo', 50000, NULL),
(57, 1, 'Gamabunta', 30000, NULL),
(58, 1, 'Gamaken', 25000, NULL),
(59, 1, 'Manda', 30000, NULL),
(60, 1, 'Aoda', 25000, NULL),
(61, 1, 'Katsuyu', 30000, NULL),
(62, 1, 'Pakkun', 15000, NULL),
(63, 1, 'Enma', 28000, NULL),
(64, 1, 'Ningame', 15000, NULL),
(65, 1, 'Kurama', 50000, NULL),
(66, 1, 'Gyuki', 45000, NULL),
(67, 1, 'Shukaku', 40000, NULL),
(68, 1, 'Isobu', 35000, NULL),
(69, 1, 'Samehada', 25000, NULL),
(70, 1, 'Tonton', 10000, NULL),
(71, 2, 'Luffy', 50000, NULL),
(72, 2, 'Zoro', 45000, NULL),
(73, 2, 'Sanji', 40000, NULL),
(74, 2, 'Nami', 30000, NULL),
(75, 2, 'Usopp', 25000, NULL),
(76, 2, 'Robin', 32000, NULL),
(77, 2, 'Franky', 28000, NULL),
(78, 2, 'Brook', 26000, NULL),
(79, 2, 'Jinbe', 38000, NULL),
(80, 2, 'Chopper', 15000, NULL),
(81, 2, 'Ace', 42000, NULL),
(82, 2, 'Sabo', 42000, NULL),
(83, 2, 'Law', 45000, NULL),
(84, 2, 'Kid', 42000, NULL),
(85, 2, 'Oden', 48000, NULL),
(86, 2, 'Yamato', 42000, NULL),
(87, 2, 'Momonosuke', 25000, NULL),
(88, 2, 'Kinemon', 28000, NULL),
(89, 2, 'Vegapunk', 48000, NULL),
(90, 2, 'Lilith', 30000, NULL),
(91, 2, 'Dorry', 35000, NULL),
(92, 2, 'Broggy', 35000, NULL),
(93, 2, 'Shanks', 50000, NULL),
(94, 2, 'Rayleigh', 48000, NULL),
(95, 2, 'Roger', 50000, NULL),
(96, 2, 'Barbe Blanche', 50000, NULL),
(97, 2, 'Garp', 48000, NULL),
(98, 2, 'Sengoku', 45000, NULL),
(99, 2, 'Aokiji', 45000, NULL),
(100, 2, 'Akainu', 48000, NULL),
(101, 2, 'Kizaru', 42000, NULL),
(102, 2, 'Fujitora', 42000, NULL),
(103, 2, 'Kaido', 50000, NULL),
(104, 2, 'Big Mom', 48000, NULL),
(105, 2, 'Barbe Noire', 48000, NULL),
(106, 2, 'Buggy', 20000, NULL),
(107, 2, 'Mihawk', 48000, NULL),
(108, 2, 'Doflamingo', 40000, NULL),
(109, 2, 'Hancock', 38000, NULL),
(110, 2, 'Kuma', 38000, NULL),
(111, 2, 'Crocodile', 35000, NULL),
(112, 2, 'Moria', 28000, NULL),
(113, 2, 'Dragon', 48000, NULL),
(114, 2, 'Ivankov', 30000, NULL),
(115, 2, 'King', 42000, NULL),
(116, 2, 'Queen', 40000, NULL),
(117, 2, 'Katakuri', 48000, NULL),
(118, 2, 'Perospero', 32000, NULL),
(119, 2, 'Inuarashi', 35000, NULL),
(120, 2, 'Nekomamushi', 35000, NULL),
(121, 2, 'Carrot', 25000, NULL),
(122, 2, 'Pedro', 28000, NULL),
(123, 2, 'Kyros', 32000, NULL),
(124, 2, 'Bartolomeo', 30000, NULL),
(125, 2, 'Caesar', 32000, NULL),
(126, 2, 'Vergo', 32000, NULL),
(127, 2, 'Shirahoshi', 35000, NULL),
(128, 2, 'Arlong', 22000, NULL),
(129, 2, 'Killer', 28000, NULL),
(130, 2, 'Bepo', 18000, NULL),
(131, 2, 'Perona', 28000, NULL),
(132, 2, 'Ryuma', 40000, NULL),
(133, 2, 'Lucci', 38000, NULL),
(134, 2, 'Kaku', 30000, NULL),
(135, 2, 'Ener', 38000, NULL),
(136, 2, 'Wiper', 25000, NULL),
(137, 2, 'Vivi', 20000, NULL),
(138, 2, 'Bon Clay', 28000, NULL),
(139, 2, 'Koby', 30000, NULL),
(140, 2, 'Smoker', 32000, NULL),
(141, 2, 'Magellan', 38000, NULL),
(142, 2, 'Shiryu', 38000, NULL),
(143, 2, 'Beckman', 42000, NULL),
(144, 2, 'Roux', 30000, NULL),
(145, 2, 'Marco', 42000, NULL),
(146, 2, 'Vista', 32000, NULL),
(147, 2, 'Saturn', 50000, NULL),
(148, 2, 'Imu', 50000, NULL),
(149, 2, 'Ulti', 30000, NULL),
(150, 2, 'Page One', 28000, NULL),
(151, 2, 'Denjiro', 28000, NULL),
(152, 2, 'Izo', 30000, NULL),
(153, 2, 'Cavendish', 30000, NULL),
(154, 2, 'Monet', 25000, NULL),
(155, 2, 'Zeff', 28000, NULL),
(156, 2, 'Kuro', 22000, NULL),
(157, 2, 'Laboon', 20000, NULL),
(158, 2, 'Zunesha', 45000, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `univers`
--

DROP TABLE IF EXISTS `univers`;
CREATE TABLE IF NOT EXISTS `univers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `univers`
--

INSERT INTO `univers` (`id`, `nom`) VALUES
(1, 'Naruto'),
(2, 'One Piece');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_naissance` date NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'avatar1.jpg',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pseudo` (`pseudo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `pseudo`, `email`, `nom`, `prenom`, `date_naissance`, `mot_de_passe`, `avatar`) VALUES
(1, 'hedgegane', '', '', '', '0000-00-00', '$2y$10$W17BLvsHix3ASkBfm8n8m.7FPb4o9kVnSO9vJfntHNWBWP7sMwtlq', 'avatar6.jpg'),
(2, 'test', '', '', '', '0000-00-00', '$2y$10$8CKJ/1Wzxyx2R3laNSeRleb1DpGK2be1TnuPB1uL2xdL5Rd689MEq', 'avatar5.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `vote`
--

DROP TABLE IF EXISTS `vote`;
CREATE TABLE IF NOT EXISTS `vote` (
  `id` int NOT NULL AUTO_INCREMENT,
  `partie_id` int NOT NULL,
  `id_votant` int NOT NULL,
  `id_cible` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `partie_id` (`partie_id`),
  KEY `id_votant` (`id_votant`),
  KEY `id_cible` (`id_cible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `enchere`
--
ALTER TABLE `enchere`
  ADD CONSTRAINT `enchere_ibfk_1` FOREIGN KEY (`partie_id`) REFERENCES `partie_undercover` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enchere_ibfk_2` FOREIGN KEY (`personnage_id`) REFERENCES `personnage` (`id`),
  ADD CONSTRAINT `enchere_ibfk_3` FOREIGN KEY (`id_gagnant`) REFERENCES `utilisateur` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `historique_partie`
--
ALTER TABLE `historique_partie`
  ADD CONSTRAINT `historique_partie_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `indice`
--
ALTER TABLE `indice`
  ADD CONSTRAINT `indice_ibfk_1` FOREIGN KEY (`partie_joueur_id`) REFERENCES `partie_joueur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD CONSTRAINT `leaderboard_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `mise`
--
ALTER TABLE `mise`
  ADD CONSTRAINT `mise_ibfk_1` FOREIGN KEY (`enchere_id`) REFERENCES `enchere` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mise_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paire_undercover`
--
ALTER TABLE `paire_undercover`
  ADD CONSTRAINT `fk_paire_p1` FOREIGN KEY (`personnage_1_id`) REFERENCES `personnage` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_paire_p2` FOREIGN KEY (`personnage_2_id`) REFERENCES `personnage` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_paire_univers` FOREIGN KEY (`univers_id`) REFERENCES `univers` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `partie_joueur`
--
ALTER TABLE `partie_joueur`
  ADD CONSTRAINT `partie_joueur_ibfk_1` FOREIGN KEY (`partie_id`) REFERENCES `partie_undercover` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partie_joueur_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partie_joueur_ibfk_3` FOREIGN KEY (`personnage_id`) REFERENCES `personnage` (`id`);

--
-- Contraintes pour la table `partie_undercover`
--
ALTER TABLE `partie_undercover`
  ADD CONSTRAINT `partie_undercover_ibfk_1` FOREIGN KEY (`univers_id`) REFERENCES `univers` (`id`);

--
-- Contraintes pour la table `personnage`
--
ALTER TABLE `personnage`
  ADD CONSTRAINT `personnage_ibfk_1` FOREIGN KEY (`univers_id`) REFERENCES `univers` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `vote`
--
ALTER TABLE `vote`
  ADD CONSTRAINT `vote_ibfk_1` FOREIGN KEY (`partie_id`) REFERENCES `partie_undercover` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vote_ibfk_2` FOREIGN KEY (`id_votant`) REFERENCES `partie_joueur` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vote_ibfk_3` FOREIGN KEY (`id_cible`) REFERENCES `partie_joueur` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
