-- Curated Programming Tutorials Web Platform Database Schema
-- Created for MySQL/MariaDB

-- Create database
CREATE DATABASE IF NOT EXISTS programming_tutorials CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE programming_tutorials;

-- Users table for authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('admin', 'student') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    programming_language VARCHAR(50) NOT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    thumbnail_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Videos table for YouTube tutorials
CREATE TABLE videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    youtube_video_id VARCHAR(20) NOT NULL,
    youtube_url VARCHAR(255) NOT NULL,
    thumbnail_url VARCHAR(255),
    views_count BIGINT DEFAULT 0,
    likes_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    duration VARCHAR(20),
    channel_name VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enrollments table for student course enrollments
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    UNIQUE KEY unique_enrollment (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- User favorites table
CREATE TABLE user_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    video_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favorite (user_id, video_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

-- Video progress tracking
CREATE TABLE video_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    video_id INT NOT NULL,
    watched_duration INT DEFAULT 0, -- seconds watched
    completed BOOLEAN DEFAULT FALSE,
    last_watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_progress (user_id, video_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('admin', 'admin@tutorialplatform.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');

    -- Insert sample courses
    INSERT INTO courses (title, description, programming_language, difficulty_level) VALUES 
    ('Python Programming for Beginners', 'Learn Python from scratch with hands-on tutorials', 'Python', 'beginner'),
    ('Java Development Fundamentals', 'Master Java programming concepts and build applications', 'Java', 'beginner'),
    ('JavaScript Web Development', 'Create interactive web applications with JavaScript', 'JavaScript', 'intermediate'),
    ('PHP Backend Development', 'Build dynamic websites with PHP and MySQL', 'PHP', 'intermediate'),
    ('C++ Programming Essentials', 'Learn C++ for system programming and game development', 'C++', 'advanced');

-- Insert sample videos (these would normally be fetched from YouTube API)
INSERT INTO videos (course_id, title, description, youtube_video_id, youtube_url, thumbnail_url, views_count, likes_count, comments_count, channel_name) VALUES 
(1, 'Python Tutorial for Beginners 1: Intro and Setup', 'Complete Python tutorial for absolute beginners', 'rfscVS0vtbw', 'https://www.youtube.com/watch?v=rfscVS0vtbw', 'https://img.youtube.com/vi/rfscVS0vtbw/default.jpg', 2500000, 75000, 3200, 'Programming with Mosh'),
(1, 'Python Full Course for Beginners', 'Comprehensive Python course covering all basics', 'eWRfhZUzrAc', 'https://www.youtube.com/watch?v=eWRfhZUzrAc', 'https://img.youtube.com/vi/eWRfhZUzrAc/default.jpg', 1800000, 54000, 2100, 'freeCodeCamp'),
(2, 'Java Tutorial for Beginners', 'Complete Java programming tutorial', 'GoXwIVyj87M', 'https://www.youtube.com/watch?v=GoXwIVyj87M', 'https://img.youtube.com/vi/GoXwIVyj87M/default.jpg', 3200000, 96000, 4500, 'Programming with Mosh'),
(2, 'Java Full Course for Free', 'Complete Java development course', 'grEKMHUynfs', 'https://www.youtube.com/watch?v=grEKMHUynfs', 'https://img.youtube.com/vi/grEKMHUynfs/default.jpg', 1500000, 45000, 1800, 'freeCodeCamp'),
(3, 'JavaScript Tutorial for Beginners', 'Learn JavaScript from scratch', 'W6NZfCO5SIk', 'https://www.youtube.com/watch?v=W6NZfCO5SIk', 'https://img.youtube.com/vi/W6NZfCO5SIk/default.jpg', 2800000, 84000, 3800, 'Programming with Mosh'),
(3, 'JavaScript Full Course', 'Complete JavaScript bootcamp', 'PkZNo7MFNFg', 'https://www.youtube.com/watch?v=PkZNo7MFNFg', 'https://img.youtube.com/vi/PkZNo7MFNFg/default.jpg', 1900000, 57000, 2400, 'freeCodeCamp'),
(4, 'PHP Tutorial for Beginners', 'Learn PHP programming from scratch', 'OK_JCtrrv-c', 'https://www.youtube.com/watch?v=OK_JCtrrv-c', 'https://img.youtube.com/vi/OK_JCtrrv-c/default.jpg', 1200000, 36000, 1500, 'Programming with Mosh'),
(4, 'PHP Full Course for Beginners', 'Complete PHP development tutorial', 'BASzovN0xk8', 'https://www.youtube.com/watch?v=BASzovN0xk8', 'https://img.youtube.com/vi/BASzovN0xk8/default.jpg', 980000, 29000, 1200, 'freeCodeCamp'),
(5, 'C++ Tutorial for Beginners', 'Learn C++ programming fundamentals', 'vLnPwxZdW4Y', 'https://www.youtube.com/watch?v=vLnPwxZdW4Y', 'https://img.youtube.com/vi/vLnPwxZdW4Y/default.jpg', 2100000, 63000, 2800, 'Programming with Mosh'),
(5, 'C++ Full Course', 'Complete C++ programming tutorial', '8jLOx1hD4_o', 'https://www.youtube.com/watch?v=8jLOx1hD4_o', 'https://img.youtube.com/vi/8jLOx1hD4_o/default.jpg', 1300000, 39000, 1600, 'freeCodeCamp');


UPDATE users SET role = 'admin' WHERE id = 1;

-- Insert sample student users
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('john_student', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Student', 'student'),
('jane_student', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Student', 'student'),
('mike_student', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Student', 'student');

-- Insert sample enrollments
INSERT INTO enrollments (user_id, course_id, progress_percentage) VALUES 
(2, 1, 75.50),  -- John enrolled in Python with 75.5% progress
(3, 2, 45.25),  -- Jane enrolled in Java with 45.25% progress
(4, 3, 90.00),  -- Mike enrolled in JavaScript with 90% progress
(2, 3, 30.75),  -- John also enrolled in JavaScript with 30.75% progress
(3, 1, 60.00);  -- Jane also enrolled in Python with 60% progress