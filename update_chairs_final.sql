-- Drop the existing reservations table
DROP TABLE IF EXISTS reservations;

-- Create the updated reservations table
CREATE TABLE `reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(20) NOT NULL,
  `table_number` int(11) NOT NULL,
  `chairs_used` int(11) NOT NULL CHECK (chairs_used >= 1 AND chairs_used <= 4),
  `reservation_time` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'reserved',
  `duration` int(11) DEFAULT 60,
  `qr_scanned` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_table_status_time` (`table_number`, `status`, `reservation_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert the existing data with chairs renamed to chairs_used
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
(21, 'STUD9', 2, 1, '2025-04-09 20:00:00', 'expired', 60, 0),
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
(38, 'STUD10', 2, 3, '2025-04-10 15:36:00', 'reserved', 60, 0); 