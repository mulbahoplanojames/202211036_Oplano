<?php
/**
 * Admin Users Management - Manage platform users
 */

require_once '../includes/functions.php';

// Initialize database connection
require_once '../config/db.php';
$db = new Database();
$db->getConnection();

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'toggle_status':
            $user_id = $_POST['user_id'] ?? '';
            if ($user_id && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                // Toggle user status (active/inactive)
                $query = "UPDATE users SET role = CASE 
                         WHEN role = 'admin' THEN 'student' 
                         ELSE 'admin' 
                         END WHERE id = :user_id AND id != :current_user";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':current_user', $_SESSION['user_id']);
                $stmt->execute();
                
                $_SESSION['success'] = "User role updated successfully!";
            }
            break;
            
        case 'delete_user':
            $user_id = $_POST['user_id'] ?? '';
            if ($user_id && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                // Don't allow deleting current user or admin users
                if ($user_id != $_SESSION['user_id']) {
                    $query = "DELETE FROM users WHERE id = :user_id AND role != 'admin'";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->execute();
                    
                    $_SESSION['success'] = "User deleted successfully!";
                } else {
                    $_SESSION['error'] = "Cannot delete your own account!";
                }
            }
            break;
    }
    
    redirect('users.php');
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(username LIKE :search OR email LIKE :search OR full_name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($role_filter)) {
    $where_conditions[] = "role = :role";
    $params[':role'] = $role_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total users count
$count_query = "SELECT COUNT(*) as total FROM users $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_users = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_users / $per_page);

// Get users with pagination
$users_query = "SELECT id, username, email, full_name, role, created_at 
               FROM users $where_clause 
               ORDER BY created_at DESC 
               LIMIT :limit OFFSET :offset";
$users_stmt = $db->prepare($users_query);
foreach ($params as $key => $value) {
    $users_stmt->bindValue($key, $value);
}
$users_stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$users_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$users_stmt->execute();
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);



// Get user statistics
try {
    $total_students_query = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
    $total_students_stmt = $db->prepare($total_students_query);
    $total_students_stmt->execute();
    $total_students = $total_students_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
} catch (Exception $e) {
    $total_students = 0;
}

try {
    $total_admins_query = "SELECT COUNT(*) as total FROM users WHERE role = 'admin'";
    $total_admins_stmt = $db->prepare($total_admins_query);
    $total_admins_stmt->execute();
    $total_admins = $total_admins_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
} catch (Exception $e) {
    $total_admins = 0;
}

try {
    $recent_users_query = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
    $recent_users_stmt = $db->prepare($recent_users_query);
    $recent_users_stmt->execute();
    $recent_users = $recent_users_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recent_users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - CodeTutorials Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            .stat-number {
                font-size: 1.5rem;
            }
            .stat-label {
                font-size: 0.8rem;
            }
            .stat-card {
                padding: 1rem;
            }
        }
        @media (max-width: 768px) and (min-width: 481px) {
            .stat-number {
                font-size: 1.8rem;
            }
            .stat-label {
                font-size: 0.85rem;
            }
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
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        .filters-bar {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .search-box {
            flex: 1;
            min-width: 250px;
        }
        .search-box input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .filter-select {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            min-width: 150px;
        }
        .users-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        .admin-table th,
        .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .admin-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .admin-table tr:hover {
            background: #f8f9fa;
        }
        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .role-admin {
            background: #dc3545;
            color: white;
        }
        .role-student {
            background: #28a745;
            color: white;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.85rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        .btn-toggle {
            background: #ffc107;
            color: #212529;
        }
        .btn-toggle:hover {
            background: #e0a800;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        .pagination a {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            transition: background 0.3s ease;
        }
        .pagination a:hover {
            background: #f8f9fa;
        }
        .pagination .active {
            background: var(--primary-accent);
            color: white;
            border-color: var(--primary-accent);
        }
        .recent-users {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-top: 2rem;
        }
        .user-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }
        .user-item:last-child {
            border-bottom: none;
        }
        .user-info {
            flex: 1;
        }
        .user-name {
            font-weight: 600;
            color: #333;
        }
        .user-email {
            color: #666;
            font-size: 0.9rem;
        }
        .user-date {
            color: #999;
            font-size: 0.85rem;
        }
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                <li><a href="users.php" class="active">Users</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="dashboard" style="padding: 2rem 0;">
        <div class="container">
            <!-- Page Header -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 2.5rem; color: #333; margin-bottom: 0.5rem;">
                    Users Management
                </h1>
                <p style="color: #666;">Manage platform users and their roles</p>
            </div>

         

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_users; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $total_students; ?></div>
                    <div class="stat-label">Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $total_admins; ?></div>
                    <div class="stat-label">Administrators</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= count($recent_users); ?></div>
                    <div class="stat-label">New This Week</div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="filters-bar">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search users..." 
                           value="<?= htmlspecialchars($search); ?>"
                           onchange="window.location.href='?search=' + encodeURIComponent(this.value) + '&role=<?= urlencode($role_filter); ?>'">
                </div>
                <select class="filter-select" onchange="window.location.href='?search=<?= urlencode($search); ?>&role=' + encodeURIComponent(this.value)">
                    <option value="">All Roles</option>
                    <option value="admin" <?= $role_filter === 'admin' ? 'selected' : ''; ?>>Administrators</option>
                    <option value="student" <?= $role_filter === 'student' ? 'selected' : ''; ?>>Students</option>
                </select>
                <a href="users.php" class="btn btn-outline">Clear Filters</a>
            </div>

            <!-- Users Table -->
            <div class="users-table">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <div style="font-weight: 600; color: #333;">
                                                    <?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Unknown'); ?>
                                                </div>
                                                <div style="color: #666; font-size: 0.9rem;">
                                                    @<?= htmlspecialchars($user['username'] ?? 'unknown'); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($user['email'] ?? 'No email'); ?></td>
                                        <td>
                                            <span class="role-badge role-<?= $user['role'] ?? 'unknown'; ?>">
                                                <?= ucfirst($user['role'] ?? 'unknown'); ?>
                                            </span>
                                        </td>
                                        <td><?= isset($user['created_at']) ? formatDate($user['created_at']) : 'N/A'; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if (($user['id'] ?? 0) != $_SESSION['user_id']): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?? ''; ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                                                        <button type="submit" class="btn-sm btn-toggle" 
                                                                onclick="return confirm('Are you sure you want to change this user\'s role?')">
                                                            <?= ($user['role'] ?? 'student') === 'admin' ? 'Make Student' : 'Make Admin'; ?>
                                                        </button>
                                                    </form>
                                                    <?php if (($user['role'] ?? 'student') !== 'admin'): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="delete_user">
                                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?? ''; ?>">
                                                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                                                            <button type="submit" class="btn-sm btn-delete" 
                                                                    onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span style="color: #666; font-style: italic;">You</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #666; padding: 2rem;">
                                        No users found matching your criteria.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?search=<?= urlencode($search); ?>&role=<?= urlencode($role_filter); ?>&page=<?= $i; ?>" 
                           class="<?= $i === $page ? 'active' : ''; ?>">
                            <?= $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>

            <!-- Recent Users -->
            <div class="recent-users">
                <h3 style="margin-bottom: 1rem; color: #333;">Recent Registrations</h3>
                <?php if (count($recent_users) > 0): ?>
                    <?php foreach ($recent_users as $user): ?>
                        <div class="user-item">
                            <div class="user-info">
                                <div class="user-name"><?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Unknown'); ?></div>
                                <div class="user-email"><?= htmlspecialchars($user['email'] ?? 'No email'); ?></div>
                            </div>
                            <div class="user-date"><?= isset($user['created_at']) ? formatDate($user['created_at']) : 'N/A'; ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #666; text-align: center;">No recent registrations.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>
</html>
