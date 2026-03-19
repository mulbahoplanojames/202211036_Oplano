<?php
// Simple test file to diagnose issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Course Management System - Test Page</h1>";

// Test 1: Database Connection
echo "<h2>1. Testing Database Connection</h2>";
try {
    require_once 'config/database.php';
    $conn = getConnection();
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Test if database exists and has tables
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tables found: " . implode(', ', $tables) . "</p>";
    
    // Test if users table has data
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p>Users in database: " . $result['count'] . "</p>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

// Test 2: Functions
echo "<h2>2. Testing Functions</h2>";
try {
    require_once 'includes/functions.php';
    echo "<p style='color: green;'>✓ Functions loaded successfully!</p>";
    
    // Test session
    startSession();
    echo "<p style='color: green;'>✓ Session started!</p>";
    
    // Test if user is logged in
    $loggedIn = isLoggedIn();
    echo "<p>User logged in: " . ($loggedIn ? 'Yes' : 'No') . "</p>";
    
    if ($loggedIn) {
        echo "<p>User role: " . $_SESSION['user_role'] . "</p>";
        echo "<p>User name: " . $_SESSION['full_name'] . "</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>✗ Functions error: " . $e->getMessage() . "</p>";
}

// Test 3: File paths
echo "<h2>3. Testing File Paths</h2>";
$paths = [
    'config/database.php',
    'includes/functions.php',
    'auth/login.php',
    'auth/register.php',
    'admin/dashboard.php',
    'user/dashboard.php'
];

foreach ($paths as $path) {
    if (file_exists($path)) {
        echo "<p style='color: green;'>✓ $path exists</p>";
    } else {
        echo "<p style='color: red;'>✗ $path missing</p>";
    }
}

// Test 4: Current working directory
echo "<h2>4. Current Setup</h2>";
echo "<p>Current working directory: " . getcwd() . "</p>";
echo "<p>Document root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "</p>";
echo "<p>Script name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "</p>";

echo "<h2>5. Quick Links</h2>";
echo "<p><a href='auth/login.php'>Go to Login</a></p>";
echo "<p><a href='auth/register.php'>Go to Register</a></p>";
echo "<p><a href='public/index.php'>Go to Main Entry</a></p>";
?>
