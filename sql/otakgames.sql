-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 15 oct. 2026 à 15:56
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
  PRIMARY KEY (`id`),
  KEY `univers_id` (`univers_id`)
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `personnage`
--

INSERT INTO `personnage` (`id`, `univers_id`, `nom`, `prix_base`) VALUES
(1, 1, 'Naruto', 45000),
(2, 1, 'Sasuke', 45000),
(3, 1, 'Itachi', 50000),
(4, 1, 'Shisui', 42000),
(5, 1, 'Jiraya', 38000),
(6, 1, 'Orochimaru', 40000),
(7, 1, 'Tsunade', 38000),
(8, 1, 'Sakura', 28000),
(9, 1, 'Kakashi', 42000),
(10, 1, 'Obito', 45000),
(11, 1, 'Hashirama', 50000),
(12, 1, 'Madara', 50000),
(13, 1, 'Minato', 48000),
(14, 1, 'Tobirama', 45000),
(15, 1, 'Deidara', 32000),
(16, 1, 'Sasori', 32000),
(17, 1, 'Hidan', 30000),
(18, 1, 'Kakuzu', 32000),
(19, 1, 'Pain', 45000),
(20, 1, 'Konan', 30000),
(21, 1, 'Gaara', 35000),
(22, 1, 'Kankuro', 22000),
(23, 1, 'Temari', 25000),
(24, 1, 'Chiyo', 25000),
(25, 1, 'Shikamaru', 32000),
(26, 1, 'Neji', 30000),
(27, 1, 'Gai', 40000),
(28, 1, 'Lee', 28000),
(29, 1, 'Kiba', 20000),
(30, 1, 'Shino', 22000),
(31, 1, 'Choji', 22000),
(32, 1, 'Ino', 20000),
(33, 1, 'Suigetsu', 25000),
(34, 1, 'Karin', 20000),
(35, 1, 'Jugo', 24000),
(36, 1, 'Kimimaro', 30000),
(37, 1, 'Zabuza', 28000),
(38, 1, 'Haku', 25000),
(39, 1, 'Ay', 42000),
(40, 1, 'Bee', 40000),
(41, 1, 'Darui', 30000),
(42, 1, 'Omoi', 20000),
(43, 1, 'Onoki', 38000),
(44, 1, 'Kurotsuchi', 25000),
(45, 1, 'Mei', 35000),
(46, 1, 'Chojuro', 24000),
(47, 1, 'Yugito', 28000),
(48, 1, 'Yagura', 32000),
(49, 1, 'Danzo', 35000),
(50, 1, 'Sai', 25000),
(51, 1, 'Kabuto', 38000),
(52, 1, 'Anko', 22000),
(53, 1, 'Hiruzen', 38000),
(54, 1, 'Asuma', 28000),
(55, 1, 'Kaguya', 50000),
(56, 1, 'Hagoromo', 50000),
(57, 1, 'Gamabunta', 30000),
(58, 1, 'Gamaken', 25000),
(59, 1, 'Manda', 30000),
(60, 1, 'Aoda', 25000),
(61, 1, 'Katsuyu', 30000),
(62, 1, 'Pakkun', 15000),
(63, 1, 'Enma', 28000),
(64, 1, 'Ningame', 15000),
(65, 1, 'Kurama', 50000),
(66, 1, 'Gyuki', 45000),
(67, 1, 'Shukaku', 40000),
(68, 1, 'Isobu', 35000),
(69, 1, 'Samehada', 25000),
(70, 1, 'Tonton', 10000),
(71, 2, 'Luffy', 50000),
(72, 2, 'Zoro', 45000),
(73, 2, 'Sanji', 40000),
(74, 2, 'Nami', 30000),
(75, 2, 'Usopp', 25000),
(76, 2, 'Robin', 32000),
(77, 2, 'Franky', 28000),
(78, 2, 'Brook', 26000),
(79, 2, 'Jinbe', 38000),
(80, 2, 'Chopper', 15000),
(81, 2, 'Ace', 42000),
(82, 2, 'Sabo', 42000),
(83, 2, 'Law', 45000),
(84, 2, 'Kid', 42000),
(85, 2, 'Oden', 48000),
(86, 2, 'Yamato', 42000),
(87, 2, 'Momonosuke', 25000),
(88, 2, 'Kinemon', 28000),
(89, 2, 'Vegapunk', 48000),
(90, 2, 'Lilith', 30000),
(91, 2, 'Dorry', 35000),
(92, 2, 'Broggy', 35000),
(93, 2, 'Shanks', 50000),
(94, 2, 'Rayleigh', 48000),
(95, 2, 'Roger', 50000),
(96, 2, 'Barbe Blanche', 50000),
(97, 2, 'Garp', 48000),
(98, 2, 'Sengoku', 45000),
(99, 2, 'Aokiji', 45000),
(100, 2, 'Akainu', 48000),
(101, 2, 'Kizaru', 42000),
(102, 2, 'Fujitora', 42000),
(103, 2, 'Kaido', 50000),
(104, 2, 'Big Mom', 48000),
(105, 2, 'Barbe Noire', 48000),
(106, 2, 'Buggy', 20000),
(107, 2, 'Mihawk', 48000),
(108, 2, 'Doflamingo', 40000),
(109, 2, 'Hancock', 38000),
(110, 2, 'Kuma', 38000),
(111, 2, 'Crocodile', 35000),
(112, 2, 'Moria', 28000),
(113, 2, 'Dragon', 48000),
(114, 2, 'Ivankov', 30000),
(115, 2, 'King', 42000),
(116, 2, 'Queen', 40000),
(117, 2, 'Katakuri', 48000),
(118, 2, 'Perospero', 32000),
(119, 2, 'Inuarashi', 35000),
(120, 2, 'Nekomamushi', 35000),
(121, 2, 'Carrot', 25000),
(122, 2, 'Pedro', 28000),
(123, 2, 'Kyros', 32000),
(124, 2, 'Bartolomeo', 30000),
(125, 2, 'Caesar', 32000),
(126, 2, 'Vergo', 32000),
(127, 2, 'Shirahoshi', 35000),
(128, 2, 'Arlong', 22000),
(129, 2, 'Killer', 28000),
(130, 2, 'Bepo', 18000),
(131, 2, 'Perona', 28000),
(132, 2, 'Ryuma', 40000),
(133, 2, 'Lucci', 38000),
(134, 2, 'Kaku', 30000),
(135, 2, 'Ener', 38000),
(136, 2, 'Wiper', 25000),
(137, 2, 'Vivi', 20000),
(138, 2, 'Bon Clay', 28000),
(139, 2, 'Koby', 30000),
(140, 2, 'Smoker', 32000),
(141, 2, 'Magellan', 38000),
(142, 2, 'Shiryu', 38000),
(143, 2, 'Beckman', 42000),
(144, 2, 'Roux', 30000),
(145, 2, 'Marco', 42000),
(146, 2, 'Vista', 32000),
(147, 2, 'Saturn', 50000),
(148, 2, 'Imu', 50000),
(149, 2, 'Ulti', 30000),
(150, 2, 'Page One', 28000),
(151, 2, 'Denjiro', 28000),
(152, 2, 'Izo', 30000),
(153, 2, 'Cavendish', 30000),
(154, 2, 'Monet', 25000),
(155, 2, 'Zeff', 28000),
(156, 2, 'Kuro', 22000),
(157, 2, 'Laboon', 20000),
(158, 2, 'Zunesha', 45000);

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
