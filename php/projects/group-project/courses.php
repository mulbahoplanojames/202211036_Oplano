<?php
/**
 * All Courses Page - Display all available programming courses
 */

require_once 'includes/functions.php';

// Get filter parameters
$language_filter = isset($_GET['language']) ? sanitize($_GET['language']) : '';
$difficulty_filter = isset($_GET['difficulty']) ? sanitize($_GET['difficulty']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$query = "SELECT c.*, COUNT(v.id) as video_count 
          FROM courses c 
          LEFT JOIN videos v ON c.id = v.course_id AND v.is_active = 1 
          WHERE c.is_active = 1";

$params = [];

// Add search filter
if (!empty($search)) {
    $query .= " AND (c.title LIKE :search OR c.description LIKE :search OR c.programming_language LIKE :search)";
    $params[':search'] = "%$search%";
}

// Add language filter
if (!empty($language_filter)) {
    $query .= " AND c.programming_language = :language";
    $params[':language'] = $language_filter;
}

// Add difficulty filter
if (!empty($difficulty_filter)) {
    $query .= " AND c.difficulty_level = :difficulty";
    $params[':difficulty'] = $difficulty_filter;
}

$query .= " GROUP BY c.id ORDER BY c.title";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);

// Get favorite status for logged-in users
if (isLoggedIn()) {
    foreach ($courses as &$course) {
        $course['is_favorited'] = isCourseFavorited($db, $_SESSION['user_id'], $course['id']);
    }
}

// Get unique languages for filter
$language_query = "SELECT DISTINCT programming_language FROM courses WHERE is_active = 1 ORDER BY programming_language";
$language_stmt = $db->prepare($language_query);
$language_stmt->execute();
$languages = $language_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get course count for stats
$total_courses_query = "SELECT COUNT(*) as total FROM courses WHERE is_active = 1";
$total_courses_stmt = $db->prepare($total_courses_query);
$total_courses_stmt->execute();
$total_courses = $total_courses_stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Programming Courses - CodeTutorials</title>
    <meta name="description" content="Browse all available programming courses. Learn Python, Java, JavaScript, PHP, C++ and more with curated YouTube tutorials.">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/mobile-menu.js" defer></script>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">📚 CodeTutorials</a>
                <button class="mobile-menu-toggle" aria-expanded="false" aria-label="Toggle navigation menu">☰</button>
                <nav>
                    <ul class="nav-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="courses.php" class="active">Courses</a></li>
                        <?php if (isLoggedIn()): ?>
                            <?php if (isAdmin()): ?>
                                <li><a href="admin/dashboard.php">Admin Dashboard</a></li>
                            <?php else: ?>
                                <li><a href="student/dashboard.php">My Dashboard</a></li>
                            <?php endif; ?>
                            <li><a href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <section class="hero" style="background: #191e3b">
        <div class="container">
            <h1>All Programming Courses</h1>
            <p>Explore our complete collection of curated programming courses</p>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="courses-section" style="background: #f8f9fa; padding: 2rem 0;">
        <div class="container">
            <form method="GET" action="courses.php" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <div style="flex: 1; min-width: 200px;">
                    <input type="text" name="search" placeholder="Search courses..." value="<?= htmlspecialchars($search); ?>" 
                           style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <select name="language" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="">All Languages</option>
                        <?php foreach ($languages as $lang): ?>
                            <option value="<?= htmlspecialchars($lang); ?>" <?= $language_filter === $lang ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($lang); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <select name="difficulty" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="">All Levels</option>
                        <option value="beginner" <?= $difficulty_filter === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                        <option value="intermediate" <?= $difficulty_filter === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                        <option value="advanced" <?= $difficulty_filter === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="courses.php" class="btn btn-outline">Clear</a>
            </form>
        </div>
    </section>

    <!-- Courses Section -->
    <section class="courses-section">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 class="section-title" style="margin: 0;">
                    <?= count($courses); ?> Courses Available
                </h2>
                <?php if (!empty($search) || !empty($language_filter) || !empty($difficulty_filter)): ?>
                    <div style="color: #666;">
                        Showing filtered results
                    </div>
                <?php endif; ?>
            </div>

            <?php if (count($courses) > 0): ?>
                <div class="courses-grid">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <div class="course-thumbnail">
                                <?php if (!empty($course['thumbnail_url'])): ?>
                                    <img src="<?= htmlspecialchars($course['thumbnail_url']); ?>" alt="<?= htmlspecialchars($course['title']); ?>">
                                <?php else: ?>
                                    <span><?= strtoupper(substr($course['programming_language'], 0, 2)); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="course-content">
                                <h3 class="course-title"><?= htmlspecialchars($course['title']); ?></h3>
                                <p class="course-description"><?= htmlspecialchars($course['description']); ?></p>
                                <div class="course-meta">
                                    <span class="difficulty-badge difficulty-<?= $course['difficulty_level']; ?>">
                                        <?= $course['difficulty_level']; ?>
                                    </span>
                                    <span><?= $course['video_count']; ?> videos</span>
                                    <span><?= $course['programming_language']; ?></span>
                                </div>
                                <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                                    <a href="course.php?id=<?= $course['id']; ?>" class="btn btn-primary">View Tutorials</a>
                                    <?php if (isLoggedIn()): ?>
                                        <form method="POST" action="course.php?id=<?= $course['id']; ?>" style="display: inline;">
                                            <button type="submit" name="toggle_favorite" class="btn <?= $course['is_favorited'] ? 'btn-danger' : 'btn-outline'; ?>" title="<?= $course['is_favorited'] ? 'Remove from favorites' : 'Add to favorites'; ?>">
                                                <?= $course['is_favorited'] ? '❤️' : '🤍'; ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem;">
                    <h3>No courses found</h3>
                    <p>Try adjusting your filters or search terms.</p>
                    <a href="courses.php" class="btn btn-primary" style="margin-top: 1rem;">Clear Filters</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="courses-section" style="background: #191e3b; color: white;">
        <div class="container">
            <h2 class="section-title" style="color: white;">Platform Overview</h2>
            <div class="stats-grid">
                <div class="stat-card" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                    <div class="stat-number"><?= $total_courses; ?></div>
                    <div class="stat-label">Total Courses</div>
                </div>
                <div class="stat-card" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                    <?php 
                    $total_videos_query = "SELECT COUNT(*) as total FROM videos WHERE is_active = 1";
                    $total_videos_stmt = $db->prepare($total_videos_query);
                    $total_videos_stmt->execute();
                    $total_videos = $total_videos_stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="stat-number"><?= $total_videos['total']; ?></div>
                    <div class="stat-label">Curated Videos</div>
                </div>
                <div class="stat-card" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                    <div class="stat-number"><?= count($languages); ?></div>
                    <div class="stat-label">Languages</div>
                </div>
                <div class="stat-card" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                    <?php 
                    $total_views_query = "SELECT SUM(views_count) as total FROM videos WHERE is_active = 1";
                    $total_views_stmt = $db->prepare($total_views_query);
                    $total_views_stmt->execute();
                    $total_views = $total_views_stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="stat-number"><?= formatViews($total_views['total']); ?></div>
                    <div class="stat-label">Total Views</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Languages Section -->
    <section class="courses-section" style="background: #f8f9fa;">
        <div class="container">
            <h2 class="section-title">Popular Programming Languages</h2>
            <div class="courses-grid">
                <?php 
                $popular_languages = ['Python', 'JavaScript', 'Java', 'PHP', 'C++'];
                foreach ($popular_languages as $lang): 
                    $count_query = "SELECT COUNT(*) as count FROM courses WHERE programming_language = :language AND is_active = 1";
                    $count_stmt = $db->prepare($count_query);
                    $count_stmt->bindParam(':language', $lang);
                    $count_stmt->execute();
                    $course_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    
                    if ($course_count > 0):
                ?>
                    <div class="course-card" style="text-align: center; cursor: pointer;" 
                         onclick="window.location.href='courses.php?language=<?= urlencode($lang); ?>'">
                        <div class="course-thumbnail">
                            <?php
                            // Get a course with thumbnail for this language, or use default
                            $thumb_query = "SELECT thumbnail_url FROM courses WHERE programming_language = :language AND thumbnail_url IS NOT NULL AND thumbnail_url != '' AND is_active = 1 LIMIT 1";
                            $thumb_stmt = $db->prepare($thumb_query);
                            $thumb_stmt->bindParam(':language', $lang);
                            $thumb_stmt->execute();
                            $thumb_course = $thumb_stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($thumb_course && !empty($thumb_course['thumbnail_url'])):
                            ?>
                                <img src="<?= htmlspecialchars($thumb_course['thumbnail_url']); ?>" alt="<?= htmlspecialchars($lang); ?>">
                            <?php else: ?>
                                <span><?= strtoupper(substr($lang, 0, 2)); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="course-content">
                            <h3 class="course-title"><?= htmlspecialchars($lang); ?></h3>
                            <p class="course-description"><?= $course_count ?> courses available</p>
                        </div>
                    </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About CodeTutorials</h3>
                    <p>Your gateway to high-quality programming tutorials. We curate the best YouTube content to help you learn programming effectively.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="courses.php">All Courses</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Popular Courses</h3>
                    <ul>
                        <li><a href="course.php?id=1">Python Programming</a></li>
                        <li><a href="course.php?id=2">Java Development</a></li>
                        <li><a href="course.php?id=3">JavaScript Web Dev</a></li>
                        <li><a href="course.php?id=4">PHP Backend</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p>Email: info@codetutorials.com</p>
                    <p>Follow us on social media for updates</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 CodeTutorials. All rights reserved. | Curated Programming Tutorials Platform</p>
            </div>
        </div>
    </footer>

    <script>
        // Add search functionality
        document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });

        // Add animation to course cards
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
