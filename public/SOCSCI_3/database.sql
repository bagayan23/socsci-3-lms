CREATE DATABASE IF NOT EXISTS socsci3_lms;
USE socsci3_lms;

CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default courses
INSERT INTO courses (code, name) VALUES 
('BSCS', 'BS Computer Science'),
('BSIT', 'BS Information Technology'),
('BA PolSci', 'BA Political Science'),
('BSEd', 'Bachelor of Secondary Education')
ON DUPLICATE KEY UPDATE code=code;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('student', 'teacher') NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    extension_name VARCHAR(20),
    birthday DATE NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    
    -- Address Fields
    region VARCHAR(100),
    province VARCHAR(100),
    city VARCHAR(100),
    barangay VARCHAR(100),
    street VARCHAR(255),

    -- Student Specific Fields
    student_id VARCHAR(50), -- 00-0000 format (renamed from student_school_id)
    year_level VARCHAR(20),
    program VARCHAR(100),
    section VARCHAR(20),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    original_filename VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('quiz', 'activity', 'assignment', 'exam', 'project') NOT NULL DEFAULT 'activity',
    total_score INT DEFAULT 100,
    file_path VARCHAR(255),
    original_filename VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    student_id INT NOT NULL,
    file_path VARCHAR(255),
    text_submission TEXT,
    grade VARCHAR(50), -- Changed to VARCHAR to support letter grades
    feedback TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    graded_at TIMESTAMP NULL,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Removed separate grades table as grade/feedback are now in submissions table
