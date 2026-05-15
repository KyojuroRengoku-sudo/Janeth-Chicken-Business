-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2026 at 06:36 AM
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
-- Database: `inventory_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `daily_expenses`
--

CREATE TABLE `daily_expenses` (
  `id` int(11) NOT NULL,
  `expense_date` date NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_expenses`
--

INSERT INTO `daily_expenses` (`id`, `expense_date`, `category`, `description`, `amount`, `created_at`) VALUES
(1, '2026-04-01', 'Supplies', 'Ice', 155.00, '2026-05-09 03:01:01');

-- --------------------------------------------------------

--
-- Table structure for table `janeth_records`
--

CREATE TABLE `janeth_records` (
  `id` int(11) NOT NULL,
  `record_date` date NOT NULL,
  `product_id` int(11) NOT NULL,
  `yesterday_qty` int(11) DEFAULT 0,
  `stock_in` int(11) DEFAULT 0,
  `remaining_qty` int(11) DEFAULT 0,
  `sold` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `janeth_records`
--

INSERT INTO `janeth_records` (`id`, `record_date`, `product_id`, `yesterday_qty`, `stock_in`, `remaining_qty`, `sold`, `created_at`, `updated_at`) VALUES
(46, '2026-05-01', 2, 0, 6, 0, 6, '2026-05-09 01:42:06', '2026-05-09 01:52:18'),
(47, '2026-05-01', 14, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(48, '2026-05-01', 6, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(49, '2026-05-01', 15, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(50, '2026-05-01', 11, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(51, '2026-05-01', 7, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(52, '2026-05-01', 1, 0, 20, 0, 20, '2026-05-09 01:42:06', '2026-05-09 01:51:26'),
(53, '2026-05-01', 9, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(54, '2026-05-01', 12, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(55, '2026-05-01', 13, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(56, '2026-05-01', 8, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(57, '2026-05-01', 3, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(58, '2026-05-01', 5, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(59, '2026-05-01', 4, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(60, '2026-05-01', 10, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(61, '2026-05-01', 26, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(62, '2026-05-01', 21, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(63, '2026-05-01', 20, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(64, '2026-05-01', 19, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(65, '2026-05-01', 18, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(66, '2026-05-01', 17, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(67, '2026-05-01', 16, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(68, '2026-05-01', 24, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(69, '2026-05-01', 27, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(70, '2026-05-01', 25, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(71, '2026-05-01', 28, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(72, '2026-05-01', 23, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(73, '2026-05-01', 22, 0, 0, 0, 0, '2026-05-09 01:42:06', '2026-05-09 01:42:06'),
(186, '2026-04-01', 1, 0, 22, 2, 20, '2026-05-09 02:52:47', '2026-05-09 02:53:38'),
(187, '2026-04-01', 2, 0, 20, 2, 18, '2026-05-09 02:52:47', '2026-05-09 02:53:43'),
(188, '2026-04-01', 3, 0, 5, 7, 0, '2026-05-09 02:52:47', '2026-05-09 02:53:48'),
(189, '2026-04-01', 4, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(190, '2026-04-01', 5, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(191, '2026-04-01', 6, 0, 0, 4, 0, '2026-05-09 02:52:47', '2026-05-09 02:53:58'),
(192, '2026-04-01', 7, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(193, '2026-04-01', 8, 0, 15, 0, 15, '2026-05-09 02:52:47', '2026-05-09 02:53:13'),
(194, '2026-04-01', 9, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(195, '2026-04-01', 10, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(196, '2026-04-01', 11, 0, 15, 0, 15, '2026-05-09 02:52:47', '2026-05-09 02:53:18'),
(197, '2026-04-01', 12, 0, 10, 0, 10, '2026-05-09 02:52:47', '2026-05-09 02:53:21'),
(198, '2026-04-01', 13, 0, 10, 3, 7, '2026-05-09 02:52:47', '2026-05-09 02:54:11'),
(199, '2026-04-01', 14, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(200, '2026-04-01', 15, 0, 0, 4, 0, '2026-05-09 02:52:47', '2026-05-09 02:54:13'),
(201, '2026-04-01', 16, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(202, '2026-04-01', 17, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(203, '2026-04-01', 18, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(204, '2026-04-01', 19, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(205, '2026-04-01', 20, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(206, '2026-04-01', 21, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(207, '2026-04-01', 22, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(208, '2026-04-01', 23, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(209, '2026-04-01', 24, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(210, '2026-04-01', 25, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(211, '2026-04-01', 26, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(212, '2026-04-01', 27, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(213, '2026-04-01', 28, 0, 0, 0, 0, '2026-05-09 02:52:47', '2026-05-09 02:52:47'),
(690, '2026-03-31', 1, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(691, '2026-03-31', 2, 0, 0, 1, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(692, '2026-03-31', 3, 0, 0, 5, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:10'),
(693, '2026-03-31', 4, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(694, '2026-03-31', 5, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(695, '2026-03-31', 6, 0, 0, 1, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:15'),
(696, '2026-03-31', 7, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(697, '2026-03-31', 8, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(698, '2026-03-31', 9, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(699, '2026-03-31', 10, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(700, '2026-03-31', 11, 0, 0, 4, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:22'),
(701, '2026-03-31', 12, 0, 0, 1, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:27'),
(702, '2026-03-31', 13, 0, 0, 8, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:39'),
(703, '2026-03-31', 14, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(704, '2026-03-31', 15, 0, 0, 5, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:45'),
(705, '2026-03-31', 16, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(706, '2026-03-31', 17, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(707, '2026-03-31', 18, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(708, '2026-03-31', 19, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(709, '2026-03-31', 20, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(710, '2026-03-31', 21, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(711, '2026-03-31', 22, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(712, '2026-03-31', 23, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(713, '2026-03-31', 24, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(714, '2026-03-31', 25, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(715, '2026-03-31', 26, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(716, '2026-03-31', 27, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02'),
(717, '2026-03-31', 28, 0, 0, 0, 0, '2026-05-09 02:55:02', '2026-05-09 02:55:02');

-- --------------------------------------------------------

--
-- Table structure for table `liquidations`
--

CREATE TABLE `liquidations` (
  `id` int(11) NOT NULL,
  `liquidation_date` date NOT NULL,
  `opening_cash` decimal(10,2) DEFAULT 0.00,
  `cash_sales` decimal(10,2) DEFAULT 0.00,
  `total_expenses` decimal(10,2) DEFAULT 0.00,
  `stock_cost` decimal(10,2) DEFAULT 0.00,
  `actual_cash` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`extra_data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('Chicken','Frozen') NOT NULL DEFAULT 'Chicken',
  `selling_price` decimal(10,2) DEFAULT 0.00,
  `low_stock_threshold` int(11) DEFAULT 10,
  `visible_input` tinyint(1) DEFAULT 1,
  `visible_dashboard` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `selling_price`, `low_stock_threshold`, `visible_input`, `visible_dashboard`, `is_deleted`, `deleted_at`, `created_at`) VALUES
(1, 'Fresh Whole Chicken', 'Chicken', 185.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(2, 'BackBones', 'Chicken', 120.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(3, 'Neck', 'Chicken', 100.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(4, 'SKT Bones', 'Chicken', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(5, 'Skin', 'Chicken', 120.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(6, 'Cuttings', 'Chicken', 220.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(7, 'Fillet', 'Chicken', 150.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(8, 'Liver', 'Chicken', 190.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(9, 'Gizzard/\"B\"', 'Chicken', 150.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(10, 'Atay Baticon', 'Chicken', 150.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(11, 'Feet', 'Chicken', 100.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(12, 'Heads', 'Chicken', 40.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(13, 'Intestine', 'Chicken', 105.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(14, 'Crps/Prvn/BTC', 'Chicken', 120.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(15, 'Dugo', 'Chicken', 40.00, 10, 1, 1, 0, NULL, '2026-05-09 01:09:12'),
(16, 'CHA K', 'Frozen', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:33:22'),
(17, 'CHA J', 'Frozen', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:33:22'),
(18, 'BSTR KL', 'Frozen', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:33:22'),
(19, 'BSTR J/R', 'Frozen', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:33:22'),
(20, 'BS CLA K/J', 'Frozen', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:33:22'),
(21, 'BS CHZ K/J', 'Frozen', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:33:22'),
(22, 'WCH', 'Frozen', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:33:22'),
(23, 'SWTH', 'Frozen', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:33:22'),
(24, 'IQFL', 'Frozen', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:33:22'),
(25, 'LM K', 'Frozen', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:33:22'),
(26, 'BLG', 'Frozen', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:33:22'),
(27, 'KL', 'Frozen', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:33:22'),
(28, 'NO.1', 'Frozen', 0.00, 10, 1, 1, 0, NULL, '2026-05-09 01:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `registration_requests`
--

CREATE TABLE `registration_requests` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `requested_role` enum('admin','staff') DEFAULT 'staff',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration_requests`
--

INSERT INTO `registration_requests` (`id`, `username`, `password_hash`, `requested_role`, `status`, `created_at`, `reviewed_at`) VALUES
(1, 'jireh', '$2y$10$bQdl7mcqeGvxzXS6AQFgp.7u1.kEf.OnuOWniK7yS1RmbEOJ7RBj6', 'admin', 'approved', '2026-05-03 19:30:10', '2026-05-04 03:30:34');

-- --------------------------------------------------------

--
-- Table structure for table `stock_entries`
--

CREATE TABLE `stock_entries` (
  `id` int(11) NOT NULL,
  `record_date` date NOT NULL,
  `product_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact`, `notes`) VALUES
(1, 'Default Supplier', 'N/A', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$bxe9UUaiwUaos6oKRC.y0OYEwZh2dwBD/SNnF/hJ9kAnZN9cWgfFu', 'admin', '2026-05-01 17:07:53'),
(2, 'staff1', '$2y$10$0h9lXc9U89HfBccv2vIHF.Dt6E8ksy5nLhAXcL/imhC19ZjVaQJbW', 'staff', '2026-05-01 17:07:53'),
(5, 'jireh', '$2y$10$bQdl7mcqeGvxzXS6AQFgp.7u1.kEf.OnuOWniK7yS1RmbEOJ7RBj6', 'admin', '2026-05-03 19:30:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daily_expenses`
--
ALTER TABLE `daily_expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `janeth_records`
--
ALTER TABLE `janeth_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date_product` (`record_date`,`product_id`),
  ADD KEY `janeth_records_ibfk_1` (`product_id`);

--
-- Indexes for table `liquidations`
--
ALTER TABLE `liquidations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `liquidation_date` (`liquidation_date`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registration_requests`
--
ALTER TABLE `registration_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_entries`
--
ALTER TABLE `stock_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `stock_entries_ibfk_1` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `daily_expenses`
--
ALTER TABLE `daily_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `janeth_records`
--
ALTER TABLE `janeth_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=942;

--
-- AUTO_INCREMENT for table `liquidations`
--
ALTER TABLE `liquidations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `registration_requests`
--
ALTER TABLE `registration_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_entries`
--
ALTER TABLE `stock_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `janeth_records`
--
ALTER TABLE `janeth_records`
  ADD CONSTRAINT `janeth_records_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_entries`
--
ALTER TABLE `stock_entries`
  ADD CONSTRAINT `stock_entries_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_entries_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
