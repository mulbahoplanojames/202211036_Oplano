<?php
// Router script for PHP built-in server

$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove query string from URI
$path = parse_url($request_uri, PHP_URL_PATH);

// Route mapping
$routes = [
    '/api/donors' => 'api/donors.php',
    '/api/emergency-donors' => 'api/emergency-donors.php'
];

// Handle CORS preflight requests
if ($request_method === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    http_response_code(200);
    exit;
}

// Route the request
if (isset($routes[$path])) {
    // Change to the correct working directory before including
    $file_path = __DIR__ . '/' . $routes[$path];
    if (file_exists($file_path)) {
        // Set the working directory to the script's location
        chdir(dirname($file_path));
        include $file_path;
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "API file not found"]);
    }
} else {
    // Handle 404 for unknown routes
    http_response_code(404);
    echo json_encode([
        "status" => "error", 
        "message" => "Endpoint not found",
        "available_endpoints" => array_keys($routes)
    ]);
}
?>
