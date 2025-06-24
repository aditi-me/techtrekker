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
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` char(36) NOT NULL DEFAULT uuid(),
  `stream_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `total_duration` varchar(50) NOT NULL,
  `buyer_name` varchar(255) NOT NULL,
  `buyer_email` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `receipt_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `stream_id`, `course_id`, `course_name`, `total_duration`, `buyer_name`, `buyer_email`, `phone_number`, `amount_paid`, `enrolled_at`, `receipt_path`) VALUES
('6847d19bbd0a7', 1, 101, 'Data Structures & Algorithms in Python', 'Approx. 4 Months', 'Student Das', 'student@gmail.com', '9876543456', 7500.00, '2025-06-10 03:02:59', 'receipts/receipt_6847d19bbd0a7.html'),
('6847d3d6761ca', 4, 403, 'Bioprocess Engineering Principles', 'Approx. 4 Months', 'Student Das', 'student@gmail.com', '9876543456', 7800.00, '2025-06-10 03:12:30', 'receipts/receipt_6847d3d6761ca.html'),
('6847d4d04e18e', 4, 403, 'Bioprocess Engineering Principles', 'Approx. 4 Months', 'admin', 'admin@gmail.com', NULL, 7800.00, '2025-06-10 03:16:40', 'receipts/receipt_6847d4d04e18e.html'),
('6847d7cd63f8d', 4, 403, 'Bioprocess Engineering Principles', 'Approx. 4 Months', 'admin', 'admin@gmail.com', NULL, 7800.00, '2025-06-10 03:29:25', 'receipts/receipt_6847d7cd63f8d.html'),
('68570c08a7368', 1, 102, 'Operating Systems Fundamentals', 'Designed for 35 Classes', 'Manirika Karmakar', 'mk@gmail.com', '9876545678', 6000.00, '2025-06-21 16:16:16', 'receipts/receipt_68570c08a7368.html');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `idx_enroll_buyer_email` (`buyer_email`),
  ADD KEY `idx_enroll_course_id` (`course_id`),
  ADD KEY `idx_enroll_stream_id` (`stream_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enroll_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enroll_stream` FOREIGN KEY (`stream_id`) REFERENCES `streams` (`stream_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
