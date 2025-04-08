-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 06, 2025 at 12:45 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `genuis_rental`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subject` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('new','read','replied') COLLATE utf8mb4_general_ci DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

DROP TABLE IF EXISTS `options`;
CREATE TABLE IF NOT EXISTS `options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `icon` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `name`, `price_per_day`, `description`, `icon`, `created_at`, `updated_at`) VALUES
(1, 'GPS', 5.00, 'Système de navigation GPS intégré', 'fa-location-dot', '2025-04-05 21:37:11', '2025-04-05 21:37:11'),
(2, 'Siège bébé', 10.00, 'Siège auto homologué pour enfant', 'fa-baby', '2025-04-05 21:37:11', '2025-04-05 21:37:11'),
(3, 'Assurance complète', 15.00, 'Couverture tous risques avec assistance 24/7', 'fa-shield', '2025-04-05 21:37:11', '2025-04-05 21:37:11'),
(4, 'Chaînes neige', 8.00, 'Kit de chaînes pour conditions hivernales', 'fa-snowflake', '2025-04-05 21:37:11', '2025-04-05 21:37:11'),
(5, 'Wifi portable', 7.00, 'Connexion internet 4G dans votre véhicule', 'fa-wifi', '2025-04-05 21:37:11', '2025-04-05 21:37:11'),
(6, 'Second conducteur', 12.00, 'Ajout d\'un conducteur supplémentaire', 'fa-user-plus', '2025-04-05 21:37:11', '2025-04-05 21:37:11');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------


--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reservation_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('card','paypal','bank_transfer') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transaction_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

DROP TABLE IF EXISTS `remember_tokens`;
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `vehicle_id` int DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `pickup_time` time NOT NULL,
  `return_time` time NOT NULL,
  `total_days` int NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `options_price` decimal(10,2) DEFAULT '0.00',
  `insurance_price` decimal(10,2) DEFAULT '0.00',
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','in_progress','completed','cancelled') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `payment_status` enum('pending','paid','refunded') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `vehicle_id` (`vehicle_id`),
  CONSTRAINT `vehicle_images_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `vehicle_id`, `start_date`, `end_date`, `pickup_time`, `return_time`, `total_days`, `base_price`, `options_price`, `insurance_price`, `total_price`, `status`, `payment_status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2025-04-07', '2025-04-12', '10:00:00', '18:00:00', 6, 1000.00, 0.00, 0.00, 6000.00, 'pending', 'pending', NULL, '2025-04-06 00:33:49', '2025-04-06 00:33:49'),
(2, 2, 1, '2025-04-13', '2025-04-19', '10:00:00', '18:00:00', 7, 1000.00, 0.00, 0.00, 7000.00, 'pending', 'pending', NULL, '2025-04-06 00:37:50', '2025-04-06 00:37:50'),
(3, 2, 1, '2025-04-20', '2025-05-10', '10:00:00', '18:00:00', 21, 1000.00, 0.00, 0.00, 21000.00, 'pending', 'pending', NULL, '2025-04-06 00:43:10', '2025-04-06 00:43:10');

-- --------------------------------------------------------

--
-- Table structure for table `reservation_options`
--

DROP TABLE IF EXISTS `reservation_options`;
CREATE TABLE IF NOT EXISTS `reservation_options` (
  `reservation_id` int NOT NULL,
  `option_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`reservation_id`,`option_id`),
  KEY `option_id` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `vehicle_id` int DEFAULT NULL,
  `reservation_id` int DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `vehicle_id` (`vehicle_id`),
  KEY `reservation_id` (`reservation_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `city` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `postal_code` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `driving_license` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('client','admin') COLLATE utf8mb4_general_ci DEFAULT 'client',
  `avatar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `address`, `city`, `postal_code`, `country`, `driving_license`, `role`, `avatar`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'System', 'admin@thegenuis.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, NULL, 'admin', NULL, 1, NULL, '2025-04-05 21:37:11', '2025-04-05 21:37:11'),
(2, 'Youvelie', 'Chery', 'cheryyouvelie9@gmail.com', '1234567890', '$2y$10$ROEOxXcEqKts3CruP2Fztud94Xi4cS13e5.tOLG4SbROizsqkrjA2', '128 Nw 6th Ave Hallandale  Beach', 'Florida', '33009', 'Haiti', NULL, 'client', NULL, 1, NULL, '2025-04-05 21:50:31', '2025-04-05 21:50:31');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Administrateur avec tous les droits', '2025-04-05 21:37:10', '2025-04-05 21:37:10'),
(2, 'client', 'Client standard', '2025-04-05 21:37:10', '2025-04-05 21:37:10');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `brand` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `model` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `year` int NOT NULL,
  `registration_number` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `specifications` text COLLATE utf8mb4_general_ci,
  `mileage` int DEFAULT NULL,
  `fuel_type` enum('essence','diesel','électrique','hybride') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transmission` enum('manuelle','automatique') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `seats` int DEFAULT NULL,
  `doors` int DEFAULT NULL,
  `air_conditioning` tinyint(1) DEFAULT '1',
  `image_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  
  `is_available` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `registration_number` (`registration_number`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `vehicle_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `category_id`, `brand`, `model`, `year`, `registration_number`, `price_per_day`, `description`, `specifications`, `mileage`, `fuel_type`, `transmission`, `seats`, `doors`, `air_conditioning`, `image_url`, `gallery_images`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 3, 'BMW', 'M4 Competition', 2025, 'BM4525XY', 1000.00, 'Voiture sport de luxe avec des performances exceptionnelles. Parfaite pour les amateurs de sensations fortes.', 'Moteur 6 cylindres en ligne, 510 ch, 0-100 km/h en 3.9s', 1000, 'essence', 'automatique', 4, 2, 1, '/genuis_php/assets/images/vehicles/bmw-m4.jpg', '["/genuis_php/assets/images/gallery/bmw-m4-1.jpg","/genuis_php/assets/images/gallery/bmw-m4-2.jpg","/genuis_php/assets/images/gallery/bmw-m4-3.jpg"]', 1, '2025-04-05 21:47:03', '2025-04-05 21:47:03'),
(2, 1, 'Range Rover', 'Sport', 2024, 'RR2024SP', 800.00, 'SUV luxueux combinant confort, espace et capacités tout-terrain.', 'Moteur V8, 525 ch, Suspension adaptative, Système tout-terrain intelligent', 2500, 'diesel', 'automatique', 5, 5, 1, '/genuis_php/assets/images/vehicles/range-rover-sport.jpg', '["/genuis_php/assets/images/gallery/range-sport-1.jpg","/genuis_php/assets/images/gallery/range-sport-2.jpg"]', 1, '2025-04-07 13:42:00', '2025-04-07 13:42:00'),
(3, 2, 'Mercedes', 'Classe S', 2024, 'MS2024LX', 750.00, 'Berline haut de gamme incarnant le luxe et le confort absolu.', 'Moteur V6, 435 ch, Conduite semi-autonome, Intérieur cuir premium', 3000, 'hybride', 'automatique', 5, 4, 1, '/genuis_php/assets/images/vehicles/mercedes-s.jpg', '["/genuis_php/assets/images/gallery/mercedes-s-1.jpg","/genuis_php/assets/images/gallery/mercedes-s-2.jpg"]', 1, '2025-04-07 13:42:00', '2025-04-07 13:42:00'),
(4, 4, 'Peugeot', '208', 2024, 'PG2024CT', 200.00, 'Citadine moderne, économique et agile pour la ville.', 'Moteur 1.2L, 100 ch, Système d''aide au stationnement, Écran tactile 10"', 5000, 'essence', 'manuelle', 5, 5, 1, '/genuis_php/assets/images/vehicles/peugeot-208.jpg', '["/genuis_php/assets/images/gallery/peugeot-208-1.jpg","/genuis_php/assets/images/gallery/peugeot-208-2.jpg"]', 1, '2025-04-07 13:42:00', '2025-04-07 13:42:00'),
(5, 5, 'Renault', 'Trafic', 2024, 'RT2024UT', 300.00, 'Utilitaire spacieux et pratique, idéal pour les déménagements.', 'Volume de chargement 8m³, Charge utile 1200kg, Radar de recul', 8000, 'diesel', 'manuelle', 3, 4, 1, '/genuis_php/assets/images/vehicles/renault-trafic.jpg', '["/genuis_php/assets/images/gallery/renault-trafic-1.jpg","/genuis_php/assets/images/gallery/renault-trafic-2.jpg"]', 1, '2025-04-07 13:42:00', '2025-04-07 13:42:00'),
(6, 3, 'Porsche', '911 GT3', 2024, 'PO2024GT', 1200.00, 'Voiture de sport légendaire offrant des performances exceptionnelles.', 'Moteur 6 cylindres à plat, 510 ch, 0-100 km/h en 3.4s', 1500, 'essence', 'automatique', 2, 2, 1, '/genuis_php/assets/images/vehicles/porsche-911.jpg', '["/genuis_php/assets/images/gallery/porsche-911-1.jpg","/genuis_php/assets/images/gallery/porsche-911-2.jpg"]', 1, '2025-04-07 13:42:00', '2025-04-07 13:42:00'),
(7, 1, 'Audi', 'Q7', 2024, 'AQ2024Q7', 600.00, 'SUV familial premium avec technologie de pointe.', 'Moteur V6 TDI, 286 ch, 7 places, Système audio Bang & Olufsen', 4000, 'diesel', 'automatique', 7, 5, 1, '/genuis_php/assets/images/vehicles/audi-q7.jpg', '["/genuis_php/assets/images/gallery/audi-q7-1.jpg","/genuis_php/assets/images/gallery/audi-q7-2.jpg"]', 1, '2025-04-07 13:42:00', '2025-04-07 13:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_categories`
--

DROP TABLE IF EXISTS `vehicle_categories`;
CREATE TABLE IF NOT EXISTS `vehicle_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `image_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_categories`
--

INSERT INTO `vehicle_categories` (`id`, `name`, `description`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'SUV', 'Véhicules spacieux et polyvalents, parfaits pour les familles et les longs trajets', './assets/images/categories/suv.jpg', '2025-04-05 21:37:10', '2025-04-05 21:37:10'),
(2, 'Berline', 'Voitures élégantes et confortables, idéales pour les déplacements professionnels', './assets/images/categories/berline.jpg', '2025-04-05 21:37:10', '2025-04-05 21:37:10'),
(3, 'Sport', 'Véhicules performants et design, pour une expérience de conduite unique', './assets/images/categories/sport.jpg', '2025-04-05 21:37:10', '2025-04-05 21:37:10'),
(4, 'Citadine', 'Petites voitures agiles, parfaites pour la ville', './assets/images/categories/citadine.jpg', '2025-04-05 21:37:10', '2025-04-05 21:37:10'),
(5, 'Utilitaire', 'Véhicules pratiques pour vos déménagements et transports', './assets/images/categories/utilitaire.jpg', '2025-04-05 21:37:10', '2025-04-05 21:37:10');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_images`
--

--
-- Table structure for table `vehicle_gallery`
--

DROP TABLE IF EXISTS `vehicle_gallery`;
CREATE TABLE IF NOT EXISTS `vehicle_gallery` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehicle_id` int NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` int DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vehicle_id` (`vehicle_id`),
  CONSTRAINT `vehicle_gallery_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Insert gallery images from vehicles table
--
INSERT INTO `vehicle_gallery` (`vehicle_id`, `image_url`, `sort_order`)
SELECT v.id, TRIM('"' FROM JSON_UNQUOTE(JSON_EXTRACT(v.gallery_images, CONCAT('$[', numbers.n, ']')))) as image_url, numbers.n
FROM vehicles v
CROSS JOIN (
    SELECT 0 as n UNION SELECT 1 UNION SELECT 2
) numbers
WHERE JSON_EXTRACT(v.gallery_images, CONCAT('$[', numbers.n, ']')) IS NOT NULL;

--
-- Table structure for table `vehicle_images`
--

DROP TABLE IF EXISTS `vehicle_images`;
CREATE TABLE IF NOT EXISTS `vehicle_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehicle_id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vehicle_id` (`vehicle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
