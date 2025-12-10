-- PostgreSQL Database Schema for SOCSCI-3 LMS
-- Run this on your Render.com PostgreSQL database

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default courses
INSERT INTO courses (code, name) VALUES 
('BSCS', 'BS Computer Science'),
('BSIT', 'BS Information Technology'),
('BA PolSci', 'BA Political Science'),
('BSEd', 'Bachelor of Secondary Education')
ON CONFLICT (code) DO NOTHING;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    role VARCHAR(20) NOT NULL CHECK (role IN ('student', 'teacher')),
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
    student_id VARCHAR(50), -- 00-0000 format
    year_level VARCHAR(20),
    program VARCHAR(100),
    section VARCHAR(20),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index on email for faster lookups
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

-- Create resources table
CREATE TABLE IF NOT EXISTS resources (
    id SERIAL PRIMARY KEY,
    teacher_id INTEGER NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    original_filename VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_resources_teacher ON resources(teacher_id);

-- Create activities table
CREATE TABLE IF NOT EXISTS activities (
    id SERIAL PRIMARY KEY,
    teacher_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(20) NOT NULL DEFAULT 'activity' CHECK (type IN ('quiz', 'activity', 'assignment', 'exam', 'project')),
    total_score INTEGER DEFAULT 100,
    file_path VARCHAR(255),
    original_filename VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_activities_teacher ON activities(teacher_id);

-- Create submissions table
CREATE TABLE IF NOT EXISTS submissions (
    id SERIAL PRIMARY KEY,
    activity_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    file_path VARCHAR(255),
    text_submission TEXT,
    grade VARCHAR(50), -- VARCHAR to support letter grades
    feedback TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    graded_at TIMESTAMP NULL,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_submissions_activity ON submissions(activity_id);
CREATE INDEX IF NOT EXISTS idx_submissions_student ON submissions(student_id);
CREATE INDEX IF NOT EXISTS idx_submissions_graded ON submissions(graded_at);

-- Create function to update graded_at timestamp
CREATE OR REPLACE FUNCTION update_graded_at()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.grade IS NOT NULL AND OLD.grade IS NULL THEN
        NEW.graded_at = CURRENT_TIMESTAMP;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger to automatically set graded_at
CREATE TRIGGER set_graded_at
BEFORE UPDATE ON submissions
FOR EACH ROW
EXECUTE FUNCTION update_graded_at();

-- Grant necessary permissions (adjust username if needed)
-- GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO wilms;
-- GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO wilms;
