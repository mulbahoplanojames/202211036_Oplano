<?php
require_once '../config/database.php';
require_once '../includes/auth_middleware.php';

requireAdmin();

$user = getCurrentUser();
$courses = getAllCourses();
$alert = getAlert();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Course Management System</title>
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
                <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                <p>Administrator Dashboard</p>
            </div>

            <?php if ($alert): ?>
                <div class="alert alert-<?php echo $alert['type']; ?>">
                    <?php echo $alert['message']; ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">System Overview</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $conn = getConnection();
                        
                        // Get statistics
                        $stmt = $conn->query("SELECT COUNT(*) as total_users FROM users");
                        $totalUsers = $stmt->fetch()['total_users'];
                        
                        $stmt = $conn->query("SELECT COUNT(*) as total_courses FROM courses");
                        $totalCourses = $stmt->fetch()['total_courses'];
                        
                        $stmt = $conn->query("SELECT COUNT(*) as total_enrollments FROM enrollments");
                        $totalEnrollments = $stmt->fetch()['total_enrollments'];
                        
                        $stmt = $conn->query("SELECT COUNT(*) as active_courses FROM courses WHERE status = 'active'");
                        $activeCourses = $stmt->fetch()['active_courses'];
                        ?>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div style="text-align: center; padding: 1rem; border: 2px solid #f8f9fa ; border-radius: 5px;">
                                <h4 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $totalUsers; ?></h4>
                                <p>Total Users</p>
                            </div>
                            <div style="text-align: center; padding: 1rem; border: 2px solid #f8f9fa ; border-radius: 5px;">
                                <h4 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $totalCourses; ?></h4>
                                <p>Total Courses</p>
                            </div>
                            <div style="text-align: center; padding: 1rem; border: 2px solid #f8f9fa ; border-radius: 5px;">
                                <h4 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $totalEnrollments; ?></h4>
                                <p>Total Enrollments</p>
                            </div>
                            <div style="text-align: center; padding: 1rem; border: 2px solid #f8f9fa ; border-radius: 5px;">
                                <h4 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $activeCourses; ?></h4>
                                <p>Active Courses</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Courses</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($courses)): ?>
                            <p>No courses found. <a href="courses.php?action=create">Create your first course</a>.</p>
                        <?php else: ?>
                            <div style="max-height: 400px; overflow-y: auto;">
                                <?php foreach (array_slice($courses, 0, 5) as $course): ?>
                                    <div style="padding: 1rem; border-bottom: 1px solid #eee;">
                                        <h4><?php echo htmlspecialchars($course['title']); ?></h4>
                                        <p style="color: #666; font-size: 0.9rem;">
                                            Instructor: <?php echo htmlspecialchars($course['instructor']); ?><br>
                                            Duration: <?php echo $course['duration_weeks']; ?> weeks<br>
                                            Enrolled: <?php echo $course['current_enrollments']; ?>/<?php echo $course['max_students']; ?>
                                        </p>
                                        <div style="margin-top: 0.5rem;">
                                            <span class="btn btn-sm" style="background: <?php echo $course['status'] == 'active' ? '#28a745' : '#ffc107'; ?>; color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                                <?php echo ucfirst($course['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div style="margin-top: 1rem; text-align: center;">
                                <a href="courses.php" class="btn btn-primary">View All Courses</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <a href="courses.php?action=create" class="btn btn-primary">Create New Course</a>
                            <a href="users.php" class="btn btn-secondary">Manage Users</a>
                            <a href="../courses/index.php" class="btn btn-warning">View Public Courses</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
