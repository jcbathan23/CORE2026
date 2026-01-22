-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 22, 2026 at 12:04 PM
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
-- Database: `newcore2`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_service_provider`
--

DROP TABLE IF EXISTS `active_service_provider`;
CREATE TABLE IF NOT EXISTS `active_service_provider` (
  `provider_id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `account_type` int NOT NULL DEFAULT '3',
  `contact_person` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `services` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `iso_certified` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `business_permit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_profile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_approved` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `active_service_provider`
--

INSERT INTO `active_service_provider` (`provider_id`, `company_name`, `email`, `password`, `account_type`, `contact_person`, `contact_number`, `address`, `services`, `iso_certified`, `business_permit`, `company_profile`, `date_approved`, `status`) VALUES
(31, 'FLASH EXPRESS', 'flashexpressincorporated@gmail.com', 'flashexpress', 3, 'Kurt Cobain', '09315454613', 'Unit 304 Dona Julita Bldg., 112 Kamuning Road, Quezon City 1103', 'land', 'yes', 'businesspermitsample.png', 'flash.png', '2025-09-22 03:43:52', 'Active'),
(32, 'ABC Freight Express', 'abcfreightexpress@gmail.com', 'abc', 3, 'April Joy Consigna', '09107993740', 'asdasd', 'land', 'yes', 'businesspermitsample.png', 'companyprofle.jpg', '2025-09-22 08:30:05', 'Active'),
(33, 'AVRIL FREIGHT EXPRESS', 'apriljoyconsigna@gmail.com', 'aprik', 3, 'April Joy Consigna', '09275374767', 'jk', 'land', 'yes', 'Messenger_creation_A8DE8FC8-FDDF-4F9D-9DCB-030D795B7BEF.jpeg', 'metabase.png', '2025-09-22 08:35:17', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `admin_list`
--

DROP TABLE IF EXISTS `admin_list`;
CREATE TABLE IF NOT EXISTS `admin_list` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `account_type` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_list`
--

INSERT INTO `admin_list` (`email`, `password`, `account_type`) VALUES
('bathanjc23@gmail.com', '123456', 1);

-- --------------------------------------------------------

--
-- Table structure for table `admin_profiles`
--

DROP TABLE IF EXISTS `admin_profiles`;
CREATE TABLE IF NOT EXISTS `admin_profiles` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_profiles`
--

INSERT INTO `admin_profiles` (`email`, `name`, `phone`, `avatar`, `updated_at`) VALUES
('bathanjc23@gmail.com', 'AGHIK', '', '1767326890_avatar.png', '2026-01-02 04:08:10');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `customer_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `customer_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `origin_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `destination_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `origin_lat` double DEFAULT NULL,
  `origin_lng` double DEFAULT NULL,
  `destination_lat` double DEFAULT NULL,
  `destination_lng` double DEFAULT NULL,
  `origin_id` int DEFAULT NULL,
  `destination_id` int DEFAULT NULL,
  `carrier_type` enum('land','air','sea') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cargo_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'general',
  `weight` decimal(10,2) DEFAULT NULL,
  `volume` decimal(10,2) DEFAULT NULL,
  `dimensions` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `special_instructions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `estimated_cost` decimal(10,2) DEFAULT NULL,
  `estimated_transit_time` int DEFAULT NULL,
  `status` enum('pending','confirmed','in_transit','delivered','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `provider_id` int DEFAULT NULL,
  `route_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `booking_reference` (`booking_reference`),
  KEY `fk_bookings_provider` (`provider_id`),
  KEY `fk_bookings_route` (`route_id`),
  KEY `idx_bookings_status` (`status`),
  KEY `idx_bookings_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `booking_reference`, `customer_name`, `customer_email`, `customer_phone`, `origin_address`, `destination_address`, `origin_lat`, `origin_lng`, `destination_lat`, `destination_lng`, `origin_id`, `destination_id`, `carrier_type`, `cargo_type`, `weight`, `volume`, `dimensions`, `special_instructions`, `estimated_cost`, `estimated_transit_time`, `status`, `provider_id`, `route_id`, `created_at`, `updated_at`) VALUES
(5, 'BK202671758', 'Test Customer 70', 'Test@gmail.com', '091232137873821', 'Manila', 'Cebu', 14.5995, 120.9842, 10.3157, 123.8854, NULL, NULL, 'land', 'general', 11.00, NULL, '', '', 13712.00, 742, 'pending', 32, 71, '2026-01-22 16:24:16', '2026-01-22 17:59:12'),
(7, 'BK202670288', 'Unknown Supplier', '', '', 'Metro Manila', 'Caloocan City', NULL, NULL, NULL, NULL, NULL, NULL, 'land', 'general', 15.00, NULL, '', 'Ref: SHIP-6971F756B2AB7', NULL, NULL, 'pending', NULL, NULL, '2026-01-22 18:13:23', '2026-01-22 18:13:23'),
(8, 'BK202660690', 'Unknown Supplier', '', '', 'Manila, Metro Manila, Philippines', 'Taguig, Metro Manila, Philippines', NULL, NULL, NULL, NULL, NULL, NULL, 'land', 'general', 20.00, NULL, '', 'Ref: SHIP-6971FB8072C88', NULL, NULL, 'pending', NULL, NULL, '2026-01-22 18:31:09', '2026-01-22 18:31:09'),
(9, 'BK202649937', 'Unknown Supplier', '', '', 'Quezon City, Metro Manila, Philippines', 'Caloocan, Metro Manila, Philippines', NULL, NULL, NULL, NULL, NULL, NULL, 'land', 'general', 3.00, NULL, '', 'Ref: SHIP-6971FB8587490', NULL, NULL, 'pending', NULL, NULL, '2026-01-22 18:31:14', '2026-01-22 18:31:14');

-- --------------------------------------------------------

--
-- Table structure for table `calculated_rates`
--

DROP TABLE IF EXISTS `calculated_rates`;
CREATE TABLE IF NOT EXISTS `calculated_rates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `route_id` int NOT NULL,
  `provider_id` int NOT NULL,
  `carrier_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `unit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` decimal(10,2) DEFAULT '0.00',
  `total_rate` decimal(12,2) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','approved','rejected','calculating','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `ai_confidence_score` int DEFAULT NULL COMMENT 'AI confidence percentage (0-100)',
  PRIMARY KEY (`id`),
  KEY `fk_cr_provider` (`provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `freight_rates`
--

DROP TABLE IF EXISTS `freight_rates`;
CREATE TABLE IF NOT EXISTS `freight_rates` (
  `rate_id` int NOT NULL AUTO_INCREMENT,
  `provider_id` int NOT NULL,
  `mode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `distance_range` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `weight_range` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `unit` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pending','Accepted','Rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  PRIMARY KEY (`rate_id`),
  KEY `provider_id` (`provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `freight_rates`
--

INSERT INTO `freight_rates` (`rate_id`, `provider_id`, `mode`, `distance_range`, `weight_range`, `rate`, `unit`, `created_at`, `status`) VALUES
(48, 31, 'land', '0-2000km', '0-2000kg', 50.00, 'per kg', '2025-09-22 10:10:53', 'Accepted');

-- --------------------------------------------------------

--
-- Table structure for table `login_otps`
--

DROP TABLE IF EXISTS `login_otps`;
CREATE TABLE IF NOT EXISTS `login_otps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `otp` varchar(16) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `expires_at` (`expires_at`),
  KEY `used` (`used`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login_otps`
--

INSERT INTO `login_otps` (`id`, `email`, `otp`, `expires_at`, `used`, `created_at`) VALUES
(1, 'bathanjc23@gmail.com', '923400', '2026-01-02 03:59:22', 0, '2026-01-02 03:54:22'),
(3, 'bathanjc23@gmail.com', '988630', '2026-01-02 04:06:38', 0, '2026-01-02 04:01:38'),
(4, 'bathanjc23@gmail.com', '455549', '2026-01-02 04:09:17', 1, '2026-01-02 04:04:17'),
(5, 'bathanjc23@gmail.com', '194938', '2026-01-06 10:57:13', 1, '2026-01-06 10:52:13'),
(6, 'bathanjc23@gmail.com', '858666', '2026-01-06 12:38:41', 0, '2026-01-06 12:33:41'),
(7, 'bathanjc23@gmail.com', '525878', '2026-01-06 12:38:59', 1, '2026-01-06 12:33:59'),
(8, 'bathanjc23@gmail.com', '306424', '2026-01-11 10:54:36', 1, '2026-01-11 10:49:36'),
(9, 'bathanjc23@gmail.com', '598602', '2026-01-11 11:48:54', 1, '2026-01-11 11:43:54'),
(10, 'bathanjc23@gmail.com', '157486', '2026-01-12 11:12:35', 1, '2026-01-12 11:07:35'),
(11, 'bathanjc23@gmail.com', '477409', '2026-01-13 11:09:01', 1, '2026-01-13 11:04:01'),
(12, 'bathanjc23@gmail.com', '708609', '2026-01-14 11:13:02', 1, '2026-01-14 11:08:02'),
(13, 'bathanjc23@gmail.com', '830934', '2026-01-14 11:51:54', 1, '2026-01-14 11:46:54'),
(14, 'bathanjc23@gmail.com', '599753', '2026-01-14 12:01:35', 1, '2026-01-14 11:56:35'),
(15, 'bathanjc23@gmail.com', '663654', '2026-01-15 02:32:26', 1, '2026-01-15 02:27:26'),
(16, 'bathanjc23@gmail.com', '141727', '2026-01-15 02:53:03', 1, '2026-01-15 02:48:03'),
(17, 'bathanjc23@gmail.com', '355866', '2026-01-15 02:58:58', 1, '2026-01-15 02:53:58'),
(18, 'bathanjc23@gmail.com', '727498', '2026-01-15 05:30:07', 1, '2026-01-15 05:25:07'),
(19, 'bathanjc23@gmail.com', '539629', '2026-01-15 05:58:58', 1, '2026-01-15 05:53:58'),
(20, 'bathanjc23@gmail.com', '041979', '2026-01-15 06:06:00', 1, '2026-01-15 06:01:00'),
(21, 'bathanjc23@gmail.com', '413094', '2026-01-15 10:08:53', 1, '2026-01-15 10:03:53'),
(22, 'bathanjc23@gmail.com', '860015', '2026-01-15 10:10:44', 1, '2026-01-15 10:05:44'),
(23, 'bathanjc23@gmail.com', '811059', '2026-01-15 10:26:23', 1, '2026-01-15 10:21:23'),
(24, 'bathanjc23@gmail.com', '344045', '2026-01-15 13:25:51', 1, '2026-01-15 13:20:51'),
(25, 'bathanjc23@gmail.com', '702243', '2026-01-15 13:43:37', 1, '2026-01-15 13:38:37'),
(26, 'bathanjc23@gmail.com', '373470', '2026-01-15 13:55:02', 1, '2026-01-15 13:50:02'),
(27, 'bathanjc23@gmail.com', '008711', '2026-01-15 14:09:23', 1, '2026-01-15 14:04:23'),
(28, 'bathanjc23@gmail.com', '443374', '2026-01-15 14:10:41', 1, '2026-01-15 14:05:41'),
(29, 'bathanjc23@gmail.com', '617270', '2026-01-15 14:48:03', 1, '2026-01-15 14:43:03'),
(30, 'bathanjc23@gmail.com', '675856', '2026-01-16 11:00:49', 1, '2026-01-16 10:55:49'),
(31, 'bathanjc23@gmail.com', '447362', '2026-01-20 14:13:04', 1, '2026-01-20 14:08:04'),
(32, 'bathanjc23@gmail.com', '108577', '2026-01-20 15:03:03', 1, '2026-01-20 14:58:03'),
(33, 'bathanjc23@gmail.com', '118736', '2026-01-20 15:18:12', 1, '2026-01-20 15:13:12'),
(34, 'bathanjc23@gmail.com', '761742', '2026-01-20 15:22:21', 1, '2026-01-20 15:17:21'),
(35, 'bathanjc23@gmail.com', '410146', '2026-01-21 05:35:42', 1, '2026-01-21 05:30:42'),
(36, 'bathanjc23@gmail.com', '200387', '2026-01-21 12:23:45', 1, '2026-01-21 12:18:45'),
(37, 'bathanjc23@gmail.com', '988875', '2026-01-22 02:11:04', 1, '2026-01-22 02:06:04'),
(38, 'bathanjc23@gmail.com', '726571', '2026-01-22 05:43:36', 1, '2026-01-22 05:38:36'),
(39, 'bathanjc23@gmail.com', '667165', '2026-01-22 07:42:52', 1, '2026-01-22 07:37:52'),
(40, 'bathanjc23@gmail.com', '961540', '2026-01-22 08:24:55', 1, '2026-01-22 08:19:55'),
(41, 'bathanjc23@gmail.com', '628030', '2026-01-22 08:58:12', 1, '2026-01-22 08:53:12'),
(42, 'bathanjc23@gmail.com', '200523', '2026-01-22 09:25:08', 1, '2026-01-22 09:20:08'),
(43, 'bathanjc23@gmail.com', '407841', '2026-01-22 09:29:22', 1, '2026-01-22 09:24:22'),
(44, 'bathanjc23@gmail.com', '899223', '2026-01-22 10:26:45', 1, '2026-01-22 10:21:45');

-- --------------------------------------------------------

--
-- Table structure for table `network_points`
--

DROP TABLE IF EXISTS `network_points`;
CREATE TABLE IF NOT EXISTS `network_points` (
  `point_id` int NOT NULL AUTO_INCREMENT,
  `point_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `point_type` enum('Port','Airport','Warehouse','Terminal') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`point_id`)
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `network_points`
--

INSERT INTO `network_points` (`point_id`, `point_name`, `point_type`, `country`, `city`, `latitude`, `longitude`, `status`, `created_at`, `updated_at`) VALUES
(86, 'Batangas Port', 'Port', 'Philippines', 'Batangas Port', 13.753794, 121.042717, 'Active', '2025-09-22 07:27:20', '2026-01-11 20:07:26'),
(92, 'General Santos Airport', 'Airport', 'Philippines', 'General Santos Airport', 6.063702, 125.098489, 'Active', '2025-09-22 17:31:45', '2026-01-11 20:07:31'),
(93, 'Ninoy Aquino International Airport', 'Airport', 'Philippines', 'Ninoy Aquino International Airport', 14.512302, 121.021886, 'Active', '2025-09-22 17:32:54', '2026-01-11 20:07:37'),
(95, 'Manila Port', 'Port', 'Philippines', 'Port Area, Manila', 14.5869, 120.965, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(96, 'Subic Bay Port', 'Port', 'Philippines', 'Olongapo, Zambales and Morong, Bataan', 14.8294, 120.2828, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(97, 'Abra de Ilog Port', 'Port', 'Philippines', 'Abra de Ilog, Occidental Mindoro', 13.4386, 120.7215, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(98, 'Ambulong Port', 'Port', 'Philippines', 'Magdiwang, Romblon', 12.5792, 122.5386, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(100, 'Balancan Port', 'Port', 'Philippines', 'Mogpog, Marinduque', 13.4767, 121.9189, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(101, 'Calapan Port', 'Port', 'Philippines', 'Calapan City', 13.4108, 121.1806, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(102, 'Cawit Port', 'Port', 'Philippines', 'Boac, Marinduque', 13.4453, 121.8392, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(103, 'Currimao Port', 'Port', 'Philippines', 'Currimao, Ilocos Norte', 18.0194, 120.4864, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(104, 'Legazpi Port', 'Port', 'Philippines', 'Legazpi, Albay', 13.1391, 123.735, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(105, 'Lucena Port', 'Port', 'Philippines', 'Lucena City', 13.9316, 121.6171, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(106, 'Odiongan/Pocoy Port', 'Port', 'Philippines', 'Odiongan, Romblon', 12.4039, 121.9847, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(107, 'Puerto Princesa Port', 'Port', 'Philippines', 'Puerto Princesa', 9.7392, 118.7353, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(108, 'Romblon Port', 'Port', 'Philippines', 'Romblon, Romblon', 12.5753, 122.2706, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(109, 'Dangay Port', 'Port', 'Philippines', 'Roxas, Oriental Mindoro', 12.5854, 121.5042, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(110, 'Caminawit Port', 'Port', 'Philippines', 'San Jose, Occidental Mindoro', 12.3546, 121.0572, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(111, 'Sual Port', 'Port', 'Philippines', 'Sual, Pangasinan', 16.0639, 119.9508, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(112, 'Banago Port', 'Port', 'Philippines', 'Bacolod', 10.6874, 122.9455, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(113, 'Banate Port', 'Port', 'Philippines', 'Banate, Iloilo', 11.0398, 122.7775, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(114, 'Bato Port', 'Port', 'Philippines', 'Samboan, Cebu', 9.4606, 123.3047, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(115, 'Baybay Port', 'Port', 'Philippines', 'Baybay, Leyte', 10.6782, 124.8013, 'Active', '2026-01-11 19:41:32', '2026-01-11 20:30:13'),
(116, 'BREDCO Port', 'Port', 'Philippines', 'Bacolod', 10.6762836, 122.9513786, 'Active', '2026-01-11 19:41:32', '2026-01-14 20:59:20'),
(117, 'Calbayog Port', 'Port', 'Philippines', 'Calbayog, Samar', 12.1490458, 124.5375762, 'Active', '2026-01-11 19:41:32', '2026-01-14 20:59:21'),
(118, 'Catbalogan Port', 'Port', 'Philippines', 'Catbalogan, Samar', 11.7753199, 124.8829549, 'Active', '2026-01-11 19:41:32', '2026-01-14 20:59:26'),
(119, 'Caticlan Port', 'Port', 'Philippines', 'Malay, Aklan', 11.9000941, 121.9099, 'Active', '2026-01-11 19:41:32', '2026-01-14 20:59:32'),
(120, 'Cebu Port', 'Port', 'Philippines', 'Cebu City', 10.2934946, 123.9018183, 'Active', '2026-01-11 19:41:32', '2026-01-14 20:59:34'),
(121, 'Danao Port', 'Port', 'Philippines', 'Danao, Cebu', 10.5194879, 124.0271297, 'Active', '2026-01-11 19:41:32', '2026-01-14 20:59:36'),
(122, 'Dumaguete Port', 'Port', 'Philippines', 'Dumaguete, Negros Oriental', 9.3054777, 123.3080446, 'Active', '2026-01-11 19:41:32', '2026-01-14 20:59:38'),
(123, 'Dumagit Port', 'Port', 'Philippines', 'New Washington, Aklan', 11.6508856, 122.4320224, 'Active', '2026-01-11 19:41:32', '2026-01-14 20:59:43'),
(124, 'Dumangas Port', 'Port', 'Philippines', 'Dumangas, Iloilo', 10.8215809, 122.712398, 'Active', '2026-01-11 19:41:32', '2026-01-14 20:59:48'),
(125, 'Escalante Port', 'Port', 'Philippines', 'Escalante, Negros Occidental', 10.8412587, 123.499306, 'Active', '2026-01-11 19:41:32', '2026-01-14 20:59:51'),
(126, 'Getafe Port', 'Port', 'Philippines', 'Getafe, Bohol', 10.1497111, 124.1535145, 'Active', '2026-01-11 19:41:32', '2026-01-14 20:59:52'),
(127, 'Guihulngan Port', 'Port', 'Philippines', 'Guihulngan, Negros Oriental', 10.1195972, 123.273871, 'Active', '2026-01-11 19:41:33', '2026-01-14 20:59:54'),
(128, 'Hagnaya Port', 'Port', 'Philippines', 'San Remigio, Cebu', 11.0835479, 123.9366262, 'Active', '2026-01-11 19:41:33', '2026-01-14 20:59:56'),
(129, 'Hilongos Port', 'Port', 'Philippines', 'Hilongos, Leyte', 10.3733001, 124.7488169, 'Active', '2026-01-11 19:41:33', '2026-01-14 20:59:58'),
(130, 'Iloilo Port', 'Port', 'Philippines', 'Iloilo City', 10.6932884, 122.5732604, 'Active', '2026-01-11 19:41:33', '2026-01-14 20:59:59'),
(131, 'Jagna Port', 'Port', 'Philippines', 'Jagna, Bohol', 9.6501914, 124.3661633, 'Active', '2026-01-11 19:41:33', '2026-01-14 21:00:00'),
(132, 'Jordan Port', 'Port', 'Philippines', 'Jordan, Guimaras', 10.5959639, 122.5877929, 'Active', '2026-01-11 19:41:33', '2026-01-14 21:00:02'),
(133, 'Larena Port', 'Port', 'Philippines', 'Larena, Siquijor', 9.2488946, 123.5910019, 'Active', '2026-01-11 19:41:33', '2026-01-14 21:00:06'),
(134, 'Liloan/Santander Port', 'Port', 'Philippines', 'Santander, Cebu', 9.4170689, 123.3351935, 'Active', '2026-01-11 19:41:33', '2026-01-14 21:00:09'),
(135, 'Liloan Port', 'Port', 'Philippines', 'Liloan, Southern Leyte', 10.1563177, 125.117794, 'Active', '2026-01-11 19:41:33', '2026-01-14 21:00:13'),
(136, 'Maasin Port', 'Port', 'Philippines', 'Maasin, Southern Leyte', 10.1325061, 124.8385147, 'Active', '2026-01-11 19:41:33', '2026-01-14 21:00:18'),
(137, 'Naval Port', 'Port', 'Philippines', 'Naval, Biliran', 11.56179, 124.3965267, 'Active', '2026-01-11 19:41:33', '2026-01-14 21:00:23'),
(138, 'Ormoc Port', 'Port', 'Philippines', 'Ormoc', 11.0052935, 124.6090753, 'Active', '2026-01-11 19:41:33', '2026-01-14 21:00:31'),
(139, 'Palompon Port', 'Port', 'Philippines', 'Palompon, Leyte', 11.0483792, 124.3825547, 'Active', '2026-01-11 19:41:33', '2026-01-14 21:00:34'),
(140, 'Pulupandan Port', 'Port', 'Philippines', 'Pulupandan, Negros Occidental', 10.5192005, 122.8034563, 'Active', '2026-01-11 19:41:33', '2026-01-14 21:00:39'),
(141, 'Roxas City/Culasi Port', 'Port', 'Philippines', 'Roxas, Capiz', 11.5895171, 122.7500577, 'Active', '2026-01-11 19:41:33', '2026-01-14 21:00:56'),
(142, 'San Carlos Port', 'Port', 'Philippines', 'San Carlos, Negros Occidental', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(143, 'Sibulan Port', 'Port', 'Philippines', 'Sibulan, Negros Oriental', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(144, 'Tacloban Port', 'Port', 'Philippines', 'Tacloban', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(145, 'Tabuelan Port', 'Port', 'Philippines', 'Tabuelan, Cebu', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(146, 'Talibon Port', 'Port', 'Philippines', 'Talibon, Bohol', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(147, 'Tagbilaran Port', 'Port', 'Philippines', 'Tagbilaran, Bohol', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(148, 'Tampi Port', 'Port', 'Philippines', 'San Jose, Negros Oriental', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(149, 'Tandaya Port', 'Port', 'Philippines', 'Amlan, Negros Oriental', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(150, 'Toledo Port', 'Port', 'Philippines', 'Toledo, Cebu', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(151, 'Tubigon Port', 'Port', 'Philippines', 'Tubigon, Bohol', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(152, 'Ubay Port', 'Port', 'Philippines', 'Ubay, Bohol', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(153, 'Benoni Port', 'Port', 'Philippines', 'Mahinog, Camiguin', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(154, 'Davao Port', 'Port', 'Philippines', 'Davao City', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(155, 'Dipolog Port', 'Port', 'Philippines', 'Dipolog, Zamboanga del Norte', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(156, 'Zamboanga Port', 'Port', 'Philippines', 'Zamboanga City', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(157, 'Cagayan de Oro Port', 'Port', 'Philippines', 'Cagayan de Oro', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(158, 'General Santos Port', 'Port', 'Philippines', 'General Santos', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(159, 'Iligan Port', 'Port', 'Philippines', 'Iligan', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(160, 'Jimenez Port', 'Port', 'Philippines', 'Jimenez, Misamis Occidental', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(161, 'Mukas Port', 'Port', 'Philippines', 'Kalumbugan, Lanao del Norte', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(162, 'Nasipit/Butuan Port', 'Port', 'Philippines', 'Nasipit, Agusan del Norte', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(163, 'Oroquieta Port', 'Port', 'Philippines', 'Oroquieta, Misamis Occidental', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(164, 'Ozamiz Port', 'Port', 'Philippines', 'Ozamiz City', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(165, 'Pagadian Port', 'Port', 'Philippines', 'Pagadian, Zamboanga del Sur', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(166, 'Plaridel Port', 'Port', 'Philippines', 'Plaridel, Misamis Occidental', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(167, 'Dapitan Port', 'Port', 'Philippines', 'Dapitan, Zamboanga del Norte', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(168, 'San Jose Port', 'Port', 'Philippines', 'San Jose, Dinagat Islands', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(169, 'Surigao Port', 'Port', 'Philippines', 'Surigao City', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(170, 'Dapa Port', 'Port', 'Philippines', 'Dapa, Siargao', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(171, 'Jubang Port', 'Port', 'Philippines', 'Dapa, Siargao', NULL, NULL, 'Active', '2026-01-11 19:41:33', '2026-01-11 19:41:33'),
(172, 'Batangas Port', 'Port', 'Philippines', 'Batangas City', 13.7565, 121.0583, 'Active', '2026-01-11 20:54:17', '2026-01-11 20:54:17'),
(173, 'NXLP NLI – G Warehouse (LIMA Tech Center)', 'Warehouse', 'Philippines', 'Lipa / Malvar, Batangas', 13.9511, 121.1636, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(174, 'NXLP NLI – C & D Warehouse (LIMA Tech Center)', 'Warehouse', 'Philippines', 'Malvar, Batangas', 13.9385, 121.161, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(175, 'NXLP CEZ – 1 Warehouse (Cavite Economic Zone)', 'Warehouse', 'Philippines', 'Rosario, Cavite', 14.3973, 120.8856, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(176, 'NXLP PTC Warehouse (People\'s Tech Complex)', 'Warehouse', 'Philippines', 'Carmona, Cavite', 14.2932, 120.937, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(177, 'NXLP MEZ – 1 Main Warehouse', 'Warehouse', 'Philippines', 'Lapu-Lapu City, Cebu (Mactan Econ Zone)', 10.3157, 123.9628, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(178, 'NXLP MEZ – 2 Warehouse', 'Warehouse', 'Philippines', 'Lapu-Lapu City, Cebu', 10.3135, 123.962, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(179, 'LTI Phase 2 Orient Warehouse', 'Warehouse', 'Philippines', 'Biñan, Laguna (Laguna Technopark)', 14.3306, 121.1364, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(180, 'NXLP LTI Main Warehouse', 'Warehouse', 'Philippines', 'Biñan, Laguna', 14.331, 121.136, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(181, 'NXLP LTI Annex Warehouse', 'Warehouse', 'Philippines', 'Biñan, Laguna', 14.332, 121.137, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(182, 'NXLP LTI Phase 6A Warehouse', 'Warehouse', 'Philippines', 'Biñan, Laguna', 14.333, 121.138, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(183, 'Air Cargo Division / Ocean Cargo Division (FTI Warehouses)', 'Warehouse', 'Philippines', 'Taguig City, Metro Manila', 14.5176, 121.0493, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(184, 'Logistics Division (FT12)', 'Warehouse', 'Philippines', 'Taguig / Paranaque, Metro Manila', 14.475, 121.02, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(185, 'Clark Warehouse Satellite Office', 'Warehouse', 'Philippines', 'Clark Freeport Zone, Pampanga', 15.1819, 120.5583, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(186, 'ISLA Logistics Warehouse – Manila', 'Warehouse', 'Philippines', 'Manila, Luzon', 14.5995, 120.9842, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(187, 'ISLA Logistics Warehouse – Clark', 'Warehouse', 'Philippines', 'Clark, Pampanga', 15.1859, 120.56, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(188, 'ISLA Logistics Warehouse – Cebu', 'Warehouse', 'Philippines', 'Cebu City / Mandaue', 10.3157, 123.9175, 'Active', '2026-01-11 20:58:20', '2026-01-11 20:58:20'),
(189, 'Manila', 'Warehouse', 'Philippines', '', 14.5995, 120.9842, 'Active', '2026-01-22 17:59:12', '2026-01-22 17:59:12'),
(190, 'Cebu', 'Warehouse', 'Philippines', '', 10.3157, 123.8854, 'Active', '2026-01-22 17:59:12', '2026-01-22 17:59:12');

-- --------------------------------------------------------

--
-- Table structure for table `newaccounts`
--

DROP TABLE IF EXISTS `newaccounts`;
CREATE TABLE IF NOT EXISTS `newaccounts` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `account_type` int DEFAULT '2',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newaccounts`
--

INSERT INTO `newaccounts` (`user_id`, `email`, `password`, `account_type`) VALUES
(11, 'leonardgaro@gmail.com', 'garo', 2),
(12, 'kenji@gmail.com', 'kenji', 2),
(13, 'kenji@gmail.com', 'ken', 2),
(15, 'bathanjc@gmail.com', '123456', 2);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'info',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `message`, `type`, `link`, `is_read`, `created_at`) VALUES
(8, 'Your freight rate #10 has been Accepted.', 'service_provider', 'rates_management.php?rate_id=10', 1, '2025-09-13 11:06:13'),
(9, 'Freight rate #10 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-13 11:06:13'),
(10, 'Your freight rate #9 has been Rejected.', 'service_provider', 'rates_management.php?rate_id=9', 1, '2025-09-13 11:06:14'),
(11, 'Freight rate #9 has been Rejected.', 'admin', 'rates_management.php', 1, '2025-09-13 11:06:14'),
(12, 'Freight rate #11 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-13 11:09:36'),
(13, 'New Service Provider Registered: DOWNTOWN QC', 'info', 'pending_providers.php', 1, '2025-09-13 19:43:23'),
(14, 'Freight rate #12 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-13 20:27:40'),
(15, 'Schedule ID 13 has been delayed.', 'info', 'provider_schedules.php', 1, '2025-09-13 21:23:14'),
(16, 'Schedule ID 13 has been completed.', 'info', 'provider_schedules.php', 1, '2025-09-13 21:23:28'),
(17, 'Service Provider approved: DOWNTOWN QC', 'service_provider', 'active_providers.php', 1, '2025-09-14 16:11:41'),
(18, 'Schedule ID 14 has been delayed.', 'info', 'provider_schedules.php', 1, '2025-09-18 12:31:17'),
(19, 'Schedule ID 14 has been completed.', 'info', 'provider_schedules.php', 1, '2025-09-18 12:31:27'),
(20, 'Freight rate #14 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-19 07:12:32'),
(21, 'Schedule ID 15 has been delayed.', 'info', 'provider_schedules.php', 1, '2025-09-19 07:23:31'),
(22, 'Schedule ID 15 has been completed.', 'info', 'provider_schedules.php', 1, '2025-09-19 07:23:33'),
(23, 'Schedule ID 16 has been delayed.', 'info', 'provider_schedules.php', 1, '2025-09-19 07:26:00'),
(24, 'Schedule ID 16 has been completed.', 'info', 'provider_schedules.php', 1, '2025-09-19 07:26:00'),
(25, 'Schedule ID 17 has been delayed.', 'info', 'schedule_routes.php', 1, '2025-09-19 07:37:03'),
(26, 'Schedule ID 17 has been completed.', 'info', 'schedule_routes.php', 1, '2025-09-19 07:37:05'),
(27, 'Schedule ID 18 has been delayed.', 'info', 'schedule_routes.php', 1, '2025-09-19 07:42:11'),
(28, 'Schedule ID 18 has been completed.', 'info', 'schedule_routes.php', 1, '2025-09-19 07:42:19'),
(29, 'Service Provider approved: COCO PANDAN', 'service_provider', 'active_providers.php', 1, '2025-09-19 07:55:16'),
(30, 'Freight rate #20 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-19 08:15:07'),
(31, 'Freight rate #19 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-19 08:15:07'),
(32, 'Freight rate #15 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-19 08:15:08'),
(33, 'Schedule ID 20 has been completed.', 'info', 'schedule_routes.php', 1, '2025-09-21 11:08:08'),
(34, 'Schedule ID 21 has been completed.', 'info', 'schedule_routes.php', 1, '2025-09-21 11:11:09'),
(35, 'Schedule ID 22 has been delayed.', 'info', 'schedule_routes.php', 1, '2025-09-21 11:13:18'),
(36, 'Schedule ID 22 has been completed. Route removed.', 'info', 'schedule_routes.php', 1, '2025-09-21 11:13:27'),
(37, 'Schedule ID 23 has been delayed.', 'info', 'schedule_routes.php', 1, '2025-09-21 11:16:02'),
(38, 'Schedule ID 23 has been completed.', 'info', 'schedule_routes.php', 1, '2025-09-21 11:16:02'),
(39, 'Schedule ID 24 has been delayed.', 'info', 'schedule_routes.php', 1, '2025-09-21 11:23:32'),
(40, 'Schedule ID 24 has been completed.', 'info', 'schedule_routes.php', 1, '2025-09-21 11:23:33'),
(41, 'Schedule ID 25 has been completed.', 'info', 'schedule_routes.php', 1, '2025-09-21 11:24:35'),
(42, 'Schedule ID 24 has been completed.', 'info', 'schedule_routes.php', 1, '2025-09-21 11:24:38'),
(43, 'Schedule ID 26 has been completed.', 'info', 'schedule_routes.php', 1, '2025-09-21 13:21:22'),
(44, 'New Service Provider Registered: JOLO & COCO Freight Services', 'info', 'pending_providers.php', 1, '2025-09-21 13:28:59'),
(45, 'Service Provider approved: JOLO & COCO Freight Services', 'service_provider', 'active_providers.php', 1, '2025-09-21 13:51:11'),
(46, 'New Service Provider Registered: COCO PANDAN', 'info', 'pending_providers.php', 1, '2025-09-21 13:52:38'),
(47, 'Service Provider rejected: COCO PANDAN', 'service_provider', 'pending_providers.php', 1, '2025-09-21 13:53:19'),
(48, 'New Service Provider Registered: GARO FREIGHT SERVICES', 'info', 'pending_providers.php', 1, '2025-09-21 17:06:14'),
(49, 'Service Provider approved: GARO FREIGHT SERVICES', 'service_provider', 'active_providers.php', 1, '2025-09-21 18:16:21'),
(50, 'Freight rate #22 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-21 18:43:50'),
(51, 'Schedule ID 27 has been delayed.', 'info', 'schedule_routes.php', 1, '2025-09-21 18:47:08'),
(52, 'New Service Provider Registered: FLASH EXPRESS', 'info', 'pending_providers.php', 1, '2025-09-21 19:42:46'),
(53, 'Service Provider approved: FLASH EXPRESS', 'service_provider', 'active_providers.php', 1, '2025-09-21 19:43:52'),
(54, 'Freight rate #23 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-21 19:45:03'),
(55, 'Schedule ID 29 has been delayed.', 'info', 'schedule_routes.php', 1, '2025-09-21 19:48:06'),
(56, 'Schedule ID 29 has been completed.', 'info', 'schedule_routes.php', 1, '2025-09-21 19:48:25'),
(57, 'Freight rate #24 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-21 20:26:17'),
(58, 'Freight rate #25 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-21 20:37:27'),
(59, 'Freight rate #26 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-21 20:49:18'),
(60, 'Freight rate #27 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-21 20:59:49'),
(61, 'Freight rate #28 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-21 21:03:08'),
(62, 'Freight rate #29 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-21 21:04:11'),
(63, 'Schedule ID 32 has been completed.', 'info', 'schedule_routes.php', 1, '2025-09-21 23:14:20'),
(64, 'Schedule ID 31 has been completed.', 'info', 'schedule_routes.php', 1, '2025-09-21 23:14:20'),
(65, 'Schedule ID 30 has been completed.', 'info', 'schedule_routes.php', 1, '2025-09-21 23:14:21'),
(66, 'Freight rate #30 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-21 23:24:30'),
(67, 'Freight rate #31 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-21 23:32:12'),
(68, 'Freight rate #32 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-21 23:41:28'),
(69, 'Freight rate #34 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-22 00:14:02'),
(70, 'Freight rate #35 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-22 00:16:06'),
(71, 'New Service Provider Registered: ABC Freight Express', 'info', 'pending_providers.php', 1, '2025-09-22 00:29:04'),
(72, 'Service Provider approved: ABC Freight Express', 'service_provider', 'active_providers.php', 1, '2025-09-22 00:30:05'),
(73, 'Freight rate #39 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-22 00:30:32'),
(74, 'New Service Provider Registered: AVRIL FREIGHT EXPRESS', 'info', 'pending_providers.php', 1, '2025-09-22 00:34:50'),
(75, 'Service Provider approved: AVRIL FREIGHT EXPRESS', 'service_provider', 'active_providers.php', 1, '2025-09-22 00:35:17'),
(76, 'Schedule ID 33 has been delayed.', 'info', 'provider_schedules.php', 1, '2025-09-22 04:36:58'),
(77, 'Schedule ID 33 has been completed.', 'info', 'provider_schedules.php', 1, '2025-09-22 04:36:59'),
(78, 'Schedule ID 35 has been completed.', 'info', 'provider_schedules.php', 1, '2025-09-22 04:47:49'),
(79, 'Schedule ID 34 has been completed.', 'info', 'provider_schedules.php', 1, '2025-09-22 04:47:50'),
(80, 'Schedule ID 36 has been completed.', 'info', 'provider_schedules.php', 1, '2025-09-22 04:55:25'),
(81, 'Freight rate #44 has been Accepted.', 'admin', 'rates_management.php', 1, '2025-09-22 07:38:19'),
(82, 'Schedule ID 38 has been delayed.', 'info', 'provider_schedules.php', 1, '2025-09-22 09:37:19'),
(83, 'Schedule ID 38 has been completed.', 'info', 'provider_schedules.php', 1, '2025-09-22 09:37:21'),
(84, 'Schedule ID 37 has been completed.', 'info', 'provider_schedules.php', 1, '2025-09-22 09:37:46'),
(85, 'Schedule ID 39 has been delayed.', 'info', 'provider_schedules.php', 1, '2025-09-22 10:03:00'),
(86, 'Schedule ID 39 has been completed.', 'info', 'provider_schedules.php', 1, '2025-09-22 10:03:12'),
(87, 'New User Registered: bathanjc@gmail.com', 'info', 'user_management.php', 1, '2025-09-22 10:54:15'),
(88, 'Service Provider rejected: Jazz Inc | Remarks: asdasd', 'service_provider', 'pending_providers.php', 1, '2026-01-02 04:06:36'),
(89, 'New route created: Manila Port to Ninoy Aquino International Airport (land, 11.06km)', 'success', 'manage_routes.php', 1, '2026-01-11 11:29:32'),
(90, 'New route created: Batangas Port to Manila Port (sea, 92.54km)', 'success', 'manage_routes.php', 1, '2026-01-11 12:45:51'),
(91, 'Route deleted: Batangas Port to Manila Port (sea)', 'warning', 'manage_routes.php', 1, '2026-01-11 12:46:11'),
(92, 'New route created: General Santos Airport to Ninoy Aquino International Airport (air, 1039.75km)', 'success', 'manage_routes.php', 1, '2026-01-11 12:46:25'),
(93, 'New route created: Manila Port to Batangas Port (land, 108.36km)', 'success', 'manage_routes.php', 1, '2026-01-11 12:48:07'),
(94, 'New route created: Manila Port to Logistics Division (FT12) (land, 18.64km)', 'success', 'manage_routes.php', 1, '2026-01-11 14:09:48'),
(95, 'New Rate & Tariff Management System is now available with AI-powered calculations', 'admin', 'rate_tariff_management.php', 1, '2026-01-14 11:11:56'),
(96, 'Service Provider rejected: jnntt | Remarks: asds', 'service_provider', 'pending_providers.php', 1, '2026-01-14 11:16:10'),
(97, 'Service Provider rejected: Jazz Inc | Reason: Missing Requirements | Remarks: asdsadas | Contact: Jazz', 'service_provider', 'pending_providers.php', 1, '2026-01-14 12:40:44');

-- --------------------------------------------------------

--
-- Table structure for table `pending_service_provider`
--

DROP TABLE IF EXISTS `pending_service_provider`;
CREATE TABLE IF NOT EXISTS `pending_service_provider` (
  `registration_id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `account_type` int NOT NULL DEFAULT '3',
  `contact_person` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `services` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `iso_certified` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `business_permit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_profile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_submitted` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`registration_id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_service_provider`
--

INSERT INTO `pending_service_provider` (`registration_id`, `company_name`, `email`, `password`, `account_type`, `contact_person`, `contact_number`, `address`, `services`, `iso_certified`, `business_permit`, `company_profile`, `date_submitted`, `status`) VALUES
(44, 'Jazz Inc', 'jazznellevince.a@gmail.com', '$2y$10$2n61Gd0sX.HMZWZCGSOfueJNxoOgRE7XgI2bWaVQ0o3vTyrlHKUH2', 3, 'Jazz', '09777323270', 'Phase 1F Ottawa St. B3 L4 Vista Verde Llano', 'Lahat [Logistic1 ID: 82]', NULL, NULL, NULL, '2026-01-21 21:40:54', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `rate_calculation_log`
--

DROP TABLE IF EXISTS `rate_calculation_log`;
CREATE TABLE IF NOT EXISTS `rate_calculation_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `rate_id` int NOT NULL,
  `calculation_type` enum('ai','manual','system') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `input_parameters` json NOT NULL,
  `calculation_steps` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `result_base_rate` decimal(12,2) NOT NULL,
  `result_tariff_amount` decimal(12,2) NOT NULL,
  `result_total_rate` decimal(12,2) NOT NULL,
  `calculated_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `calculation_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `rate_id` (`rate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

DROP TABLE IF EXISTS `routes`;
CREATE TABLE IF NOT EXISTS `routes` (
  `route_id` int NOT NULL AUTO_INCREMENT,
  `origin_id` int NOT NULL,
  `destination_id` int NOT NULL,
  `carrier_type` enum('land','air','sea') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `provider_id` int NOT NULL,
  `distance_km` decimal(10,2) NOT NULL,
  `eta_min` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','approved','rejected','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `booking_id` int DEFAULT NULL,
  PRIMARY KEY (`route_id`),
  KEY `fk_routes_origin` (`origin_id`),
  KEY `fk_routes_destination` (`destination_id`),
  KEY `fk_routes_provider` (`provider_id`),
  KEY `fk_routes_booking` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`route_id`, `origin_id`, `destination_id`, `carrier_type`, `provider_id`, `distance_km`, `eta_min`, `created_at`, `status`, `booking_id`) VALUES
(61, 93, 92, 'air', 32, 1039.75, 78, '2025-09-22 17:33:37', 'completed', NULL),
(68, 92, 93, 'air', 33, 1039.75, 78, '2026-01-11 20:46:25', 'pending', NULL),
(69, 95, 86, 'land', 31, 108.36, 191, '2026-01-11 20:48:07', 'pending', NULL),
(70, 95, 184, 'land', 31, 18.64, 48, '2026-01-11 22:09:48', 'pending', NULL),
(71, 189, 190, 'land', 32, 571.03, 742, '2026-01-22 17:59:12', 'pending', 5);

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
CREATE TABLE IF NOT EXISTS `schedules` (
  `schedule_id` int NOT NULL AUTO_INCREMENT,
  `rate_id` int NOT NULL,
  `route_id` int NOT NULL,
  `provider_id` int DEFAULT NULL,
  `sop_id` int NOT NULL,
  `schedule_date` date NOT NULL,
  `schedule_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'scheduled',
  `total_rate` decimal(12,2) NOT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `route_id` (`route_id`),
  KEY `sop_id` (`sop_id`),
  KEY `provider_id` (`provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `rate_id`, `route_id`, `provider_id`, `sop_id`, `schedule_date`, `schedule_time`, `created_at`, `status`, `total_rate`) VALUES
(38, 49, 61, 32, 7, '2025-09-22', '17:39:00', '2025-09-22 09:36:08', 'completed', 25000.00);

-- --------------------------------------------------------

--
-- Table structure for table `sop_documents`
--

DROP TABLE IF EXISTS `sop_documents`;
CREATE TABLE IF NOT EXISTS `sop_documents` (
  `sop_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('Active','Draft','Archived') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sop_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sop_documents`
--

INSERT INTO `sop_documents` (`sop_id`, `title`, `category`, `content`, `file_path`, `status`, `created_at`, `updated_at`) VALUES
(4, 'Fragile Cargo Handling', 'Safety', '1. Identify fragile cargo upon receipt and ensure it is properly labeled with “FRAGILE” stickers.  \r\n2. Inspect packaging to confirm it meets protective standards (bubble wrap, cushioning, wooden crate if required).  \r\n3. Use specialized equipment (trolleys, forklifts with padding) for loading and unloading.  \r\n4. Ensure cargo is always handled manually with two or more staff if weight exceeds 20kg.  \r\n5. Secure fragile cargo in transport vehicles using straps and separators to avoid shifting during transit.  \r\n6. Avoid stacking heavy items on top of fragile cargo during storage and transport.  \r\n7. Document handling steps in the shipment record for accountability.  \r\n8. In case of damage, immediately report, photograph, and file an incident log with operations management.', 'uploads/sop/1768573766_1122.jpg', 'Active', '2025-09-12 20:52:38', '2026-01-16 14:29:26'),
(5, 'Hazardous Cargo', 'Safety', '1. asdsaad', NULL, 'Archived', '2025-09-12 22:37:07', '2026-01-15 15:41:06'),
(6, 'Handling and Shipment of Live Animals', 'Customs', '1. Obtain and verify veterinary health certificates, import/export permits, vaccination records, and other required documents.  \r\n2. Submit documents to customs and quarantine authorities for clearance before shipment.  \r\n3. Prepare IATA-approved containers with proper ventilation, bedding, labels, and handling instructions.  \r\n4. Have a veterinary officer inspect animals and issue a \"Fit to Transport\" certificate.  \r\n5. Load animals under supervision of customs and quarantine officers, ensuring segregation from incompatible cargo.  \r\n6. Monitor animals during transit with adequate ventilation, food, and water.  \r\n7. Present documents at the destination for customs and veterinary inspection.  \r\n8. Deliver animals to the consignee once clearance is granted.  \r\n9. Record shipment details, customs references, and animal health condition in the system.', 'uploads/sop/1768573776_1122.jpg', 'Active', '2025-09-19 07:15:55', '2026-01-16 14:29:36'),
(7, 'Handling and Shipment of Perishable Goods via Air Transport', 'Logistics', '1. Verify that the shipper provides valid documents including health certificates, invoices, and export permits if required.  \r\n2. Ensure all perishable goods are properly packed in insulated or refrigerated containers suitable for air transport.  \r\n3. Check temperature control devices and labeling such as “Perishable – Keep Refrigerated” before acceptance.  \r\n4. Submit documents to customs and quarantine authorities for clearance prior to flight loading.  \r\n5. Load perishable goods last and unload first to minimize exposure to non-controlled environments.  \r\n6. Monitor storage conditions in the aircraft hold and ensure appropriate ventilation or refrigeration is active.  \r\n7. Notify ground handling teams at the destination of special handling requirements for perishable cargo.  \r\n8. On arrival, present documents for customs and health inspections before releasing cargo.  \r\n9. Deliver goods immediately to the consignee or transfer to a cold storage facility to prevent spoilage.  \r\n10. Record shipment details, temperature logs, and customs references in the system for traceability.', NULL, 'Archived', '2025-09-19 07:36:10', '2025-09-21 10:51:21');

-- --------------------------------------------------------

--
-- Table structure for table `tariff_configurations`
--

DROP TABLE IF EXISTS `tariff_configurations`;
CREATE TABLE IF NOT EXISTS `tariff_configurations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `carrier_type` enum('land','sea','air') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cargo_type` enum('general','perishable','hazardous','fragile','oversized') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_level` enum('standard','express','economy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_rate_per_km` decimal(10,2) NOT NULL,
  `base_rate_per_kg` decimal(10,2) NOT NULL,
  `minimum_fee` decimal(10,2) NOT NULL,
  `cargo_multiplier` decimal(5,2) NOT NULL DEFAULT '1.00',
  `service_multiplier` decimal(5,2) NOT NULL DEFAULT '1.00',
  `tariff_percentage` decimal(5,2) NOT NULL,
  `effective_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tariff_configurations`
--

INSERT INTO `tariff_configurations` (`id`, `carrier_type`, `cargo_type`, `service_level`, `base_rate_per_km`, `base_rate_per_kg`, `minimum_fee`, `cargo_multiplier`, `service_multiplier`, `tariff_percentage`, `effective_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'land', 'general', 'standard', 8.50, 2.75, 150.00, 1.00, 1.00, 15.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(2, 'land', 'general', 'express', 8.50, 2.75, 150.00, 1.00, 1.60, 15.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(3, 'land', 'general', 'economy', 8.50, 2.75, 150.00, 1.00, 0.80, 15.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(4, 'land', 'perishable', 'standard', 8.50, 2.75, 150.00, 1.30, 1.00, 15.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(5, 'land', 'perishable', 'express', 8.50, 2.75, 150.00, 1.30, 1.60, 15.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(6, 'land', 'hazardous', 'standard', 8.50, 2.75, 150.00, 2.10, 1.00, 15.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(7, 'land', 'fragile', 'standard', 8.50, 2.75, 150.00, 1.50, 1.00, 15.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(8, 'land', 'oversized', 'standard', 8.50, 2.75, 150.00, 1.80, 1.00, 15.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(9, 'sea', 'general', 'standard', 3.25, 1.50, 500.00, 1.00, 1.00, 12.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(10, 'sea', 'general', 'express', 3.25, 1.50, 500.00, 1.00, 1.60, 12.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(11, 'sea', 'general', 'economy', 3.25, 1.50, 500.00, 1.00, 0.80, 12.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(12, 'sea', 'perishable', 'standard', 3.25, 1.50, 500.00, 1.30, 1.00, 12.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(13, 'sea', 'hazardous', 'standard', 3.25, 1.50, 500.00, 2.10, 1.00, 12.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(14, 'sea', 'fragile', 'standard', 3.25, 1.50, 500.00, 1.50, 1.00, 12.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(15, 'sea', 'oversized', 'standard', 3.25, 1.50, 500.00, 1.80, 1.00, 12.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(16, 'air', 'general', 'standard', 15.75, 8.25, 800.00, 1.00, 1.00, 18.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(17, 'air', 'general', 'express', 15.75, 8.25, 800.00, 1.00, 1.60, 18.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(18, 'air', 'general', 'economy', 15.75, 8.25, 800.00, 1.00, 0.80, 18.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(19, 'air', 'perishable', 'standard', 15.75, 8.25, 800.00, 1.30, 1.00, 18.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(20, 'air', 'hazardous', 'standard', 15.75, 8.25, 800.00, 2.10, 1.00, 18.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(21, 'air', 'fragile', 'standard', 15.75, 8.25, 800.00, 1.50, 1.00, 18.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56'),
(22, 'air', 'oversized', 'standard', 15.75, 8.25, 800.00, 1.80, 1.00, 18.00, '2026-01-14', 1, '2026-01-14 11:11:56', '2026-01-14 11:11:56');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `calculated_rates`
--
ALTER TABLE `calculated_rates`
  ADD CONSTRAINT `fk_cr_provider` FOREIGN KEY (`provider_id`) REFERENCES `active_service_provider` (`provider_id`) ON DELETE CASCADE;

--
-- Constraints for table `freight_rates`
--
ALTER TABLE `freight_rates`
  ADD CONSTRAINT `freight_rates_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `active_service_provider` (`provider_id`) ON DELETE CASCADE;

--
-- Constraints for table `rate_calculation_log`
--
ALTER TABLE `rate_calculation_log`
  ADD CONSTRAINT `rate_calculation_log_ibfk_1` FOREIGN KEY (`rate_id`) REFERENCES `calculated_rates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `routes`
--
ALTER TABLE `routes`
  ADD CONSTRAINT `fk_routes_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_routes_destination` FOREIGN KEY (`destination_id`) REFERENCES `network_points` (`point_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_routes_origin` FOREIGN KEY (`origin_id`) REFERENCES `network_points` (`point_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_routes_provider` FOREIGN KEY (`provider_id`) REFERENCES `active_service_provider` (`provider_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`sop_id`) REFERENCES `sop_documents` (`sop_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_ibfk_3` FOREIGN KEY (`provider_id`) REFERENCES `active_service_provider` (`provider_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
