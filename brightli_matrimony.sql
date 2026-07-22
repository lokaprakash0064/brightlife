-- phpMyAdmin SQL Dump
-- version 5.2.0-rc1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 19, 2026 at 12:32 PM
-- Server version: 8.0.46-0ubuntu0.24.04.3
-- PHP Version: 8.5.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `matrimony`
--

-- --------------------------------------------------------

--
-- Table structure for table `bl_caste`
--

CREATE TABLE `bl_caste` (
  `caste_id` int NOT NULL,
  `caste_name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bl_education`
--

CREATE TABLE `bl_education` (
  `edu_id` int NOT NULL,
  `edu_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bl_images`
--

CREATE TABLE `bl_images` (
  `img_id` bigint NOT NULL,
  `img_su_id` bigint NOT NULL,
  `img_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `img_dp` tinyint NOT NULL COMMENT '1 active',
  `img_dttm` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bl_income`
--

CREATE TABLE `bl_income` (
  `in_id` int NOT NULL,
  `in_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bl_message`
--

CREATE TABLE `bl_message` (
  `msg_id` bigint NOT NULL,
  `cust_id` varchar(50) NOT NULL,
  `sender_cus_id` varchar(50) NOT NULL,
  `msg_message` longtext NOT NULL,
  `msg_sts` tinyint(1) NOT NULL,
  `msg_dttm` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bl_occupation`
--

CREATE TABLE `bl_occupation` (
  `occ_id` int NOT NULL,
  `occ_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bl_partner_preference`
--

CREATE TABLE `bl_partner_preference` (
  `pp_id` bigint NOT NULL,
  `pp_cust_id` varchar(50) NOT NULL,
  `signup_id` bigint NOT NULL,
  `email_id` varchar(100) NOT NULL,
  `pp_maritial_stat` varchar(50) NOT NULL,
  `pp_religion` varchar(50) NOT NULL,
  `pp_caste` varchar(20) NOT NULL,
  `pp_residing_state` varchar(50) NOT NULL,
  `pp_residing_city` varchar(30) NOT NULL,
  `pp_ageFrom` varchar(10) NOT NULL,
  `pp_ageTo` varchar(10) NOT NULL,
  `pp_education` varchar(40) NOT NULL,
  `pp_occupation` varchar(40) NOT NULL,
  `pp_employedIn` varchar(30) NOT NULL,
  `pp_income` varchar(30) NOT NULL,
  `pp_familyStatus` varchar(30) NOT NULL,
  `pp_familyType` varchar(30) NOT NULL,
  `pp_familyValues` varchar(30) NOT NULL,
  `pp_partnerDesc` longtext NOT NULL,
  `pp_dttm` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bl_personal_detail`
--

CREATE TABLE `bl_personal_detail` (
  `pd_id` bigint NOT NULL,
  `sign_up_id` bigint NOT NULL,
  `pd_profCreated` varchar(100) NOT NULL COMMENT '1 self, 2 son, 3 daughter, 4 brother,  5 relative, 6 sister, 7 friend',
  `pd_maritalStatus` varchar(100) NOT NULL COMMENT '1 unmarried, 2 widow, 3 divorced, 4 awaiting divorce',
  `pd_rashi` varchar(50) NOT NULL,
  `pd_star` varchar(30) NOT NULL,
  `pd_gothra` varchar(30) NOT NULL,
  `pd_residingState` varchar(100) NOT NULL,
  `pd_residingCity` varchar(30) NOT NULL,
  `pd_height` varchar(50) NOT NULL,
  `pd_weight` varchar(50) NOT NULL,
  `pd_bodyType` varchar(50) NOT NULL,
  `pd_complexion` varchar(50) NOT NULL,
  `pd_phyisicalStatus` varchar(50) NOT NULL,
  `pd_education` varchar(50) NOT NULL,
  `pd_eduDetails` varchar(100) NOT NULL,
  `pd_occupation` varchar(50) NOT NULL,
  `pd_occDetails` varchar(100) NOT NULL,
  `pd_employedIn` varchar(50) NOT NULL,
  `pd_income` varchar(50) NOT NULL,
  `pd_food` varchar(50) NOT NULL,
  `pd_smoking` varchar(50) NOT NULL,
  `pd_drinking` varchar(50) NOT NULL,
  `pd_familyStatus` varchar(50) NOT NULL,
  `pd_familyType` varchar(50) NOT NULL,
  `pd_familyValues` varchar(50) NOT NULL,
  `pd_occFather` varchar(100) NOT NULL,
  `pd_occMother` varchar(100) NOT NULL,
  `pd_desc` longtext NOT NULL,
  `pd_email` varchar(100) NOT NULL,
  `pd_dttm` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bl_rashi`
--

CREATE TABLE `bl_rashi` (
  `ras_id` int NOT NULL,
  `ras_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bl_sign_up`
--

CREATE TABLE `bl_sign_up` (
  `su_id` bigint NOT NULL,
  `su_name` varchar(100) NOT NULL,
  `su_dob` date NOT NULL,
  `su_gender` varchar(30) NOT NULL,
  `su_religion` varchar(40) NOT NULL,
  `su_motherTounge` varchar(40) NOT NULL,
  `su_caste` varchar(50) NOT NULL,
  `su_mobile` varchar(100) NOT NULL,
  `su_referrer_mobile` varchar(10) DEFAULT NULL,
  `su_email` varchar(100) NOT NULL,
  `su_pass` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `su_dttm` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bl_star`
--

CREATE TABLE `bl_star` (
  `star_id` int NOT NULL,
  `star_name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bl_states`
--

CREATE TABLE `bl_states` (
  `state_id` int NOT NULL,
  `state_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mother_tounge`
--

CREATE TABLE `mother_tounge` (
  `mt_id` int NOT NULL,
  `mt_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `religion`
--

CREATE TABLE `religion` (
  `rel_id` int NOT NULL,
  `rel_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bl_caste`
--
ALTER TABLE `bl_caste`
  ADD PRIMARY KEY (`caste_id`);

--
-- Indexes for table `bl_education`
--
ALTER TABLE `bl_education`
  ADD PRIMARY KEY (`edu_id`);

--
-- Indexes for table `bl_images`
--
ALTER TABLE `bl_images`
  ADD PRIMARY KEY (`img_id`);

--
-- Indexes for table `bl_income`
--
ALTER TABLE `bl_income`
  ADD PRIMARY KEY (`in_id`);

--
-- Indexes for table `bl_message`
--
ALTER TABLE `bl_message`
  ADD PRIMARY KEY (`msg_id`);

--
-- Indexes for table `bl_occupation`
--
ALTER TABLE `bl_occupation`
  ADD PRIMARY KEY (`occ_id`);

--
-- Indexes for table `bl_partner_preference`
--
ALTER TABLE `bl_partner_preference`
  ADD PRIMARY KEY (`pp_id`);

--
-- Indexes for table `bl_personal_detail`
--
ALTER TABLE `bl_personal_detail`
  ADD PRIMARY KEY (`pd_id`);

--
-- Indexes for table `bl_rashi`
--
ALTER TABLE `bl_rashi`
  ADD PRIMARY KEY (`ras_id`);

--
-- Indexes for table `bl_sign_up`
--
ALTER TABLE `bl_sign_up`
  ADD PRIMARY KEY (`su_id`);

--
-- Indexes for table `bl_star`
--
ALTER TABLE `bl_star`
  ADD PRIMARY KEY (`star_id`);

--
-- Indexes for table `bl_states`
--
ALTER TABLE `bl_states`
  ADD PRIMARY KEY (`state_id`);

--
-- Indexes for table `mother_tounge`
--
ALTER TABLE `mother_tounge`
  ADD PRIMARY KEY (`mt_id`);

--
-- Indexes for table `religion`
--
ALTER TABLE `religion`
  ADD PRIMARY KEY (`rel_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bl_caste`
--
ALTER TABLE `bl_caste`
  MODIFY `caste_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bl_education`
--
ALTER TABLE `bl_education`
  MODIFY `edu_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bl_images`
--
ALTER TABLE `bl_images`
  MODIFY `img_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bl_income`
--
ALTER TABLE `bl_income`
  MODIFY `in_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bl_message`
--
ALTER TABLE `bl_message`
  MODIFY `msg_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bl_occupation`
--
ALTER TABLE `bl_occupation`
  MODIFY `occ_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bl_partner_preference`
--
ALTER TABLE `bl_partner_preference`
  MODIFY `pp_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bl_personal_detail`
--
ALTER TABLE `bl_personal_detail`
  MODIFY `pd_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bl_rashi`
--
ALTER TABLE `bl_rashi`
  MODIFY `ras_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bl_sign_up`
--
ALTER TABLE `bl_sign_up`
  MODIFY `su_id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bl_star`
--
ALTER TABLE `bl_star`
  MODIFY `star_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bl_states`
--
ALTER TABLE `bl_states`
  MODIFY `state_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mother_tounge`
--
ALTER TABLE `mother_tounge`
  MODIFY `mt_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `religion`
--
ALTER TABLE `religion`
  MODIFY `rel_id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
