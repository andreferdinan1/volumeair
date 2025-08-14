-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 14 Agu 2025 pada 17.31
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
-- Database: `volumeair_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `sensor_data`
--

CREATE TABLE `sensor_data` (
  `id` int(11) NOT NULL,
  `jarak_cm` decimal(10,2) NOT NULL,
  `flow_rate` decimal(10,2) NOT NULL,
  `status_kran` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_volume` decimal(15,3) NOT NULL,
  `session_volume` decimal(10,3) NOT NULL,
  `current_shalat` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shalat_water` decimal(10,3) NOT NULL,
  `timestamp` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sensor_data`
--

INSERT INTO `sensor_data` (`id`, `jarak_cm`, `flow_rate`, `status_kran`, `total_volume`, `session_volume`, `current_shalat`, `shalat_water`, `timestamp`, `created_at`) VALUES
(1, '86.46', '2.93', 'OFF', '0.000', '0.000', 'Non-Shalat', '0.000', '2025-08-10 16:28:01', '2025-08-10 09:28:02'),
(2, '86.85', '0.00', 'OFF', '0.049', '0.000', 'Non-Shalat', '0.000', '2025-08-10 16:28:07', '2025-08-10 09:28:08'),
(3, '85.58', '0.00', 'OFF', '0.049', '0.000', 'Non-Shalat', '0.000', '2025-08-10 16:28:13', '2025-08-10 09:28:14'),
(4, '86.41', '0.00', 'OFF', '0.049', '0.000', 'Non-Shalat', '0.000', '2025-08-10 16:28:19', '2025-08-10 09:28:20'),
(5, '86.84', '0.00', 'OFF', '0.049', '0.000', 'Non-Shalat', '0.000', '2025-08-10 16:28:25', '2025-08-10 09:28:26'),
(6, '86.04', '0.00', 'OFF', '0.049', '0.000', 'Non-Shalat', '0.000', '2025-08-10 16:28:31', '2025-08-10 09:28:32'),
(7, '86.84', '0.00', 'OFF', '0.049', '0.000', 'Non-Shalat', '0.000', '2025-08-10 16:28:37', '2025-08-10 09:28:38'),
(8, '88.54', '0.00', 'OFF', '0.049', '0.000', 'Non-Shalat', '0.000', '2025-08-10 16:28:43', '2025-08-10 09:28:43'),
(9, '86.82', '0.00', 'OFF', '0.049', '0.000', 'Non-Shalat', '0.000', '2025-08-10 16:28:49', '2025-08-10 09:28:49'),
(10, '112.27', '0.00', 'OFF', '0.049', '0.000', 'Non-Shalat', '0.000', '2025-08-10 16:28:54', '2025-08-10 09:28:55'),
(11, '86.82', '0.00', 'OFF', '0.049', '0.000', 'Non-Shalat', '0.000', '2025-08-10 16:29:00', '2025-08-10 09:29:01'),
(12, '999.00', '0.13', 'OFF', '0.000', '0.000', 'Ashar', '0.000', '2025-08-10 16:35:08', '2025-08-10 09:35:10'),
(13, '999.00', '0.93', 'OFF', '0.051', '0.000', 'Ashar', '0.051', '2025-08-10 16:35:15', '2025-08-10 09:35:16'),
(14, '999.00', '0.93', 'OFF', '0.124', '0.000', 'Ashar', '0.124', '2025-08-10 16:35:21', '2025-08-10 09:35:23'),
(15, '999.00', '0.93', 'OFF', '0.198', '0.000', 'Ashar', '0.198', '2025-08-10 16:35:28', '2025-08-10 09:35:29'),
(16, '999.00', '0.93', 'OFF', '0.271', '0.000', 'Ashar', '0.271', '2025-08-10 16:35:34', '2025-08-10 09:35:36'),
(17, '999.00', '0.93', 'OFF', '0.344', '0.000', 'Ashar', '0.344', '2025-08-10 16:35:41', '2025-08-10 09:35:43'),
(18, '999.00', '0.93', 'OFF', '0.418', '0.000', 'Ashar', '0.418', '2025-08-10 16:35:48', '2025-08-10 09:35:52'),
(19, '999.00', '0.93', 'OFF', '0.491', '0.000', 'Ashar', '0.491', '2025-08-10 16:35:57', '2025-08-10 09:35:57'),
(20, '999.00', '0.93', 'OFF', '0.564', '0.000', 'Ashar', '0.564', '2025-08-10 16:36:03', '2025-08-10 09:36:04');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
