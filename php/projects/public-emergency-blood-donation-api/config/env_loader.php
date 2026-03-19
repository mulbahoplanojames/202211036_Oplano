<?php

/**
 * Manual environment loader for non-Composer PHP projects
 * Loads .env file and sets environment variables
 * Prioritizes existing server environment variables (like on Render)
 */

function loadEnv($filePath = null) {
    // Default to .env in project root if no path specified
    if ($filePath === null) {
        $filePath = __DIR__ . '/../.env';
    }
    
    // If file doesn't exist, return silently (might be in production)
    if (!file_exists($filePath)) {
        return;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments and lines that don't contain '='
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        
        // Split at first '=' to handle values with '='
        list($key, $value) = explode('=', $line, 2);
        
        // Trim whitespace and remove quotes if present
        $key = trim($key);
        $value = trim($value);
        
        // Remove surrounding quotes (single or double)
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        
        // Only set if not already defined (prioritize server env vars)
        if (!getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Auto-load when this file is included
loadEnv();

?>
