-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 21 Agu 2025 pada 19.26
-- Versi server: 10.4.24-MariaDB
-- Versi PHP: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_volumeair`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `daily_water_summary`
--

CREATE TABLE `daily_water_summary` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `total_volume` decimal(10,3) NOT NULL DEFAULT 0.000,
  `total_usage_time` int(11) NOT NULL DEFAULT 0,
  `peak_flow_rate` decimal(6,3) NOT NULL DEFAULT 0.000,
  `average_flow_rate` decimal(6,3) NOT NULL DEFAULT 0.000,
  `usage_sessions` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `daily_water_summary`
--

INSERT INTO `daily_water_summary` (`id`, `date`, `total_volume`, `total_usage_time`, `peak_flow_rate`, `average_flow_rate`, `usage_sessions`, `created_at`, `updated_at`) VALUES
(1, '2025-08-21', '24.819', 935, '2.930', '2.553', 1, '2025-08-21 08:31:52', '2025-08-21 08:31:53'),
(2, '2025-08-20', '0.833', 25, '2.750', '2.456', 1, '2025-08-21 08:31:52', '2025-08-21 08:31:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_08_21_152309_create_volumeair_tables', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sensor_data`
--

CREATE TABLE `sensor_data` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jarak` decimal(5,2) NOT NULL,
  `flow` decimal(6,3) NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active_prayer` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sensor_data`
--

INSERT INTO `sensor_data` (`id`, `jarak`, `flow`, `status`, `active_prayer`, `timestamp`) VALUES
(1, '42.61', '2.553', 'ON', NULL, '2025-08-21 03:20:52'),
(2, '28.33', '1.895', 'ON', NULL, '2025-08-21 15:20:52'),
(3, '35.61', '2.310', 'ON', NULL, '2025-08-21 02:34:52'),
(4, '33.60', '0.885', 'ON', NULL, '2025-08-21 14:05:52'),
(5, '8.21', '0.203', 'ON', NULL, '2025-08-21 14:28:52'),
(6, '43.82', '0.246', 'ON', NULL, '2025-08-21 03:57:52'),
(7, '27.16', '2.140', 'ON', NULL, '2025-08-21 01:22:52'),
(8, '36.66', '1.986', 'ON', NULL, '2025-08-21 15:15:52'),
(9, '6.14', '1.425', 'ON', NULL, '2025-08-20 22:28:52'),
(10, '32.66', '0.270', 'ON', NULL, '2025-08-20 17:13:52'),
(11, '39.50', '2.349', 'ON', NULL, '2025-08-21 12:33:52'),
(12, '31.55', '2.288', 'ON', NULL, '2025-08-20 19:28:52'),
(13, '45.57', '1.888', 'ON', NULL, '2025-08-20 20:56:52'),
(14, '21.22', '2.456', 'ON', NULL, '2025-08-20 16:11:52'),
(15, '30.17', '2.275', 'ON', NULL, '2025-08-21 12:31:52'),
(16, '24.39', '2.144', 'ON', NULL, '2025-08-20 21:29:52'),
(17, '47.33', '0.518', 'ON', NULL, '2025-08-21 13:31:52'),
(18, '9.99', '2.549', 'ON', NULL, '2025-08-21 07:55:52'),
(19, '10.82', '1.276', 'ON', NULL, '2025-08-21 03:29:52'),
(20, '30.79', '2.084', 'ON', NULL, '2025-08-20 22:07:52'),
(21, '21.95', '1.097', 'ON', NULL, '2025-08-21 14:17:52'),
(22, '37.71', '0.952', 'ON', NULL, '2025-08-21 08:35:52'),
(23, '48.14', '1.309', 'ON', NULL, '2025-08-20 23:05:52'),
(24, '15.11', '0.292', 'ON', NULL, '2025-08-21 00:07:52'),
(25, '48.94', '0.221', 'ON', NULL, '2025-08-21 01:17:52'),
(26, '38.48', '0.896', 'ON', NULL, '2025-08-20 22:07:52'),
(27, '12.38', '0.090', 'ON', NULL, '2025-08-21 10:02:52'),
(28, '9.73', '2.443', 'ON', NULL, '2025-08-21 08:58:52'),
(29, '21.24', '1.011', 'ON', NULL, '2025-08-21 12:46:52'),
(30, '12.06', '1.182', 'ON', NULL, '2025-08-20 18:34:52'),
(31, '17.69', '1.884', 'ON', NULL, '2025-08-21 06:57:52'),
(32, '13.98', '1.129', 'ON', NULL, '2025-08-21 07:52:52'),
(33, '36.29', '1.162', 'ON', NULL, '2025-08-21 08:14:52'),
(34, '22.04', '1.069', 'ON', NULL, '2025-08-21 03:36:52'),
(35, '26.44', '2.341', 'ON', NULL, '2025-08-21 01:31:52'),
(36, '42.68', '1.243', 'ON', NULL, '2025-08-21 14:00:52'),
(37, '13.22', '2.748', 'ON', NULL, '2025-08-21 07:57:52'),
(38, '46.17', '2.518', 'ON', NULL, '2025-08-21 10:36:52'),
(39, '30.23', '2.827', 'ON', NULL, '2025-08-20 21:24:52'),
(40, '28.11', '2.559', 'ON', NULL, '2025-08-21 05:35:52'),
(41, '6.70', '2.433', 'ON', NULL, '2025-08-21 01:18:52'),
(42, '43.10', '2.917', 'ON', NULL, '2025-08-21 08:18:52'),
(43, '45.13', '1.252', 'ON', NULL, '2025-08-21 04:06:52'),
(44, '9.64', '0.307', 'ON', NULL, '2025-08-21 02:48:52'),
(45, '10.95', '0.704', 'ON', NULL, '2025-08-21 11:58:52'),
(46, '43.75', '2.649', 'ON', NULL, '2025-08-21 08:55:52'),
(47, '23.65', '2.265', 'ON', NULL, '2025-08-20 23:14:52'),
(48, '8.43', '1.194', 'ON', NULL, '2025-08-21 14:40:52'),
(49, '24.68', '2.753', 'ON', NULL, '2025-08-21 10:38:52'),
(50, '41.69', '2.560', 'ON', NULL, '2025-08-20 21:25:52'),
(51, '37.61', '0.716', 'ON', NULL, '2025-08-21 13:46:52'),
(52, '42.32', '2.358', 'ON', NULL, '2025-08-21 07:14:52'),
(53, '38.58', '0.254', 'ON', NULL, '2025-08-21 15:03:52'),
(54, '35.83', '2.930', 'ON', NULL, '2025-08-20 19:57:52'),
(55, '30.07', '2.691', 'ON', NULL, '2025-08-20 21:29:52'),
(56, '20.92', '1.802', 'ON', NULL, '2025-08-20 23:30:52'),
(57, '39.04', '0.628', 'ON', NULL, '2025-08-21 13:51:52'),
(58, '44.28', '2.424', 'ON', NULL, '2025-08-21 10:40:52'),
(59, '49.37', '1.120', 'ON', NULL, '2025-08-20 17:04:52'),
(60, '33.23', '1.346', 'ON', NULL, '2025-08-21 01:44:52'),
(61, '46.16', '0.291', 'ON', NULL, '2025-08-20 21:21:52'),
(62, '6.02', '1.921', 'ON', NULL, '2025-08-21 03:08:52'),
(63, '7.72', '2.857', 'ON', NULL, '2025-08-21 05:25:52'),
(64, '49.08', '2.297', 'ON', NULL, '2025-08-21 13:47:52'),
(65, '38.02', '2.337', 'ON', NULL, '2025-08-21 00:46:52'),
(66, '8.28', '1.825', 'ON', NULL, '2025-08-21 15:02:52'),
(67, '5.61', '2.858', 'ON', NULL, '2025-08-20 23:16:52'),
(68, '22.19', '1.022', 'ON', NULL, '2025-08-21 00:33:52'),
(69, '18.53', '1.376', 'ON', NULL, '2025-08-21 09:39:52'),
(70, '10.33', '1.051', 'ON', NULL, '2025-08-20 17:17:52'),
(71, '33.64', '0.628', 'ON', NULL, '2025-08-21 06:33:52'),
(72, '13.17', '1.983', 'ON', NULL, '2025-08-21 13:14:52'),
(73, '6.92', '1.166', 'ON', NULL, '2025-08-20 20:11:52'),
(74, '27.77', '2.324', 'ON', NULL, '2025-08-20 19:26:52'),
(75, '38.37', '1.267', 'ON', NULL, '2025-08-21 03:14:52'),
(76, '25.93', '2.850', 'ON', NULL, '2025-08-21 11:28:52'),
(77, '43.97', '0.544', 'ON', NULL, '2025-08-21 00:56:52'),
(78, '49.39', '2.244', 'ON', NULL, '2025-08-21 10:16:52'),
(79, '37.82', '2.102', 'ON', NULL, '2025-08-21 03:58:52'),
(80, '49.52', '1.550', 'ON', NULL, '2025-08-21 01:58:52'),
(81, '42.41', '1.607', 'ON', NULL, '2025-08-20 21:39:52'),
(82, '41.59', '1.153', 'ON', NULL, '2025-08-20 23:54:52'),
(83, '14.87', '2.853', 'ON', NULL, '2025-08-20 17:20:52'),
(84, '33.38', '2.390', 'ON', NULL, '2025-08-20 20:13:52'),
(85, '10.96', '0.644', 'ON', NULL, '2025-08-20 21:40:52'),
(86, '33.98', '2.021', 'ON', NULL, '2025-08-21 05:47:52'),
(87, '26.18', '2.255', 'ON', NULL, '2025-08-21 07:05:52'),
(88, '17.69', '1.175', 'ON', NULL, '2025-08-21 03:10:52'),
(89, '11.34', '2.286', 'ON', NULL, '2025-08-21 11:54:52'),
(90, '20.64', '1.816', 'ON', NULL, '2025-08-21 01:35:52'),
(91, '46.73', '2.763', 'ON', NULL, '2025-08-21 03:15:52'),
(92, '38.69', '1.053', 'ON', NULL, '2025-08-21 13:59:52'),
(93, '25.71', '0.946', 'ON', NULL, '2025-08-21 07:05:52'),
(94, '29.56', '0.284', 'ON', NULL, '2025-08-21 07:30:52'),
(95, '21.41', '2.574', 'ON', NULL, '2025-08-20 19:58:52'),
(96, '26.73', '1.009', 'ON', NULL, '2025-08-21 07:18:52'),
(97, '21.40', '1.196', 'ON', NULL, '2025-08-21 12:40:52'),
(98, '12.48', '2.011', 'ON', NULL, '2025-08-20 22:47:52'),
(99, '20.24', '1.613', 'ON', NULL, '2025-08-20 17:03:52'),
(100, '26.64', '2.429', 'ON', NULL, '2025-08-21 14:45:52'),
(101, '42.06', '2.045', 'ON', NULL, '2025-08-21 05:34:52'),
(102, '18.08', '2.648', 'ON', NULL, '2025-08-20 18:31:52'),
(103, '23.30', '1.850', 'ON', NULL, '2025-08-21 02:20:52'),
(104, '22.53', '2.288', 'ON', NULL, '2025-08-20 22:34:52'),
(105, '27.95', '2.243', 'ON', NULL, '2025-08-21 07:21:52'),
(106, '41.22', '2.368', 'ON', NULL, '2025-08-21 07:06:52'),
(107, '21.70', '0.827', 'ON', NULL, '2025-08-21 10:27:52'),
(108, '47.70', '1.641', 'ON', NULL, '2025-08-20 23:09:52'),
(109, '48.40', '1.161', 'ON', NULL, '2025-08-21 08:01:52'),
(110, '21.00', '2.406', 'ON', NULL, '2025-08-20 23:53:52'),
(111, '25.46', '1.180', 'ON', NULL, '2025-08-21 05:12:52'),
(112, '15.38', '2.727', 'ON', NULL, '2025-08-20 17:33:52'),
(113, '13.41', '1.732', 'ON', NULL, '2025-08-20 21:13:52'),
(114, '33.00', '2.668', 'ON', NULL, '2025-08-21 02:23:52'),
(115, '46.50', '1.491', 'ON', NULL, '2025-08-21 03:30:52'),
(116, '35.78', '1.436', 'ON', NULL, '2025-08-21 08:14:52'),
(117, '15.24', '0.102', 'ON', NULL, '2025-08-20 23:59:52'),
(118, '12.56', '1.581', 'ON', NULL, '2025-08-21 08:30:52'),
(119, '36.38', '2.061', 'ON', NULL, '2025-08-21 07:49:52'),
(120, '29.37', '0.009', 'OFF', NULL, '2025-08-21 07:09:52'),
(121, '35.68', '2.399', 'ON', NULL, '2025-08-21 08:47:52'),
(122, '39.87', '2.157', 'ON', NULL, '2025-08-21 00:01:52'),
(123, '15.51', '1.312', 'ON', NULL, '2025-08-20 15:44:52'),
(124, '46.06', '0.070', 'ON', NULL, '2025-08-21 01:25:52'),
(125, '26.84', '1.318', 'ON', NULL, '2025-08-21 12:50:52'),
(126, '24.16', '0.764', 'ON', NULL, '2025-08-20 23:36:52'),
(127, '19.63', '2.377', 'ON', NULL, '2025-08-21 03:00:52'),
(128, '14.72', '1.407', 'ON', NULL, '2025-08-21 00:06:52'),
(129, '32.32', '2.750', 'ON', NULL, '2025-08-20 16:58:52'),
(130, '34.02', '1.122', 'ON', NULL, '2025-08-21 15:12:52'),
(131, '43.54', '0.090', 'ON', NULL, '2025-08-20 20:31:52'),
(132, '26.87', '2.899', 'ON', NULL, '2025-08-21 04:24:52'),
(133, '32.66', '0.145', 'ON', NULL, '2025-08-21 06:48:52'),
(134, '41.58', '1.064', 'ON', NULL, '2025-08-21 13:24:52'),
(135, '47.06', '1.814', 'ON', NULL, '2025-08-21 06:17:52'),
(136, '27.23', '0.206', 'ON', NULL, '2025-08-21 00:35:52'),
(137, '14.18', '1.555', 'ON', NULL, '2025-08-20 18:35:52'),
(138, '17.57', '2.363', 'ON', NULL, '2025-08-21 05:19:52'),
(139, '5.34', '2.477', 'ON', NULL, '2025-08-21 13:38:52'),
(140, '27.89', '1.748', 'ON', NULL, '2025-08-21 08:56:52'),
(141, '23.28', '2.293', 'ON', NULL, '2025-08-20 18:41:52'),
(142, '14.17', '1.395', 'ON', NULL, '2025-08-20 19:27:52'),
(143, '12.24', '1.252', 'ON', NULL, '2025-08-20 19:14:52'),
(144, '36.43', '1.739', 'ON', NULL, '2025-08-20 22:12:52'),
(145, '45.72', '0.727', 'ON', NULL, '2025-08-21 08:37:52'),
(146, '24.99', '1.384', 'ON', NULL, '2025-08-21 04:08:52'),
(147, '43.31', '1.567', 'ON', NULL, '2025-08-20 20:02:52'),
(148, '44.10', '1.910', 'ON', NULL, '2025-08-21 10:00:52'),
(149, '19.46', '0.904', 'ON', NULL, '2025-08-21 10:41:52'),
(150, '8.52', '2.885', 'ON', NULL, '2025-08-20 22:38:52'),
(151, '7.63', '2.620', 'ON', NULL, '2025-08-21 01:39:52'),
(152, '22.47', '2.078', 'ON', NULL, '2025-08-21 10:06:52'),
(153, '24.86', '1.866', 'ON', NULL, '2025-08-21 14:16:52'),
(154, '11.49', '1.276', 'ON', NULL, '2025-08-20 20:58:52'),
(155, '45.79', '0.829', 'ON', NULL, '2025-08-21 04:27:52'),
(156, '12.98', '1.194', 'ON', NULL, '2025-08-20 22:41:52'),
(157, '9.45', '2.310', 'ON', NULL, '2025-08-20 16:21:52'),
(158, '36.87', '0.756', 'ON', NULL, '2025-08-21 06:25:52'),
(159, '23.87', '0.002', 'OFF', NULL, '2025-08-21 00:49:52'),
(160, '26.22', '0.246', 'ON', NULL, '2025-08-21 13:48:52'),
(161, '9.48', '0.004', 'OFF', NULL, '2025-08-21 03:07:52'),
(162, '27.70', '1.664', 'ON', NULL, '2025-08-21 10:58:52'),
(163, '8.91', '1.394', 'ON', NULL, '2025-08-21 07:49:52'),
(164, '31.10', '0.298', 'ON', NULL, '2025-08-21 15:18:52'),
(165, '38.17', '1.150', 'ON', NULL, '2025-08-21 10:01:52'),
(166, '41.69', '0.784', 'ON', NULL, '2025-08-21 02:52:52'),
(167, '14.76', '2.820', 'ON', NULL, '2025-08-21 07:59:52'),
(168, '15.10', '2.587', 'ON', NULL, '2025-08-21 01:37:52'),
(169, '14.57', '2.295', 'ON', NULL, '2025-08-21 12:36:52'),
(170, '7.15', '1.025', 'ON', NULL, '2025-08-21 02:57:52'),
(171, '15.52', '0.390', 'ON', NULL, '2025-08-21 11:49:52'),
(172, '13.36', '1.318', 'ON', NULL, '2025-08-21 02:49:52'),
(173, '41.41', '1.303', 'ON', NULL, '2025-08-20 17:15:52'),
(174, '18.32', '1.051', 'ON', NULL, '2025-08-20 23:19:52'),
(175, '21.22', '2.471', 'ON', NULL, '2025-08-20 18:40:52'),
(176, '42.83', '2.195', 'ON', NULL, '2025-08-21 15:07:52'),
(177, '13.36', '0.653', 'ON', NULL, '2025-08-21 13:28:52'),
(178, '41.42', '0.394', 'ON', NULL, '2025-08-21 10:10:52'),
(179, '20.50', '2.231', 'ON', NULL, '2025-08-20 20:08:52'),
(180, '32.27', '1.479', 'ON', NULL, '2025-08-21 01:08:52'),
(181, '43.20', '0.211', 'ON', NULL, '2025-08-20 18:35:52'),
(182, '9.70', '0.503', 'ON', NULL, '2025-08-20 20:51:52'),
(183, '48.08', '2.116', 'ON', NULL, '2025-08-21 02:09:52'),
(184, '29.20', '1.169', 'ON', NULL, '2025-08-20 16:12:52'),
(185, '43.73', '0.372', 'ON', NULL, '2025-08-21 02:51:52'),
(186, '16.33', '0.076', 'ON', NULL, '2025-08-21 07:05:52'),
(187, '46.92', '0.908', 'ON', NULL, '2025-08-21 00:49:52'),
(188, '34.16', '0.413', 'ON', NULL, '2025-08-21 05:44:52'),
(189, '34.32', '0.027', 'OFF', NULL, '2025-08-21 11:49:52'),
(190, '22.37', '0.386', 'ON', NULL, '2025-08-21 00:36:52'),
(191, '47.23', '1.202', 'ON', NULL, '2025-08-20 22:59:52'),
(192, '19.94', '2.793', 'ON', NULL, '2025-08-21 11:45:52'),
(193, '31.87', '0.569', 'ON', NULL, '2025-08-20 22:53:52'),
(194, '5.96', '1.841', 'ON', NULL, '2025-08-20 17:27:52'),
(195, '9.12', '0.415', 'ON', NULL, '2025-08-21 00:03:52'),
(196, '43.03', '1.141', 'ON', NULL, '2025-08-20 18:17:52'),
(197, '40.33', '1.536', 'ON', NULL, '2025-08-21 06:11:52'),
(198, '27.36', '0.902', 'ON', NULL, '2025-08-20 18:09:52'),
(199, '35.69', '2.559', 'ON', NULL, '2025-08-21 01:08:52'),
(200, '24.48', '1.152', 'ON', NULL, '2025-08-20 22:09:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('2Yt4VEsh8e6qCHSEZssqnnisTzxOlUwQajNV6w7w', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiT2F6aHNNOVhhVHo0QW0ySmlhZmFWd25idFJta2pUY0FCN2puYXNvOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC92b2x1bWVhaXIiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1755790602),
('baSMgpR0iQ2bUEi5exltrL3Xr18zJd3VwwelDUiJ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieDB5ekZqRnU4ZFNzUTN3UEtFUlpTOXhnYm1LaXQ5Y21xdktxS3pxNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC92b2x1bWVhaXIiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1755792359);

-- --------------------------------------------------------

--
-- Struktur dari tabel `shalat_water_reports`
--

CREATE TABLE `shalat_water_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prayer_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_volume` decimal(8,3) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `shalat_water_reports`
--

INSERT INTO `shalat_water_reports` (`id`, `prayer_name`, `total_volume`, `date`, `start_time`, `end_time`, `timestamp`) VALUES
(1, 'Subuh', '3.330', '2025-08-21', '03:30:00', '05:30:00', '2025-08-20 20:30:00'),
(2, 'Dzuhur', '3.385', '2025-08-21', '12:00:00', '14:00:00', '2025-08-21 05:00:00'),
(3, 'Ashar', '3.713', '2025-08-21', '16:30:00', '18:30:00', '2025-08-21 09:30:00'),
(4, 'Maghrib', '3.955', '2025-08-21', '18:30:00', '19:00:00', '2025-08-21 11:30:00'),
(5, 'Isya', '0.606', '2025-08-21', '19:00:00', '21:00:00', '2025-08-21 12:00:00'),
(6, 'Subuh', '0.053', '2025-08-20', '03:30:00', '05:30:00', '2025-08-19 20:30:00'),
(7, 'Dzuhur', '3.125', '2025-08-20', '12:00:00', '14:00:00', '2025-08-20 05:00:00'),
(8, 'Ashar', '0.296', '2025-08-20', '16:30:00', '18:30:00', '2025-08-20 09:30:00'),
(9, 'Maghrib', '1.172', '2025-08-20', '18:30:00', '19:00:00', '2025-08-20 11:30:00'),
(10, 'Isya', '3.738', '2025-08-20', '19:00:00', '21:00:00', '2025-08-20 12:00:00'),
(11, 'Subuh', '3.386', '2025-08-19', '03:30:00', '05:30:00', '2025-08-18 20:30:00'),
(12, 'Dzuhur', '1.575', '2025-08-19', '12:00:00', '14:00:00', '2025-08-19 05:00:00'),
(13, 'Ashar', '1.749', '2025-08-19', '16:30:00', '18:30:00', '2025-08-19 09:30:00'),
(14, 'Maghrib', '1.554', '2025-08-19', '18:30:00', '19:00:00', '2025-08-19 11:30:00'),
(15, 'Isya', '2.342', '2025-08-19', '19:00:00', '21:00:00', '2025-08-19 12:00:00'),
(16, 'Subuh', '0.117', '2025-08-18', '03:30:00', '05:30:00', '2025-08-17 20:30:00'),
(17, 'Dzuhur', '1.109', '2025-08-18', '12:00:00', '14:00:00', '2025-08-18 05:00:00'),
(18, 'Ashar', '2.182', '2025-08-18', '16:30:00', '18:30:00', '2025-08-18 09:30:00'),
(19, 'Maghrib', '1.807', '2025-08-18', '18:30:00', '19:00:00', '2025-08-18 11:30:00'),
(20, 'Isya', '1.238', '2025-08-18', '19:00:00', '21:00:00', '2025-08-18 12:00:00'),
(21, 'Subuh', '0.106', '2025-08-17', '03:30:00', '05:30:00', '2025-08-16 20:30:00'),
(22, 'Dzuhur', '1.083', '2025-08-17', '12:00:00', '14:00:00', '2025-08-17 05:00:00'),
(23, 'Ashar', '3.577', '2025-08-17', '16:30:00', '18:30:00', '2025-08-17 09:30:00'),
(24, 'Maghrib', '0.045', '2025-08-17', '18:30:00', '19:00:00', '2025-08-17 11:30:00'),
(25, 'Isya', '1.493', '2025-08-17', '19:00:00', '21:00:00', '2025-08-17 12:00:00'),
(26, 'Subuh', '0.642', '2025-08-16', '03:30:00', '05:30:00', '2025-08-15 20:30:00'),
(27, 'Dzuhur', '3.119', '2025-08-16', '12:00:00', '14:00:00', '2025-08-16 05:00:00'),
(28, 'Ashar', '0.746', '2025-08-16', '16:30:00', '18:30:00', '2025-08-16 09:30:00'),
(29, 'Maghrib', '2.219', '2025-08-16', '18:30:00', '19:00:00', '2025-08-16 11:30:00'),
(30, 'Isya', '3.722', '2025-08-16', '19:00:00', '21:00:00', '2025-08-16 12:00:00'),
(31, 'Subuh', '0.322', '2025-08-15', '03:30:00', '05:30:00', '2025-08-14 20:30:00'),
(32, 'Dzuhur', '3.581', '2025-08-15', '12:00:00', '14:00:00', '2025-08-15 05:00:00'),
(33, 'Ashar', '1.616', '2025-08-15', '16:30:00', '18:30:00', '2025-08-15 09:30:00'),
(34, 'Maghrib', '2.878', '2025-08-15', '18:30:00', '19:00:00', '2025-08-15 11:30:00'),
(35, 'Isya', '2.992', '2025-08-15', '19:00:00', '21:00:00', '2025-08-15 12:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `weekly_water_view`
--

CREATE TABLE `weekly_water_view` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `week_start` date NOT NULL,
  `week_end` date NOT NULL,
  `total_volume` decimal(12,3) NOT NULL DEFAULT 0.000,
  `daily_average` decimal(8,3) NOT NULL DEFAULT 0.000,
  `subuh_total` decimal(8,3) NOT NULL DEFAULT 0.000,
  `dzuhur_total` decimal(8,3) NOT NULL DEFAULT 0.000,
  `ashar_total` decimal(8,3) NOT NULL DEFAULT 0.000,
  `maghrib_total` decimal(8,3) NOT NULL DEFAULT 0.000,
  `isya_total` decimal(8,3) NOT NULL DEFAULT 0.000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `weekly_water_view`
--

INSERT INTO `weekly_water_view` (`id`, `week_start`, `week_end`, `total_volume`, `daily_average`, `subuh_total`, `dzuhur_total`, `ashar_total`, `maghrib_total`, `isya_total`, `created_at`, `updated_at`) VALUES
(1, '2025-08-18', '2025-08-24', '40.432', '5.776', '6.886', '9.194', '7.940', '8.488', '7.924', '2025-08-21 08:31:53', '2025-08-21 08:31:53'),
(2, '2025-08-11', '2025-08-17', '28.141', '4.020', '1.070', '7.783', '5.939', '5.142', '8.207', '2025-08-21 08:31:53', '2025-08-21 08:31:53'),
(3, '2025-08-04', '2025-08-10', '0.000', '0.000', '0.000', '0.000', '0.000', '0.000', '0.000', '2025-08-21 08:31:53', '2025-08-21 08:31:53'),
(4, '2025-07-28', '2025-08-03', '0.000', '0.000', '0.000', '0.000', '0.000', '0.000', '0.000', '2025-08-21 08:31:53', '2025-08-21 08:31:53');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `daily_water_summary`
--
ALTER TABLE `daily_water_summary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `daily_water_summary_date_unique` (`date`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sensor_data_timestamp_index` (`timestamp`),
  ADD KEY `sensor_data_status_index` (`status`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `shalat_water_reports`
--
ALTER TABLE `shalat_water_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shalat_water_reports_date_prayer_name_index` (`date`,`prayer_name`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indeks untuk tabel `weekly_water_view`
--
ALTER TABLE `weekly_water_view`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `weekly_water_view_week_start_unique` (`week_start`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `daily_water_summary`
--
ALTER TABLE `daily_water_summary`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT untuk tabel `shalat_water_reports`
--
ALTER TABLE `shalat_water_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `weekly_water_view`
--
ALTER TABLE `weekly_water_view`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
