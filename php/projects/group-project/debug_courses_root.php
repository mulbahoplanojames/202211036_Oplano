<?php
require_once 'includes/functions.php';

echo "<h2>Debug Root courses.php</h2>";

// Get filter parameters
$language_filter = isset($_GET['language']) ? sanitize($_GET['language']) : '';
$difficulty_filter = isset($_GET['difficulty']) ? sanitize($_GET['difficulty']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$query = "SELECT c.*, COUNT(v.id) as video_count 
          FROM courses c 
          LEFT JOIN videos v ON c.id = v.course_id AND v.is_active = 1 
          WHERE c.is_active = 1";

$params = [];

// Add search filter
if (!empty($search)) {
    $query .= " AND (c.title LIKE :search OR c.description LIKE :search OR c.programming_language LIKE :search)";
    $params[':search'] = "%$search%";
}

// Add language filter
if (!empty($language_filter)) {
    $query .= " AND c.programming_language = :language";
    $params[':language'] = $language_filter;
}

// Add difficulty filter
if (!empty($difficulty_filter)) {
    $query .= " AND c.difficulty_level = :difficulty";
    $params[':difficulty'] = $difficulty_filter;
}

$query .= " GROUP BY c.id ORDER BY c.title";

echo "<p><strong>Query:</strong><br><code>" . htmlspecialchars($query) . "</code></p>";
echo "<p><strong>Params:</strong><br><pre>" . print_r($params, true) . "</pre></p>";

try {
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $courses = $stmt->fetchAll(MYSQLI_ASSOC);
    
    echo "<p><strong>Query executed. Found " . count($courses) . " courses</strong></p>";
    
    if (!empty($courses)) {
        echo "<h4>First 3 courses:</h4>";
        for ($i = 0; $i < min(3, count($courses)); $i++) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px;'>";
            echo "<strong>Course " . ($i + 1) . ":</strong><br>";
            echo "ID: " . $courses[$i]['id'] . "<br>";
            echo "Title: " . htmlspecialchars($courses[$i]['title']) . "<br>";
            echo "Language: " . htmlspecialchars($courses[$i]['programming_language']) . "<br>";
            echo "Difficulty: " . htmlspecialchars($courses[$i]['difficulty_level']) . "<br>";
            echo "Video Count: " . ($courses[$i]['video_count'] ?? 0) . "<br>";
            echo "Active: " . ($courses[$i]['is_active'] ? 'Yes' : 'No') . "<br>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>No courses found!</p>";
        
        // Test simple query
        echo "<h4>Testing simple query:</h4>";
        $simple_query = "SELECT * FROM courses WHERE is_active = 1";
        $simple_stmt = $db->prepare($simple_query);
        $simple_stmt->execute();
        $simple_courses = $simple_stmt->fetchAll(MYSQLI_ASSOC);
        echo "<p>Simple query found: " . count($simple_courses) . " courses</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h3>Display Logic Test:</h3>";
echo "count(\$courses) = " . count($courses) . "<br>";
echo "count(\$courses) > 0: " . (count($courses) > 0 ? 'TRUE' : 'FALSE') . "<br>";

if (count($courses) > 0) {
    echo "<p style='color: green;'>✓ Courses should display in the grid!</p>";
} else {
    echo "<p style='color: red;'>✗ 'No courses found' message will show</p>";
}
?>
