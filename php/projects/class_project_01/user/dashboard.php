<?php
require_once '../config/database.php';
require_once '../includes/auth_middleware.php';

requireAuth();
requireUser();

$user = getCurrentUser();
$enrollments = getUserEnrollments($user['id']);
$alert = getAlert();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Course Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">Course Management System</div>
            <nav>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="../courses/index.php">Browse Courses</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                <p>Student Dashboard</p>
            </div>

            <?php if ($alert): ?>
                <div class="alert alert-<?php echo $alert['type']; ?>">
                    <?php echo $alert['message']; ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">My Enrollments</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($enrollments)): ?>
                            <p>You haven't enrolled in any courses yet. <a href="../courses/index.php">Browse available courses</a>.</p>
                        <?php else: ?>
                            <?php foreach ($enrollments as $enrollment): ?>
                                <div class="course-card" style="margin-bottom: 1.5rem;">
                                    <div class="course-image">
                                        <?php echo strtoupper(substr($enrollment['title'], 0, 2)); ?>
                                    </div>
                                    <div class="course-content">
                                        <h3 class="course-title"><?php echo htmlspecialchars($enrollment['title']); ?></h3>
                                        <p class="course-description"><?php echo htmlspecialchars($enrollment['description']); ?></p>
                                        <div class="course-meta">
                                            <span>Instructor: <?php echo htmlspecialchars($enrollment['instructor']); ?></span>
                                            <span>Duration: <?php echo $enrollment['duration_weeks']; ?> weeks</span>
                                        </div>
                                        <div class="course-meta">
                                            <span>Enrolled: <?php echo formatDate($enrollment['enrollment_date']); ?></span>
                                            <span>
                                                Status: 
                                                <span style="color: <?php echo $enrollment['enrollment_status'] == 'active' ? '#28a745' : '#6c757d'; ?>;">
                                                    <?php echo ucfirst($enrollment['enrollment_status']); ?>
                                                </span>
                                            </span>
                                        </div>
                                        <?php if ($enrollment['grade']): ?>
                                            <div class="course-meta">
                                                <span>Grade: <strong><?php echo $enrollment['grade']; ?></strong></span>
                                            </div>
                                        <?php endif; ?>
                                        <div style="margin-top: 1rem;">
                                            <a href="#" class="btn btn-primary">View Course Details</a>
                                            <?php if ($enrollment['enrollment_status'] == 'active'): ?>
                                                <a href="../courses/unenroll.php?course_id=<?php echo $enrollment['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to unenroll from this course?')">Unenroll</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Stats</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $conn = getConnection();
                        
                        // Get user statistics
                        $stmt = $conn->prepare("SELECT COUNT(*) as active_courses FROM enrollments WHERE user_id = ? AND status = 'active'");
                        $stmt->execute([$user['id']]);
                        $activeCourses = $stmt->fetch()['active_courses'];
                        
                        $stmt = $conn->prepare("SELECT COUNT(*) as completed_courses FROM enrollments WHERE user_id = ? AND status = 'completed'");
                        $stmt->execute([$user['id']]);
                        $completedCourses = $stmt->fetch()['completed_courses'];
                        
                        $stmt = $conn->prepare("SELECT AVG(grade) as average_grade FROM enrollments WHERE user_id = ? AND grade IS NOT NULL");
                        $stmt->execute([$user['id']]);
                        $avgGrade = $stmt->fetch()['average_grade'];
                        ?>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div style="text-align: center; padding: 1rem;  border:2px solid #f8f9fa; border-radius: 5px;">
                                <h4 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $activeCourses; ?></h4>
                                <p>Active Courses</p>
                            </div>
                            <div style="text-align: center; padding: 1rem; border:2px solid #f8f9fa; border-radius: 5px;">
                                <h4 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $completedCourses; ?></h4>
                                <p>Completed Courses</p>
                            </div>
                            <?php if ($avgGrade): ?>
                            <div style="text-align: center; padding: 1rem; border:2px solid #f8f9fa; border-radius: 5px; grid-column: 1 / -1;">
                                <h4 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo number_format($avgGrade, 2); ?></h4>
                                <p>Average Grade</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Browse More Courses</h3>
                    </div>
                    <div class="card-body">
                        <p>Discover new courses and expand your knowledge!</p>
                        <a href="../courses/index.php" class="btn btn-primary">Browse All Courses</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
