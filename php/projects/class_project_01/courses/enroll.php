<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAuth();

$user = getCurrentUser();
$courseId = $_GET['course_id'] ?? null;

if (!$courseId) {
    showAlert('Invalid course selection.', 'danger');
    redirect('index.php');
}

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        showAlert('Invalid request. Please try again.', 'danger');
        redirect('index.php');
    }

    if (enrollInCourse($user['id'], $courseId)) {
        showAlert('Successfully enrolled in the course!', 'success');
    } else {
        showAlert('Failed to enroll. The course might be full or you may already be enrolled.', 'danger');
    }
    redirect('index.php');
}

// Get course details for confirmation
$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ? AND status IN ('upcoming', 'active')");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    showAlert('Course not found or not available for enrollment.', 'danger');
    redirect('index.php');
}

// Check if already enrolled
if (isEnrolled($user['id'], $courseId)) {
    showAlert('You are already enrolled in this course.', 'warning');
    redirect('index.php');
}

// Check if course is full
if ($course['current_enrollments'] >= $course['max_students']) {
    showAlert('This course is already full.', 'danger');
    redirect('index.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Enrollment - Course Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">Course Management System</div>
            <nav>
                <ul class="nav-links">
                    <li><a href="../user/dashboard.php">Dashboard</a></li>
                    <li><a href="index.php">Browse Courses</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>Confirm Enrollment</h1>
                <p>Review course details before enrolling</p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                </div>
                <div class="card-body">
                    <div class="course-card">
                        <div class="course-image">
                            <?php echo strtoupper(substr($course['title'], 0, 2)); ?>
                        </div>
                        <div class="course-content">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                            <div class="course-meta">
                                <span><strong>Instructor:</strong> <?php echo htmlspecialchars($course['instructor']); ?></span>
                                <span><strong>Duration:</strong> <?php echo $course['duration_weeks']; ?> weeks</span>
                            </div>
                            <div class="course-meta">
                                <span><strong>Start Date:</strong> <?php echo formatDate($course['start_date']); ?></span>
                                <span><strong>End Date:</strong> <?php echo formatDate($course['end_date']); ?></span>
                            </div>
                            <div class="course-meta">
                                <span><strong>Available Spots:</strong> <?php echo ($course['max_students'] - $course['current_enrollments']); ?> / <?php echo $course['max_students']; ?></span>
                                <span><strong>Status:</strong> <span style="color: <?php echo $course['status'] == 'active' ? '#28a745' : '#ffc107'; ?>;"><?php echo ucfirst($course['status']); ?></span></span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="" style="margin-top: 2rem;">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="alert alert-warning">
                            <strong>Important:</strong> By enrolling in this course, you commit to attending all classes and completing required coursework.
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">Confirm Enrollment</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
