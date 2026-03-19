<?php
/**
 * Homepage - Curated Programming Tutorials Web Platform
 */

require_once 'includes/functions.php';

// Get all active courses
$query = "SELECT c.*, COUNT(v.id) as video_count 
          FROM courses c 
          LEFT JOIN videos v ON c.id = v.course_id AND v.is_active = 1 
          WHERE c.is_active = 1 
          GROUP BY c.id 
          ORDER BY c.title";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curated Programming Tutorials - Learn Programming with High-Quality Videos</title>
    <meta name="description" content="Discover high-quality programming tutorials from YouTube. Learn Python, Java, JavaScript, PHP, C++ and more with curated content.">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/mobile-menu.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                        <li><a href="courses.php">Courses</a></li>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Master Programming with Curated Tutorials</h1>
            <p style="color: white;">Discover high-quality, hand-picked programming tutorials from YouTube. Learn from the best content creators without the distraction.</p>
            <?php if (!isLoggedIn()): ?>
                <div>
                    <a href="register.php" class="btn btn-outline" style="margin-right: 1rem;">Get Started Free</a>
                    <a href="courses.php" class="btn btn-primary">Browse Courses</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="courses-section" style="background: white; padding: 3rem 0;">
        <div class="container">
            <h2 class="section-title">Why Choose Our Platform?</h2>
            <div class="courses-grid">
                <div class="course-card" style="text-align: center;">
                    <div class="course-thumbnail">
                        <img src="assets/images/curated-content.svg" alt="Curated Content" style="width: 60px; height: 60px; object-fit: contain;">
                    </div>
                    <div class="course-content">
                        <h3 class="course-title">Curated Content</h3>
                        <p class="course-description">Only high-quality tutorials with over 1M views, hand-picked by our team for maximum learning value.</p>
                    </div>
                </div>
                <div class="course-card" style="text-align: center;">
                    <div class="course-thumbnail">
                        <img src="assets/images/distraction-free.svg" alt="Distraction-Free Learning" style="width: 60px; height: 60px; object-fit: contain;">
                    </div>
                    <div class="course-content">
                        <h3 class="course-title">Distraction-Free Learning</h3>
                        <p class="course-description">Focus on what matters - learning. No irrelevant recommendations or clickbait content.</p>
                    </div>
                </div>
                <div class="course-card" style="text-align: center;">
                    <div class="course-thumbnail">
                        <img src="assets/images/track-progress.svg" alt="Track Progress" style="width: 60px; height: 60px; object-fit: contain;">
                    </div>
                    <div class="course-content">
                        <h3 class="course-title">Track Progress</h3>
                        <p class="course-description">Monitor your learning journey, save favorites, and continue where you left off.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Courses Section -->
    <section class="courses-section">
        <div class="container">
            <h2 class="section-title">Popular Programming Courses</h2>
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
                            </div>
                            <div style="margin-top: 1rem;">
                                <a href="course.php?id=<?= $course['id']; ?>" class="btn btn-primary">View Tutorials</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($courses) > 0): ?>
                <div class="text-center">
                    <a href="courses.php" class="btn btn-outline">View All Courses</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="courses-section" style="background: #191e3b; color: white;">
        <div class="container">
            <h2 class="section-title" style="color: white;">Platform Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                    <div class="stat-number"><?= count($courses); ?></div>
                    <div class="stat-label">Programming Courses</div>
                </div>
                <div class="stat-card" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                    <?php 
                    $total_videos_query = "SELECT COUNT(*) as total FROM videos WHERE is_active = 1";
                    $total_videos_stmt = $db->prepare($total_videos_query);
                    $total_videos_stmt->execute();
                    $total_videos_result = $total_videos_stmt->get_result();
                    $total_videos = $total_videos_result->fetch_assoc();
                    ?>
                    <div class="stat-number"><?= $total_videos['total']; ?></div>
                    <div class="stat-label">Curated Videos</div>
                </div>
                <div class="stat-card" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                    <?php 
                    $total_views_query = "SELECT SUM(views_count) as total FROM videos WHERE is_active = 1";
                    $total_views_stmt = $db->prepare($total_views_query);
                    $total_views_stmt->execute();
                    $total_views_result = $total_views_stmt->get_result();
                    $total_views = $total_views_result->fetch_assoc();
                    ?>
                    <div class="stat-number"><?= formatViews($total_views['total']); ?></div>
                    <div class="stat-label">Total Views</div>
                </div>
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
        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add animation to cards on scroll
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

        // Observe all course cards
        document.querySelectorAll('.course-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>
