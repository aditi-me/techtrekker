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
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `notice_id` char(36) NOT NULL DEFAULT uuid(),
  `teacher_id` varchar(20) DEFAULT NULL,
  `admin_id` varchar(20) DEFAULT NULL,
  `target_audience` varchar(50) NOT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `notice_content` text NOT NULL,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`notice_id`, `teacher_id`, `admin_id`, `target_audience`, `stream_id`, `course_id`, `notice_content`, `posted_at`) VALUES
('55b31ba8-4ee2-11f0-aef0-c03eba385aa1', NULL, 'admin_default_id', 'all', NULL, NULL, 'hi', '2025-06-21 20:57:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`notice_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `stream_id` (`stream_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notices`
--
ALTER TABLE `notices`
  ADD CONSTRAINT `notices_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `notices_ibfk_2` FOREIGN KEY (`stream_id`) REFERENCES `streams` (`stream_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `notices_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
