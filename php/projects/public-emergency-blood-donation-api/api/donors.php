<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        handleGet($db);
        break;
    case 'POST':
        handlePost($db);
        break;
    case 'DELETE':
        handleDelete($db);
        break;
    case 'OPTIONS':
        http_response_code(200);
        break;
    default:
        http_response_code(405);
        echo json_encode(array("status" => "error", "message" => "Method not allowed"));
        break;
}

function handleGet($db) {
    $blood_type = isset($_GET['blood_type']) ? $_GET['blood_type'] : null;
    $city = isset($_GET['city']) ? $_GET['city'] : null;
    
    $query = "SELECT id, name, blood_type, city, phone, last_donation_date FROM donors WHERE 1=1";
    $params = array();
    
    if ($blood_type) {
        $query .= " AND blood_type = :blood_type";
        $params[':blood_type'] = $blood_type;
    }
    
    if ($city) {
        $query .= " AND city = :city";
        $params[':city'] = $city;
    }
    
    $query .= " ORDER BY name";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        $donors = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $donors[] = $row;
        }
        
        http_response_code(200);
        echo json_encode(array("status" => "success", "data" => $donors));
        
    } catch(PDOException $exception) {
        http_response_code(500);
        echo json_encode(array("status" => "error", "message" => "Database error: " . $exception->getMessage()));
    }
}

function handlePost($db) {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->name) || !isset($data->blood_type) || !isset($data->city) || !isset($data->phone)) {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Missing required fields"));
        return;
    }
    
    $query = "INSERT INTO donors (name, blood_type, city, phone, last_donation_date) VALUES (:name, :blood_type, :city, :phone, :last_donation_date)";
    
    try {
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':name', $data->name);
        $stmt->bindParam(':blood_type', $data->blood_type);
        $stmt->bindParam(':city', $data->city);
        $stmt->bindParam(':phone', $data->phone);
        
        $last_donation_date = isset($data->last_donation_date) ? $data->last_donation_date : null;
        $stmt->bindParam(':last_donation_date', $last_donation_date);
        
        if ($stmt->execute()) {
            $id = $db->lastInsertId();
            http_response_code(201);
            echo json_encode(array(
                "status" => "success", 
                "message" => "Donor registered successfully",
                "data" => array("id" => $id)
            ));
        } else {
            http_response_code(500);
            echo json_encode(array("status" => "error", "message" => "Failed to register donor"));
        }
        
    } catch(PDOException $exception) {
        http_response_code(500);
        echo json_encode(array("status" => "error", "message" => "Database error: " . $exception->getMessage()));
    }
}

function handleDelete($db) {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Missing donor ID"));
        return;
    }
    
    $query = "DELETE FROM donors WHERE id = :id";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(array("status" => "success", "message" => "Donor deleted successfully"));
            } else {
                http_response_code(404);
                echo json_encode(array("status" => "error", "message" => "Donor not found"));
            }
        } else {
            http_response_code(500);
            echo json_encode(array("status" => "error", "message" => "Failed to delete donor"));
        }
        
    } catch(PDOException $exception) {
        http_response_code(500);
        echo json_encode(array("status" => "error", "message" => "Database error: " . $exception->getMessage()));
    }
}
?>
