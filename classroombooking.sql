-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2025 at 12:30 AM
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
-- Database: `classroombooking`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booked_item_id` int(11) NOT NULL,
  `time_slot_id` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classroom`
--

CREATE TABLE `classroom` (
  `id` int(11) NOT NULL,
  `seat_capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `classroom`
--

INSERT INTO `classroom` (`id`, `seat_capacity`) VALUES
(1, 30),
(2, 30),
(3, 30),
(4, 30),
(5, 30),
(6, 30),
(7, 30),
(8, 30),
(9, 30),
(10, 30),
(11, 30),
(12, 30),
(13, 30),
(14, 30),
(15, 30),
(16, 30),
(17, 30),
(18, 30),
(19, 30),
(20, 30),
(21, 40),
(22, 40),
(23, 40),
(24, 40),
(25, 40),
(26, 40),
(27, 40),
(28, 40),
(29, 40),
(30, 40),
(31, 40),
(32, 40),
(33, 40),
(34, 40),
(35, 40),
(36, 40),
(37, 40),
(38, 40);

-- --------------------------------------------------------

--
-- Table structure for table `classroombookings`
--

CREATE TABLE `classroombookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booked_item_id` int(11) NOT NULL,
  `time_slot_id` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seats`
--

CREATE TABLE `seats` (
  `id` int(11) NOT NULL,
  `room_number` int(11) NOT NULL,
  `seat_number` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `seats`
--

INSERT INTO `seats` (`id`, `room_number`, `seat_number`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 1, 6),
(7, 1, 7),
(8, 1, 8),
(9, 1, 9),
(10, 1, 10),
(11, 1, 11),
(12, 1, 12),
(13, 1, 13),
(14, 1, 14),
(15, 1, 15),
(16, 1, 16),
(17, 1, 17),
(18, 1, 18),
(19, 1, 19),
(20, 1, 20),
(21, 1, 21),
(22, 1, 22),
(24, 2, 1),
(25, 2, 2),
(26, 2, 3),
(27, 2, 4),
(28, 2, 5),
(29, 2, 6),
(30, 2, 7),
(31, 2, 8),
(32, 2, 9),
(33, 2, 10),
(34, 2, 11),
(35, 2, 12),
(36, 2, 13),
(37, 2, 14),
(38, 2, 15),
(39, 2, 16),
(40, 2, 17),
(41, 2, 18),
(42, 2, 19),
(43, 2, 20),
(44, 2, 21);

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`id`, `start_time`, `end_time`) VALUES
(1, '08:00:00', '09:00:00'),
(2, '09:00:00', '10:00:00'),
(3, '10:00:00', '11:00:00'),
(4, '11:00:00', '12:00:00'),
(5, '12:00:00', '01:00:00'),
(6, '01:00:00', '02:00:00'),
(7, '02:00:00', '03:00:00'),
(8, '03:00:00', '04:00:00'),
(9, '04:00:00', '05:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `secret_key` varchar(256) NOT NULL,
  `2FA_enabled` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booked_item_id` (`booked_item_id`),
  ADD KEY `time_slot_id` (`time_slot_id`);

--
-- Indexes for table `classroom`
--
ALTER TABLE `classroom`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classroombookings`
--
ALTER TABLE `classroombookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booked_item_id` (`booked_item_id`),
  ADD KEY `time_slot_id` (`time_slot_id`);

--
-- Indexes for table `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `classroom`
--
ALTER TABLE `classroom`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `classroombookings`
--
ALTER TABLE `classroombookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=453;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`booked_item_id`) REFERENCES `seats` (`id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`time_slot_id`) REFERENCES `time_slots` (`id`);

--
-- Constraints for table `classroombookings`
--
ALTER TABLE `classroombookings`
  ADD CONSTRAINT `classroombookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `classroombookings_ibfk_2` FOREIGN KEY (`booked_item_id`) REFERENCES `classroom` (`id`),
  ADD CONSTRAINT `classroombookings_ibfk_3` FOREIGN KEY (`time_slot_id`) REFERENCES `time_slots` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
