<?php
/**
 * Insert sample enrollment data
 */

require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Sample student users to insert
$sample_users = [
    ['username' => 'john_student', 'email' => 'john@example.com', 'full_name' => 'John Student'],
    ['username' => 'jane_student', 'email' => 'jane@example.com', 'full_name' => 'Jane Student'],
    ['username' => 'mike_student', 'email' => 'mike@example.com', 'full_name' => 'Mike Student']
];

// Insert sample users
foreach ($sample_users as $user) {
    // Check if user already exists
    $check_query = "SELECT id FROM users WHERE username = :username";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':username', $user['username']);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() == 0) {
        // Insert user
        $insert_query = "INSERT INTO users (username, email, password_hash, full_name, role) 
                        VALUES (:username, :email, :password_hash, :full_name, 'student')";
        $insert_stmt = $db->prepare($insert_query);
        $password_hash = password_hash('password123', PASSWORD_DEFAULT);
        $insert_stmt->bindParam(':username', $user['username']);
        $insert_stmt->bindParam(':email', $user['email']);
        $insert_stmt->bindParam(':password_hash', $password_hash);
        $insert_stmt->bindParam(':full_name', $user['full_name']);
        $insert_stmt->execute();
        echo "Inserted user: " . $user['username'] . "<br>";
    } else {
        echo "User already exists: " . $user['username'] . "<br>";
    }
}

// Get user IDs
$users_query = "SELECT id, username FROM users WHERE role = 'student' ORDER BY id LIMIT 3";
$users_stmt = $db->prepare($users_query);
$users_stmt->execute();
$students = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get course IDs
$courses_query = "SELECT id, title FROM courses ORDER BY id LIMIT 3";
$courses_stmt = $db->prepare($courses_query);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Insert sample enrollments
$enrollments_to_insert = [
    ['user_id' => $students[0]['id'], 'course_id' => $courses[0]['id'], 'progress' => 75.50],
    ['user_id' => $students[1]['id'], 'course_id' => $courses[1]['id'], 'progress' => 45.25],
    ['user_id' => $students[2]['id'], 'course_id' => $courses[2]['id'], 'progress' => 90.00],
    ['user_id' => $students[0]['id'], 'course_id' => $courses[2]['id'], 'progress' => 30.75],
    ['user_id' => $students[1]['id'], 'course_id' => $courses[0]['id'], 'progress' => 60.00]
];

foreach ($enrollments_to_insert as $enrollment) {
    // Check if enrollment already exists
    $check_query = "SELECT id FROM enrollments WHERE user_id = :user_id AND course_id = :course_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':user_id', $enrollment['user_id']);
    $check_stmt->bindParam(':course_id', $enrollment['course_id']);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() == 0) {
        // Insert enrollment
        $insert_query = "INSERT INTO enrollments (user_id, course_id, progress_percentage) 
                        VALUES (:user_id, :course_id, :progress)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':user_id', $enrollment['user_id']);
        $insert_stmt->bindParam(':course_id', $enrollment['course_id']);
        $insert_stmt->bindParam(':progress', $enrollment['progress']);
        $insert_stmt->execute();
        echo "Inserted enrollment for user_id: " . $enrollment['user_id'] . ", course_id: " . $enrollment['course_id'] . "<br>";
    } else {
        echo "Enrollment already exists for user_id: " . $enrollment['user_id'] . ", course_id: " . $enrollment['course_id'] . "<br>";
    }
}

echo "<br><a href='dashboard.php'>Back to Dashboard</a>";
?>
