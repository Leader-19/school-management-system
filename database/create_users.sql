-- ============================================================
-- Create login accounts with password: 12345678
-- ------------------------------------------------------------
-- Run this in phpMyAdmin (SQL tab) or:
--   mysql -u root < database/create_users.sql
--
-- If a username/email already exists, its password is reset to
-- 12345678 instead of failing (ON DUPLICATE KEY UPDATE).
--
-- IMPORTANT: users.password stores a BCRYPT HASH, never plain text.
-- UserRepository::authenticate() calls password_verify(), which only
-- matches a hash - an account inserted as
--     INSERT INTO users (..., password, ...) VALUES ('leader', ..., '123456789', ...)
-- cannot sign in, no matter how many times you try.
-- Generate your own hash with:
--   php -r "echo password_hash('mypassword', PASSWORD_DEFAULT), PHP_EOL;"
-- ============================================================

USE school_management;

INSERT INTO users (username, email, password, role_id, status) VALUES
('admin',    'admin@school.local',    '$2y$10$VN4WQnVj3JYPlNnYFhxPxO014D7JaRbkLjQESHSGvyLyJ2SXXaVvy', 1, 'active'),
('teacher1', 'teacher1@school.local', '$2y$10$VN4WQnVj3JYPlNnYFhxPxO014D7JaRbkLjQESHSGvyLyJ2SXXaVvy', 2, 'active'),
('student1', 'student1@school.local', '$2y$10$VN4WQnVj3JYPlNnYFhxPxO014D7JaRbkLjQESHSGvyLyJ2SXXaVvy', 3, 'active')
ON DUPLICATE KEY UPDATE
    password = VALUES(password),
    status   = 'active';