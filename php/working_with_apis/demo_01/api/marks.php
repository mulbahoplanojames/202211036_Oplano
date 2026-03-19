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
            getMark($db, $_GET['id']);
        } else {
            getMarks($db);
        }
        break;
    case 'POST':
        createMark($db);
        break;
    case 'PUT':
        if (isset($_GET['id'])) {
            updateMark($db, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Mark ID is required for update"));
        }
        break;
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteMark($db, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Mark ID is required for delete"));
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}

function getMarks($db) {
    $query = "SELECT m.id, m.mark, m.grade, m.semester, m.academic_year, m.created_at, m.updated_at,
                     u.name as student_name, u.email as student_email,
                     c.course_name, c.course_code
              FROM marks m
              JOIN users u ON m.user_id = u.id
              JOIN courses c ON m.course_id = c.id
              ORDER BY m.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $marks_arr = array();
    $marks_arr["records"] = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $mark_item = array(
            "id" => $row["id"],
            "mark" => $row["mark"],
            "grade" => $row["grade"],
            "semester" => $row["semester"],
            "academic_year" => $row["academic_year"],
            "student_name" => $row["student_name"],
            "student_email" => $row["student_email"],
            "course_name" => $row["course_name"],
            "course_code" => $row["course_code"],
            "created_at" => $row["created_at"],
            "updated_at" => $row["updated_at"]
        );
        array_push($marks_arr["records"], $mark_item);
    }
    
    http_response_code(200);
    echo json_encode($marks_arr);
}

function getMark($db, $id) {
    $query = "SELECT m.id, m.mark, m.grade, m.semester, m.academic_year, m.created_at, m.updated_at,
                     u.name as student_name, u.email as student_email,
                     c.course_name, c.course_code
              FROM marks m
              JOIN users u ON m.user_id = u.id
              JOIN courses c ON m.course_id = c.id
              WHERE m.id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $mark_item = array(
            "id" => $row["id"],
            "mark" => $row["mark"],
            "grade" => $row["grade"],
            "semester" => $row["semester"],
            "academic_year" => $row["academic_year"],
            "student_name" => $row["student_name"],
            "student_email" => $row["student_email"],
            "course_name" => $row["course_name"],
            "course_code" => $row["course_code"],
            "created_at" => $row["created_at"],
            "updated_at" => $row["updated_at"]
        );
        http_response_code(200);
        echo json_encode($mark_item);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Mark not found"));
    }
}

function createMark($db) {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->user_id) && !empty($data->course_id) && !empty($data->mark)) {
        $grade = calculateGrade($data->mark);
        
        $query = "INSERT INTO marks (user_id, course_id, mark, grade, semester, academic_year) 
                  VALUES (:user_id, :course_id, :mark, :grade, :semester, :academic_year)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":user_id", $data->user_id);
        $stmt->bindParam(":course_id", $data->course_id);
        $stmt->bindParam(":mark", $data->mark);
        $stmt->bindParam(":grade", $grade);
        $stmt->bindParam(":semester", htmlspecialchars(strip_tags($data->semester)));
        $stmt->bindParam(":academic_year", htmlspecialchars(strip_tags($data->academic_year)));
        
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("message" => "Mark created successfully.", "grade" => $grade));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create mark."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Unable to create mark. Data is incomplete."));
    }
}

function updateMark($db, $id) {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->user_id) && !empty($data->course_id) && !empty($data->mark)) {
        $grade = calculateGrade($data->mark);
        
        $query = "UPDATE marks SET 
                  user_id = :user_id, 
                  course_id = :course_id, 
                  mark = :mark, 
                  grade = :grade, 
                  semester = :semester, 
                  academic_year = :academic_year 
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":user_id", $data->user_id);
        $stmt->bindParam(":course_id", $data->course_id);
        $stmt->bindParam(":mark", $data->mark);
        $stmt->bindParam(":grade", $grade);
        $stmt->bindParam(":semester", htmlspecialchars(strip_tags($data->semester)));
        $stmt->bindParam(":academic_year", htmlspecialchars(strip_tags($data->academic_year)));
        $stmt->bindParam(":id", $id);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(array("message" => "Mark updated successfully.", "grade" => $grade));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Mark not found."));
            }
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to update mark."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Unable to update mark. Data is incomplete."));
    }
}

function deleteMark($db, $id) {
    $query = "DELETE FROM marks WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(array("message" => "Mark deleted successfully."));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Mark not found."));
        }
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to delete mark."));
    }
}

function calculateGrade($mark) {
    if ($mark >= 90) return 'A';
    elseif ($mark >= 85) return 'A-';
    elseif ($mark >= 80) return 'B+';
    elseif ($mark >= 75) return 'B';
    elseif ($mark >= 70) return 'B-';
    elseif ($mark >= 65) return 'C+';
    elseif ($mark >= 60) return 'C';
    elseif ($mark >= 55) return 'C-';
    elseif ($mark >= 50) return 'D';
    else return 'F';
}
?>
