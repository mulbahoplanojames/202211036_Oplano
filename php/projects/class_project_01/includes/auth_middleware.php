<?php
require_once '../config/database.php';
require_once 'functions.php';

// Middleware to check if user is admin
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        showAlert('Access denied. Administrator privileges required.', 'danger');
        redirect('../user/dashboard.php');
    }
}

// Middleware to check if user is regular user (not admin)
function requireUser() {
    requireAuth();
    if (isAdmin()) {
        showAlert('This page is for students only.', 'warning');
        redirect('../admin/dashboard.php');
    }
}

// Function to check if user can access a specific course
function canAccessCourse($userId, $courseId) {
    $conn = getConnection();
    
    // Admin can access all courses
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user && $user['role'] === 'admin') {
        return true;
    }
    
    // Regular users can access courses they're enrolled in
    $stmt = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    return $stmt->fetch() !== false;
}
?>
