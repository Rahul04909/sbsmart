-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 09, 2025 at 04:11 PM
-- Server version: 5.7.23-23
-- PHP Version: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `invest13_sbsmart`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `name`, `email`, `password`, `remember_token`, `reset_token`, `reset_expires`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin@sbsmart.in', '$2y$10$HQuTXyU.BLznapetxq/X.e44f1J0AI6JQ5ZisgvVNQysZSXeDyM76', NULL, NULL, NULL, '2025-12-01 03:53:59', '2025-12-01 04:07:32');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(5, 2, 3, 3, '2025-12-05 15:20:09', '2025-12-06 11:54:13'),
(6, 5, 3, 1, '2025-12-06 14:57:44', '2025-12-06 14:57:44'),
(12, 6, 2, 1, '2025-12-06 17:44:51', '2025-12-06 17:44:51'),
(16, 3, 1, 1, '2025-12-08 20:13:57', '2025-12-08 20:13:57'),
(17, 2, 7, 1, '2025-12-09 13:49:11', '2025-12-09 13:49:11');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'SIEMENS', 'siemens', 'Siemens brand items', '2025-09-21 12:20:24'),
(2, 'BCH', 'bch', 'BCH brand items', '2025-09-21 12:20:24'),
(3, 'FLENDER', 'flender', 'Flender brand items', '2025-09-21 12:20:24'),
(4, 'INNOMOTICS', 'innomotics', 'Innomotics brand items', '2025-09-21 12:20:24'),
(5, 'LAPP', 'lapp', 'LAPP brand items', '2025-09-21 12:20:24'),
(6, 'OTHERS', 'others', 'Other brands', '2025-09-21 12:20:24');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','failed','cancelled') DEFAULT 'pending',
  `razorpay_order_id` varchar(100) DEFAULT NULL,
  `razorpay_payment_id` varchar(100) DEFAULT NULL,
  `razorpay_signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `name`, `email`, `phone`, `address`, `total`, `status`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `created_at`) VALUES
(1, 'Pramjeet Yadav', 'helpdesk@mineib.com', '09821113178', 'First Floor, Shop No 3, Tigaon road, Bhatola\r\nSector 82', 458.00, 'pending', 'order_RBV6khYeYDyWMz', NULL, NULL, '2025-08-30 09:43:51'),
(2, 'PRAMJEET', 'PR4M@OUTLOOK.COM', '6375072316', 'TONK ROAD', 229.00, 'paid', 'order_RBVPm9WoqMQNSG', 'pay_RBVPz2JvqaXwoF', '4ec9f100968f74b7fe757d42aca465e9d111bfe8816281408f357b5b98f61d6b', '2025-08-30 10:01:52'),
(3, 'Pramjeet Yadav', 'helpdesk@mineib.com', '09821113178', 'First Floor, Shop No 3, Tigaon road, Bhatola\r\nSector 82', 229.00, 'paid', 'order_RBW1Ik8ifOxATE', 'pay_RBW1SAkI6u1Gyp', NULL, '2025-08-30 10:37:23'),
(4, 'Pramjeet', 'raopramjeetyadav@gmail.com', '08950591781', 'Flat no 102\r\nTower 2, Rps savana city, Sector 88', 79.00, 'pending', 'order_RBW32IFHIAvjgc', NULL, NULL, '2025-08-30 10:39:02'),
(5, 'Pramjeet Yadav', 'helpdesk@mineib.com', '09821113178', 'First Floor, Shop No 3, Tigaon road, Bhatola\r\nSector 82', 14320.00, 'pending', NULL, NULL, NULL, '2025-11-28 04:10:52'),
(6, 'Pramjeet Yadav', 'helpdesk@mineib.com', '09821113178', 'First Floor, Shop No 3, Tigaon road, Bhatola\r\nSector 82', 13390.00, 'pending', NULL, NULL, NULL, '2025-11-28 05:02:44'),
(7, 'pramj', 'raopramjeetyadav@gmail.com', '09821113170', 'na', 13390.00, 'pending', NULL, NULL, NULL, '2025-11-28 05:07:14'),
(8, 'deepal', 'deepal@gmail.com', '9898989898', 'ha', 14320.00, 'pending', NULL, NULL, NULL, '2025-11-28 05:35:50'),
(9, 'Ritesh Singh', 'admin@sbsmart.in', '1234567890', 'Building Number: 601\r\nStreet Name: Hiranandani Gardens\r\nStreet Address: Olympia, Central Ave, Powai\r\nState: Maharashtra\r\nCity: Mumbai\r\nPost Code: 400076', 2200.00, 'pending', NULL, NULL, NULL, '2025-12-05 09:57:12'),
(10, 'Ritesh Singh', 'amit.school@example.com', '1234567890', '50 ,Vijay Nagar, Scheme 54, Near Meghdoot Garden\r\nState: Madhya Pradesh\r\nCity: Indore\r\nPost Code: 452010', 2200.00, 'pending', NULL, NULL, NULL, '2025-12-05 11:48:16'),
(11, 'Ritesh Singh', 'amit.school@example.com', '1234567890', 'Building Number: 22\r\nStreet Name: C. G. Road\r\nStreet Address: Unit 12, Dev Arc Mall, Near Panchvati Circle\r\nState: Gujarat\r\nCity: Ahmedabad\r\nPost Code: 380009', 3800.00, 'pending', NULL, NULL, NULL, '2025-12-05 16:50:40'),
(12, 'Ritesh Singh', 'amit.school@example.com', '1234567890', 'sdxfcghn', 3800.00, 'pending', NULL, NULL, NULL, '2025-12-06 04:36:03'),
(13, 'Ritesh Singh', 'admin@sbsmart.in', '9810833885', '1D - 45A\r\nN.I.T', 11000.00, 'pending', NULL, NULL, NULL, '2025-12-06 04:47:02'),
(14, 'Ritesh Singh', 'amit.school@example.com', '1234567890', '12345', 1600.00, 'pending', NULL, NULL, NULL, '2025-12-06 10:24:01'),
(15, 'Ritesh Singh', 'amit.school@example.com', '1599578624', 'Asd', 1000.00, 'pending', NULL, NULL, NULL, '2025-12-06 11:03:38'),
(16, 'Ritesh Singh', 'amit.school@example.com', '1234567890', 'xcv', 1000.00, 'pending', NULL, NULL, NULL, '2025-12-06 11:15:53'),
(17, 'Ritesh Singh', 'amit.school@example.com', '', 'asdrtfygu', 0.05, 'pending', NULL, NULL, NULL, '2025-12-06 11:48:45'),
(18, 'Ritesh Singh', 'amit.school@example.com', '8130363264', 'sonia vihar', 0.05, 'pending', NULL, NULL, NULL, '2025-12-06 11:51:55'),
(19, 'Ritesh Singh', 'rajputritesh1907@gmail.com', '1234567890', 'sonia vihar', 0.05, 'pending', NULL, NULL, NULL, '2025-12-06 12:03:17'),
(20, 'Ritesh Singh', 'rajputritesh1907@gmail.com', '8130363264', 'sonia vihar', 0.05, 'pending', NULL, NULL, NULL, '2025-12-06 12:09:07'),
(21, 'Ritesh Singh', 'rajputritesh1907@gmail.com', '8130363264', 'sonia vihar', 2000.00, 'pending', NULL, NULL, NULL, '2025-12-06 12:14:27'),
(22, 'Ritesh Singh', 'rajputritesh1907@gmail.com', '8130363264', 'sonia vihar', 1000.00, 'pending', NULL, NULL, NULL, '2025-12-06 12:15:05'),
(23, 'Pram', 'jeet.propertystation@gmail.com', '09311053102', 'rps savana', 1000.00, 'pending', NULL, NULL, NULL, '2025-12-06 12:17:33'),
(24, 'Ritesh Singh', 'amit.school@example.com', '1234567890', 'fcfg', 0.05, 'pending', NULL, NULL, NULL, '2025-12-07 05:06:46'),
(25, 'Ritesh Singh', 'amit.school@example.com', '8130363264', 'km', 1.00, 'paid', NULL, NULL, NULL, '2025-12-07 05:08:58'),
(26, 'Ritesh Singh', 'amit.school@example.com', '1234567890', 'sdcv', 1.00, 'pending', NULL, NULL, NULL, '2025-12-07 05:18:38'),
(27, 'Ritesh Singh', 'amit.school@example.com', '1234567890', 'ghjhgf', 1.00, 'pending', NULL, NULL, NULL, '2025-12-07 05:26:27'),
(28, 'Ritesh Singh', 'amit.school@example.com', '1234567890', 'dfg', 1.00, 'pending', NULL, NULL, NULL, '2025-12-07 05:31:39'),
(29, 'Ritesh Singh', 'amit.school@example.com', '8130363264', 'asdc', 1.00, 'paid', '534143285837', '114140980193', NULL, '2025-12-07 09:23:48'),
(30, 'Ritesh Singh', 'amit.school@example.com', '1234567890', 'gh', 1000.00, 'paid', '534113313573', '114140993757', NULL, '2025-12-07 10:25:22'),
(31, 'Ritesh Singh', 'amit.school@example.com', '1234567890', 'dfg', 1000.00, 'failed', NULL, '114140996492', NULL, '2025-12-07 10:26:49'),
(32, 'Ritesh Singh', 'amit.school@example.com', '8130363264', 'qSADS', 3000.00, 'pending', NULL, NULL, NULL, '2025-12-07 11:15:39'),
(33, 'Ritesh Singh', 'amit.school@example.com', '1234567890', 'szdxfcg', 1.00, 'pending', NULL, NULL, NULL, '2025-12-08 14:44:30');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `title`, `price`, `qty`) VALUES
(5, 5, 32, 'Siemens MCCB 3VM1096-2ED42-0AA0 - MCCB_IEC_FS100_16A_4P_16KA_TM_ FTFM', 14320.00, 1),
(6, 6, 29, 'Siemens MCCB 3VM1010-3ED32-0AA0 - MCCB_IEC_FS100_100A_3P_25KA_TM_ FTFM', 13390.00, 1),
(9, 9, 3, 'BCH Enclosure System IP65', 2200.00, 1),
(10, 10, 3, 'BCH Enclosure System IP65', 2200.00, 1),
(11, 11, 3, 'BCH Enclosure System IP65', 2200.00, 1),
(12, 11, 2, 'Enclosure Metal Box 12x10', 1600.00, 1),
(13, 12, 3, 'BCH Enclosure System IP65', 2200.00, 1),
(14, 12, 2, 'Enclosure Metal Box 12x10', 1600.00, 1),
(15, 13, 3, 'BCH Enclosure System IP65', 2200.00, 5),
(16, 14, 2, 'Enclosure Metal Box 12x10', 1600.00, 1),
(17, 15, 1, 'Enclosure Metal Box 10x8', 1000.00, 1),
(18, 16, 1, 'Enclosure Metal Box 10x8', 1000.00, 1),
(19, 17, 1, 'Enclosure Metal Box 10x8', 0.05, 1),
(20, 18, 1, 'Enclosure Metal Box 10x8', 0.05, 1),
(21, 19, 1, 'Enclosure Metal Box 10x8', 0.05, 1),
(22, 20, 1, 'Enclosure Metal Box 10x8', 0.05, 1),
(23, 21, 2, 'Enclosure Metal Box 12x10', 1000.00, 2),
(24, 22, 2, 'Enclosure Metal Box 12x10', 1000.00, 1),
(25, 23, 2, 'Enclosure Metal Box 12x10', 1000.00, 1),
(26, 24, 1, 'Enclosure Metal Box 10x8', 0.05, 1),
(27, 25, 1, 'Enclosure Metal Box 10x8', 1.00, 1),
(28, 26, 1, 'Enclosure Metal Box 10x8', 1.00, 1),
(29, 27, 1, 'Enclosure Metal Box 10x8', 1.00, 1),
(30, 28, 1, 'Enclosure Metal Box 10x8', 1.00, 1),
(31, 29, 1, 'Enclosure Metal Box 10x8', 1.00, 1),
(32, 30, 2, 'Enclosure Metal Box 12x10', 1000.00, 1),
(33, 31, 2, 'Enclosure Metal Box 12x10', 1000.00, 1),
(34, 32, 2, 'Enclosure Metal Box 12x10', 1000.00, 3),
(35, 33, 1, 'Enclosure Metal Box 10x8', 1.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_otps`
--

CREATE TABLE `password_otps` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `password_otps`
--

INSERT INTO `password_otps` (`id`, `user_id`, `otp_hash`, `expires_at`, `used`, `created_at`) VALUES
(11, 2, '$2y$10$PUxfjxIX9AQ7jwTWO1DJIuFklmUo/kdMDfmul0zjoqIo82YOWrBHm', '2025-12-06 10:15:15', 0, '2025-12-06 14:30:15');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(5, 1, '406382', '2025-12-06 09:59:53', '2025-12-06 14:14:54'),
(6, 2, '532827', '2025-12-06 10:00:56', '2025-12-06 14:15:56');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `hsn_code` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `short_desc` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `short_description` text,
  `description` text,
  `price` decimal(10,2) DEFAULT '0.00',
  `mrp` decimal(10,2) DEFAULT '0.00',
  `stock` int(11) DEFAULT '0',
  `tags` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `images` text,
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `hsn_code`, `title`, `short_desc`, `slug`, `category_id`, `subcategory_id`, `short_description`, `description`, `price`, `mrp`, `stock`, `tags`, `image`, `images`, `status`, `created_at`, `updated_at`) VALUES
(5, 'BIL-50000', '8538', 'Enclosure Systems', '', '', 2, 14, 'Bhartia Boxes - BIL-50000', 'SINGLE DOOR BOX - Size - W-200x H-200x D-150 in mm - BIL-50000', 2704.00, 3380.00, 10, '', 'p_6937db4a9a859.jpg', '[]', 1, '2025-12-09 07:57:28', '2025-12-09 08:18:18'),
(6, 'BIL-50010', '8538', 'Enclosure Systems', 'Bhartia Boxes - BIL-50010', '', 2, 14, 'SINGLE DOOR BOX - Size - W-200x H-300x D-150 in mm - BIL-50010', 'Enclosure Systems', 3870.00, 4599.00, 10, '', 'p_6937db3e02571.jpg', '[]', 1, '2025-12-09 08:04:14', '2025-12-09 08:18:06'),
(7, 'BIL-50020', '8538', 'Enclosure Systems', '', '', 2, 14, 'Bhartia Boxes - BIL-50020', 'SINGLE DOOR BOX - Size - W-250x H-300x D-150 in mm - BIL-50020', 3216.00, 4020.00, 10, '', 'p_6937db2d246f2.jpg', '[]', 1, '2025-12-09 08:09:58', '2025-12-09 08:17:49'),
(8, 'BIL-50030', '8538', 'Enclosure Systems', '', '', 2, 14, 'Bhartia Boxes - BIL-50030', 'SINGLE DOOR BOX - Size - W-300x H-300x D-150 in mm - BIL-50030', 3468.00, 4335.00, 10, '', 'p_6937db0ceb5ca.jpg', '[]', 1, '2025-12-09 08:11:29', '2025-12-09 08:17:23'),
(9, 'BIL-50050', '8538', 'Enclosure Systems', '', '', 2, 14, 'Bhartia Boxes - BIL-50050', 'SINGLE DOOR BOX - Size - W-300x H-300x D-200 in mm - BIL-50050', 3696.00, 4620.00, 10, '', 'p_6937db040420f.jpg', '[]', 1, '2025-12-09 08:12:14', '2025-12-09 08:17:08');

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`id`, `category_id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 1, 'Enclosure Systems', 'Limit Switch', NULL, '2025-09-21 12:20:24'),
(2, 1, 'Limit Switch \n', 'solenoids', NULL, '2025-09-21 12:20:24'),
(3, 1, 'Timmer  ', 'switchgear', NULL, '2025-09-21 12:20:24'),
(4, 1, 'Foot Switch ', 'panels', NULL, '2025-09-21 12:20:24'),
(5, 1, 'Plug & Sockets\n', 'gearbox', NULL, '2025-09-21 12:20:24'),
(6, 1, 'Limit Switch1', 'enclosure-systems', 'BCH-Enclosure Systems', '2025-12-01 04:56:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verify_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `verify_token`, `is_verified`, `is_active`, `reset_token`, `reset_expires_at`, `created_at`, `updated_at`) VALUES
(1, 'Ritesh Singh', 'ritesh.singh@venetsmedia.com', NULL, '$2y$10$o/6YqxptiZVSqUyZ8CPyH.wab9VCg5G1m.Ws94fIJ8qZ.mrp2TtoS', 'ca11eb6866c39469953854672a103d0eea94f9d1b0a11c2e', 0, 1, NULL, NULL, '2025-12-05 08:55:42', '2025-12-05 13:28:46'),
(2, 'Ritesh Singh', 'admin@sbsmart.in', NULL, '$2y$10$iitiuFmCnhDVQ/Hxts90XOr1YLqf906HGzfwDG7wXWjWy6Q3B2hMi', '75bfa9c66012b978a5bc0842d5b2eb01f6db579542af04e3', 0, 1, NULL, NULL, '2025-12-05 08:57:08', '2025-12-05 13:28:46'),
(3, 'Ritesh Singh', 'amit.school@example.com', '1234567890', '$2y$10$3ADnxzm6LwhD5ysExcUnBOmZkYxBUHSfW.dgjncFHbeB3Gz5YBvX6', 'ef9102d7bcadea01f3c195e3817cdb49bb240b741c9f3f6d', 0, 1, NULL, NULL, '2025-12-05 11:44:55', '2025-12-07 11:01:22'),
(6, 'Ritesh Singh', 'rajputritesh1907@gmail.com', '8130363264', '$2y$10$yjm8D36EpTBqxSzqPO9d../264uYgm3FVNIOoAYVbTQYNh7EPGn2e', NULL, 0, 1, NULL, NULL, '2025-12-06 17:32:27', '2025-12-06 17:32:27'),
(7, 'Pram', 'jeet.propertystation@gmail.com', '09311053102', '$2y$10$.DOqQyIwP7.vaZrcK3OGV.pqjmYFgufFxBLD4cEjB3XWQp0t0lI5.', NULL, 0, 1, NULL, NULL, '2025-12-06 17:45:32', '2025-12-06 17:45:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `fk_cart_products` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `password_otps`
--
ALTER TABLE `password_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_used` (`user_id`,`used`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_token` (`user_id`,`token`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_category_name` (`category_id`,`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`),
  ADD KEY `idx_verify_token` (`verify_token`),
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `password_otps`
--
ALTER TABLE `password_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_otps`
--
ALTER TABLE `password_otps`
  ADD CONSTRAINT `fk_otps_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_resets_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
