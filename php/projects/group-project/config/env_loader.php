<?php
/**
 * Manual Environment Loader
 * Loads environment variables from .env file without Composer
 * Prioritizes server environment variables over .env file
 */

function loadEnv($filePath = null) {
    // Default to .env in project root if no path specified
    if ($filePath === null) {
        $filePath = __DIR__ . '/../.env';
    }
    
    // If file doesn't exist, return silently (environment variables may be set by server)
    if (!file_exists($filePath)) {
        return;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments and empty lines
        if (strpos(trim($line), '#') === 0 || trim($line) === '') {
            continue;
        }
        
        // Parse KEY=VALUE format
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Only set if environment variable is not already set (server priority)
            if (getenv($key) === false) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// Auto-load environment variables
loadEnv();
?>
