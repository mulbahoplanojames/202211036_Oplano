<?php
/**
 * Admin Dashboard - Main admin interface
 */

require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get dashboard statistics
$total_courses_query = "SELECT COUNT(*) as total FROM courses";
$total_courses_stmt = $db->prepare($total_courses_query);
$total_courses_stmt->execute();
$total_courses = $total_courses_stmt->fetch_assoc()['total'];

$total_videos_query = "SELECT COUNT(*) as total FROM videos";
$total_videos_stmt = $db->prepare($total_videos_query);
$total_videos_stmt->execute();
$total_videos = $total_videos_stmt->fetch_assoc()['total'];

$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_users_stmt = $db->prepare($total_users_query);
$total_users_stmt->execute();
$total_users = $total_users_stmt->fetch_assoc()['total'];

$total_enrollments_query = "SELECT COUNT(*) as total FROM enrollments";
$total_enrollments_stmt = $db->prepare($total_enrollments_query);
$total_enrollments_stmt->execute();
$total_enrollments = $total_enrollments_stmt->fetch_assoc()['total'];

// Get recent enrollments
$recent_enrollments_query = "SELECT e.*, u.username, c.title as course_title 
                            FROM enrollments e 
                            JOIN users u ON e.user_id = u.id 
                            JOIN courses c ON e.course_id = c.id 
                            ORDER BY e.enrolled_at DESC 
                            LIMIT 5";

// Debug: Check database connection first
error_log("Database connection status: " . ($db ? "Connected" : "Not connected"));
error_log("Recent enrollments query: " . $recent_enrollments_query);

try {
    $recent_enrollments_stmt = $db->prepare($recent_enrollments_query);
    error_log("Statement prepared successfully");
    
    $result = $recent_enrollments_stmt->execute();
    error_log("Execute result: " . ($result ? "Success" : "Failed"));
    
    // Use the correct fetch method for MySQLi wrapper
    $recent_enrollments = $recent_enrollments_stmt->fetchAll(MYSQLI_ASSOC);
    error_log("Recent enrollments count: " . count($recent_enrollments));
    
    if (count($recent_enrollments) > 0) {
        error_log("First enrollment data: " . print_r($recent_enrollments[0], true));
    } else {
        error_log("No enrollments found - checking table existence");
        
        // Check if enrollments table exists and has data
        $check_query = "SELECT COUNT(*) as count FROM enrollments";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute();
        $check_result = $check_stmt->fetch_assoc();
        error_log("Total enrollments in database: " . $check_result['count']);
        
        // Check users table
        $users_query = "SELECT COUNT(*) as count FROM users";
        $users_stmt = $db->prepare($users_query);
        $users_stmt->execute();
        $users_result = $users_stmt->fetch_assoc();
        error_log("Total users in database: " . $users_result['count']);
        
        // Check courses table
        $courses_query = "SELECT COUNT(*) as count FROM courses";
        $courses_stmt = $db->prepare($courses_query);
        $courses_stmt->execute();
        $courses_result = $courses_stmt->fetch_assoc();
        error_log("Total courses in database: " . $courses_result['count']);
    }
} catch (Exception $e) {
    error_log("Database error in recent enrollments: " . $e->getMessage());
    $recent_enrollments = [];
}

// Get popular courses
$popular_courses_query = "SELECT c.*, COUNT(e.id) as enrollment_count 
                         FROM courses c 
                         LEFT JOIN enrollments e ON c.id = e.course_id 
                         GROUP BY c.id 
                         ORDER BY enrollment_count DESC 
                         LIMIT 5";
$popular_courses_stmt = $db->prepare($popular_courses_query);
$popular_courses_stmt->execute();
$popular_courses = $popular_courses_stmt->fetchAll(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CodeTutorials</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/mobile-menu.js" defer></script>
    <style>
        .admin-nav {
            background: var(--primary-dark);
            padding: 1rem 0;
        }
        .admin-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        .admin-nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .admin-nav-links a:hover,
        .admin-nav-links a.active {
            background: var(--primary-medium);
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .dashboard-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .dashboard-number {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-accent);
            margin-bottom: 0.5rem;
        }
        .dashboard-label {
            color: #666;
            font-size: 1.1rem;
        }
        .dashboard-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .section-title {
            font-size: 1.5rem;
            color: #333;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .action-card {
            background: linear-gradient(135deg, var(--primary-accent) 0%, var(--primary-medium) 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        .action-card:hover {
            transform: translateY(-5px);
            color: white;
        }
        .action-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .action-title {
            font-size: 1.1rem;
            font-weight: bold;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        .admin-table th,
        .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .admin-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .admin-table tr:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="container">
            <div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <a href="../index.php" class="logo" style="color: white;">📚 CodeTutorials</a>
                    <span style="color: #bdc3c7;">|</span>
                    <span style="color: white;">Admin Panel</span>
                </div>
                <button class="mobile-menu-toggle" aria-expanded="false" aria-label="Toggle navigation menu">☰</button>
            </div>
            <ul class="admin-nav-links">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="courses.php">Courses</a></li>
                <li><a href="videos.php">Videos</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Dashboard Content -->
    <section class="dashboard">
        <div class="container">
            <!-- Welcome Header -->
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 style="font-size: 2.5rem; color: #333; margin-bottom: 0.5rem;">
                    Welcome, <?= htmlspecialchars($_SESSION['full_name']); ?>
                </h1>
                <p style="color: #666;">Manage your programming tutorials platform</p>
            </div>

            <!-- Statistics Cards -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="dashboard-number"><?= $total_courses; ?></div>
                    <div class="dashboard-label">Total Courses</div>
                </div>
                <div class="dashboard-card">
                    <div class="dashboard-number"><?= $total_videos; ?></div>
                    <div class="dashboard-label">Total Videos</div>
                </div>
                <div class="dashboard-card">
                    <div class="dashboard-number"><?= $total_users; ?></div>
                    <div class="dashboard-label">Total Users</div>
                </div>
                <div class="dashboard-card">
                    <div class="dashboard-number"><?= $total_enrollments; ?></div>
                    <div class="dashboard-label">Total Enrollments</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-section">
                <h2 class="section-title">Quick Actions</h2>
                <div class="quick-actions">
                    <a href="add_course.php" class="action-card">
                        <div class="action-icon">➕</div>
                        <div class="action-title">Add New Course</div>
                    </a>
                    <a href="add_video.php" class="action-card">
                        <div class="action-icon">🎥</div>
                        <div class="action-title">Add New Video</div>
                    </a>
                    <a href="courses.php" class="action-card">
                        <div class="action-icon">📚</div>
                        <div class="action-title">Manage Courses</div>
                    </a>
                    <a href="videos.php" class="action-card">
                        <div class="action-icon">📹</div>
                        <div class="action-title">Manage Videos</div>
                    </a>
                    <a href="users.php" class="action-card">
                        <div class="action-icon">👥</div>
                        <div class="action-title">Manage Users</div>
                    </a>
                    <a href="enrollments.php" class="action-card">
                        <div class="action-icon">📊</div>
                        <div class="action-title">View Enrollments</div>
                    </a>
                </div>
            </div>

            <!-- Recent Enrollments -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Recent Enrollments</h2>
                    <a href="enrollments.php" class="btn btn-outline">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Course</th>
                                <th>Enrolled Date</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_enrollments) && count($recent_enrollments) > 0): ?>
                                <?php foreach ($recent_enrollments as $enrollment): ?>
                                    <?php if (!empty($enrollment['username']) && !empty($enrollment['course_title'])): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($enrollment['username']); ?></td>
                                        <td><?= htmlspecialchars($enrollment['course_title']); ?></td>
                                        <td><?= !empty($enrollment['enrolled_at']) ? formatDate($enrollment['enrolled_at']) : 'N/A'; ?></td>
                                        <td><?= number_format($enrollment['progress_percentage'] ?? 0, 1); ?>%</td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #666;">No enrollments yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Popular Courses -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Popular Courses</h2>
                    <a href="courses.php" class="btn btn-outline">Manage Courses</a>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Course Title</th>
                                <th>Language</th>
                                <th>Difficulty</th>
                                <th>Enrollments</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($popular_courses) > 0): ?>
                                <?php foreach ($popular_courses as $course): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($course['title']); ?></td>
                                        <td><?= htmlspecialchars($course['programming_language']); ?></td>
                                        <td>
                                            <span class="difficulty-badge difficulty-<?= $course['difficulty_level']; ?>">
                                                <?= $course['difficulty_level']; ?>
                                            </span>
                                        </td>
                                        <td><?= $course['enrollment_count']; ?></td>
                                        <td>
                                            <span style="color: <?= $course['is_active'] ? '#28a745' : '#dc3545'; ?>">
                                                <?= $course['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #666;">No courses available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- System Information -->
            <div class="dashboard-section">
                <h2 class="section-title">System Information</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    <div>
                        <h4 style="color: #333; margin-bottom: 1rem;">Database Status</h4>
                        <ul style="list-style: none; color: #666;">
                            <li>✅ MySQL Connection: Active</li>
                            <li>📊 Database: programming_tutorials</li>
                            <li>🔄 Last Backup: N/A</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: #333; margin-bottom: 1rem;">Platform Statistics</h4>
                        <ul style="list-style: none; color: #666;">
                            <li>🌐 Platform Version: 1.0.0</li>
                            <li>📱 Responsive Design: Enabled</li>
                            <li>🔐 Security: CSRF Protection Active</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Auto-refresh dashboard every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);

        // Add click animations to action cards
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 200);
            });
        });

        // Add hover effects to dashboard cards
        document.querySelectorAll('.dashboard-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 30px rgba(0,0,0,0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
                this.style.boxShadow = '';
            });
        });
    </script>
</body>
</html>
