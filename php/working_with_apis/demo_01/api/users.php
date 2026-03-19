<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getUser($db, $_GET['id']);
        } else {
            getUsers($db);
        }
        break;
    case 'POST':
        createUser($db);
        break;
    case 'PUT':
        if (isset($_GET['id'])) {
            updateUser($db, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "User ID is required for update"));
        }
        break;
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteUser($db, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "User ID is required for delete"));
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}

function getUsers($db) {
    $query = "SELECT * FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $users_arr = array();
    $users_arr["records"] = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $user_item = array(
            "id" => $id,
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        );
        array_push($users_arr["records"], $user_item);
    }
    
    http_response_code(200);
    echo json_encode($users_arr);
}

function getUser($db, $id) {
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        extract($row);
        $user_item = array(
            "id" => $id,
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        );
        http_response_code(200);
        echo json_encode($user_item);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "User not found"));
    }
}

function createUser($db) {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->name) && !empty($data->email)) {
        $query = "INSERT INTO users (name, email, phone) VALUES (:name, :email, :phone)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":name", htmlspecialchars(strip_tags($data->name)));
        $stmt->bindParam(":email", htmlspecialchars(strip_tags($data->email)));
        $stmt->bindParam(":phone", htmlspecialchars(strip_tags($data->phone)));
        
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("message" => "User created successfully."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create user."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
    }
}

function updateUser($db, $id) {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->name) && !empty($data->email)) {
        $query = "UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :id";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":name", htmlspecialchars(strip_tags($data->name)));
        $stmt->bindParam(":email", htmlspecialchars(strip_tags($data->email)));
        $stmt->bindParam(":phone", htmlspecialchars(strip_tags($data->phone)));
        $stmt->bindParam(":id", $id);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(array("message" => "User updated successfully."));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "User not found."));
            }
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to update user."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Unable to update user. Data is incomplete."));
    }
}

function deleteUser($db, $id) {
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(array("message" => "User deleted successfully."));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "User not found."));
        }
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to delete user."));
    }
}
?>
