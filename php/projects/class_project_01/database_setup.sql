-- Course Management System Database Schema
-- Database: course_management

-- Create database
CREATE DATABASE IF NOT EXISTS course_management;
USE course_management;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role)
);

-- Courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    instructor VARCHAR(100),
    duration_weeks INT DEFAULT 8,
    max_students INT DEFAULT 50,
    current_enrollments INT DEFAULT 0,
    start_date DATE,
    end_date DATE,
    status ENUM('upcoming', 'active', 'completed') DEFAULT 'upcoming',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_start_date (start_date)
);

-- Enrollments table (junction table for users and courses)
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
    grade DECIMAL(5,2) DEFAULT NULL,
    UNIQUE KEY unique_enrollment (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_course_id (course_id),
    INDEX idx_status (status)
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('admin', 'admin@coursemgmt.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');

-- Insert sample courses
INSERT INTO courses (title, description, instructor, duration_weeks, max_students, start_date, end_date, status, created_by) VALUES 
('Introduction to Web Development', 'Learn the fundamentals of HTML, CSS, and JavaScript to build modern websites.', 'John Smith', 12, 30, '2024-01-15', '2024-04-15', 'active', 1),
('Advanced PHP Programming', 'Master PHP with advanced concepts including OOP, security, and framework development.', 'Jane Doe', 10, 25, '2024-02-01', '2024-04-15', 'active', 1),
('Database Design and Management', 'Comprehensive course on database design, SQL, and optimization techniques.', 'Robert Johnson', 8, 20, '2024-03-01', '2024-04-30', 'upcoming', 1),
('Frontend Frameworks', 'Explore modern frontend frameworks like React, Vue, and Angular.', 'Emily Brown', 6, 35, '2024-04-01', '2024-05-15', 'upcoming', 1);

-- Insert sample regular users
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('student1', 'student1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Johnson', 'user'),
('student2', 'student2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Wilson', 'user'),
('student3', 'student3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Charlie Davis', 'user');

-- Sample enrollments
INSERT INTO enrollments (user_id, course_id, status) VALUES 
(2, 1, 'active'),
(3, 1, 'active'),
(4, 2, 'active'),
(2, 3, 'active');
