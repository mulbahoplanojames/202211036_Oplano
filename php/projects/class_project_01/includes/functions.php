<?php
// Helper functions for the Course Management System

// Start session if not already started
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    startSession();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Redirect to a specific page
function redirect($url) {
    header("Location: $url");
    exit();
}

// Sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Generate CSRF token
function generateCSRFToken() {
    startSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Display alert messages
function showAlert($message, $type = 'danger') {
    startSession();
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Get and clear alert message
function getAlert() {
    startSession();
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

// Format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Check if user is enrolled in a course
function isEnrolled($userId, $courseId) {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    return $stmt->fetch() !== false;
}

// Middleware to check if user is logged in
function requireAuth() {
    startSession();
    if (!isLoggedIn()) {
        showAlert('Please login to access this page.', 'warning');
        redirect('../auth/login.php');
    }
}

// Function to get current user data
function getCurrentUser() {
    startSession();
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['user_role']
        ];
    }
    return null;
}

// Function to get user's enrolled courses
function getUserEnrollments($userId) {
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT c.*, e.enrollment_date, e.status as enrollment_status, e.grade
        FROM courses c
        JOIN enrollments e ON c.id = e.course_id
        WHERE e.user_id = ?
        ORDER BY e.enrollment_date DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Function to get all courses (for admin)
function getAllCourses() {
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT c.*, u.full_name as created_by_name
        FROM courses c
        LEFT JOIN users u ON c.created_by = u.id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Function to get available courses for enrollment
function getAvailableCourses($userId = null) {
    $conn = getConnection();
    
    if ($userId) {
        // Get courses user is not enrolled in
        $stmt = $conn->prepare("
            SELECT c.*, 
                   (c.max_students - c.current_enrollments) as available_spots,
                   CASE WHEN e.user_id IS NOT NULL THEN 1 ELSE 0 END as is_enrolled
            FROM courses c
            LEFT JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
            WHERE c.status IN ('upcoming', 'active')
            AND (e.user_id IS NULL OR e.user_id != ?)
            ORDER BY c.start_date ASC
        ");
        $stmt->execute([$userId, $userId]);
    } else {
        // Get all active/upcoming courses
        $stmt = $conn->prepare("
            SELECT c.*, (c.max_students - c.current_enrollments) as available_spots
            FROM courses c
            WHERE c.status IN ('upcoming', 'active')
            ORDER BY c.start_date ASC
        ");
        $stmt->execute();
    }
    
    return $stmt->fetchAll();
}

// Function to create a new course (admin only)
function createCourse($title, $description, $instructor, $durationWeeks, $maxStudents, $startDate, $endDate, $createdBy) {
    $conn = getConnection();
    $stmt = $conn->prepare("
        INSERT INTO courses (title, description, instructor, duration_weeks, max_students, start_date, end_date, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$title, $description, $instructor, $durationWeeks, $maxStudents, $startDate, $endDate, $createdBy]);
}

// Function to update a course (admin only)
function updateCourse($courseId, $title, $description, $instructor, $durationWeeks, $maxStudents, $startDate, $endDate) {
    $conn = getConnection();
    $stmt = $conn->prepare("
        UPDATE courses 
        SET title = ?, description = ?, instructor = ?, duration_weeks = ?, max_students = ?, start_date = ?, end_date = ?
        WHERE id = ?
    ");
    return $stmt->execute([$title, $description, $instructor, $durationWeeks, $maxStudents, $startDate, $endDate, $courseId]);
}

// Function to delete a course (admin only)
function deleteCourse($courseId) {
    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    return $stmt->execute([$courseId]);
}

// Function to enroll user in a course
function enrollInCourse($userId, $courseId) {
    $conn = getConnection();
    
    // Check if course has available spots
    $stmt = $conn->prepare("SELECT current_enrollments, max_students FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
    
    if (!$course || $course['current_enrollments'] >= $course['max_students']) {
        return false;
    }
    
    try {
        $conn->beginTransaction();
        
        // Add enrollment
        $stmt = $conn->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
        $stmt->execute([$userId, $courseId]);
        
        // Update course enrollment count
        $stmt = $conn->prepare("UPDATE courses SET current_enrollments = current_enrollments + 1 WHERE id = ?");
        $stmt->execute([$courseId]);
        
        $conn->commit();
        return true;
    } catch (PDOException $e) {
        $conn->rollback();
        return false;
    }
}

// Function to unenroll user from a course
function unenrollFromCourse($userId, $courseId) {
    $conn = getConnection();
    
    try {
        $conn->beginTransaction();
        
        // Remove enrollment
        $stmt = $conn->prepare("DELETE FROM enrollments WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$userId, $courseId]);
        
        // Update course enrollment count
        $stmt = $conn->prepare("UPDATE courses SET current_enrollments = current_enrollments - 1 WHERE id = ?");
        $stmt->execute([$courseId]);
        
        $conn->commit();
        return true;
    } catch (PDOException $e) {
        $conn->rollback();
        return false;
    }
}
?>
