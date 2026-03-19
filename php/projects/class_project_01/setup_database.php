<?php
// Database setup script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Setup</h1>";

try {
    // Connect to MySQL without specifying database first
    $host = '127.0.0.1:3307';
    $user = 'root';
    $pass = 'newstrongpassword';
    
    $conn = new PDO("mysql:host=$host", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Connected to MySQL server</p>";
    
    // Create database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS course_management");
    echo "<p style='color: green;'>✓ Database 'course_management' created/verified</p>";
    
    // Switch to the course_management database
    $conn->exec("USE course_management");
    
    // Read and execute the SQL setup file
    $sqlFile = __DIR__ . '/database_setup.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Remove comments and split into statements
        $sql = preg_replace('/--.*$/m', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $conn->exec($statement);
                } catch(PDOException $e) {
                    // Ignore "already exists" errors
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "<p style='color: orange;'>Warning: " . $e->getMessage() . "</p>";
                    }
                }
            }
        }
        
        echo "<p style='color: green;'>✓ Database setup completed</p>";
        
        // Verify setup
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $users = $stmt->fetch();
        echo "<p>Users created: " . $users['count'] . "</p>";
        
        $stmt = $conn->query("SELECT COUNT(*) as count FROM courses");
        $courses = $stmt->fetch();
        echo "<p>Courses created: " . $courses['count'] . "</p>";
        
        echo "<p><a href='test.php'>Test the application</a></p>";
        echo "<p><a href='public/index.php'>Go to application</a></p>";
        
    } else {
        echo "<p style='color: red;'>✗ database_setup.sql file not found</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>✗ Database setup failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your MySQL credentials and ensure MySQL is running.</p>";
}
?>
