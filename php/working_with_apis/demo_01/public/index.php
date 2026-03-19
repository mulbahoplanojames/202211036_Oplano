<?php
// This is the entry point for the application
// For now, we'll redirect to the API endpoints or serve as a router

// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'];
$parsed_url = parse_url($request_uri);
$path = $parsed_url['path'];

$path = ltrim($path, '/');

// Route the request
switch($path) {
    case 'api/users':
    case strpos($path, 'api/users/') === 0:
        require_once __DIR__ . '/../api/users.php';
        break;
    case 'api/courses':
    case strpos($path, 'api/courses/') === 0:
        require_once __DIR__ . '/../api/courses.php';
        break;
    case 'api/marks':
    case strpos($path, 'api/marks/') === 0:
        require_once __DIR__ . '/../api/marks.php';
        break;
    default:
        // For root path, show API info
        if (empty($path)) {
            echo '<h1>School Management API</h1>';
            echo '<p>Available endpoints:</p>';
            echo '<ul>';
            echo '<li><a href="/api/users">GET /api/users</a></li>';
            echo '<li><a href="/api/courses">GET /api/courses</a></li>';
            echo '<li><a href="/api/marks">GET /api/marks</a></li>';
            echo '</ul>';
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Endpoint not found']);
        }
        break;
}
?>
