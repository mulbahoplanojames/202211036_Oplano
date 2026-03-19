<?php
/**
 * Fetch Videos from YouTube API
 * Curated Programming Tutorials Web Platform
 */

require_once '../includes/functions.php';
require_once '../includes/youtube_api_service.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Initialize YouTube API service
$youtube_service = new YouTubeAPIService($db);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int)$_POST['course_id'];
    
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = "Invalid request. Please try again.";
    } else {
        // Get course information
        $course_query = "SELECT * FROM courses WHERE id = :course_id AND is_active = 1";
        $course_stmt = $db->prepare($course_query);
        $course_stmt->bindParam(':course_id', $course_id);
        $course_stmt->execute();
        $course = $course_stmt->fetch(MYSQLI_ASSOC);
        
        if (!$course) {
            $error_message = "Invalid course selected.";
        } else {
            try {
                // Search for videos using YouTube API
                $videos = $youtube_service->searchVideosForCourse(
                    $course['title'],
                    $course['programming_language'],
                    $course['difficulty_level']
                );
                
                if (empty($videos)) {
                    $error_message = "No videos found matching the criteria. Please try again later.";
                } else {
                    // Save videos to database
                    $saved_count = $youtube_service->saveVideosToDatabase($videos, $course_id);
                    
                    if ($saved_count > 0) {
                        $success_message = "Successfully fetched and saved {$saved_count} videos for '{$course['title']}'.";
                    } else {
                        $warning_message = "Found " . count($videos) . " videos, but they may already exist in the database.";
                    }
                    
                    $fetched_videos = $videos;
                }
            } catch (Exception $e) {
                $error_message = "Error fetching videos: " . $e->getMessage();
            }
        }
    }
}

// Get all active courses
$courses_query = "SELECT * FROM courses WHERE is_active = 1 ORDER BY title";
$courses_stmt = $db->prepare($courses_query);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll(MYSQLI_ASSOC);

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fetch Videos - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family-primary);
            line-height: 1.6;
            color: var(--gray-800);
            background: var(--gray-50);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: var(--container-xl);
            margin: 0 auto;
            padding: 0 var(--space-4);
        }

        .admin-nav {
            background: var(--primary-dark);
            padding: var(--space-4) 0;
        }

        .admin-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-nav-links {
            display: flex;
            list-style: none;
            gap: var(--space-8);
            margin: 0;
            padding: 0;
        }

        .admin-nav-links a {
            color: white;
            text-decoration: none;
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-md);
            transition: background var(--transition-normal);
            font-weight: var(--font-medium);
        }

        .admin-nav-links a:hover,
        .admin-nav-links a.active {
            background: var(--primary-medium);
        }

        .admin-header {
            background: linear-gradient(135deg, var(--primary-accent) 0%, var(--primary-medium) 100%);
            color: white;
            padding: var(--space-16) 0;
            text-align: center;
        }

        .admin-header h1 {
            font-size: var(--text-4xl);
            margin-bottom: var(--space-2);
            font-weight: var(--font-bold);
            color: white;
        }

        .admin-header p {
            font-size: var(--text-xl);
            opacity: 0.9;
            margin: 0;
            color: white;
        }

        .main-content {
            padding: var(--space-8) 0;
        }

        .fetch-form-container {
            background: var(--white);
            padding: var(--space-8);
            margin: var(--space-8) 0;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
        }

        .form-group {
            margin-bottom: var(--space-6);
        }

        .form-group label {
            display: block;
            font-weight: var(--font-semibold);
            color: var(--gray-700);
            margin-bottom: var(--space-2);
            font-size: var(--text-sm);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-group select {
            width: 100%;
            padding: var(--space-3) var(--space-4);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            font-size: var(--text-base);
            transition: all var(--transition-normal);
            background: var(--white);
        }

        .form-group select:focus {
            outline: none;
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 3px rgba(61, 68, 112, 0.1);
        }

        .btn {
            padding: var(--space-3) var(--space-6);
            border: none;
            border-radius: var(--radius-lg);
            font-size: var(--text-sm);
            font-weight: var(--font-semibold);
            text-decoration: none;
            cursor: pointer;
            transition: all var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-accent) 0%, var(--primary-medium) 100%);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--gray-500);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-secondary:hover {
            background: var(--gray-600);
            transform: translateY(-2px);
        }

        .alert {
            padding: var(--space-5) var(--space-6);
            margin: var(--space-6) 0;
            border-radius: var(--radius-lg);
            border-left: 4px solid;
            position: relative;
            overflow: hidden;
            animation: slideIn var(--transition-normal);
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .alert-success {
            background: var(--success-bg);
            color: var(--success-text);
            border-left-color: var(--success-border);
        }

        .alert-error {
            background: var(--error-bg);
            color: var(--error-text);
            border-left-color: var(--error-border);
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
        }

        .videos-preview {
            background: var(--white);
            padding: var(--space-8);
            margin: var(--space-8) 0;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
        }

        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: var(--space-6);
            margin-top: var(--space-6);
        }

        .video-card {
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: transform var(--transition-normal);
            border: 1px solid var(--gray-200);
        }

        .video-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .video-thumbnail {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .video-info {
            padding: var(--space-4);
        }

        .video-title {
            font-weight: var(--font-semibold);
            color: var(--gray-800);
            margin-bottom: var(--space-2);
            line-height: 1.4;
            font-size: var(--text-sm);
        }

        .video-stats {
            display: flex;
            justify-content: space-between;
            font-size: var(--text-xs);
            color: var(--gray-500);
            margin-top: var(--space-3);
        }

        .video-channel {
            color: var(--primary-accent);
            font-size: var(--text-xs);
            margin-bottom: var(--space-2);
        }

        .api-info {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: var(--radius-lg);
            padding: var(--space-4);
            margin: var(--space-6) 0;
        }

        .api-info h4 {
            color: #1976d2;
            margin-top: 0;
        }

        @media (max-width: 768px) {
            .admin-header h1 {
                font-size: var(--text-3xl);
            }

            .videos-grid {
                grid-template-columns: 1fr;
            }

            .admin-nav-links {
                flex-wrap: wrap;
                gap: var(--space-2);
            }

            .admin-nav-links a {
                padding: var(--space-2) var(--space-3);
                font-size: var(--text-sm);
            }
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="container">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="../index.php" class="logo" style="color: white;">📚 CodeTutorials</a>
                <span style="color: #bdc3c7;">|</span>
                <span style="color: white;">Admin Panel</span>
            </div>
            <ul class="admin-nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="courses.php">Courses</a></li>
                <li><a href="videos.php">Videos</a></li>
                <li><a href="fetch_videos.php" class="active">Fetch Videos</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">

            <div class="admin-header">
                <h1>🎥 Fetch Videos from YouTube</h1>
                <p>Automatically fetch relevant educational videos for your courses</p>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($warning_message)): ?>
                <div class="alert alert-warning">
                    <?php echo htmlspecialchars($warning_message); ?>
                </div>
            <?php endif; ?>

            <!-- API Information -->
            <div class="api-info">
                <h4>📋 YouTube API Information</h4>
                <p><strong>Video Selection Criteria:</strong></p>
                <ul>
                    <li>✅ More than 1,000,000 views on YouTube</li>
                    <li>✅ Relevant to the selected programming course</li>
                    <li>✅ Educational content (tutorials, courses, guides)</li>
                    <li>✅ Medium duration (4-20 minutes) for optimal learning</li>
                </ul>
                <p><strong>Note:</strong> You need to configure your YouTube API key in <code>config/youtube_api.php</code> to use this feature.</p>
            </div>

            <!-- Fetch Form -->
            <div class="fetch-form-container">
                <h3>Select Course to Fetch Videos</h3>
                <form method="POST" action="fetch_videos.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="form-group">
                        <label for="course_id">Choose Course:</label>
                        <select name="course_id" id="course_id" required>
                            <option value="">-- Select a Course --</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>">
                                    <?php echo htmlspecialchars($course['title']); ?> 
                                    (<?php echo htmlspecialchars($course['programming_language']); ?> - 
                                    <?php echo htmlspecialchars($course['difficulty_level']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">
                            🔍 Fetch Videos
                        </button>
                        <a href="videos.php" class="btn btn-secondary">
                            ← Back to Videos
                        </a>
                    </div>
                </form>
            </div>

            <!-- Videos Preview (if fetched) -->
            <?php if (isset($fetched_videos) && !empty($fetched_videos)): ?>
                <div class="videos-preview">
                    <h3>📹 Fetched Videos Preview</h3>
                    <div class="videos-grid">
                        <?php foreach ($fetched_videos as $video): ?>
                            <div class="video-card">
                                <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($video['title']); ?>" 
                                     class="video-thumbnail">
                                <div class="video-info">
                                    <div class="video-channel">
                                        <?php echo htmlspecialchars($video['channel_name']); ?>
                                    </div>
                                    <div class="video-title">
                                        <?php echo htmlspecialchars(substr($video['title'], 0, 60)) . '...'; ?>
                                    </div>
                                    <div class="video-stats">
                                        <span>👁️ <?php echo number_format($video['views_count']); ?> views</span>
                                        <span>👍 <?php echo number_format($video['likes_count']); ?></span>
                                        <span>⏱️ <?php echo htmlspecialchars($video['duration']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>

</body>
</html>
