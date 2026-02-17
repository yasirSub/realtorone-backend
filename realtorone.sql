-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql:3306
-- Generation Time: Feb 15, 2026 at 04:26 PM
-- Server version: 8.4.8
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `realtorone`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'leadOutreach',
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'conscious',
  `points` int NOT NULL DEFAULT '0',
  `quantity` int DEFAULT NULL,
  `value` decimal(15,2) DEFAULT NULL,
  `min_tier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Free',
  `duration_minutes` int NOT NULL DEFAULT '30',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `user_id`, `title`, `description`, `type`, `category`, `points`, `quantity`, `value`, `min_tier`, `duration_minutes`, `scheduled_at`, `is_completed`, `completed_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 6, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-09 10:00:00', 1, '2026-02-09 18:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(2, 6, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-09 09:00:00', 1, '2026-02-09 13:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(3, 6, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-09 16:00:00', 0, NULL, NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(4, 6, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-09 14:00:00', 1, '2026-02-09 15:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(5, 6, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-09 17:00:00', 0, NULL, NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(6, 6, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-10 11:00:00', 1, '2026-02-10 12:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(7, 6, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-10 11:00:00', 1, '2026-02-10 13:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(8, 6, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-10 10:00:00', 0, NULL, NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(9, 6, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-11 11:00:00', 1, '2026-02-11 11:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(10, 6, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-11 15:00:00', 1, '2026-02-11 10:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(11, 6, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-11 09:00:00', 0, NULL, NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(12, 6, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-11 13:00:00', 1, '2026-02-11 12:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(13, 6, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-11 15:00:00', 1, '2026-02-11 13:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(14, 6, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-12 15:00:00', 1, '2026-02-12 18:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(15, 6, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-12 14:00:00', 0, NULL, NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(16, 6, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-12 16:00:00', 1, '2026-02-12 12:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(17, 6, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-13 10:00:00', 1, '2026-02-13 16:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(18, 6, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-13 17:00:00', 1, '2026-02-13 16:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(19, 6, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-13 13:00:00', 0, NULL, NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(20, 6, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-14 17:00:00', 1, '2026-02-14 13:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(21, 6, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-14 13:00:00', 1, '2026-02-14 15:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(22, 6, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-14 10:00:00', 0, NULL, NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(23, 6, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-15 10:00:00', 1, '2026-02-15 11:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(24, 6, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-15 09:00:00', 1, '2026-02-15 11:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(25, 6, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-15 10:00:00', 1, '2026-02-15 14:00:00', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(26, 6, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-15 13:00:00', 0, NULL, NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(27, 7, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-09 13:00:00', 1, '2026-02-09 11:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(28, 7, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-09 14:00:00', 1, '2026-02-09 12:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(29, 7, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-09 10:00:00', 1, '2026-02-09 13:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(30, 7, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-09 15:00:00', 1, '2026-02-09 15:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(31, 7, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-09 11:00:00', 1, '2026-02-09 18:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(32, 7, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-10 16:00:00', 1, '2026-02-10 17:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(33, 7, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-10 11:00:00', 1, '2026-02-10 11:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(34, 7, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-10 09:00:00', 1, '2026-02-10 18:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(35, 7, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-11 15:00:00', 1, '2026-02-11 14:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(36, 7, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-11 15:00:00', 1, '2026-02-11 11:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(37, 7, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-11 13:00:00', 1, '2026-02-11 13:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(38, 7, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-12 14:00:00', 0, NULL, NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(39, 7, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-12 09:00:00', 1, '2026-02-12 14:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(40, 7, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-12 12:00:00', 1, '2026-02-12 17:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(41, 7, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-13 16:00:00', 0, NULL, NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(42, 7, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-13 16:00:00', 1, '2026-02-13 17:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(43, 7, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-13 12:00:00', 1, '2026-02-13 10:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(44, 7, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-14 15:00:00', 1, '2026-02-14 15:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(45, 7, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-14 16:00:00', 1, '2026-02-14 17:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(46, 7, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-14 13:00:00', 1, '2026-02-14 13:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(47, 7, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-14 10:00:00', 1, '2026-02-14 10:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(48, 7, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-15 15:00:00', 1, '2026-02-15 12:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(49, 7, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-15 15:00:00', 1, '2026-02-15 16:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(50, 7, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-15 13:00:00', 1, '2026-02-15 12:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(51, 7, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-15 13:00:00', 1, '2026-02-15 14:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(52, 8, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-09 15:00:00', 1, '2026-02-09 14:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(53, 8, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-09 09:00:00', 1, '2026-02-09 11:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(54, 8, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-09 16:00:00', 1, '2026-02-09 11:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(55, 8, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-09 09:00:00', 1, '2026-02-09 12:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(56, 8, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-09 10:00:00', 1, '2026-02-09 18:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(57, 8, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-10 09:00:00', 1, '2026-02-10 13:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(58, 8, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-10 10:00:00', 1, '2026-02-10 17:00:00', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(59, 8, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-10 17:00:00', 0, NULL, NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(60, 8, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-10 10:00:00', 0, NULL, NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(61, 8, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-10 14:00:00', 0, NULL, NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(62, 8, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-11 14:00:00', 0, NULL, NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(63, 8, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-11 13:00:00', 1, '2026-02-11 12:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(64, 8, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-11 09:00:00', 1, '2026-02-11 10:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(65, 8, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-11 17:00:00', 1, '2026-02-11 15:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(66, 8, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-12 10:00:00', 1, '2026-02-12 15:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(67, 8, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-12 15:00:00', 1, '2026-02-12 14:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(68, 8, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-12 16:00:00', 1, '2026-02-12 17:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(69, 8, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-12 13:00:00', 1, '2026-02-12 16:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(70, 8, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-13 15:00:00', 1, '2026-02-13 10:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(71, 8, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-13 11:00:00', 1, '2026-02-13 15:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(72, 8, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-13 14:00:00', 0, NULL, NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(73, 8, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-13 09:00:00', 1, '2026-02-13 14:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(74, 8, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-14 10:00:00', 0, NULL, NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(75, 8, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-14 09:00:00', 1, '2026-02-14 14:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(76, 8, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-14 11:00:00', 0, NULL, NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(77, 8, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-14 10:00:00', 1, '2026-02-14 12:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(78, 8, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-15 13:00:00', 1, '2026-02-15 14:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(79, 8, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-15 16:00:00', 1, '2026-02-15 12:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(80, 8, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-15 14:00:00', 0, NULL, NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(81, 8, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-15 11:00:00', 1, '2026-02-15 10:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(82, 9, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-09 10:00:00', 1, '2026-02-09 14:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(83, 9, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-09 10:00:00', 1, '2026-02-09 18:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(84, 9, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-09 14:00:00', 1, '2026-02-09 13:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(85, 9, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-09 11:00:00', 1, '2026-02-09 11:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(86, 9, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-10 09:00:00', 1, '2026-02-10 13:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(87, 9, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-10 13:00:00', 1, '2026-02-10 15:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(88, 9, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-10 11:00:00', 1, '2026-02-10 13:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(89, 9, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-11 17:00:00', 0, NULL, NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(90, 9, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-11 15:00:00', 1, '2026-02-11 14:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(91, 9, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-11 09:00:00', 0, NULL, NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(92, 9, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-11 17:00:00', 1, '2026-02-11 16:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(93, 9, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-11 12:00:00', 1, '2026-02-11 16:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(94, 9, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-12 09:00:00', 1, '2026-02-12 11:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(95, 9, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-12 12:00:00', 0, NULL, NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(96, 9, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-12 12:00:00', 1, '2026-02-12 16:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(97, 9, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-12 15:00:00', 1, '2026-02-12 16:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(98, 9, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-13 13:00:00', 1, '2026-02-13 17:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(99, 9, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-13 16:00:00', 1, '2026-02-13 16:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(100, 9, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-13 09:00:00', 1, '2026-02-13 10:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(101, 9, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-14 14:00:00', 1, '2026-02-14 11:00:00', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(102, 9, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-14 11:00:00', 0, NULL, NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(103, 9, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-14 12:00:00', 0, NULL, NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(104, 9, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-14 14:00:00', 0, NULL, NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(105, 9, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-14 11:00:00', 1, '2026-02-14 17:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(106, 9, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-15 11:00:00', 1, '2026-02-15 13:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(107, 9, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-15 11:00:00', 1, '2026-02-15 15:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(108, 9, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-15 11:00:00', 0, NULL, NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(109, 9, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-15 15:00:00', 1, '2026-02-15 17:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(110, 9, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-15 15:00:00', 1, '2026-02-15 13:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(111, 10, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-09 15:00:00', 1, '2026-02-09 16:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(112, 10, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-09 10:00:00', 1, '2026-02-09 11:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(113, 10, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-09 17:00:00', 1, '2026-02-09 18:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(114, 10, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-09 11:00:00', 1, '2026-02-09 14:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(115, 10, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-09 16:00:00', 1, '2026-02-09 13:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(116, 10, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-10 12:00:00', 1, '2026-02-10 18:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(117, 10, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-10 17:00:00', 1, '2026-02-10 14:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(118, 10, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-10 17:00:00', 1, '2026-02-10 10:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(119, 10, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-10 17:00:00', 0, NULL, NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(120, 10, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-11 13:00:00', 1, '2026-02-11 10:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(121, 10, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-11 15:00:00', 1, '2026-02-11 17:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(122, 10, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-11 11:00:00', 1, '2026-02-11 18:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(123, 10, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-12 15:00:00', 0, NULL, NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(124, 10, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-12 12:00:00', 1, '2026-02-12 13:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(125, 10, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-12 15:00:00', 0, NULL, NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(126, 10, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-13 14:00:00', 1, '2026-02-13 12:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(127, 10, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-13 14:00:00', 1, '2026-02-13 15:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(128, 10, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-13 09:00:00', 1, '2026-02-13 16:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(129, 10, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-14 15:00:00', 1, '2026-02-14 12:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(130, 10, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-14 17:00:00', 1, '2026-02-14 12:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(131, 10, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-14 11:00:00', 1, '2026-02-14 11:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(132, 10, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-14 10:00:00', 1, '2026-02-14 10:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(133, 10, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-15 17:00:00', 1, '2026-02-15 17:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(134, 10, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-15 15:00:00', 1, '2026-02-15 16:00:00', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(135, 10, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-15 15:00:00', 1, '2026-02-15 17:00:00', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(136, 10, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-15 17:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(137, 11, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-09 14:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(138, 11, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-09 16:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(139, 11, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-09 13:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(140, 11, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-10 14:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(141, 11, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-10 16:00:00', 1, '2026-02-10 18:00:00', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(142, 11, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-10 14:00:00', 1, '2026-02-10 14:00:00', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(143, 11, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-11 15:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(144, 11, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-11 12:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(145, 11, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-11 10:00:00', 1, '2026-02-11 18:00:00', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(146, 11, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-12 12:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(147, 11, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-12 13:00:00', 1, '2026-02-12 14:00:00', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(148, 11, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-12 16:00:00', 1, '2026-02-12 17:00:00', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(149, 11, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-12 15:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(150, 11, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-13 17:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(151, 11, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-13 17:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(152, 11, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-13 11:00:00', 1, '2026-02-13 17:00:00', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(153, 11, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-13 16:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(154, 11, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-14 12:00:00', 1, '2026-02-14 15:00:00', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(155, 11, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-14 17:00:00', 1, '2026-02-14 17:00:00', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(156, 11, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-14 15:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(157, 11, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-14 15:00:00', 0, NULL, NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(158, 11, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-15 10:00:00', 1, '2026-02-15 13:00:00', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(159, 11, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-15 09:00:00', 1, '2026-02-15 11:00:00', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(160, 11, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-15 13:00:00', 1, '2026-02-15 18:00:00', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(161, 11, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-15 12:00:00', 1, '2026-02-15 11:00:00', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(162, 12, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-09 09:00:00', 1, '2026-02-09 18:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(163, 12, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-09 16:00:00', 1, '2026-02-09 17:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(164, 12, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-09 10:00:00', 1, '2026-02-09 16:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(165, 12, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-09 12:00:00', 0, NULL, NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(166, 12, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-09 13:00:00', 1, '2026-02-09 18:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(167, 12, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-10 17:00:00', 0, NULL, NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(168, 12, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-10 16:00:00', 1, '2026-02-10 17:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(169, 12, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-10 09:00:00', 1, '2026-02-10 18:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(170, 12, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-10 17:00:00', 1, '2026-02-10 17:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(171, 12, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-10 11:00:00', 1, '2026-02-10 16:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(172, 12, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-11 09:00:00', 1, '2026-02-11 15:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(173, 12, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-11 09:00:00', 1, '2026-02-11 15:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(174, 12, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-11 09:00:00', 1, '2026-02-11 13:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(175, 12, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-11 10:00:00', 1, '2026-02-11 13:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(176, 12, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-11 11:00:00', 1, '2026-02-11 11:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(177, 12, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-12 09:00:00', 1, '2026-02-12 17:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(178, 12, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-12 13:00:00', 1, '2026-02-12 16:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(179, 12, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-12 14:00:00', 1, '2026-02-12 16:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(180, 12, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-12 11:00:00', 0, NULL, NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(181, 12, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-12 15:00:00', 0, NULL, NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(182, 12, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-13 09:00:00', 0, NULL, NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(183, 12, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-13 17:00:00', 1, '2026-02-13 11:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(184, 12, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-13 15:00:00', 1, '2026-02-13 10:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(185, 12, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-14 13:00:00', 1, '2026-02-14 16:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(186, 12, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-14 16:00:00', 0, NULL, NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(187, 12, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-14 13:00:00', 1, '2026-02-14 10:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(188, 12, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-14 10:00:00', 1, '2026-02-14 15:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(189, 12, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-15 14:00:00', 1, '2026-02-15 11:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(190, 12, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-15 13:00:00', 0, NULL, NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(191, 12, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-15 14:00:00', 1, '2026-02-15 15:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(192, 12, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-15 13:00:00', 0, NULL, NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(193, 13, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-09 09:00:00', 0, NULL, NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(194, 13, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-09 17:00:00', 1, '2026-02-09 11:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(195, 13, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-09 12:00:00', 1, '2026-02-09 15:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(196, 13, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-09 16:00:00', 1, '2026-02-09 13:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(197, 13, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-10 16:00:00', 1, '2026-02-10 17:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(198, 13, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-10 16:00:00', 1, '2026-02-10 11:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(199, 13, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-10 11:00:00', 1, '2026-02-10 14:00:00', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(200, 13, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-11 16:00:00', 1, '2026-02-11 18:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(201, 13, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-11 14:00:00', 0, NULL, NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(202, 13, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-11 11:00:00', 1, '2026-02-11 17:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(203, 13, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-11 09:00:00', 1, '2026-02-11 14:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(204, 13, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-12 13:00:00', 1, '2026-02-12 17:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(205, 13, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-12 10:00:00', 1, '2026-02-12 14:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(206, 13, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-12 14:00:00', 1, '2026-02-12 16:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(207, 13, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-12 14:00:00', 1, '2026-02-12 10:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(208, 13, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-12 09:00:00', 1, '2026-02-12 14:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(209, 13, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-13 15:00:00', 1, '2026-02-13 13:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(210, 13, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-13 11:00:00', 1, '2026-02-13 15:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(211, 13, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-13 11:00:00', 1, '2026-02-13 13:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(212, 13, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-14 15:00:00', 1, '2026-02-14 11:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(213, 13, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-14 15:00:00', 1, '2026-02-14 16:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(214, 13, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-14 10:00:00', 1, '2026-02-14 15:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(215, 13, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-14 10:00:00', 0, NULL, NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(216, 13, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-14 11:00:00', 0, NULL, NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(217, 13, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-15 11:00:00', 1, '2026-02-15 15:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(218, 13, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-15 14:00:00', 1, '2026-02-15 18:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(219, 13, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-15 14:00:00', 1, '2026-02-15 15:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(220, 13, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-15 13:00:00', 1, '2026-02-15 13:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(221, 13, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-15 17:00:00', 1, '2026-02-15 12:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(222, 14, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-09 16:00:00', 1, '2026-02-09 18:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(223, 14, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-09 16:00:00', 0, NULL, NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(224, 14, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-09 17:00:00', 0, NULL, NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(225, 14, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-10 09:00:00', 1, '2026-02-10 18:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(226, 14, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-10 17:00:00', 0, NULL, NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(227, 14, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-10 11:00:00', 1, '2026-02-10 17:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(228, 14, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-11 11:00:00', 0, NULL, NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(229, 14, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-11 13:00:00', 1, '2026-02-11 10:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(230, 14, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-11 13:00:00', 1, '2026-02-11 17:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(231, 14, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-12 15:00:00', 1, '2026-02-12 13:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(232, 14, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-12 11:00:00', 1, '2026-02-12 14:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(233, 14, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-12 13:00:00', 1, '2026-02-12 11:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(234, 14, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-12 11:00:00', 1, '2026-02-12 16:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(235, 14, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-12 13:00:00', 1, '2026-02-12 16:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(236, 14, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-13 12:00:00', 1, '2026-02-13 18:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(237, 14, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-13 12:00:00', 1, '2026-02-13 11:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(238, 14, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, NULL, NULL, 'Diamond', 30, '2026-02-13 10:00:00', 1, '2026-02-13 11:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(239, 14, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-13 16:00:00', 0, NULL, NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(240, 14, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-13 17:00:00', 1, '2026-02-13 16:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(241, 14, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-14 10:00:00', 1, '2026-02-14 17:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(242, 14, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-14 11:00:00', 0, NULL, NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(243, 14, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-14 12:00:00', 1, '2026-02-14 14:00:00', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(244, 14, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-14 11:00:00', 1, '2026-02-14 15:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(245, 14, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-14 16:00:00', 0, NULL, NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(246, 14, 'Visualization', NULL, 'visualization', 'subconscious', 8, NULL, NULL, 'Free', 30, '2026-02-15 13:00:00', 1, '2026-02-15 16:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(247, 14, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-15 15:00:00', 1, '2026-02-15 10:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(248, 14, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-15 17:00:00', 1, '2026-02-15 12:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(249, 14, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-15 15:00:00', 0, NULL, NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(250, 14, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-15 10:00:00', 0, NULL, NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(251, 15, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-09 14:00:00', 0, NULL, NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(252, 15, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-09 13:00:00', 1, '2026-02-09 17:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(253, 15, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-09 15:00:00', 1, '2026-02-09 10:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(254, 15, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-09 16:00:00', 1, '2026-02-09 10:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(255, 15, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-09 12:00:00', 1, '2026-02-09 10:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(256, 15, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-10 16:00:00', 1, '2026-02-10 10:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(257, 15, 'Site Visits', NULL, 'site_visits', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-10 12:00:00', 1, '2026-02-10 18:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(258, 15, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-10 11:00:00', 1, '2026-02-10 10:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(259, 15, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, NULL, NULL, 'Free', 30, '2026-02-11 12:00:00', 1, '2026-02-11 14:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(260, 15, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-11 14:00:00', 1, '2026-02-11 17:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(261, 15, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-11 15:00:00', 0, NULL, NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(262, 15, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-11 16:00:00', 0, NULL, NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(263, 15, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-11 10:00:00', 1, '2026-02-11 14:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43');
INSERT INTO `activities` (`id`, `user_id`, `title`, `description`, `type`, `category`, `points`, `quantity`, `value`, `min_tier`, `duration_minutes`, `scheduled_at`, `is_completed`, `completed_at`, `notes`, `created_at`, `updated_at`) VALUES
(264, 15, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-12 11:00:00', 0, NULL, NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(265, 15, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-12 13:00:00', 1, '2026-02-12 16:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(266, 15, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-12 17:00:00', 0, NULL, NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(267, 15, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-12 17:00:00', 1, '2026-02-12 14:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(268, 15, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-13 12:00:00', 1, '2026-02-13 10:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(269, 15, 'Content Creation', NULL, 'content_creation', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-13 10:00:00', 1, '2026-02-13 16:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(270, 15, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, NULL, NULL, 'Silver', 30, '2026-02-13 16:00:00', 0, NULL, NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(271, 15, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, NULL, NULL, 'Gold', 30, '2026-02-13 10:00:00', 1, '2026-02-13 18:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(272, 15, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-13 13:00:00', 0, NULL, NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(273, 15, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-14 13:00:00', 0, NULL, NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(274, 15, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, NULL, NULL, 'Silver', 30, '2026-02-14 10:00:00', 1, '2026-02-14 11:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(275, 15, 'CRM Update', NULL, 'crm_update', 'conscious', 5, NULL, NULL, 'Free', 30, '2026-02-14 10:00:00', 1, '2026-02-14 10:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(276, 15, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, NULL, NULL, 'Silver', 30, '2026-02-15 11:00:00', 1, '2026-02-15 17:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(277, 15, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, NULL, NULL, 'Gold', 30, '2026-02-15 12:00:00', 1, '2026-02-15 18:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(278, 15, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, NULL, NULL, 'Free', 30, '2026-02-15 12:00:00', 1, '2026-02-15 13:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(279, 15, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, NULL, NULL, 'Diamond', 30, '2026-02-15 15:00:00', 1, '2026-02-15 16:00:00', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43');

-- --------------------------------------------------------

--
-- Table structure for table `activity_types`
--

CREATE TABLE `activity_types` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int NOT NULL DEFAULT '0',
  `is_global` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `min_tier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Free',
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_types`
--

INSERT INTO `activity_types` (`id`, `name`, `description`, `type_key`, `category`, `points`, `is_global`, `user_id`, `min_tier`, `icon`, `created_at`, `updated_at`) VALUES
(1, 'Visualization', NULL, 'visualization', 'subconscious', 8, 1, NULL, 'Free', 'Eye', '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(2, 'Affirmations', NULL, 'affirmations', 'subconscious', 6, 1, NULL, 'Free', 'Repeat', '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(3, 'Audio Reprogramming', NULL, 'audio_reprogramming', 'subconscious', 6, 1, NULL, 'Silver', 'Headphones', '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(4, 'Belief Exercise', NULL, 'belief_exercise', 'subconscious', 8, 1, NULL, 'Gold', 'PenTool', '2026-02-15 16:21:36', '2026-02-15 16:25:33'),
(5, 'Identity Statement', NULL, 'identity_statement', 'subconscious', 5, 1, NULL, 'Diamond', 'UserCheck', '2026-02-15 16:21:36', '2026-02-15 16:25:33'),
(6, 'Cold Calling', NULL, 'cold_calling', 'conscious', 8, 1, NULL, 'Free', 'Phone', '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(7, 'Content Creation', NULL, 'content_creation', 'conscious', 8, 1, NULL, 'Free', 'Camera', '2026-02-15 16:21:36', '2026-02-15 16:25:33'),
(8, 'DM Conversations', NULL, 'dm_convos', 'conscious', 6, 1, NULL, 'Silver', 'MessageSquare', '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(9, 'Client Meetings', NULL, 'client_meetings', 'conscious', 10, 1, NULL, 'Silver', 'Users', '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(10, 'Deal Negotiation', NULL, 'negotiation', 'conscious', 10, 1, NULL, 'Gold', 'Gavel', '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(11, 'CRM Update', NULL, 'crm_update', 'conscious', 5, 1, NULL, 'Free', 'Database', '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(12, 'Site Visits', NULL, 'site_visits', 'conscious', 10, 1, NULL, 'Gold', 'MapPin', '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(13, 'Luxury Outreach', NULL, 'luxury_outreach', 'conscious', 15, 1, NULL, 'Diamond', 'Star', '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(14, 'Gratitude Journaling', NULL, 'gratitude', 'subconscious', 6, 1, NULL, 'Free', 'BookHeart', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(15, 'Mindset Training', NULL, 'mindset_training', 'subconscious', 8, 1, NULL, 'Free', 'Brain', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(16, 'Webinar Attendance', NULL, 'webinar', 'subconscious', 10, 1, NULL, 'Free', 'Video', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(17, 'Calm Reset', NULL, 'calm_reset', 'subconscious', 5, 1, NULL, 'Free', 'Wind', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(18, 'Morning Focus Ritual', NULL, 'morning_ritual', 'subconscious', 6, 1, NULL, 'Free', 'Sun', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(19, 'Content Posting', NULL, 'content_posting', 'conscious', 6, 1, NULL, 'Free', 'Share2', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(20, 'DM Conversations', NULL, 'dm_conversations', 'conscious', 6, 1, NULL, 'Free', 'MessageCircle', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(21, 'WhatsApp Broadcast', NULL, 'whatsapp_broadcast', 'conscious', 6, 1, NULL, 'Free', 'Send', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(22, 'Mass Emailing', NULL, 'mass_emailing', 'conscious', 6, 1, NULL, 'Free', 'Mail', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(23, 'Prospecting', NULL, 'prospecting', 'conscious', 8, 1, NULL, 'Free', 'Search', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(24, 'Follow-ups', NULL, 'follow_ups', 'conscious', 8, 1, NULL, 'Free', 'RefreshCw', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(25, 'Deal Negotiation', NULL, 'deal_negotiation', 'conscious', 10, 1, NULL, 'Free', 'Briefcase', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(26, 'Client Servicing', NULL, 'client_servicing', 'conscious', 6, 1, NULL, 'Free', 'HeartHandshake', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(27, 'Referral Ask', NULL, 'referral_ask', 'conscious', 6, 1, NULL, 'Free', 'UserPlus', '2026-02-15 16:25:33', '2026-02-15 16:25:33'),
(28, 'Skill Training', NULL, 'skill_training', 'conscious', 8, 1, NULL, 'Free', 'Zap', '2026-02-15 16:25:33', '2026-02-15 16:25:33');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_percentage` int NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `max_uses` int DEFAULT NULL,
  `used_count` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_percentage`, `expires_at`, `max_uses`, `used_count`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'SIR20', 20, NULL, 50, 0, 1, '2026-02-15 16:21:35', '2026-02-15 16:21:35');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_tier` enum('Free','Silver','Gold','Diamond') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Free',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `url`, `min_tier`, `created_at`, `updated_at`) VALUES
(1, 'Real Estate Fundamentals', 'Core principles for new agents.', 'https://example.com/video1', 'Free', '2026-02-15 16:21:35', '2026-02-15 16:21:35'),
(2, 'Digital Branding Mastery', 'Build an online presence that converts.', 'https://example.com/video2', 'Free', '2026-02-15 16:21:35', '2026-02-15 16:21:35'),
(3, 'Advanced Negotiation Tactics', 'Close deals with higher margins.', 'https://example.com/video3', 'Silver', '2026-02-15 16:21:35', '2026-02-15 16:21:35'),
(4, 'Lead Generation Systems', 'Automate your client acquisition.', 'https://example.com/video4', 'Silver', '2026-02-15 16:21:35', '2026-02-15 16:21:35'),
(5, 'Market Analysis & Valuation', 'Deep dive into property pricing.', 'https://example.com/video5', 'Silver', '2026-02-15 16:21:35', '2026-02-15 16:21:35'),
(6, 'Luxury Market Penetration', 'Breaking into high-net-worth circles.', 'https://example.com/video6', 'Gold', '2026-02-15 16:21:35', '2026-02-15 16:21:35'),
(7, 'Team Scaling Dynamics', 'From solo agent to agency owner.', 'https://example.com/video7', 'Gold', '2026-02-15 16:21:35', '2026-02-15 16:21:35'),
(8, 'Investment Portfolio Management', 'Advising investors for long-term wealth.', 'https://example.com/video8', 'Gold', '2026-02-15 16:21:35', '2026-02-15 16:21:35'),
(9, 'Legacy Building & Philanthropy', 'Creating a lasting impact beyond business.', 'https://example.com/video9', 'Diamond', '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(10, 'Global Real Estate Markets', 'International expansion strategies.', 'https://example.com/video10', 'Diamond', '2026-02-15 16:21:36', '2026-02-15 16:21:36');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `learning_content`
--

CREATE TABLE `learning_content` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` enum('marketFundamentals','leadSystems','communication','negotiation','hniHandling','commissionScaling','dealArchitecture','brandAuthority') COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('video','audio','article','quiz') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tier` enum('free','premium') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'free',
  `thumbnail_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_minutes` int NOT NULL DEFAULT '0',
  `order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `learning_content`
--

INSERT INTO `learning_content` (`id`, `title`, `description`, `category`, `type`, `tier`, `thumbnail_url`, `content_url`, `duration_minutes`, `order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Dubai Market Overview 2026', 'A strategic look at the upcoming year in real estate.', 'marketFundamentals', 'video', 'free', NULL, NULL, 45, 0, 1, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(2, 'The Blueprint Checklist', 'Essential documents for every transaction.', 'marketFundamentals', 'article', 'free', NULL, NULL, 10, 0, 1, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(3, 'Automated Lead Magnets', 'How to build systems that attract clients while you sleep.', 'leadSystems', 'video', 'free', NULL, NULL, 30, 0, 1, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(4, 'The Billionaire Code', 'Ethical psychology for working with HNIs.', 'hniHandling', 'video', 'premium', NULL, NULL, 60, 0, 1, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(5, 'Private Client Group Protocol', 'The exclusive service standard for top-tier clients.', 'hniHandling', 'audio', 'premium', NULL, NULL, 25, 0, 1, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(6, 'Negotiating the 5%', 'Never drop your commission again.', 'commissionScaling', 'video', 'premium', NULL, NULL, 40, 0, 1, '2026-02-15 16:21:43', '2026-02-15 16:21:43');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_02_02_202124_add_realtor_fields_to_users_table', 1),
(5, '2026_02_02_202152_create_activities_table', 1),
(6, '2026_02_02_202203_create_learning_content_table', 1),
(7, '2026_02_03_000001_add_profile_photo_to_users_table', 1),
(8, '2026_02_11_132338_create_activity_types_table', 1),
(9, '2026_02_11_143033_create_subscription_packages_table', 1),
(10, '2026_02_11_143111_add_tier_fields_to_tables', 1),
(11, '2026_02_11_144053_create_coupons_table', 1),
(12, '2026_02_11_144105_create_user_subscriptions_table', 1),
(13, '2026_02_11_161315_create_courses_table', 1),
(14, '2026_02_11_165220_add_profile_details_to_users_table', 1),
(15, '2026_02_11_184500_update_activities_and_add_metrics', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `performance_metrics`
--

CREATE TABLE `performance_metrics` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `subconscious_score` int NOT NULL DEFAULT '0',
  `conscious_score` int NOT NULL DEFAULT '0',
  `results_score` int NOT NULL DEFAULT '0',
  `total_momentum_score` int NOT NULL DEFAULT '0',
  `leads_generated` int NOT NULL DEFAULT '0',
  `deals_closed` int NOT NULL DEFAULT '0',
  `commission_earned` decimal(15,2) NOT NULL DEFAULT '0.00',
  `streak_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `performance_metrics`
--

INSERT INTO `performance_metrics` (`id`, `user_id`, `date`, `subconscious_score`, `conscious_score`, `results_score`, `total_momentum_score`, `leads_generated`, `deals_closed`, `commission_earned`, `streak_count`, `created_at`, `updated_at`) VALUES
(1, 6, '2026-02-01', 81, 71, 78, 84, 10, 3, 3810.00, 5, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(2, 6, '2026-02-02', 78, 90, 75, 72, 3, 0, 8129.00, 2, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(3, 6, '2026-02-03', 68, 63, 51, 69, 7, 2, 4575.00, 7, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(4, 6, '2026-02-04', 68, 85, 30, 58, 5, 0, 5347.00, 2, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(5, 6, '2026-02-05', 86, 79, 82, 49, 11, 1, 7006.00, 7, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(6, 6, '2026-02-06', 78, 75, 84, 44, 7, 1, 332.00, 17, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(7, 6, '2026-02-07', 82, 87, 77, 80, 11, 2, 3846.00, 16, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(8, 6, '2026-02-08', 67, 70, 43, 50, 3, 1, 586.00, 13, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(9, 6, '2026-02-09', 40, 20, 82, 22, 5, 3, 6102.00, 19, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(10, 6, '2026-02-10', 33, 33, 43, 14, 5, 1, 2998.00, 6, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(11, 6, '2026-02-11', 40, 40, 71, 30, 10, 3, 3508.00, 15, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(12, 6, '2026-02-12', 0, 66, 31, 13, 2, 3, 3477.00, 17, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(13, 6, '2026-02-13', 66, 0, 41, 14, 12, 0, 214.00, 14, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(14, 6, '2026-02-14', 0, 66, 42, 18, 0, 3, 2196.00, 6, '2026-02-15 16:21:36', '2026-02-15 16:21:36'),
(15, 6, '2026-02-15', 25, 50, 87, 22, 9, 0, 835.00, 18, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(16, 7, '2026-02-01', 84, 68, 45, 71, 14, 0, 255.00, 5, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(17, 7, '2026-02-02', 68, 88, 34, 65, 6, 2, 8109.00, 14, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(18, 7, '2026-02-03', 77, 85, 42, 78, 9, 1, 3906.00, 16, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(19, 7, '2026-02-04', 74, 83, 59, 62, 15, 1, 1499.00, 9, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(20, 7, '2026-02-05', 72, 82, 81, 43, 13, 3, 9677.00, 20, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(21, 7, '2026-02-06', 89, 79, 41, 89, 0, 0, 2687.00, 2, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(22, 7, '2026-02-07', 80, 60, 80, 70, 12, 3, 3153.00, 2, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(23, 7, '2026-02-08', 63, 77, 75, 69, 14, 3, 5200.00, 15, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(24, 7, '2026-02-09', 40, 60, 34, 37, 3, 2, 8375.00, 11, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(25, 7, '2026-02-10', 66, 33, 57, 21, 9, 0, 5729.00, 15, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(26, 7, '2026-02-11', 33, 66, 81, 22, 1, 2, 6269.00, 8, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(27, 7, '2026-02-12', 0, 66, 43, 16, 3, 2, 8530.00, 14, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(28, 7, '2026-02-13', 0, 66, 35, 15, 3, 0, 6862.00, 9, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(29, 7, '2026-02-14', 75, 25, 90, 30, 5, 3, 3838.00, 18, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(30, 7, '2026-02-15', 25, 75, 33, 33, 4, 2, 6850.00, 4, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(31, 8, '2026-02-01', 82, 68, 50, 65, 2, 2, 4255.00, 18, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(32, 8, '2026-02-02', 67, 76, 53, 87, 5, 2, 7772.00, 20, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(33, 8, '2026-02-03', 86, 70, 65, 55, 1, 3, 6507.00, 7, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(34, 8, '2026-02-04', 87, 84, 58, 47, 14, 1, 7078.00, 10, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(35, 8, '2026-02-05', 80, 79, 83, 50, 10, 1, 8996.00, 6, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(36, 8, '2026-02-06', 67, 64, 44, 50, 0, 3, 6826.00, 13, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(37, 8, '2026-02-07', 84, 85, 66, 74, 5, 3, 7076.00, 9, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(38, 8, '2026-02-08', 64, 60, 39, 47, 6, 2, 9756.00, 17, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(39, 8, '2026-02-09', 60, 40, 72, 30, 4, 0, 9468.00, 10, '2026-02-15 16:21:37', '2026-02-15 16:21:37'),
(40, 8, '2026-02-10', 20, 20, 71, 14, 14, 1, 6357.00, 16, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(41, 8, '2026-02-11', 0, 75, 34, 35, 12, 1, 6935.00, 8, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(42, 8, '2026-02-12', 25, 75, 30, 37, 5, 2, 3220.00, 4, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(43, 8, '2026-02-13', 50, 25, 66, 27, 0, 0, 5931.00, 10, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(44, 8, '2026-02-14', 25, 25, 88, 23, 4, 3, 6237.00, 15, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(45, 8, '2026-02-15', 0, 75, 53, 26, 5, 2, 6243.00, 2, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(46, 9, '2026-02-01', 90, 75, 88, 73, 2, 1, 6717.00, 12, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(47, 9, '2026-02-02', 63, 75, 76, 64, 8, 3, 6899.00, 10, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(48, 9, '2026-02-03', 69, 73, 80, 88, 12, 2, 8530.00, 9, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(49, 9, '2026-02-04', 66, 81, 48, 85, 3, 3, 9371.00, 18, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(50, 9, '2026-02-05', 73, 70, 58, 41, 9, 1, 8664.00, 9, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(51, 9, '2026-02-06', 70, 82, 72, 62, 9, 3, 9547.00, 6, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(52, 9, '2026-02-07', 75, 60, 70, 76, 4, 1, 1875.00, 15, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(53, 9, '2026-02-08', 85, 87, 81, 58, 2, 2, 4620.00, 7, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(54, 9, '2026-02-09', 25, 75, 50, 27, 2, 2, 4347.00, 10, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(55, 9, '2026-02-10', 33, 66, 30, 28, 13, 0, 8905.00, 6, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(56, 9, '2026-02-11', 20, 40, 80, 31, 8, 2, 8092.00, 18, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(57, 9, '2026-02-12', 50, 25, 88, 22, 15, 2, 8837.00, 5, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(58, 9, '2026-02-13', 33, 66, 44, 19, 5, 1, 1266.00, 15, '2026-02-15 16:21:38', '2026-02-15 16:21:38'),
(59, 9, '2026-02-14', 20, 20, 39, 13, 4, 3, 8188.00, 5, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(60, 9, '2026-02-15', 40, 40, 47, 39, 5, 2, 5852.00, 6, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(61, 10, '2026-02-01', 72, 78, 83, 72, 1, 1, 3331.00, 7, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(62, 10, '2026-02-02', 75, 71, 31, 53, 6, 0, 8004.00, 15, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(63, 10, '2026-02-03', 83, 60, 40, 78, 11, 2, 6544.00, 11, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(64, 10, '2026-02-04', 86, 86, 71, 44, 13, 2, 3058.00, 11, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(65, 10, '2026-02-05', 88, 84, 87, 50, 6, 2, 2408.00, 10, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(66, 10, '2026-02-06', 76, 79, 67, 48, 10, 1, 6653.00, 3, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(67, 10, '2026-02-07', 71, 64, 72, 48, 10, 3, 5382.00, 14, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(68, 10, '2026-02-08', 82, 75, 73, 60, 10, 1, 5577.00, 5, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(69, 10, '2026-02-09', 20, 80, 64, 46, 12, 3, 4344.00, 7, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(70, 10, '2026-02-10', 75, 0, 70, 19, 9, 3, 4845.00, 15, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(71, 10, '2026-02-11', 33, 66, 66, 26, 7, 0, 9447.00, 11, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(72, 10, '2026-02-12', 33, 0, 59, 8, 13, 0, 7698.00, 6, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(73, 10, '2026-02-13', 66, 33, 75, 22, 15, 2, 7216.00, 6, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(74, 10, '2026-02-14', 75, 25, 81, 30, 0, 3, 5866.00, 20, '2026-02-15 16:21:39', '2026-02-15 16:21:39'),
(75, 10, '2026-02-15', 0, 75, 57, 24, 3, 2, 4423.00, 8, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(76, 11, '2026-02-01', 87, 67, 89, 63, 11, 1, 1487.00, 10, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(77, 11, '2026-02-02', 76, 70, 39, 69, 10, 1, 2112.00, 11, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(78, 11, '2026-02-03', 78, 84, 44, 40, 1, 1, 8971.00, 3, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(79, 11, '2026-02-04', 62, 74, 50, 42, 7, 0, 1021.00, 18, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(80, 11, '2026-02-05', 79, 89, 68, 90, 6, 0, 9598.00, 7, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(81, 11, '2026-02-06', 84, 62, 44, 82, 0, 2, 5641.00, 4, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(82, 11, '2026-02-07', 72, 90, 85, 44, 13, 3, 1615.00, 18, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(83, 11, '2026-02-08', 75, 84, 72, 46, 7, 0, 5174.00, 6, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(84, 11, '2026-02-09', 0, 0, 65, 0, 1, 1, 7777.00, 16, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(85, 11, '2026-02-10', 0, 66, 32, 20, 8, 2, 9685.00, 3, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(86, 11, '2026-02-11', 0, 33, 54, 10, 0, 3, 1203.00, 6, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(87, 11, '2026-02-12', 25, 25, 50, 11, 6, 3, 1854.00, 1, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(88, 11, '2026-02-13', 0, 25, 33, 5, 12, 3, 8997.00, 4, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(89, 11, '2026-02-14', 0, 50, 30, 18, 10, 0, 1199.00, 18, '2026-02-15 16:21:40', '2026-02-15 16:21:40'),
(90, 11, '2026-02-15', 75, 25, 37, 29, 5, 2, 2509.00, 7, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(91, 12, '2026-02-01', 60, 89, 70, 62, 4, 3, 6375.00, 18, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(92, 12, '2026-02-02', 79, 86, 30, 75, 11, 1, 6585.00, 19, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(93, 12, '2026-02-03', 72, 86, 67, 62, 12, 0, 6513.00, 5, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(94, 12, '2026-02-04', 62, 84, 37, 71, 7, 3, 7523.00, 1, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(95, 12, '2026-02-05', 61, 63, 48, 57, 13, 1, 6816.00, 8, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(96, 12, '2026-02-06', 65, 62, 36, 70, 15, 2, 8036.00, 16, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(97, 12, '2026-02-07', 67, 81, 56, 81, 8, 0, 5011.00, 1, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(98, 12, '2026-02-08', 84, 82, 67, 71, 8, 1, 2027.00, 12, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(99, 12, '2026-02-09', 20, 60, 49, 34, 5, 3, 1357.00, 7, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(100, 12, '2026-02-10', 40, 40, 51, 27, 1, 0, 9212.00, 9, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(101, 12, '2026-02-11', 60, 40, 62, 39, 3, 1, 7864.00, 19, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(102, 12, '2026-02-12', 60, 0, 85, 20, 11, 2, 2601.00, 13, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(103, 12, '2026-02-13', 0, 66, 78, 25, 9, 2, 2598.00, 19, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(104, 12, '2026-02-14', 25, 50, 37, 24, 11, 0, 1138.00, 14, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(105, 12, '2026-02-15', 0, 50, 39, 13, 0, 2, 263.00, 1, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(106, 13, '2026-02-01', 89, 83, 68, 77, 11, 3, 1878.00, 18, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(107, 13, '2026-02-02', 79, 83, 83, 60, 6, 0, 9896.00, 1, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(108, 13, '2026-02-03', 62, 68, 60, 78, 14, 1, 3368.00, 5, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(109, 13, '2026-02-04', 74, 90, 64, 80, 1, 3, 9774.00, 9, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(110, 13, '2026-02-05', 66, 74, 81, 69, 9, 1, 644.00, 9, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(111, 13, '2026-02-06', 86, 85, 67, 62, 15, 1, 482.00, 19, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(112, 13, '2026-02-07', 83, 86, 81, 79, 2, 2, 4911.00, 16, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(113, 13, '2026-02-08', 64, 76, 76, 79, 15, 1, 3676.00, 17, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(114, 13, '2026-02-09', 25, 50, 40, 31, 2, 0, 9134.00, 19, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(115, 13, '2026-02-10', 0, 100, 76, 31, 4, 0, 9882.00, 16, '2026-02-15 16:21:41', '2026-02-15 16:21:41'),
(116, 13, '2026-02-11', 0, 75, 72, 28, 14, 3, 8295.00, 5, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(117, 13, '2026-02-12', 40, 60, 61, 40, 15, 0, 5841.00, 3, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(118, 13, '2026-02-13', 33, 66, 81, 18, 7, 1, 6284.00, 2, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(119, 13, '2026-02-14', 40, 20, 42, 20, 3, 3, 6537.00, 20, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(120, 13, '2026-02-15', 20, 80, 39, 42, 4, 2, 39.00, 10, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(121, 14, '2026-02-01', 80, 83, 38, 79, 2, 0, 9261.00, 8, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(122, 14, '2026-02-02', 82, 64, 79, 86, 13, 0, 3000.00, 8, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(123, 14, '2026-02-03', 64, 62, 41, 90, 2, 1, 3672.00, 12, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(124, 14, '2026-02-04', 60, 62, 58, 88, 7, 3, 2961.00, 7, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(125, 14, '2026-02-05', 88, 79, 46, 68, 0, 2, 7802.00, 18, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(126, 14, '2026-02-06', 63, 88, 48, 83, 0, 2, 4670.00, 20, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(127, 14, '2026-02-07', 75, 62, 87, 85, 3, 0, 7012.00, 4, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(128, 14, '2026-02-08', 90, 84, 64, 78, 0, 2, 8395.00, 3, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(129, 14, '2026-02-09', 33, 0, 90, 6, 11, 3, 7534.00, 10, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(130, 14, '2026-02-10', 0, 66, 72, 16, 4, 1, 4861.00, 18, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(131, 14, '2026-02-11', 33, 33, 82, 15, 5, 3, 5805.00, 1, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(132, 14, '2026-02-12', 20, 80, 47, 41, 11, 3, 3800.00, 11, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(133, 14, '2026-02-13', 60, 20, 75, 31, 1, 0, 8356.00, 5, '2026-02-15 16:21:42', '2026-02-15 16:21:42'),
(134, 14, '2026-02-14', 20, 40, 83, 28, 8, 0, 6871.00, 20, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(135, 14, '2026-02-15', 20, 40, 38, 26, 6, 3, 8330.00, 6, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(136, 15, '2026-02-01', 69, 84, 81, 74, 12, 3, 3496.00, 11, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(137, 15, '2026-02-02', 79, 88, 41, 69, 6, 2, 6979.00, 17, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(138, 15, '2026-02-03', 88, 75, 83, 68, 12, 3, 8842.00, 7, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(139, 15, '2026-02-04', 63, 76, 68, 68, 8, 2, 3117.00, 12, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(140, 15, '2026-02-05', 89, 68, 66, 90, 3, 3, 4245.00, 16, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(141, 15, '2026-02-06', 79, 74, 80, 64, 7, 3, 8743.00, 14, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(142, 15, '2026-02-07', 77, 83, 69, 80, 12, 2, 8078.00, 8, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(143, 15, '2026-02-08', 70, 69, 43, 89, 12, 0, 7616.00, 15, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(144, 15, '2026-02-09', 0, 80, 58, 29, 10, 1, 4833.00, 12, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(145, 15, '2026-02-10', 0, 100, 88, 35, 0, 2, 5380.00, 10, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(146, 15, '2026-02-11', 20, 40, 85, 29, 2, 1, 9258.00, 13, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(147, 15, '2026-02-12', 0, 50, 60, 23, 3, 1, 8181.00, 15, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(148, 15, '2026-02-13', 0, 60, 34, 26, 7, 1, 462.00, 18, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(149, 15, '2026-02-14', 0, 66, 67, 15, 11, 0, 8136.00, 11, '2026-02-15 16:21:43', '2026-02-15 16:21:43'),
(150, 15, '2026-02-15', 50, 50, 82, 37, 4, 2, 5247.00, 1, '2026-02-15 16:21:43', '2026-02-15 16:21:43');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('ekT4BZT2byOO9MIpVMUahpv5Y5E7jl31MnyU6U4d', NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNFhtT05ueElvSlo4enpPdlB6ZW5QZGI4WWRkVmFvQm5JdkVWYlUzSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1771172675),
('Qeg6VzFc7wnyKwwup41p5dENgbWAoyNuaQiZnlxC', NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUmtObHFiTXE1SDNtWjN2VzFwV2xXYThZV1hiaE5yYXRIU0NzYnZuUSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1771172667),
('YrQDXoZYjcR6bQPvxdg8h71O62ijs9PFSzcvZsHe', NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWFBJd3hRMFFYc0VWbHVzbjVTdHc0TXF2SWVTeXFtcmRGRk1GYWpZUiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1771172672);

-- --------------------------------------------------------

--
-- Table structure for table `subscription_packages`
--

CREATE TABLE `subscription_packages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tier_level` int NOT NULL DEFAULT '0',
  `price_monthly` decimal(10,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `features` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_packages`
--

INSERT INTO `subscription_packages` (`id`, `name`, `tier_level`, `price_monthly`, `description`, `features`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Free', 0, 0.00, 'Standard behavioral tracking.', '[\"Activity Log\"]', 1, '2026-02-15 16:21:35', '2026-02-15 16:21:35'),
(2, 'Silver', 1, 49.00, 'Advanced execution tools and deeper analytics.', '[\"Market Analytics\", \"Priority Support\"]', 1, '2026-02-15 16:21:35', '2026-02-15 16:21:35'),
(3, 'Gold', 2, 99.00, 'Maximum performance infrastructure for elite operators.', '[\"Pro Mastermind Access\", \"dedicated account manager\"]', 1, '2026-02-15 16:21:35', '2026-02-15 16:21:35'),
(4, 'Diamond', 3, 299.00, 'The ultimate status for industry icons.', '[\"Inner Circle Access\", \"Personal Coaching\", \"Unlimited Resources\"]', 1, '2026-02-15 16:21:35', '2026-02-15 16:21:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brokerage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `years_experience` int DEFAULT NULL,
  `current_monthly_income` decimal(12,2) DEFAULT NULL,
  `target_monthly_income` decimal(12,2) DEFAULT NULL,
  `is_profile_complete` tinyint(1) NOT NULL DEFAULT '0',
  `has_completed_diagnosis` tinyint(1) NOT NULL DEFAULT '0',
  `diagnosis_blocker` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diagnosis_scores` json DEFAULT NULL,
  `growth_score` int NOT NULL DEFAULT '0',
  `execution_rate` int NOT NULL DEFAULT '0',
  `mindset_index` int NOT NULL DEFAULT '0',
  `rank` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `membership_tier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Free',
  `current_streak` int NOT NULL DEFAULT '0',
  `last_activity_date` date DEFAULT NULL,
  `is_premium` tinyint(1) NOT NULL DEFAULT '0',
  `premium_expires_at` timestamp NULL DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `profile_photo_path`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `mobile`, `city`, `brokerage`, `instagram`, `linkedin`, `years_experience`, `current_monthly_income`, `target_monthly_income`, `is_profile_complete`, `has_completed_diagnosis`, `diagnosis_blocker`, `diagnosis_scores`, `growth_score`, `execution_rate`, `mindset_index`, `rank`, `membership_tier`, `current_streak`, `last_activity_date`, `is_premium`, `premium_expires_at`, `phone_number`, `license_number`) VALUES
(1, 'Root Admin', 'admin@realtorone.com', NULL, NULL, '$2y$04$rhRxGVGHEhwW4d.gCauAmetSLyl.CVlmKL1xypwFVIYjAIrqsaVn6', NULL, '2026-02-15 16:21:35', '2026-02-15 16:21:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0, 0, NULL, 'Gold', 0, NULL, 0, NULL, NULL, NULL),
(2, 'Elite Practitioner', 'myname@gmail.com', NULL, NULL, '$2y$04$0xLJPKTxSXCT7dSQprF1g.4C7855aL4oxcycvPy2CPb2lFLD9zCo.', NULL, '2026-02-15 16:21:35', '2026-02-15 16:21:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 92, \"mindset\": 94, \"branding\": 95, \"lead_gen\": 88}', 94, 91, 9, NULL, 'Gold', 18, NULL, 1, NULL, NULL, NULL),
(3, 'Growth Operator', 'realtorone@example.com', NULL, NULL, '$2y$04$vzXtf6dYPX4W0eDGnBQ0iewDPm3/jITGFGw/7QSIg0dUB5hJopQ1a', NULL, '2026-02-15 16:21:35', '2026-02-15 16:21:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 65, \"mindset\": 80, \"branding\": 70, \"lead_gen\": 75}', 76, 68, 7, NULL, 'Silver', 8, NULL, 1, NULL, NULL, NULL),
(4, 'New Practitioner', 'realtortwo@example.com', NULL, NULL, '$2y$04$bcRVEVQH/vGRUqfjcjzWHONMNzEfUrEQMVP3MbqnbFiZBDEqe1K1q', NULL, '2026-02-15 16:21:35', '2026-02-15 16:21:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 15, \"mindset\": 40, \"branding\": 30, \"lead_gen\": 20}', 32, 25, 4, NULL, 'Free', 2, NULL, 0, NULL, NULL, NULL),
(5, 'Iconic Leader', 'diamond@example.com', NULL, NULL, '$2y$04$QwHPvCsKiVZ4M6ILfFlBquY1BnU5AeB72tOLujXnktkJDtUKrpN.S', NULL, '2026-02-15 16:21:35', '2026-02-15 16:21:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 97, \"mindset\": 100, \"branding\": 99, \"lead_gen\": 98}', 99, 98, 10, NULL, 'Diamond', 365, NULL, 1, NULL, NULL, NULL),
(6, 'James Rodriguez', 'james.rodriguez@example.com', NULL, NULL, '$2y$04$1w8vYXKPj1uqXlzv4Jx7T.JxMiFGybkkmZDRFyQxfQTK.GPGK5hDK', NULL, '2026-02-15 16:21:36', '2026-02-15 16:21:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 73, \"mindset\": 78, \"branding\": 39, \"lead_gen\": 68}', 44, 81, 8, NULL, 'Silver', 6, NULL, 1, NULL, '+14021765528', 'RE27142'),
(7, 'Sarah Chen', 'sarah.chen@example.com', NULL, NULL, '$2y$04$SIkCzLCB94/Q56YervIbcONB2CHjBrNm0RQILjpXbJgpHS/TUDnsC', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 31, \"mindset\": 90, \"branding\": 57, \"lead_gen\": 53}', 50, 71, 9, NULL, 'Gold', 15, NULL, 1, NULL, '+17946727679', 'RE57619'),
(8, 'Michael Olayinka', 'michael.olayinka@example.com', NULL, NULL, '$2y$04$NWjN19n6Ry4NS2jbkjaGQ.mgD8vEvp6b8tH8jB6n3/jq0PMqABksu', NULL, '2026-02-15 16:21:37', '2026-02-15 16:21:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 55, \"mindset\": 30, \"branding\": 33, \"lead_gen\": 100}', 41, 62, 9, NULL, 'Diamond', 6, NULL, 1, NULL, '+13095436226', 'RE33218'),
(9, 'Elena Petrova', 'elena.petrova@example.com', NULL, NULL, '$2y$04$uPtxc8RD0s3fj/lz21Zio.e8uxXt2wkUrq.qakFXkt.rtxQg5XHGe', NULL, '2026-02-15 16:21:38', '2026-02-15 16:21:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 39, \"mindset\": 57, \"branding\": 41, \"lead_gen\": 32}', 35, 91, 8, NULL, 'Free', 18, NULL, 0, NULL, '+19166638019', 'RE25576'),
(10, 'David Wilson', 'david.wilson@example.com', NULL, NULL, '$2y$04$bXY2NeQQ4AALz3xUGIZSOODRGmJMnQv5WI8/a8PIP9maaFGzkvWhu', NULL, '2026-02-15 16:21:39', '2026-02-15 16:21:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 63, \"mindset\": 67, \"branding\": 78, \"lead_gen\": 94}', 83, 80, 10, NULL, 'Silver', 22, NULL, 1, NULL, '+13119812402', 'RE19553'),
(11, 'Aria Gupta', 'aria.gupta@example.com', NULL, NULL, '$2y$04$9HXL/GaPDxuXv5PEFZHjtuFmMKCSO9D1FbkFH83XQAhNvy/ImHbbq', NULL, '2026-02-15 16:21:40', '2026-02-15 16:21:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 47, \"mindset\": 61, \"branding\": 31, \"lead_gen\": 57}', 42, 61, 6, NULL, 'Gold', 10, NULL, 1, NULL, '+13522361918', 'RE43768'),
(12, 'Liam O\'Shea', 'liam.o\'shea@example.com', NULL, NULL, '$2y$04$Qz2aT4sndsvazvkLq03ee.kciGKOZk9p5hVU4EqwmHv9gVhMjAe1C', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 95, \"mindset\": 46, \"branding\": 39, \"lead_gen\": 63}', 53, 66, 10, NULL, 'Diamond', 23, NULL, 1, NULL, '+12878823502', 'RE59450'),
(13, 'Sophia Kim', 'sophia.kim@example.com', NULL, NULL, '$2y$04$5oQLGcKGCvTLhLiwvsVGVea3q4fJSpMyWFl2D1eOq053eYobb7LPi', NULL, '2026-02-15 16:21:41', '2026-02-15 16:21:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 70, \"mindset\": 84, \"branding\": 59, \"lead_gen\": 71}', 38, 51, 6, NULL, 'Free', 28, NULL, 0, NULL, '+19158661341', 'RE15152'),
(14, 'Lucas Silva', 'lucas.silva@example.com', NULL, NULL, '$2y$04$FO.DQif7iziEfKrRRPhZ3urgYKwluOmWg1NFaLC5GDXLOtkjsK.9C', NULL, '2026-02-15 16:21:42', '2026-02-15 16:21:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 95, \"mindset\": 99, \"branding\": 57, \"lead_gen\": 93}', 30, 90, 8, NULL, 'Silver', 21, NULL, 1, NULL, '+13664699669', 'RE98246'),
(15, 'Emma Watson', 'emma.watson@example.com', NULL, NULL, '$2y$04$YSTFL6ER57g0xXso6dgyGeJWUErY1oEcyzFPJrIWthMhmk8NaUFuy', NULL, '2026-02-15 16:21:43', '2026-02-15 16:21:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '{\"sales\": 35, \"mindset\": 49, \"branding\": 70, \"lead_gen\": 94}', 46, 90, 6, NULL, 'Gold', 23, NULL, 1, NULL, '+12617824761', 'RE66912');

-- --------------------------------------------------------

--
-- Table structure for table `user_learning_progress`
--

CREATE TABLE `user_learning_progress` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `learning_content_id` bigint UNSIGNED NOT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `progress_percent` int NOT NULL DEFAULT '0',
  `last_accessed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

CREATE TABLE `user_subscriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `package_id` bigint UNSIGNED NOT NULL,
  `started_at` timestamp NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paypal',
  `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_subscriptions`
--

INSERT INTO `user_subscriptions` (`id`, `user_id`, `package_id`, `started_at`, `expires_at`, `status`, `payment_method`, `payment_id`, `amount_paid`, `coupon_id`, `created_at`, `updated_at`) VALUES
(1, 2, 3, '2026-02-15 16:21:35', '2027-02-15 16:21:35', 'active', 'stripe', 'SEED_GOLD_1771172495', 99.00, NULL, '2026-02-15 16:21:35', '2026-02-15 16:21:35'),
(2, 3, 2, '2026-02-15 16:21:35', '2026-03-15 16:21:35', 'active', 'paypal', 'SEED_SILVER_1771172495', 49.00, NULL, '2026-02-15 16:21:35', '2026-02-15 16:21:35'),
(3, 5, 4, '2026-02-15 16:21:35', '2027-02-15 16:21:35', 'active', 'stripe', 'SEED_DIAMOND_1771172495', 299.00, NULL, '2026-02-15 16:21:35', '2026-02-15 16:21:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activities_user_id_scheduled_at_index` (`user_id`,`scheduled_at`),
  ADD KEY `activities_user_id_is_completed_index` (`user_id`,`is_completed`);

--
-- Indexes for table `activity_types`
--
ALTER TABLE `activity_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `activity_types_type_key_unique` (`type_key`),
  ADD KEY `activity_types_user_id_foreign` (`user_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `learning_content`
--
ALTER TABLE `learning_content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `learning_content_category_tier_index` (`category`,`tier`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `performance_metrics`
--
ALTER TABLE `performance_metrics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `performance_metrics_user_id_date_unique` (`user_id`,`date`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `subscription_packages`
--
ALTER TABLE `subscription_packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_learning_progress`
--
ALTER TABLE `user_learning_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_learning_progress_user_id_learning_content_id_unique` (`user_id`,`learning_content_id`),
  ADD KEY `user_learning_progress_learning_content_id_foreign` (`learning_content_id`);

--
-- Indexes for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_subscriptions_user_id_foreign` (`user_id`),
  ADD KEY `user_subscriptions_package_id_foreign` (`package_id`),
  ADD KEY `user_subscriptions_coupon_id_foreign` (`coupon_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=280;

--
-- AUTO_INCREMENT for table `activity_types`
--
ALTER TABLE `activity_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `learning_content`
--
ALTER TABLE `learning_content`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `performance_metrics`
--
ALTER TABLE `performance_metrics`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `subscription_packages`
--
ALTER TABLE `subscription_packages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_learning_progress`
--
ALTER TABLE `user_learning_progress`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `activity_types`
--
ALTER TABLE `activity_types`
  ADD CONSTRAINT `activity_types_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `performance_metrics`
--
ALTER TABLE `performance_metrics`
  ADD CONSTRAINT `performance_metrics_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_learning_progress`
--
ALTER TABLE `user_learning_progress`
  ADD CONSTRAINT `user_learning_progress_learning_content_id_foreign` FOREIGN KEY (`learning_content_id`) REFERENCES `learning_content` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_learning_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD CONSTRAINT `user_subscriptions_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_subscriptions_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `subscription_packages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
