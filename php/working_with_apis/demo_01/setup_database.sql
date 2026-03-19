CREATE DATABASE IF NOT EXISTS school_api;
USE school_api;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    credits INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Marks table
CREATE TABLE IF NOT EXISTS marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    mark DECIMAL(5,2) NOT NULL,
    grade VARCHAR(2),
    semester VARCHAR(20),
    academic_year VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO users (name, email, phone) VALUES 
('John Doe', 'john.doe@email.com', '1234567890'),
('Jane Smith', 'jane.smith@email.com', '0987654321'),
('Mike Johnson', 'mike.johnson@email.com', '1122334455');

INSERT INTO courses (course_name, course_code, description, credits) VALUES 
('Mathematics', 'MATH101', 'Basic Mathematics Course', 4),
('Physics', 'PHYS101', 'Introduction to Physics', 3),
('Computer Science', 'CS101', 'Introduction to Programming', 4),
('Chemistry', 'CHEM101', 'Basic Chemistry', 3);

INSERT INTO marks (user_id, course_id, mark, grade, semester, academic_year) VALUES 
(1, 1, 85.50, 'B+', 'Fall', '2023-2024'),
(1, 2, 92.00, 'A-', 'Fall', '2023-2024'),
(2, 1, 78.00, 'B', 'Fall', '2023-2024'),
(2, 3, 95.00, 'A', 'Fall', '2023-2024'),
(3, 2, 88.50, 'B+', 'Fall', '2023-2024'),
(3, 4, 76.00, 'B-', 'Fall', '2023-2024');
