<?php
/**
 * Test script to debug enrollment data issues
 */

require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

echo "<h2>Database Connection Test</h2>";

// Test 1: Check database connection
echo "<h3>1. Database Connection</h3>";
if ($db) {
    echo "✅ Database connection: SUCCESS<br>";
} else {
    echo "❌ Database connection: FAILED<br>";
    exit;
}

// Test 2: Check table counts
echo "<h3>2. Table Data Counts</h3>";

$queries = [
    'users' => "SELECT COUNT(*) as count FROM users",
    'courses' => "SELECT COUNT(*) as count FROM courses", 
    'enrollments' => "SELECT COUNT(*) as count FROM enrollments"
];

foreach ($queries as $table => $query) {
    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch_assoc();
        echo "$table table: {$result['count']} records<br>";
    } catch (Exception $e) {
        echo "$table table: ERROR - " . $e->getMessage() . "<br>";
    }
}

// Test 3: Check sample enrollment data
echo "<h3>3. Sample Enrollment Data</h3>";
try {
    $query = "SELECT e.id, e.user_id, e.course_id, e.progress_percentage, e.enrolled_at,
                     u.username, u.full_name, c.title as course_title
              FROM enrollments e 
              JOIN users u ON e.user_id = u.id 
              JOIN courses c ON e.course_id = c.id 
              LIMIT 3";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $enrollments = $stmt->fetchAll(MYSQLI_ASSOC);
    
    echo "Found " . count($enrollments) . " enrollments with joins<br><br>";
    
    if (count($enrollments) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Username</th><th>Course</th><th>Progress</th><th>Enrolled Date</th></tr>";
        
        foreach ($enrollments as $enrollment) {
            echo "<tr>";
            echo "<td>{$enrollment['id']}</td>";
            echo "<td>{$enrollment['username']}</td>";
            echo "<td>{$enrollment['course_title']}</td>";
            echo "<td>" . number_format($enrollment['progress_percentage'], 1) . "%</td>";
            echo "<td>" . ($enrollment['enrolled_at'] ? date('M j, Y', strtotime($enrollment['enrolled_at'])) : 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No enrollment data found with proper joins<br>";
        
        // Check raw enrollments table
        echo "<h4>Raw Enrollments Table:</h4>";
        $raw_query = "SELECT * FROM enrollments LIMIT 3";
        $raw_stmt = $db->prepare($raw_query);
        $raw_stmt->execute();
        $raw_enrollments = $raw_stmt->fetchAll(MYSQLI_ASSOC);
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>User ID</th><th>Course ID</th><th>Progress</th><th>Enrolled Date</th></tr>";
        
        foreach ($raw_enrollments as $enrollment) {
            echo "<tr>";
            echo "<td>{$enrollment['id']}</td>";
            echo "<td>{$enrollment['user_id']}</td>";
            echo "<td>{$enrollment['course_id']}</td>";
            echo "<td>" . number_format($enrollment['progress_percentage'], 1) . "%</td>";
            echo "<td>" . ($enrollment['enrolled_at'] ? date('M j, Y', strtotime($enrollment['enrolled_at'])) : 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "❌ Query Error: " . $e->getMessage() . "<br>";
}

// Test 4: Check if users and courses exist for the enrollments
echo "<h3>4. User/Course Reference Check</h3>";
try {
    // Check for orphaned enrollments
    $orphan_query = "SELECT e.id, e.user_id, e.course_id
                     FROM enrollments e 
                     LEFT JOIN users u ON e.user_id = u.id 
                     LEFT JOIN courses c ON e.course_id = c.id 
                     WHERE u.id IS NULL OR c.id IS NULL";
    
    $orphan_stmt = $db->prepare($orphan_query);
    $orphan_stmt->execute();
    $orphans = $orphan_stmt->fetchAll(MYSQLI_ASSOC);
    
    if (count($orphans) > 0) {
        echo "⚠️ Found " . count($orphans) . " orphaned enrollments (missing user or course references)<br>";
        foreach ($orphans as $orphan) {
            echo "Enrollment ID {$orphan['id']}: User ID {$orphan['user_id']}, Course ID {$orphan['course_id']}<br>";
        }
    } else {
        echo "✅ All enrollments have valid user and course references<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Reference Check Error: " . $e->getMessage() . "<br>";
}

echo "<br><a href='dashboard.php'>← Back to Dashboard</a>";
?>
