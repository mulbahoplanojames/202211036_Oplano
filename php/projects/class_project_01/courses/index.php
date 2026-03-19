<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$user = getCurrentUser();
$courses = getAvailableCourses($user ? $user['id'] : null);
$alert = getAlert();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Courses - Course Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">Course Management System</div>
            <nav>
                <ul class="nav-links">
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="index.php" class="active">Courses</a></li>
                    <?php if ($user): ?>
                        <?php if ($user['role'] === 'admin'): ?>
                            <li><a href="../admin/dashboard.php">Dashboard</a></li>
                            <li><a href="../admin/courses.php">Manage Courses</a></li>
                        <?php else: ?>
                            <li><a href="../user/dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="../auth/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="../auth/login.php">Login</a></li>
                        <li><a href="../auth/register.php">Create Account</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>Welcome to Course Management System</h1>
                <p>Browse and enroll in our available courses</p>
            </div>

            <?php if ($alert): ?>
                <div class="alert alert-<?php echo $alert['type']; ?>">
                    <?php echo $alert['message']; ?>
                </div>
            <?php endif; ?>

            <?php if (!$user): ?>
            <div class="card" style="border:2px solid white; color: white; margin-bottom: 2rem;">
                <div class="card-body text-center" style="padding: 2rem;">
                    <h2 style="margin-bottom: 1rem;">Start Your Learning Journey Today!</h2>
                    <p style="margin-bottom: 1.5rem; font-size: 1.1rem;">Join our platform and access quality courses to enhance your skills.</p>
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <a href="../auth/register.php" class="btn" style="background: white; color: #667eea;">Sign Up Now</a>
                        <a href="../auth/login.php" class="btn" style="background: transparent; border: 2px solid white;">Login</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($courses)): ?>
                <div class="card">
                    <div class="card-body text-center">
                        <h3>No courses available at the moment</h3>
                        <p>Check back later for new course offerings!</p>
                    </div>
                </div>
            <?php else: ?>
                <div style="margin-bottom: 2rem;">
                    <h2>Available Courses</h2>
                    <p>Choose from our selection of courses and start learning today</p>
                </div>
                <div class="dashboard-content">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <div class="course-image">
                                <?php echo strtoupper(substr($course['title'], 0, 2)); ?>
                            </div>
                            <div class="course-content">
                                <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                                <div class="course-meta">
                                    <span>Instructor: <?php echo htmlspecialchars($course['instructor']); ?></span>
                                    <span>Duration: <?php echo $course['duration_weeks']; ?> weeks</span>
                                </div>
                                <div class="course-meta">
                                    <span>Start: <?php echo formatDate($course['start_date']); ?></span>
                                    <span>End: <?php echo formatDate($course['end_date']); ?></span>
                                </div>
                                <div class="course-meta">
                                    <span>
                                        Available Spots: 
                                        <strong style="color: <?php echo $course['available_spots'] > 0 ? '#28a745' : '#dc3545'; ?>;">
                                            <?php echo $course['available_spots']; ?>
                                        </strong>
                                    </span>
                                    <span>
                                        Status: 
                                        <span style="color: <?php echo $course['status'] == 'active' ? '#28a745' : '#ffc107'; ?>;">
                                            <?php echo ucfirst($course['status']); ?>
                                        </span>
                                    </span>
                                </div>
                                <div style="margin-top: 1rem;">
                                    <?php if ($user): ?>
                                        <?php if (isset($course['is_enrolled']) && $course['is_enrolled']): ?>
                                            <span class="btn btn-success" style="cursor: default;">Enrolled</span>
                                            <a href="unenroll.php?course_id=<?php echo $course['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to unenroll from this course?')">Unenroll</a>
                                        <?php elseif ($course['available_spots'] > 0): ?>
                                            <a href="enroll.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary">Enroll Now</a>
                                        <?php else: ?>
                                            <span class="btn btn-secondary" style="cursor: default;">Class Full</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="../auth/login.php" class="btn btn-primary">Login to Enroll</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
