-- phpMyAdmin SQL Dump
-- version 4.0.10.12
-- http://www.phpmyadmin.net
--
-- Host: 127.11.218.130:3306
-- Generation Time: Jun 12, 2017 at 04:51 PM
-- Server version: 5.5.52
-- PHP Version: 5.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `EmotionClock`
--

-- --------------------------------------------------------

--
-- Table structure for table `UserGroup`
--

CREATE TABLE IF NOT EXISTS `UserGroup` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `group_id` bigint(20) DEFAULT NULL,
  `default_receiver_id` bigint(20) DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `delete_by` bigint(20) DEFAULT NULL,
  `role` tinyint(1) DEFAULT NULL,
  `make_admin_by` bigint(20) DEFAULT NULL,
  `is_main` tinyint(1) DEFAULT NULL,
  `is_accept` tinyint(1) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `admin_of_group` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_954D5B0A76ED395` (`user_id`),
  KEY `IDX_954D5B0FE54D947` (`group_id`),
  KEY `IDX_954D5B019A0FE00` (`default_receiver_id`),
  KEY `IDX_954D5B0DE12AB56` (`created_by`),
  KEY `IDX_954D5B016FE72E1` (`updated_by`),
  KEY `IDX_954D5B072FA3AAF` (`delete_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `UserGroup`
--
ALTER TABLE `UserGroup`
  ADD CONSTRAINT `FK_954D5B016FE72E1` FOREIGN KEY (`updated_by`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_954D5B019A0FE00` FOREIGN KEY (`default_receiver_id`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_954D5B072FA3AAF` FOREIGN KEY (`delete_by`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_954D5B0A76ED395` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_954D5B0DE12AB56` FOREIGN KEY (`created_by`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_954D5B0FE54D947` FOREIGN KEY (`group_id`) REFERENCES `Groups` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
