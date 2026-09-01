-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 17, 2026 at 07:31 AM
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
-- Database: `db_uteparts`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
CREATE TABLE IF NOT EXISTS `brands` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_name_unique` (`name`),
  UNIQUE KEY `brands_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Xiaomi', 'xiaomi', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(2, 'Samsung', 'samsung', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(3, 'Oppo', 'oppo', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(4, 'Vivo', 'vivo', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(5, 'iPhone', 'iphone', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-avatar_0aee3d7f2a7cb7684a578150e2910438', 's:3022:\"data:image/png;base64,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\";', 1776497047),
('laravel-cache-avatar_6f0dcabccdf9866d9b5b6f091d072f91', 's:3022:\"data:image/png;base64,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\";', 1776497047);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_created_by_foreign` (`created_by`),
  KEY `categories_updated_by_foreign` (`updated_by`),
  KEY `categories_deleted_by_foreign` (`deleted_by`),
  KEY `categories_is_active_sort_order_index` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `sort_order`, `is_active`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'LCD & Touchscreen', 'lcd-touchscreen', 'Layar LCD, AMOLED, dan touchscreen untuk berbagai tipe HP.', 1, 1, NULL, NULL, NULL, '2026-04-14 07:36:31', '2026-04-14 07:36:31', NULL),
(2, 'Baterai', 'baterai', 'Baterai tanam dan baterai original-compatible untuk smartphone.', 2, 1, NULL, NULL, NULL, '2026-04-14 07:36:31', '2026-04-14 07:36:31', NULL),
(3, 'Backdoor & Casing', 'backdoor-casing', 'Backdoor, housing, frame, dan casing pengganti HP.', 3, 1, NULL, NULL, NULL, '2026-04-14 07:36:31', '2026-04-14 07:36:31', NULL),
(4, 'Flexibel & Tombol', 'flexibel-tombol', 'Flexibel power, volume, fingerprint, dan sparepart tombol lainnya.', 4, 1, NULL, NULL, NULL, '2026-04-14 07:36:31', '2026-04-14 07:36:31', NULL),
(5, 'Kamera', 'kamera', 'Kamera depan, belakang, dan modul kamera smartphone.', 5, 1, NULL, NULL, NULL, '2026-04-14 07:36:31', '2026-04-14 07:36:31', NULL),
(6, 'Konektor Charging', 'konektor-charging', 'Board charger, port charging, dan konektor pendukung.', 6, 1, NULL, NULL, NULL, '2026-04-14 07:36:31', '2026-04-14 07:36:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customer_groups`
--

DROP TABLE IF EXISTS `customer_groups`;
CREATE TABLE IF NOT EXISTS `customer_groups` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_groups_name_unique` (`name`),
  UNIQUE KEY `customer_groups_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_groups`
--

INSERT INTO `customer_groups` (`id`, `name`, `slug`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Group 1', 'group-1', 1, 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(2, 'Group 2', 'group-2', 2, 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(3, 'Group 3', 'group-3', 3, 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(4, 'Group 4', 'group-4', 4, 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
CREATE TABLE IF NOT EXISTS `locations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locations_name_unique` (`name`),
  UNIQUE KEY `locations_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`, `code`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Gudang', 'GDG', 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(2, 'Etalase', 'ETL', 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(3, 'Toko', 'TOKO', 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_01_01_000000_add_ldap_columns_to_users_table', 1),
(5, '2025_08_18_022702_add_audit_trail_to_users_table', 1),
(6, '2025_08_18_023829_create_user_logs_table', 1),
(7, '2025_08_18_025233_create_roles_table', 1),
(8, '2025_08_18_025240_create_permissions_table', 1),
(9, '2025_08_18_025245_create_role_permission_table', 1),
(10, '2025_08_18_025251_create_user_role_table', 1),
(11, '2025_09_19_022847_create_personal_access_tokens_table', 1),
(12, '2025_09_25_142328_add_parent_and_sort_order_to_permissions_table', 1),
(13, '2026_04_13_000001_create_categories_table', 1),
(14, '2026_04_13_000002_create_products_table', 1),
(15, '2026_04_13_000003_add_image_path_to_products_table', 1),
(16, '2026_04_14_000001_create_product_supporting_tables', 2),
(17, '2026_04_14_000002_expand_products_table_for_inventory_master', 2),
(18, '2026_04_14_000003_create_product_stocks_table', 3),
(19, '2026_04_15_000001_create_stock_movements_table', 3),
(20, '2026_04_15_000002_create_stock_transfers_table', 3),
(21, '2026_04_15_000003_create_suppliers_table', 4),
(22, '2026_04_15_000004_create_product_suppliers_table', 4),
(23, '2026_04_16_000001_create_units_table', 5),
(24, '2026_04_16_000002_create_purchase_orders_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `module` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `parent` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`),
  KEY `permissions_created_by_foreign` (`created_by`),
  KEY `permissions_updated_by_foreign` (`updated_by`),
  KEY `permissions_name_module_is_active_index` (`name`,`module`,`is_active`),
  KEY `permissions_parent_foreign` (`parent`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `display_name`, `description`, `module`, `parent`, `sort_order`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'management.access', 'Management', 'Can access Management section', 'management', NULL, 2, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(2, 'management.users.view', 'Users - View', 'Can view users', 'management', 'management.access', 1, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(3, 'management.users.create', 'Users - Create', 'Can create users', 'management', 'management.access', 2, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(4, 'management.users.edit', 'Users - Edit', 'Can edit users', 'management', 'management.access', 3, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(5, 'management.users.delete', 'Users - Delete', 'Can delete users', 'management', 'management.access', 4, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(6, 'management.users.restore', 'Users - Restore', 'Can restore deleted users', 'management', 'management.access', 5, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(7, 'management.users.force_delete', 'Users - Force Delete', 'Can permanently delete users', 'management', 'management.access', 6, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(8, 'management.users.logs', 'Users - View Logs', 'Can view user activity logs', 'management', 'management.access', 7, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(9, 'management.users.permissions', 'Users - Manage Permissions', 'Can manage user permissions directly', 'management', 'management.access', 8, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(10, 'management.roles.view', 'Role Templates - View', 'Can view role templates', 'management', 'management.access', 9, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(11, 'management.roles.create', 'Role Templates - Create', 'Can create role templates', 'management', 'management.access', 10, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(12, 'management.roles.edit', 'Role Templates - Edit', 'Can edit role templates', 'management', 'management.access', 11, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(13, 'management.roles.delete', 'Role Templates - Delete', 'Can delete role templates', 'management', 'management.access', 12, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(14, 'images.access', 'Images', 'Can access Images section', 'images', NULL, 3, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(15, 'images.view', 'Images - View', 'Can view images', 'images', 'images.access', 1, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(16, 'images.create', 'Images - Upload', 'Can upload images', 'images', 'images.access', 2, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(17, 'images.edit', 'Images - Edit', 'Can edit images', 'images', 'images.access', 3, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(18, 'images.delete', 'Images - Delete', 'Can delete images', 'images', 'images.access', 4, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(19, 'images.restore', 'Images - Restore', 'Can restore deleted images', 'images', 'images.access', 5, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(20, 'images.force_delete', 'Images - Force Delete', 'Can permanently delete images', 'images', 'images.access', 6, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(21, 'master.access', 'Master Data', 'Can access Master Data section', 'master', NULL, 4, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(22, 'master.categories.view', 'Categories - View', 'Can view categories', 'master', 'master.access', 1, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(23, 'master.categories.create', 'Categories - Create', 'Can create categories', 'master', 'master.access', 2, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(24, 'master.categories.edit', 'Categories - Edit', 'Can edit categories', 'master', 'master.access', 3, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(25, 'master.categories.delete', 'Categories - Delete', 'Can delete categories', 'master', 'master.access', 4, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(26, 'master.categories.restore', 'Categories - Restore', 'Can restore deleted categories', 'master', 'master.access', 5, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(27, 'master.categories.force_delete', 'Categories - Force Delete', 'Can permanently delete categories', 'master', 'master.access', 6, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(28, 'master.products.view', 'Products - View', 'Can view products', 'master', 'master.access', 7, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(29, 'master.products.create', 'Products - Create', 'Can create products', 'master', 'master.access', 8, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(30, 'master.products.edit', 'Products - Edit', 'Can edit products', 'master', 'master.access', 9, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(31, 'master.products.delete', 'Products - Delete', 'Can delete products', 'master', 'master.access', 10, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(32, 'master.products.restore', 'Products - Restore', 'Can restore deleted products', 'master', 'master.access', 11, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(33, 'master.products.force_delete', 'Products - Force Delete', 'Can permanently delete products', 'master', 'master.access', 12, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(34, 'master.sub_categories.view', 'Sub Categories - View', 'Can view sub categories', 'master', 'master.access', 13, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(35, 'master.sub_categories.create', 'Sub Categories - Create', 'Can create sub categories', 'master', 'master.access', 14, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(36, 'master.sub_categories.edit', 'Sub Categories - Edit', 'Can edit sub categories', 'master', 'master.access', 15, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(37, 'master.sub_categories.delete', 'Sub Categories - Delete', 'Can delete sub categories', 'master', 'master.access', 16, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(38, 'master.brands.view', 'Brands - View', 'Can view brands', 'master', 'master.access', 17, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(39, 'master.brands.create', 'Brands - Create', 'Can create brands', 'master', 'master.access', 18, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(40, 'master.brands.edit', 'Brands - Edit', 'Can edit brands', 'master', 'master.access', 19, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(41, 'master.brands.delete', 'Brands - Delete', 'Can delete brands', 'master', 'master.access', 20, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(42, 'master.product_types.view', 'Product Types - View', 'Can view product types', 'master', 'master.access', 21, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(43, 'master.product_types.create', 'Product Types - Create', 'Can create product types', 'master', 'master.access', 22, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(44, 'master.product_types.edit', 'Product Types - Edit', 'Can edit product types', 'master', 'master.access', 23, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(45, 'master.product_types.delete', 'Product Types - Delete', 'Can delete product types', 'master', 'master.access', 24, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(46, 'master.locations.view', 'Locations - View', 'Can view locations', 'master', 'master.access', 25, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(47, 'master.locations.create', 'Locations - Create', 'Can create locations', 'master', 'master.access', 26, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(48, 'master.locations.edit', 'Locations - Edit', 'Can edit locations', 'master', 'master.access', 27, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(49, 'master.locations.delete', 'Locations - Delete', 'Can delete locations', 'master', 'master.access', 28, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(50, 'master.customer_groups.view', 'Customer Groups - View', 'Can view customer groups', 'master', 'master.access', 29, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(51, 'master.customer_groups.create', 'Customer Groups - Create', 'Can create customer groups', 'master', 'master.access', 30, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(52, 'master.customer_groups.edit', 'Customer Groups - Edit', 'Can edit customer groups', 'master', 'master.access', 31, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(53, 'master.customer_groups.delete', 'Customer Groups - Delete', 'Can delete customer groups', 'master', 'master.access', 32, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(54, 'master.product_stocks.view', 'Product Stocks - View', 'Can view stock by product location', 'master', 'master.access', 33, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(55, 'master.product_stocks.edit', 'Product Stocks - Edit', 'Can update stock by product location', 'master', 'master.access', 34, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(56, 'master.employees.view', 'Employees - View', 'Can view employees', 'master', 'master.access', 35, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(57, 'master.employees.sync', 'Employees - Sync', 'Can sync employees from JPayroll', 'master', 'master.access', 36, 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `sub_category_id` bigint UNSIGNED DEFAULT NULL,
  `brand_id` bigint UNSIGNED DEFAULT NULL,
  `default_location_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legacy_image_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `has_serial_number` tinyint(1) NOT NULL DEFAULT '0',
  `buy_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sale_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_conversion_qty` decimal(15,2) NOT NULL DEFAULT '1.00',
  `stock_global` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stock_min` decimal(15,2) DEFAULT NULL,
  `stock_max` decimal(15,2) DEFAULT NULL,
  `damaged_stock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount_value` decimal(15,2) DEFAULT NULL,
  `member_point` decimal(15,2) DEFAULT NULL,
  `staff_point` decimal(15,2) DEFAULT NULL,
  `sales_commission` decimal(15,2) DEFAULT NULL,
  `last_purchase_date` date DEFAULT NULL,
  `rack_location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_notes` text COLLATE utf8mb4_unicode_ci,
  `is_open_price` tinyint(1) NOT NULL DEFAULT '0',
  `allow_discount_override` tinyint(1) NOT NULL DEFAULT '0',
  `sync_sell_price_to_branch` tinyint(1) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `purchase_price` decimal(15,2) DEFAULT NULL,
  `selling_price` decimal(15,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pcs',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_created_by_foreign` (`created_by`),
  KEY `products_updated_by_foreign` (`updated_by`),
  KEY `products_deleted_by_foreign` (`deleted_by`),
  KEY `products_category_id_is_active_index` (`category_id`,`is_active`),
  KEY `products_name_sku_index` (`name`,`sku`),
  KEY `products_sub_category_id_foreign` (`sub_category_id`),
  KEY `products_brand_id_foreign` (`brand_id`),
  KEY `products_default_location_id_foreign` (`default_location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_code`, `category_id`, `sub_category_id`, `brand_id`, `default_location_id`, `name`, `slug`, `sku`, `barcode`, `legacy_image_path`, `expired_date`, `has_serial_number`, `buy_unit`, `sale_unit`, `default_conversion_qty`, `stock_global`, `stock_min`, `stock_max`, `damaged_stock`, `discount_value`, `member_point`, `staff_point`, `sales_commission`, `last_purchase_date`, `rack_location`, `additional_notes`, `is_open_price`, `allow_discount_override`, `sync_sell_price_to_branch`, `description`, `purchase_price`, `selling_price`, `unit`, `is_published`, `is_active`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'SP-LCD-RN10', 1, 1, 1, 3, 'LCD Xiaomi Redmi Note 10', 'lcd-xiaomi-redmi-note-10', 'LCD-RN10-001', '8998801001001', NULL, NULL, 0, 'pcs', 'pcs', 1.00, 24.00, NULL, NULL, 1.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 'LCD set touchscreen untuk Xiaomi Redmi Note 10.', 240000.00, 285000.00, 'pcs', 0, 1, NULL, NULL, NULL, '2026-04-14 07:36:31', '2026-04-15 02:38:52', NULL),
(2, 'SP-BAT-A3S', 2, 4, 3, 3, 'Baterai Oppo A3S', 'baterai-oppo-a3s', 'BAT-A3S-001', '8998801001002', NULL, NULL, 0, 'pcs', 'pcs', 1.00, 45.00, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 'Baterai replacement untuk Oppo A3S dengan konektor standar.', 70000.00, 95000.00, 'pcs', 0, 1, NULL, NULL, NULL, '2026-04-14 07:36:31', '2026-04-15 02:38:52', NULL),
(3, 'SP-BD-A12', 3, 7, 2, 2, 'Backdoor Samsung A12 Hitam', 'backdoor-samsung-a12-hitam', 'BD-A12-BLK', '8998801001003', NULL, NULL, 0, 'pcs', 'pcs', 1.00, 18.00, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 'Backdoor Samsung A12 warna hitam.', 60000.00, 85000.00, 'pcs', 0, 1, NULL, NULL, NULL, '2026-04-14 07:36:31', '2026-04-15 02:38:52', NULL),
(4, 'SP-FLX-R9A', 4, 10, 1, 3, 'Flexibel Power Xiaomi Redmi 9A', 'flexibel-power-xiaomi-redmi-9a', 'FLX-R9A-PWR', '8998801001004', NULL, NULL, 0, 'pcs', 'pcs', 1.00, 57.00, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 'Flexibel tombol power untuk Xiaomi Redmi 9A.', 22000.00, 35000.00, 'pcs', 0, 1, NULL, NULL, NULL, '2026-04-14 07:36:31', '2026-04-15 02:38:52', NULL),
(5, 'SP-CAM-IPXR', 5, 14, 5, 3, 'Kamera Belakang iPhone XR', 'kamera-belakang-iphone-xr', 'CAM-IPXR-001', '8998801001005', NULL, NULL, 0, 'pcs', 'pcs', 1.00, 10.00, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 'Modul kamera belakang untuk iPhone XR.', 180000.00, 225000.00, 'pcs', 0, 1, NULL, NULL, NULL, '2026-04-14 07:36:31', '2026-04-15 02:38:52', NULL),
(6, 'SP-CHG-VY12', 6, 16, 4, 3, 'Board Charger Vivo Y12', 'board-charger-vivo-y12', 'CHG-VY12-001', '8998801001006', NULL, NULL, 0, 'pcs', 'pcs', 1.00, 37.00, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 'Sub-board charger untuk Vivo Y12.', 45000.00, 65000.00, 'pcs', 0, 1, NULL, NULL, NULL, '2026-04-14 07:36:31', '2026-04-15 02:38:52', NULL),
(7, 'SP-LCD-IP11-OLED', 1, 1, 5, 3, 'LCD iPhone 11 OLED Premium', 'lcd-iphone-11-oled-premium', 'LCD-IP11-OLED-001', '8998802001001', NULL, NULL, 1, 'PCS', 'PCS', 1.00, 11.00, 5.00, 40.00, 1.00, 0.00, 10.00, 5.00, 7500.00, '2026-04-10', 'Rak LCD A1', 'Barang fragile, wajib cek fleksibel dan dead pixel sebelum dijual.', 0, 1, 1, 'LCD set OLED premium untuk iPhone 11, sudah include touchscreen dan frame.', 420000.00, 535000.00, 'PCS', 0, 1, NULL, NULL, NULL, '2026-04-17 03:07:25', '2026-04-17 03:07:26', NULL),
(8, 'SP-BAT-SAM-A12-DP', 2, 5, 2, 1, 'Baterai Samsung A12/A13 Double Power', 'baterai-samsung-a12a13-double-power', 'BAT-SAM-A12-DP-001', '8998802002001', NULL, '2027-04-17', 0, 'PCS', 'PCS', 1.00, 95.00, 15.00, 120.00, 0.00, 5000.00, 3.00, 2.00, 2500.00, '2026-04-14', 'Rak Baterai B2', 'Simpan di suhu ruang, hindari panas langsung.', 0, 1, 1, 'Baterai double power kompatibel Samsung A12 dan A13.', 78000.00, 125000.00, 'PCS', 0, 1, NULL, NULL, NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26', NULL),
(9, 'PRD-260417142536-821', 2, 4, 1, 1, 'Tes Baterai 1', 'tes-baterai-1', 'PRD-260417142536-821', '1230120312', NULL, NULL, 0, 'PCS', 'PCS', 1.00, 2.00, 3.00, NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, 300000.00, 400000.00, 'PCS', 0, 1, 1, 1, NULL, '2026-04-17 07:25:36', '2026-04-17 07:27:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_barcodes`
--

DROP TABLE IF EXISTS `product_barcodes`;
CREATE TABLE IF NOT EXISTS `product_barcodes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `barcode` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_level` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `label` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_barcodes_product_id_foreign` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_barcodes`
--

INSERT INTO `product_barcodes` (`id`, `product_id`, `barcode`, `unit_level`, `label`, `is_primary`, `created_at`, `updated_at`) VALUES
(1, 1, '8998801001001', 1, 'Barcode Utama', 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(2, 2, '8998801001002', 1, 'Barcode Utama', 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(3, 3, '8998801001003', 1, 'Barcode Utama', 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(4, 4, '8998801001004', 1, 'Barcode Utama', 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(5, 5, '8998801001005', 1, 'Barcode Utama', 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(6, 6, '8998801001006', 1, 'Barcode Utama', 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(7, 7, '8998802001001', 1, 'Barcode Utama', 1, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(8, 8, '8998802002001', 1, 'Barcode Utama', 1, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(11, 9, '1230120312', 1, 'Barcode Utama', 1, '2026-04-17 07:27:03', '2026-04-17 07:27:03');

-- --------------------------------------------------------

--
-- Table structure for table `product_customer_group_prices`
--

DROP TABLE IF EXISTS `product_customer_group_prices`;
CREATE TABLE IF NOT EXISTS `product_customer_group_prices` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `customer_group_id` bigint UNSIGNED NOT NULL,
  `channel` enum('toko','partai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'toko',
  `price` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_customer_group_prices_product_id_foreign` (`product_id`),
  KEY `product_customer_group_prices_customer_group_id_foreign` (`customer_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_customer_group_prices`
--

INSERT INTO `product_customer_group_prices` (`id`, `product_id`, `customer_group_id`, `channel`, `price`, `created_at`, `updated_at`) VALUES
(1, 7, 1, 'toko', 535000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(2, 7, 1, 'partai', 510000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(3, 7, 2, 'toko', 520000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(4, 7, 2, 'partai', 500000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(5, 7, 3, 'toko', 510000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(6, 7, 3, 'partai', 495000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(7, 7, 4, 'toko', 500000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(8, 7, 4, 'partai', 485000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(9, 8, 1, 'toko', 125000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(10, 8, 1, 'partai', 115000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(11, 8, 2, 'toko', 120000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(12, 8, 2, 'partai', 112000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(13, 8, 3, 'toko', 118000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(14, 8, 3, 'partai', 110000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(15, 8, 4, 'toko', 115000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(16, 8, 4, 'partai', 108000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `image_path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_foreign` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_price_tiers`
--

DROP TABLE IF EXISTS `product_price_tiers`;
CREATE TABLE IF NOT EXISTS `product_price_tiers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `unit_level` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `min_qty` int UNSIGNED NOT NULL DEFAULT '1',
  `price` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_price_tiers_product_id_foreign` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_price_tiers`
--

INSERT INTO `product_price_tiers` (`id`, `product_id`, `unit_level`, `min_qty`, `price`, `created_at`, `updated_at`) VALUES
(1, 7, 1, 3, 520000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(2, 7, 1, 10, 500000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(3, 8, 1, 5, 118000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(4, 8, 1, 20, 110000.00, '2026-04-17 03:07:26', '2026-04-17 03:07:26');

-- --------------------------------------------------------

--
-- Table structure for table `product_product_type`
--

DROP TABLE IF EXISTS `product_product_type`;
CREATE TABLE IF NOT EXISTS `product_product_type` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `product_type_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_product_type_product_id_product_type_id_unique` (`product_id`,`product_type_id`),
  KEY `product_product_type_product_type_id_foreign` (`product_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_product_type`
--

INSERT INTO `product_product_type` (`id`, `product_id`, `product_type_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL),
(2, 2, 7, NULL, NULL),
(3, 3, 4, NULL, NULL),
(4, 4, 2, NULL, NULL),
(5, 5, 13, NULL, NULL),
(6, 6, 10, NULL, NULL),
(7, 7, 14, NULL, NULL),
(8, 8, 4, NULL, NULL),
(9, 8, 5, NULL, NULL),
(10, 9, 4, NULL, NULL),
(11, 9, 5, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_stocks`
--

DROP TABLE IF EXISTS `product_stocks`;
CREATE TABLE IF NOT EXISTS `product_stocks` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `location_id` bigint UNSIGNED NOT NULL,
  `quantity` decimal(14,2) NOT NULL DEFAULT '0.00',
  `damaged_quantity` decimal(14,2) NOT NULL DEFAULT '0.00',
  `stock_min` decimal(14,2) DEFAULT NULL,
  `stock_max` decimal(14,2) DEFAULT NULL,
  `notes` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_stocks_product_id_location_id_unique` (`product_id`,`location_id`),
  KEY `product_stocks_location_id_foreign` (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_stocks`
--

INSERT INTO `product_stocks` (`id`, `product_id`, `location_id`, `quantity`, `damaged_quantity`, `stock_min`, `stock_max`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 17.00, 0.00, NULL, NULL, NULL, '2026-04-15 02:38:51', '2026-04-15 02:38:52'),
(2, 1, 3, 7.00, 1.00, NULL, NULL, NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(3, 2, 1, 33.00, 0.00, NULL, NULL, NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(4, 2, 3, 12.00, 0.00, NULL, NULL, NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(5, 3, 1, 14.00, 0.00, NULL, NULL, NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(6, 3, 2, 4.00, 0.00, NULL, NULL, NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(7, 4, 1, 40.00, 0.00, NULL, NULL, NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(8, 4, 3, 17.00, 0.00, NULL, NULL, NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(9, 5, 1, 8.00, 0.00, NULL, NULL, NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(10, 5, 3, 2.00, 0.00, NULL, NULL, NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(11, 6, 1, 25.00, 0.00, NULL, NULL, NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(12, 6, 3, 12.00, 0.00, NULL, NULL, NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(13, 7, 1, 5.00, 0.00, NULL, NULL, NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(14, 7, 3, 4.00, 1.00, NULL, NULL, NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(15, 7, 2, 2.00, 0.00, NULL, NULL, NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(16, 8, 1, 60.00, 0.00, NULL, NULL, NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(17, 8, 3, 25.00, 0.00, NULL, NULL, NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(18, 8, 2, 10.00, 0.00, NULL, NULL, NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26');

-- --------------------------------------------------------

--
-- Table structure for table `product_suppliers`
--

DROP TABLE IF EXISTS `product_suppliers`;
CREATE TABLE IF NOT EXISTS `product_suppliers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `supplier_id` bigint UNSIGNED NOT NULL,
  `supplier_product_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_purchase_price` decimal(15,2) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `notes` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_suppliers_product_id_supplier_id_unique` (`product_id`,`supplier_id`),
  KEY `product_suppliers_supplier_id_foreign` (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_suppliers`
--

INSERT INTO `product_suppliers` (`id`, `product_id`, `supplier_id`, `supplier_product_code`, `last_purchase_price`, `is_primary`, `notes`, `created_at`, `updated_at`) VALUES
(1, 7, 2, 'IP11-OLED-PREM', 420000.00, 1, 'Supplier utama LCD premium', '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(2, 7, 5, 'IPH11-LCD-OLED', 430000.00, 0, 'Supplier cadangan', '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(3, 8, 3, 'BAT-A12-DP', 78000.00, 1, 'Supplier baterai utama', '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(4, 8, 4, 'SAMA12-BAT-DP', 80000.00, 0, 'Backup fast moving', '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(7, 9, 4, NULL, NULL, 1, NULL, '2026-04-17 07:27:03', '2026-04-17 07:27:03');

-- --------------------------------------------------------

--
-- Table structure for table `product_types`
--

DROP TABLE IF EXISTS `product_types`;
CREATE TABLE IF NOT EXISTS `product_types` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `brand_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_types_slug_unique` (`slug`),
  KEY `product_types_brand_id_foreign` (`brand_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_types`
--

INSERT INTO `product_types` (`id`, `brand_id`, `name`, `slug`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Redmi Note 10', 'xiaomi-redmi-note-10', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(2, 1, 'Redmi 9A', 'xiaomi-redmi-9a', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(3, 1, 'Redmi Note 11', 'xiaomi-redmi-note-11', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(4, 2, 'A12', 'samsung-a12', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(5, 2, 'A13', 'samsung-a13', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(6, 2, 'A14', 'samsung-a14', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(7, 3, 'A3S', 'oppo-a3s', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(8, 3, 'A5 2020', 'oppo-a5-2020', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(9, 3, 'A16', 'oppo-a16', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(10, 4, 'Y12', 'vivo-y12', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(11, 4, 'Y15', 'vivo-y15', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(12, 4, 'Y20', 'vivo-y20', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(13, 5, 'iPhone XR', 'iphone-iphone-xr', 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(14, 5, 'iPhone 11', 'iphone-iphone-11', 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(15, 5, 'iPhone 12', 'iphone-iphone-12', 1, '2026-04-15 02:38:51', '2026-04-15 02:38:51');

-- --------------------------------------------------------

--
-- Table structure for table `product_units`
--

DROP TABLE IF EXISTS `product_units`;
CREATE TABLE IF NOT EXISTS `product_units` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `level` tinyint UNSIGNED NOT NULL,
  `unit_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversion_qty` decimal(15,2) NOT NULL DEFAULT '1.00',
  `price_toko` decimal(15,2) DEFAULT NULL,
  `margin_toko` decimal(8,2) DEFAULT NULL,
  `price_partai` decimal(15,2) DEFAULT NULL,
  `margin_partai` decimal(8,2) DEFAULT NULL,
  `price_cabang` decimal(15,2) DEFAULT NULL,
  `margin_cabang` decimal(8,2) DEFAULT NULL,
  `price_lain` decimal(15,2) DEFAULT NULL,
  `margin_lain` decimal(8,2) DEFAULT NULL,
  `barcode` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode_label` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_units_product_id_foreign` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_units`
--

INSERT INTO `product_units` (`id`, `product_id`, `level`, `unit_name`, `conversion_qty`, `price_toko`, `margin_toko`, `price_partai`, `margin_partai`, `price_cabang`, `margin_cabang`, `price_lain`, `margin_lain`, `barcode`, `barcode_label`, `created_at`, `updated_at`) VALUES
(1, 7, 1, 'PCS', 1.00, 535000.00, 115000.00, 510000.00, 90000.00, 520000.00, 100000.00, 500000.00, 80000.00, '8998802001001', 'LCD IP11 PCS', '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(2, 7, 2, 'SET', 5.00, 2600000.00, 500000.00, 2500000.00, 400000.00, 2550000.00, 450000.00, 2450000.00, 350000.00, '8998802001002', 'LCD IP11 SET 5', '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(3, 8, 1, 'PCS', 1.00, 125000.00, 47000.00, 115000.00, 37000.00, 118000.00, 40000.00, 110000.00, 32000.00, '8998802002001', 'BAT SAM A12 PCS', '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(4, 8, 2, 'PACK', 10.00, 1180000.00, 400000.00, 1100000.00, 320000.00, 1130000.00, 350000.00, 1080000.00, 300000.00, '8998802002002', 'BAT SAM A12 PACK 10', '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(7, 9, 1, 'PCS', 1.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-17 07:27:03', '2026-04-17 07:27:03');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `size` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `price` decimal(15,2) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_variants_product_id_foreign` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `size`, `color`, `stock`, `price`, `notes`, `created_at`, `updated_at`) VALUES
(1, 7, 'Original Size', 'Black', 0.00, 535000.00, 'Grade premium', '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(2, 8, 'A12', 'Black', 0.00, 125000.00, 'Kompatibel A12', '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(3, 8, 'A13', 'Black', 0.00, 125000.00, 'Kompatibel A13', '2026-04-17 03:07:26', '2026-04-17 03:07:26');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `po_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `supplier_id` bigint UNSIGNED NOT NULL,
  `location_id` bigint UNSIGNED NOT NULL,
  `quantity` decimal(14,2) NOT NULL,
  `unit_price` decimal(15,2) DEFAULT NULL,
  `total_price` decimal(15,2) DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'received',
  `notes` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordered_at` timestamp NOT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `stock_movement_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_orders_po_number_unique` (`po_number`),
  KEY `purchase_orders_stock_movement_id_foreign` (`stock_movement_id`),
  KEY `purchase_orders_created_by_foreign` (`created_by`),
  KEY `purchase_orders_product_id_ordered_at_index` (`product_id`,`ordered_at`),
  KEY `purchase_orders_supplier_id_ordered_at_index` (`supplier_id`,`ordered_at`),
  KEY `purchase_orders_location_id_ordered_at_index` (`location_id`,`ordered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`),
  KEY `roles_created_by_foreign` (`created_by`),
  KEY `roles_updated_by_foreign` (`updated_by`),
  KEY `roles_name_is_active_index` (`name`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'super_admin_template', 'Super Administrator Template', 'Template with full system access - all permissions', 1, NULL, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(2, 'admin_template', 'Administrator Template', 'Template with management access and most features', 1, NULL, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(3, 'user_manager_template', 'User Manager Template', 'Template for user management focus', 1, NULL, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(4, 'viewer_template', 'Viewer Template', 'Template for read-only access', 1, NULL, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48');

-- --------------------------------------------------------

--
-- Table structure for table `role_permission`
--

DROP TABLE IF EXISTS `role_permission`;
CREATE TABLE IF NOT EXISTS `role_permission` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permission_role_id_permission_id_unique` (`role_id`,`permission_id`),
  KEY `role_permission_permission_id_foreign` (`permission_id`),
  KEY `role_permission_created_by_foreign` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permission`
--

INSERT INTO `role_permission` (`id`, `role_id`, `permission_id`, `created_by`, `created_at`, `updated_at`) VALUES
(77, 1, 1, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(78, 1, 2, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(79, 1, 3, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(80, 1, 4, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(81, 1, 5, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(82, 1, 6, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(83, 1, 7, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(84, 1, 8, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(85, 1, 9, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(86, 1, 10, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(87, 1, 11, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(88, 1, 12, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(89, 1, 13, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(90, 1, 14, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(91, 1, 15, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(92, 1, 16, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(93, 1, 17, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(94, 1, 18, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(95, 1, 19, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(96, 1, 20, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(97, 1, 21, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(98, 1, 22, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(99, 1, 23, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(100, 1, 24, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(101, 1, 25, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(102, 1, 26, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(103, 1, 27, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(104, 1, 28, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(105, 1, 29, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(106, 1, 30, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(107, 1, 31, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(108, 1, 32, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(109, 1, 33, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(110, 1, 34, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(111, 1, 35, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(112, 1, 36, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(113, 1, 37, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(114, 1, 38, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(115, 1, 39, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(116, 1, 40, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(117, 1, 41, NULL, '2026-04-15 02:38:47', '2026-04-15 02:38:47'),
(118, 1, 42, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(119, 1, 43, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(120, 1, 44, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(121, 1, 45, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(122, 1, 46, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(123, 1, 47, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(124, 1, 48, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(125, 1, 49, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(126, 1, 50, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(127, 1, 51, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(128, 1, 52, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(129, 1, 53, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(130, 1, 54, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(131, 1, 55, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(132, 1, 56, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(133, 1, 57, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(134, 2, 1, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(135, 2, 2, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(136, 2, 3, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(137, 2, 4, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(138, 2, 5, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(139, 2, 6, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(140, 2, 8, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(141, 2, 10, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(142, 2, 11, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(143, 2, 12, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(144, 2, 14, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(145, 2, 15, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(146, 2, 16, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(147, 2, 17, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(148, 2, 18, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(149, 2, 19, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(150, 2, 21, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(151, 2, 22, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(152, 2, 23, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(153, 2, 24, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(154, 2, 25, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(155, 2, 26, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(156, 2, 28, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(157, 2, 29, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(158, 2, 30, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(159, 2, 31, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(160, 2, 32, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(161, 2, 34, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(162, 2, 35, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(163, 2, 36, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(164, 2, 37, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(165, 2, 38, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(166, 2, 39, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(167, 2, 40, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(168, 2, 41, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(169, 2, 42, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(170, 2, 43, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(171, 2, 44, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(172, 2, 45, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(173, 2, 46, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(174, 2, 47, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(175, 2, 48, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(176, 2, 49, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(177, 2, 50, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(178, 2, 51, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(179, 2, 52, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(180, 2, 53, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(181, 2, 54, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(182, 2, 55, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(183, 2, 56, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(184, 2, 57, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(185, 3, 1, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(186, 3, 2, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(187, 3, 3, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(188, 3, 4, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(189, 3, 8, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(190, 3, 10, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(191, 4, 1, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(192, 4, 2, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(193, 4, 21, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(194, 4, 22, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(195, 4, 28, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(196, 4, 34, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(197, 4, 38, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(198, 4, 42, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(199, 4, 46, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(200, 4, 50, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(201, 4, 54, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48'),
(202, 4, 56, NULL, '2026-04-15 02:38:48', '2026-04-15 02:38:48');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('rMJRD7xAQw0Z6vGFsZ9bFgd36lRErZuLxeByleGb', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiMEkzUDJMa0wyQ3ZGZElRQXNLa0U5Z2dLTzBSbnM0Nk1qR3owQXhLZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wcm9kdWN0cy85L3N0b2NrcyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czo0OiJhdXRoIjthOjE6e3M6MjE6InBhc3N3b3JkX2NvbmZpcm1lZF9hdCI7aToxNzc2MzkzOTA1O319', 1776411035);

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `location_id` bigint UNSIGNED NOT NULL,
  `movement_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(14,2) NOT NULL,
  `good_delta` decimal(14,2) NOT NULL DEFAULT '0.00',
  `damaged_delta` decimal(14,2) NOT NULL DEFAULT '0.00',
  `stock_before` decimal(14,2) NOT NULL DEFAULT '0.00',
  `stock_after` decimal(14,2) NOT NULL DEFAULT '0.00',
  `damaged_before` decimal(14,2) NOT NULL DEFAULT '0.00',
  `damaged_after` decimal(14,2) NOT NULL DEFAULT '0.00',
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `movement_at` timestamp NOT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_created_by_foreign` (`created_by`),
  KEY `stock_movements_product_id_movement_at_index` (`product_id`,`movement_at`),
  KEY `stock_movements_location_id_movement_at_index` (`location_id`,`movement_at`),
  KEY `stock_movements_reference_type_reference_code_index` (`reference_type`,`reference_code`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `product_id`, `location_id`, `movement_type`, `quantity`, `good_delta`, `damaged_delta`, `stock_before`, `stock_after`, `damaged_before`, `damaged_after`, `reference_type`, `reference_code`, `notes`, `movement_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'opening', 25.00, 25.00, 0.00, 0.00, 25.00, 0.00, 0.00, NULL, NULL, 'Saldo awal seed', '2026-04-15 02:38:51', NULL, '2026-04-15 02:38:51', '2026-04-15 02:38:51'),
(2, 1, 1, 'transfer_out', 8.00, -8.00, 0.00, 25.00, 17.00, 0.00, 0.00, 'transfer', 'TRF-1-260415093852-639', 'Display awal toko', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(3, 1, 3, 'transfer_in', 8.00, 8.00, 0.00, 0.00, 8.00, 0.00, 0.00, 'transfer', 'TRF-1-260415093852-639', 'Display awal toko', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(4, 1, 3, 'damaged_in', 1.00, -1.00, 1.00, 8.00, 7.00, 0.00, 1.00, NULL, NULL, 'Panel retak saat pengecekan', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(5, 2, 1, 'opening', 40.00, 40.00, 0.00, 0.00, 40.00, 0.00, 0.00, NULL, NULL, 'Saldo awal seed', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(6, 2, 1, 'transfer_out', 12.00, -12.00, 0.00, 40.00, 28.00, 0.00, 0.00, 'transfer', 'TRF-2-260415093852-551', 'Restock etalase depan', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(7, 2, 3, 'transfer_in', 12.00, 12.00, 0.00, 0.00, 12.00, 0.00, 0.00, 'transfer', 'TRF-2-260415093852-551', 'Restock etalase depan', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(8, 2, 1, 'in', 5.00, 5.00, 0.00, 28.00, 33.00, 0.00, 0.00, NULL, NULL, 'Barang datang tambahan supplier', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(9, 3, 1, 'opening', 18.00, 18.00, 0.00, 0.00, 18.00, 0.00, 0.00, NULL, NULL, 'Saldo awal seed', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(10, 3, 1, 'transfer_out', 4.00, -4.00, 0.00, 18.00, 14.00, 0.00, 0.00, 'transfer', 'TRF-3-260415093852-697', 'Display casing premium', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(11, 3, 2, 'transfer_in', 4.00, 4.00, 0.00, 0.00, 4.00, 0.00, 0.00, 'transfer', 'TRF-3-260415093852-697', 'Display casing premium', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(12, 4, 1, 'opening', 60.00, 60.00, 0.00, 0.00, 60.00, 0.00, 0.00, NULL, NULL, 'Saldo awal seed', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(13, 4, 1, 'transfer_out', 20.00, -20.00, 0.00, 60.00, 40.00, 0.00, 0.00, 'transfer', 'TRF-4-260415093852-914', 'Stok teknisi counter', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(14, 4, 3, 'transfer_in', 20.00, 20.00, 0.00, 0.00, 20.00, 0.00, 0.00, 'transfer', 'TRF-4-260415093852-914', 'Stok teknisi counter', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(15, 4, 3, 'out', 3.00, -3.00, 0.00, 20.00, 17.00, 0.00, 0.00, NULL, NULL, 'Pakai internal servis', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(16, 5, 1, 'opening', 10.00, 10.00, 0.00, 0.00, 10.00, 0.00, 0.00, NULL, NULL, 'Saldo awal seed', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(17, 5, 1, 'transfer_out', 2.00, -2.00, 0.00, 10.00, 8.00, 0.00, 0.00, 'transfer', 'TRF-5-260415093852-753', 'Stok display iPhone', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(18, 5, 3, 'transfer_in', 2.00, 2.00, 0.00, 0.00, 2.00, 0.00, 0.00, 'transfer', 'TRF-5-260415093852-753', 'Stok display iPhone', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(19, 6, 1, 'opening', 35.00, 35.00, 0.00, 0.00, 35.00, 0.00, 0.00, NULL, NULL, 'Saldo awal seed', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(20, 6, 1, 'transfer_out', 10.00, -10.00, 0.00, 35.00, 25.00, 0.00, 0.00, 'transfer', 'TRF-6-260415093852-336', 'Barang fast moving', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(21, 6, 3, 'transfer_in', 10.00, 10.00, 0.00, 0.00, 10.00, 0.00, 0.00, 'transfer', 'TRF-6-260415093852-336', 'Barang fast moving', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(22, 6, 3, 'adjustment_plus', 2.00, 2.00, 0.00, 10.00, 12.00, 0.00, 0.00, NULL, NULL, 'Selisih opname masuk', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(23, 7, 1, 'opening', 12.00, 12.00, 0.00, 0.00, 12.00, 0.00, 0.00, NULL, NULL, 'Saldo awal seed', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(24, 7, 1, 'transfer_out', 5.00, -5.00, 0.00, 12.00, 7.00, 0.00, 0.00, 'transfer', 'TRF-7-260417100726-702', 'Display awal LCD iPhone 11', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(25, 7, 3, 'transfer_in', 5.00, 5.00, 0.00, 0.00, 5.00, 0.00, 0.00, 'transfer', 'TRF-7-260417100726-702', 'Display awal LCD iPhone 11', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(26, 7, 1, 'transfer_out', 2.00, -2.00, 0.00, 7.00, 5.00, 0.00, 0.00, 'transfer', 'TRF-7-260417100726-360', 'Stok etalase premium', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(27, 7, 2, 'transfer_in', 2.00, 2.00, 0.00, 0.00, 2.00, 0.00, 0.00, 'transfer', 'TRF-7-260417100726-360', 'Stok etalase premium', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(28, 7, 3, 'damaged_in', 1.00, -1.00, 1.00, 5.00, 4.00, 0.00, 1.00, NULL, NULL, 'Panel retak saat quality check', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(29, 8, 1, 'opening', 80.00, 80.00, 0.00, 0.00, 80.00, 0.00, 0.00, NULL, NULL, 'Saldo awal seed', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(30, 8, 1, 'transfer_out', 25.00, -25.00, 0.00, 80.00, 55.00, 0.00, 0.00, 'transfer', 'TRF-8-260417100726-774', 'Stok fast moving toko', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(31, 8, 3, 'transfer_in', 25.00, 25.00, 0.00, 0.00, 25.00, 0.00, 0.00, 'transfer', 'TRF-8-260417100726-774', 'Stok fast moving toko', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(32, 8, 1, 'transfer_out', 10.00, -10.00, 0.00, 55.00, 45.00, 0.00, 0.00, 'transfer', 'TRF-8-260417100726-358', 'Display baterai Samsung', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(33, 8, 2, 'transfer_in', 10.00, 10.00, 0.00, 0.00, 10.00, 0.00, 0.00, 'transfer', 'TRF-8-260417100726-358', 'Display baterai Samsung', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(34, 8, 1, 'in', 15.00, 15.00, 0.00, 45.00, 60.00, 0.00, 0.00, NULL, NULL, 'Barang datang tambahan supplier', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

DROP TABLE IF EXISTS `stock_transfers`;
CREATE TABLE IF NOT EXISTS `stock_transfers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `transfer_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_location_id` bigint UNSIGNED NOT NULL,
  `target_location_id` bigint UNSIGNED NOT NULL,
  `quantity` decimal(14,2) NOT NULL,
  `notes` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transferred_at` timestamp NOT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_transfers_transfer_code_unique` (`transfer_code`),
  KEY `stock_transfers_source_location_id_foreign` (`source_location_id`),
  KEY `stock_transfers_target_location_id_foreign` (`target_location_id`),
  KEY `stock_transfers_created_by_foreign` (`created_by`),
  KEY `stock_transfers_product_id_transferred_at_index` (`product_id`,`transferred_at`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transfers`
--

INSERT INTO `stock_transfers` (`id`, `product_id`, `transfer_code`, `source_location_id`, `target_location_id`, `quantity`, `notes`, `transferred_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'TRF-1-260415093852-639', 1, 3, 8.00, 'Display awal toko', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(2, 2, 'TRF-2-260415093852-551', 1, 3, 12.00, 'Restock etalase depan', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(3, 3, 'TRF-3-260415093852-697', 1, 2, 4.00, 'Display casing premium', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(4, 4, 'TRF-4-260415093852-914', 1, 3, 20.00, 'Stok teknisi counter', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(5, 5, 'TRF-5-260415093852-753', 1, 3, 2.00, 'Stok display iPhone', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(6, 6, 'TRF-6-260415093852-336', 1, 3, 10.00, 'Barang fast moving', '2026-04-15 02:38:52', NULL, '2026-04-15 02:38:52', '2026-04-15 02:38:52'),
(7, 7, 'TRF-7-260417100726-702', 1, 3, 5.00, 'Display awal LCD iPhone 11', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(8, 7, 'TRF-7-260417100726-360', 1, 2, 2.00, 'Stok etalase premium', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(9, 8, 'TRF-8-260417100726-774', 1, 3, 25.00, 'Stok fast moving toko', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26'),
(10, 8, 'TRF-8-260417100726-358', 1, 2, 10.00, 'Display baterai Samsung', '2026-04-17 03:07:26', NULL, '2026-04-17 03:07:26', '2026-04-17 03:07:26');

-- --------------------------------------------------------

--
-- Table structure for table `sub_categories`
--

DROP TABLE IF EXISTS `sub_categories`;
CREATE TABLE IF NOT EXISTS `sub_categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_categories_slug_unique` (`slug`),
  KEY `sub_categories_category_id_foreign` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sub_categories`
--

INSERT INTO `sub_categories` (`id`, `category_id`, `name`, `slug`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'OLED', 'lcd-touchscreen-oled', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(2, 1, 'Incell', 'lcd-touchscreen-incell', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(3, 1, 'Touchscreen', 'lcd-touchscreen-touchscreen', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(4, 2, 'Baterai Original Grade', 'baterai-baterai-original-grade', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(5, 2, 'Baterai Double Power', 'baterai-baterai-double-power', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(6, 2, 'Baterai Tanam', 'baterai-baterai-tanam', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(7, 3, 'Backdoor', 'backdoor-casing-backdoor', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(8, 3, 'Frame', 'backdoor-casing-frame', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(9, 3, 'Housing', 'backdoor-casing-housing', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(10, 4, 'Flexibel Power', 'flexibel-tombol-flexibel-power', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(11, 4, 'Flexibel Volume', 'flexibel-tombol-flexibel-volume', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(12, 4, 'Flexibel Fingerprint', 'flexibel-tombol-flexibel-fingerprint', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(13, 5, 'Kamera Depan', 'kamera-kamera-depan', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(14, 5, 'Kamera Belakang', 'kamera-kamera-belakang', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(15, 5, 'Kaca Kamera', 'kamera-kaca-kamera', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(16, 6, 'Board Charger', 'konektor-charging-board-charger', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(17, 6, 'Port Charger', 'konektor-charging-port-charger', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(18, 6, 'Mic Flexibel', 'konektor-charging-mic-flexibel', 1, '2026-04-15 02:38:50', '2026-04-15 02:38:50');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `notes` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `suppliers_name_unique` (`name`),
  UNIQUE KEY `suppliers_slug_unique` (`slug`),
  UNIQUE KEY `suppliers_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `code`, `slug`, `phone`, `email`, `contact_person`, `address`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Supplier 1', NULL, 'supplier-1', NULL, NULL, 'Dodit', NULL, NULL, 1, '2026-04-16 06:55:42', '2026-04-16 08:02:52'),
(2, 'CV Mitra LCD Nusantara', 'SUP-LCD', 'cv-mitra-lcd-nusantara', '081234567801', NULL, 'Andi', 'Jakarta Barat', NULL, 1, '2026-04-17 03:07:25', '2026-04-17 03:07:25'),
(3, 'PT Baterai Mobile Indo', 'SUP-BAT', 'pt-baterai-mobile-indo', '081234567802', NULL, 'Rina', 'Jakarta Utara', NULL, 1, '2026-04-17 03:07:25', '2026-04-17 03:07:25'),
(4, 'Sinar Sparepart Gadget', 'SUP-SPG', 'sinar-sparepart-gadget', '081234567803', NULL, 'Budi', 'Surabaya', NULL, 1, '2026-04-17 03:07:25', '2026-04-17 03:07:25'),
(5, 'Galaxy Part Center', 'SUP-GPC', 'galaxy-part-center', '081234567804', NULL, 'Nando', 'Bandung', NULL, 1, '2026-04-17 03:07:25', '2026-04-17 03:07:25');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
CREATE TABLE IF NOT EXISTS `units` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_name_unique` (`name`),
  UNIQUE KEY `units_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`, `code`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'LUSIN', 'LUSIN', 1, '2026-04-16 07:49:44', '2026-04-16 07:49:44'),
(2, 'PCS', 'PCS', 1, '2026-04-17 03:07:25', '2026-04-17 03:07:25'),
(3, 'SET', 'SET', 1, '2026-04-17 03:07:25', '2026-04-17 03:07:25'),
(4, 'PACK', 'PACK', 1, '2026-04-17 03:07:25', '2026-04-17 03:07:25'),
(5, 'DUS', 'DUS', 1, '2026-04-17 03:07:25', '2026-04-17 03:07:25'),
(6, 'ROLL', 'ROLL', 1, '2026-04-17 03:07:25', '2026-04-17 03:07:25'),
(7, 'LEMBAR', 'LEMBAR', 1, '2026-04-17 03:07:25', '2026-04-17 03:07:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `guid` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domain` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_guid_unique` (`guid`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_created_by_foreign` (`created_by`),
  KEY `users_updated_by_foreign` (`updated_by`),
  KEY `users_deleted_by_foreign` (`deleted_by`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `guid`, `username`, `domain`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`) VALUES
(1, 'Owner Uteparts', 'owner@uteparts.test', '2026-04-15 02:38:49', '$2y$12$yCAkegiz8DhadSWLnmcjdeiIYml2khLkNxPnVh1qkfVvKwTI34YDi', NULL, '2026-04-14 07:36:30', '2026-04-15 02:38:49', NULL, 'owner', NULL, NULL, NULL, NULL, NULL),
(2, 'Manager Uteparts', 'manager@uteparts.test', '2026-04-15 02:38:50', '$2y$12$zzs0Soa/DhqAmjUVJY.odOzmwUxu6dc4867l32YmxIkWRSgfIbFV2', NULL, '2026-04-14 07:36:30', '2026-04-15 02:38:50', NULL, 'manager', NULL, NULL, NULL, NULL, NULL),
(3, 'Kasir Uteparts', 'kasir@uteparts.test', '2026-04-15 02:38:50', '$2y$12$3xOtkRXgGlRe/WYaf3IABO9xjC4s/OF5oLiBEoMnhanwIn61.RLcq', NULL, '2026-04-14 07:36:31', '2026-04-15 02:38:50', NULL, 'kasir', NULL, NULL, NULL, NULL, NULL),
(4, 'Staging Purpose', 'staging@tongtji.com', '2026-04-15 02:38:50', '$2y$12$1oXo3BxwiyjhU8il4CUBquwSuTOFPdGeyhv5mp0HAv6wKaZAM4UFG', NULL, '2026-04-14 07:36:31', '2026-04-15 02:43:04', NULL, 'stagingpurpose', NULL, NULL, 1, 1, '2026-04-15 02:43:04');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

DROP TABLE IF EXISTS `user_logs`;
CREATE TABLE IF NOT EXISTS `user_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `target_user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `user_logs_target_user_id_created_at_index` (`target_user_id`,`created_at`),
  KEY `user_logs_action_created_at_index` (`action`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `target_user_id`, `action`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 'DELETE_USER', 'Soft deleted user: Staging Purpose (stagingpurpose)', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 02:43:04', '2026-04-15 02:43:04'),
(2, 1, NULL, 'CREATE_SUPPLIER', 'Created supplier: Suppliyer 1', NULL, '{\"code\": null, \"name\": \"Suppliyer 1\", \"email\": null, \"phone\": null, \"is_active\": true}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 06:55:42', '2026-04-16 06:55:42'),
(3, 1, NULL, 'CREATE_UNIT', 'Created unit: LUSIN', NULL, '{\"code\": \"LUSIN\", \"name\": \"LUSIN\", \"is_active\": true}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 07:49:44', '2026-04-16 07:49:44'),
(4, 1, NULL, 'UPDATE_SUPPLIER', 'Updated supplier: Supplier 1', '{\"code\": null, \"name\": \"Suppliyer 1\", \"email\": null, \"phone\": null, \"is_active\": true, \"contact_person\": \"Dodit\"}', '{\"code\": null, \"name\": \"Supplier 1\", \"email\": null, \"phone\": null, \"is_active\": true, \"contact_person\": \"Dodit\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 08:02:52', '2026-04-16 08:02:52'),
(5, 1, NULL, 'CREATE_PRODUCT', 'Created product: Tes Baterai 1 (PRD-260417142536-821)', NULL, '{\"sku\": \"PRD-260417142536-821\", \"name\": \"Tes Baterai 1\", \"brand\": \"Xiaomi\", \"types\": [\"A12\", \"A13\"], \"barcode\": \"1230120312\", \"buy_unit\": \"DUS\", \"category\": \"Baterai\", \"is_active\": true, \"sale_unit\": \"DUS\", \"suppliers\": [{\"supplier\": \"Sinar Sparepart Gadget\", \"is_primary\": true, \"last_purchase_price\": null, \"supplier_product_code\": null}], \"is_published\": false, \"product_code\": \"PRD-260417142536-821\", \"stock_global\": \"2.00\", \"sub_category\": \"Baterai Original Grade\", \"selling_price\": \"400000.00\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 07:25:36', '2026-04-17 07:25:36'),
(6, 1, NULL, 'UPDATE_PRODUCT', 'Updated product: Tes Baterai 1 (PRD-260417142536-821)', '{\"sku\": \"PRD-260417142536-821\", \"name\": \"Tes Baterai 1\", \"brand\": \"Xiaomi\", \"types\": [\"A12\", \"A13\"], \"barcode\": \"1230120312\", \"buy_unit\": \"DUS\", \"category\": \"Baterai\", \"is_active\": true, \"sale_unit\": \"DUS\", \"suppliers\": [{\"supplier\": \"Sinar Sparepart Gadget\", \"is_primary\": true, \"last_purchase_price\": null, \"supplier_product_code\": null}], \"is_published\": false, \"product_code\": \"PRD-260417142536-821\", \"stock_global\": \"2.00\", \"sub_category\": \"Baterai Original Grade\", \"selling_price\": \"400000.00\"}', '{\"sku\": \"PRD-260417142536-821\", \"name\": \"Tes Baterai 1\", \"brand\": \"Xiaomi\", \"types\": [\"A12\", \"A13\"], \"barcode\": \"1230120312\", \"buy_unit\": \"PCS\", \"category\": \"Baterai\", \"is_active\": true, \"sale_unit\": \"DUS\", \"suppliers\": [{\"supplier\": \"Sinar Sparepart Gadget\", \"is_primary\": true, \"last_purchase_price\": null, \"supplier_product_code\": null}], \"is_published\": false, \"product_code\": \"PRD-260417142536-821\", \"stock_global\": \"2.00\", \"sub_category\": \"Baterai Original Grade\", \"selling_price\": \"400000.00\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 07:26:22', '2026-04-17 07:26:22'),
(7, 1, NULL, 'UPDATE_PRODUCT', 'Updated product: Tes Baterai 1 (PRD-260417142536-821)', '{\"sku\": \"PRD-260417142536-821\", \"name\": \"Tes Baterai 1\", \"brand\": \"Xiaomi\", \"types\": [\"A12\", \"A13\"], \"barcode\": \"1230120312\", \"buy_unit\": \"PCS\", \"category\": \"Baterai\", \"is_active\": true, \"sale_unit\": \"DUS\", \"suppliers\": [{\"supplier\": \"Sinar Sparepart Gadget\", \"is_primary\": true, \"last_purchase_price\": null, \"supplier_product_code\": null}], \"is_published\": false, \"product_code\": \"PRD-260417142536-821\", \"stock_global\": \"2.00\", \"sub_category\": \"Baterai Original Grade\", \"selling_price\": \"400000.00\"}', '{\"sku\": \"PRD-260417142536-821\", \"name\": \"Tes Baterai 1\", \"brand\": \"Xiaomi\", \"types\": [\"A12\", \"A13\"], \"barcode\": \"1230120312\", \"buy_unit\": \"PCS\", \"category\": \"Baterai\", \"is_active\": true, \"sale_unit\": \"PCS\", \"suppliers\": [{\"supplier\": \"Sinar Sparepart Gadget\", \"is_primary\": true, \"last_purchase_price\": null, \"supplier_product_code\": null}], \"is_published\": false, \"product_code\": \"PRD-260417142536-821\", \"stock_global\": \"2.00\", \"sub_category\": \"Baterai Original Grade\", \"selling_price\": \"400000.00\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 07:27:03', '2026-04-17 07:27:03');

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

DROP TABLE IF EXISTS `user_role`;
CREATE TABLE IF NOT EXISTS `user_role` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  `assigned_by` bigint UNSIGNED DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_role_user_id_role_id_unique` (`user_id`,`role_id`),
  KEY `user_role_role_id_foreign` (`role_id`),
  KEY `user_role_assigned_by_foreign` (`assigned_by`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`id`, `user_id`, `role_id`, `assigned_by`, `assigned_at`, `created_at`, `updated_at`) VALUES
(5, 1, 1, NULL, '2026-04-15 02:38:49', '2026-04-15 02:38:49', '2026-04-15 02:38:49'),
(6, 2, 2, NULL, '2026-04-15 02:38:50', '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(7, 3, 4, NULL, '2026-04-15 02:38:50', '2026-04-15 02:38:50', '2026-04-15 02:38:50'),
(8, 4, 1, NULL, '2026-04-15 02:38:50', '2026-04-15 02:38:50', '2026-04-15 02:38:50');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `permissions_parent_foreign` FOREIGN KEY (`parent`) REFERENCES `permissions` (`name`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_default_location_id_foreign` FOREIGN KEY (`default_location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_sub_category_id_foreign` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_barcodes`
--
ALTER TABLE `product_barcodes`
  ADD CONSTRAINT `product_barcodes_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_customer_group_prices`
--
ALTER TABLE `product_customer_group_prices`
  ADD CONSTRAINT `product_customer_group_prices_customer_group_id_foreign` FOREIGN KEY (`customer_group_id`) REFERENCES `customer_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_customer_group_prices_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_price_tiers`
--
ALTER TABLE `product_price_tiers`
  ADD CONSTRAINT `product_price_tiers_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_product_type`
--
ALTER TABLE `product_product_type`
  ADD CONSTRAINT `product_product_type_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_product_type_product_type_id_foreign` FOREIGN KEY (`product_type_id`) REFERENCES `product_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_stocks`
--
ALTER TABLE `product_stocks`
  ADD CONSTRAINT `product_stocks_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_suppliers`
--
ALTER TABLE `product_suppliers`
  ADD CONSTRAINT `product_suppliers_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_suppliers_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `product_types`
--
ALTER TABLE `product_types`
  ADD CONSTRAINT `product_types_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_units`
--
ALTER TABLE `product_units`
  ADD CONSTRAINT `product_units_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `purchase_orders_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_orders_stock_movement_id_foreign` FOREIGN KEY (`stock_movement_id`) REFERENCES `stock_movements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `roles_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `role_permission_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `role_permission_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permission_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movements_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD CONSTRAINT `stock_transfers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_transfers_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_transfers_source_location_id_foreign` FOREIGN KEY (`source_location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `stock_transfers_target_location_id_foreign` FOREIGN KEY (`target_location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD CONSTRAINT `sub_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_target_user_id_foreign` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_role`
--
ALTER TABLE `user_role`
  ADD CONSTRAINT `user_role_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_role_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
