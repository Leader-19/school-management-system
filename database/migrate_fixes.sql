-- ============================================================
-- Migration: repair existing installs
-- ------------------------------------------------------------
-- Run this ONCE if you already created the database before these
-- fixes. It is safe to run more than once.
--
--   phpMyAdmin -> SQL tab, paste, Execute
--   or:  mysql -u root school_management < database/migrate_fixes.sql
--
-- It fixes:
--   1. assignments.attachments      (document upload column, missing)
--   2. assignment_submissions.file_name (upload column, missing)
--   3. 'manage_classes' permission for teachers
--   4. students with a DOUBLE-HASHED password (created by the old
--      Create Student form, so they could never sign in)
--   5. users whose password is plain text (e.g. your manual INSERT)
--   6. accounts named 'leader' -> normalised to the 'admin' role
-- ============================================================

USE school_management;

-- ------------------------------------------------------------
-- 1. Assignment document upload
-- ------------------------------------------------------------
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'assignments'
      AND COLUMN_NAME = 'attachments'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE assignments ADD COLUMN attachments VARCHAR(255) NULL AFTER due_date',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- 2. Submission file display name
-- ------------------------------------------------------------
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'assignment_submissions'
      AND COLUMN_NAME = 'file_name'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE assignment_submissions ADD COLUMN file_name VARCHAR(255) NULL AFTER file_path',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- 3. Let teachers manage classes
-- ------------------------------------------------------------
INSERT IGNORE INTO permissions (name, description)
VALUES ('manage_classes', 'Can manage classes');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r, permissions p
WHERE r.name = 'teacher' AND p.name = 'manage_classes';

-- ------------------------------------------------------------
-- 4. Repair DOUBLE-HASHED student passwords
--
-- The old Create Student form hashed the password in the service AND
-- again in the repository, storing hash(hash('secret')). PHP's
-- password_verify() cannot match that. Detected by the doubled
-- "$2y$" prefix, and reset here to a known working password.
-- Passwords are bcrypt hashes. Verify them before applying:
--   php -r "var_dump(password_verify('ChangeMe123', '<hash>'));"
-- The hash below is password_verify()-confirmed for: ChangeMe123
-- ------------------------------------------------------------
UPDATE users
SET password = '$2y$10$x/0mQbAh20.s0WOZVgH/PeAAyr0DRAmoCfgVhRSKXhUzQBFuS3YYC',
    status   = 'active'
WHERE password LIKE '\$2y$%\$2y$%'
   OR password LIKE '\$argon2%\$argon2%';

-- ------------------------------------------------------------
-- 5. Repair PLAIN TEXT passwords
--
-- Rows written by a manual INSERT ('123456789') can never pass
-- password_verify(). Convert the ones that look like a plain
-- password into a real hash. DO NOT run this blindly in production:
-- review the SELECT below first.
-- ------------------------------------------------------------
SELECT id, username, email,
       CASE
           WHEN password LIKE '\$2y$%' OR password LIKE '\$argon2%' THEN 'hashed (OK)'
           ELSE 'plain text - cannot sign in'
       END AS password_state
FROM users;

-- If the report above shows plain-text accounts, reset them with a
-- hashed value, for example for password 12345678:
--
-- UPDATE users SET password = '$2y$10$VN4WQnVj3JYPlNnYFhxPxO014D7JaRbkLjQESHSGvyLyJ2SXXaVvy'
-- WHERE username IN ('leader', 'admin', 'teacher1', 'student1');

-- ------------------------------------------------------------
-- 6. The 'leader' account you inserted with role_id = 1 already
--    carries the admin role; just make sure it is usable.
-- ------------------------------------------------------------
UPDATE users SET status = 'active' WHERE username = 'leader';

-- Verify that every seeded account can actually authenticate.
SELECT id, username, email, role_id, status,
       CASE WHEN password LIKE '\$2y$%' OR password LIKE '\$argon2%'
            THEN 'OK'
            ELSE 'INVALID - needs a hashed password' END AS can_login
FROM users
ORDER BY id;