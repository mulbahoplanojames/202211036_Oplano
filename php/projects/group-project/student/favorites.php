<?php
/**
 * Student Favorites - Display all favorite videos
 */

require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../login.php');
}

// Check if user is student (not admin)
if (isAdmin()) {
    redirect('../admin/dashboard.php');
}

// Get all user's favorite videos
$videos_query = "SELECT v.*, c.title as course_title, c.programming_language, uf.created_at as favorited_at
                FROM videos v 
                JOIN user_favorites uf ON v.id = uf.video_id 
                JOIN courses c ON v.course_id = c.id 
                WHERE uf.user_id = :user_id AND v.is_active = 1 
                ORDER BY uf.created_at DESC";
$videos_stmt = $db->prepare($videos_query);
$videos_stmt->bindParam(':user_id', $_SESSION['user_id']);
$videos_stmt->execute();
$favorite_videos = $videos_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all user's favorite courses
$courses_query = "SELECT c.*, cf.created_at as favorited_at,
                  (SELECT COUNT(*) FROM videos v WHERE v.course_id = c.id AND v.is_active = 1) as video_count
                  FROM courses c 
                  JOIN course_favorites cf ON c.id = cf.course_id 
                  WHERE cf.user_id = :user_id AND c.is_active = 1 
                  ORDER BY cf.created_at DESC";
$courses_stmt = $db->prepare($courses_query);
$courses_stmt->bindParam(':user_id', $_SESSION['user_id']);
$courses_stmt->execute();
$favorite_courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

$total_videos = count($favorite_videos);
$total_courses = count($favorite_courses);
$total_favorites = $total_videos + $total_courses;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - CodeTutorials</title>
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
        .page-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 3rem 0;
            text-align: center;
        }
        .page-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .page-subtitle {
            color: #666;
            font-size: 1.2rem;
        }
        .favorites-section {
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
            margin-bottom: 2rem;
        }
        .section-title {
            font-size: 1.5rem;
            color: #333;
        }
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e9ecef;
        }
        .tab {
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            color: #666;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }
        .tab:hover {
            color: var(--primary-accent);
        }
        .tab.active {
            color: var(--primary-accent);
        }
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--primary-accent);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .video-grid, .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        .video-card, .course-card {
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .video-card:hover, .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: var(--primary-accent);
        }
        .video-thumbnail, .course-thumbnail {
            position: relative;
            width: 100%;
            height: 180px;
            overflow: hidden;
        }
        .video-thumbnail img, .course-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 1rem;
            border-radius: 50%;
            font-size: 1.5rem;
            transition: background 0.3s ease;
        }
        .video-card:hover .play-overlay {
            background: rgba(0,0,0,0.9);
        }
        .favorite-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            padding: 0.5rem;
            border-radius: 50%;
            font-size: 1.2rem;
        }
        .video-info, .course-info {
            padding: 1.5rem;
        }
        .video-title, .course-title {
            font-size: 1rem;
            color: #333;
            margin-bottom: 0.5rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-weight: 600;
        }
        .video-description, .course-description {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .video-meta, .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #666;
        }
        .course-tag {
            background: var(--primary-light);
            color: var(--primary-dark);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .favorited-date {
            color: #999;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }
        .empty-state p {
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        .stats-bar {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-medium) 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stats-info {
            font-size: 1.1rem;
        }
        .stats-number {
            font-weight: bold;
            font-size: 1.3rem;
        }
        .course-stats {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        .course-stat {
            background: rgba(0,0,0,0.05);
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            font-size: 0.75rem;
        }
        @media (max-width: 768px) {
            .video-grid, .course-grid {
                grid-template-columns: 1fr;
            }
            .student-nav-links {
                display: none;
            }
            .stats-bar {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            .tabs {
                flex-direction: column;
            }
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
                    <span style="color: white;">My Favorites</span>
                </div>
                <button class="mobile-menu-toggle" aria-expanded="false" aria-label="Toggle navigation menu">☰</button>
            </div>
            <ul class="student-nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="../courses.php">Browse Courses</a></li>
                <li><a href="favorites.php" class="active">My Favorites</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">My Favorites ❤️</h1>
            <p class="page-subtitle">Your saved courses and videos in one place</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container" style="padding: 2rem 0;">
        <!-- Statistics Bar -->
        <div class="stats-bar">
            <div class="stats-info">
                You have <span class="stats-number"><?= $total_favorites; ?></span> total favorites: 
                <span class="stats-number"><?= $total_courses; ?></span> courses and 
                <span class="stats-number"><?= $total_videos; ?></span> videos
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-outline" style="background: white; color: var(--primary-dark); border: none;">← Back to Dashboard</a>
            </div>
        </div>

        <!-- Favorites Section -->
        <div class="favorites-section">
            <div class="section-header">
                <h2 class="section-title">My Favorites</h2>
            </div>
            
            <!-- Tabs -->
            <div class="tabs">
                <button class="tab active" onclick="showTab('courses')">
                    📚 Courses (<?= $total_courses; ?>)
                </button>
                <button class="tab" onclick="showTab('videos')">
                    🎥 Videos (<?= $total_videos; ?>)
                </button>
            </div>

            <!-- Courses Tab -->
            <div id="courses-tab" class="tab-content active">
                <?php if (count($favorite_courses) > 0): ?>
                    <div class="course-grid">
                        <?php foreach ($favorite_courses as $course): ?>
                            <div class="course-card" onclick="window.location.href='../course.php?id=<?= $course['id']; ?>'">
                                <div class="course-thumbnail">
                                    <?php if (!empty($course['thumbnail_url'])): ?>
                                        <img src="<?= htmlspecialchars($course['thumbnail_url']); ?>" alt="<?= htmlspecialchars($course['title']); ?>">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-medium) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: bold;">
                                            <?= strtoupper(substr($course['programming_language'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="favorite-badge">❤️</div>
                                </div>
                                <div class="course-info">
                                    <h3 class="course-title"><?= htmlspecialchars($course['title']); ?></h3>
                                    <p class="course-description"><?= htmlspecialchars(substr($course['description'], 0, 150)) . '...'; ?></p>
                                    <div class="course-meta">
                                        <span class="course-tag"><?= htmlspecialchars($course['programming_language']); ?></span>
                                        <span class="favorited-date">Favorited <?= date('M j, Y', strtotime($course['favorited_at'])); ?></span>
                                    </div>
                                    <div class="course-stats">
                                        <span class="course-stat">📹 <?= $course['video_count']; ?> videos</span>
                                        <span class="course-stat">📊 <?= ucfirst($course['difficulty_level']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📚</div>
                        <h3>No favorite courses yet</h3>
                        <p>Start adding courses to your favorites to build your personal learning collection.</p>
                        <a href="../courses.php" class="btn btn-primary">Browse Courses</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Videos Tab -->
            <div id="videos-tab" class="tab-content">
                <?php if (count($favorite_videos) > 0): ?>
                    <div class="video-grid">
                        <?php foreach ($favorite_videos as $video): ?>
                            <div class="video-card" onclick="window.open('<?= htmlspecialchars($video['youtube_url']); ?>', '_blank')">
                                <div class="video-thumbnail">
                                    <img src="<?= htmlspecialchars($video['thumbnail_url']); ?>" alt="<?= htmlspecialchars($video['title']); ?>">
                                    <div class="play-overlay">▶</div>
                                    <div class="favorite-badge">❤️</div>
                                </div>
                                <div class="video-info">
                                    <h3 class="video-title"><?= htmlspecialchars($video['title']); ?></h3>
                                    <p class="video-description"><?= htmlspecialchars(substr($video['description'], 0, 150)) . '...'; ?></p>
                                    <div class="video-meta">
                                        <span class="course-tag"><?= htmlspecialchars($video['programming_language']); ?></span>
                                        <span class="favorited-date">Favorited <?= date('M j, Y', strtotime($video['favorited_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🎥</div>
                        <h3>No favorite videos yet</h3>
                        <p>Start adding videos to your favorites to build your personal learning collection.</p>
                        <a href="../courses.php" class="btn btn-primary">Browse Courses</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Add hover effects to cards
        document.querySelectorAll('.video-card, .course-card').forEach(card => {
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

        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && window.location.pathname.includes('favorites.php')) {
                window.location.href = 'dashboard.php';
            }
        });
    </script>
</body>
</html>
