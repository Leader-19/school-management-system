CREATE DATABASE IF NOT EXISTS school_management;
USE school_management;

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE(role_id, permission_id)
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    teacher_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    student_code VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male','female','other'),
    address TEXT,
    phone VARCHAR(20),
    class_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    credit_hours INT DEFAULT 3
);

CREATE TABLE IF NOT EXISTS class_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE(class_id, subject_id)
);

CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    teacher_id INT NOT NULL,
    due_date DATETIME NOT NULL,
    attachments VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS assignment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    content TEXT,
    file_path VARCHAR(255) NULL,
    file_name VARCHAR(255) NULL,
    grade DECIMAL(5,2) NULL,
    feedback TEXT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    graded_at TIMESTAMP NULL,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Seed Data
INSERT INTO roles (name, description) VALUES 
('admin', 'Administrator with full access'),
('teacher', 'Teacher with access to manage classes, assignments, and grades'),
('student', 'Student with access to view assignments, submit work, and view grades');

INSERT INTO permissions (name, description) VALUES
('manage_users', 'Can manage users'),
('manage_students', 'Can manage students'),
('manage_subjects', 'Can manage subjects'),
('manage_classes', 'Can manage classes'),
('create_assignments', 'Can create assignments'),
('view_assignments', 'Can view assignments'),
('submit_assignments', 'Can submit assignments'),
('manage_grades', 'Can manage grades'),
('view_own_grades', 'Can view own grades'),
('view_dashboard', 'Can view dashboard');

-- Role Permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE name = 'admin'), id FROM permissions;

INSERT INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE name = 'teacher'), id FROM permissions WHERE name IN ('manage_students', 'manage_classes', 'create_assignments', 'view_assignments', 'manage_grades', 'view_dashboard');

INSERT INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE name = 'student'), id FROM permissions WHERE name IN ('view_assignments', 'submit_assignments', 'view_own_grades', 'view_dashboard');

-- Users (password for all: 12345678)
--
-- Passwords MUST be stored as a bcrypt hash. UserRepository::authenticate()
-- uses password_verify(), which never matches plain text - inserting
-- '12345678' directly here produces an account that cannot sign in.
-- database/create_users.sql contains ready-to-run hashes.
INSERT INTO users (username, email, password, role_id) VALUES
('admin', 'admin@school.local', '$2y$10$VN4WQnVj3JYPlNnYFhxPxO014D7JaRbkLjQESHSGvyLyJ2SXXaVvy', 1),
('teacher1', 'teacher1@school.local', '$2y$10$VN4WQnVj3JYPlNnYFhxPxO014D7JaRbkLjQESHSGvyLyJ2SXXaVvy', 2),
('student1', 'student1@school.local', '$2y$10$VN4WQnVj3JYPlNnYFhxPxO014D7JaRbkLjQESHSGvyLyJ2SXXaVvy', 3);

-- Classes
INSERT INTO classes (name, description, teacher_id) VALUES
('Class 10A', '10th Grade Section A', 2),
('Class 10B', '10th Grade Section B', 2);

-- Subjects
INSERT INTO subjects (name, code, description) VALUES
('Mathematics', 'MATH101', 'Basic Mathematics'),
('Physics', 'PHY101', 'Basic Physics'),
('English', 'ENG101', 'Basic English');

-- Class Subjects
INSERT INTO class_subjects (class_id, subject_id) VALUES (1, 1), (1, 2), (1, 3), (2, 1), (2, 3);

-- Students
INSERT INTO students (user_id, student_code, first_name, last_name, date_of_birth, gender, address, phone, class_id) VALUES
(3, 'STU001', 'John', 'Doe', '2005-05-15', 'male', '123 School Lane', '555-1234', 1);
