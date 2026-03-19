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

// Handle unenrollment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        showAlert('Invalid request. Please try again.', 'danger');
        redirect('index.php');
    }

    if (unenrollFromCourse($user['id'], $courseId)) {
        showAlert('Successfully unenrolled from the course.', 'success');
    } else {
        showAlert('Failed to unenroll from the course.', 'danger');
    }
    redirect('index.php');
}

// Get course details for confirmation
$conn = getConnection();
$stmt = $conn->prepare("
    SELECT c.*, e.enrollment_date 
    FROM courses c 
    JOIN enrollments e ON c.id = e.course_id 
    WHERE c.id = ? AND e.user_id = ?
");
$stmt->execute([$courseId, $user['id']]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    showAlert('You are not enrolled in this course.', 'warning');
    redirect('index.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Unenrollment - Course Management System</title>
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
                <h1>Confirm Unenrollment</h1>
                <p>Are you sure you want to unenroll from this course?</p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo htmlspecialchars($enrollment['title']); ?></h3>
                </div>
                <div class="card-body">
                    <div class="course-card">
                        <div class="course-image">
                            <?php echo strtoupper(substr($enrollment['title'], 0, 2)); ?>
                        </div>
                        <div class="course-content">
                            <h3 class="course-title"><?php echo htmlspecialchars($enrollment['title']); ?></h3>
                            <p class="course-description"><?php echo htmlspecialchars($enrollment['description']); ?></p>
                            <div class="course-meta">
                                <span><strong>Instructor:</strong> <?php echo htmlspecialchars($enrollment['instructor']); ?></span>
                                <span><strong>Duration:</strong> <?php echo $enrollment['duration_weeks']; ?> weeks</span>
                            </div>
                            <div class="course-meta">
                                <span><strong>Enrolled:</strong> <?php echo formatDate($enrollment['enrollment_date']); ?></span>
                                <span><strong>Status:</strong> <span style="color: <?php echo $enrollment['status'] == 'active' ? '#28a745' : '#6c757d'; ?>;"><?php echo ucfirst($enrollment['status']); ?></span></span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="" style="margin-top: 2rem;">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="alert alert-danger">
                            <strong>Warning:</strong> Once you unenroll from this course, you may lose access to course materials and progress. This action cannot be undone easily.
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-danger">Confirm Unenrollment</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
