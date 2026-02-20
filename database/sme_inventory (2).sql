-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 20, 2026 at 08:18 AM
-- Server version: 9.6.0
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sme_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-livewire-rate-limiter:16d36dff9abd246c67dfac3e63b993a169af77e6', 'i:1;', 1771574949),
('laravel-cache-livewire-rate-limiter:16d36dff9abd246c67dfac3e63b993a169af77e6:timer', 'i:1771574949;', 1771574949),
('laravel-cache-low_stock_ids', 'O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;i:8;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1771571960),
('laravel-cache-low_stock_ids_1', 'O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;i:8;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1771575369),
('laravel-cache-low_stock_ids_2', 'O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1771574892),
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:47:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:17:\"view-any issuance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:15:\"create issuance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:15:\"update issuance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"delete issuance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:16:\"release issuance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:14:\"issue issuance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:15:\"return issuance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:15:\"cancel issuance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:16:\"view-any restock\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:14:\"create restock\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:14:\"update restock\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:11;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:14:\"delete restock\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:12;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:15:\"deliver restock\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:13;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:14:\"return restock\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:14;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:14:\"cancel restock\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:15;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:13:\"view-any site\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:16;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:11:\"create site\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:17;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:11:\"update site\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:18;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:11:\"delete site\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:19;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:13:\"view-any item\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:20;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:11:\"create item\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:21;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:11:\"update item\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:22;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:11:\"delete item\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:23;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:14:\"view-any stock\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:24;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:12:\"create stock\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:25;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:12:\"update stock\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:26;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:12:\"delete stock\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:27;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:17:\"view-any category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:28;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:15:\"create category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:29;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:15:\"update category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:30;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:15:\"delete category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:31;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:13:\"view-any user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:5;}}i:32;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:11:\"create user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:5;}}i:33;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:11:\"update user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:5;}}i:34;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:11:\"delete user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:5;}}i:35;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:19:\"view-any permission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:36;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:17:\"create permission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:37;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:17:\"update permission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:38;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:17:\"delete permission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:39;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:13:\"view-any role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:40;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:11:\"create role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:41;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:11:\"update role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:42;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:11:\"delete role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:43;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:22:\"view-any issuance-type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:44;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:20:\"create issuance-type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:5;}}i:45;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:20:\"update issuance-type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:5;}}i:46;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:20:\"delete issuance-type\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:5;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"IT developer\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:10:\"Management\";s:1:\"c\";s:3:\"web\";}}}', 1771661135);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`, `department_id`) VALUES
(2, 'category 1', NULL, '2026-02-19 22:50:02', '2026-02-19 22:50:02', 1);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Operation', 'operation', '2026-02-19 20:53:57', '2026-02-19 20:53:57'),
(2, 'HR', 'hr', '2026-02-19 20:54:28', '2026-02-19 20:54:28');

-- --------------------------------------------------------

--
-- Table structure for table `department_item`
--

CREATE TABLE `department_item` (
  `department_id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department_item`
--

INSERT INTO `department_item` (`department_id`, `item_id`) VALUES
(1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `department_user`
--

CREATE TABLE `department_user` (
  `department_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department_user`
--

INSERT INTO `department_user` (`department_id`, `user_id`) VALUES
(1, 2),
(2, 2),
(1, 4),
(2, 5);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issuances`
--

CREATE TABLE `issuances` (
  `id` bigint UNSIGNED NOT NULL,
  `site_id` bigint UNSIGNED NOT NULL,
  `issued_to` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issued_at` date DEFAULT NULL,
  `partial_at` date DEFAULT NULL,
  `pending_at` date DEFAULT NULL,
  `released_at` date DEFAULT NULL,
  `returned_at` date DEFAULT NULL,
  `cancelled_at` date DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `issuance_type_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `issuances`
--

INSERT INTO `issuances` (`id`, `site_id`, `issued_to`, `issued_at`, `partial_at`, `pending_at`, `released_at`, `returned_at`, `cancelled_at`, `note`, `status`, `created_at`, `updated_at`, `department_id`, `issuance_type_id`) VALUES
(51, 2, 'sample', NULL, '2026-02-20', '2026-02-20', '2026-02-20', NULL, NULL, NULL, 'released', '2026-02-19 23:26:44', '2026-02-19 23:27:07', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `issuance_items`
--

CREATE TABLE `issuance_items` (
  `id` bigint UNSIGNED NOT NULL,
  `issuance_id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL,
  `released_quantity` int DEFAULT NULL,
  `remaining_quantity` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `issuance_items`
--

INSERT INTO `issuance_items` (`id`, `issuance_id`, `item_id`, `size`, `quantity`, `released_quantity`, `remaining_quantity`, `created_at`, `updated_at`) VALUES
(62, 51, 5, 'S', 20, 20, 0, '2026-02-19 23:26:44', '2026-02-19 23:27:07');

-- --------------------------------------------------------

--
-- Table structure for table `issuance_logs`
--

CREATE TABLE `issuance_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `issuance_id` bigint UNSIGNED NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `performed_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `issuance_logs`
--

INSERT INTO `issuance_logs` (`id`, `issuance_id`, `action`, `performed_by`, `note`, `created_at`, `updated_at`) VALUES
(31, 51, 'pending', 'developer', NULL, '2026-02-19 23:26:44', '2026-02-19 23:26:44'),
(32, 51, 'partial', 'developer', '[{\"label\":\"item 1 (S)\",\"released\":10,\"remaining\":10,\"ordered\":20}]', '2026-02-19 23:26:51', '2026-02-19 23:26:51'),
(33, 51, 'released', 'developer', '[{\"label\":\"item 1 (S)\",\"released\":10,\"remaining\":0,\"ordered\":20}]', '2026-02-19 23:27:07', '2026-02-19 23:27:07');

-- --------------------------------------------------------

--
-- Table structure for table `issuance_types`
--

CREATE TABLE `issuance_types` (
  `id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `issuance_types`
--

INSERT INTO `issuance_types` (`id`, `department_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 2, 'Salary Deduct', NULL, '2026-02-19 23:51:49', '2026-02-19 23:51:49');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `description`, `created_at`, `updated_at`, `category_id`) VALUES
(5, 'item 1', NULL, '2026-02-19 22:50:10', '2026-02-19 22:50:10', 2);

-- --------------------------------------------------------

--
-- Table structure for table `item_variants`
--

CREATE TABLE `item_variants` (
  `id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `size_label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `item_variants`
--

INSERT INTO `item_variants` (`id`, `item_id`, `size_label`, `quantity`, `created_at`, `updated_at`) VALUES
(8, 5, 'S', 30, '2026-02-19 22:50:10', '2026-02-19 23:27:07');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_02_12_065455_create_permission_tables', 1),
(5, '2026_02_12_080558_create_items_table', 1),
(6, '2026_02_12_080619_create_item_variants_table', 1),
(7, '2026_02_12_233002_create_categories_table', 2),
(8, '2026_02_13_013503_add_category_id_to_items_table', 2),
(9, '2026_02_13_024357_drop_category_id_from_items_table', 2),
(10, '2026_02_13_033528_create_sites_table', 2),
(11, '2026_02_13_050942_create_issuances_table', 2),
(12, '2026_02_13_051055_create_issuance_items_table', 2),
(13, '2026_02_13_063022_add_size_to_issuance_items_table', 2),
(14, '2026_02_16_054420_add_status_dates_to_issuances_table', 2),
(15, '2026_02_16_062815_create_restocks_table', 2),
(16, '2026_02_16_064146_make_issuance_nullable', 3),
(17, '2026_02_18_013228_add_delivered_quantity_to_restock_items_table', 4),
(18, '2026_02_18_025146_add_status_dates_to_restocks_table', 5),
(19, '2026_02_18_030901_add_note_to_issuances_table', 6),
(20, '2026_02_18_033048_create_issuance_logs_table', 7),
(21, '2026_02_18_034151_create_restock_logs_table', 8),
(22, '2026_02_18_034859_add_remaining_quantity_to_restock_items_table', 9),
(23, '2026_02_18_035922_make_ordered_at_nullable_in_restocks_table', 10),
(24, '2026_02_18_052440_create_scheduled_issuances_table', 11),
(25, '2026_02_20_014614_add_partial_at_to_issuances_table', 12),
(26, '2026_02_20_015014_add_release_columns_to_issuance_items_table', 12),
(27, '2026_02_20_032804_create_departments_table', 13),
(28, '2026_02_20_032831_create_department_user_table', 13),
(29, '2026_02_20_032851_create_department_item_table', 13),
(30, '2026_02_20_033400_add_department_id_to_issuances_table', 14),
(31, '2026_02_20_033441_add_department_id_to_restocks_table', 15),
(32, '2026_02_20_033503_add_department_id_to_categories_table', 15),
(33, '2026_02_20_053130_create_prescriptions_table', 16),
(34, '2026_02_20_053448_add_department_id_to_prescriptions_table', 17),
(35, '2026_02_20_073219_create_issuance_types_table', 18),
(36, '2026_02_20_073527_add_issuance_type_id_to_issuances_table', 19);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(5, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3),
(4, 'App\\Models\\User', 3),
(1, 'App\\Models\\User', 4),
(1, 'App\\Models\\User', 5);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view-any issuance', 'web', '2026-02-18 00:05:32', '2026-02-18 00:53:15'),
(2, 'create issuance', 'web', '2026-02-18 00:54:23', '2026-02-18 00:54:23'),
(3, 'update issuance', 'web', '2026-02-18 00:55:09', '2026-02-18 00:55:09'),
(4, 'delete issuance', 'web', '2026-02-18 00:55:37', '2026-02-18 00:55:37'),
(5, 'release issuance', 'web', '2026-02-18 19:53:22', '2026-02-18 19:53:22'),
(6, 'issue issuance', 'web', '2026-02-18 19:54:49', '2026-02-18 19:54:49'),
(7, 'return issuance', 'web', '2026-02-18 19:56:44', '2026-02-18 19:56:44'),
(8, 'cancel issuance', 'web', '2026-02-18 19:59:39', '2026-02-18 19:59:39'),
(9, 'view-any restock', 'web', '2026-02-18 21:14:07', '2026-02-18 21:14:07'),
(10, 'create restock', 'web', '2026-02-18 21:15:26', '2026-02-18 21:15:26'),
(11, 'update restock', 'web', '2026-02-18 21:16:05', '2026-02-18 21:16:05'),
(13, 'delete restock', 'web', '2026-02-18 21:18:02', '2026-02-18 21:18:02'),
(14, 'deliver restock', 'web', '2026-02-18 21:18:56', '2026-02-18 21:18:56'),
(15, 'return restock', 'web', '2026-02-18 21:19:47', '2026-02-18 21:19:47'),
(16, 'cancel restock', 'web', '2026-02-18 21:20:13', '2026-02-18 21:20:13'),
(17, 'view-any site', 'web', '2026-02-18 21:37:39', '2026-02-18 21:37:39'),
(18, 'create site', 'web', '2026-02-18 21:38:16', '2026-02-18 21:38:16'),
(19, 'update site', 'web', '2026-02-18 21:38:49', '2026-02-18 21:38:49'),
(20, 'delete site', 'web', '2026-02-18 21:39:10', '2026-02-18 21:39:10'),
(21, 'view-any item', 'web', '2026-02-18 21:42:56', '2026-02-18 21:42:56'),
(22, 'create item', 'web', '2026-02-18 21:43:15', '2026-02-18 21:43:15'),
(23, 'update item', 'web', '2026-02-18 21:46:26', '2026-02-18 21:46:26'),
(24, 'delete item', 'web', '2026-02-18 21:46:40', '2026-02-18 21:46:40'),
(25, 'view-any stock', 'web', '2026-02-18 21:49:54', '2026-02-18 21:49:54'),
(26, 'create stock', 'web', '2026-02-18 21:50:14', '2026-02-18 21:50:14'),
(27, 'update stock', 'web', '2026-02-18 21:50:27', '2026-02-18 21:50:27'),
(28, 'delete stock', 'web', '2026-02-18 21:50:41', '2026-02-18 21:50:41'),
(29, 'view-any category', 'web', '2026-02-18 21:56:00', '2026-02-18 21:56:00'),
(30, 'create category', 'web', '2026-02-18 21:56:26', '2026-02-18 21:56:26'),
(31, 'update category', 'web', '2026-02-18 21:56:38', '2026-02-18 21:56:38'),
(32, 'delete category', 'web', '2026-02-18 21:56:48', '2026-02-18 21:56:48'),
(33, 'view-any user', 'web', '2026-02-18 21:59:18', '2026-02-18 21:59:18'),
(34, 'create user', 'web', '2026-02-18 22:01:56', '2026-02-18 22:01:56'),
(35, 'update user', 'web', '2026-02-18 22:06:14', '2026-02-18 22:06:14'),
(36, 'delete user', 'web', '2026-02-18 22:07:13', '2026-02-18 22:07:13'),
(37, 'view-any permission', 'web', '2026-02-18 22:23:08', '2026-02-18 22:23:08'),
(38, 'create permission', 'web', '2026-02-18 22:23:25', '2026-02-18 22:23:25'),
(39, 'update permission', 'web', '2026-02-18 22:23:37', '2026-02-18 22:23:37'),
(40, 'delete permission', 'web', '2026-02-18 22:23:54', '2026-02-18 22:23:54'),
(41, 'view-any role', 'web', '2026-02-18 22:24:08', '2026-02-18 22:24:08'),
(42, 'create role', 'web', '2026-02-18 22:24:28', '2026-02-18 22:24:28'),
(43, 'update role', 'web', '2026-02-18 22:24:42', '2026-02-18 22:24:42'),
(44, 'delete role', 'web', '2026-02-18 22:24:53', '2026-02-18 22:24:53'),
(45, 'view-any issuance-type', 'web', '2026-02-19 23:40:17', '2026-02-19 23:40:17'),
(46, 'create issuance-type', 'web', '2026-02-19 23:40:35', '2026-02-19 23:40:35'),
(47, 'update issuance-type', 'web', '2026-02-19 23:40:47', '2026-02-19 23:40:47'),
(48, 'delete issuance-type', 'web', '2026-02-19 23:41:00', '2026-02-19 23:41:00');

-- --------------------------------------------------------

--
-- Table structure for table `restocks`
--

CREATE TABLE `restocks` (
  `id` bigint UNSIGNED NOT NULL,
  `supplier_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordered_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordered_at` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `delivered_at` date DEFAULT NULL,
  `partial_at` date DEFAULT NULL,
  `returned_at` date DEFAULT NULL,
  `cancelled_at` date DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restocks`
--

INSERT INTO `restocks` (`id`, `supplier_name`, `ordered_by`, `ordered_at`, `status`, `delivered_at`, `partial_at`, `returned_at`, `cancelled_at`, `note`, `created_at`, `updated_at`, `department_id`) VALUES
(19, 'sample', 'sample', '2026-02-20', 'delivered', '2026-02-20', '2026-02-20', NULL, NULL, NULL, '2026-02-19 23:22:40', '2026-02-19 23:24:11', 1);

-- --------------------------------------------------------

--
-- Table structure for table `restock_items`
--

CREATE TABLE `restock_items` (
  `id` bigint UNSIGNED NOT NULL,
  `restock_id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `delivered_quantity` int UNSIGNED DEFAULT NULL,
  `remaining_quantity` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restock_items`
--

INSERT INTO `restock_items` (`id`, `restock_id`, `item_id`, `size`, `quantity`, `delivered_quantity`, `remaining_quantity`, `created_at`, `updated_at`) VALUES
(24, 19, 5, 'S', 50, 50, 0, '2026-02-19 23:22:40', '2026-02-19 23:24:11');

-- --------------------------------------------------------

--
-- Table structure for table `restock_logs`
--

CREATE TABLE `restock_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `restock_id` bigint UNSIGNED NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `performed_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restock_logs`
--

INSERT INTO `restock_logs` (`id`, `restock_id`, `action`, `performed_by`, `note`, `created_at`, `updated_at`) VALUES
(20, 19, 'created', 'developer', NULL, '2026-02-19 23:22:40', '2026-02-19 23:22:40'),
(21, 19, 'partial', 'developer', '[{\"label\":\"item 1 (S)\",\"delivered\":20,\"remaining\":30,\"ordered\":50}]', '2026-02-19 23:23:01', '2026-02-19 23:23:01'),
(22, 19, 'delivered', 'developer', '[{\"label\":\"item 1 (S)\",\"delivered\":30,\"remaining\":0,\"ordered\":50}]', '2026-02-19 23:24:11', '2026-02-19 23:24:11');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2026-02-17 23:58:41', '2026-02-17 23:58:41'),
(3, 'IT developer', 'web', '2026-02-18 22:22:31', '2026-02-18 22:22:31'),
(4, 'super_admin', 'web', '2026-02-19 20:58:29', '2026-02-19 20:58:29'),
(5, 'Management', 'web', '2026-02-19 22:29:42', '2026-02-19 22:29:42');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(45, 1),
(1, 3),
(2, 3),
(3, 3),
(4, 3),
(5, 3),
(6, 3),
(7, 3),
(8, 3),
(9, 3),
(10, 3),
(11, 3),
(13, 3),
(14, 3),
(15, 3),
(16, 3),
(17, 3),
(18, 3),
(19, 3),
(20, 3),
(21, 3),
(22, 3),
(23, 3),
(24, 3),
(25, 3),
(26, 3),
(27, 3),
(28, 3),
(29, 3),
(30, 3),
(31, 3),
(32, 3),
(33, 3),
(34, 3),
(35, 3),
(36, 3),
(37, 3),
(38, 3),
(39, 3),
(40, 3),
(41, 3),
(42, 3),
(43, 3),
(44, 3),
(45, 3),
(46, 3),
(47, 3),
(48, 3),
(1, 5),
(9, 5),
(17, 5),
(21, 5),
(25, 5),
(29, 5),
(33, 5),
(34, 5),
(35, 5),
(36, 5),
(45, 5),
(46, 5),
(47, 5),
(48, 5);

-- --------------------------------------------------------

--
-- Table structure for table `scheduled_issuances`
--

CREATE TABLE `scheduled_issuances` (
  `id` bigint UNSIGNED NOT NULL,
  `site_id` bigint UNSIGNED NOT NULL,
  `issued_to` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheduled_date` date NOT NULL,
  `frequency` enum('once','daily','weekly','monthly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'once',
  `repeat_day_of_week` tinyint UNSIGNED DEFAULT NULL,
  `repeat_day_of_month` tinyint UNSIGNED DEFAULT NULL,
  `repeat_until` date DEFAULT NULL,
  `status` enum('scheduled','processing','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `last_processed_at` date DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scheduled_issuance_items`
--

CREATE TABLE `scheduled_issuance_items` (
  `id` bigint UNSIGNED NOT NULL,
  `scheduled_issuance_id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('Ea43ycpzAw0tbeD45Hvb5p5ti5DtdK5uxBlBFuuD', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiQllBaUs5b1FoNmxqenNXeXdMZjJWV2FaUEJ4SkZkdWVjV2dDSGlwRCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1771569171),
('LklfqAxulF4eD7nLf3FuXqdsRKaOrirgMUqqEoqv', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiTWNwWlQ4eG1aZ0YyRUtGRldWMXZaY3h3UnRBVm54b2NrNG8yeTJ2MCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTE6Imh0dHA6Ly9zbWVfaW52ZW50b3J5LnRlc3QvYWRtaW4vb3BlcmF0aW9uL2lzc3VhbmNlcyI7czo1OiJyb3V0ZSI7czo0MDoiZmlsYW1lbnQuYWRtaW4ucmVzb3VyY2VzLmlzc3VhbmNlcy5pbmRleCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6ImNiM2IxMjU2MzE1OGQ2OGMwYWMwNzUwZDEzYjBiYWQyNTMyN2Q2MTJmYTE2NDI2NmQ4Y2QyOWRlNzRjNzM1M2MiO3M6NjoidGFibGVzIjthOjI6e3M6NDA6ImQ1YzBiYWRiNDE2ZGNiOTQ3OTIyODc2ZWQ5MTczMTk5X2NvbHVtbnMiO2E6NTp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjk6Iml0ZW0ubmFtZSI7czo1OiJsYWJlbCI7czo0OiJJdGVtIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoic2l6ZV9sYWJlbCI7czo1OiJsYWJlbCI7czo0OiJTaXplIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo4OiJxdWFudGl0eSI7czo1OiJsYWJlbCI7czoxMzoiQ3VycmVudCBTdG9jayI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MzoibW9xIjtzOjU6ImxhYmVsIjtzOjM6Ik1PUSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTI6InN0b2NrX3N0YXR1cyI7czo1OiJsYWJlbCI7czo2OiJTdGF0dXMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fXM6NDA6ImMzOTA3MGVlMWNkMTRjNGVkOTA2YWIyODZiNWVlZjUwX2NvbHVtbnMiO2E6Njp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjk6InNpdGUubmFtZSI7czo1OiJsYWJlbCI7czo0OiJTaXRlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo5OiJpc3N1ZWRfdG8iO3M6NToibGFiZWwiO3M6OToiSXNzdWVkIHRvIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJzdGF0dXMiO3M6NToibGFiZWwiO3M6NjoiU3RhdHVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMToic3RhdHVzX2RhdGUiO3M6NToibGFiZWwiO3M6NDoiRGF0ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6ODoiaXRlbV9pZHMiO3M6NToibGFiZWwiO3M6NToiSXRlbXMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjQ6Im5vdGUiO3M6NToibGFiZWwiO3M6NDoiTm90ZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319fX0=', 1771575195),
('O2GwcioRSUwUGagTstZqqS1zmhlC0KrfadKtylSg', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoieW01cHVzbjVWNkVZNVFvUzhjQTNxczBFSVBUN0UzUTlZclF2QmF1dCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ3OiJodHRwOi8vc21lX2ludmVudG9yeS50ZXN0L2FkbWluL29wZXJhdGlvbi91c2VycyI7czo1OiJyb3V0ZSI7czozNjoiZmlsYW1lbnQuYWRtaW4ucmVzb3VyY2VzLnVzZXJzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MztzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiY2IzYjEyNTYzMTU4ZDY4YzBhYzA3NTBkMTNiMGJhZDI1MzI3ZDYxMmZhMTY0MjY2ZDhjZDI5ZGU3NGM3MzUzYyI7czo2OiJ0YWJsZXMiO2E6Mjp7czo0MDoiOGMyZTlkOTlkMmYzYzlmOTg3OTBkMmI2MGVlYWM2YzdfY29sdW1ucyI7YTo0OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTM6InN1cHBsaWVyX25hbWUiO3M6NToibGFiZWwiO3M6ODoiU3VwcGxpZXIiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJvcmRlcmVkX2J5IjtzOjU6ImxhYmVsIjtzOjEwOiJPcmRlcmVkIEJ5IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJzdGF0dXMiO3M6NToibGFiZWwiO3M6NjoiU3RhdHVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoidXBkYXRlZF9hdCI7czo1OiJsYWJlbCI7czoxMjoiTGFzdCBVcGRhdGVkIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX1zOjQwOiJlNjQ0ODMzZjRlNGUwODcxMjMxNWRhNzFiMzNmYWNkMl9jb2x1bW5zIjthOjU6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo0OiJuYW1lIjtzOjU6ImxhYmVsIjtzOjQ6Ik5hbWUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjU6ImVtYWlsIjtzOjU6ImxhYmVsIjtzOjEzOiJFbWFpbCBhZGRyZXNzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMDoicm9sZXMubmFtZSI7czo1OiJsYWJlbCI7czo1OiJSb2xlcyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6ImNyZWF0ZWRfYXQiO3M6NToibGFiZWwiO3M6MTA6IkNyZWF0ZWQgYXQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6InVwZGF0ZWRfYXQiO3M6NToibGFiZWwiO3M6MTA6IlVwZGF0ZWQgYXQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO319fXM6ODoiZmlsYW1lbnQiO2E6MDp7fX0=', 1771568612),
('RUyqmGWqadFTQPHROuj2K6WmvZJ0m2GB7ZFK2Ua0', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiQmxySHdyZUUxZlAxeThxRTRaY3pFM0xZMUVRaG1pd1hBMU9Xd2p5TSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1771570158);

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE `sites` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sites`
--

INSERT INTO `sites` (`id`, `name`, `location`, `created_at`, `updated_at`) VALUES
(2, 'SITE 1', NULL, '2026-02-19 23:25:42', '2026-02-19 23:25:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(2, 'Manager', 'manager@example.com', NULL, '$2y$12$6z0FF4CYM08BB67riB5sd.Fm6pbwzhv.ERkd3oZOY/3JUXwkrd2hW', NULL, '2026-02-18 00:57:02', '2026-02-18 00:57:02'),
(3, 'developer', 'developer@developer.com', NULL, '$2y$12$vPsr3TGJKHrHlK1LDCe6MOxnRoldEVntkyGBWXDdrm1kSmDndxDQO', NULL, '2026-02-18 22:41:58', '2026-02-18 22:41:58'),
(4, 'Angelo', 'angelo.stronglink@gmail.com', NULL, '$2y$12$G.51qnELORLFHsDgm0/ZWuOI2RteOiBKxoFaAYJI1x4Zy9NBqF9q2', NULL, '2026-02-19 22:11:39', '2026-02-19 22:11:39'),
(5, 'Rhon', 'rhon.stronglink@gmail.com', NULL, '$2y$12$jcxdZYEOHK/msR.F.SDZhetIp3FRyg.lSO22gCizIaGM.4fQUjmpS', NULL, '2026-02-19 22:16:53', '2026-02-19 22:16:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_department_id_foreign` (`department_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_slug_unique` (`slug`);

--
-- Indexes for table `department_item`
--
ALTER TABLE `department_item`
  ADD PRIMARY KEY (`department_id`,`item_id`),
  ADD KEY `department_item_item_id_foreign` (`item_id`);

--
-- Indexes for table `department_user`
--
ALTER TABLE `department_user`
  ADD PRIMARY KEY (`department_id`,`user_id`),
  ADD KEY `department_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `issuances`
--
ALTER TABLE `issuances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issuances_site_id_foreign` (`site_id`),
  ADD KEY `issuances_department_id_foreign` (`department_id`),
  ADD KEY `issuances_issuance_type_id_foreign` (`issuance_type_id`);

--
-- Indexes for table `issuance_items`
--
ALTER TABLE `issuance_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issuance_items_issuance_id_foreign` (`issuance_id`),
  ADD KEY `issuance_items_item_id_foreign` (`item_id`);

--
-- Indexes for table `issuance_logs`
--
ALTER TABLE `issuance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issuance_logs_issuance_id_foreign` (`issuance_id`);

--
-- Indexes for table `issuance_types`
--
ALTER TABLE `issuance_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issuance_types_department_id_foreign` (`department_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `items_category_id_foreign` (`category_id`);

--
-- Indexes for table `item_variants`
--
ALTER TABLE `item_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_variants_item_id_foreign` (`item_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `restocks`
--
ALTER TABLE `restocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restocks_department_id_foreign` (`department_id`);

--
-- Indexes for table `restock_items`
--
ALTER TABLE `restock_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restock_items_restock_id_foreign` (`restock_id`),
  ADD KEY `restock_items_item_id_foreign` (`item_id`);

--
-- Indexes for table `restock_logs`
--
ALTER TABLE `restock_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restock_logs_restock_id_foreign` (`restock_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `scheduled_issuances`
--
ALTER TABLE `scheduled_issuances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `scheduled_issuances_site_id_foreign` (`site_id`);

--
-- Indexes for table `scheduled_issuance_items`
--
ALTER TABLE `scheduled_issuance_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `scheduled_issuance_items_scheduled_issuance_id_foreign` (`scheduled_issuance_id`),
  ADD KEY `scheduled_issuance_items_item_id_foreign` (`item_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `sites`
--
ALTER TABLE `sites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sites_name_unique` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issuances`
--
ALTER TABLE `issuances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `issuance_items`
--
ALTER TABLE `issuance_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `issuance_logs`
--
ALTER TABLE `issuance_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `issuance_types`
--
ALTER TABLE `issuance_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `item_variants`
--
ALTER TABLE `item_variants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `restocks`
--
ALTER TABLE `restocks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `restock_items`
--
ALTER TABLE `restock_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `restock_logs`
--
ALTER TABLE `restock_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `scheduled_issuances`
--
ALTER TABLE `scheduled_issuances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `scheduled_issuance_items`
--
ALTER TABLE `scheduled_issuance_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sites`
--
ALTER TABLE `sites`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `department_item`
--
ALTER TABLE `department_item`
  ADD CONSTRAINT `department_item_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `department_item_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `department_user`
--
ALTER TABLE `department_user`
  ADD CONSTRAINT `department_user_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `department_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `issuances`
--
ALTER TABLE `issuances`
  ADD CONSTRAINT `issuances_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `issuances_issuance_type_id_foreign` FOREIGN KEY (`issuance_type_id`) REFERENCES `issuance_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `issuances_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `issuance_items`
--
ALTER TABLE `issuance_items`
  ADD CONSTRAINT `issuance_items_issuance_id_foreign` FOREIGN KEY (`issuance_id`) REFERENCES `issuances` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `issuance_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `issuance_logs`
--
ALTER TABLE `issuance_logs`
  ADD CONSTRAINT `issuance_logs_issuance_id_foreign` FOREIGN KEY (`issuance_id`) REFERENCES `issuances` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `issuance_types`
--
ALTER TABLE `issuance_types`
  ADD CONSTRAINT `issuance_types_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `item_variants`
--
ALTER TABLE `item_variants`
  ADD CONSTRAINT `item_variants_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `restocks`
--
ALTER TABLE `restocks`
  ADD CONSTRAINT `restocks_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `restock_items`
--
ALTER TABLE `restock_items`
  ADD CONSTRAINT `restock_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `restock_items_restock_id_foreign` FOREIGN KEY (`restock_id`) REFERENCES `restocks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `restock_logs`
--
ALTER TABLE `restock_logs`
  ADD CONSTRAINT `restock_logs_restock_id_foreign` FOREIGN KEY (`restock_id`) REFERENCES `restocks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scheduled_issuances`
--
ALTER TABLE `scheduled_issuances`
  ADD CONSTRAINT `scheduled_issuances_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scheduled_issuance_items`
--
ALTER TABLE `scheduled_issuance_items`
  ADD CONSTRAINT `scheduled_issuance_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scheduled_issuance_items_scheduled_issuance_id_foreign` FOREIGN KEY (`scheduled_issuance_id`) REFERENCES `scheduled_issuances` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
