<?php
/**
 * Admin Videos Management - CRUD operations for videos
 */

require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle video deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $video_id = (int)$_GET['delete'];
    
    // Verify CSRF token
    if (!verifyCSRFToken($_GET['csrf_token'])) {
        $error_message = "Invalid request. Please try again.";
    } else {
        // Soft delete - set is_active to 0
        $query = "UPDATE videos SET is_active = 0 WHERE id = :video_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':video_id', $video_id);
        
        if ($stmt->execute()) {
            $success_message = "Video deactivated successfully.";
        } else {
            $error_message = "Failed to deactivate video.";
        }
    }
}

// Handle video activation
if (isset($_GET['activate']) && is_numeric($_GET['activate'])) {
    $video_id = (int)$_GET['activate'];
    
    // Verify CSRF token
    if (!verifyCSRFToken($_GET['csrf_token'])) {
        $error_message = "Invalid request. Please try again.";
    } else {
        $query = "UPDATE videos SET is_active = 1 WHERE id = :video_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':video_id', $video_id);
        
        if ($stmt->execute()) {
            $success_message = "Video activated successfully.";
        } else {
            $error_message = "Failed to activate video.";
        }
    }
}

// Get filter parameters
$course_filter = isset($_GET['course']) ? (int)$_GET['course'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build base query
$where_conditions = [];
$params = [];

if ($course_filter > 0) {
    $where_conditions[] = "v.course_id = :course_id";
    $params[':course_id'] = $course_filter;
}

if ($status_filter === 'active') {
    $where_conditions[] = "v.is_active = 1";
} elseif ($status_filter === 'inactive') {
    $where_conditions[] = "v.is_active = 0";
}

if (!empty($search)) {
    $where_conditions[] = "(v.title LIKE :search OR v.description LIKE :search OR c.title LIKE :search)";
    $params[':search'] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get videos with course information
$query = "SELECT v.*, c.title as course_title, c.programming_language 
          FROM videos v 
          LEFT JOIN courses c ON v.course_id = c.id 
          $where_clause 
          ORDER BY v.created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all courses for filter dropdown
$course_query = "SELECT id, title, programming_language FROM courses WHERE is_active = 1 ORDER BY title";
$course_stmt = $db->prepare($course_query);
$course_stmt->execute();
$courses = $course_stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos Management - Admin Dashboard</title>
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

        .filters-section {
            background: var(--white);
            padding: var(--space-8);
            margin: var(--space-8) 0;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-6);
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: var(--space-2);
        }

        .filter-group label {
            font-weight: var(--font-semibold);
            color: var(--gray-700);
            font-size: var(--text-sm);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .filter-group input,
        .filter-group select {
            padding: var(--space-3) var(--space-4);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            font-size: var(--text-base);
            transition: all var(--transition-normal);
            background: var(--white);
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 3px rgba(61, 68, 112, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: var(--space-3);
            align-self: end;
            margin-top: 10px;
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

        .btn-warning {
            background: var(--warning-border);
            color: white;
        }

        .btn-danger {
            background: var(--error-border);
            color: white;
        }

        .btn-success {
            background: var(--success-border);
            color: white;
        }

        .btn-info {
            background: var(--info-border);
            color: white;
        }

        .btn-sm {
            padding: var(--space-2) var(--space-3);
            font-size: var(--text-xs);
        }

        .table-container {
            background: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin: var(--space-8) 0;
            border: 1px solid var(--gray-200);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-8);
            border-bottom: 1px solid var(--gray-200);
        }

        .table-header h3 {
            margin: 0;
            font-size: var(--text-xl);
            font-weight: var(--font-bold);
            color: var(--gray-800);
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
        }

        .admin-table th {
            background: var(--gray-50);
            padding: var(--space-4);
            text-align: left;
            font-weight: var(--font-semibold);
            color: var(--gray-700);
            border-bottom: 2px solid var(--gray-200);
            font-size: var(--text-sm);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .admin-table td {
            padding: var(--space-4);
            border-bottom: 1px solid var(--gray-200);
            vertical-align: middle;
        }

        .admin-table tbody tr {
            transition: all var(--transition-normal);
        }

        .admin-table tbody tr:hover {
            background: var(--gray-50);
            transform: scale(1.01);
        }

        .thumbnail-small {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: transform var(--transition-normal);
        }

        .thumbnail-small:hover {
            transform: scale(1.05);
        }

        .thumbnail-placeholder {
            width: 80px;
            height: 60px;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-lg);
            font-size: 2rem;
            color: var(--gray-400);
        }

        .video-title strong {
            display: block;
            margin-bottom: var(--space-2);
            font-size: var(--text-base);
            color: var(--gray-800);
            line-height: 1.4;
        }

        .video-description {
            font-size: var(--text-sm);
            color: var(--gray-500);
            line-height: 1.5;
        }

        .course-badge {
            display: inline-block;
            padding: var(--space-2) var(--space-4);
            background: var(--primary-accent);
            color: white;
            border-radius: var(--radius-full);
            font-size: var(--text-sm);
            font-weight: var(--font-semibold);
        }

        .course-badge small {
            opacity: 0.9;
            font-size: var(--text-xs);
            font-weight: var(--font-normal);
        }

        .status-badge {
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            font-size: var(--text-sm);
            font-weight: var(--font-semibold);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-badge.active {
            background: var(--success-bg);
            color: var(--success-text);
        }

        .status-badge.inactive {
            background: var(--error-bg);
            color: var(--error-text);
        }

        .action-buttons {
            display: flex;
            gap: var(--space-2);
        }

        .empty-state {
            text-align: center;
            padding: var(--space-16) var(--space-8);
            color: var(--gray-500);
        }

        .empty-state p {
            font-size: var(--text-lg);
            margin-bottom: var(--space-8);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-6);
            margin: var(--space-8) 0;
        }

        .stat-card {
            background: var(--white);
            padding: var(--space-8);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            text-align: center;
            border: 1px solid var(--gray-200);
            transition: transform var(--transition-normal);
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-card h3 {
            margin-bottom: var(--space-4);
            color: var(--gray-500);
            font-size: var(--text-base);
            font-weight: var(--font-semibold);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-number {
            font-size: var(--text-4xl);
            font-weight: var(--font-bold);
            color: var(--primary-accent);
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

        @media (max-width: 768px) {
            .admin-header h1 {
                font-size: var(--text-3xl);
            }

            .filter-form {
                grid-template-columns: 1fr;
            }

            .admin-table {
                font-size: var(--text-sm);
            }

            .admin-table th,
            .admin-table td {
                padding: var(--space-3);
            }

            .action-buttons {
                flex-direction: column;
            }

            .stats-grid {
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
                <li><a href="videos.php" class="active">Videos</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">

<div class="admin-header">
    <h1>📹 Videos Management</h1>
    <p>Manage all video content in the platform</p>
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

<!-- Filters -->
<div class="filters-section">
    <form method="GET" action="videos.php" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label for="course">Course:</label>
                <select name="course" id="course">
                    <option value="0">All Courses</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo $course_filter == $course['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['title']); ?> (<?php echo htmlspecialchars($course['programming_language']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Videos</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="search">Search:</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search videos...">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="videos.php" class="btn btn-secondary">Clear</a>
            </div>
        </div>
    </form>
</div>

<!-- Videos Table -->
<div class="table-container">
    <div class="table-header">
        <h3>Videos (<?php echo count($videos); ?> total)</h3>
        <div class="table-actions">
            <a href="fetch_videos.php" class="btn btn-success">🎥 Fetch from YouTube</a>
            <a href="add_video.php" class="btn btn-primary">➕ Add New Video</a>
        </div>
    </div>
    
    <?php if (empty($videos)): ?>
        <div class="empty-state">
            <p>No videos found matching your criteria.</p>
            <a href="add_video.php" class="btn btn-primary">Add your first video</a>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Thumbnail</th>
                    <th>Title</th>
                    <th>Course</th>
                    <th>Channel</th>
                    <th>Duration</th>
                    <th>Views</th>
                    <th>Likes</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($videos as $video): ?>
                    <tr>
                        <td>
                            <?php if ($video['thumbnail_url']): ?>
                                <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" alt="Thumbnail" class="thumbnail-small">
                            <?php else: ?>
                                <div class="thumbnail-placeholder">📹</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="video-title">
                                <strong><?php echo htmlspecialchars($video['title']); ?></strong>
                                <?php if ($video['description']): ?>
                                    <div class="video-description">
                                        <?php echo htmlspecialchars(substr($video['description'], 0, 100)); ?>...
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="course-badge">
                                <?php echo htmlspecialchars($video['course_title']); ?>
                                <small>(<?php echo htmlspecialchars($video['programming_language']); ?>)</small>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($video['channel_name'] ?: 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($video['duration'] ?: 'N/A'); ?></td>
                        <td><?php echo number_format($video['views_count']); ?></td>
                        <td><?php echo number_format($video['likes_count']); ?></td>
                        <td>
                            <?php if ($video['is_active']): ?>
                                <span class="status-badge active">Active</span>
                            <?php else: ?>
                                <span class="status-badge inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?php echo htmlspecialchars($video['youtube_url']); ?>" target="_blank" class="btn btn-sm btn-info" title="View on YouTube">
                                    🔗
                                </a>
                                <a href="edit_video.php?id=<?php echo $video['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                    ✏️
                                </a>
                                <?php if ($video['is_active']): ?>
                                    <a href="videos.php?activate=<?php echo $video['id']; ?>&csrf_token=<?php echo $csrf_token; ?>" 
                                       class="btn btn-sm btn-secondary" title="Deactivate" 
                                       onclick="return confirm('Are you sure you want to deactivate this video?')">
                                        ⏸️
                                    </a>
                                <?php else: ?>
                                    <a href="videos.php?activate=<?php echo $video['id']; ?>&csrf_token=<?php echo $csrf_token; ?>" 
                                       class="btn btn-sm btn-success" title="Activate">
                                        ▶️
                                    </a>
                                <?php endif; ?>
                                <a href="videos.php?delete=<?php echo $video['id']; ?>&csrf_token=<?php echo $csrf_token; ?>" 
                                   class="btn btn-sm btn-danger" title="Delete" 
                                   onclick="return confirm('Are you sure you want to delete this video? This action cannot be undone.')">
                                    🗑️
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Videos</h3>
        <p class="stat-number"><?php echo count($videos); ?></p>
    </div>
    <div class="stat-card">
        <h3>Active Videos</h3>
        <p class="stat-number">
            <?php 
            $active_count = array_filter($videos, function($v) { return $v['is_active']; });
            echo count($active_count); 
            ?>
        </p>
    </div>
    <div class="stat-card">
        <h3>Total Views</h3>
        <p class="stat-number">
            <?php echo number_format(array_sum(array_column($videos, 'views_count'))); ?>
        </p>
    </div>
    <div class="stat-card">
        <h3>Total Likes</h3>
        <p class="stat-number">
            <?php echo number_format(array_sum(array_column($videos, 'likes_count'))); ?>
        </p>
    </div>
</div>

</div>
    </div>

<script>
    // Simple form validation and interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to table rows
        const tableRows = document.querySelectorAll('.admin-table tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.01)';
                this.style.transition = 'transform 0.2s ease';
            });
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });

        // Auto-hide alerts after 5 seconds
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
