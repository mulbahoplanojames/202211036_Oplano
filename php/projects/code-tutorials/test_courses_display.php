<?php
require_once 'includes/functions.php';

// Get courses
$query = "SELECT c.*, COUNT(v.id) as video_count 
          FROM courses c 
          LEFT JOIN videos v ON c.id = v.course_id AND v.is_active = 1 
          WHERE c.is_active = 1 
          GROUP BY c.id ORDER BY c.title";

$stmt = $db->prepare($query);
$stmt->execute();
$courses = $stmt->fetchAll(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Courses Display</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { padding: 20px; font-family: Arial, sans-serif; }
        .debug-info { 
            background: #f0f0f0; 
            padding: 10px; 
            margin: 10px 0; 
            border-radius: 5px; 
        }
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .test-card {
            border: 2px solid #007bff;
            padding: 15px;
            border-radius: 8px;
            background: white;
        }
    </style>
</head>
<body>
    <h1>Test Courses Display</h1>
    
    <div class="debug-info">
        <strong>Debug Info:</strong><br>
        Found <?php echo count($courses); ?> courses<br>
        count($courses) > 0: <?php echo count($courses) > 0 ? 'TRUE' : 'FALSE'; ?><br>
        PHP Version: <?php echo phpversion(); ?><br>
        Memory usage: <?php echo memory_get_usage(true); ?>
    </div>

    <?php if (count($courses) > 0): ?>
        <h2>Using Custom Grid (Should Always Work):</h2>
        <div class="test-grid">
            <?php foreach ($courses as $course): ?>
                <div class="test-card">
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p><strong>Language:</strong> <?php echo htmlspecialchars($course['programming_language']); ?></p>
                    <p><strong>Difficulty:</strong> <?php echo htmlspecialchars($course['difficulty_level']); ?></p>
                    <p><strong>Videos:</strong> <?php echo (int)$course['video_count']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <h2>Using Original courses-grid CSS:</h2>
        <div class="courses-grid">
            <?php foreach ($courses as $course): ?>
                <div class="course-card">
                    <div class="course-thumbnail">
                        <?php if (!empty($course['thumbnail_url'])): ?>
                            <img src="<?php echo htmlspecialchars($course['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <?php else: ?>
                            <span><?php echo strtoupper(substr($course['programming_language'], 0, 2)); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="course-content">
                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                        <div class="course-meta">
                            <span class="difficulty-badge difficulty-<?php echo $course['difficulty_level']; ?>">
                                <?php echo $course['difficulty_level']; ?>
                            </span>
                            <span><?php echo $course['video_count']; ?> videos</span>
                            <span><?php echo $course['programming_language']; ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="color: red; font-size: 18px;">
            NO COURSES FOUND - This should not happen!
        </div>
    <?php endif; ?>

    <div class="debug-info">
        <strong>HTML Structure Check:</strong><br>
        Check browser developer tools to see if elements are rendered but hidden by CSS.
    </div>
</body>
</html>
