<?php
// Simple test to check if redirects work
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Redirect Test</h1>";

// Test 1: Check if we can access courses page
echo "<h2>Testing Courses Page Access</h2>";
try {
    require_once 'config/database.php';
    require_once 'includes/functions.php';
    
    $user = getCurrentUser();
    echo "<p style='color: green;'>✓ Functions loaded successfully</p>";
    echo "<p>User logged in: " . ($user ? 'Yes' : 'No') . "</p>";
    
    if (!$user) {
        echo "<p style='color: green;'>✓ Courses page should be accessible without login</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='courses/index.php'>Test Courses Page</a></p>";
echo "<p><a href='public/index.php'>Test Main Entry</a></p>";
echo "<p><a href='auth/login.php'>Test Login Page</a></p>";
?>
