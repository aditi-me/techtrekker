-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jun 24, 2025 at 06:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `techtrekker`
--

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `last_qualification` varchar(100) DEFAULT NULL,
  `last_percentage` decimal(5,2) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `first_name`, `surname`, `email`, `phone_number`, `last_qualification`, `last_percentage`, `password_hash`, `registration_date`, `deleted_at`) VALUES
(4, 'Rahul', 'Raj', 'rahul@raj.com', '9876543234', 'Btech in Biotechnology', 82.00, '$2y$10$O1yBQs7jPztA1T6mAkocCOR4oWatwkQNFHKMS5Qxn1EyfFo7Kf/RC', '2025-06-04 14:59:53', '2025-06-21 22:19:21'),
(5, 'Aditi', 'Biswas', 'aditibiswas513@gmail.com', '9876543234', 'Btech in Biotechnology', 82.00, '$2y$10$uercVnxqvPhTjcBjgYa9LOI8ceNjcq4y0TSI2pNGqAjRritk333Pm', '2025-06-04 15:02:40', '2025-06-21 22:19:19'),
(7, 'Rohan', 'Arora', 'rohan@a.com', '9876543234', 'Btech in Biotechnology', 82.00, '$2y$10$ohkR.YuquPBY1UBZAwBOyOrEDY6LIO/OsPNG/VJmMxQXfF7rqYJpa', '2025-06-04 15:05:34', NULL),
(9, 'Student', 'Das', 'student@gmail.com', '9876543456', 'Btech', 82.67, '$2y$10$JwsEvLKE/GXrDM7L5Rq4lOx9XuxGXfhWSL5kpPAt7d0elU2p1D6F.', '2025-06-04 19:31:01', NULL),
(16, 'Manirika', 'Karmakar', 'mk@gmail.com', '9876545678', 'btech in ece', 89.67, '$2y$10$tYRFF/YTYUxNw26yhKWFIuz.CrA1L8m9zGt2z8hwQbSkHHUB79OXO', '2025-06-21 19:44:44', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_student_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
