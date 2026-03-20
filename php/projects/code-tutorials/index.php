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
    <link rel="stylesheet" href="assets/css/saas-sections.css">
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
    <section class="saas-hero">
        <div class="saas-hero-bg">
            <div class="saas-glow saas-glow-primary"></div>
            <div class="saas-glow saas-glow-secondary"></div>
            <div class="saas-grid"></div>
        </div>
        <div class="container saas-hero-container">
            <div class="saas-hero-content">
                <div class="saas-badge">
                    <span class="saas-badge-dot"></span>
                    <span class="saas-badge-text">New Curated Courses Added Daily</span>
                </div>
                <h1 class="saas-title">Master Programming with <span class="text-gradient">Curated Tutorials</span></h1>
                <p class="saas-subtitle">Discover high-quality, hand-picked programming tutorials from YouTube. Learn from the best content creators without the distraction.</p>
                <?php if (!isLoggedIn()): ?>
                    <div class="saas-actions">
                        <a href="register.php" class="btn saas-btn saas-btn-primary">
                            Get Started Free
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                        <a href="courses.php" class="btn saas-btn saas-btn-outline">
                            Browse Courses
                        </a>
                    </div>
                <?php endif; ?>
                <div class="saas-social-proof">
                    <div class="saas-avatars">
                        <div class="saas-avatar"><img src="https://i.pravatar.cc/100?img=33" alt="User"></div>
                        <div class="saas-avatar"><img src="https://i.pravatar.cc/100?img=47" alt="User"></div>
                        <div class="saas-avatar"><img src="https://i.pravatar.cc/100?img=12" alt="User"></div>
                        <div class="saas-avatar"><img src="https://i.pravatar.cc/100?img=32" alt="User"></div>
                    </div>
                    <div class="saas-proof-text">
                        <div class="saas-stars">★★★★★</div>
                        <span>Join <strong>10,000+</strong> developers learning today</span>
                    </div>
                </div>
            </div>
            
            <div class="saas-hero-visual">
                <div class="saas-code-window">
                    <div class="saas-code-header">
                        <div class="saas-code-dots">
                            <span></span><span></span><span></span>
                        </div>
                        <div class="saas-code-title">learn_to_code.py</div>
                    </div>
                    <div class="saas-code-body">
                        <pre><code><span class="keyword">def</span> <span class="function">master_programming</span>():
    <span class="variable">tutorials</span> = <span class="string">"curated"</span>
    <span class="variable">distractions</span> = <span class="number">0</span>
    
    <span class="keyword">if</span> tutorials == <span class="string">"curated"</span>:
        <span class="keyword">return</span> <span class="string">"Success!"</span>
        
<span class="comment"># Output: Success! Built with love.</span></code></pre>
                    </div>
                </div>
                
                <div class="saas-floating-card saas-float-1">
                    <div class="saas-icon-wrapper pulse-bg">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div class="saas-card-text">
                        <strong>Quality Content</strong>
                        <span>Hand-picked videos</span>
                    </div>
                </div>
                
                <div class="saas-floating-card saas-float-2">
                    <div class="saas-icon-wrapper primary-bg">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div class="saas-card-text">
                        <strong>Zero Distractions</strong>
                        <span>Focus on learning</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="saas-features">
        <div class="container">
            <div class="saas-section-header">
                <span class="saas-badge-text text-accent">Platform Features</span>
                <h2 class="saas-section-title">Why Choose Our Platform?</h2>
                <p class="saas-section-subtitle">Everything you need to master programming, without the noise.</p>
            </div>
            
            <div class="saas-features-grid">
                <div class="saas-feature-card">
                    <div class="saas-feature-icon-wrapper bg-blue">
                        <img src="assets/images/curated-content.svg" alt="Curated Content">
                    </div>
                    <div class="saas-feature-content">
                        <h3>Curated Content</h3>
                        <p>Only high-quality tutorials with over 1M views, hand-picked by our team for maximum learning value.</p>
                    </div>
                </div>
                <div class="saas-feature-card">
                    <div class="saas-feature-icon-wrapper bg-emerald">
                        <img src="assets/images/distraction-free.svg" alt="Distraction-Free Learning">
                    </div>
                    <div class="saas-feature-content">
                        <h3>Distraction-Free</h3>
                        <p>Focus on what matters - learning. No irrelevant recommendations or clickbait content.</p>
                    </div>
                </div>
                <div class="saas-feature-card">
                    <div class="saas-feature-icon-wrapper bg-purple">
                        <img src="assets/images/track-progress.svg" alt="Track Progress">
                    </div>
                    <div class="saas-feature-content">
                        <h3>Track Progress</h3>
                        <p>Monitor your learning journey, save favorites, and continue exactly where you left off.</p>
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
    <section class="saas-stats">
        <div class="saas-stats-bg"></div>
        <div class="container">
            <div class="saas-stats-grid">
                <div class="saas-stat-card">
                    <div class="saas-stat-icon">📚</div>
                    <div class="saas-stat-number"><?= count($courses); ?></div>
                    <div class="saas-stat-label">Programming Courses</div>
                </div>
                <div class="saas-stat-card">
                    <?php 
                    $total_videos_query = "SELECT COUNT(*) as total FROM videos WHERE is_active = 1";
                    $total_videos_stmt = $db->prepare($total_videos_query);
                    $total_videos_stmt->execute();
                    $total_videos_result = $total_videos_stmt->get_result();
                    $total_videos = $total_videos_result->fetch_assoc();
                    ?>
                    <div class="saas-stat-icon">🎬</div>
                    <div class="saas-stat-number"><?= $total_videos['total']; ?></div>
                    <div class="saas-stat-label">Curated Videos</div>
                </div>
                <div class="saas-stat-card">
                    <?php 
                    $total_views_query = "SELECT SUM(views_count) as total FROM videos WHERE is_active = 1";
                    $total_views_stmt = $db->prepare($total_views_query);
                    $total_views_stmt->execute();
                    $total_views_result = $total_views_stmt->get_result();
                    $total_views = $total_views_result->fetch_assoc();
                    ?>
                    <div class="saas-stat-icon">👁️</div>
                    <div class="saas-stat-number"><?= formatViews($total_views['total']); ?></div>
                    <div class="saas-stat-label">Total Views</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="saas-footer">
        <div class="container">
            <div class="saas-footer-top">
                <div class="saas-footer-brand">
                    <a href="index.php" class="saas-footer-logo">📚 CodeTutorials</a>
                    <p>Your gateway to high-quality programming tutorials. We curate the best YouTube content to help you learn programming effectively and without distraction.</p>
                    <div class="saas-social-links">
                        <a href="#" aria-label="Twitter"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg></a>
                        <a href="#" aria-label="GitHub"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg></a>
                        <a href="#" aria-label="LinkedIn"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg></a>
                    </div>
                </div>
                <div class="saas-footer-links">
                    <div class="footer-link-group">
                        <h3>Platform</h3>
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <li><a href="courses.php">All Courses</a></li>
                            <li><a href="register.php">Get Started</a></li>
                            <li><a href="login.php">Login</a></li>
                        </ul>
                    </div>
                    <div class="footer-link-group">
                        <h3>Popular Courses</h3>
                        <ul>
                            <li><a href="course.php?id=1">Python Programming</a></li>
                            <li><a href="course.php?id=2">Java Development</a></li>
                            <li><a href="course.php?id=3">JavaScript Web Dev</a></li>
                            <li><a href="course.php?id=4">PHP Backend</a></li>
                        </ul>
                    </div>
                    <div class="footer-link-group">
                        <h3>Connect</h3>
                        <ul>
                            <li><a href="#">Help Center</a></li>
                            <li><a href="#">Contact Support</a></li>
                            <li><a href="#">Community</a></li>
                            <li><a href="#">Twitter</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="saas-footer-bottom">
                <p>&copy; 2024 CodeTutorials. All rights reserved.</p>
                <div class="saas-footer-legal">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
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
