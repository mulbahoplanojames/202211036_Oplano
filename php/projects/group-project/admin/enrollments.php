<?php
/**
 * Admin Enrollments Management - Manage student course enrollments
 */

require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle enrollment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'delete_enrollment':
            $enrollment_id = $_POST['enrollment_id'] ?? '';
            if ($enrollment_id && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $query = "DELETE FROM enrollments WHERE id = :enrollment_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':enrollment_id', $enrollment_id);
                $stmt->execute();
                
                $_SESSION['success'] = "Enrollment deleted successfully!";
            }
            break;
            
        case 'update_progress':
            $enrollment_id = $_POST['enrollment_id'] ?? '';
            $progress = $_POST['progress'] ?? 0;
            if ($enrollment_id && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $query = "UPDATE enrollments SET progress_percentage = :progress WHERE id = :enrollment_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':progress', $progress);
                $stmt->bindParam(':enrollment_id', $enrollment_id);
                $stmt->execute();
                
                $_SESSION['success'] = "Progress updated successfully!";
            }
            break;
    }
    
    redirect('enrollments.php');
}

// Get pagination parameters
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get search filters
$search = $_GET['search'] ?? '';
$course_filter = $_GET['course_filter'] ?? '';
$user_filter = $_GET['user_filter'] ?? '';

// Build base query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(u.username LIKE :search OR u.full_name LIKE :search OR c.title LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($course_filter)) {
    $where_conditions[] = "e.course_id = :course_id";
    $params[':course_id'] = $course_filter;
}

if (!empty($user_filter)) {
    $where_conditions[] = "e.user_id = :user_id";
    $params[':user_id'] = $user_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total enrollments count
$count_query = "SELECT COUNT(*) as total FROM enrollments e 
                JOIN users u ON e.user_id = u.id 
                JOIN courses c ON e.course_id = c.id 
                $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_enrollments = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_enrollments / $per_page);

// Get enrollments with pagination
$enrollments_query = "SELECT e.*, u.username, u.full_name, u.email, c.title as course_title, 
                      c.programming_language, c.difficulty_level 
                      FROM enrollments e 
                      JOIN users u ON e.user_id = u.id 
                      JOIN courses c ON e.course_id = c.id 
                      $where_clause 
                      ORDER BY e.enrolled_at DESC 
                      LIMIT :per_page OFFSET :offset";
$enrollments_stmt = $db->prepare($enrollments_query);
foreach ($params as $key => $value) {
    $enrollments_stmt->bindValue($key, $value);
}
$enrollments_stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$enrollments_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$enrollments_stmt->execute();
$enrollments = $enrollments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get filter options
$courses_query = "SELECT id, title FROM courses ORDER BY title";
$courses_stmt = $db->prepare($courses_query);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

$users_query = "SELECT id, username, full_name FROM users WHERE role = 'student' ORDER BY username";
$users_stmt = $db->prepare($users_query);
$users_stmt->execute();
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollments Management - CodeTutorials</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/mobile-menu.js" defer></script>
    <style>
        .admin-nav {
            background: var(--primary-dark);
            padding: 1rem 0;
        }
        .admin-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        .admin-nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .admin-nav-links a:hover,
        .admin-nav-links a.active {
            background: var(--primary-medium);
        }
        .page-header {
            background: white;
            padding: 2rem 0;
            border-bottom: 1px solid #eee;
            margin-bottom: 2rem;
        }
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }
        .table-responsive {
            overflow-x: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .enrollments-table {
            width: 100%;
            border-collapse: collapse;
        }
        .enrollments-table th,
        .enrollments-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .enrollments-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .enrollments-table tr:hover {
            background: #f8f9fa;
        }
        .progress-bar {
            width: 100px;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-accent), var(--primary-medium));
            transition: width 0.3s ease;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
        }
        .pagination a:hover {
            background: var(--primary-medium);
            color: white;
            border-color: var(--primary-medium);
        }
        .pagination .current {
            background: var(--primary-accent);
            color: white;
            border-color: var(--primary-accent);
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-accent);
        }
        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 10px;
            max-width: 500px;
            position: relative;
        }
        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="container">
            <div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <a href="../index.php" class="logo" style="color: white;">📚 CodeTutorials</a>
                    <span style="color: #bdc3c7;">|</span>
                    <span style="color: white;">Admin Panel</span>
                </div>
                <button class="mobile-menu-toggle" aria-expanded="false" aria-label="Toggle navigation menu">☰</button>
            </div>
            <ul class="admin-nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="courses.php">Courses</a></li>
                <li><a href="videos.php">Videos</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="enrollments.php" class="active">Enrollments</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1 style="font-size: 2rem; color: #333; margin-bottom: 0.5rem;">Enrollments Management</h1>
            <p style="color: #666;">View and manage student course enrollments</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="admin-content">
        <div class="container">
            <!-- Statistics Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_enrollments; ?></div>
                    <div class="stat-label">Total Enrollments</div>
                </div>
                <div class="stat-card">
                    <?php
                    $avg_progress_query = "SELECT AVG(progress_percentage) as avg_progress FROM enrollments";
                    $avg_progress_stmt = $db->prepare($avg_progress_query);
                    $avg_progress_stmt->execute();
                    $avg_progress = $avg_progress_stmt->fetch(PDO::FETCH_ASSOC)['avg_progress'];
                    ?>
                    <div class="stat-number"><?= number_format($avg_progress, 1); ?>%</div>
                    <div class="stat-label">Average Progress</div>
                </div>
                <div class="stat-card">
                    <?php
                    $completed_query = "SELECT COUNT(*) as completed FROM enrollments WHERE progress_percentage >= 100";
                    $completed_stmt = $db->prepare($completed_query);
                    $completed_stmt->execute();
                    $completed = $completed_stmt->fetch(PDO::FETCH_ASSOC)['completed'];
                    ?>
                    <div class="stat-number"><?= $completed; ?></div>
                    <div class="stat-label">Completed Courses</div>
                </div>
                <div class="stat-card">
                    <?php
                    $active_query = "SELECT COUNT(DISTINCT user_id) as active_students FROM enrollments";
                    $active_stmt = $db->prepare($active_query);
                    $active_stmt->execute();
                    $active_students = $active_stmt->fetch(PDO::FETCH_ASSOC)['active_students'];
                    ?>
                    <div class="stat-number"><?= $active_students; ?></div>
                    <div class="stat-label">Active Students</div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="filters-section">
                <form method="GET" class="filter-form">
                    <div>
                        <label for="search" style="display: block; margin-bottom: 0.5rem; color: #333;">Search</label>
                        <input type="text" id="search" name="search" value="<?= htmlspecialchars($search); ?>" 
                               placeholder="Search by user, course..." style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div>
                        <label for="course_filter" style="display: block; margin-bottom: 0.5rem; color: #333;">Course</label>
                        <select id="course_filter" name="course_filter" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id']; ?>" <?= $course_filter == $course['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($course['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="user_filter" style="display: block; margin-bottom: 0.5rem; color: #333;">Student</label>
                        <select id="user_filter" name="user_filter" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="">All Students</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id']; ?>" <?= $user_filter == $user['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($user['username'] . ' - ' . $user['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="enrollments.php" class="btn btn-outline">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Enrollments Table -->
            <div class="table-responsive">
                <table class="enrollments-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Language</th>
                            <th>Difficulty</th>
                            <th>Enrolled Date</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($enrollments) > 0): ?>
                            <?php foreach ($enrollments as $enrollment): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($enrollment['username']); ?></strong><br>
                                            <small style="color: #666;"><?= htmlspecialchars($enrollment['full_name']); ?></small><br>
                                            <small style="color: #999;"><?= htmlspecialchars($enrollment['email']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($enrollment['course_title']); ?></strong>
                                    </td>
                                    <td>
                                        <span style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.875rem;">
                                            <?= htmlspecialchars($enrollment['programming_language']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="difficulty-badge difficulty-<?= $enrollment['difficulty_level']; ?>">
                                            <?= $enrollment['difficulty_level']; ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($enrollment['enrolled_at']); ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?= min(100, $enrollment['progress_percentage']); ?>%;"></div>
                                            </div>
                                            <span><?= number_format($enrollment['progress_percentage'], 1); ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button onclick="openProgressModal(<?= $enrollment['id']; ?>, <?= $enrollment['progress_percentage']; ?>)" 
                                                    class="btn btn-outline btn-small">Edit Progress</button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this enrollment?');">
                                                <input type="hidden" name="action" value="delete_enrollment">
                                                <input type="hidden" name="enrollment_id" value="<?= $enrollment['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                                                <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #666; padding: 2rem;">
                                    No enrollments found matching your criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1; ?>&search=<?= urlencode($search); ?>&course_filter=<?= urlencode($course_filter); ?>&user_filter=<?= urlencode($user_filter); ?>">« Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>&course_filter=<?= urlencode($course_filter); ?>&user_filter=<?= urlencode($user_filter); ?>"><?= $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1; ?>&search=<?= urlencode($search); ?>&course_filter=<?= urlencode($course_filter); ?>&user_filter=<?= urlencode($user_filter); ?>">Next »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Progress Edit Modal -->
    <div id="progressModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeProgressModal()">&times;</span>
            <h3 style="margin-bottom: 1.5rem;">Update Enrollment Progress</h3>
            <form method="POST" id="progressForm">
                <input type="hidden" name="action" value="update_progress">
                <input type="hidden" name="enrollment_id" id="modalEnrollmentId">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                
                <div style="margin-bottom: 1rem;">
                    <label for="progress" style="display: block; margin-bottom: 0.5rem; color: #333;">Progress Percentage</label>
                    <input type="number" id="progress" name="progress" min="0" max="100" step="0.1" required
                           style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                    <small style="color: #666;">Enter a value between 0 and 100</small>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="closeProgressModal()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Progress</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openProgressModal(enrollmentId, currentProgress) {
            document.getElementById('modalEnrollmentId').value = enrollmentId;
            document.getElementById('progress').value = currentProgress;
            document.getElementById('progressModal').style.display = 'block';
        }

        function closeProgressModal() {
            document.getElementById('progressModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('progressModal');
            if (event.target == modal) {
                closeProgressModal();
            }
        }

        // Add hover effects to table rows
        document.querySelectorAll('.enrollments-table tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(2px)';
                this.style.transition = 'transform 0.2s ease';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    </script>
</body>
</html>
