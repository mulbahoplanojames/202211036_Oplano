<?php
/**
 * Edit Video - Modify existing video content
 */

require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get video ID from URL
$video_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($video_id === 0) {
    redirect('videos.php');
}

// Get existing video data
$query = "SELECT v.*, c.title as course_title, c.programming_language 
          FROM videos v 
          LEFT JOIN courses c ON v.course_id = c.id 
          WHERE v.id = :video_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':video_id', $video_id);
$stmt->execute();
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    redirect('videos.php');
}

// Get all courses for dropdown selection
$course_query = "SELECT id, title, programming_language FROM courses WHERE is_active = 1 ORDER BY title";
$course_stmt = $db->prepare($course_query);
$course_stmt->execute();
$courses = $course_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $youtube_url = sanitize($_POST['youtube_url']);
    $course_id = (int)$_POST['course_id'];
    $thumbnail_url = sanitize($_POST['thumbnail_url']);
    $duration = sanitize($_POST['duration']);
    $channel_name = sanitize($_POST['channel_name']);
    
    // CSRF validation
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = "Invalid request. Please try again.";
    } else {
        // Validate inputs
        if (empty($title) || empty($youtube_url) || empty($course_id)) {
            $error_message = "Please fill in all required fields.";
        } elseif (!filter_var($youtube_url, FILTER_VALIDATE_URL) || !strpos($youtube_url, 'youtube.com') && !strpos($youtube_url, 'youtu.be')) {
            $error_message = "Please enter a valid YouTube URL.";
        } else {
            // Extract YouTube video ID
            $youtube_video_id = '';
            if (strpos($youtube_url, 'youtube.com/watch?v=') !== false) {
                parse_str(parse_url($youtube_url, PHP_URL_QUERY), $query);
                $youtube_video_id = $query['v'] ?? '';
            } elseif (strpos($youtube_url, 'youtu.be/') !== false) {
                $youtube_video_id = substr(parse_url($youtube_url, PHP_URL_PATH), 1);
            }
            
            if (empty($youtube_video_id)) {
                $error_message = "Could not extract YouTube video ID from the URL.";
            } else {
                // Update video
                $query = "UPDATE videos SET 
                         title = :title, 
                         description = :description, 
                         youtube_url = :youtube_url, 
                         course_id = :course_id, 
                         thumbnail_url = :thumbnail_url, 
                         duration = :duration, 
                         channel_name = :channel_name, 
                         youtube_video_id = :youtube_video_id,
                         updated_at = CURRENT_TIMESTAMP
                         WHERE id = :video_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':youtube_url', $youtube_url);
                $stmt->bindParam(':course_id', $course_id);
                $stmt->bindParam(':thumbnail_url', $thumbnail_url);
                $stmt->bindParam(':duration', $duration);
                $stmt->bindParam(':channel_name', $channel_name);
                $stmt->bindParam(':youtube_video_id', $youtube_video_id);
                $stmt->bindParam(':video_id', $video_id);
                
                if ($stmt->execute()) {
                    $success_message = "Video updated successfully!";
                    // Refresh video data
                    $query = "SELECT v.*, c.title as course_title, c.programming_language 
                              FROM videos v 
                              LEFT JOIN courses c ON v.course_id = c.id 
                              WHERE v.id = :video_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':video_id', $video_id);
                    $stmt->execute();
                    $video = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $error_message = "Failed to update video. Please try again.";
                }
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Video - Admin Dashboard</title>
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
        }

        .admin-header p {
            font-size: var(--text-xl);
            opacity: 0.9;
            margin: 0;
        }

        .main-content {
            padding: var(--space-8) 0;
        }

        .form-container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--white);
            padding: var(--space-10);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
        }

        .form-header {
            text-align: center;
            margin-bottom: var(--space-8);
            padding-bottom: var(--space-6);
            border-bottom: 2px solid var(--gray-100);
        }

        .form-header h2 {
            font-size: var(--text-2xl);
            font-weight: var(--font-semibold);
            color: var(--gray-900);
            margin-bottom: var(--space-2);
        }

        .form-header p {
            color: var(--gray-600);
            font-size: var(--text-base);
            margin: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-6);
            margin-bottom: var(--space-8);
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: var(--font-semibold);
            color: var(--gray-700);
            margin-bottom: var(--space-2);
            font-size: var(--text-sm);
            display: flex;
            align-items: center;
            gap: var(--space-1);
        }

        .required-indicator {
            color: var(--error-border);
            font-weight: var(--font-bold);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: var(--space-3) var(--space-4);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            font-size: var(--text-base);
            font-family: var(--font-family-primary);
            transition: all var(--transition-normal);
            background-color: var(--white);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 3px rgba(61, 68, 112, 0.1);
        }

        .form-group input:hover,
        .form-group select:hover,
        .form-group textarea:hover {
            border-color: var(--gray-300);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            line-height: 1.5;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-help-text {
            font-size: var(--text-xs);
            color: var(--gray-500);
            margin-top: var(--space-1);
            font-style: italic;
        }

        .form-actions {
            display: flex;
            gap: var(--space-4);
            justify-content: flex-end;
            padding-top: var(--space-8);
            border-top: 2px solid var(--gray-100);
            margin-top: var(--space-8);
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

        .btn-danger {
            background: var(--error-bg);
            color: var(--error-text);
            border: 1px solid var(--error-border);
        }

        .btn-danger:hover {
            background: var(--error-border);
            color: white;
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

        .video-info {
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--primary-light) 100%);
            padding: var(--space-6);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-8);
            border: 1px solid var(--primary-light);
        }

        .video-info h3 {
            margin-top: 0;
            color: var(--primary-dark);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .admin-header h1 {
                font-size: var(--text-3xl);
            }

            .admin-nav-links {
                flex-wrap: wrap;
                gap: var(--space-2);
            }

            .admin-nav-links a {
                padding: var(--space-2) var(--space-3);
                font-size: var(--text-sm);
            }

            .form-actions {
                flex-direction: column;
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
                <li><a href="videos.php" class="active">Videos</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">

<div class="admin-header">
    <h1>📹 Edit Video</h1>
    <p>Modify video tutorial information</p>
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

<div class="video-info">
    <h3>Current Video Information</h3>
    <p><strong>Title:</strong> <?php echo htmlspecialchars($video['title']); ?></p>
    <p><strong>Course:</strong> <?php echo htmlspecialchars($video['course_title'] ?? 'Not assigned'); ?></p>
    <p><strong>YouTube URL:</strong> <?php echo htmlspecialchars($video['youtube_url']); ?></p>
</div>

<div class="form-container">
    <div class="form-header">
        <h2>Edit Video Details</h2>
        <p>Update the information for this video tutorial</p>
    </div>
    
    <form method="POST" action="edit_video.php?id=<?php echo $video_id; ?>" id="videoForm">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <div class="form-grid">
            <div class="form-group">
                <label for="title">
                    Video Title
                    <span class="required-indicator">*</span>
                </label>
                <input type="text" id="title" name="title" required 
                       value="<?php echo htmlspecialchars($video['title']); ?>"
                       placeholder="e.g., Introduction to Python Variables">
                <div class="form-help-text">Choose a clear, descriptive title for your video</div>
            </div>

            <div class="form-group">
                <label for="course_id">
                    Course
                    <span class="required-indicator">*</span>
                </label>
                <select id="course_id" name="course_id" required>
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo ($video['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['title']); ?> (<?php echo htmlspecialchars($course['programming_language']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-help-text">Select the course this video belongs to</div>
            </div>

            <div class="form-group">
                <label for="youtube_url">
                    YouTube URL
                    <span class="required-indicator">*</span>
                </label>
                <input type="url" id="youtube_url" name="youtube_url" required 
                       value="<?php echo htmlspecialchars($video['youtube_url']); ?>"
                       placeholder="https://www.youtube.com/watch?v=...">
                <div class="form-help-text">Link to the YouTube video</div>
            </div>

            <div class="form-group">
                <label for="channel_name">Channel Name</label>
                <input type="text" id="channel_name" name="channel_name" 
                       value="<?php echo htmlspecialchars($video['channel_name'] ?? ''); ?>"
                       placeholder="e.g., Code Academy">
                <div class="form-help-text">Optional: Name of the YouTube channel</div>
            </div>

            <div class="form-group">
                <label for="duration">Duration</label>
                <input type="text" id="duration" name="duration" 
                       value="<?php echo htmlspecialchars($video['duration'] ?? ''); ?>"
                       placeholder="e.g., 15:30">
                <div class="form-help-text">Optional: Video duration in MM:SS format</div>
            </div>

            <div class="form-group">
                <label for="thumbnail_url">Thumbnail URL</label>
                <input type="url" id="thumbnail_url" name="thumbnail_url" 
                       value="<?php echo htmlspecialchars($video['thumbnail_url'] ?? ''); ?>"
                       placeholder="https://example.com/thumbnail.jpg">
                <div class="form-help-text">Optional: Custom thumbnail image URL</div>
            </div>

            <div class="form-group full-width">
                <label for="description">Video Description</label>
                <textarea id="description" name="description" rows="5"
                          placeholder="Describe what this video covers, including key topics and learning points..."><?php echo htmlspecialchars($video['description'] ?? ''); ?></textarea>
                <div class="form-help-text">Optional: Provide a description of the video content</div>
            </div>
        </div>

        <div class="form-actions">
            <a href="videos.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <span>📹</span>
                Update Video
            </button>
        </div>
    </form>
</div>

</div>
    </div>

<script>
    // Form validation
    document.getElementById('videoForm').addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const youtubeUrl = document.getElementById('youtube_url').value.trim();
        const courseId = document.getElementById('course_id').value;
        
        if (!title || !youtubeUrl || !courseId) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
        
        if (title.length < 3) {
            e.preventDefault();
            alert('Video title must be at least 3 characters long.');
            return false;
        }
        
        // Validate YouTube URL
        if (!youtubeUrl.includes('youtube.com') && !youtubeUrl.includes('youtu.be')) {
            e.preventDefault();
            alert('Please enter a valid YouTube URL.');
            return false;
        }
    });

    // Auto-focus on title field
    document.getElementById('title').focus();

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
</script>

</body>
</html>
