<?php
// Set headers to allow cross-origin requests and specify JSON content type
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration (for future use)
define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASS', 'password');
define('DB_NAME', 'groupprime_portfolio');

// Sample data for projects
$projects = [
    [
        'id' => 1,
        'title' => 'CodeTutorials Platform',
        'description' => 'A full-stack web application that curates and recommends high-quality programming tutorials from YouTube, offering a focused and distraction-free learning experience.',
        'image' => './images/course.png',
        'github' => '#',
        'demo' => 'https://codetutorials.onrender.com/index.php',
        'technologies' => ['HTML', 'CSS', 'JavaScript', 'PHP'],
        'featured' => true,
        'created_at' => '2024-01-15'
    ],
    [
        'id' => 2,
        'title' => 'Blood Bank Bonor API',
        'description' => 'This API helps hospitals, healthcare institutions, and NGOs quickly find available blood donors in emergency situations. It provides real-time access to donor information including blood type, location, and contact details.',
        'image' => './images/api.png',
        'github' => '#',
        'demo' => 'https://lifelineapi-93c3.onrender.com',
        'technologies' => ['HTML', 'CSS', 'JavaScript', 'PHP'],
        'featured' => true,
        'created_at' => '2024-02-20'
    ],
];

// Sample data for team members
$teamMembers = [
    [
        'id' => 1,
        'name' => 'Oplano James Mulbah',
        'role' => '202211036',
        'bio' => 'Passionate developer with 5+ years of experience in building scalable web applications.',
        'initials' => 'OM',
        'github' => '#',
        'linkedin' => '#',
        'twitter' => '#',
        'email' => 'mulbahoplanojames@gmail.com',
        'skills' => ['JavaScript', 'React', 'Node.js', 'MongoDB'],
        'joined' => '2022-01-15'
    ],
    [
        'id' => 2,
        'name' => 'William Adam Williams',
        'role' => '202211345',
        'bio' => 'Creative designer focused on user-centered design and creating intuitive digital experiences.',
        'initials' => 'WA',
        'github' => '#',
        'linkedin' => '#',
        'twitter' => '#',
        'email' => 'williamadamwilliams@gmail.com',
        'skills' => ['Figma', 'Adobe XD', 'Sketch', 'Prototyping'],
        'joined' => '2022-03-20'
    ],
    [
        'id' => 3,
        'name' => 'Christine Sankely',
        'role' => '202211255',
        'bio' => 'Backend specialist with expertise in building robust APIs and managing complex databases.',
        'initials' => 'CS',
        'github' => '#',
        'linkedin' => '#',
        'twitter' => '#',
        'email' => 'christinesankely@gmail.com',
        'skills' => ['Python', 'Django', 'PostgreSQL', 'AWS'],
        'joined' => '2021-11-10'
    ],
    [
        'id' => 4,
        'name' => 'Jamel Will Smith',
        'role' => '202211165',
        'bio' => 'Frontend enthusiast with a keen eye for detail and passion for creating beautiful interfaces.',
        'initials' => 'JW',
        'github' => '#',
        'linkedin' => '#',
        'twitter' => '#',
        'email' => 'jamelwillsmith@gmail.com',
        'skills' => ['Vue.js', 'TypeScript', 'CSS', 'Webpack'],
        'joined' => '2022-06-01'
    ],
    [
        'id' => 5,
        'name' => 'Abdallah Aleer',
        'role' => '202211165',
        'bio' => 'DevOps expert focused on automation, CI/CD pipelines, and cloud infrastructure management.',
        'initials' => 'AA',
        'github' => '#',
        'linkedin' => '#',
        'twitter' => '#',
        'email' => 'abdallahaleer@gmail.com',
        'skills' => ['Docker', 'Kubernetes', 'Jenkins', 'Terraform'],
        'joined' => '2021-09-15'
    ],
     [
        'id' => 6,
        'name' => 'Narmin Stanley',
        'role' => '202211195',
        'bio' => 'DevOps expert focused on automation, CI/CD pipelines, and cloud infrastructure management.',
        'initials' => 'NS',
        'github' => '#',
        'linkedin' => '#',
        'twitter' => '#',
        'email' => 'narminstanley@gmail.com',
        'skills' => ['Docker', 'Kubernetes', 'Jenkins', 'Terraform'],
        'joined' => '2021-09-15'
    ],
 
];

// Handle API requests
try {
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestUri = $_SERVER['REQUEST_URI'];
    $path = parse_url($requestUri, PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));

    // Get the endpoint (last part of the path)
    $endpoint = end($pathParts);

    switch ($endpoint) {
        case 'projects':
            handleProjectsRequest($requestMethod, $projects);
            break;
        case 'team':
            handleTeamRequest($requestMethod, $teamMembers);
            break;
        case 'stats':
            handleStatsRequest($projects, $teamMembers);
            break;
        default:
            sendErrorResponse('Endpoint not found', 404);
            break;
    }
} catch (Exception $e) {
    sendErrorResponse('Internal server error: ' . $e->getMessage(), 500);
}

// Handle projects requests
function handleProjectsRequest($method, $projects) {
    switch ($method) {
        case 'GET':
            // Get query parameters
            $featured = isset($_GET['featured']) ? filter_var($_GET['featured'], FILTER_VALIDATE_BOOLEAN) : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';

            // Filter projects
            $filteredProjects = $projects;
            if ($featured !== null) {
                $filteredProjects = array_filter($filteredProjects, function($project) use ($featured) {
                    return $project['featured'] === $featured;
                });
            }

            // Sort projects
            usort($filteredProjects, function($a, $b) use ($sort) {
                switch ($sort) {
                    case 'title':
                        return strcmp($a['title'], $b['title']);
                    case 'created_at':
                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                    default:
                        return $a['id'] - $b['id'];
                }
            });

            // Limit results
            if ($limit) {
                $filteredProjects = array_slice($filteredProjects, 0, $limit);
            }

            sendSuccessResponse(array_values($filteredProjects));
            break;

        case 'POST':
            // Handle project creation (for future use)
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                sendErrorResponse('Invalid JSON data', 400);
            }
            sendSuccessResponse(['message' => 'Project created successfully'], 201);
            break;

        default:
            sendErrorResponse('Method not allowed', 405);
            break;
    }
}

// Handle team requests
function handleTeamRequest($method, $teamMembers) {
    switch ($method) {
        case 'GET':
            // Get query parameters
            $role = isset($_GET['role']) ? $_GET['role'] : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;

            // Filter team members
            $filteredMembers = $teamMembers;
            if ($role) {
                $filteredMembers = array_filter($filteredMembers, function($member) use ($role) {
                    return stripos($member['role'], $role) !== false;
                });
            }

            // Sort by name
            usort($filteredMembers, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            // Limit results
            if ($limit) {
                $filteredMembers = array_slice($filteredMembers, 0, $limit);
            }

            sendSuccessResponse(array_values($filteredMembers));
            break;

        default:
            sendErrorResponse('Method not allowed', 405);
            break;
    }
}

// Handle statistics requests
function handleStatsRequest($projects, $teamMembers) {
    $stats = [
        'total_projects' => count($projects),
        'featured_projects' => count(array_filter($projects, function($p) { return $p['featured']; })),
        'total_team_members' => count($teamMembers),
        'technologies' => getUniqueTechnologies($projects),
        'recent_projects' => array_slice($projects, 0, 3),
        'team_roles' => getTeamRoles($teamMembers)
    ];

    sendSuccessResponse($stats);
}

// Helper functions
function getUniqueTechnologies($projects) {
    $allTechs = [];
    foreach ($projects as $project) {
        if (isset($project['technologies'])) {
            $allTechs = array_merge($allTechs, $project['technologies']);
        }
    }
    return array_unique($allTechs);
}

function getTeamRoles($teamMembers) {
    $roles = [];
    foreach ($teamMembers as $member) {
        $roles[] = $member['role'];
    }
    return array_unique($roles);
}

function sendSuccessResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

function sendErrorResponse($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'error' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}
?>
