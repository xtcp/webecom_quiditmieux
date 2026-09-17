-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 17, 2026 at 05:43 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qdm`
--

-- --------------------------------------------------------

--
-- Table structure for table `enchere`
--

CREATE TABLE `enchere` (
  `id` int UNSIGNED NOT NULL,
  `utilisateur` int UNSIGNED NOT NULL,
  `prix` int NOT NULL,
  `vente` int UNSIGNED NOT NULL,
  `dateheure` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favori`
--

CREATE TABLE `favori` (
  `id` int UNSIGNED NOT NULL,
  `utilisateur` int UNSIGNED NOT NULL,
  `vente` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `image`
--

CREATE TABLE `image` (
  `id` int UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `vente` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int UNSIGNED NOT NULL,
  `pseudo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `motdepasse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `pseudo`, `email`, `motdepasse`) VALUES
(1, 'micael', 'micael@example.fr', '$2y$10$tzgG7FmDkMc6fBkY1LKPn.jJnL0wHx6Xv1uLiwr9D7hZKl.osdkDa'),
(2, 'julie', 'julie@exemple.fr', '$2y$10$tzgG7FmDkMc6fBkY1LKPn.jJnL0wHx6Xv1uLiwr9D7hZKl.osdkDa'),
(3, 'gourmand94', 'lucas@exemple.fr', '$2y$10$S7Vid3bCT73tseSr2JKh0.ovWbcKZvvhT/ojQ3yGA9yFId4wirLR2'),
(4, 'boulanger_fou', 'fou@qpm.fr', '$2y$10$S7Vid3bCT73tseSr2JKh0.ovWbcKZvvhT/ojQ3yGA9yFId4wirLR2'),
(5, 'clara', 'clara@qpm.fr', '$2y$10$S7Vid3bCT73tseSr2JKh0.ovWbcKZvvhT/ojQ3yGA9yFId4wirLR2'),
(6, 'thomas', 'thomas@qpm.fr', '$2y$10$S7Vid3bCT73tseSr2JKh0.ovWbcKZvvhT/ojQ3yGA9yFId4wirLR2'),
(7, 'john', 'john@qpm.fr', '$2y$10$S7Vid3bCT73tseSr2JKh0.ovWbcKZvvhT/ojQ3yGA9yFId4wirLR2'),
(8, 'sarah', 'sarah@qpm.fr', '$2y$10$S7Vid3bCT73tseSr2JKh0.ovWbcKZvvhT/ojQ3yGA9yFId4wirLR2'),
(9, 'mamie', 'mamie@qpm.fr', '$2y$10$S7Vid3bCT73tseSr2JKh0.ovWbcKZvvhT/ojQ3yGA9yFId4wirLR2'),
(10, 'chloe', 'chloe@qpm.fr', '$2y$10$S7Vid3bCT73tseSr2JKh0.ovWbcKZvvhT/ojQ3yGA9yFId4wirLR2'),
(11, 'luc', 'luc@qpm.fr', '$2y$10$S7Vid3bCT73tseSr2JKh0.ovWbcKZvvhT/ojQ3yGA9yFId4wirLR2'),
(12, 'enzo', 'enzo@qpm.fr', '$2y$10$S7Vid3bCT73tseSr2JKh0.ovWbcKZvvhT/ojQ3yGA9yFId4wirLR2'),
(13, 'eva', 'eva@qpm.fr', '$2y$10$S7Vid3bCT73tseSr2JKh0.ovWbcKZvvhT/ojQ3yGA9yFId4wirLR2'),
(20, 'asd', 'asd@asd.fr', '$2y$10$C9UnGXuUh918hL9T.1JQPuk9FprqyZp9K6JGAJaSy4fZ7pEpnD8D.');

-- --------------------------------------------------------

--
-- Table structure for table `vente`
--

CREATE TABLE `vente` (
  `id` int UNSIGNED NOT NULL,
  `utilisateur` int UNSIGNED NOT NULL,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie` int NOT NULL,
  `status` int NOT NULL DEFAULT '0',
  `image_principale` int UNSIGNED DEFAULT NULL,
  `etat_produit` int NOT NULL DEFAULT '0',
  `prix_depart` int NOT NULL DEFAULT '0',
  `dateheure` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateheure_fin` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `vente`
--

INSERT INTO `vente` (`id`, `utilisateur`, `titre`, `description`, `categorie`, `status`, `image_principale`, `etat_produit`, `prix_depart`, `dateheure`, `dateheure_fin`) VALUES
(1, 1, 'PC Gamer RTX 4070 complet', 'Configuration complete avec carte RTX 4070, 32Go RAM, SSD 1To. Fonctionne parfaitement, peu utilise.', 1, 0, 1, 2, 85000, '2026-08-01 10:00:00', '2026-09-23 23:01:01'),
(2, 2, 'iPhone 13 Pro 256Go', 'iPhone 13 Pro couleur graphite, batterie a 89%, ecran sans rayures, avec boite et chargeur.', 2, 1, 0, 3, 45000, '2026-09-10 09:00:00', '2026-09-20 22:00:00'),
(3, 3, 'Enceinte Bluetooth JBL Xtreme 3', 'Enceinte portable puissante, autonomie 15h, achetee il y a 2 mois, comme neuve.', 3, 2, 1, 1, 12000, '2026-07-01 12:00:00', '2026-07-08 18:00:00'),
(4, 4, 'Réfrigérateur combiné Samsung', 'Refrigerateur combine 350L, classe energetique A++, quelques traces d\'usage.', 4, 0, 0, 3, 30000, '2026-09-25 08:00:00', '2026-10-05 20:00:00'),
(5, 5, 'Lot de vaisselle vintage', 'Service de table complet annees 60, 24 pieces, parfait etat de conservation.', 5, 2, 0, 3, 4500, '2026-06-01 14:00:00', '2026-06-05 20:00:00'),
(6, 1, 'Tondeuse thermique Honda', 'Tondeuse thermique 4 temps, bac de ramassage inclus, entretenue regulierement.', 16, 1, 0, 2, 22000, '2026-09-12 07:00:00', '2026-09-22 20:00:00'),
(7, 2, 'Canapé d\'angle en cuir', 'Canape d\'angle cuir marron, tres confortable, quelques marques d\'usure sur les accoudoirs.', 17, 3, 0, 3, 40000, '2026-08-15 10:00:00', '2026-08-25 20:00:00'),
(8, 3, 'Veste en cuir vintage taille M', 'Veste en cuir authentique annees 80, style motard, quelques marques d\'usage.', 19, 2, 0, 4, 3500, '2026-05-10 11:00:00', '2026-05-15 20:00:00'),
(9, 4, 'Baskets Nike Air Jordan 1', 'Paire jamais portee, encore avec etiquette, pointure 42.', 22, 1, 0, 1, 15000, '2026-09-08 09:00:00', '2026-09-18 22:00:00'),
(10, 5, 'Montre automatique Seiko', 'Montre automatique mecanique, bracelet acier, revisee recemment.', 30, 2, 0, 2, 28000, '2026-04-01 15:00:00', '2026-04-10 20:00:00'),
(11, 1, 'Collection complète Tintin', 'Serie complete des 24 albums, editions cartonnees, bon etat general.', 31, 1, 0, 3, 8000, '2026-09-05 08:00:00', '2026-09-25 22:00:00'),
(12, 2, 'Vinyle Pink Floyd - Dark Side of the Moon', 'Edition originale, pochette et disque en bon etat, quelques rayures legeres.', 32, 2, 0, 2, 6000, '2026-03-01 10:00:00', '2026-03-05 20:00:00'),
(13, 3, 'Coffret Blu-ray Star Wars - Saga complète', 'Les 9 films en Blu-ray, coffret collector, neuf sous blister.', 45, 0, 0, 1, 5000, '2026-10-01 09:00:00', '2026-10-10 22:00:00'),
(14, 4, 'Vélo elliptique Domyos', 'Velo elliptique pliable, peu servi, ideal appartement.', 91, 2, 0, 3, 18000, '2026-08-20 08:00:00', '2026-08-30 20:00:00'),
(15, 5, 'VTT électrique Decathlon', 'VTT electrique moteur central, batterie 500Wh, 800km au compteur.', 93, 1, 0, 2, 90000, '2026-09-01 07:00:00', '2026-09-30 22:00:00'),
(16, 1, 'a', 'a', 1, 2, NULL, 1, 0, '2026-09-16 19:22:23', '2026-09-16 19:22:23'),
(17, 2, 'Cartes Pokémon rares', 'Lot de cartes rares et holographiques, collection annees 2000.', 110, 1, 0, 1, 25000, '2026-09-11 10:00:00', '2026-09-21 22:00:00'),
(18, 3, 'Tableau huile sur toile XIXe', 'Tableau ancien signe, huile sur toile, cadre dore d\'origine.', 115, 0, 0, 3, 150000, '2026-10-05 12:00:00', '2026-10-20 20:00:00'),
(19, 4, 'Appareil photo Canon EOS R6', 'Boitier hybride plein format, tres peu de declenchements, avec objectif 24-105mm.', 121, 2, 0, 2, 130000, '2026-01-10 08:00:00', '2026-01-20 20:00:00'),
(20, 5, 'Guitare électrique Fender Stratocaster', 'Stratocaster americaine, sunburst, etui rigide inclus.', 122, 1, 0, 3, 60000, '2026-09-09 09:00:00', '2026-09-19 22:00:00'),
(24, 1, 'asd', 'asd', 1, 2, NULL, 1, 0, '2026-09-16 21:34:11', '2026-09-16 21:34:11'),
(25, 1, 'asd', 'abncxcxA', 1, 1, NULL, 1, 0, '2026-09-16 23:00:10', '2026-09-16 23:00:10'),
(26, 1, 'asd', 'asd', 1, 2, NULL, 1, 0, '2026-09-16 23:03:53', '2026-09-16 23:03:53'),
(27, 1, 'asd', 'asd', 1, 1, NULL, 1, 0, '2026-09-16 23:11:02', '2026-09-16 23:11:02'),
(28, 1, 'asd', 'asd', 1, 0, NULL, 0, 0, '2026-09-16 23:12:40', '2026-09-16 23:12:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `enchere`
--
ALTER TABLE `enchere`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ce_commentaire_recette` (`vente`),
  ADD KEY `ce_commentaire_utilisateur` (`utilisateur`);

--
-- Indexes for table `favori`
--
ALTER TABLE `favori`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ce_favori_utilisateur` (`utilisateur`),
  ADD KEY `ce_favori_vente` (`vente`);

--
-- Indexes for table `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ce_image_vente` (`vente`);

--
-- Indexes for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vente`
--
ALTER TABLE `vente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ce_recette_utilisateur` (`utilisateur`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `enchere`
--
ALTER TABLE `enchere`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `favori`
--
ALTER TABLE `favori`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `image`
--
ALTER TABLE `image`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `vente`
--
ALTER TABLE `vente`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enchere`
--
ALTER TABLE `enchere`
  ADD CONSTRAINT `ce_enchere_utilisateur` FOREIGN KEY (`utilisateur`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `ce_enchere_vente` FOREIGN KEY (`vente`) REFERENCES `vente` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `favori`
--
ALTER TABLE `favori`
  ADD CONSTRAINT `ce_favori_utilisateur` FOREIGN KEY (`utilisateur`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `ce_favori_vente` FOREIGN KEY (`vente`) REFERENCES `vente` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `image`
--
ALTER TABLE `image`
  ADD CONSTRAINT `ce_image_vente` FOREIGN KEY (`vente`) REFERENCES `vente` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `vente`
--
ALTER TABLE `vente`
  ADD CONSTRAINT `ce_vente_utilisateur` FOREIGN KEY (`utilisateur`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
