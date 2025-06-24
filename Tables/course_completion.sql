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
-- Table structure for table `course_completion`
--

CREATE TABLE `course_completion` (
  `completion_id` char(36) NOT NULL DEFAULT uuid(),
  `enrollment_id` char(36) DEFAULT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `teacher_id` varchar(20) NOT NULL,
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `course_completion`
--
ALTER TABLE `course_completion`
  ADD PRIMARY KEY (`completion_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `stream_id` (`stream_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course_completion`
--
ALTER TABLE `course_completion`
  ADD CONSTRAINT `course_completion_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_completion_ibfk_2` FOREIGN KEY (`stream_id`) REFERENCES `streams` (`stream_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_completion_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
