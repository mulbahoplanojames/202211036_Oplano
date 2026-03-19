<?php
/**
 * Admin Courses Management - CRUD operations for courses
 */

require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle course deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $course_id = (int)$_GET['delete'];
    
    // Verify CSRF token
    if (!verifyCSRFToken($_GET['csrf_token'])) {
        $error_message = "Invalid request. Please try again.";
    } else {
        // Soft delete - set is_active to 0
        $query = "UPDATE courses SET is_active = 0 WHERE id = :course_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        
        if ($stmt->execute()) {
            // Also deactivate associated videos
            $video_query = "UPDATE videos SET is_active = 0 WHERE course_id = :course_id";
            $video_stmt = $db->prepare($video_query);
            $video_stmt->bindParam(':course_id', $course_id);
            $video_stmt->execute();
            
            $success_message = "Course deactivated successfully.";
        } else {
            $error_message = "Failed to deactivate course.";
        }
    }
}

// Get all courses with video count
$query = "SELECT c.*, COUNT(v.id) as video_count 
          FROM courses c 
          LEFT JOIN videos v ON c.id = v.course_id 
          GROUP BY c.id 
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Admin Dashboard</title>
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
        .page-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%);
            color: white;
            padding: 2rem 0;
        }
        .page-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-title {
            font-size: 2rem;
            margin: 0;
            color: white;
        }
        .courses-container {
            padding: 2rem 0;
        }
        .filter-bar {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .filter-form {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        .course-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .course-card:hover {
            transform: translateY(-5px);
        }
        .course-header {
            height: 200px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            display: flex;
            align-items: flex-end;
        }
        .course-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.8) 100%);
        }
        .course-header-content {
            position: relative;
            z-index: 2;
            color: white;
            padding: 1.5rem;
            width: 100%;
        }
        .course-title {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--white);
        }
        .course-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.9rem;
            opacity: 1;
        }
        .course-body {
            padding: 1.5rem;
        }
        .course-description {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        .course-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #666;
        }
        .course-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="courses.php" class="active">Courses</a></li>
                <li><a href="videos.php">Videos</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">Manage Courses</h1>
            <a href="add_course.php" class="btn btn-outline-white">Add New Course</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="courses-container">
        <div class="container">
            <?php if (isset($success_message)): ?>
                <?= displayAlert('success', $success_message); ?>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <?= displayAlert('error', $error_message); ?>
            <?php endif; ?>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <form class="filter-form" method="GET">
                    <select name="status" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <select name="language" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="">All Languages</option>
                        <option value="Python">Python</option>
                        <option value="JavaScript">JavaScript</option>
                        <option value="Java">Java</option>
                        <option value="PHP">PHP</option>
                        <option value="C++">C++</option>
                    </select>
                    <select name="difficulty" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="">All Levels</option>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="courses.php" class="btn btn-outline btn-sm">Clear</a>
                </form>
            </div>

            <!-- Courses Grid -->
            <?php if (count($courses) > 0): ?>
                <div class="courses-grid">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <div class="course-header" style="background-image: url('<?= !empty($course['thumbnail_url']) ? htmlspecialchars($course['thumbnail_url']) : '../assets/images/default-course-thumbnail.svg'; ?>')">
                                <div class="course-header-content">
                                    <h3 class="course-title"><?= htmlspecialchars($course['title']); ?></h3>
                                    <div class="course-meta">
                                        <span><?= htmlspecialchars($course['programming_language']); ?></span>
                                        <span><?= ucfirst($course['difficulty_level']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="course-body">
                                <p class="course-description"><?= htmlspecialchars($course['description']); ?></p>
                                <div class="course-stats">
                                    <span><?= $course['video_count']; ?> videos</span>
                                    <span class="status-badge <?= $course['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?= $course['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                                <div class="course-actions">
                                    <a href="edit_course.php?id=<?= $course['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="videos.php?course=<?= $course['id']; ?>" class="btn btn-secondary btn-sm">Videos</a>
                                    <a href="../course.php?id=<?= $course['id']; ?>" class="btn btn-outline btn-sm" target="_blank">View</a>
                                    <?php if ($course['is_active']): ?>
                                        <a href="courses.php?delete=<?= $course['id']; ?>&csrf_token=<?= $csrf_token; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Are you sure you want to deactivate this course?')">Deactivate</a>
                                    <?php else: ?>
                                        <a href="courses.php?activate=<?= $course['id']; ?>&csrf_token=<?= $csrf_token; ?>" 
                                           class="btn btn-success btn-sm">Activate</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No courses found</h3>
                    <p>Start by adding your first course to the platform.</p>
                    <a href="add_course.php" class="btn btn-primary" style="margin-top: 1rem;">Add First Course</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Confirm deletion
        document.querySelectorAll('a[href*="delete"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to deactivate this course? This will also deactivate all associated videos.')) {
                    e.preventDefault();
                }
            });
        });

        // Add animations to course cards
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.course-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>
