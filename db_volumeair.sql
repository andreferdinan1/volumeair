-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 09 Agu 2025 pada 10.28
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
-- Struktur dari tabel `daily_water_summary`
--

CREATE TABLE `daily_water_summary` (
  `id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `subuh_water` decimal(15,3) DEFAULT 0.000 COMMENT 'Air digunakan waktu Subuh (04:00-06:00)',
  `dzuhur_water` decimal(15,3) DEFAULT 0.000 COMMENT 'Air digunakan waktu Dzuhur (12:00-14:00)',
  `ashar_water` decimal(15,3) DEFAULT 0.000 COMMENT 'Air digunakan waktu Ashar (15:30-17:30)',
  `maghrib_water` decimal(15,3) DEFAULT 0.000 COMMENT 'Air digunakan waktu Maghrib (18:00-20:00)',
  `isya_water` decimal(15,3) DEFAULT 0.000 COMMENT 'Air digunakan waktu Isya (19:00-21:00)',
  `total_daily_water` decimal(15,3) DEFAULT 0.000 COMMENT 'Total air harian dari 5 waktu shalat',
  `non_shalat_water` decimal(15,3) DEFAULT 0.000 COMMENT 'Air digunakan di luar jadwal shalat',
  `grand_total_water` decimal(15,3) DEFAULT 0.000 COMMENT 'Total air harian keseluruhan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `daily_water_summary`
--

INSERT INTO `daily_water_summary` (`id`, `report_date`, `subuh_water`, `dzuhur_water`, `ashar_water`, `maghrib_water`, `isya_water`, `total_daily_water`, `non_shalat_water`, `grand_total_water`, `created_at`, `updated_at`) VALUES
(1, '2025-08-07', '0.000', '0.000', '0.000', '0.000', '0.000', '0.000', '0.000', '0.000', '2025-08-07 15:46:25', '2025-08-07 15:46:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sensor_data`
--

CREATE TABLE `sensor_data` (
  `id` int(11) NOT NULL,
  `jarak_cm` decimal(10,2) NOT NULL COMMENT 'Jarak dalam centimeter',
  `flow_rate` decimal(10,2) NOT NULL COMMENT 'Flow rate dalam L/min',
  `status_kran` varchar(20) NOT NULL COMMENT 'Status kran: ON/OFF/STANDBY',
  `total_volume` decimal(15,3) NOT NULL DEFAULT 0.000 COMMENT 'Total volume keseluruhan dalam liter',
  `session_volume` decimal(15,3) NOT NULL DEFAULT 0.000 COMMENT 'Volume dalam sesi saat ini',
  `current_shalat` varchar(20) DEFAULT NULL COMMENT 'Jadwal shalat aktif saat ini',
  `shalat_water` decimal(15,3) DEFAULT 0.000 COMMENT 'Air yang digunakan dalam jadwal shalat aktif',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `shalat_water_reports`
--

CREATE TABLE `shalat_water_reports` (
  `id` int(11) NOT NULL,
  `waktu_shalat` varchar(20) NOT NULL COMMENT 'Nama waktu shalat: Subuh/Dzuhur/Ashar/Maghrib/Isya',
  `start_time` time NOT NULL COMMENT 'Waktu mulai jadwal shalat',
  `end_time` time NOT NULL COMMENT 'Waktu selesai jadwal shalat',
  `total_water` decimal(15,3) NOT NULL DEFAULT 0.000 COMMENT 'Total air digunakan dalam periode ini (liter)',
  `usage_count` int(11) DEFAULT 0 COMMENT 'Jumlah kali penggunaan dalam periode ini',
  `report_date` date NOT NULL COMMENT 'Tanggal laporan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Trigger `shalat_water_reports`
--
DELIMITER $$
CREATE TRIGGER `update_daily_summary_after_shalat_report` AFTER INSERT ON `shalat_water_reports` FOR EACH ROW BEGIN
    INSERT INTO daily_water_summary (
        report_date,
        subuh_water,
        dzuhur_water,
        ashar_water,
        maghrib_water,
        isya_water
    ) VALUES (
        NEW.report_date,
        CASE WHEN NEW.shalat_name = 'Subuh' THEN NEW.total_water ELSE 0 END,
        CASE WHEN NEW.shalat_name = 'Dzuhur' THEN NEW.total_water ELSE 0 END,
        CASE WHEN NEW.shalat_name = 'Ashar' THEN NEW.total_water ELSE 0 END,
        CASE WHEN NEW.shalat_name = 'Maghrib' THEN NEW.total_water ELSE 0 END,
        CASE WHEN NEW.shalat_name = 'Isya' THEN NEW.total_water ELSE 0 END
    ) ON DUPLICATE KEY UPDATE
        subuh_water = CASE WHEN NEW.shalat_name = 'Subuh' THEN NEW.total_water ELSE subuh_water END,
        dzuhur_water = CASE WHEN NEW.shalat_name = 'Dzuhur' THEN NEW.total_water ELSE dzuhur_water END,
        ashar_water = CASE WHEN NEW.shalat_name = 'Ashar' THEN NEW.total_water ELSE ashar_water END,
        maghrib_water = CASE WHEN NEW.shalat_name = 'Maghrib' THEN NEW.total_water ELSE maghrib_water END,
        isya_water = CASE WHEN NEW.shalat_name = 'Isya' THEN NEW.total_water ELSE isya_water END,
        total_daily_water = subuh_water + dzuhur_water + ashar_water + maghrib_water + isya_water,
        updated_at = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `weekly_water_view`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `weekly_water_view` (
`year_week` int(6)
,`week_start` date
,`week_end` date
,`subuh_avg` decimal(19,7)
,`dzuhur_avg` decimal(19,7)
,`ashar_avg` decimal(19,7)
,`maghrib_avg` decimal(19,7)
,`isya_avg` decimal(19,7)
,`weekly_avg` decimal(19,7)
,`weekly_total` decimal(37,3)
,`days_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `weekly_water_view`
--
DROP TABLE IF EXISTS `weekly_water_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`` SQL SECURITY DEFINER VIEW `weekly_water_view`  AS SELECT yearweek(`daily_water_summary`.`report_date`,1) AS `year_week`, `daily_water_summary`.`report_date`- interval weekday(`daily_water_summary`.`report_date`) day AS `week_start`, `daily_water_summary`.`report_date`- interval weekday(`daily_water_summary`.`report_date`) day + interval 6 day AS `week_end`, avg(`daily_water_summary`.`subuh_water`) AS `subuh_avg`, avg(`daily_water_summary`.`dzuhur_water`) AS `dzuhur_avg`, avg(`daily_water_summary`.`ashar_water`) AS `ashar_avg`, avg(`daily_water_summary`.`maghrib_water`) AS `maghrib_avg`, avg(`daily_water_summary`.`isya_water`) AS `isya_avg`, avg(`daily_water_summary`.`total_daily_water`) AS `weekly_avg`, sum(`daily_water_summary`.`total_daily_water`) AS `weekly_total`, count(0) AS `days_count` FROM `daily_water_summary` GROUP BY yearweek(`daily_water_summary`.`report_date`,1) ORDER BY yearweek(`daily_water_summary`.`report_date`,1) AS `DESCdesc` ASC  ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `daily_water_summary`
--
ALTER TABLE `daily_water_summary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_date` (`report_date`),
  ADD KEY `idx_report_date` (`report_date`);

--
-- Indeks untuk tabel `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `shalat_water_reports`
--
ALTER TABLE `shalat_water_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shalat_date` (`waktu_shalat`,`report_date`),
  ADD KEY `idx_report_date` (`report_date`),
  ADD KEY `idx_shalat_name` (`waktu_shalat`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `daily_water_summary`
--
ALTER TABLE `daily_water_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `shalat_water_reports`
--
ALTER TABLE `shalat_water_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
