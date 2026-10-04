-- ============================================================
-- Migration: student attendance per session
-- ------------------------------------------------------------
-- Run ONCE (safe to re-run):
--   phpMyAdmin -> SQL tab, paste, Execute
--   or:  mysql -u root school_management < database/migrate_attendance.sql
--
-- Adds:
--   attendance_sessions  - one row per class + date + session type
--   student_attendance   - one row per student per session
-- ============================================================

USE school_management;

-- ------------------------------------------------------------
-- 1. attendance_sessions
-- ------------------------------------------------------------
SET @t_exists = (
    SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attendance_sessions'
);
SET @sql = IF(@t_exists = 0,
    'CREATE TABLE attendance_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        subject_id INT NULL,
        session_date DATE NOT NULL,
        session_type VARCHAR(50) NOT NULL DEFAULT ''full_day'',
        notes TEXT NULL,
        created_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
        UNIQUE KEY unique_session (class_id, session_date, session_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- 2. student_attendance
-- ------------------------------------------------------------
SET @t_exists = (
    SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_attendance'
);
SET @sql = IF(@t_exists = 0,
    'CREATE TABLE student_attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id INT NOT NULL,
        student_id INT NOT NULL,
        status ENUM(''present'',''absent'',''late'',''excused'') NOT NULL DEFAULT ''present'',
        note VARCHAR(255) NULL,
        marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        UNIQUE KEY unique_student_session (session_id, student_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Verify
SHOW TABLES LIKE '%attendance%';


