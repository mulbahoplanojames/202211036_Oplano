<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/db.php';

$database = new Database();
$db = $database->getMysqliConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    handleGet($db);
} elseif ($method == 'OPTIONS') {
    http_response_code(200);
} else {
    http_response_code(405);
    echo json_encode(array("status" => "error", "message" => "Method not allowed"));
}

function handleGet($db) {
    // Emergency donors are those who haven't donated in the last 56 days (8 weeks)
    // This follows medical guidelines for blood donation eligibility
    $query = "SELECT id, name, blood_type, city, phone, last_donation_date 
              FROM donors 
              WHERE (last_donation_date IS NULL OR last_donation_date < DATE_SUB(CURDATE(), INTERVAL 56 DAY))
              ORDER BY name";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $donors = array();
        while ($row = $result->fetch_assoc()) {
            $donors[] = $row;
        }
        
        http_response_code(200);
        echo json_encode(array("status" => "success", "data" => $donors));
        
    } catch(Exception $exception) {
        http_response_code(500);
        echo json_encode(array("status" => "error", "message" => "Database error: " . $exception->getMessage()));
    }
}
?>
