-- phpMyAdmin SQL Dump
-- version 4.0.10.12
-- http://www.phpmyadmin.net
--
-- Host: 127.11.218.130:3306
-- Generation Time: Jun 12, 2017 at 04:50 PM
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


-- Constraints for dumped tables
--

--
-- Constraints for table `Message`
--
ALTER TABLE `Message`
  ADD CONSTRAINT `FK_790009E316FE72E1` FOREIGN KEY (`updated_by`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_790009E354B661DA` FOREIGN KEY (`from_group`) REFERENCES `Groups` (`id`),
  ADD CONSTRAINT `FK_790009E372FA3AAF` FOREIGN KEY (`delete_by`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_790009E3CD53EDB6` FOREIGN KEY (`receiver_id`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `FK_790009E3DE12AB56` FOREIGN KEY (`created_by`) REFERENCES `User` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
