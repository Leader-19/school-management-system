-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for school_management
CREATE DATABASE IF NOT EXISTS `school_management` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `school_management`;

-- Dumping structure for table school_management.assignments
CREATE TABLE IF NOT EXISTS `assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `subject_id` int NOT NULL,
  `class_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `due_date` datetime NOT NULL,
  `attachments` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `class_id` (`class_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table school_management.assignments: ~2 rows (approximately)
INSERT IGNORE INTO `assignments` (`id`, `title`, `description`, `subject_id`, `class_id`, `teacher_id`, `due_date`, `attachments`, `created_at`, `updated_at`) VALUES
	(1, 'sfews', 'dsfsdfsf', 3, 1, 2, '2026-10-13 00:08:00', NULL, '2026-10-03 17:08:51', '2026-10-03 17:08:51'),
	(2, 'Test', 'tesgdsgdfsgbfd', 3, 1, 1, '2026-10-05 12:02:00', 'assignments/20261004_050241_982b1283628bf79bdb6604a63af748c0.docx', '2026-10-04 05:02:41', '2026-10-04 05:02:41');

-- Dumping structure for table school_management.assignment_submissions
CREATE TABLE IF NOT EXISTS `assignment_submissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assignment_id` int NOT NULL,
  `student_id` int NOT NULL,
  `content` text,
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `feedback` text,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `graded_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `assignment_submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table school_management.assignment_submissions: ~3 rows (approximately)
INSERT IGNORE INTO `assignment_submissions` (`id`, `assignment_id`, `student_id`, `content`, `file_path`, `file_name`, `grade`, `feedback`, `submitted_at`, `graded_at`) VALUES
	(1, 1, 2, 'werewtewtet', 'submissions/20261003_171046_61a13c2c02afd0a79c021bc99eebc44f.docx', 'CCNA-Assignment.docx', 100.00, NULL, '2026-10-03 10:10:46', '2026-10-03 10:11:54'),
	(2, 2, 3, 'dfgvdfgfdg', 'submissions/20261004_053811_0aba76f6fdf08f80676fc9a4ef2d8d1e.docx', 'configures.docx', NULL, NULL, '2026-10-03 22:38:11', NULL),
	(3, 1, 3, 'dgdfgd', 'submissions/20261004_053858_f7b6057525e950524e43fdc8e7033eb5.docx', 'Java_50_Classes_Cover_Modern.docx', NULL, NULL, '2026-10-03 22:38:58', NULL);

-- Dumping structure for table school_management.attendance_sessions
CREATE TABLE IF NOT EXISTS `attendance_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `subject_id` int DEFAULT NULL,
  `session_date` date NOT NULL,
  `session_type` varchar(50) NOT NULL DEFAULT 'full_day',
  `notes` text,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_session` (`class_id`,`session_date`,`session_type`),
  KEY `subject_id` (`subject_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `attendance_sessions_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_sessions_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_sessions_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table school_management.attendance_sessions: ~2 rows (approximately)
INSERT IGNORE INTO `attendance_sessions` (`id`, `class_id`, `subject_id`, `session_date`, `session_type`, `notes`, `created_by`, `created_at`) VALUES
	(1, 1, NULL, '2026-10-04', 'full_day', NULL, NULL, '2026-10-04 06:58:28'),
	(2, 2, NULL, '2026-10-04', 'full_day', NULL, NULL, '2026-10-04 07:06:16');

-- Dumping structure for table school_management.classes
CREATE TABLE IF NOT EXISTS `classes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `teacher_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table school_management.classes: ~3 rows (approximately)
INSERT IGNORE INTO `classes` (`id`, `name`, `description`, `teacher_id`, `created_at`) VALUES
	(1, 'Class 10A', '10th Grade Section A', 2, '2026-10-03 14:40:49'),
	(2, 'Class 10B', '10th Grade Section B', 2, '2026-10-03 14:40:49'),
	(3, '33', 'erwer', 2, '2026-10-03 16:44:25');

-- Dumping structure for table school_management.class_subjects
CREATE TABLE IF NOT EXISTS `class_subjects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class_id` int NOT NULL,
  `subject_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_id` (`class_id`,`subject_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `class_subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table school_management.class_subjects: ~5 rows (approximately)
INSERT IGNORE INTO `class_subjects` (`id`, `class_id`, `subject_id`) VALUES
	(1, 1, 1),
	(2, 1, 2),
	(3, 1, 3),
	(4, 2, 1),
	(5, 2, 3);

-- Dumping structure for table school_management.grades
CREATE TABLE IF NOT EXISTS `grades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table school_management.grades: ~2 rows (approximately)
INSERT IGNORE INTO `grades` (`id`, `student_id`, `subject_id`, `score`, `semester`, `academic_year`, `created_at`) VALUES
	(1, 2, 3, 100.00, '1', '2026', '2026-10-03 16:58:11'),
	(3, 5, 1, 85.00, '1', '2026', '2026-10-04 06:29:57');

-- Dumping structure for table school_management.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table school_management.permissions: ~10 rows (approximately)
INSERT IGNORE INTO `permissions` (`id`, `name`, `description`) VALUES
	(1, 'manage_users', 'Can manage users'),
	(2, 'manage_students', 'Can manage students'),
	(3, 'manage_subjects', 'Can manage subjects'),
	(4, 'manage_classes', 'Can manage classes'),
	(5, 'create_assignments', 'Can create assignments'),
	(6, 'view_assignments', 'Can view assignments'),
	(7, 'submit_assignments', 'Can submit assignments'),
	(8, 'manage_grades', 'Can manage grades'),
	(9, 'view_own_grades', 'Can view own grades'),
	(10, 'view_dashboard', 'Can view dashboard');

-- Dumping structure for table school_management.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table school_management.roles: ~3 rows (approximately)
INSERT IGNORE INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
	(1, 'admin', 'Administrator with full access', '2026-10-03 14:40:14'),
	(2, 'teacher', 'Teacher with access to manage classes, assignments, and grades', '2026-10-03 14:40:14'),
	(3, 'student', 'Student with access to view assignments, submit work, and view grades', '2026-10-03 14:40:14');

-- Dumping structure for table school_management.role_permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_id` (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table school_management.role_permissions: ~20 rows (approximately)
INSERT IGNORE INTO `role_permissions` (`id`, `role_id`, `permission_id`) VALUES
	(6, 1, 1),
	(4, 1, 2),
	(5, 1, 3),
	(2, 1, 4),
	(1, 1, 5),
	(8, 1, 6),
	(7, 1, 7),
	(3, 1, 8),
	(10, 1, 9),
	(9, 1, 10),
	(18, 2, 2),
	(30, 2, 4),
	(16, 2, 5),
	(19, 2, 6),
	(17, 2, 8),
	(20, 2, 10),
	(24, 3, 6),
	(23, 3, 7),
	(26, 3, 9),
	(25, 3, 10);

-- Dumping structure for table school_management.students
CREATE TABLE IF NOT EXISTS `students` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `student_code` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text,
  `phone` varchar(20) DEFAULT NULL,
  `class_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_code` (`student_code`),
  KEY `user_id` (`user_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `students_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table school_management.students: ~102 rows (approximately)
INSERT IGNORE INTO `students` (`id`, `user_id`, `student_code`, `first_name`, `last_name`, `date_of_birth`, `gender`, `address`, `phone`, `class_id`, `created_at`, `updated_at`) VALUES
	(2, 15, '11111', 'LEADER', 'DIN', NULL, 'male', 'rgdfgdfg', '1111111', 1, '2026-10-03 16:00:46', '2026-10-03 16:00:46'),
	(3, 17, 'STU2222', 'student', 'student', '2026-10-06', 'male', 'Thsfdsd', '01010202', 1, '2026-10-04 05:18:50', '2026-10-04 05:18:50'),
	(4, 18, 'STU002', 'Ben', 'Kim', '2010-11-02', 'male', NULL, '555-0102', NULL, '2026-10-04 06:12:37', '2026-10-04 06:12:37'),
	(5, 19, 'STU001', 'Ana', 'Lee', '2010-01-05', 'female', '10 Oak Street', '555-1000', 1, '2026-10-04 06:16:27', '2026-10-04 06:16:27'),
	(6, 20, 'STU003', 'Dara', 'Park', '2010-01-11', 'female', '12 Oak Street', '555-1002', 1, '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(7, 21, 'STU004', 'Sokha', 'Smith', '2010-01-14', 'male', '13 Oak Street', '555-1003', 1, '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(8, 22, 'STU005', 'Lina', 'Johnson', '2010-01-17', 'female', '14 Oak Street', '555-1004', 1, '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(9, 23, 'STU006', 'Vanna', 'Brown', '2010-01-20', 'male', '15 Oak Street', '555-1005', 1, '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(10, 24, 'STU007', 'Mina', 'Williams', '2010-01-23', 'female', '16 Oak Street', '555-1006', 1, '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(11, 25, 'STU008', 'David', 'Davis', '2010-01-26', 'male', '17 Oak Street', '555-1007', 1, '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(12, 26, 'STU009', 'Sophea', 'Miller', '2010-01-29', 'female', '18 Oak Street', '555-1008', 1, '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(13, 27, 'STU010', 'Rith', 'Wilson', '2010-02-01', 'male', '19 Oak Street', '555-1009', 1, '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(14, 28, 'STU011', 'Maly', 'Taylor', '2010-02-04', 'female', '20 Oak Street', '555-1010', 1, '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(15, 29, 'STU012', 'Davy', 'Anderson', '2010-02-07', 'male', '21 Oak Street', '555-1011', 1, '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(16, 30, 'STU013', 'Bora', 'Thomas', '2010-02-10', 'female', '22 Oak Street', '555-1012', 1, '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(17, 31, 'STU014', 'Chenda', 'Jackson', '2010-02-13', 'male', '23 Oak Street', '555-1013', 1, '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(18, 32, 'STU015', 'Nara', 'White', '2010-02-16', 'female', '24 Oak Street', '555-1014', 1, '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(19, 33, 'STU016', 'Kanha', 'Harris', '2010-02-19', 'male', '25 Oak Street', '555-1015', 1, '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(20, 34, 'STU017', 'Pheak', 'Martin', '2010-02-22', 'female', '26 Oak Street', '555-1016', 1, '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(21, 35, 'STU018', 'Sreyneang', 'Thompson', '2010-02-25', 'male', '27 Oak Street', '555-1017', 1, '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(22, 36, 'STU019', 'Visal', 'Garcia', '2010-02-28', 'female', '28 Oak Street', '555-1018', 1, '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(23, 37, 'STU020', 'Kosal', 'Martinez', '2010-03-03', 'male', '29 Oak Street', '555-1019', 1, '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(24, 38, 'STU021', 'Nita', 'Robinson', '2010-03-06', 'female', '30 Oak Street', '555-1020', 1, '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(25, 39, 'STU022', 'Sokun', 'Clark', '2010-03-09', 'male', '31 Oak Street', '555-1021', 1, '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(26, 40, 'STU023', 'Rina', 'Lewis', '2010-03-12', 'female', '32 Oak Street', '555-1022', 1, '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(27, 41, 'STU024', 'Bunthoeun', 'Walker', '2010-03-15', 'male', '33 Oak Street', '555-1023', 1, '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(28, 42, 'STU025', 'Pisey', 'Hall', '2010-03-18', 'female', '34 Oak Street', '555-1024', 1, '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(29, 43, 'STU026', 'Ratha', 'Allen', '2010-03-21', 'male', '35 Oak Street', '555-1025', 1, '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(30, 44, 'STU027', 'Kunthea', 'Young', '2010-03-24', 'female', '36 Oak Street', '555-1026', 1, '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(31, 45, 'STU028', 'Dalin', 'King', '2010-03-27', 'male', '37 Oak Street', '555-1027', 1, '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(32, 46, 'STU029', 'Vuthy', 'Wright', '2010-03-30', 'female', '38 Oak Street', '555-1028', 1, '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(33, 47, 'STU030', 'Sothea', 'Scott', '2010-04-02', 'male', '39 Oak Street', '555-1029', 1, '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(34, 48, 'STU031', 'Makara', 'Green', '2010-04-05', 'female', '40 Oak Street', '555-1030', 1, '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(35, 49, 'STU032', 'Sovann', 'Baker', '2010-04-08', 'male', '41 Oak Street', '555-1031', 1, '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(36, 50, 'STU033', 'Kiri', 'Adams', '2010-04-11', 'female', '42 Oak Street', '555-1032', 1, '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(37, 51, 'STU034', 'Monika', 'Nelson', '2010-04-14', 'male', '43 Oak Street', '555-1033', 1, '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(38, 52, 'STU035', 'Nary', 'Hill', '2010-04-17', 'female', '44 Oak Street', '555-1034', 1, '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(39, 53, 'STU036', 'Ravy', 'Campbell', '2010-04-20', 'male', '45 Oak Street', '555-1035', 1, '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(40, 54, 'STU037', 'Borey', 'Mitchell', '2010-04-23', 'female', '46 Oak Street', '555-1036', 1, '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(41, 55, 'STU038', 'Sophal', 'Roberts', '2010-04-26', 'male', '47 Oak Street', '555-1037', 1, '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(42, 56, 'STU039', 'Channary', 'Carter', '2010-04-29', 'female', '48 Oak Street', '555-1038', 1, '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(43, 57, 'STU040', 'Dara', 'Phillips', '2010-05-02', 'male', '49 Oak Street', '555-1039', 1, '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(44, 58, 'STU041', 'Sina', 'Evans', '2010-05-05', 'female', '50 Oak Street', '555-1040', 1, '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(45, 59, 'STU042', 'Rina', 'Turner', '2010-05-08', 'male', '51 Oak Street', '555-1041', 1, '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(46, 60, 'STU043', 'Vicheka', 'Torres', '2010-05-11', 'female', '52 Oak Street', '555-1042', 1, '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(47, 61, 'STU044', 'Sreymao', 'Parker', '2010-05-14', 'male', '53 Oak Street', '555-1043', 1, '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(48, 62, 'STU045', 'Rithy', 'Collins', '2010-05-17', 'female', '54 Oak Street', '555-1044', 1, '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(49, 63, 'STU046', 'Pheaktra', 'Edwards', '2010-05-20', 'male', '55 Oak Street', '555-1045', 1, '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(50, 64, 'STU047', 'Chantha', 'Stewart', '2010-05-23', 'female', '56 Oak Street', '555-1046', 1, '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(51, 65, 'STU048', 'Mony', 'Flores', '2010-05-26', 'male', '57 Oak Street', '555-1047', 1, '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(52, 66, 'STU049', 'Sokchea', 'Morris', '2010-05-29', 'female', '58 Oak Street', '555-1048', 1, '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(53, 67, 'STU050', 'Nimol', 'Nguyen', '2010-06-01', 'male', '59 Oak Street', '555-1049', 1, '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(54, 68, 'STU051', 'Sovan', 'Murphy', '2010-06-04', 'female', '60 Oak Street', '555-1050', 2, '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(55, 69, 'STU052', 'Sreypich', 'Rivera', '2010-06-07', 'male', '61 Oak Street', '555-1051', 2, '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(56, 70, 'STU053', 'Kosal', 'Cook', '2010-06-10', 'female', '62 Oak Street', '555-1052', 2, '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(57, 71, 'STU054', 'Bopha', 'Rogers', '2010-06-13', 'male', '63 Oak Street', '555-1053', 2, '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(58, 72, 'STU055', 'Dany', 'Morgan', '2010-06-16', 'female', '64 Oak Street', '555-1054', 2, '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(59, 73, 'STU056', 'Vannak', 'Peterson', '2010-06-19', 'male', '65 Oak Street', '555-1055', 2, '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(60, 74, 'STU057', 'Sokly', 'Cooper', '2010-06-22', 'female', '66 Oak Street', '555-1056', 2, '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(61, 75, 'STU058', 'Sotheary', 'Reed', '2010-06-25', 'male', '67 Oak Street', '555-1057', 2, '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(62, 76, 'STU059', 'Rong', 'Bailey', '2010-06-28', 'female', '68 Oak Street', '555-1058', 2, '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(63, 77, 'STU060', 'Kimheng', 'Bell', '2010-07-01', 'male', '69 Oak Street', '555-1059', 2, '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(64, 78, 'STU061', 'Sovichea', 'Gomez', '2010-07-04', 'female', '70 Oak Street', '555-1060', 2, '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(65, 79, 'STU062', 'Sreymom', 'Kelly', '2010-07-07', 'male', '71 Oak Street', '555-1061', 2, '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(66, 80, 'STU063', 'Dalin', 'Howard', '2010-07-10', 'female', '72 Oak Street', '555-1062', 2, '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(67, 81, 'STU064', 'Bunroeun', 'Ward', '2010-07-13', 'male', '73 Oak Street', '555-1063', 2, '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(68, 82, 'STU065', 'Sophea', 'Cox', '2010-07-16', 'female', '74 Oak Street', '555-1064', 2, '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(69, 83, 'STU066', 'Khemera', 'Diaz', '2010-07-19', 'male', '75 Oak Street', '555-1065', 2, '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(70, 84, 'STU067', 'Vireak', 'Richardson', '2010-07-22', 'female', '76 Oak Street', '555-1066', 2, '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(71, 85, 'STU068', 'Sokunthea', 'Wood', '2010-07-25', 'male', '77 Oak Street', '555-1067', 2, '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(72, 86, 'STU069', 'Rachana', 'Watson', '2010-07-28', 'female', '78 Oak Street', '555-1068', 2, '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(73, 87, 'STU070', 'Davin', 'Brooks', '2010-07-31', 'male', '79 Oak Street', '555-1069', 2, '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(74, 88, 'STU071', 'Sokha', 'Bennett', '2010-08-03', 'female', '80 Oak Street', '555-1070', 2, '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(75, 89, 'STU072', 'Mony', 'Gray', '2010-08-06', 'male', '81 Oak Street', '555-1071', 2, '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(76, 90, 'STU073', 'Vanna', 'James', '2010-08-09', 'female', '82 Oak Street', '555-1072', 2, '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(77, 91, 'STU074', 'Sophea', 'Reyes', '2010-08-12', 'male', '83 Oak Street', '555-1073', 2, '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(78, 92, 'STU075', 'Rith', 'Cruz', '2010-08-15', 'female', '84 Oak Street', '555-1074', 2, '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(79, 93, 'STU076', 'Nita', 'Hughes', '2010-08-18', 'male', '85 Oak Street', '555-1075', 2, '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(80, 94, 'STU077', 'Dara', 'Price', '2010-08-21', 'female', '86 Oak Street', '555-1076', 2, '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(81, 95, 'STU078', 'Lina', 'Myers', '2010-08-24', 'male', '87 Oak Street', '555-1077', 2, '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(82, 96, 'STU079', 'Visal', 'Long', '2010-08-27', 'female', '88 Oak Street', '555-1078', 2, '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(83, 97, 'STU080', 'Kosal', 'Foster', '2010-08-30', 'male', '89 Oak Street', '555-1079', 2, '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(84, 98, 'STU081', 'Bora', 'Sanders', '2010-09-02', 'female', '90 Oak Street', '555-1080', 2, '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(85, 99, 'STU082', 'Chenda', 'Ross', '2010-09-05', 'male', '91 Oak Street', '555-1081', 2, '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(86, 100, 'STU083', 'Nara', 'Morales', '2010-09-08', 'female', '92 Oak Street', '555-1082', 2, '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(87, 101, 'STU084', 'Kanha', 'Powell', '2010-09-11', 'male', '93 Oak Street', '555-1083', 2, '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(88, 102, 'STU085', 'Pheak', 'Sullivan', '2010-09-14', 'female', '94 Oak Street', '555-1084', 2, '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(89, 103, 'STU086', 'Maly', 'Russell', '2010-09-17', 'male', '95 Oak Street', '555-1085', 2, '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(90, 104, 'STU087', 'Ravy', 'Ortiz', '2010-09-20', 'female', '96 Oak Street', '555-1086', 2, '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(91, 105, 'STU088', 'Borey', 'Jenkins', '2010-09-23', 'male', '97 Oak Street', '555-1087', 2, '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(92, 106, 'STU089', 'Sina', 'Gutierrez', '2010-09-26', 'female', '98 Oak Street', '555-1088', 2, '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(93, 107, 'STU090', 'Mina', 'Perry', '2010-09-29', 'male', '99 Oak Street', '555-1089', 2, '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(94, 108, 'STU091', 'Sreyneang', 'Butler', '2010-10-02', 'female', '100 Oak Street', '555-1090', 2, '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(95, 109, 'STU092', 'Davy', 'Barnes', '2010-10-05', 'male', '101 Oak Street', '555-1091', 2, '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(96, 110, 'STU093', 'Makara', 'Fisher', '2010-10-08', 'female', '102 Oak Street', '555-1092', 2, '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(97, 111, 'STU094', 'Nimol', 'Henderson', '2010-10-11', 'male', '103 Oak Street', '555-1093', 2, '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(98, 112, 'STU095', 'Sovann', 'Coleman', '2010-10-14', 'female', '104 Oak Street', '555-1094', 2, '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(99, 113, 'STU096', 'Pisey', 'Simmons', '2010-10-17', 'male', '105 Oak Street', '555-1095', 2, '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(100, 114, 'STU097', 'Ratha', 'Patterson', '2010-10-20', 'female', '106 Oak Street', '555-1096', 2, '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(101, 115, 'STU098', 'Kunthea', 'Jordan', '2010-10-23', 'male', '107 Oak Street', '555-1097', 2, '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(102, 116, 'STU099', 'Vuthy', 'Reynolds', '2010-10-26', 'female', '108 Oak Street', '555-1098', 2, '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(103, 117, 'STU100', 'Sothea', 'Hamilton', '2010-10-29', 'male', '109 Oak Street', '555-1099', 2, '2026-10-04 06:16:38', '2026-10-04 06:16:38');

-- Dumping structure for table school_management.student_attendance
CREATE TABLE IF NOT EXISTS `student_attendance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `student_id` int NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL DEFAULT 'present',
  `note` varchar(255) DEFAULT NULL,
  `marked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_session` (`session_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `student_attendance_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table school_management.student_attendance: ~101 rows (approximately)
INSERT IGNORE INTO `student_attendance` (`id`, `session_id`, `student_id`, `status`, `note`, `marked_at`) VALUES
	(1, 1, 5, 'present', NULL, '2026-10-04 06:59:15'),
	(2, 1, 16, 'present', NULL, '2026-10-04 06:59:15'),
	(3, 1, 40, 'present', NULL, '2026-10-04 06:59:15'),
	(4, 1, 27, 'present', NULL, '2026-10-04 06:59:15'),
	(5, 1, 42, 'present', NULL, '2026-10-04 06:59:15'),
	(6, 1, 50, 'present', NULL, '2026-10-04 06:59:15'),
	(7, 1, 17, 'present', NULL, '2026-10-04 06:59:15'),
	(8, 1, 31, 'present', NULL, '2026-10-04 06:59:15'),
	(9, 1, 6, 'present', NULL, '2026-10-04 06:59:15'),
	(10, 1, 43, 'present', NULL, '2026-10-04 06:59:15'),
	(11, 1, 11, 'present', NULL, '2026-10-04 06:59:15'),
	(12, 1, 15, 'present', NULL, '2026-10-04 06:59:15'),
	(13, 1, 19, 'present', NULL, '2026-10-04 06:59:15'),
	(14, 1, 36, 'present', NULL, '2026-10-04 06:59:15'),
	(15, 1, 23, 'present', NULL, '2026-10-04 06:59:15'),
	(16, 1, 30, 'present', NULL, '2026-10-04 06:59:15'),
	(17, 1, 2, 'present', NULL, '2026-10-04 06:59:15'),
	(18, 1, 8, 'present', NULL, '2026-10-04 06:59:15'),
	(19, 1, 34, 'present', NULL, '2026-10-04 06:59:15'),
	(20, 1, 14, 'present', NULL, '2026-10-04 06:59:15'),
	(21, 1, 10, 'present', NULL, '2026-10-04 06:59:15'),
	(22, 1, 37, 'present', NULL, '2026-10-04 06:59:15'),
	(23, 1, 51, 'present', NULL, '2026-10-04 06:59:15'),
	(24, 1, 18, 'present', NULL, '2026-10-04 06:59:15'),
	(25, 1, 38, 'present', NULL, '2026-10-04 06:59:15'),
	(26, 1, 53, 'present', NULL, '2026-10-04 06:59:15'),
	(27, 1, 24, 'present', NULL, '2026-10-04 06:59:15'),
	(28, 1, 20, 'present', NULL, '2026-10-04 06:59:15'),
	(29, 1, 49, 'present', NULL, '2026-10-04 06:59:15'),
	(30, 1, 28, 'present', NULL, '2026-10-04 06:59:15'),
	(31, 1, 29, 'present', NULL, '2026-10-04 06:59:15'),
	(32, 1, 39, 'present', NULL, '2026-10-04 06:59:15'),
	(33, 1, 26, 'present', NULL, '2026-10-04 06:59:15'),
	(34, 1, 45, 'present', NULL, '2026-10-04 06:59:15'),
	(35, 1, 13, 'present', NULL, '2026-10-04 06:59:15'),
	(36, 1, 48, 'present', NULL, '2026-10-04 06:59:15'),
	(37, 1, 44, 'present', NULL, '2026-10-04 06:59:15'),
	(38, 1, 52, 'present', NULL, '2026-10-04 06:59:15'),
	(39, 1, 7, 'present', NULL, '2026-10-04 06:59:15'),
	(40, 1, 25, 'present', NULL, '2026-10-04 06:59:15'),
	(41, 1, 41, 'present', NULL, '2026-10-04 06:59:15'),
	(42, 1, 12, 'present', NULL, '2026-10-04 06:59:15'),
	(43, 1, 33, 'present', NULL, '2026-10-04 06:59:15'),
	(44, 1, 35, 'present', NULL, '2026-10-04 06:59:15'),
	(45, 1, 47, 'present', NULL, '2026-10-04 06:59:15'),
	(46, 1, 21, 'present', NULL, '2026-10-04 06:59:15'),
	(47, 1, 3, 'present', NULL, '2026-10-04 06:59:15'),
	(48, 1, 9, 'present', NULL, '2026-10-04 06:59:15'),
	(49, 1, 46, 'present', NULL, '2026-10-04 06:59:15'),
	(50, 1, 22, 'present', NULL, '2026-10-04 06:59:15'),
	(51, 1, 32, 'present', NULL, '2026-10-04 06:59:15'),
	(52, 2, 57, 'present', NULL, '2026-10-04 07:06:26'),
	(53, 2, 84, 'present', NULL, '2026-10-04 07:06:26'),
	(54, 2, 91, 'present', NULL, '2026-10-04 07:06:26'),
	(55, 2, 67, 'present', NULL, '2026-10-04 07:06:26'),
	(56, 2, 85, 'present', NULL, '2026-10-04 07:06:26'),
	(57, 2, 66, 'present', NULL, '2026-10-04 07:06:26'),
	(58, 2, 58, 'present', NULL, '2026-10-04 07:06:26'),
	(59, 2, 80, 'present', NULL, '2026-10-04 07:06:26'),
	(60, 2, 73, 'present', NULL, '2026-10-04 07:06:26'),
	(61, 2, 95, 'present', NULL, '2026-10-04 07:06:26'),
	(62, 2, 87, 'present', NULL, '2026-10-04 07:06:26'),
	(63, 2, 69, 'present', NULL, '2026-10-04 07:06:26'),
	(64, 2, 63, 'present', NULL, '2026-10-04 07:06:26'),
	(65, 2, 56, 'present', NULL, '2026-10-04 07:06:26'),
	(66, 2, 83, 'present', NULL, '2026-10-04 07:06:26'),
	(67, 2, 101, 'present', NULL, '2026-10-04 07:06:26'),
	(68, 2, 81, 'present', NULL, '2026-10-04 07:06:26'),
	(69, 2, 96, 'present', NULL, '2026-10-04 07:06:26'),
	(70, 2, 89, 'present', NULL, '2026-10-04 07:06:26'),
	(71, 2, 93, 'present', NULL, '2026-10-04 07:06:26'),
	(72, 2, 75, 'present', NULL, '2026-10-04 07:06:26'),
	(73, 2, 86, 'present', NULL, '2026-10-04 07:06:26'),
	(74, 2, 97, 'present', NULL, '2026-10-04 07:06:26'),
	(75, 2, 79, 'present', NULL, '2026-10-04 07:06:26'),
	(76, 2, 88, 'present', NULL, '2026-10-04 07:06:26'),
	(77, 2, 99, 'present', NULL, '2026-10-04 07:06:26'),
	(78, 2, 72, 'present', NULL, '2026-10-04 07:06:26'),
	(79, 2, 100, 'present', NULL, '2026-10-04 07:06:26'),
	(80, 2, 90, 'present', NULL, '2026-10-04 07:06:26'),
	(81, 2, 78, 'present', NULL, '2026-10-04 07:06:26'),
	(82, 2, 62, 'present', NULL, '2026-10-04 07:06:26'),
	(83, 2, 92, 'present', NULL, '2026-10-04 07:06:26'),
	(84, 2, 74, 'present', NULL, '2026-10-04 07:06:26'),
	(85, 2, 60, 'present', NULL, '2026-10-04 07:06:26'),
	(86, 2, 71, 'present', NULL, '2026-10-04 07:06:26'),
	(87, 2, 68, 'present', NULL, '2026-10-04 07:06:26'),
	(88, 2, 77, 'present', NULL, '2026-10-04 07:06:26'),
	(89, 2, 103, 'present', NULL, '2026-10-04 07:06:26'),
	(90, 2, 61, 'present', NULL, '2026-10-04 07:06:26'),
	(91, 2, 54, 'present', NULL, '2026-10-04 07:06:26'),
	(92, 2, 98, 'present', NULL, '2026-10-04 07:06:26'),
	(93, 2, 64, 'present', NULL, '2026-10-04 07:06:26'),
	(94, 2, 65, 'present', NULL, '2026-10-04 07:06:26'),
	(95, 2, 94, 'present', NULL, '2026-10-04 07:06:26'),
	(96, 2, 55, 'present', NULL, '2026-10-04 07:06:26'),
	(97, 2, 76, 'present', NULL, '2026-10-04 07:06:26'),
	(98, 2, 59, 'present', NULL, '2026-10-04 07:06:26'),
	(99, 2, 70, 'present', NULL, '2026-10-04 07:06:26'),
	(100, 2, 82, 'present', NULL, '2026-10-04 07:06:26'),
	(101, 2, 102, 'present', NULL, '2026-10-04 07:06:26');

-- Dumping structure for table school_management.subjects
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text,
  `credit_hours` int DEFAULT '3',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table school_management.subjects: ~3 rows (approximately)
INSERT IGNORE INTO `subjects` (`id`, `name`, `code`, `description`, `credit_hours`) VALUES
	(1, 'Mathematics', 'MATH101', 'Basic Mathematics', 3),
	(2, 'Physics', 'PHY101', 'Basic Physics', 3),
	(3, 'English', 'ENG101', 'Basic English', 3);

-- Dumping structure for table school_management.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table school_management.users: ~109 rows (approximately)
INSERT IGNORE INTO `users` (`id`, `username`, `email`, `password`, `role_id`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'admin', 'admin@school.local', '$2y$10$VN4WQnVj3JYPlNnYFhxPxO014D7JaRbkLjQESHSGvyLyJ2SXXaVvy', 1, 'active', '2026-10-03 14:40:44', '2026-10-03 15:55:27'),
	(2, 'teacher1', 'teacher1@school.local', '$2y$10$VN4WQnVj3JYPlNnYFhxPxO014D7JaRbkLjQESHSGvyLyJ2SXXaVvy', 2, 'active', '2026-10-03 14:40:44', '2026-10-03 15:55:27'),
	(4, 'admin1', 'admin1@school.local', '123', 1, 'active', '2026-10-03 15:36:07', '2026-10-03 15:36:07'),
	(5, 'admin3', 'admin3@school.local', '12345678', 1, 'active', '2026-10-03 15:51:19', '2026-10-03 15:51:19'),
	(7, 'admin4', 'admin@gmail.com', '12345678', 1, 'active', '2026-10-03 15:53:25', '2026-10-03 15:53:25'),
	(14, 'leader', 'leader@school.local', '123456789', 1, 'active', '2026-10-03 15:56:38', '2026-10-03 15:56:38'),
	(15, 'DINLEADER', 'leader.din@student.passerellesnumeriques.org', '$2y$10$rgBgwAJ9Nkqhp2cAsdqCwub29ZyggbpaDQqE2hhuuJLxmrdRnv842', 3, 'active', '2026-10-03 16:00:46', '2026-10-03 17:10:03'),
	(16, 'test', 'test@school.local', '123456789', 1, 'active', '2026-10-03 17:14:59', '2026-10-03 17:14:59'),
	(17, 'student', 'student@gmail.com', '$2y$10$ZSkRE8O1jlXyRVSW5U/COe3BwNZPWsU4eerGnjvC4S56Zy29hGPSS', 3, 'active', '2026-10-04 05:18:50', '2026-10-04 05:18:50'),
	(18, 'ben.kim', 'ben.kim@school.local', '$2y$10$YCf3rDJPqQM2Ui12Urh0FOQSwgKoa46N87nA74aE.9KTPwOLbxgDK', 3, 'active', '2026-10-04 06:12:37', '2026-10-04 06:12:37'),
	(19, 'ana.lee001', 'ana.lee001@school.local', '$2y$10$s6CUDfedr/Zlt51WTG6CluxKo94bNucIDF3ezlATR2zzu52z1DGnC', 3, 'active', '2026-10-04 06:16:27', '2026-10-04 06:16:27'),
	(20, 'dara.park003', 'dara.park003@school.local', '$2y$10$aD/RFnoMVbR/60hlkUOt.uXo3WZwN6B4qubjcyHUpvRB00VpT4vVi', 3, 'active', '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(21, 'sokha.smith004', 'sokha.smith004@school.local', '$2y$10$YSEnZvUgz3YVsypEqqDwKuhDFlN6RXDQWJc85PvW0/9j4hqPZO5fe', 3, 'active', '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(22, 'lina.johnson005', 'lina.johnson005@school.local', '$2y$10$pc8Gl1ITcWR8PiSqhusNaODtY3rKP5kCi0eP40hcVERX3/NVm8dma', 3, 'active', '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(23, 'vanna.brown006', 'vanna.brown006@school.local', '$2y$10$HSqJQkyW3wMA6l.tTR25U.8AxhZGO0uHXZaQuq8BauTgPcEHSdUoO', 3, 'active', '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(24, 'mina.williams007', 'mina.williams007@school.local', '$2y$10$TbQM7vAEY2IFTlzZU0UISuC7j8kS7zBTpz9DYwrLA3eQ6n4JECv7u', 3, 'active', '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(25, 'david.davis008', 'david.davis008@school.local', '$2y$10$bWlkXCSZiY4eTayRRH/PjOADrjUpnqnZV.SAzCgmUn.X1vMEEP2Ga', 3, 'active', '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(26, 'sophea.miller009', 'sophea.miller009@school.local', '$2y$10$dsHrusI9BBpdU1d7C.oltOJL3e7vJjDV5hOTtrvFlUQC5f0XCsqJq', 3, 'active', '2026-10-04 06:16:28', '2026-10-04 06:16:28'),
	(27, 'rith.wilson010', 'rith.wilson010@school.local', '$2y$10$8EqFyXOMnhB/vqgcGJAx9OH8EazAsx7s.ISL6orFM/ZaDIPQooyU.', 3, 'active', '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(28, 'maly.taylor011', 'maly.taylor011@school.local', '$2y$10$8VdG2xy50s9X9lMv7fHQhO594g54/JUNx.BhhT9pNOgpr/wo5kmKK', 3, 'active', '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(29, 'davy.anderson012', 'davy.anderson012@school.local', '$2y$10$kPUlMcjxWM8Ccj2vylalveaLXE3PCnUe5KiiiAs/bbuunj6V/EmJi', 3, 'active', '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(30, 'bora.thomas013', 'bora.thomas013@school.local', '$2y$10$XiTzmfatW3LnrgqZogIbZe/8KoLHnDXaJGpiOXOfSVYGtE0S1VyLS', 3, 'active', '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(31, 'chenda.jackson014', 'chenda.jackson014@school.local', '$2y$10$juvtt49mtDvNlFl.e/PcsOn/gYx5qdJmvGbUNUFOhCoQhavPZMT.y', 3, 'active', '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(32, 'nara.white015', 'nara.white015@school.local', '$2y$10$R3muyMEqxmUbUr2/ya6vGuxZm.NGNfcrBmB1uBTNF.XjWTnsQNcUG', 3, 'active', '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(33, 'kanha.harris016', 'kanha.harris016@school.local', '$2y$10$EdwVngo0M4zmB5yHQPKwl.zm0i54pGiLC6kZbdZXkRjvzPLDQFkgO', 3, 'active', '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(34, 'pheak.martin017', 'pheak.martin017@school.local', '$2y$10$KVU2K49yNK5tDXKZdzjtw./.HKWm5eIddbVW7pJmuPv/GppArLjTS', 3, 'active', '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(35, 'sreyneang.thompson018', 'sreyneang.thompson018@school.local', '$2y$10$SUgqraIOzCvsN/QImw4LGOZRmMqqtLmbQkIabQZlpHDfQ/7IJ4LmG', 3, 'active', '2026-10-04 06:16:29', '2026-10-04 06:16:29'),
	(36, 'visal.garcia019', 'visal.garcia019@school.local', '$2y$10$GcOC6I5fhEZ8OC0B5LWpDe7ER.9w539M6rBH7TmH68isKshviFYFO', 3, 'active', '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(37, 'kosal.martinez020', 'kosal.martinez020@school.local', '$2y$10$dBe7oEiLUjPaGhWV.5GHqO739OEe50p/6Cy2PdL1wpW8fzIw7gfZ.', 3, 'active', '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(38, 'nita.robinson021', 'nita.robinson021@school.local', '$2y$10$b3rI.oYzEa9hUNJdrwiHT.cxX.jtRBbn4u5VITB0g/aL/J9T343aK', 3, 'active', '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(39, 'sokun.clark022', 'sokun.clark022@school.local', '$2y$10$z/3gAU6wl0OZyHs.iCuj6uE.PlkFlThuASGQuSU0HpmZqn8/9iTrG', 3, 'active', '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(40, 'rina.lewis023', 'rina.lewis023@school.local', '$2y$10$l4iiNUJ7o55FMGfYbNt2kegYxm7g/qHPng6Wg1O2vf/.4dJ2uUWIa', 3, 'active', '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(41, 'bunthoeun.walker024', 'bunthoeun.walker024@school.local', '$2y$10$hXjTvYHdJJbHK3400jIReuTX9B864VGIocxmsAD/lxS8sD1UWv8Ze', 3, 'active', '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(42, 'pisey.hall025', 'pisey.hall025@school.local', '$2y$10$DU4tY3YGyfyAEwH2Z8dk3umI7UrCMVJ3orWQ0q53.yS8wcNSouhwu', 3, 'active', '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(43, 'ratha.allen026', 'ratha.allen026@school.local', '$2y$10$JvoImp4dOIxOst5.kQ3Cp.UUNUwTVmCxrCMJF9lBHZ6wp1Y9A.Rtu', 3, 'active', '2026-10-04 06:16:30', '2026-10-04 06:16:30'),
	(44, 'kunthea.young027', 'kunthea.young027@school.local', '$2y$10$HOY3cJQ3UHm0q0z4DslWCuXnUkh7kTB7lINLsPfx8azGIuBtkXqKO', 3, 'active', '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(45, 'dalin.king028', 'dalin.king028@school.local', '$2y$10$URP36WeydAwwRrBRb7Dh/e7Q.qu17P0iuS60HAQ7wrIQMdDW5HYz2', 3, 'active', '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(46, 'vuthy.wright029', 'vuthy.wright029@school.local', '$2y$10$dMTZxrsiMPtEHCwFt2LJMeZdSD8d5arVSfptuD30NOOZC78mK2gE6', 3, 'active', '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(47, 'sothea.scott030', 'sothea.scott030@school.local', '$2y$10$oko8dASo0TtXQiw9sOfDreW9Ir1iV02poEt2KccMYjDJoVlCoLZO.', 3, 'active', '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(48, 'makara.green031', 'makara.green031@school.local', '$2y$10$jyKUrvh21zc9JnkoertoZePsezT2eC3nbG4mGcmb3Glb/Vi29m2Oa', 3, 'active', '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(49, 'sovann.baker032', 'sovann.baker032@school.local', '$2y$10$Ltbw3n7ZECpBKo/CUap/L.vPWIZ/sHB3F9j66eKVMnP3jbmHOXBmK', 3, 'active', '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(50, 'kiri.adams033', 'kiri.adams033@school.local', '$2y$10$040j1GnAb5EFmryRZPnQB.q82sSFhu2TVDhnqMQ4VXmgewY7dZ/XG', 3, 'active', '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(51, 'monika.nelson034', 'monika.nelson034@school.local', '$2y$10$Fl805FGZT2PsUAlGdR7CpeWbUGufOdxXtnOeW7HylLCiSfvYw1BBi', 3, 'active', '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(52, 'nary.hill035', 'nary.hill035@school.local', '$2y$10$GammHoNqTUKMMtTmS0Xy/.XIkWNPsPq8AIev6TdvbpOMDEO1rgrmS', 3, 'active', '2026-10-04 06:16:31', '2026-10-04 06:16:31'),
	(53, 'ravy.campbell036', 'ravy.campbell036@school.local', '$2y$10$32n5N9txLYUgBOqw1EI4EOUWdx9OWqM5hYsP5PjAUJVvEDtmdG5e2', 3, 'active', '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(54, 'borey.mitchell037', 'borey.mitchell037@school.local', '$2y$10$E21GtRznqKstDm1457Dlg.ud95VAyBVt5ip9AzCXMpFFDce1SdnPW', 3, 'active', '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(55, 'sophal.roberts038', 'sophal.roberts038@school.local', '$2y$10$40zRyYJ7FzJPPfivIyqsfu01I52ictZCaJboLa2EaGA3LBAIRTMn6', 3, 'active', '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(56, 'channary.carter039', 'channary.carter039@school.local', '$2y$10$q6SrjBbFsY5Qxv1TdDa6Ue9t3mUIqDXYuUthbgwXi/Hy9E82rMHOe', 3, 'active', '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(57, 'dara.phillips040', 'dara.phillips040@school.local', '$2y$10$xaE90JcnxV9Y1/CS7Zp5T.GpVFsiEYgdM8LQ9ospTse2H8Yeq1cqe', 3, 'active', '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(58, 'sina.evans041', 'sina.evans041@school.local', '$2y$10$OsL5lZv4MHfV1.THwOSzteWQaBYaAw7D/TbUptxA3utqox.DaTF72', 3, 'active', '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(59, 'rina.turner042', 'rina.turner042@school.local', '$2y$10$SCNvGLCxuu.v8iNPO.rS3..4.kCRTuxNMqaWO19MsqybeBk/BAgz.', 3, 'active', '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(60, 'vicheka.torres043', 'vicheka.torres043@school.local', '$2y$10$g4A1CPO7BY6Pp.Kk2Oc/l..WT5KH3vy.KQgh7ZPh7HQPCJdDG0SZu', 3, 'active', '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(61, 'sreymao.parker044', 'sreymao.parker044@school.local', '$2y$10$ZJ6ojHphPnrIDqM3nYAsB.vfAMru2RrRZxwyMkjBKLm9n6YMKlApK', 3, 'active', '2026-10-04 06:16:32', '2026-10-04 06:16:32'),
	(62, 'rithy.collins045', 'rithy.collins045@school.local', '$2y$10$PRlqs4zXjW1W3H9YHg6HE..PFDC/QkdQvE5r54qZziNdeCF2hhSC2', 3, 'active', '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(63, 'pheaktra.edwards046', 'pheaktra.edwards046@school.local', '$2y$10$G6lPBt4nlP.jKmjOo9gWDOyX/Eu3/qYr60PVP6YJ9cpIHEYQQ8MSu', 3, 'active', '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(64, 'chantha.stewart047', 'chantha.stewart047@school.local', '$2y$10$I3TQ7YRoQbnLogUMdOstgeEfDnYncUJtaP5K9d6gJIuJfckJcPfS6', 3, 'active', '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(65, 'mony.flores048', 'mony.flores048@school.local', '$2y$10$W1XX.t2p6pkhHHOD5ixOOefs/6feWy7n4a.4eQHtX3dT/Il/8Sfb.', 3, 'active', '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(66, 'sokchea.morris049', 'sokchea.morris049@school.local', '$2y$10$Yn4KNyCMPpY5xw5cydCxI.cjFTbAQporSrD6QCCAOzjB3HKpJ8YBe', 3, 'active', '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(67, 'nimol.nguyen050', 'nimol.nguyen050@school.local', '$2y$10$FP8tScVuqifNNEtW3wLlM.TenNxVVPQI37G.JPFOFgv4xuXtoNmqi', 3, 'active', '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(68, 'sovan.murphy051', 'sovan.murphy051@school.local', '$2y$10$Bva6bcuaUEXUSJdxHLLly.vCNSLc.Lyb/eH2UXLkyFbO7Zb.Ue4Ti', 3, 'active', '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(69, 'sreypich.rivera052', 'sreypich.rivera052@school.local', '$2y$10$kZqvqDVIc/BFab2EvnYRG./R8SJ6p9peBWMh2skZoE9J8bLpGOn/2', 3, 'active', '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(70, 'kosal.cook053', 'kosal.cook053@school.local', '$2y$10$1YCeDhQ7vhu6Vd5auIODxuGu1DJTtPMNZUVJ2UI7ZRygWrvyeBqjK', 3, 'active', '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(71, 'bopha.rogers054', 'bopha.rogers054@school.local', '$2y$10$6Ky5ZL6JkpJ1YeC6cQUKae.ekzZaou1qnXzPicXAyjYcTGJVDuM1C', 3, 'active', '2026-10-04 06:16:33', '2026-10-04 06:16:33'),
	(72, 'dany.morgan055', 'dany.morgan055@school.local', '$2y$10$9YT89xk8.X1kpA5CXdlB2..W.FmhnmPXvHgky2c4r9m.ONoh7OmRS', 3, 'active', '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(73, 'vannak.peterson056', 'vannak.peterson056@school.local', '$2y$10$WE5.UPo71r5K5DBm/7l8d.zyD/N53Jl269OW8wPYpceYW2DXxGYU2', 3, 'active', '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(74, 'sokly.cooper057', 'sokly.cooper057@school.local', '$2y$10$VF2HX4pFxP6kvz4.sSkGWOVMj9v7JXMPHj9QRxRP/aqHD3jfYv82C', 3, 'active', '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(75, 'sotheary.reed058', 'sotheary.reed058@school.local', '$2y$10$IbeR628xtF82LbRCsTqpy.sa5i/2KXyBxkGoEAXW4H2/70kg4MSpu', 3, 'active', '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(76, 'rong.bailey059', 'rong.bailey059@school.local', '$2y$10$A49V8qOqL64S/Bae1WAc0ejbVOHfWis95U7ThBt3l.MmAT2v2U57m', 3, 'active', '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(77, 'kimheng.bell060', 'kimheng.bell060@school.local', '$2y$10$DWT2L5ki/h1sziFckrcTsOfydQ7lk28P5yYMeMGgoGwMBmRANNkXS', 3, 'active', '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(78, 'sovichea.gomez061', 'sovichea.gomez061@school.local', '$2y$10$dFi3uTuDrPWUT4ONhW9Hz.YLZ8heD2S91YQxGvf/KKa3ZCCTc4ayu', 3, 'active', '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(79, 'sreymom.kelly062', 'sreymom.kelly062@school.local', '$2y$10$udg0B5NfBJsnm6dwpf8xmeoS7x40XOmzahqWR6Il82PSSb3Rvc8vC', 3, 'active', '2026-10-04 06:16:34', '2026-10-04 06:16:34'),
	(80, 'dalin.howard063', 'dalin.howard063@school.local', '$2y$10$tbZe8hUKU7JS.A9mwyBujuYLiS5VvRFG4k/VhyR5YXr6wP/MHe7vC', 3, 'active', '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(81, 'bunroeun.ward064', 'bunroeun.ward064@school.local', '$2y$10$4hvzDR5bGoM1Kv78Bgn0vuxCS7i6GCWYWefoHoy313qRVRZKxcQla', 3, 'active', '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(82, 'sophea.cox065', 'sophea.cox065@school.local', '$2y$10$/sz96Ro9R6UpOCNu5M1h9uWyhfVs3T50D7WrtBqhHMyW2dh7GRUnO', 3, 'active', '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(83, 'khemera.diaz066', 'khemera.diaz066@school.local', '$2y$10$S6cpOSvZw1.GCEO79VoxIeGQo/97BknpEh3IGz9o52ZiV9jCR/weq', 3, 'active', '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(84, 'vireak.richardson067', 'vireak.richardson067@school.local', '$2y$10$o4drJy5elZ4PLEnQEykCg.zH52ZgPgKUeZs0oqKOKDfoWq5r1tEwW', 3, 'active', '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(85, 'sokunthea.wood068', 'sokunthea.wood068@school.local', '$2y$10$XOIJbJEYwiSiTKsybrh0bueUTf9AtxILp/kUhVDjTymezNpuFBhSe', 3, 'active', '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(86, 'rachana.watson069', 'rachana.watson069@school.local', '$2y$10$1EfAi.qIrNl0DWrPmU3J3e8H58R6wphQnwGpmTCAIaIfUp0vlZ91O', 3, 'active', '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(87, 'davin.brooks070', 'davin.brooks070@school.local', '$2y$10$Qtka0FreQ0piMFs7oxcRd.y/iHJVZhWnSVO4nuhJXboU.hOuobG9.', 3, 'active', '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(88, 'sokha.bennett071', 'sokha.bennett071@school.local', '$2y$10$7CaBRoVFcTaIFG./g24t6Ok5qxJqKfoA9QuoR4NTYxYnxdKGPCgvy', 3, 'active', '2026-10-04 06:16:35', '2026-10-04 06:16:35'),
	(89, 'mony.gray072', 'mony.gray072@school.local', '$2y$10$W0W4bRe3NGjLnU5pjfg5HuJfkuW8gBrrkJx45AEQnlx0Awo6fExiy', 3, 'active', '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(90, 'vanna.james073', 'vanna.james073@school.local', '$2y$10$ksaehbAVuBIOXPnieABf.eXxFjRToY9Kp2qGnUgxSmmayAZLdameW', 3, 'active', '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(91, 'sophea.reyes074', 'sophea.reyes074@school.local', '$2y$10$ubz7ZfqMa1ExKWrumKbzS.Iq/cRsm8yabflicGSYMtWgTL97qA2TW', 3, 'active', '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(92, 'rith.cruz075', 'rith.cruz075@school.local', '$2y$10$OIV68yoa.p9ZlhtRzayaa.9l6CTpNlS.kje0V2xH3TjdvISVG8wGS', 3, 'active', '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(93, 'nita.hughes076', 'nita.hughes076@school.local', '$2y$10$7vme3G7WiFzRaP8hSV18EOeefmPcLenYu2tb1tkj5J2pBKXhyYPwW', 3, 'active', '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(94, 'dara.price077', 'dara.price077@school.local', '$2y$10$VrjW0sG2oAK1HpOKOZL2COoLEQYFc73fBYAYFwInaPZCvHHlIaWVe', 3, 'active', '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(95, 'lina.myers078', 'lina.myers078@school.local', '$2y$10$V4CEUlvjXcHt6Nm2N8kQ1uPPVojQrz1g4GLjOnPWRkoC7NhUELvG.', 3, 'active', '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(96, 'visal.long079', 'visal.long079@school.local', '$2y$10$mO8VHlQHH171BsWsZtCXxueDJk3fAbIY/beZMdc6tBjQNf8JJHgrC', 3, 'active', '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(97, 'kosal.foster080', 'kosal.foster080@school.local', '$2y$10$Er1aU.qK/5sZSK8cinwR6.PHhvcvI8HrO5vwbNN2rZDQ6jWlxyuEa', 3, 'active', '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(98, 'bora.sanders081', 'bora.sanders081@school.local', '$2y$10$mQ9WwyWSps5r2TdmefxI6OJo./2TTs6It5Qcj/mj0sPjvYS7xMGZq', 3, 'active', '2026-10-04 06:16:36', '2026-10-04 06:16:36'),
	(99, 'chenda.ross082', 'chenda.ross082@school.local', '$2y$10$EzK78b6LI27/rpKVFc37puyN7ecIKK9htaDtFdYoUyAk6228KXl46', 3, 'active', '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(100, 'nara.morales083', 'nara.morales083@school.local', '$2y$10$Yg8iveO6F4/6Pg97ms/od.dmjNgdaYpGBojFvKrymbHDpYuzq07jy', 3, 'active', '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(101, 'kanha.powell084', 'kanha.powell084@school.local', '$2y$10$ngE49bgTtt94jP/zpfT1XuHnOyuRXxM3XYhgqr02gLqnwq.6rQF1O', 3, 'active', '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(102, 'pheak.sullivan085', 'pheak.sullivan085@school.local', '$2y$10$ujdQpSWpparzABsgCuU6w./aOmFDBJNh.CHwfrpnr7Std33JUOTFK', 3, 'active', '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(103, 'maly.russell086', 'maly.russell086@school.local', '$2y$10$iyiAvBbrCZ6vvqfKIFRae.OScWXGmE.abebq6ksTMhF1rVY.l8wEe', 3, 'active', '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(104, 'ravy.ortiz087', 'ravy.ortiz087@school.local', '$2y$10$./rlbXC9hwdDMr.xWy1/LOBQayW6vPB0SNhvVsxizfGGA4E76tMkS', 3, 'active', '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(105, 'borey.jenkins088', 'borey.jenkins088@school.local', '$2y$10$xLUc2cwyznwFbdodI2zFkOM0t7ug0YqKrDKgN33r1gAFFF4buSvYu', 3, 'active', '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(106, 'sina.gutierrez089', 'sina.gutierrez089@school.local', '$2y$10$h/LuNCSQEagKYS36664M/.2PiztvHzmGVwjLWa5JSGfj59hM7gZEi', 3, 'active', '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(107, 'mina.perry090', 'mina.perry090@school.local', '$2y$10$Q5FoMjUUVXvjrIct2ho4C.3hL8KLQGjvOlt1HxkVZ7.1F4MWWC7h.', 3, 'active', '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(108, 'sreyneang.butler091', 'sreyneang.butler091@school.local', '$2y$10$nsJkfXTmRKCzkTJKKiEBRuGuyXEwnZLnHRwm.62sKZpj7Wd1Zq/5C', 3, 'active', '2026-10-04 06:16:37', '2026-10-04 06:16:37'),
	(109, 'davy.barnes092', 'davy.barnes092@school.local', '$2y$10$uyPFLqEizVJ8UXC65QAX5./Qam.2sd2iriYVgGuIjyRYPSsX8iKk2', 3, 'active', '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(110, 'makara.fisher093', 'makara.fisher093@school.local', '$2y$10$gGn6tGgqtQ8pspRWT8D9uOcnyCH.5RU0sbgCGmVFVSRvdup18mSHS', 3, 'active', '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(111, 'nimol.henderson094', 'nimol.henderson094@school.local', '$2y$10$CVBkb7KkWSe0WO9oNBW75uJsXPNuHa1TNJm0CM5.vIpdFcFe1x7Nu', 3, 'active', '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(112, 'sovann.coleman095', 'sovann.coleman095@school.local', '$2y$10$LnUPevWTPszoSgWAE5XlBeBo8DQXErDy82HgYNbHYcbodtFhMNk2y', 3, 'active', '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(113, 'pisey.simmons096', 'pisey.simmons096@school.local', '$2y$10$MgW2kLaV04vINAwUw4FvueVR7XKLi9/6/UKAdXP53FqgQTFzg5KAu', 3, 'active', '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(114, 'ratha.patterson097', 'ratha.patterson097@school.local', '$2y$10$dInXvB2AkVqlQFRXB1i5cu54/dhg5sn19zQMIlu8ofjEsAhW4hPJy', 3, 'active', '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(115, 'kunthea.jordan098', 'kunthea.jordan098@school.local', '$2y$10$8rV2hH.qQV/MZGhP3zb.1.NBFV6fLU8HZxSRIBUoTGEVaqU0RTmX2', 3, 'active', '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(116, 'vuthy.reynolds099', 'vuthy.reynolds099@school.local', '$2y$10$5wHaR/HQClEobYHdmYIaOOphCoXQQYNZ9A7BEMDKucWpA9OEut1CS', 3, 'active', '2026-10-04 06:16:38', '2026-10-04 06:16:38'),
	(117, 'sothea.hamilton100', 'sothea.hamilton100@school.local', '$2y$10$6TTACDQoyIv3DBVgVIfs3e4uqg/bmxTdoLPzojl6bNB0szVJromzW', 3, 'active', '2026-10-04 06:16:38', '2026-10-04 06:16:38');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
