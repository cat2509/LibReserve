-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2025 at 09:14 PM
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
-- Database: `library_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `reset_token_hash` varchar(255) DEFAULT NULL,
  `t_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `email`, `password_hash`, `reset_token_hash`, `t_token_expires_at`) VALUES
(1, 'shaunak', 'shaunak.gite@somaiya.edu', '$2y$10$qcqBQmQiAsNKs/0XScLIc.M3BdCM.Eyfkt4RNefmXea5yGupfcwb2', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `table_number` int(11) NOT NULL,
  `chairs_used` int(11) NOT NULL CHECK (`chairs_used` >= 1 and `chairs_used` <= 4),
  `reservation_time` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'reserved',
  `duration` int(11) DEFAULT 60,
  `qr_scanned` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `student_id`, `table_number`, `chairs_used`, `reservation_time`, `status`, `duration`, `qr_scanned`) VALUES
(9, 'STUD10', 21, 4, '2025-04-08 14:00:00', 'expired', 60, 0),
(10, 'STUD9', 1, 4, '2025-04-08 15:30:00', 'cancelled', 60, 0),
(11, 'STUD9', 20, 4, '2025-04-08 15:30:00', 'expired', 60, 0),
(15, 'STUD9', 22, 3, '2025-04-08 17:00:00', 'expired', 60, 0),
(16, 'STUD101', 1, 4, '2025-04-09 13:00:00', 'expired', 60, 0),
(17, 'STUD101', 2, 4, '2025-04-09 13:00:00', 'expired', 60, 0),
(18, 'STUD9', 3, 2, '2025-04-09 14:00:00', 'cancelled', 60, 0),
(19, 'STUD9', 3, 2, '2025-04-09 15:00:00', 'cancelled', 60, 0),
(20, 'STUD9', 2, 2, '2025-04-09 15:00:00', 'expired', 60, 0),
(22, 'STUD10', 11, 3, '2025-04-09 14:28:00', 'cancelled', 60, 0),
(23, 'STUD10', 1, 2, '2025-04-09 15:21:00', 'expired', 60, 0),
(24, 'STUD9', 1, 4, '2025-04-09 08:00:00', 'expired', 60, 0),
(25, 'STUD9', 1, 4, '2025-04-09 10:00:00', 'expired', 60, 0),
(26, 'STUD9', 1, 4, '2025-04-09 10:00:00', 'expired', 60, 0),
(27, 'STUD9', 3, 3, '2025-04-09 09:20:00', 'expired', 60, 0),
(28, 'STUD9', 17, 3, '2025-04-09 09:20:00', 'expired', 60, 0),
(29, 'STUD9', 1, 4, '2025-04-10 11:00:00', 'expired', 60, 0),
(30, 'STUD9', 2, 4, '2025-04-10 10:00:00', 'expired', 60, 0),
(31, 'STUD9', 1, 1, '2025-04-10 12:30:00', 'cancelled', 60, 0),
(32, 'STUD9', 1, 4, '2025-04-10 12:30:00', 'cancelled', 60, 0),
(33, 'STUD9', 2, 4, '2025-04-10 12:33:00', 'cancelled', 60, 0),
(34, 'STUD9', 3, 4, '2025-04-10 12:50:00', 'expired', 60, 0),
(35, 'STUD9', 1, 4, '2025-04-10 13:00:00', 'cancelled', 60, 0),
(36, 'STUD10', 22, 3, '2025-04-10 16:22:00', 'cancelled', 60, 0),
(37, 'STUD9', 1, 4, '2025-04-10 13:45:00', 'cancelled', 60, 0),
(38, 'STUD10', 2, 3, '2025-04-10 15:36:00', 'expired', 60, 0),
(39, 'STUD9', 3, 2, '2025-04-10 16:00:00', 'expired', 60, 0),
(40, 'STUD9', 1, 2, '2025-04-10 17:30:00', 'cancelled', 60, 0),
(41, 'STUD9', 1, 4, '2025-04-10 18:15:00', 'cancelled', 60, 0),
(42, 'STUD10', 1, 2, '2025-04-10 18:33:00', 'cancelled', 60, 0),
(48, 'STUD9', 1, 4, '2025-04-11 08:05:00', 'cancelled', 60, 0);

-- --------------------------------------------------------

--
-- Table structure for table `seat_reservation`
--

CREATE TABLE `seat_reservation` (
  `id` int(11) NOT NULL,
  `student_id` varchar(10) NOT NULL,
  `table_number` varchar(10) NOT NULL,
  `chairs` int(11) NOT NULL,
  `status` enum('reserved','available') DEFAULT 'reserved',
  `reserved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seat_reservation`
--

INSERT INTO `seat_reservation` (`id`, `student_id`, `table_number`, `chairs`, `status`, `reserved_at`) VALUES
(3, 'S103', '21', 4, 'reserved', '2025-03-23 10:57:24'),
(4, 'S101', '3', 2, 'reserved', '2025-04-02 10:09:04');

--
-- Triggers `seat_reservation`
--
DELIMITER $$
CREATE TRIGGER `before_insert_seat_reservation` BEFORE INSERT ON `seat_reservation` FOR EACH ROW BEGIN
    DECLARE min_id INT;

    -- Find the smallest missing ID
    SELECT MIN(t1.id + 1) INTO min_id
    FROM seat_reservation t1
    LEFT JOIN seat_reservation t2 ON t1.id + 1 = t2.id
    WHERE t2.id IS NULL;

    -- If a missing ID exists, assign it
    IF min_id IS NOT NULL THEN
        SET NEW.id = min_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `t_token_expires_at` datetime DEFAULT NULL,
  `student_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `email`, `password_hash`, `reset_token_hash`, `t_token_expires_at`, `student_id`) VALUES
(9, '', 'shaunak.gite@somaiya.edu', '$2y$10$1HZSWCWxfYe4vr9rW9x7o.NjN/DwOzmulo3roG2wAoSOkRC2xdvcO', NULL, NULL, 'STUD9'),
(10, '', 'sana.kadam@somaiya.edu', '$2y$10$r0LvztSW7038aULy/5A6kuz1mdN3mVG9i7lTuYLelmdp4PHFFuGM.', NULL, NULL, 'STUD10'),
(12, '', 'yash.arjugade@somaiya.edu', '$2y$10$YgxE0NqsYNKvTsGNh34T9udQZ6uoEX0oK7ifNR2JyKyr43dYZPcOC', NULL, NULL, 'STUD101');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_table_status_time` (`table_number`,`status`,`reservation_time`),
  ADD KEY `idx_reservation_status` (`status`),
  ADD KEY `idx_reservation_time` (`reservation_time`),
  ADD KEY `idx_student_reservations` (`student_id`,`status`);

--
-- Indexes for table `seat_reservation`
--
ALTER TABLE `seat_reservation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `table_number` (`table_number`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `reset_token_hash` (`reset_token_hash`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `seat_reservation`
--
ALTER TABLE `seat_reservation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
