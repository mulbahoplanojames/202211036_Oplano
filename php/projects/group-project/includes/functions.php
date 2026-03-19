<?php
/**
 * Common Functions
 * Curated Programming Tutorials Web Platform
 */

// Start session
session_start();

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect to a page
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Format view count
 */
function formatViews($views) {
    if ($views >= 1000000) {
        return number_format($views / 1000000, 1) . 'M';
    } elseif ($views >= 1000) {
        return number_format($views / 1000, 1) . 'K';
    } else {
        return number_format($views);
    }
}

/**
 * Format date
 */
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

/**
 * Format watch time
 */
function formatWatchTime($seconds) {
    if ($seconds < 60) {
        return $seconds . 's';
    } elseif ($seconds < 3600) {
        return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
    } else {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return $hours . 'h ' . $minutes . 'm';
    }
}

/**
 * Get YouTube video ID from URL
 */
function getYouTubeVideoId($url) {
    $video_id = "";
    if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $url, $matches)) {
        $video_id = $matches[1];
    } elseif (preg_match('/youtu\.be\/([^?]+)/', $url, $matches)) {
        $video_id = $matches[1];
    }
    return $video_id;
}

/**
 * Get YouTube thumbnail URL
 */
function getYouTubeThumbnail($video_id) {
    return "https://img.youtube.com/vi/$video_id/hqdefault.jpg";
}

/**
 * Generate YouTube embed URL
 */
function getYouTubeEmbedUrl($video_id) {
    return "https://www.youtube.com/embed/$video_id";
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Display alert message
 */
function displayAlert($type, $message) {
    return "<div class='alert alert-$type'>$message</div>";
}

/**
 * Get user by ID
 */
function getUserById($db, $user_id) {
    $query = "SELECT * FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get course by ID
 */
function getCourseById($db, $course_id) {
    $query = "SELECT * FROM courses WHERE id = :course_id AND is_active = 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get videos by course ID
 */
function getVideosByCourseId($db, $course_id) {
    $query = "SELECT * FROM videos WHERE course_id = :course_id AND is_active = 1 ORDER BY views_count DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Check if user is enrolled in course
 */
function isUserEnrolled($db, $user_id, $course_id) {
    $query = "SELECT id FROM enrollments WHERE user_id = :user_id AND course_id = :course_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

/**
 * Enroll user in course
 */
function enrollUserInCourse($db, $user_id, $course_id) {
    $query = "INSERT INTO enrollments (user_id, course_id) VALUES (:user_id, :course_id)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':course_id', $course_id);
    return $stmt->execute();
}

/**
 * Check if user has favorited a course
 */
function isCourseFavorited($db, $user_id, $course_id) {
    $query = "SELECT id FROM course_favorites WHERE user_id = :user_id AND course_id = :course_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

/**
 * Add course to user favorites
 */
function addCourseToFavorites($db, $user_id, $course_id) {
    $query = "INSERT INTO course_favorites (user_id, course_id) VALUES (:user_id, :course_id)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':course_id', $course_id);
    return $stmt->execute();
}

/**
 * Remove course from user favorites
 */
function removeCourseFromFavorites($db, $user_id, $course_id) {
    $query = "DELETE FROM course_favorites WHERE user_id = :user_id AND course_id = :course_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':course_id', $course_id);
    return $stmt->execute();
}

/**
 * Get user's favorite courses
 */
function getUserFavoriteCourses($db, $user_id) {
    $query = "SELECT c.*, cf.created_at as favorited_at 
              FROM courses c 
              JOIN course_favorites cf ON c.id = cf.course_id 
              WHERE cf.user_id = :user_id AND c.is_active = 1 
              ORDER BY cf.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get total count of user's favorite courses
 */
function getUserFavoriteCoursesCount($db, $user_id) {
    $query = "SELECT COUNT(*) as count 
              FROM course_favorites cf 
              JOIN courses c ON cf.course_id = c.id 
              WHERE cf.user_id = :user_id AND c.is_active = 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'];
}

/**
 * Toggle course favorite status (add if not favorited, remove if favorited)
 */
function toggleCourseFavorite($db, $user_id, $course_id) {
    if (isCourseFavorited($db, $user_id, $course_id)) {
        return removeCourseFromFavorites($db, $user_id, $course_id);
    } else {
        return addCourseToFavorites($db, $user_id, $course_id);
    }
}

/**
 * Get or create video progress record
 */
function getVideoProgress($db, $user_id, $video_id) {
    $query = "SELECT * FROM video_progress WHERE user_id = :user_id AND video_id = :video_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':video_id', $video_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update video progress
 */
function updateVideoProgress($db, $user_id, $video_id, $watched_duration, $completed = false) {
    // Check if progress record exists
    $existing = getVideoProgress($db, $user_id, $video_id);
    
    if ($existing) {
        // Update existing record
        $query = "UPDATE video_progress 
                  SET watched_duration = :watched_duration, 
                      completed = :completed,
                      last_watched_at = CURRENT_TIMESTAMP
                  WHERE user_id = :user_id AND video_id = :video_id";
    } else {
        // Insert new record
        $query = "INSERT INTO video_progress 
                  (user_id, video_id, watched_duration, completed, last_watched_at) 
                  VALUES (:user_id, :video_id, :watched_duration, :completed, CURRENT_TIMESTAMP)";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':video_id', $video_id);
    $stmt->bindParam(':watched_duration', $watched_duration);
    $stmt->bindParam(':completed', $completed);
    
    return $stmt->execute();
}

/**
 * Get user's video progress for a course
 */
function getCourseVideoProgress($db, $user_id, $course_id) {
    $query = "SELECT v.*, vp.watched_duration, vp.completed, vp.last_watched_at
              FROM videos v
              LEFT JOIN video_progress vp ON v.id = vp.video_id AND vp.user_id = :user_id
              WHERE v.course_id = :course_id AND v.is_active = 1
              ORDER BY v.id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Calculate course completion percentage
 */
function calculateCourseProgress($db, $user_id, $course_id) {
    $videos_query = "SELECT COUNT(*) as total FROM videos WHERE course_id = :course_id AND is_active = 1";
    $stmt = $db->prepare($videos_query);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->execute();
    $total_videos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($total_videos == 0) {
        return 0;
    }
    
    $completed_query = "SELECT COUNT(*) as completed 
                        FROM video_progress vp 
                        JOIN videos v ON vp.video_id = v.id 
                        WHERE vp.user_id = :user_id AND v.course_id = :course_id AND vp.completed = 1";
    $stmt = $db->prepare($completed_query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->execute();
    $completed_videos = $stmt->fetch(PDO::FETCH_ASSOC)['completed'];
    
    return round(($completed_videos / $total_videos) * 100, 2);
}

/**
 * Update course enrollment progress
 */
function updateEnrollmentProgress($db, $user_id, $course_id) {
    $progress_percentage = calculateCourseProgress($db, $user_id, $course_id);
    
    $query = "UPDATE enrollments 
              SET progress_percentage = :progress_percentage 
              WHERE user_id = :user_id AND course_id = :course_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':progress_percentage', $progress_percentage);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':course_id', $course_id);
    
    return $stmt->execute();
}

/**
 * Get user's recently watched videos with progress
 */
function getRecentlyWatchedVideos($db, $user_id, $limit = 6) {
    $query = "SELECT v.*, c.title as course_title, vp.watched_duration, vp.completed, vp.last_watched_at
              FROM videos v
              JOIN video_progress vp ON v.id = vp.video_id
              JOIN courses c ON v.course_id = c.id
              WHERE vp.user_id = :user_id AND v.is_active = 1
              ORDER BY vp.last_watched_at DESC
              LIMIT :limit";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get video progress statistics for user
 */
function getUserVideoStats($db, $user_id) {
    $stats = [];
    
    // Total videos watched
    $query = "SELECT COUNT(*) as total_watched FROM video_progress WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $stats['total_watched'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_watched'];
    
    // Total videos completed
    $query = "SELECT COUNT(*) as total_completed FROM video_progress WHERE user_id = :user_id AND completed = 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $stats['total_completed'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_completed'];
    
    // Total watch time
    $query = "SELECT SUM(watched_duration) as total_time FROM video_progress WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_watch_time'] = $result['total_time'] ? $result['total_time'] : 0;
    
    return $stats;
}
?>
