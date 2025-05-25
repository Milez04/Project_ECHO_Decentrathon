-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 25 May 2025, 12:55:43
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `utm_db`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `drones`
--

CREATE TABLE `drones` (
  `id` int(11) NOT NULL,
  `model` varchar(255) DEFAULT NULL,
  `serial` varchar(255) DEFAULT NULL,
  `pilot_name` varchar(255) DEFAULT NULL,
  `pilot_contact` varchar(255) DEFAULT NULL,
  `current_base` varchar(255) NOT NULL,
  `battery` double DEFAULT 100,
  `flight_count` int(11) DEFAULT 0,
  `maintenance_needed` tinyint(1) DEFAULT 0,
  `last_maintenance` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `drones`
--

INSERT INTO `drones` (`id`, `model`, `serial`, `pilot_name`, `pilot_contact`, `current_base`, `battery`, `flight_count`, `maintenance_needed`, `last_maintenance`) VALUES
(2, 'deneme', '2525', 'ggg', '2525', 'EXPO', 100, 0, 0, NULL),
(3, 'fdgbff', 'dxv', 'fdxf', 'xdfv', 'Khan Shatyr', 100, 0, 0, NULL),
(4, 'ff', 'ff22', 'ggg', '2525', 'EXPO', 100, 0, 0, NULL),
(5, 'jj', 'jj22', 'jjj', '2525', 'EXPO', 100, 0, 0, NULL),
(6, 'lk', 'lk25', 'kkk', '2525', 'Арнайы Орын', 100, 0, 0, NULL),
(7, 'ppp', 'pp', 'pp', '2552', 'Khan Shatyr', 100, 0, 0, NULL),
(27, 'dd', 'gg25', 'ff', '2525', 'EXPO', 76.12584981595253, 0, 0, NULL),
(30, 'тест', 'тт25', 'Миләз', '2525', 'EXPO', 100, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `flight_history`
--

CREATE TABLE `flight_history` (
  `id` int(11) NOT NULL,
  `drone_id` int(11) DEFAULT NULL,
  `start_base` varchar(255) DEFAULT NULL,
  `end_base` varchar(255) DEFAULT NULL,
  `distance` float DEFAULT NULL,
  `duration` float DEFAULT NULL,
  `altitude` float DEFAULT NULL,
  `start_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `flight_history`
--

INSERT INTO `flight_history` (`id`, `drone_id`, `start_base`, `end_base`, `distance`, `duration`, `altitude`, `start_time`) VALUES
(1, 3, 'Khan Shatyr', 'Nurzhol', 2.02673, 6.08019, 229.468, '2025-05-23 15:02:39'),
(2, 2, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 366.722, '2025-05-23 15:03:18'),
(3, 2, 'EXPO', 'Bayterek', 4.96018, 14.8805, 248.716, '2025-05-24 09:58:45'),
(4, 2, 'Bayterek', 'EXPO', 4.96018, 14.8805, 174.318, '2025-05-24 11:30:41'),
(5, 2, 'Bayterek', 'EXPO', 4.96018, 14.8805, 159.277, '2025-05-24 11:43:32'),
(6, 3, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 230.03, '2025-05-24 11:43:40'),
(7, 4, 'EXPO', 'Nurzhol', 4.55417, 13.6625, 140.203, '2025-05-24 11:45:48'),
(8, 3, 'EXPO', 'Nurzhol', 4.55417, 13.6625, 392.176, '2025-05-24 11:52:48'),
(9, 2, 'EXPO', 'Nurzhol', 4.55417, 13.6625, 138.841, '2025-05-24 11:53:43'),
(10, 4, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 388.94, '2025-05-24 11:54:33'),
(11, 4, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 193.017, '2025-05-24 11:56:47'),
(12, 5, 'Bayterek', 'EXPO', 4.96018, 14.8805, 266.787, '2025-05-24 11:56:53'),
(13, 4, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 313.56, '2025-05-24 11:57:26'),
(14, 4, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 344.309, '2025-05-24 11:57:51'),
(15, 5, 'Bayterek', 'EXPO', 4.96018, 14.8805, 374.315, '2025-05-24 11:58:30'),
(16, 5, 'Bayterek', 'EXPO', 4.96018, 14.8805, 199.821, '2025-05-24 12:01:20'),
(17, 4, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 301.406, '2025-05-24 12:01:40'),
(18, 4, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 119.163, '2025-05-24 12:01:59'),
(19, 4, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 304.309, '2025-05-24 12:03:02'),
(20, 5, 'Bayterek', 'EXPO', 4.96018, 14.8805, 183.203, '2025-05-24 12:03:09'),
(21, 2, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 262.785, '2025-05-24 12:04:51'),
(22, 6, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 150, '2025-05-24 13:00:13'),
(23, 6, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 150, '2025-05-24 13:01:06'),
(24, 6, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 230, '2025-05-24 13:08:41'),
(25, 3, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-24 13:09:57'),
(26, 7, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 150, '2025-05-24 13:11:12'),
(27, 2, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-24 13:18:25'),
(28, 3, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-24 13:18:46'),
(29, 6, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 230, '2025-05-24 13:19:27'),
(30, 2, 'Khan Shatyr', 'Nurzhol', 2.02673, 6.08019, 150, '2025-05-24 13:22:00'),
(31, 3, 'Khan Shatyr', 'Nurzhol', 2.02673, 6.08019, 150, '2025-05-24 13:33:51'),
(32, 2, 'Khan Shatyr', 'Nurzhol', 2.02673, 6.08019, 150, '2025-05-24 13:57:46'),
(33, 2, 'Khan Shatyr', 'Nurzhol', 2.02673, 0, 150, '2025-05-24 14:07:27'),
(34, 27, 'Nurzhol', 'EXPO', 4.55417, 0, 230, '2025-05-24 14:08:58'),
(35, 2, 'Nurzhol', 'Bayterek', 1.06775, 0, 150, '2025-05-24 14:13:02'),
(36, 2, 'Nurzhol', 'Bayterek', 1.06775, 3.20325, 150, '2025-05-24 14:20:49'),
(37, 2, 'Bayterek', 'EXPO', 4.96018, 14.8805, 230, '2025-05-24 16:45:21'),
(38, 2, 'Bayterek', 'EXPO', 4.96018, 14.8805, 230, '2025-05-25 08:26:42'),
(39, 3, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 150, '2025-05-25 09:11:21'),
(40, 3, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 150, '2025-05-25 09:21:28'),
(41, 3, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 250, '2025-05-25 09:21:31'),
(42, 3, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 150, '2025-05-25 09:22:39'),
(43, 3, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 250, '2025-05-25 09:22:43'),
(44, 3, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 150, '2025-05-25 09:23:05'),
(45, 4, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-25 09:23:31'),
(46, 3, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 150, '2025-05-25 09:24:21'),
(47, 4, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-25 09:24:28'),
(48, 5, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-25 09:24:58'),
(49, 6, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-25 09:25:05'),
(50, 3, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 150, '2025-05-25 09:28:46'),
(51, 4, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-25 09:29:07'),
(52, 3, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 150, '2025-05-25 09:38:16'),
(53, 4, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-25 09:38:26'),
(54, 27, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 230, '2025-05-25 09:40:09'),
(55, 4, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 150, '2025-05-25 09:51:28'),
(56, 3, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-25 09:51:56'),
(57, 2, 'Bayterek', 'EXPO', 4.96018, 14.8805, 230, '2025-05-25 09:52:40'),
(58, 2, 'Bayterek', 'EXPO', 4.96018, 14.8805, 230, '2025-05-25 09:53:17'),
(59, 4, 'Khan Shatyr', 'Özel Konum', 2.75111, 8.25332, 150, '2025-05-25 11:14:38'),
(60, 3, 'EXPO', 'Özel Konum', 4.966, 14.898, 150, '2025-05-25 11:15:09'),
(61, 5, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-25 11:15:28'),
(62, 7, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 150, '2025-05-25 11:15:49'),
(63, 2, 'Bayterek', 'EXPO', 4.96018, 14.8805, 230, '2025-05-25 11:16:25'),
(64, 4, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 150, '2025-05-25 11:17:51'),
(65, 6, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 250, '2025-05-25 11:17:58'),
(66, 5, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-25 11:18:32'),
(67, 4, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 150, '2025-05-25 11:18:44'),
(68, 2, 'Bayterek', 'EXPO', 4.96018, 14.8805, 230, '2025-05-25 11:19:16'),
(69, 2, 'Bayterek', 'EXPO', 4.96018, 14.8805, 230, '2025-05-25 11:27:20'),
(70, 5, 'EXPO', 'Арнайы Орын', 4.54332, 13.63, 150, '2025-05-25 11:29:16'),
(71, 30, 'Nurzhol', 'EXPO', 4.55417, 13.6625, 230, '2025-05-25 11:44:18'),
(72, 2, 'Bayterek', 'EXPO', 4.96018, 14.8805, 230, '2025-05-25 11:45:13'),
(73, 4, 'Khan Shatyr', 'EXPO', 5.23019, 15.6906, 150, '2025-05-25 11:45:56'),
(74, 3, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-25 11:46:16'),
(75, 6, 'EXPO', 'Арнайы Орын', 3.44514, 10.3354, 150, '2025-05-25 11:46:49'),
(76, 5, 'EXPO', 'Nurzhol', 4.55417, 13.6625, 230, '2025-05-25 11:49:43'),
(77, 5, 'EXPO', 'Khan Shatyr', 5.23019, 15.6906, 150, '2025-05-25 11:49:48');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `drones`
--
ALTER TABLE `drones`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `flight_history`
--
ALTER TABLE `flight_history`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `drones`
--
ALTER TABLE `drones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Tablo için AUTO_INCREMENT değeri `flight_history`
--
ALTER TABLE `flight_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
