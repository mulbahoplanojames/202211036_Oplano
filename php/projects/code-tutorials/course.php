<?php
/**
 * Course Detail Page - Display videos for a specific course
 */

require_once 'includes/functions.php';

// Get course ID from URL
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$course_id) {
    redirect('index.php');
}

// Get course details
$course = getCourseById($db, $course_id);

if (!$course) {
    redirect('index.php');
}

// Get videos for this course
$videos = getVideosByCourseId($db, $course_id);

// Check if user is enrolled (for logged-in users)
$is_enrolled = false;
$is_favorited = false;
$video_progress = [];
if (isLoggedIn()) {
    $is_enrolled = isUserEnrolled($db, $_SESSION['user_id'], $course_id);
    $is_favorited = isCourseFavorited($db, $_SESSION['user_id'], $course_id);
    
    // Get video progress for enrolled users
    if ($is_enrolled) {
        $video_progress = getCourseVideoProgress($db, $_SESSION['user_id'], $course_id);
        // Create associative array for easy lookup
        $progress_map = [];
        foreach ($video_progress as $progress) {
            $progress_map[$progress['id']] = $progress;
        }
        $video_progress = $progress_map;
    }
}

// Handle enrollment
if (isset($_POST['enroll']) && isLoggedIn() && !$is_enrolled) {
    if (enrollUserInCourse($db, $_SESSION['user_id'], $course_id)) {
        $is_enrolled = true;
        $success_message = "Successfully enrolled in this course!";
    } else {
        $error_message = "Failed to enroll. Please try again.";
    }
}

// Handle favorite toggle
if (isset($_POST['toggle_favorite']) && isLoggedIn()) {
    if (toggleCourseFavorite($db, $_SESSION['user_id'], $course_id)) {
        $is_favorited = !$is_favorited;
        $success_message = $is_favorited ? "Course added to favorites!" : "Course removed from favorites.";
    } else {
        $error_message = "Failed to update favorites. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['title']); ?> - CodeTutorials</title>
    <meta name="description" content="<?= htmlspecialchars($course['description']); ?>">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/mobile-menu.js" defer></script>
         <link rel="stylesheet" href="assets/css/saas-sections.css">
    <style>
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
        .video-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .video-modal-content {
            position: relative;
            width: 90%;
            max-width: 900px;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
        }
        .video-modal-header {
            background: #333;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .video-modal-body {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
        }
        .video-modal-body iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        .video-modal-footer {
            background: #f8f9fa;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .progress-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .btn-mark-complete {
            background: #28a745;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn-mark-complete:hover {
            background: #218838;
        }
        .btn-mark-complete.completed {
            background: #6c757d;
            cursor: default;
        }
        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }
        .close-modal:hover {
            opacity: 0.7;
        }
    </style>
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

    <!-- Course Header -->
    <section class="hero course-hero">
        <div class="container">
            <div class="course-hero-layout">
                <div class="course-hero-main">
                    <div class="course-breadcrumb">
                        <a href="courses.php">All Courses</a>
                        <span class="breadcrumb-separator">›</span>
                        <span class="breadcrumb-current"><?= htmlspecialchars($course['programming_language']); ?></span>
                    </div>
                    
                    <div class="course-header-content">
                        <div class="course-badge-row">
                            <span class="difficulty-badge difficulty-<?= $course['difficulty_level']; ?>">
                                <?= ucfirst($course['difficulty_level']); ?>
                            </span>
                            <span class="course-language-badge"><?= $course['programming_language']; ?></span>
                        </div>
                        
                        <h1 class="course-title"><?= htmlspecialchars($course['title']); ?></h1>
                        
                        <p class="course-description-white"><?= htmlspecialchars($course['description']); ?></p>
                        
                        <div class="course-stats">
                            <div class="stat-item">
                                <span class="stat-number"><?= count($videos); ?></span>
                                <span class="stat-label">Tutorials</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?= $course['difficulty_level']; ?></span>
                                <span class="stat-label">Level</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">★</span>
                                <span class="stat-label">Rated</span>
                            </div>
                        </div>
                        
                        <div class="course-action-area">
                            <?php if (isLoggedIn()): ?>
                                <div class="action-buttons" style="display: flex; gap: 1rem; align-items: center;">
                                    <?php if (!$is_enrolled): ?>
                                        <form method="POST" class="enroll-form">
                                            <button type="submit" name="enroll" class="btn btn-primary btn-enroll">
                                                <span class="btn-icon">🚀</span>
                                                Enroll Now
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <div class="enrollment-status">
                                            <div class="status-icon">✓</div>
                                            <div class="status-text">
                                                <strong>Enrolled</strong>
                                                <small>You have access to all materials</small>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form method="POST" class="favorite-form">
                                        <button type="submit" name="toggle_favorite" class="btn <?= $is_favorited ? 'btn-danger' : 'btn-outline'; ?> btn-favorite">
                                            <span class="btn-icon"><?= $is_favorited ? '❤️' : '🤍'; ?></span>
                                            <?= $is_favorited ? 'Favorited' : 'Add to Favorites'; ?>
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="auth-prompt">
                                    <a href="login.php" class="btn btn-outline">Login to Enroll</a>
                                    <a href="register.php" class="btn btn-ghost">Create Account</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="course-hero-thumbnail">
                    <?php if (!empty($course['thumbnail_url'])): ?>
                        <div class="thumbnail-container">
                            <img src="<?= htmlspecialchars($course['thumbnail_url']); ?>" alt="<?= htmlspecialchars($course['title']); ?>" class="course-thumbnail">
                            <div class="thumbnail-overlay">
                                <div class="play-button">▶</div>
                            </div>
                        </img>
                    <?php else: ?>
                        <div class="thumbnail-placeholder">
                            <div class="placeholder-icon"><?= strtoupper(substr($course['programming_language'], 0, 2)); ?></div>
                            <div class="placeholder-text"><?= $course['programming_language']; ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Messages -->
    <?php if (isset($success_message)): ?>
        <div class="container mt-2">
            <?= displayAlert('success', $success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="container mt-2">
            <?= displayAlert('error', $error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Videos Section -->
    <section class="courses-section">
        <div class="container">
            <h2 class="section-title">Curated Video Tutorials</h2>
            
            <?php if (count($videos) > 0): ?>
                <div class="videos-grid">
                    <?php foreach ($videos as $video): ?>
                        <?php 
                        $progress = isset($video_progress[$video['id']]) ? $video_progress[$video['id']] : null;
                        $is_completed = $progress && $progress['completed'];
                        $has_progress = $progress && $progress['watched_duration'] > 0;
                        ?>
                        <div class="video-card" onclick="openVideoModal(<?= $video['id']; ?>, '<?= htmlspecialchars($video['youtube_video_id']); ?>')">
                            <div class="video-thumbnail">
                                <img src="<?= htmlspecialchars($video['thumbnail_url']); ?>" alt="<?= htmlspecialchars($video['title']); ?>">
                                <div class="play-overlay">▶</div>
                                <?php if ($is_completed): ?>
                                    <div class="progress-badge completed">✓</div>
                                <?php elseif ($has_progress): ?>
                                    <div class="progress-badge in-progress">⏸</div>
                                <?php endif; ?>
                                <?php if ($progress && $progress['watched_duration'] > 0): ?>
                                    <div class="progress-bar-mini">
                                        <div class="progress-fill-mini" style="width: <?= min(100, ($progress['watched_duration'] / 60) * 10); ?>%;"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="video-info">
                                <h3 class="video-title"><?= htmlspecialchars($video['title']); ?></h3>
                                <div class="video-stats">
                                    <span>👁️ <?= formatViews($video['views_count']); ?> views</span>
                                    <span>👍 <?= number_format($video['likes_count']); ?></span>
                                    <span>💬 <?= number_format($video['comments_count']); ?></span>
                                </div>
                                <div class="channel-name"><?= htmlspecialchars($video['channel_name']); ?></div>
                                <?php if ($video['duration']): ?>
                                    <div style="color: #666; font-size: 0.85rem; margin-top: 0.5rem;">
                                        Duration: <?= htmlspecialchars($video['duration']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($progress): ?>
                                    <div style="margin-top: 0.5rem; font-size: 0.8rem;">
                                        <?php if ($is_completed): ?>
                                            <span style="color: #28a745; font-weight: bold;">✓ Completed</span>
                                        <?php else: ?>
                                            <span style="color: #ffc107;">
                                                ⏱️ <?= formatWatchTime($progress['watched_duration']); ?> watched
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($progress['last_watched_at']): ?>
                                            <div style="color: #666; margin-top: 0.25rem;">
                                                Last watched: <?= formatDate($progress['last_watched_at']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem;">
                    <h3>No videos available yet</h3>
                    <p>We're working on curating the best tutorials for this course. Check back soon!</p>
                </div>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 3rem;">
                <a href="courses.php" class="btn btn-outline">Browse All Courses</a>
                <?php if (isLoggedIn() && $is_enrolled): ?>
                    <a href="student/dashboard.php" class="btn btn-primary" style="margin-left: 1rem;">My Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Learning Tips Section -->
    <section class="courses-section" style="background: #f8f9fa;">
        <div class="container">
            <h2 class="section-title">Learning Tips</h2>
            <div class="courses-grid">
                <div class="course-card" style="text-align: center;">
                    <div class="course-thumbnail">
                        <span>📝</span>
                    </div>
                    <div class="course-content">
                        <h3 class="course-title">Take Notes</h3>
                        <p class="course-description">Write down important concepts and code snippets as you watch the tutorials.</p>
                    </div>
                </div>
                <div class="course-card" style="text-align: center;">
                    <div class="course-thumbnail">
                        <span>💻</span>
                    </div>
                    <div class="course-content">
                        <h3 class="course-title">Practice Along</h3>
                        <p class="course-description">Code along with the tutorials to reinforce your learning and build muscle memory.</p>
                    </div>
                </div>
                <div class="course-card" style="text-align: center;">
                    <div class="course-thumbnail">
                        <span>🔄</span>
                    </div>
                    <div class="course-content">
                        <h3 class="course-title">Review Regularly</h3>
                        <p class="course-description">Revisit tutorials and concepts regularly to solidify your understanding.</p>
                    </div>
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

    <!-- Video Modal -->
    <div id="videoModal" class="video-modal">
        <div class="video-modal-content">
            <div class="video-modal-header">
                <h3 id="modalVideoTitle">Video Player</h3>
                <button class="close-modal" onclick="closeVideoModal()">&times;</button>
            </div>
            <div class="video-modal-body">
                <iframe id="videoPlayer" src="" allowfullscreen allow="autoplay; encrypted-media"></iframe>
            </div>
            <div class="video-modal-footer">
                <div class="progress-controls">
                    <span id="progressText">Progress: 0s</span>
                    <button id="markCompleteBtn" class="btn-mark-complete" onclick="markVideoComplete()">
                        Mark as Complete
                    </button>
                </div>
                <div>
                    <button class="btn btn-outline" onclick="closeVideoModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentVideoId = null;
        let currentVideoProgress = 0;
        let progressInterval = null;
        let player = null;

        // YouTube API ready function
        function onYouTubeIframeAPIReady() {
            // This will be called when the YouTube API is ready
        }

        function openVideoModal(videoId, youtubeVideoId) {
            <?php if (!isLoggedIn()): ?>
                // Redirect to login if not logged in
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            currentVideoId = videoId;
            
            // Get video title from the card
            const videoCard = event.currentTarget;
            const videoTitle = videoCard.querySelector('.video-title').textContent;
            
            document.getElementById('modalVideoTitle').textContent = videoTitle;
            
            // Load YouTube video with autoplay
            const iframe = document.getElementById('videoPlayer');
            iframe.src = `https://www.youtube.com/embed/${youtubeVideoId}?enablejsapi=1&autoplay=1&rel=0`;
            
            // Show modal
            document.getElementById('videoModal').style.display = 'flex';
            
            // Load existing progress
            loadVideoProgress(videoId);
            
            // Start tracking progress
            setTimeout(() => {
                startProgressTracking();
            }, 1000);
        }

        function closeVideoModal() {
            // Stop tracking
            if (progressInterval) {
                clearInterval(progressInterval);
                progressInterval = null;
            }
            
            // Save progress
            if (currentVideoId && currentVideoProgress > 0) {
                saveVideoProgress();
            }
            
            // Clear iframe
            document.getElementById('videoPlayer').src = '';
            
            // Hide modal
            document.getElementById('videoModal').style.display = 'none';
            
            currentVideoId = null;
            currentVideoProgress = 0;
        }

        function loadVideoProgress(videoId) {
            fetch('ajax/video_progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_progress',
                    video_id: videoId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.progress) {
                    currentVideoProgress = data.progress.watched_duration || 0;
                    updateProgressDisplay();
                    
                    if (data.progress.completed) {
                        document.getElementById('markCompleteBtn').textContent = '✓ Completed';
                        document.getElementById('markCompleteBtn').classList.add('completed');
                    }
                }
            })
            .catch(error => {
                console.error('Error loading progress:', error);
            });
        }

        function startProgressTracking() {
            progressInterval = setInterval(() => {
                try {
                    // Try to get current time from YouTube iframe
                    const iframe = document.getElementById('videoPlayer');
                    if (iframe.contentWindow) {
                        // Send message to YouTube iframe to get current time
                        iframe.contentWindow.postMessage('{"event":"listening","id":1}', '*');
                        iframe.contentWindow.postMessage('{"event":"command","func":"getPlayerState","args":""}', '*');
                        iframe.contentWindow.postMessage('{"event":"command","func":"getCurrentTime","args":""}', '*');
                    }
                    
                    // Increment progress (fallback - every 5 seconds)
                    currentVideoProgress += 5;
                    updateProgressDisplay();
                } catch (e) {
                    // Fallback: increment progress every 5 seconds
                    currentVideoProgress += 5;
                    updateProgressDisplay();
                }
            }, 5000);
        }

        function updateProgressDisplay() {
            const progressText = document.getElementById('progressText');
            if (currentVideoProgress < 60) {
                progressText.textContent = `Progress: ${currentVideoProgress}s`;
            } else if (currentVideoProgress < 3600) {
                const minutes = Math.floor(currentVideoProgress / 60);
                const seconds = currentVideoProgress % 60;
                progressText.textContent = `Progress: ${minutes}m ${seconds}s`;
            } else {
                const hours = Math.floor(currentVideoProgress / 3600);
                const minutes = Math.floor((currentVideoProgress % 3600) / 60);
                progressText.textContent = `Progress: ${hours}h ${minutes}m`;
            }
        }

        function saveVideoProgress() {
            fetch('ajax/video_progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_progress',
                    video_id: currentVideoId,
                    watched_duration: currentVideoProgress,
                    completed: false
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Progress saved');
                }
            })
            .catch(error => {
                console.error('Error saving progress:', error);
            });
        }

        function markVideoComplete() {
            const btn = document.getElementById('markCompleteBtn');
            
            if (btn.classList.contains('completed')) {
                return; // Already completed
            }
            
            fetch('ajax/video_progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'mark_complete',
                    video_id: currentVideoId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.textContent = '✓ Completed';
                    btn.classList.add('completed');
                    
                    // Show success message
                    const progressText = document.getElementById('progressText');
                    progressText.textContent = '✓ Video completed!';
                    progressText.style.color = '#28a745';
                }
            })
            .catch(error => {
                console.error('Error marking complete:', error);
            });
        }

        // Listen for messages from YouTube iframe
        window.addEventListener('message', function(event) {
            try {
                const data = JSON.parse(event.data);
                if (data.event === 'infoDelivery' && data.info && data.info.currentTime) {
                    currentVideoProgress = Math.floor(data.info.currentTime);
                    updateProgressDisplay();
                }
            } catch (e) {
                // Ignore parsing errors
            }
        });

        // Close modal when clicking outside
        document.getElementById('videoModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeVideoModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && document.getElementById('videoModal').style.display === 'flex') {
                closeVideoModal();
            }
        });
    </script>
</body>
</html>
