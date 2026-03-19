<?php
// Debug script to check server configuration
header("Content-Type: application/json; charset=UTF-8");

$debug_info = array();

// Check if mod_rewrite is enabled
$debug_info['mod_rewrite'] = in_array('mod_rewrite', apache_get_modules());

// Check current working directory
$debug_info['cwd'] = getcwd();

// Check if .htaccess exists
$debug_info['htaccess_exists'] = file_exists('.htaccess');

// Check if API files exist
$debug_info['api_files'] = array(
    'donors.php' => file_exists('api/donors.php'),
    'emergency-donors.php' => file_exists('api/emergency-donors.php'),
    'database.php' => file_exists('config/database.php')
);

// Check PHP version and extensions
$debug_info['php_version'] = phpversion();
$debug_info['pdo_mysql'] = extension_loaded('pdo_mysql');

// Check server variables
$debug_info['server_name'] = $_SERVER['SERVER_NAME'] ?? 'unknown';
$debug_info['request_uri'] = $_SERVER['REQUEST_URI'] ?? 'unknown';
$debug_info['script_name'] = $_SERVER['SCRIPT_NAME'] ?? 'unknown';

// Test database connection
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    $debug_info['database_connection'] = $db ? 'success' : 'failed';
} catch (Exception $e) {
    $debug_info['database_connection'] = 'error: ' . $e->getMessage();
}

echo json_encode($debug_info, JSON_PRETTY_PRINT);
?>
