-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2026 at 10:08 PM
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
-- Database: `smart_lms`
--

-- --------------------------------------------------------

--
-- Table structure for table `adaptive_learning_paths`
--

CREATE TABLE `adaptive_learning_paths` (
  `path_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `current_topic_id` int(11) DEFAULT NULL,
  `learning_style` enum('visual','auditory','kinesthetic','reading') DEFAULT 'visual',
  `current_difficulty` enum('foundation','intermediate','advanced') DEFAULT 'intermediate',
  `pace_preference` enum('slow','moderate','fast') DEFAULT 'moderate',
  `path_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`path_data`)),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_tutor_interactions`
--

CREATE TABLE `ai_tutor_interactions` (
  `interaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `query_text` text NOT NULL,
  `query_language` enum('en','zu','xh','af','st','tn','ts','ss','ve','nr','nso') DEFAULT 'en',
  `ai_response` text DEFAULT NULL,
  `response_type` enum('explanation','hint','solution','encouragement','resource') DEFAULT 'explanation',
  `context_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context_data`)),
  `helpfulness_rating` int(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_offline_generated` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_tutor_interactions`
--

INSERT INTO `ai_tutor_interactions` (`interaction_id`, `user_id`, `subject_id`, `query_text`, `query_language`, `ai_response`, `response_type`, `context_data`, `helpfulness_rating`, `created_at`, `is_offline_generated`) VALUES
(1, 6, NULL, 'jh', 'en', '🤖 Smart Tutor: I understand your question about \"jh\". Let\'s break it down step by step. Focus on key concepts and practice regularly.', 'explanation', NULL, NULL, '2026-03-30 19:35:41', 0),
(2, 6, NULL, 'hello', 'en', '🤖 Smart Tutor: I understand your question about \"hello\". Let\'s break it down step by step. Focus on key concepts and practice regularly.', 'explanation', NULL, NULL, '2026-03-30 19:35:49', 0),
(3, 6, NULL, 'Help me understand math better', 'en', '🤖 I\'m here to help! You can ask me about:\n• Specific concepts you\'re struggling with\n• Practice questions\n• Study tips and techniques\n• Explanations of difficult topics\n\nWhat do you need help with?', 'explanation', NULL, NULL, '2026-03-30 20:03:30', 0),
(4, 6, NULL, 'hello', 'en', '👋 Hello test! I\'m your AI tutor. What would you like to learn today?', 'explanation', NULL, NULL, '2026-03-30 20:07:50', 0);

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `assessment_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `assessment_type` enum('diagnostic','formative','summative','practice') DEFAULT 'practice',
  `title` varchar(255) NOT NULL,
  `instructions` text DEFAULT NULL,
  `time_limit_minutes` int(11) DEFAULT NULL,
  `max_attempts` int(11) DEFAULT 3,
  `passing_score` decimal(5,2) DEFAULT 50.00,
  `is_adaptive` tinyint(1) DEFAULT 1,
  `allows_offline` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessment_attempts`
--

CREATE TABLE `assessment_attempts` (
  `attempt_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `max_possible_score` decimal(5,2) DEFAULT NULL,
  `percentage_score` decimal(5,2) DEFAULT NULL,
  `time_taken_minutes` int(11) DEFAULT NULL,
  `was_offline_attempt` tinyint(1) DEFAULT 0,
  `synced_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `badge_id` int(11) NOT NULL,
  `badge_name` varchar(100) NOT NULL,
  `badge_description` text DEFAULT NULL,
  `badge_icon` varchar(255) DEFAULT NULL,
  `criteria_type` enum('score','streak','completion','mastery','helpfulness') NOT NULL,
  `criteria_value` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`badge_id`, `badge_name`, `badge_description`, `badge_icon`, `criteria_type`, `criteria_value`, `subject_id`) VALUES
(1, 'Einstein Award', 'Master 5 advanced physics concepts', NULL, 'mastery', 5, NULL),
(2, 'Math Whiz', 'Score 90% or above on 3 consecutive math quizzes', NULL, 'streak', 3, NULL),
(3, 'Helper Hero', 'Provide helpful answers to 10 peer questions', NULL, 'helpfulness', 10, NULL),
(4, 'Perfect Attendance', 'Login and study for 30 consecutive days', NULL, 'streak', 30, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `grade_level` int(11) NOT NULL,
  `class_name` varchar(100) DEFAULT NULL,
  `academic_year` int(11) DEFAULT year(curdate()),
  `term` int(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_enrollments`
--

CREATE TABLE `class_enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dropped_at` datetime DEFAULT NULL,
  `drop_reason` varchar(255) DEFAULT NULL,
  `final_grade` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `concept_mastery`
--

CREATE TABLE `concept_mastery` (
  `mastery_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `mastery_level` decimal(5,2) DEFAULT 0.00,
  `attempts_count` int(11) DEFAULT 0,
  `successful_attempts` int(11) DEFAULT 0,
  `last_attempt_at` datetime DEFAULT NULL,
  `estimated_time_to_master` int(11) DEFAULT NULL,
  `struggling_concepts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`struggling_concepts`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `content_downloads`
--

CREATE TABLE `content_downloads` (
  `download_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `downloaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_accessed` datetime DEFAULT NULL,
  `access_count` int(11) DEFAULT 0,
  `is_synced` tinyint(1) DEFAULT 0,
  `sync_completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `curriculum_topics`
--

CREATE TABLE `curriculum_topics` (
  `topic_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `topic_name` varchar(255) NOT NULL,
  `term` int(1) DEFAULT NULL,
  `week_number` int(11) DEFAULT NULL,
  `difficulty_level` enum('foundation','intermediate','advanced') DEFAULT 'intermediate',
  `estimated_hours` int(11) DEFAULT 2,
  `prerequisites` text DEFAULT NULL,
  `caps_reference` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `curriculum_topics`
--

INSERT INTO `curriculum_topics` (`topic_id`, `subject_id`, `topic_name`, `term`, `week_number`, `difficulty_level`, `estimated_hours`, `prerequisites`, `caps_reference`) VALUES
(1, 1, 'Quadratic Equations', 2, 5, 'intermediate', 2, NULL, 'MAT-12-02-05'),
(2, 1, 'Calculus - Differentiation', 3, 2, 'advanced', 2, NULL, 'MAT-12-03-02'),
(3, 2, 'Newton\'s Laws of Motion', 1, 4, 'intermediate', 2, NULL, 'PHY-12-01-04');

-- --------------------------------------------------------

--
-- Table structure for table `device_registrations`
--

CREATE TABLE `device_registrations` (
  `device_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_name` varchar(100) DEFAULT NULL,
  `device_type` enum('android','ios','windows','mac','linux','other') NOT NULL,
  `device_unique_id` varchar(255) DEFAULT NULL,
  `last_sync_at` datetime DEFAULT NULL,
  `offline_content_size_mb` decimal(10,2) DEFAULT 0.00,
  `is_primary_device` tinyint(1) DEFAULT 0,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `engagement_metrics`
--

CREATE TABLE `engagement_metrics` (
  `metric_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_date` date NOT NULL,
  `time_spent_minutes` int(11) DEFAULT 0,
  `modules_accessed` int(11) DEFAULT 0,
  `assessments_attempted` int(11) DEFAULT 0,
  `ai_interactions` int(11) DEFAULT 0,
  `offline_study_time` int(11) DEFAULT 0,
  `engagement_score` decimal(5,2) DEFAULT NULL,
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interventions`
--

CREATE TABLE `interventions` (
  `intervention_id` int(11) NOT NULL,
  `risk_id` int(11) NOT NULL,
  `intervention_type` enum('ai_tutor_extra','peer_tutoring','teacher_meeting','parent_contact','counseling','academic_support') NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `outcome` text DEFAULT NULL,
  `success_rating` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `learning_modules`
--

CREATE TABLE `learning_modules` (
  `module_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `module_type` enum('video','interactive','reading','quiz','simulation','worksheet') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content_url` varchar(500) DEFAULT NULL,
  `offline_path` varchar(500) DEFAULT NULL,
  `file_size_mb` decimal(8,2) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `language` enum('en','zu','xh','af','st','tn','ts','ss','ve','nr','nso') DEFAULT 'en',
  `difficulty_adaptive` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`difficulty_adaptive`)),
  `requires_internet` tinyint(1) DEFAULT 0,
  `download_allowed` tinyint(1) DEFAULT 1,
  `sync_status` enum('synced','pending','downloading','error') DEFAULT 'synced',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0,
  `failure_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`attempt_id`, `email`, `ip_address`, `attempted_at`, `success`, `failure_reason`) VALUES
(1, 'test@gmail.com', '::1', '2026-03-30 19:11:59', 1, NULL),
(2, 'test@gmail.com', '::1', '2026-03-30 19:27:27', 1, NULL),
(3, 'test@gmail.com', '::1', '2026-03-30 19:37:29', 1, NULL),
(4, 'test@gmail.com', '::1', '2026-03-30 19:53:26', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_type` enum('risk_alert','achievement','assignment','system','sync_complete') NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `action_url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_via` enum('app','sms','email','push') DEFAULT 'app',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offline_sync_queue`
--

CREATE TABLE `offline_sync_queue` (
  `queue_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `entity_type` enum('assessment_attempt','ai_interaction','progress','content_download') NOT NULL,
  `entity_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`entity_data`)),
  `local_created_at` datetime DEFAULT NULL,
  `sync_status` enum('pending','syncing','completed','failed') DEFAULT 'pending',
  `retry_count` int(11) DEFAULT 0,
  `last_error` text DEFAULT NULL,
  `synced_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parent_student_links`
--

CREATE TABLE `parent_student_links` (
  `link_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `relationship` enum('mother','father','guardian','other') NOT NULL,
  `can_view_grades` tinyint(1) DEFAULT 1,
  `can_view_attendance` tinyint(1) DEFAULT 1,
  `can_view_risk_alerts` tinyint(1) DEFAULT 1,
  `linked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer','numerical','matching','drag_drop') NOT NULL,
  `question_text` text NOT NULL,
  `question_media_url` varchar(500) DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `correct_answer` text DEFAULT NULL,
  `explanation` text DEFAULT NULL,
  `hint` text DEFAULT NULL,
  `difficulty_level` enum('foundation','intermediate','advanced') DEFAULT 'intermediate',
  `concept_tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`concept_tags`)),
  `marks` decimal(5,2) DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `risk_indicators`
--

CREATE TABLE `risk_indicators` (
  `risk_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `risk_level` enum('low','medium','high','critical') NOT NULL,
  `risk_factors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`risk_factors`)),
  `indicators_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`indicators_data`)),
  `detected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `intervention_status` enum('pending','in_progress','resolved','escalated') DEFAULT 'pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `school_id` int(11) NOT NULL,
  `school_name` varchar(255) NOT NULL,
  `school_type` enum('primary','secondary','combined','tvet','university','private') NOT NULL,
  `province` varchar(50) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `town` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `quintile` int(1) DEFAULT NULL,
  `internet_connectivity` enum('excellent','good','poor','none') DEFAULT 'poor',
  `has_electricity` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`school_id`, `school_name`, `school_type`, `province`, `district`, `town`, `address`, `contact_email`, `contact_phone`, `quintile`, `internet_connectivity`, `has_electricity`, `created_at`, `is_active`) VALUES
(1, 'Soweto High School', 'secondary', 'GP', 'Johannesburg Central', NULL, NULL, NULL, NULL, 3, 'poor', 1, '2026-03-30 18:51:52', 1),
(2, 'Cape Town Academy', 'secondary', 'WC', 'Cape Town Metro', NULL, NULL, NULL, NULL, 5, 'good', 1, '2026-03-30 18:51:52', 1),
(3, 'Rural Eastern Cape Primary', 'primary', 'EC', 'Mthatha', NULL, NULL, NULL, NULL, 1, 'none', 1, '2026-03-30 18:51:52', 1),
(4, 'Durban University of Technology', 'university', 'KZN', 'Durban', NULL, NULL, NULL, NULL, NULL, 'excellent', 1, '2026-03-30 18:51:52', 1);

-- --------------------------------------------------------

--
-- Table structure for table `school_admins`
--

CREATE TABLE `school_admins` (
  `school_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_primary_admin` tinyint(1) DEFAULT 0,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `grade_level` int(11) NOT NULL,
  `phase` enum('GET','FET','university') NOT NULL,
  `description` text DEFAULT NULL,
  `is_core_subject` tinyint(1) DEFAULT 0,
  `credits` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `subject_code`, `grade_level`, `phase`, `description`, `is_core_subject`, `credits`) VALUES
(1, 'Mathematics', 'MATH', 12, 'FET', NULL, 1, 1),
(2, 'Physical Sciences', 'PHSC', 12, 'FET', NULL, 1, 1),
(3, 'Life Sciences', 'LISC', 12, 'FET', NULL, 0, 1),
(4, 'English Home Language', 'ENHL', 12, 'FET', NULL, 1, 1),
(5, 'isiZulu Home Language', 'ZUHL', 12, 'FET', NULL, 1, 1),
(6, 'Computer Applications Technology', 'CAT', 12, 'FET', NULL, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_lesson_plans`
--

CREATE TABLE `teacher_lesson_plans` (
  `plan_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `planned_date` date DEFAULT NULL,
  `actual_date` date DEFAULT NULL,
  `lesson_objectives` text DEFAULT NULL,
  `materials_needed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`materials_needed`)),
  `differentiation_strategy` text DEFAULT NULL,
  `reflection_notes` text DEFAULT NULL,
  `completion_status` enum('planned','completed','postponed') DEFAULT 'planned'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin','parent') DEFAULT 'student',
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `preferred_language` enum('en','zu','xh','af','st','tn','ts','ss','ve','nr','nso') DEFAULT 'en',
  `grade_level` int(11) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  `province` enum('EC','FS','GP','KZN','LP','MP','NC','NW','WC','Unknown') DEFAULT 'Unknown',
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `offline_access_enabled` tinyint(1) DEFAULT 1,
  `data_saver_mode` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `first_name`, `last_name`, `phone`, `preferred_language`, `grade_level`, `school_id`, `province`, `is_active`, `email_verified`, `last_login`, `created_at`, `updated_at`, `offline_access_enabled`, `data_saver_mode`) VALUES
(1, 'admin@smartlms.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator', NULL, 'en', NULL, NULL, 'Unknown', 1, 1, NULL, '2026-03-30 18:51:53', '2026-03-30 18:51:53', 1, 0),
(2, 'teacher@smartlms.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Thabo', 'Mokoena', NULL, 'zu', 12, 1, 'Unknown', 1, 0, NULL, '2026-03-30 18:51:53', '2026-03-30 18:51:53', 1, 0),
(3, 'student1@smartlms.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Lerato', 'Ndlovu', NULL, 'zu', 12, 1, 'GP', 1, 0, NULL, '2026-03-30 18:51:53', '2026-03-30 18:51:53', 1, 0),
(4, 'student2@smartlms.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Pieter', 'Van Wyk', NULL, 'af', 11, 2, 'WC', 1, 0, NULL, '2026-03-30 18:51:53', '2026-03-30 18:51:53', 1, 0),
(5, 'student3@smartlms.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Sipho', 'Dlamini', NULL, 'zu', 10, 1, 'GP', 1, 0, NULL, '2026-03-30 18:51:53', '2026-03-30 18:51:53', 1, 0),
(6, 'test@gmail.com', '$2y$10$Iupp1TWLSFmXGrbA.887B.J6HRWwPOiOY8YOL3ArnT0XPMdTNhFB.', 'student', 'test', 'test', NULL, 'en', 8, 2, 'GP', 1, 0, '2026-03-30 21:53:26', '2026-03-30 19:11:47', '2026-03-30 19:53:26', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_answers`
--

CREATE TABLE `user_answers` (
  `answer_id` int(11) NOT NULL,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_answer` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `marks_obtained` decimal(5,2) DEFAULT NULL,
  `time_spent_seconds` int(11) DEFAULT NULL,
  `ai_feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_badges`
--

CREATE TABLE `user_badges` (
  `user_badge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_points`
--

CREATE TABLE `user_points` (
  `point_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `points` int(11) DEFAULT 0,
  `points_type` enum('study','quiz','streak','helpfulness','mastery') DEFAULT 'study',
  `description` varchar(255) DEFAULT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_offline_valid` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`session_id`, `user_id`, `ip_address`, `user_agent`, `created_at`, `expires_at`, `is_offline_valid`) VALUES
('14460ea4a6acb49e66809bdf3678daefac462556c2eae20e1466fe719185ed45', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-30 19:37:29', '2026-03-31 21:37:29', 1),
('3fefd1dcb7c2374336048fece65a226744113af5657f475e9a55b046d04a9c76', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-30 19:53:26', '2026-03-31 21:53:26', 1),
('b65c8178b7cefbbae79b975100d21947ef53bbe41b1fbbb00af9b4a9bc40fa40', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-30 19:12:00', '2026-03-31 21:12:00', 1),
('e4796c086cd9d5b38b07fe7467afe773791e6ac1ce404632399f1505cd6ac30f', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-30 19:27:27', '2026-03-31 21:27:27', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adaptive_learning_paths`
--
ALTER TABLE `adaptive_learning_paths`
  ADD PRIMARY KEY (`path_id`),
  ADD UNIQUE KEY `unique_user_subject` (`user_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `current_topic_id` (`current_topic_id`);

--
-- Indexes for table `ai_tutor_interactions`
--
ALTER TABLE `ai_tutor_interactions`
  ADD PRIMARY KEY (`interaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`assessment_id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `assessment_attempts`
--
ALTER TABLE `assessment_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`badge_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `class_enrollments`
--
ALTER TABLE `class_enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `unique_class_student` (`class_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `concept_mastery`
--
ALTER TABLE `concept_mastery`
  ADD PRIMARY KEY (`mastery_id`),
  ADD UNIQUE KEY `unique_user_topic` (`user_id`,`topic_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `content_downloads`
--
ALTER TABLE `content_downloads`
  ADD PRIMARY KEY (`download_id`),
  ADD UNIQUE KEY `unique_user_module` (`user_id`,`module_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `curriculum_topics`
--
ALTER TABLE `curriculum_topics`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `device_registrations`
--
ALTER TABLE `device_registrations`
  ADD PRIMARY KEY (`device_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `engagement_metrics`
--
ALTER TABLE `engagement_metrics`
  ADD PRIMARY KEY (`metric_id`),
  ADD UNIQUE KEY `unique_user_date` (`user_id`,`login_date`),
  ADD KEY `idx_engagement_user_date` (`user_id`,`login_date`);

--
-- Indexes for table `interventions`
--
ALTER TABLE `interventions`
  ADD PRIMARY KEY (`intervention_id`),
  ADD KEY `risk_id` (`risk_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `learning_modules`
--
ALTER TABLE `learning_modules`
  ADD PRIMARY KEY (`module_id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `offline_sync_queue`
--
ALTER TABLE `offline_sync_queue`
  ADD PRIMARY KEY (`queue_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `parent_student_links`
--
ALTER TABLE `parent_student_links`
  ADD PRIMARY KEY (`link_id`),
  ADD UNIQUE KEY `unique_parent_student` (`parent_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `risk_indicators`
--
ALTER TABLE `risk_indicators`
  ADD PRIMARY KEY (`risk_id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_risk_user` (`user_id`,`detected_at`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`school_id`);

--
-- Indexes for table `school_admins`
--
ALTER TABLE `school_admins`
  ADD PRIMARY KEY (`school_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `teacher_lesson_plans`
--
ALTER TABLE `teacher_lesson_plans`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_school` (`school_id`);

--
-- Indexes for table `user_answers`
--
ALTER TABLE `user_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `attempt_id` (`attempt_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD PRIMARY KEY (`user_badge_id`),
  ADD UNIQUE KEY `unique_user_badge` (`user_id`,`badge_id`),
  ADD KEY `badge_id` (`badge_id`);

--
-- Indexes for table `user_points`
--
ALTER TABLE `user_points`
  ADD PRIMARY KEY (`point_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adaptive_learning_paths`
--
ALTER TABLE `adaptive_learning_paths`
  MODIFY `path_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_tutor_interactions`
--
ALTER TABLE `ai_tutor_interactions`
  MODIFY `interaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assessment_attempts`
--
ALTER TABLE `assessment_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `badge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_enrollments`
--
ALTER TABLE `class_enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `concept_mastery`
--
ALTER TABLE `concept_mastery`
  MODIFY `mastery_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `content_downloads`
--
ALTER TABLE `content_downloads`
  MODIFY `download_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curriculum_topics`
--
ALTER TABLE `curriculum_topics`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `device_registrations`
--
ALTER TABLE `device_registrations`
  MODIFY `device_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `engagement_metrics`
--
ALTER TABLE `engagement_metrics`
  MODIFY `metric_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interventions`
--
ALTER TABLE `interventions`
  MODIFY `intervention_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `learning_modules`
--
ALTER TABLE `learning_modules`
  MODIFY `module_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offline_sync_queue`
--
ALTER TABLE `offline_sync_queue`
  MODIFY `queue_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parent_student_links`
--
ALTER TABLE `parent_student_links`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `risk_indicators`
--
ALTER TABLE `risk_indicators`
  MODIFY `risk_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `school_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teacher_lesson_plans`
--
ALTER TABLE `teacher_lesson_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_answers`
--
ALTER TABLE `user_answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_badges`
--
ALTER TABLE `user_badges`
  MODIFY `user_badge_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_points`
--
ALTER TABLE `user_points`
  MODIFY `point_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adaptive_learning_paths`
--
ALTER TABLE `adaptive_learning_paths`
  ADD CONSTRAINT `adaptive_learning_paths_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adaptive_learning_paths_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adaptive_learning_paths_ibfk_3` FOREIGN KEY (`current_topic_id`) REFERENCES `curriculum_topics` (`topic_id`);

--
-- Constraints for table `ai_tutor_interactions`
--
ALTER TABLE `ai_tutor_interactions`
  ADD CONSTRAINT `ai_tutor_interactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_tutor_interactions_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `curriculum_topics` (`topic_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `assessment_attempts`
--
ALTER TABLE `assessment_attempts`
  ADD CONSTRAINT `assessment_attempts_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`assessment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_attempts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `badges`
--
ALTER TABLE `badges`
  ADD CONSTRAINT `badges_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `class_enrollments`
--
ALTER TABLE `class_enrollments`
  ADD CONSTRAINT `class_enrollments_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_enrollments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `concept_mastery`
--
ALTER TABLE `concept_mastery`
  ADD CONSTRAINT `concept_mastery_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `concept_mastery_ibfk_2` FOREIGN KEY (`topic_id`) REFERENCES `curriculum_topics` (`topic_id`) ON DELETE CASCADE;

--
-- Constraints for table `content_downloads`
--
ALTER TABLE `content_downloads`
  ADD CONSTRAINT `content_downloads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_downloads_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `learning_modules` (`module_id`) ON DELETE CASCADE;

--
-- Constraints for table `curriculum_topics`
--
ALTER TABLE `curriculum_topics`
  ADD CONSTRAINT `curriculum_topics_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `device_registrations`
--
ALTER TABLE `device_registrations`
  ADD CONSTRAINT `device_registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `engagement_metrics`
--
ALTER TABLE `engagement_metrics`
  ADD CONSTRAINT `engagement_metrics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `interventions`
--
ALTER TABLE `interventions`
  ADD CONSTRAINT `interventions_ibfk_1` FOREIGN KEY (`risk_id`) REFERENCES `risk_indicators` (`risk_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interventions_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `learning_modules`
--
ALTER TABLE `learning_modules`
  ADD CONSTRAINT `learning_modules_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `curriculum_topics` (`topic_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `learning_modules_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `offline_sync_queue`
--
ALTER TABLE `offline_sync_queue`
  ADD CONSTRAINT `offline_sync_queue_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `parent_student_links`
--
ALTER TABLE `parent_student_links`
  ADD CONSTRAINT `parent_student_links_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parent_student_links_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`assessment_id`) ON DELETE CASCADE;

--
-- Constraints for table `risk_indicators`
--
ALTER TABLE `risk_indicators`
  ADD CONSTRAINT `risk_indicators_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `risk_indicators_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `school_admins`
--
ALTER TABLE `school_admins`
  ADD CONSTRAINT `school_admins_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `school_admins_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_lesson_plans`
--
ALTER TABLE `teacher_lesson_plans`
  ADD CONSTRAINT `teacher_lesson_plans_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_lesson_plans_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_lesson_plans_ibfk_3` FOREIGN KEY (`topic_id`) REFERENCES `curriculum_topics` (`topic_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_answers`
--
ALTER TABLE `user_answers`
  ADD CONSTRAINT `user_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `assessment_attempts` (`attempt_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`badge_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_points`
--
ALTER TABLE `user_points`
  ADD CONSTRAINT `user_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
