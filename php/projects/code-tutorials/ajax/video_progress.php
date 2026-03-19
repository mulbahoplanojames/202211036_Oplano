<?php
/**
 * AJAX endpoint for video progress tracking
 */

require_once '../includes/functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit();
}

// Validate required fields
if (!isset($input['video_id']) || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

$video_id = (int)$input['video_id'];
$user_id = $_SESSION['user_id'];
$action = $input['action'];

try {
    switch ($action) {
        case 'update_progress':
            // Update video progress
            $watched_duration = isset($input['watched_duration']) ? (int)$input['watched_duration'] : 0;
            $completed = isset($input['completed']) ? (bool)$input['completed'] : false;
            
            if (updateVideoProgress($db, $user_id, $video_id, $watched_duration, $completed)) {
                // Get course_id to update enrollment progress
                $video_query = "SELECT course_id FROM videos WHERE id = :video_id";
                $stmt = $db->prepare($video_query);
                $stmt->bindParam(':video_id', $video_id);
                $stmt->execute();
                $video = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($video) {
                    updateEnrollmentProgress($db, $user_id, $video['course_id']);
                }
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Failed to update progress']);
            }
            break;
            
        case 'get_progress':
            // Get video progress
            $progress = getVideoProgress($db, $user_id, $video_id);
            echo json_encode(['success' => true, 'progress' => $progress]);
            break;
            
        case 'mark_complete':
            // Mark video as completed
            if (updateVideoProgress($db, $user_id, $video_id, 0, true)) {
                // Get course_id to update enrollment progress
                $video_query = "SELECT course_id FROM videos WHERE id = :video_id";
                $stmt = $db->prepare($video_query);
                $stmt->bindParam(':video_id', $video_id);
                $stmt->execute();
                $video = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($video) {
                    updateEnrollmentProgress($db, $user_id, $video['course_id']);
                }
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Failed to mark as complete']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
