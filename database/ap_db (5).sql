-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2026 at 04:59 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ap_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_orders`
--

CREATE TABLE `custom_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `reference_image` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `status` enum('pending','reviewing','approved','in_production','done','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `custom_orders`
--

INSERT INTO `custom_orders` (`id`, `user_id`, `description`, `reference_image`, `quantity`, `status`, `created_at`) VALUES
(1, 4, 'awdawdawdwdw', 'uploads/custom_orders/1778511270_icon-192.png', 1, 'pending', '2026-05-11 14:54:30');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender_role` enum('customer','admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `sender_role`, `message`, `is_read`, `created_at`) VALUES
(1, 4, 'customer', 'hello', 1, '2026-05-11 11:15:43'),
(2, 4, 'admin', 'hello! how can we help you?', 1, '2026-05-11 11:16:19');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_method` varchar(100) DEFAULT 'Cash on Delivery',
  `payment_status` varchar(50) DEFAULT 'pending',
  `released_at` datetime DEFAULT NULL,
  `courier` varchar(100) DEFAULT NULL,
  `shipping_type` varchar(50) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `shipping_address`, `created_at`, `updated_at`, `payment_method`, `payment_status`, `released_at`, `courier`, `shipping_type`, `tracking_number`) VALUES
(1, 4, 1192.00, 'shipped', 'Villegas Street, Baybayin Los Baños, Los Baños, 4030', '2026-05-07 14:39:36', '2026-05-11 11:02:35', 'Cash on Delivery', 'released', '2026-05-07 22:39:36', NULL, NULL, NULL),
(2, 4, 1341.00, 'shipped', 'Villegas Street, Baybayin Los Baños, Los Baños, 4030', '2026-05-07 15:19:37', '2026-05-11 11:02:35', 'Cash on Delivery', 'released', '2026-05-07 23:19:37', NULL, NULL, NULL),
(3, 4, 149.00, 'shipped', 'Villegas Street, Baybayin Los Baños, Los Baños, 4030', '2026-05-07 15:22:36', '2026-05-10 08:36:35', 'Cash on Delivery', 'released', '2026-05-07 23:22:36', NULL, NULL, NULL),
(4, 4, 298.00, 'shipped', 'Villegas Street, Baybayin Los Baños, Los Baños, 4030', '2026-05-10 07:59:03', '2026-05-11 11:02:35', 'Cash on Delivery', 'released', '2026-05-10 15:59:03', NULL, NULL, NULL),
(5, 4, 79.00, 'pending', 'awbduiabwdawdawdawd, awdawdwadw, 1234', '2026-05-11 11:07:07', '2026-05-11 11:07:07', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(6, 4, 790.00, 'pending', 'trying, adwdawd, 1234', '2026-05-11 11:23:43', '2026-05-11 11:23:43', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(7, 4, 149.00, 'pending', 'awdawdaw, awdawdawd, 1234', '2026-05-11 11:25:50', '2026-05-11 11:25:50', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(8, 4, 395.00, 'pending', 'awdawdaw, awdawdawd, 1234', '2026-05-11 11:31:10', '2026-05-11 11:31:10', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL);

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `shipping_address`, `created_at`, `updated_at`, `payment_method`, `payment_status`, `released_at`, `courier`, `shipping_type`, `tracking_number`) VALUES
(9, 2, 298.00, 'pending', 'Blk 1, Sample St, Los Baños, 4030', '2026-05-21 08:05:00', '2026-05-21 08:05:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(10, 3, 447.00, 'processing', 'Blk 2, Sample St, Los Baños, 4030', '2026-05-21 08:40:00', '2026-05-21 09:10:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(11, 4, 596.00, 'shipped', 'Blk 3, Sample St, Los Baños, 4030', '2026-05-21 09:15:00', '2026-05-21 17:15:00', 'Bank Transfer', 'released', '2026-05-21 17:15:00', 'J&T Express', 'Standard', 'TRK20260521011'),
(12, 2, 149.00, 'delivered', 'Blk 4, Sample St, Los Baños, 4030', '2026-05-21 10:00:00', '2026-05-22 18:00:00', 'Cash on Delivery', 'released', '2026-05-21 18:00:00', 'J&T Express', 'Standard', 'TRK20260521012'),
(13, 3, 790.00, 'cancelled', 'Blk 5, Sample St, Los Baños, 4030', '2026-05-21 10:45:00', '2026-05-21 11:30:00', 'GCash', 'paid', NULL, NULL, NULL, NULL),
(14, 4, 237.00, 'pending', 'Blk 6, Sample St, Los Baños, 4030', '2026-05-21 11:20:00', '2026-05-21 11:20:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(15, 2, 395.00, 'processing', 'Blk 7, Sample St, Los Baños, 4030', '2026-05-21 12:05:00', '2026-05-21 12:35:00', 'Bank Transfer', 'paid', NULL, NULL, 'Express', NULL),
(16, 3, 548.00, 'shipped', 'Blk 8, Sample St, Los Baños, 4030', '2026-05-21 12:40:00', '2026-05-21 20:40:00', 'GCash', 'released', '2026-05-21 20:40:00', 'LBC', 'Express', 'TRK20260521016'),
(17, 4, 149.00, 'delivered', 'Blk 9, Sample St, Los Baños, 4030', '2026-05-21 13:20:00', '2026-05-23 08:10:00', 'Cash on Delivery', 'released', '2026-05-21 21:20:00', 'LBC', 'Standard', 'TRK20260521017'),
(18, 2, 943.00, 'processing', 'Blk 10, Sample St, Los Baños, 4030', '2026-05-21 14:05:00', '2026-05-21 14:40:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(19, 3, 316.00, 'pending', 'Blk 11, Sample St, Los Baños, 4030', '2026-05-21 15:00:00', '2026-05-21 15:00:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(20, 4, 298.00, 'pending', 'Blk 12, Sample St, Los Baños, 4030', '2026-05-22 08:10:00', '2026-05-22 08:10:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(21, 2, 447.00, 'processing', 'Blk 13, Sample St, Los Baños, 4030', '2026-05-22 08:50:00', '2026-05-22 09:20:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(22, 3, 596.00, 'shipped', 'Blk 14, Sample St, Los Baños, 4030', '2026-05-22 09:30:00', '2026-05-22 17:30:00', 'Bank Transfer', 'released', '2026-05-22 17:30:00', 'J&T Express', 'Standard', 'TRK20260522022'),
(23, 4, 149.00, 'delivered', 'Blk 15, Sample St, Los Baños, 4030', '2026-05-22 10:05:00', '2026-05-23 18:00:00', 'Cash on Delivery', 'released', '2026-05-22 18:05:00', 'J&T Express', 'Standard', 'TRK20260522023'),
(24, 2, 790.00, 'cancelled', 'Blk 16, Sample St, Los Baños, 4030', '2026-05-22 10:50:00', '2026-05-22 11:20:00', 'GCash', 'paid', NULL, NULL, NULL, NULL),
(25, 3, 237.00, 'pending', 'Blk 17, Sample St, Los Baños, 4030', '2026-05-22 11:30:00', '2026-05-22 11:30:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(26, 4, 395.00, 'processing', 'Blk 18, Sample St, Los Baños, 4030', '2026-05-22 12:10:00', '2026-05-22 12:50:00', 'Bank Transfer', 'paid', NULL, NULL, 'Express', NULL),
(27, 2, 548.00, 'shipped', 'Blk 19, Sample St, Los Baños, 4030', '2026-05-22 12:55:00', '2026-05-22 20:55:00', 'GCash', 'released', '2026-05-22 20:55:00', 'LBC', 'Express', 'TRK20260522027'),
(28, 3, 149.00, 'delivered', 'Blk 20, Sample St, Los Baños, 4030', '2026-05-22 13:35:00', '2026-05-24 08:20:00', 'Cash on Delivery', 'released', '2026-05-22 21:35:00', 'LBC', 'Standard', 'TRK20260522028'),
(29, 4, 943.00, 'processing', 'Blk 21, Sample St, Los Baños, 4030', '2026-05-22 14:15:00', '2026-05-22 14:55:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(30, 2, 316.00, 'pending', 'Blk 22, Sample St, Los Baños, 4030', '2026-05-22 15:05:00', '2026-05-22 15:05:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(31, 3, 298.00, 'pending', 'Blk 23, Sample St, Los Baños, 4030', '2026-05-23 08:12:00', '2026-05-23 08:12:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(32, 4, 447.00, 'processing', 'Blk 24, Sample St, Los Baños, 4030', '2026-05-23 08:47:00', '2026-05-23 09:17:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(33, 2, 596.00, 'shipped', 'Blk 25, Sample St, Los Baños, 4030', '2026-05-23 09:20:00', '2026-05-23 17:20:00', 'Bank Transfer', 'released', '2026-05-23 17:20:00', 'J&T Express', 'Standard', 'TRK20260523033'),
(34, 3, 149.00, 'delivered', 'Blk 26, Sample St, Los Baños, 4030', '2026-05-23 10:02:00', '2026-05-24 18:10:00', 'Cash on Delivery', 'released', '2026-05-23 18:02:00', 'J&T Express', 'Standard', 'TRK20260523034'),
(35, 4, 790.00, 'cancelled', 'Blk 27, Sample St, Los Baños, 4030', '2026-05-23 10:40:00', '2026-05-23 11:22:00', 'GCash', 'paid', NULL, NULL, NULL, NULL),
(36, 2, 237.00, 'pending', 'Blk 28, Sample St, Los Baños, 4030', '2026-05-23 11:18:00', '2026-05-23 11:18:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(37, 3, 395.00, 'processing', 'Blk 29, Sample St, Los Baños, 4030', '2026-05-23 12:06:00', '2026-05-23 12:36:00', 'Bank Transfer', 'paid', NULL, NULL, 'Express', NULL),
(38, 4, 548.00, 'shipped', 'Blk 30, Sample St, Los Baños, 4030', '2026-05-23 12:50:00', '2026-05-23 20:50:00', 'GCash', 'released', '2026-05-23 20:50:00', 'LBC', 'Express', 'TRK20260523038'),
(39, 2, 149.00, 'delivered', 'Blk 31, Sample St, Los Baños, 4030', '2026-05-23 13:30:00', '2026-05-25 08:05:00', 'Cash on Delivery', 'released', '2026-05-23 21:30:00', 'LBC', 'Standard', 'TRK20260523039'),
(40, 3, 943.00, 'processing', 'Blk 32, Sample St, Los Baños, 4030', '2026-05-23 14:12:00', '2026-05-23 14:52:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(41, 4, 316.00, 'pending', 'Blk 33, Sample St, Los Baños, 4030', '2026-05-23 15:08:00', '2026-05-23 15:08:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(42, 2, 298.00, 'pending', 'Blk 34, Sample St, Los Baños, 4030', '2026-05-24 08:03:00', '2026-05-24 08:03:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(43, 3, 447.00, 'processing', 'Blk 35, Sample St, Los Baños, 4030', '2026-05-24 08:42:00', '2026-05-24 09:14:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(44, 4, 596.00, 'shipped', 'Blk 36, Sample St, Los Baños, 4030', '2026-05-24 09:25:00', '2026-05-24 17:25:00', 'Bank Transfer', 'released', '2026-05-24 17:25:00', 'J&T Express', 'Standard', 'TRK20260524044'),
(45, 2, 149.00, 'delivered', 'Blk 37, Sample St, Los Baños, 4030', '2026-05-24 10:10:00', '2026-05-25 18:15:00', 'Cash on Delivery', 'released', '2026-05-24 18:10:00', 'J&T Express', 'Standard', 'TRK20260524045'),
(46, 3, 790.00, 'cancelled', 'Blk 38, Sample St, Los Baños, 4030', '2026-05-24 10:48:00', '2026-05-24 11:33:00', 'GCash', 'paid', NULL, NULL, NULL, NULL),
(47, 4, 237.00, 'pending', 'Blk 39, Sample St, Los Baños, 4030', '2026-05-24 11:26:00', '2026-05-24 11:26:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(48, 2, 395.00, 'processing', 'Blk 40, Sample St, Los Baños, 4030', '2026-05-24 12:12:00', '2026-05-24 12:44:00', 'Bank Transfer', 'paid', NULL, NULL, 'Express', NULL),
(49, 3, 548.00, 'shipped', 'Blk 41, Sample St, Los Baños, 4030', '2026-05-24 12:58:00', '2026-05-24 20:58:00', 'GCash', 'released', '2026-05-24 20:58:00', 'LBC', 'Express', 'TRK20260524049'),
(50, 4, 149.00, 'delivered', 'Blk 42, Sample St, Los Baños, 4030', '2026-05-24 13:42:00', '2026-05-26 08:12:00', 'Cash on Delivery', 'released', '2026-05-24 21:42:00', 'LBC', 'Standard', 'TRK20260524050'),
(51, 2, 943.00, 'processing', 'Blk 43, Sample St, Los Baños, 4030', '2026-05-24 14:22:00', '2026-05-24 14:57:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(52, 3, 316.00, 'pending', 'Blk 44, Sample St, Los Baños, 4030', '2026-05-24 15:12:00', '2026-05-24 15:12:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(53, 4, 298.00, 'pending', 'Blk 45, Sample St, Los Baños, 4030', '2026-05-25 08:08:00', '2026-05-25 08:08:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(54, 2, 447.00, 'processing', 'Blk 46, Sample St, Los Baños, 4030', '2026-05-25 08:46:00', '2026-05-25 09:18:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(55, 3, 596.00, 'shipped', 'Blk 47, Sample St, Los Baños, 4030', '2026-05-25 09:28:00', '2026-05-25 17:28:00', 'Bank Transfer', 'released', '2026-05-25 17:28:00', 'J&T Express', 'Standard', 'TRK20260525055'),
(56, 4, 149.00, 'delivered', 'Blk 48, Sample St, Los Baños, 4030', '2026-05-25 10:04:00', '2026-05-26 18:00:00', 'Cash on Delivery', 'released', '2026-05-25 18:04:00', 'J&T Express', 'Standard', 'TRK20260525056'),
(57, 2, 790.00, 'cancelled', 'Blk 49, Sample St, Los Baños, 4030', '2026-05-25 10:52:00', '2026-05-25 11:21:00', 'GCash', 'paid', NULL, NULL, NULL, NULL),
(58, 3, 237.00, 'pending', 'Blk 50, Sample St, Los Baños, 4030', '2026-05-25 11:32:00', '2026-05-25 11:32:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(59, 4, 395.00, 'processing', 'Blk 51, Sample St, Los Baños, 4030', '2026-05-25 12:14:00', '2026-05-25 12:48:00', 'Bank Transfer', 'paid', NULL, NULL, 'Express', NULL),
(60, 2, 548.00, 'shipped', 'Blk 52, Sample St, Los Baños, 4030', '2026-05-25 12:59:00', '2026-05-25 20:59:00', 'GCash', 'released', '2026-05-25 20:59:00', 'LBC', 'Express', 'TRK20260525060'),
(61, 3, 149.00, 'delivered', 'Blk 53, Sample St, Los Baños, 4030', '2026-05-25 13:38:00', '2026-05-27 08:22:00', 'Cash on Delivery', 'released', '2026-05-25 21:38:00', 'LBC', 'Standard', 'TRK20260525061'),
(62, 4, 943.00, 'processing', 'Blk 54, Sample St, Los Baños, 4030', '2026-05-25 14:18:00', '2026-05-25 14:58:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(63, 2, 316.00, 'pending', 'Blk 55, Sample St, Los Baños, 4030', '2026-05-25 15:02:00', '2026-05-25 15:02:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(64, 3, 298.00, 'pending', 'Blk 56, Sample St, Los Baños, 4030', '2026-05-26 08:06:00', '2026-05-26 08:06:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(65, 4, 447.00, 'processing', 'Blk 57, Sample St, Los Baños, 4030', '2026-05-26 08:44:00', '2026-05-26 09:16:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(66, 2, 596.00, 'shipped', 'Blk 58, Sample St, Los Baños, 4030', '2026-05-26 09:22:00', '2026-05-26 17:22:00', 'Bank Transfer', 'released', '2026-05-26 17:22:00', 'J&T Express', 'Standard', 'TRK20260526066'),
(67, 3, 149.00, 'delivered', 'Blk 59, Sample St, Los Baños, 4030', '2026-05-26 10:06:00', '2026-05-27 18:06:00', 'Cash on Delivery', 'released', '2026-05-26 18:06:00', 'J&T Express', 'Standard', 'TRK20260526067'),
(68, 4, 790.00, 'cancelled', 'Blk 60, Sample St, Los Baños, 4030', '2026-05-26 10:50:00', '2026-05-26 11:35:00', 'GCash', 'paid', NULL, NULL, NULL, NULL),
(69, 2, 237.00, 'pending', 'Blk 61, Sample St, Los Baños, 4030', '2026-05-26 11:29:00', '2026-05-26 11:29:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(70, 3, 395.00, 'processing', 'Blk 62, Sample St, Los Baños, 4030', '2026-05-26 12:09:00', '2026-05-26 12:43:00', 'Bank Transfer', 'paid', NULL, NULL, 'Express', NULL),
(71, 4, 548.00, 'shipped', 'Blk 63, Sample St, Los Baños, 4030', '2026-05-26 12:54:00', '2026-05-26 20:54:00', 'GCash', 'released', '2026-05-26 20:54:00', 'LBC', 'Express', 'TRK20260526071'),
(72, 2, 149.00, 'delivered', 'Blk 64, Sample St, Los Baños, 4030', '2026-05-26 13:36:00', '2026-05-28 08:18:00', 'Cash on Delivery', 'released', '2026-05-26 21:36:00', 'LBC', 'Standard', 'TRK20260526072'),
(73, 3, 943.00, 'processing', 'Blk 65, Sample St, Los Baños, 4030', '2026-05-26 14:16:00', '2026-05-26 14:51:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(74, 4, 316.00, 'pending', 'Blk 66, Sample St, Los Baños, 4030', '2026-05-26 15:04:00', '2026-05-26 15:04:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(75, 2, 298.00, 'pending', 'Blk 67, Sample St, Los Baños, 4030', '2026-05-27 08:09:00', '2026-05-27 08:09:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(76, 3, 447.00, 'processing', 'Blk 68, Sample St, Los Baños, 4030', '2026-05-27 08:48:00', '2026-05-27 09:20:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(77, 4, 596.00, 'shipped', 'Blk 69, Sample St, Los Baños, 4030', '2026-05-27 09:26:00', '2026-05-27 17:26:00', 'Bank Transfer', 'released', '2026-05-27 17:26:00', 'J&T Express', 'Standard', 'TRK20260527077'),
(78, 2, 149.00, 'delivered', 'Blk 70, Sample St, Los Baños, 4030', '2026-05-27 10:08:00', '2026-05-28 18:08:00', 'Cash on Delivery', 'released', '2026-05-27 18:08:00', 'J&T Express', 'Standard', 'TRK20260527078'),
(79, 3, 790.00, 'cancelled', 'Blk 71, Sample St, Los Baños, 4030', '2026-05-27 10:54:00', '2026-05-27 11:26:00', 'GCash', 'paid', NULL, NULL, NULL, NULL),
(80, 4, 237.00, 'pending', 'Blk 72, Sample St, Los Baños, 4030', '2026-05-27 11:35:00', '2026-05-27 11:35:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(81, 2, 395.00, 'processing', 'Blk 73, Sample St, Los Baños, 4030', '2026-05-27 12:13:00', '2026-05-27 12:45:00', 'Bank Transfer', 'paid', NULL, NULL, 'Express', NULL),
(82, 3, 548.00, 'shipped', 'Blk 74, Sample St, Los Baños, 4030', '2026-05-27 12:57:00', '2026-05-27 20:57:00', 'GCash', 'released', '2026-05-27 20:57:00', 'LBC', 'Express', 'TRK20260527082'),
(83, 4, 149.00, 'delivered', 'Blk 75, Sample St, Los Baños, 4030', '2026-05-27 13:39:00', '2026-05-29 08:25:00', 'Cash on Delivery', 'released', '2026-05-27 21:39:00', 'LBC', 'Standard', 'TRK20260527083'),
(84, 2, 943.00, 'processing', 'Blk 76, Sample St, Los Baños, 4030', '2026-05-27 14:20:00', '2026-05-27 14:54:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(85, 3, 316.00, 'pending', 'Blk 77, Sample St, Los Baños, 4030', '2026-05-27 15:06:00', '2026-05-27 15:06:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(86, 4, 298.00, 'pending', 'Blk 78, Sample St, Los Baños, 4030', '2026-05-28 08:04:00', '2026-05-28 08:04:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(87, 2, 447.00, 'processing', 'Blk 79, Sample St, Los Baños, 4030', '2026-05-28 08:41:00', '2026-05-28 09:13:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(88, 3, 596.00, 'shipped', 'Blk 80, Sample St, Los Baños, 4030', '2026-05-28 09:24:00', '2026-05-28 17:24:00', 'Bank Transfer', 'released', '2026-05-28 17:24:00', 'J&T Express', 'Standard', 'TRK20260528088'),
(89, 4, 149.00, 'delivered', 'Blk 81, Sample St, Los Baños, 4030', '2026-05-28 10:07:00', '2026-05-29 18:07:00', 'Cash on Delivery', 'released', '2026-05-28 18:07:00', 'J&T Express', 'Standard', 'TRK20260528089'),
(90, 2, 790.00, 'cancelled', 'Blk 82, Sample St, Los Baños, 4030', '2026-05-28 10:49:00', '2026-05-28 11:31:00', 'GCash', 'paid', NULL, NULL, NULL, NULL),
(91, 3, 237.00, 'pending', 'Blk 83, Sample St, Los Baños, 4030', '2026-05-28 11:24:00', '2026-05-28 11:24:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(92, 4, 395.00, 'processing', 'Blk 84, Sample St, Los Baños, 4030', '2026-05-28 12:11:00', '2026-05-28 12:42:00', 'Bank Transfer', 'paid', NULL, NULL, 'Express', NULL),
(93, 2, 548.00, 'shipped', 'Blk 85, Sample St, Los Baños, 4030', '2026-05-28 12:56:00', '2026-05-28 20:56:00', 'GCash', 'released', '2026-05-28 20:56:00', 'LBC', 'Express', 'TRK20260528093'),
(94, 3, 149.00, 'delivered', 'Blk 86, Sample St, Los Baños, 4030', '2026-05-28 13:37:00', '2026-05-30 08:19:00', 'Cash on Delivery', 'released', '2026-05-28 21:37:00', 'LBC', 'Standard', 'TRK20260528094'),
(95, 4, 943.00, 'processing', 'Blk 87, Sample St, Los Baños, 4030', '2026-05-28 14:17:00', '2026-05-28 14:53:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(96, 2, 316.00, 'pending', 'Blk 88, Sample St, Los Baños, 4030', '2026-05-28 15:03:00', '2026-05-28 15:03:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(97, 3, 298.00, 'pending', 'Blk 89, Sample St, Los Baños, 4030', '2026-05-29 08:02:00', '2026-05-29 08:02:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(98, 4, 447.00, 'processing', 'Blk 90, Sample St, Los Baños, 4030', '2026-05-29 08:39:00', '2026-05-29 09:12:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(99, 2, 596.00, 'shipped', 'Blk 91, Sample St, Los Baños, 4030', '2026-05-29 09:21:00', '2026-05-29 17:21:00', 'Bank Transfer', 'released', '2026-05-29 17:21:00', 'J&T Express', 'Standard', 'TRK20260529099'),
(100, 3, 149.00, 'delivered', 'Blk 92, Sample St, Los Baños, 4030', '2026-05-29 10:03:00', '2026-05-30 18:03:00', 'Cash on Delivery', 'released', '2026-05-29 18:03:00', 'J&T Express', 'Standard', 'TRK20260529100'),
(101, 4, 790.00, 'cancelled', 'Blk 93, Sample St, Los Baños, 4030', '2026-05-29 10:47:00', '2026-05-29 11:29:00', 'GCash', 'paid', NULL, NULL, NULL, NULL),
(102, 2, 237.00, 'pending', 'Blk 94, Sample St, Los Baños, 4030', '2026-05-29 11:27:00', '2026-05-29 11:27:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(103, 3, 395.00, 'processing', 'Blk 95, Sample St, Los Baños, 4030', '2026-05-29 12:08:00', '2026-05-29 12:41:00', 'Bank Transfer', 'paid', NULL, NULL, 'Express', NULL),
(104, 4, 548.00, 'shipped', 'Blk 96, Sample St, Los Baños, 4030', '2026-05-29 12:53:00', '2026-05-29 20:53:00', 'GCash', 'released', '2026-05-29 20:53:00', 'LBC', 'Express', 'TRK20260529104'),
(105, 2, 149.00, 'delivered', 'Blk 97, Sample St, Los Baños, 4030', '2026-05-29 13:34:00', '2026-05-31 08:17:00', 'Cash on Delivery', 'released', '2026-05-29 21:34:00', 'LBC', 'Standard', 'TRK20260529105'),
(106, 3, 943.00, 'processing', 'Blk 98, Sample St, Los Baños, 4030', '2026-05-29 14:14:00', '2026-05-29 14:50:00', 'GCash', 'paid', NULL, NULL, 'Standard', NULL),
(107, 4, 316.00, 'pending', 'Blk 99, Sample St, Los Baños, 4030', '2026-05-29 15:00:00', '2026-05-29 15:00:00', 'Cash on Delivery', 'pending', NULL, NULL, NULL, NULL),
(108, 2, 690.00, 'processing', 'Blk 100, Sample St, Los Baños, 4030', '2026-05-29 15:35:00', '2026-05-29 16:00:00', 'Bank Transfer', 'paid', NULL, NULL, 'Express', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`) VALUES
(1, 1, 2, 4, 149.00),
(2, 1, 3, 1, 149.00),
(3, 1, 4, 1, 149.00),
(4, 1, 2, 1, 149.00),
(5, 1, 1, 1, 149.00),
(6, 2, 1, 1, 149.00),
(7, 2, 1, 5, 149.00),
(8, 2, 1, 1, 149.00),
(9, 2, 2, 1, 149.00),
(10, 2, 2, 1, 149.00),
(11, 3, 2, 1, 149.00),
(12, 4, 1, 1, 149.00),
(13, 4, 2, 1, 149.00),
(14, 5, 7, 1, 79.00),
(15, 6, 7, 10, 79.00),
(16, 7, 1, 1, 149.00),
(17, 8, 7, 5, 79.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `category` enum('atm_magnet','custom_magnet','other') DEFAULT 'other',
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category`, `stock`, `image`, `is_active`, `created_at`) VALUES
(1, 'BTS Ref Magnet', 'High-quality BTS reference magnet set', 149.00, 'atm_magnet', 5, 'bts.jpg', 1, '2026-04-11 08:18:01'),
(2, 'Demon Slayer Magnet', 'Demon Slayer character magnet collection', 149.00, 'custom_magnet', 1, 'demon_slayer.jpg', 1, '2026-04-11 08:18:01'),
(3, 'Jujutsu Kaisen Magnet', 'JJK fan favorite magnet set', 149.00, 'atm_magnet', 25, 'jjk.jpg', 1, '2026-04-11 08:18:01'),
(4, 'Straw Hat Magnet', 'One Piece Straw Hat crew magnets', 149.00, 'atm_magnet', 40, 'strawhats.jpg', 1, '2026-04-11 08:18:01'),
(5, 'TRIAL magnet', '', 79.00, 'atm_magnet', 50, 'prod_6a002234991de.webp', 0, '2026-05-10 06:14:12'),
(6, 'TRIAL magnet', 'Trial Product', 79.00, 'atm_magnet', 50, 'prod_6a002240f3e83.webp', 0, '2026-05-10 06:14:25'),
(7, 'TRIAL magnet', 'Trial Product', 79.00, 'atm_magnet', 50, 'prod_6a00224728859.webp', 1, '2026-05-10 06:14:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@armieprints.com', '09000000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-04-11 08:17:50', '2026-04-11 08:17:50'),
(2, 'Admin User', 'adminuser@gmail.com', '', '$2y$10$.fpVl1lfpQ3cznRNPdJ9Zu4oeu8vFAcgM8AhrJ8o4Znmanqp7gpGO', 'customer', '2026-04-11 08:24:37', '2026-04-11 08:24:37'),
(3, 'Test User', 'testuser@gmail.com', '1225554555565', '$2y$10$EwlT36gFDq17zlZ1GKiU1.TmOL8OIU/OrV.9Yvp.iZlA/4gjB5Bga', 'customer', '2026-04-11 08:37:44', '2026-04-11 08:37:44'),
(4, 'Test User', 'test@gmail.com', '', '$2y$10$qMRWEb5cQC.B4QzbcJeUm./4glNW.SMiET09hd8sFAWQM4HtD4H8a', 'customer', '2026-05-07 13:22:30', '2026-05-07 13:22:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `custom_orders`
--
ALTER TABLE `custom_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `custom_orders`
--
ALTER TABLE `custom_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `custom_orders`
--
ALTER TABLE `custom_orders`
  ADD CONSTRAINT `custom_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
