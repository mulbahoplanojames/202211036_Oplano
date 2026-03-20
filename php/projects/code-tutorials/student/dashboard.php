<?php
/**
 * Student Dashboard - Personal learning dashboard
 */

require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../login.php');
}

// Check if user is student (not admin)
if (isAdmin()) {
    redirect('admin/dashboard.php');
}

// Get user's enrolled courses
$enrolled_courses_query = "SELECT c.*, e.enrolled_at, e.progress_percentage 
                          FROM courses c 
                          JOIN enrollments e ON c.id = e.course_id 
                          WHERE e.user_id = :user_id AND c.is_active = 1 
                          ORDER BY e.enrolled_at DESC";
$enrolled_courses_stmt = $db->prepare($enrolled_courses_query);
$enrolled_courses_stmt->bindParam(':user_id', $_SESSION['user_id']);
$enrolled_courses_stmt->execute();
$enrolled_courses = $enrolled_courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's favorite videos
$favorites_query = "SELECT v.*, c.title as course_title, c.programming_language 
                    FROM videos v 
                    JOIN user_favorites uf ON v.id = uf.video_id 
                    JOIN courses c ON v.course_id = c.id 
                    WHERE uf.user_id = :user_id AND v.is_active = 1 
                    ORDER BY uf.created_at DESC 
                    LIMIT 6";
$favorites_stmt = $db->prepare($favorites_query);
$favorites_stmt->bindParam(':user_id', $_SESSION['user_id']);
$favorites_stmt->execute();
$favorites = $favorites_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recently watched videos (from video_progress table)
$recent_videos_query = "SELECT v.*, c.title as course_title, vp.last_watched_at 
                        FROM videos v 
                        JOIN video_progress vp ON v.id = vp.video_id 
                        JOIN courses c ON v.course_id = c.id 
                        WHERE vp.user_id = :user_id AND v.is_active = 1 
                        ORDER BY vp.last_watched_at DESC 
                        LIMIT 6";
$recent_videos_stmt = $db->prepare($recent_videos_query);
$recent_videos_stmt->bindParam(':user_id', $_SESSION['user_id']);
$recent_videos_stmt->execute();
$recent_videos = $recent_videos_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available courses for enrollment
$available_courses_query = "SELECT c.*, COUNT(v.id) as video_count 
                           FROM courses c 
                           LEFT JOIN videos v ON c.id = v.course_id AND v.is_active = 1 
                           WHERE c.is_active = 1 
                           AND c.id NOT IN (
                               SELECT course_id FROM enrollments WHERE user_id = :user_id
                           )
                           GROUP BY c.id 
                           ORDER BY c.title 
                           LIMIT 6";
$available_courses_stmt = $db->prepare($available_courses_query);
$available_courses_stmt->bindParam(':user_id', $_SESSION['user_id']);
$available_courses_stmt->execute();
$available_courses = $available_courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate learning statistics
$total_enrolled = count($enrolled_courses);
$total_favorites = count($favorites);
$total_watched = count($recent_videos);

// Get detailed video statistics
$video_stats = getUserVideoStats($db, $_SESSION['user_id']);

// Calculate overall progress
$overall_progress = 0;
if ($total_enrolled > 0) {
    $total_progress = array_sum(array_column($enrolled_courses, 'progress_percentage'));
    $overall_progress = $total_progress / $total_enrolled;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - CodeTutorials</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/mobile-menu.js" defer></script>
    <style>
        .student-nav {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%);
            padding: 1rem 0;
        }
        .student-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .student-nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        .student-nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .student-nav-links a:hover,
        .student-nav-links a.active {
            background: rgba(255,255,255,0.2);
        }
        .welcome-header {
            text-align: center;
            padding: 3rem 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .welcome-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .welcome-subtitle {
            color: #666;
            font-size: 1.2rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }
        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            .stat-number {
                font-size: 1.5rem;
            }
            .stat-label {
                font-size: 0.8rem;
            }
            .stat-card {
                padding: 1rem;
            }
        }
        @media (max-width: 768px) and (min-width: 481px) {
            .stat-number {
                font-size: 1.8rem;
            }
            .stat-label {
                font-size: 0.85rem;
            }
        }
        .additional-stats {
            grid-template-columns: repeat(2, 1fr);
        }
        @media (min-width: 768px) {
            .additional-stats {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        @media (max-width: 480px) {
            .additional-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-accent);
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #666;
            font-size: 1rem;
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
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        @media (min-width: 768px) {
            .courses-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }
        @media (max-width: 480px) {
            .courses-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
        .course-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            transition: transform 0.3s ease;
            border: 2px solid transparent;
        }
        .course-card:hover {
            transform: translateY(-3px);
            border-color: var(--primary-accent);
        }
        .course-title {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: #666;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, var(--primary-accent) 0%, var(--primary-medium) 100%);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        .video-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        @media (min-width: 768px) {
            .video-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
        @media (max-width: 480px) {
            .video-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
        .video-card {
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .video-card:hover {
            transform: translateY(-3px);
        }
        .video-thumbnail {
            position: relative;
            width: 100%;
            height: 140px;
            overflow: hidden;
        }
        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .video-info {
            padding: 1rem;
        }
        .video-title {
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 0.5rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .video-meta {
            font-size: 0.75rem;
            color: #666;
        }
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .progress-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            z-index: 2;
        }
        .progress-badge.completed {
            background: #28a745;
        }
        .progress-badge.in-progress {
            background: #ffc107;
        }
        .progress-bar-mini {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: rgba(0,0,0,0.3);
            z-index: 2;
        }
        .progress-fill-mini {
            height: 100%;
            background: #28a745;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Student Navigation -->
    <nav class="student-nav">
        <div class="container">
            <div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <a href="../index.php" class="logo" style="color: white;">📚 CodeTutorials</a>
                    <span style="color: rgba(255,255,255,0.7);">|</span>
                    <span style="color: white;">Student Dashboard</span>
                </div>
                <button class="mobile-menu-toggle" aria-expanded="false" aria-label="Toggle navigation menu">☰</button>
            </div>
            <ul class="student-nav-links">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="../courses.php">Browse Courses</a></li>
                <li><a href="favorites.php">My Favorites</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Welcome Header -->
    <div class="welcome-header">
        <div class="container">
            <h1 class="welcome-title">Welcome back, <?= htmlspecialchars($_SESSION['full_name']); ?>! 👋</h1>
            <p class="welcome-subtitle">Continue your learning journey</p>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="container" style="padding: 2rem 0;">
        <!-- Learning Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_enrolled; ?></div>
                <div class="stat-label">Enrolled Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($overall_progress, 0); ?>%</div>
                <div class="stat-label">Overall Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $video_stats['total_completed']; ?></div>
                <div class="stat-label">Videos Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= formatWatchTime($video_stats['total_watch_time']); ?></div>
                <div class="stat-label">Total Watch Time</div>
            </div>
        </div>

        <!-- Additional Video Stats -->
        <div class="dashboard-section">
            <h2 class="section-title">📊 Learning Progress Details</h2>
            <div class="stats-grid additional-stats">
                <div class="stat-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                    <div class="stat-number" style="color: white;"><?= $video_stats['total_watched']; ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.9);">Videos Started</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: white;">
                    <div class="stat-number" style="color: white;"><?= $video_stats['total_completed']; ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.9);">Videos Completed</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%); color: white;">
                    <div class="stat-number" style="color: white;">
                        <?= $video_stats['total_watched'] > 0 ? round(($video_stats['total_completed'] / $video_stats['total_watched']) * 100) : 0; ?>%
                    </div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.9);">Completion Rate</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%); color: white;">
                    <div class="stat-number" style="color: white;"><?= $total_favorites; ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.9);">Favorite Videos</div>
                </div>
            </div>
        </div>

        <!-- My Courses -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">My Courses</h2>
                <a href="../courses.php" class="btn btn-outline">Browse More</a>
            </div>
            <?php if (count($enrolled_courses) > 0): ?>
                <div class="courses-grid">
                    <?php foreach ($enrolled_courses as $course): ?>
                        <div class="course-card">
                            <h3 class="course-title"><?= htmlspecialchars($course['title']); ?></h3>
                            <div class="course-meta">
                                <span><?= htmlspecialchars($course['programming_language']); ?></span>
                                <span><?= date('M j, Y', strtotime($course['enrolled_at'])); ?></span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $course['progress_percentage']; ?>%;"></div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <span style="font-size: 0.85rem; color: #666;">
                                    Progress: <?= number_format($course['progress_percentage'], 1); ?>%
                                </span>
                            </div>
                            <a href="../course.php?id=<?= $course['id']; ?>" class="btn btn-primary" style="width: 100%;">Continue Learning</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📚</div>
                    <h3>No courses enrolled yet</h3>
                    <p>Start your learning journey by enrolling in a course.</p>
                    <a href="../courses.php" class="btn btn-primary" style="margin-top: 1rem;">Browse Courses</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recently Watched -->
        <?php if (count($recent_videos) > 0): ?>
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Recently Watched</h2>
                    <a href="history.php" class="btn btn-outline">View All</a>
                </div>
                <div class="video-grid">
                    <?php foreach ($recent_videos as $video): ?>
                        <div class="video-card" onclick="window.open('<?= htmlspecialchars($video['youtube_url']); ?>', '_blank')">
                            <div class="video-thumbnail">
                                <img src="<?= htmlspecialchars($video['thumbnail_url']); ?>" alt="<?= htmlspecialchars($video['title']); ?>">
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.7); color: white; padding: 0.5rem; border-radius: 50%; font-size: 1.2rem;">▶</div>
                                <?php if ($video['completed']): ?>
                                    <div class="progress-badge completed">✓</div>
                                <?php elseif ($video['watched_duration'] > 0): ?>
                                    <div class="progress-badge in-progress">⏸</div>
                                <?php endif; ?>
                                <?php if ($video['watched_duration'] > 0): ?>
                                    <div class="progress-bar-mini">
                                        <div class="progress-fill-mini" style="width: <?= min(100, ($video['watched_duration'] / 60) * 10); ?>%;"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="video-info">
                                <h4 class="video-title"><?= htmlspecialchars($video['title']); ?></h4>
                                <div class="video-meta">
                                    <div><?= htmlspecialchars($video['course_title']); ?></div>
                                    <div><?= date('M j, Y', strtotime($video['last_watched_at'])); ?></div>
                                </div>
                                <div style="margin-top: 0.5rem; font-size: 0.8rem;">
                                    <?php if ($video['completed']): ?>
                                        <span style="color: #28a745; font-weight: bold;">✓ Completed</span>
                                    <?php else: ?>
                                        <span style="color: #ffc107;">
                                            ⏱️ <?= formatWatchTime($video['watched_duration']); ?> watched
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Favorite Videos -->
        <?php if (count($favorites) > 0): ?>
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Favorite Videos</h2>
                    <a href="favorites.php" class="btn btn-outline">View All</a>
                </div>
                <div class="video-grid">
                    <?php foreach ($favorites as $video): ?>
                        <div class="video-card" onclick="window.open('<?= htmlspecialchars($video['youtube_url']); ?>', '_blank')">
                            <div class="video-thumbnail">
                                <img src="<?= htmlspecialchars($video['thumbnail_url']); ?>" alt="<?= htmlspecialchars($video['title']); ?>">
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.7); color: white; padding: 0.5rem; border-radius: 50%; font-size: 1.2rem;">▶</div>
                            </div>
                            <div class="video-info">
                                <h4 class="video-title"><?= htmlspecialchars($video['title']); ?></h4>
                                <div class="video-meta">
                                    <div><?= htmlspecialchars($video['course_title']); ?></div>
                                    <div>❤️ Favorite</div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recommended Courses -->
        <?php if (count($available_courses) > 0): ?>
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Recommended for You</h2>
                    <a href="../courses.php" class="btn btn-outline">Browse All</a>
                </div>
                <div class="courses-grid">
                    <?php foreach ($available_courses as $course): ?>
                        <div class="course-card">
                            <h3 class="course-title"><?= htmlspecialchars($course['title']); ?></h3>
                            <p style="color: #666; font-size: 0.9rem; margin-bottom: 1rem; line-height: 1.4;">
                                <?= htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="course-meta">
                                <span><?= htmlspecialchars($course['programming_language']); ?></span>
                                <span><?= $course['video_count']; ?> videos</span>
                            </div>
                            <a href="../course.php?id=<?= $course['id']; ?>" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">View Course</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Animate progress bars on page load
        window.addEventListener('load', function() {
            const progressFills = document.querySelectorAll('.progress-fill');
            progressFills.forEach(fill => {
                const width = fill.style.width;
                fill.style.width = '0%';
                setTimeout(() => {
                    fill.style.width = width;
                }, 100);
            });
        });

        // Add hover effects to cards
        document.querySelectorAll('.course-card, .video-card, .stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });

        // Smooth scroll for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
