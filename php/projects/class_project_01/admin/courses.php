<?php
require_once '../config/database.php';
require_once '../includes/auth_middleware.php';

requireAdmin();

$user = getCurrentUser();
$alert = getAlert();
$action = $_GET['action'] ?? 'list';
$courseId = $_GET['id'] ?? null;
$course = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        showAlert('Invalid request. Please try again.', 'danger');
        redirect('courses.php');
    }

    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $instructor = sanitize($_POST['instructor']);
    $durationWeeks = (int)$_POST['duration_weeks'];
    $maxStudents = (int)$_POST['max_students'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    if ($_POST['action'] == 'create') {
        if (createCourse($title, $description, $instructor, $durationWeeks, $maxStudents, $startDate, $endDate, $user['id'])) {
            showAlert('Course created successfully!', 'success');
            redirect('courses.php');
        } else {
            showAlert('Failed to create course.', 'danger');
        }
    } elseif ($_POST['action'] == 'update') {
        $courseId = $_POST['course_id'];
        if (updateCourse($courseId, $title, $description, $instructor, $durationWeeks, $maxStudents, $startDate, $endDate)) {
            showAlert('Course updated successfully!', 'success');
            redirect('courses.php');
        } else {
            showAlert('Failed to update course.', 'danger');
        }
    }
}

// Handle delete action
if ($action == 'delete' && $courseId) {
    if (deleteCourse($courseId)) {
        showAlert('Course deleted successfully!', 'success');
    } else {
        showAlert('Failed to delete course.', 'danger');
    }
    redirect('courses.php');
}

// Get course data for edit
if ($action == 'edit' && $courseId) {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
    
    if (!$course) {
        showAlert('Course not found.', 'danger');
        redirect('courses.php');
    }
}

$courses = getAllCourses();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">Course Management System</div>
            <nav>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="courses.php">Manage Courses</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>Manage Courses</h1>
                <p>Create, edit, and delete courses</p>
            </div>

            <?php if ($alert): ?>
                <div class="alert alert-<?php echo $alert['type']; ?>">
                    <?php echo $alert['message']; ?>
                </div>
            <?php endif; ?>

            <?php if ($action == 'create' || $action == 'edit'): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo $action == 'create' ? 'Create New Course' : 'Edit Course'; ?></h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <?php if ($action == 'edit'): ?>
                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="title" class="form-label">Course Title</label>
                                <input type="text" id="title" name="title" class="form-control" 
                                       value="<?php echo $course ? htmlspecialchars($course['title']) : (isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="4" required><?php echo $course ? htmlspecialchars($course['description']) : (isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="instructor" class="form-label">Instructor</label>
                                <input type="text" id="instructor" name="instructor" class="form-control" 
                                       value="<?php echo $course ? htmlspecialchars($course['instructor']) : (isset($_POST['instructor']) ? htmlspecialchars($_POST['instructor']) : ''); ?>" required>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label for="duration_weeks" class="form-label">Duration (Weeks)</label>
                                    <input type="number" id="duration_weeks" name="duration_weeks" class="form-control" 
                                           value="<?php echo $course ? $course['duration_weeks'] : (isset($_POST['duration_weeks']) ? $_POST['duration_weeks'] : 8); ?>" min="1" required>
                                </div>

                                <div class="form-group">
                                    <label for="max_students" class="form-label">Max Students</label>
                                    <input type="number" id="max_students" name="max_students" class="form-control" 
                                           value="<?php echo $course ? $course['max_students'] : (isset($_POST['max_students']) ? $_POST['max_students'] : 50); ?>" min="1" required>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" id="start_date" name="start_date" class="form-control" 
                                           value="<?php echo $course ? $course['start_date'] : (isset($_POST['start_date']) ? $_POST['start_date'] : ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" id="end_date" name="end_date" class="form-control" 
                                           value="<?php echo $course ? $course['end_date'] : (isset($_POST['end_date']) ? $_POST['end_date'] : ''); ?>" required>
                                </div>
                            </div>

                            <div style="display: flex; gap: 1rem;">
                                <button type="submit" class="btn btn-primary"><?php echo $action == 'create' ? 'Create Course' : 'Update Course'; ?></button>
                                <a href="courses.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">All Courses</h3>
                        <a href="courses.php?action=create" class="btn btn-primary">Create New Course</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($courses)): ?>
                            <p>No courses found. <a href="courses.php?action=create">Create your first course</a>.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Instructor</th>
                                            <th>Duration</th>
                                            <th>Enrolled</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($courses as $course): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($course['title']); ?></strong><br>
                                                    <small style="color: #666;"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($course['instructor']); ?></td>
                                                <td><?php echo $course['duration_weeks']; ?> weeks</td>
                                                <td><?php echo $course['current_enrollments']; ?>/<?php echo $course['max_students']; ?></td>
                                                <td>
                                                    <span class="btn btn-sm" style="background: <?php echo $course['status'] == 'active' ? '#28a745' : ($course['status'] == 'upcoming' ? '#ffc107' : '#6c757d'); ?>; color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                                        <?php echo ucfirst($course['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div style="display: flex; gap: 0.5rem;">
                                                        <a href="courses.php?action=edit&id=<?php echo $course['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                        <a href="courses.php?action=delete&id=<?php echo $course['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this course?')">Delete</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
