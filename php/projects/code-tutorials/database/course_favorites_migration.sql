-- Migration for Course Favorites Feature
-- Add course_favorites table to complement existing video favorites

USE programming_tutorials;

-- Create course_favorites table
CREATE TABLE IF NOT EXISTS course_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_course_favorite (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for better performance
CREATE INDEX idx_course_favorites_user_id ON course_favorites(user_id);
CREATE INDEX idx_course_favorites_course_id ON course_favorites(course_id);
CREATE INDEX idx_course_favorites_created_at ON course_favorites(created_at);
    