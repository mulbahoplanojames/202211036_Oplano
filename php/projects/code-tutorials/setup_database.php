<?php
/**
 * Database Setup Script
 * This script will create the database and insert sample data
 */

echo "<h1>Database Setup</h1>";

// Load environment variables
require_once 'config/env_loader.php';

// Database configuration
$host = getenv('DB_HOST') ?: '127.0.0.1';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'programming_tutorials';
$port = getenv('DB_PORT') ?: '3306';

try {
    // Connect without selecting database first
    $conn = new mysqli($host, $username, $password, '', $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color: green;'>✓ Connected to MySQL server</p>";
    
    // Create database if not exists
    $create_db = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($create_db)) {
        echo "<p style='color: green;'>✓ Database '$db_name' created or already exists</p>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db($db_name);
    
    // Read and execute schema
    $schema_file = __DIR__ . '/database/schema.sql';
    if (file_exists($schema_file)) {
        $schema = file_get_contents($schema_file);
        
        // Remove the CREATE DATABASE and USE statements since we already handled them
        $schema = preg_replace('/^(CREATE DATABASE|USE)[^;]*;/m', '', $schema);
        
        // Split by semicolons and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                if (!$conn->query($statement)) {
                    echo "<p style='color: orange;'>⚠ Warning: " . $conn->error . "</p>";
                }
            }
        }
        
        echo "<p style='color: green;'>✓ Database schema imported successfully</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Schema file not found</p>";
    }
    
    // Test the users table
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    echo "<p style='color: green;'>✓ Users table contains {$row['count']} records</p>";
    
    // Show sample users
    $result = $conn->query("SELECT id, username, email, full_name, role FROM users LIMIT 5");
    echo "<h3>Sample Users:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Role</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['full_name']}</td>";
        echo "<td>{$row['role']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p style='color: green; font-weight: bold;'>✓ Database setup completed successfully!</p>";
    echo "<p><a href='admin/users.php'>Go to Users Management</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in the .env file</p>";
}

if (isset($conn)) {
    $conn->close();
}
?>
