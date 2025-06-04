-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : lun. 02 juin 2025 à 13:55
-- Version du serveur : 10.11.11-MariaDB-0+deb12u1
-- Version de PHP : 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `razanateraa_cinema`
--

-- --------------------------------------------------------

--
-- Structure de la table `Addition`
--

CREATE TABLE `Addition` (
  `id_ligne` int(11) NOT NULL,
  `id_commande` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `Total` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Addition`
--

INSERT INTO `Addition` (`id_ligne`, `id_commande`, `id_produit`, `quantite`, `Total`) VALUES
(1, 1, 1, 2, NULL),
(2, 2, 56, 2, NULL),
(3, 2, 57, 5, NULL),
(4, 3, 29, 1, NULL),
(5, 3, 52, 2, NULL);

--
-- Déclencheurs `Addition`
--
DELIMITER $$
CREATE TRIGGER `trg_after_update_addition` AFTER UPDATE ON `Addition` FOR EACH ROW BEGIN
    DECLARE min_id INT;
    DECLARE total_qte INT;

    -- Trouver la ligne avec l'id minimal pour cette commande
    SELECT MIN(id) INTO min_id FROM Addition WHERE id_commande = NEW.id_commande;

    -- Calculer la somme des quantités (en remplaçant NULL par 0)
    SELECT SUM(COALESCE(quantite, 0)) INTO total_qte FROM Addition WHERE id_commande = NEW.id_commande;

    -- Mettre NULL sur toutes les quantités sauf la ligne avec id minimal
    UPDATE Addition SET quantite = NULL WHERE id_commande = NEW.id_commande AND id <> min_id;

    -- Mettre la quantité totale dans la ligne avec id minimal
    UPDATE Addition SET quantite = total_qte WHERE id = min_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `Categorie`
--

CREATE TABLE `Categorie` (
  `id_categorie` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Categorie`
--

INSERT INTO `Categorie` (`id_categorie`, `nom`, `description`) VALUES
(1, 'Bieres pression', 'Il s\'agit d\'une Boisson Interdite au Mineurs'),
(2, 'Confiseries', 'Gourmandise et petites nourritures'),
(3, 'Boisson Chaudes', 'Boisson servi chaude '),
(4, 'Boissons', 'Il s\'agit de boisson servi froides ou tièdes '),
(5, 'Dons', 'Il s\'agit de dons '),
(6, 'Glaces', 'Glaces servis froides'),
(7, 'Divers', 'Mettre ici les produits différents'),
(8, 'Pop-corn', 'Ce sont des pop-corn (global)'),
(9, 'Nourriture chaudes', 'Plat nécessitant une mise en chaleur pour la dégustation');

-- --------------------------------------------------------

--
-- Structure de la table `Commande`
--

CREATE TABLE `Commande` (
  `id_commande` int(11) NOT NULL,
  `date_commande` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Commande`
--

INSERT INTO `Commande` (`id_commande`, `date_commande`) VALUES
(1, '2025-05-28'),
(2, '2025-05-28'),
(3, '2025-05-30');

-- --------------------------------------------------------

--
-- Structure de la table `Marque`
--

CREATE TABLE `Marque` (
  `id_marque` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Marque`
--

INSERT INTO `Marque` (`id_marque`, `nom`) VALUES
(NULL, 'Belin'),
(NULL, 'Bounty'),
(NULL, 'Chamallows'),
(NULL, 'Chips'),
(NULL, 'Coca Cola'),
(NULL, 'Haribo'),
(NULL, 'Desperados'),
(NULL, 'Volvic'),
(NULL, 'Fanta'),
(NULL, 'Fuze tea'),
(NULL, 'Goût Glace'),
(NULL, 'Extrême'),
(NULL, 'Magnum'),
(NULL, 'Pop-corn'),
(NULL, 'Pur Jus de pomme'),
(NULL, 'Redbull'),
(NULL, 'San Pelligrino'),
(NULL, 'Sceau'),
(NULL, 'Sirop'),
(NULL, 'Skittles'),
(NULL, 'Snickers'),
(NULL, 'Sprite'),
(NULL, 'Sucettes'),
(NULL, 'Super Frite'),
(NULL, 'Thé mémé'),
(NULL, 'Tourtel twist'),
(NULL, 'Tropico'),
(NULL, 'twix'),
(NULL, 'Vittel'),
(NULL, 'Smarties'),
(NULL, 'Lipton'),
(NULL, 'Jus de reve '),
(NULL, 'Kinder Bueno'),
(NULL, 'Kit Kat'),
(NULL, 'Lion'),
(NULL, 'M&M\'S'),
(NULL, 'Maltesers'),
(NULL, 'Mentos'),
(NULL, 'Monster'),
(NULL, 'Oasis'),
(NULL, 'Orangina'),
(NULL, 'Pecheresse'),
(NULL, 'Perrier'),
(NULL, 'Petillant'),
(NULL, 'Heineken'),
(NULL, 'Chocolat'),
(NULL, 'Café'),
(NULL, 'Galopin'),
(NULL, 'Diabolo'),
(NULL, 'Pizza'),
(NULL, 'Doritos');

-- --------------------------------------------------------

--
-- Structure de la table `Mouvement`
--

CREATE TABLE `Mouvement` (
  `id_mouvement` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `type_mouvement` enum('entrée','sortie') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Mouvement`
--

INSERT INTO `Mouvement` (`id_mouvement`, `id_produit`, `quantite`, `type_mouvement`) VALUES
(1, 1, 2, 'entrée'),
(2, 56, 2, 'sortie'),
(3, 57, 5, 'entrée'),
(4, 29, 1, 'entrée'),
(5, 52, 2, 'entrée');

-- --------------------------------------------------------

--
-- Structure de la table `Produit`
--

CREATE TABLE `Produit` (
  `id_produit` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `format` float DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1,
  `id_marque` int(11) DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Produit`
--

INSERT INTO `Produit` (`id_produit`, `nom`, `format`, `actif`, `id_marque`, `id_categorie`) VALUES
(1, '1664 Blonde 25cl', 25, 0, 45, 1),
(2, '1664 Blonde 50cl', 50, 0, 45, 1),
(3, 'Affiche Mylene Farmer', NULL, 0, NULL, 7),
(4, 'Barb\'a box', NULL, 1, NULL, 2),
(5, 'Monaco', NULL, 1, 1, 2),
(6, 'Pizza', NULL, 1, 1, 2),
(7, 'Bounty', NULL, 1, 2, 2),
(8, 'Café ', NULL, 0, 47, 3),
(9, 'Cafe à emporter', NULL, 0, 47, 3),
(10, 'Cafe alllonge', NULL, 0, 47, 3),
(11, 'Café au lait à emporter', NULL, 0, 47, 3),
(12, 'Café creme', NULL, 0, 47, 3),
(13, 'Café creme à emporter', NULL, 0, 47, 3),
(14, 'Café deca', NULL, 0, 47, 3),
(15, 'Café Soluble', NULL, 0, 47, 3),
(16, 'Chamallows', NULL, 1, 3, 2),
(17, 'Chips à l\'ancienne', NULL, 1, 4, 2),
(18, 'Chips jura 125g', 125, 0, 4, 2),
(19, 'Chocolat a emporter', NULL, 0, 46, 3),
(20, 'Chocolat chaud', NULL, 1, 46, 3),
(28, 'Coca 33cl Verre', 33, 1, 5, 4),
(29, 'Coca Cherry', NULL, 1, 5, 4),
(30, 'Coca Cherry 50cl', 50, 1, 5, 4),
(31, 'Coca Cola 33cl canette', 33, 1, 5, 4),
(32, 'Coca Cola pet50', 50, 1, 5, 4),
(33, 'Coca Cola zero 33cl', 33, 1, 5, 4),
(34, 'Coca Cola zero 50cl', 50, 1, 5, 4),
(35, 'Delir pik 120g', 120, 1, 6, 2),
(36, 'Desperados', 33, 1, 7, 4),
(47, 'Diabolo', NULL, 0, 50, 4),
(48, 'Don Association 0.40€', 0.4, 0, NULL, 5),
(49, 'Don Association 0.90€', 0.9, 0, NULL, 5),
(50, 'Don Association 1.50€', 1.5, 0, NULL, 5),
(51, 'Don Association 3€', 3, 0, NULL, 5),
(52, 'Dragibus 120g', 120, 1, 6, 2),
(53, 'Dragibus Soft 120g', NULL, 1, 6, 2),
(54, 'Eau Petillante 50cl', 50, 1, 8, 4),
(55, 'Fanta orange 33cl', 33, 1, 9, 4),
(56, 'Fanta Petillant 50cl', 50, 1, 9, 4),
(57, 'Fraise Tagada 120g', 120, 1, 6, 2),
(58, 'Fuze tea 50cl', 50, 1, 10, 4),
(59, 'Galopin', NULL, 0, 49, 1),
(60, 'Glace à l\'eau', NULL, 1, 11, 6),
(61, 'Glace Chocolat', NULL, 1, 11, 6),
(62, 'Glace Chocolat Pistache', NULL, 1, 11, 6),
(63, 'Glace crême brulé', NULL, 1, 11, 6),
(64, 'Glace extrême', NULL, 1, 12, 6),
(65, 'Glace Magnum', NULL, 1, 13, 6),
(66, 'Glace Magnum Chocolat', NULL, 1, 13, 6),
(67, 'Pizza Campagnarde', NULL, 0, 51, 9),
(68, 'Pizza Dauphinoise', NULL, 0, 51, 9),
(69, 'Pizza Reine', NULL, 0, 51, 9),
(70, 'Pizza Vege', NULL, 0, 51, 9),
(71, 'Pizzbolo', NULL, 0, 51, 9),
(72, 'Pop-corn Sale pot', NULL, 1, 14, 8),
(73, 'Pop-corn Grand', NULL, 1, 14, 8),
(74, 'Pop-corn Maxi', NULL, 1, 14, 8),
(75, 'Pop-corn Moyen', NULL, 1, 14, 8),
(76, 'Pop-corn Petit', NULL, 1, 14, 8),
(77, 'Pur Jus de pomme', NULL, 1, 15, 4),
(78, 'Recharge Maxi-Sceaux', NULL, 0, 14, 8),
(79, 'Recharge Grand-Sceaux', NULL, 0, 14, 8),
(80, 'Redbull 25cl', 25, 1, 16, 4),
(81, 'San Pelligrino petillant 50cl', 50, 1, 17, 4),
(82, 'Schtroumpf 120g', 120, 1, 6, 2),
(83, 'Seau Captain America', NULL, 1, 14, 7),
(84, 'Seau Stitch', NULL, 1, 14, 7),
(85, 'Seau Gladiator', NULL, 1, 14, 7),
(86, 'Skittles 45g', 45, 1, 20, 2),
(87, 'Skittles 174g', 174, 1, 20, 2),
(88, 'Skittles 318g', 318, 1, 20, 2),
(89, 'Snickers', NULL, 1, 21, 2),
(90, 'Sprite 33cl', 33, 1, 22, 4),
(91, 'Sprite 50cl', 50, 1, 22, 4),
(92, 'Sucette', NULL, 1, NULL, 2),
(93, 'Super Frite 120g', 120, 1, NULL, 9),
(94, 'thé pêche blanche ', NULL, 1, 25, 4),
(95, 'thé rafraichissante', NULL, 1, NULL, 4),
(96, 'Tourtel Twist', NULL, 1, 26, 4),
(97, 'tropico tropical', NULL, 1, 27, 4),
(98, 'Twix', NULL, 1, 28, 2),
(99, 'Vittel Kids', NULL, 1, 29, 4),
(100, 'Smarties Popup', NULL, 1, 30, 6),
(101, 'Glace Vanille', NULL, 1, 11, 6),
(102, 'Glace Vanille Fraise', NULL, 1, 11, 6),
(103, 'Glace Magnum Amandes', NULL, 1, 13, 6),
(104, 'Glace Magnum Blanc', NULL, 1, 13, 6),
(105, 'Happy cola 120g', 120, 1, 6, 2),
(106, 'Happy Life 120g', 120, 1, 6, 2),
(107, 'Hari Croco 120g', 120, 1, 6, 2),
(108, 'Hari Croco Pik', NULL, 1, 6, 2),
(109, 'Lipton Ice Tea', NULL, 1, 31, 4),
(110, 'Lipton Ice tea 33cl', 33, 1, 31, 4),
(111, 'Jus Abricot', NULL, 1, 32, 4),
(112, 'Jus Ananas', NULL, 1, 32, 4),
(113, 'Jus Orange', NULL, 1, 32, 4),
(114, 'Kinder Bueno ', NULL, 1, 33, 2),
(115, 'Kit-Kat Ball Moyen', NULL, 1, 34, 2),
(116, 'Kit-Kat Ball Grand', NULL, 1, 34, 2),
(117, 'Kit-Kat Ball Petit', NULL, 1, 34, 2),
(118, 'Kit-Kat Barre', NULL, 1, 34, 2),
(119, 'Lion', NULL, 1, 35, 2),
(120, 'M&M\'S 82g', 82, 1, 36, 2),
(121, 'M&M\'S 200g', 200, 1, 36, 2),
(122, 'M&M\'S 45g', 45, 1, 36, 2),
(123, 'M&M\'S Crispy', NULL, 1, 36, 2),
(124, 'Maltesers', NULL, 1, 37, 2),
(125, 'Mentos Fruits', NULL, 1, 38, 2),
(126, 'Mentos Menthe', NULL, 1, 38, 2),
(127, 'Mini M&M\'S', NULL, 1, 36, 2),
(128, 'Monster Energy ', NULL, 1, 39, 4),
(129, 'Monster Ultra Peachy ', NULL, 1, 39, 4),
(131, 'Oasis Mini', NULL, 1, 40, 4),
(132, 'Oasis Tropical 33cl BTE', 33, 1, 40, 4),
(133, 'Oasis Mini', NULL, 1, 40, 4),
(134, 'Oasis Tropical 33cl BTE', 33, 1, 40, 4),
(135, 'Oasis Tropical 50cl', 33, 1, 40, 4),
(137, 'Orangina 33cl', 33, 1, 41, 4),
(138, 'Orangina 50cl', 33, 1, 41, 4),
(139, 'Orangina Pik', NULL, 1, 41, 4),
(140, 'Pecheresse', NULL, 1, 42, 4),
(141, 'Perrier 33cl', 33, 1, 43, 4),
(143, 'Petillant Pomme', NULL, 1, 44, 4),
(144, 'Petillant Pomme-Citron', NULL, 1, 44, 4),
(145, 'Petillant Pomme-Fruit_Rouge', NULL, 1, 44, 4),
(146, 'Doritos Saveur Fromage', NULL, 0, 53, 2),
(147, 'Doritos Saveur Paprika', NULL, 1, 53, 2);

-- --------------------------------------------------------

--
-- Structure de la table `QuantiteStock`
--

CREATE TABLE `QuantiteStock` (
  `id_stock` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `seuil_min` int(11) DEFAULT NULL,
  `seuil_max` int(11) DEFAULT NULL,
  `stock_actuel` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `QuantiteStock`
--

INSERT INTO `QuantiteStock` (`id_stock`, `id_produit`, `seuil_min`, `seuil_max`, `stock_actuel`) VALUES
(1, 1, 61000, 110000, 93550),
(2, 2, 60000, 100000, 99680),
(3, 3, 1000, 5000, 106),
(4, 4, 30000, 60000, 58000),
(5, 5, NULL, NULL, NULL),
(6, 6, NULL, NULL, NULL),
(7, 7, NULL, NULL, NULL),
(9, 8, NULL, 90000, 81951),
(11, 9, NULL, NULL, 85),
(12, 10, NULL, NULL, 8830),
(13, 11, NULL, NULL, 99999),
(14, 12, NULL, NULL, 99065),
(15, 13, NULL, NULL, 16000),
(16, 14, NULL, NULL, 999574),
(17, 15, NULL, NULL, 2000),
(18, 16, 80000, 120000, 117000),
(19, 17, 3000, 10000, 8000),
(20, 18, NULL, NULL, 2000),
(21, 19, NULL, NULL, 9999),
(24, 20, NULL, NULL, 100000),
(25, 28, 10000, 50000, 23000),
(26, 29, 30000, 100000, 60001),
(27, 30, 30000, 100000, 16000),
(28, 31, 10000, 100000, 9000),
(29, 32, 30000, 100000, 35000),
(30, 33, 3000, 100000, 4000),
(31, 34, 30000, 100000, 31000),
(32, 35, 30000, 300000, 279000),
(33, 36, 3000, 10000, 6000),
(34, 47, 30000, 100000, 97923),
(35, 48, 30000, 100000, 86000),
(36, 49, 30000, 200000, 189000),
(37, 50, 30000, 100000, 52000),
(38, 51, 30000, 100000, 44000),
(39, 52, 30000, 200000, 178002),
(40, 53, 30000, 300000, 229000),
(41, 54, 3000, 10000, 9001),
(42, 55, 2000, 10000, 3002),
(43, 56, 30000, 100000, 64999),
(44, 57, 30000, 200000, 159009),
(45, 58, 30000, 100000, 67000),
(46, 59, 30000, 100000, 99050),
(47, 60, NULL, NULL, NULL),
(48, 61, 3000, 10000, 8000),
(49, 62, 3000, 10000, 4000),
(50, 63, 3000, 10000, 2000),
(51, 64, 3000, 10000, 10000),
(52, 65, 10000, 20000, 16000),
(53, 66, NULL, NULL, NULL),
(54, 67, NULL, NULL, NULL),
(55, 68, NULL, NULL, NULL),
(56, 69, NULL, NULL, NULL),
(57, 70, NULL, NULL, NULL),
(58, 71, NULL, NULL, NULL),
(59, 72, 30000, 100000, 65000),
(60, 73, 500000, 1000000, 795000),
(61, 74, 100000, 300000, 252000),
(62, 75, 400000, 1000000, 585000),
(63, 76, 600000, 1500000, 1350000),
(64, 77, 30000, 100000, 18000),
(65, 78, 20000, 100000, 26000),
(66, 79, 30000, 100000, 31000),
(67, 80, 20000, 100000, 25000),
(68, 81, 30000, 100000, 53000),
(69, 82, 30000, 200000, 159000),
(70, 83, 1000, 10000, 6000),
(71, 84, 1000, 10000, NULL),
(72, 85, 1000, 20000, 17000),
(73, 86, 20000, 100000, 22000),
(74, 87, 20000, 100000, 22000),
(75, 88, 30000, 100000, NULL),
(76, 89, 3000, 10000, 4000),
(77, 90, 5000, 10000, 7000),
(78, 91, 30000, 100000, 40000),
(79, 92, 500000, 1000000, 584000),
(80, 93, 30000, 100000, NULL),
(81, 94, 10000, 50000, 11000),
(82, 95, 20000, 100000, 24000),
(83, 96, 1000, 5000, 3000),
(84, 97, 30000, 100000, 63000),
(85, 98, 3000, 10000, 4000),
(86, 99, 20000, 100000, 29000),
(87, 100, 10000, 20000, 11000),
(88, 101, 2000, 5000, 3000),
(89, 102, 2000, 10000, 8000),
(90, 103, 10000, 20000, 11000),
(91, 104, 1000, 3000, 2000),
(92, 105, 50000, 150000, 144000),
(93, 106, 100000, 200000, 163000),
(94, 107, 100000, 200000, 163000),
(95, 108, 100000, 200000, NULL),
(96, 109, 3000, 10000, 6000),
(97, 110, 3000, 10000, 5000),
(98, 111, 5000, 10000, 10000),
(99, 112, 10000, 50000, 14000),
(100, 113, 5000, 15000, 10000),
(101, 114, 500, 1500, 1000),
(102, 115, 600000, 1500000, 1390000),
(103, 116, 600000, 1500000, 1410000),
(104, 117, 300000, 500000, 490000),
(105, 118, 3000, 10000, 4000),
(106, 119, 3000, 10000, 8000),
(107, 120, 3000, 10000, 10000),
(108, 121, 110000, 200000, 100000),
(109, 122, 10000, 50000, 22000),
(110, 123, 10000, 50000, 50000),
(111, 124, 30000, 100000, 60000),
(112, 125, 30000, 100000, 89000),
(113, 126, 10000, 30000, 35000),
(114, 127, 30000, 100000, 88000),
(115, 128, 10000, 20000, 15000),
(116, 129, 3000, 10000, 7000),
(118, 131, 10000, 30000, 30000),
(119, 132, 3000, 15000, 10000),
(120, 133, 1000, 20000, 12000),
(121, 134, 1000, 10000, 6000),
(122, 135, 10000, 30000, 23000),
(124, 137, 3000, 10000, 2000),
(125, 138, 10000, 30000, 26000),
(126, 139, 30000, 100000, 70000),
(127, 140, 3000, 10000, 7000),
(128, 141, 30000, 100000, 65000),
(130, 143, 30000, 100000, 86000),
(131, 144, 30000, 100000, 80000),
(132, 145, 30000, 100000, 65000);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `Addition`
--
ALTER TABLE `Addition`
  ADD PRIMARY KEY (`id_ligne`),
  ADD KEY `id_commande` (`id_commande`),
  ADD KEY `id_produit` (`id_produit`);

--
-- Index pour la table `Categorie`
--
ALTER TABLE `Categorie`
  ADD PRIMARY KEY (`id_categorie`);

--
-- Index pour la table `Commande`
--
ALTER TABLE `Commande`
  ADD PRIMARY KEY (`id_commande`);

--
-- Index pour la table `Marque`
--
ALTER TABLE `Marque`
  ADD PRIMARY KEY (`id_marque`);

--
-- Index pour la table `Mouvement`
--
ALTER TABLE `Mouvement`
  ADD PRIMARY KEY (`id_mouvement`),
  ADD KEY `id_produit` (`id_produit`);

--
-- Index pour la table `Produit`
--
ALTER TABLE `Produit`
  ADD PRIMARY KEY (`id_produit`),
  ADD KEY `id_marque` (`id_marque`),
  ADD KEY `id_categorie` (`id_categorie`);

--
-- Index pour la table `QuantiteStock`
--
ALTER TABLE `QuantiteStock`
  ADD PRIMARY KEY (`id_stock`),
  ADD UNIQUE KEY `id_produit` (`id_produit`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `Addition`
--
ALTER TABLE `Addition`
  MODIFY `id_ligne` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `Categorie`
--
ALTER TABLE `Categorie`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `Commande`
--
ALTER TABLE `Commande`
  MODIFY `id_commande` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `Marque`
--
ALTER TABLE `Marque`
  MODIFY `id_marque` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT pour la table `Mouvement`
--
ALTER TABLE `Mouvement`
  MODIFY `id_mouvement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `Produit`
--
ALTER TABLE `Produit`
  MODIFY `id_produit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT pour la table `QuantiteStock`
--
ALTER TABLE `QuantiteStock`
  MODIFY `id_stock` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Addition`
--
ALTER TABLE `Addition`
  ADD CONSTRAINT `Addition_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `Commande` (`id_commande`),
  ADD CONSTRAINT `Addition_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `Produit` (`id_produit`);

--
-- Contraintes pour la table `Mouvement`
--
ALTER TABLE `Mouvement`
  ADD CONSTRAINT `Mouvement_ibfk_1` FOREIGN KEY (`id_produit`) REFERENCES `Produit` (`id_produit`);

--
-- Contraintes pour la table `Produit`
--
ALTER TABLE `Produit`
  ADD CONSTRAINT `Produit_ibfk_1` FOREIGN KEY (`id_marque`) REFERENCES `Marque` (`id_marque`),
  ADD CONSTRAINT `Produit_ibfk_2` FOREIGN KEY (`id_categorie`) REFERENCES `Categorie` (`id_categorie`);

--
-- Contraintes pour la table `QuantiteStock`
--
ALTER TABLE `QuantiteStock`
  ADD CONSTRAINT `QuantiteStock_ibfk_1` FOREIGN KEY (`id_produit`) REFERENCES `Produit` (`id_produit`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
