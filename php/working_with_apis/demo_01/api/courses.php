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
            getCourse($db, $_GET['id']);
        } else {
            getCourses($db);
        }
        break;
    case 'POST':
        createCourse($db);
        break;
    case 'PUT':
        if (isset($_GET['id'])) {
            updateCourse($db, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Course ID is required for update"));
        }
        break;
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteCourse($db, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Course ID is required for delete"));
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}

function getCourses($db) {
    $query = "SELECT * FROM courses ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $courses_arr = array();
    $courses_arr["records"] = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $course_item = array(
            "id" => $id,
            "course_name" => $course_name,
            "course_code" => $course_code,
            "description" => $description,
            "credits" => $credits,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        );
        array_push($courses_arr["records"], $course_item);
    }
    
    http_response_code(200);
    echo json_encode($courses_arr);
}

function getCourse($db, $id) {
    $query = "SELECT * FROM courses WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        extract($row);
        $course_item = array(
            "id" => $id,
            "course_name" => $course_name,
            "course_code" => $course_code,
            "description" => $description,
            "credits" => $credits,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        );
        http_response_code(200);
        echo json_encode($course_item);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Course not found"));
    }
}

function createCourse($db) {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->course_name) && !empty($data->course_code)) {
        $query = "INSERT INTO courses (course_name, course_code, description, credits) VALUES (:course_name, :course_code, :description, :credits)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":course_name", htmlspecialchars(strip_tags($data->course_name)));
        $stmt->bindParam(":course_code", htmlspecialchars(strip_tags($data->course_code)));
        $stmt->bindParam(":description", htmlspecialchars(strip_tags($data->description)));
        $stmt->bindParam(":credits", $data->credits);
        
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("message" => "Course created successfully."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create course."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Unable to create course. Data is incomplete."));
    }
}

function updateCourse($db, $id) {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->course_name) && !empty($data->course_code)) {
        $query = "UPDATE courses SET course_name = :course_name, course_code = :course_code, description = :description, credits = :credits WHERE id = :id";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":course_name", htmlspecialchars(strip_tags($data->course_name)));
        $stmt->bindParam(":course_code", htmlspecialchars(strip_tags($data->course_code)));
        $stmt->bindParam(":description", htmlspecialchars(strip_tags($data->description)));
        $stmt->bindParam(":credits", $data->credits);
        $stmt->bindParam(":id", $id);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(array("message" => "Course updated successfully."));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Course not found."));
            }
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to update course."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Unable to update course. Data is incomplete."));
    }
}

function deleteCourse($db, $id) {
    $query = "DELETE FROM courses WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(array("message" => "Course deleted successfully."));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Course not found."));
        }
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to delete course."));
    }
}
?>
