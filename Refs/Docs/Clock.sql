-- phpMyAdmin SQL Dump
-- version 4.0.10.12
-- http://www.phpmyadmin.net
--
-- Host: 127.11.218.130:3306
-- Generation Time: Mar 20, 2017 at 03:46 PM
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
-- Table structure for table `Groups`
--

CREATE TABLE IF NOT EXISTS `Groups` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `delete_by` bigint(20) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F7C13C465E237E06` (`name`),
  KEY `IDX_F7C13C46DE12AB56` (`created_by`),
  KEY `IDX_F7C13C4616FE72E1` (`updated_by`),
  KEY `IDX_F7C13C4672FA3AAF` (`delete_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Message`
--

CREATE TABLE IF NOT EXISTS `Message` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `receiver_id` bigint(20) NOT NULL,
  `from_group` bigint(20) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `delete_by` bigint(20) DEFAULT NULL,
  `emotion` smallint(6) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `longitude` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `latitude` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_790009E3CD53EDB6` (`receiver_id`),
  KEY `IDX_790009E354B661DA` (`from_group`),
  KEY `IDX_790009E3DE12AB56` (`created_by`),
  KEY `IDX_790009E316FE72E1` (`updated_by`),
  KEY `IDX_790009E372FA3AAF` (`delete_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE IF NOT EXISTS `User` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `delete_by` bigint(20) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_tmp` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone_number` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ud_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_message_id` bigint(20) DEFAULT NULL,
  `is_running` tinyint(1) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2DA17977C912ED9D` (`api_key`),
  KEY `IDX_2DA17977DE12AB56` (`created_by`),
  KEY `IDX_2DA1797716FE72E1` (`updated_by`),
  KEY `IDX_2DA1797772FA3AAF` (`delete_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

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
-- Constraints for table `Groups`
--
ALTER TABLE `Groups`
  ADD CONSTRAINT `FK_F7C13C4616FE72E1` FOREIGN KEY (`updated_by`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_F7C13C4672FA3AAF` FOREIGN KEY (`delete_by`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_F7C13C46DE12AB56` FOREIGN KEY (`created_by`) REFERENCES `User` (`id`);

--
-- Constraints for table `Message`
--
ALTER TABLE `Message`
  ADD CONSTRAINT `FK_790009E316FE72E1` FOREIGN KEY (`updated_by`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_790009E354B661DA` FOREIGN KEY (`from_group`) REFERENCES `Groups` (`id`),
  ADD CONSTRAINT `FK_790009E372FA3AAF` FOREIGN KEY (`delete_by`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_790009E3CD53EDB6` FOREIGN KEY (`receiver_id`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_790009E3DE12AB56` FOREIGN KEY (`created_by`) REFERENCES `User` (`id`);

--
-- Constraints for table `User`
--
ALTER TABLE `User`
  ADD CONSTRAINT `FK_2DA1797716FE72E1` FOREIGN KEY (`updated_by`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_2DA1797772FA3AAF` FOREIGN KEY (`delete_by`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_2DA17977DE12AB56` FOREIGN KEY (`created_by`) REFERENCES `User` (`id`);

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
