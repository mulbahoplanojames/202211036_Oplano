<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

startSession();

$user = getCurrentUser();
$alert = getAlert();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Course Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">Course Management System</div>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="courses/index.php">Courses</a></li>
                    <?php if ($user): ?>
                        <?php if ($user['role'] === 'admin'): ?>
                            <li><a href="admin/dashboard.php">Dashboard</a></li>
                            <li><a href="admin/courses.php">Manage Courses</a></li>
                        <?php else: ?>
                            <li><a href="user/dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="auth/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="auth/login.php">Login</a></li>
                        <li><a href="auth/register.php">Create Account</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="home-page">
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Welcome to Course Management System</h1>
                    <p>Discover, learn, and excel with our comprehensive course platform</p>
                    <?php if (!$user): ?>
                        <div class="hero-buttons">
                            <a href="auth/register.php" class="btn btn-primary btn-large">Get Started</a>
                            <a href="courses/index.php" class="btn btn-secondary btn-large">Browse Courses</a>
                        </div>
                    <?php else: ?>
                        <div class="hero-buttons">
                            <a href="courses/index.php" class="btn btn-primary btn-large">Browse Courses</a>
                            <?php if ($user['role'] === 'admin'): ?>
                                <a href="admin/dashboard.php" class="btn btn-secondary btn-large">Dashboard</a>
                            <?php else: ?>
                                <a href="user/dashboard.php" class="btn btn-secondary btn-large">My Dashboard</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features">
            <div class="container">
                <h2>Why Choose Our Platform?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">📚</div>
                        <h3>Diverse Courses</h3>
                        <p>Access a wide range of courses across various subjects and skill levels</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">👨‍🏫</div>
                        <h3>Expert Instructors</h3>
                        <p>Learn from experienced instructors who are experts in their fields</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🎯</div>
                        <h3>Flexible Learning</h3>
                        <p>Study at your own pace with flexible schedules and online access</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📈</div>
                        <h3>Track Progress</h3>
                        <p>Monitor your learning progress and achievements with detailed analytics</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="statistics">
            <div class="container">
                <h2>Our Impact</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Active Students</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Expert Instructors</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">100+</div>
                        <div class="stat-label">Available Courses</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">95%</div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta">
            <div class="container">
                <div class="cta-content">
                    <h2>Ready to Start Your Learning Journey?</h2>
                    <p>Join thousands of students who are already advancing their careers</p>
                    <?php if (!$user): ?>
                        <div class="cta-buttons">
                            <a href="auth/register.php" class="btn btn-primary btn-large">Create Free Account</a>
                            <a href="courses/index.php" class="btn btn-outline btn-large">Explore Courses</a>
                        </div>
                    <?php else: ?>
                        <div class="cta-buttons">
                            <a href="courses/index.php" class="btn btn-primary btn-large">Enroll in Courses</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Course Management System</h3>
                    <p>Empowering learners worldwide with quality education and flexible learning options.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="courses/index.php">Courses</a></li>
                        <li><a href="auth/login.php">Login</a></li>
                        <li><a href="auth/register.php">Register</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">FAQs</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Course Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
