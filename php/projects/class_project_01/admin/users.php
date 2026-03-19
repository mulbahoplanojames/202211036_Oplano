<?php
require_once '../config/database.php';
require_once '../includes/auth_middleware.php';

requireAdmin();

$user = getCurrentUser();
$alert = getAlert();

// Get all users
$conn = getConnection();
$stmt = $conn->prepare("
    SELECT u.*, 
           COUNT(e.id) as enrollment_count
    FROM users u
    LEFT JOIN enrollments e ON u.id = e.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">Course Management System</div>
            <nav>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="courses.php">Manage Courses</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>Manage Users</h1>
                <p>View and manage all system users</p>
            </div>

            <?php if ($alert): ?>
                <div class="alert alert-<?php echo $alert['type']; ?>">
                    <?php echo $alert['message']; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Users</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <p>No users found in the system.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Enrollments</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $userItem): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($userItem['full_name']); ?></strong>
                                                <?php if ($userItem['id'] == $user['id']): ?>
                                                    <span style="background: #28a745; color: white; padding: 0.125rem 0.5rem; border-radius: 3px; font-size: 0.75rem; margin-left: 0.5rem;">You</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($userItem['username']); ?></td>
                                            <td><?php echo htmlspecialchars($userItem['email']); ?></td>
                                            <td>
                                                <span class="btn btn-sm" style="background: <?php echo $userItem['role'] == 'admin' ? '#dc3545' : '#667eea'; ?>; color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                                    <?php echo ucfirst($userItem['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $userItem['enrollment_count']; ?></td>
                                            <td><?php echo formatDate($userItem['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div style="margin-top: 2rem; padding: 1rem;  border-radius: 5px;">
                            <h4>User Statistics</h4>
                            <?php
                            $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
                            $totalUsers = $stmt->fetch()['total'];
                            
                            $stmt = $conn->query("SELECT COUNT(*) as admins FROM users WHERE role = 'admin'");
                            $totalAdmins = $stmt->fetch()['admins'];
                            
                            $stmt = $conn->query("SELECT COUNT(*) as students FROM users WHERE role = 'user'");
                            $totalStudents = $stmt->fetch()['students'];
                            ?>
                            
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1rem;">
                                <div style="text-align: center; padding: 1rem; border:2px solid white; border-radius: 5px;">
                                    <h4 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $totalUsers; ?></h4>
                                    <p>Total Users</p>
                                </div>
                                <div style="text-align: center; padding: 1rem;  border:2px solid white;  border-radius: 5px;">
                                    <h4 style="color: #dc3545; margin-bottom: 0.5rem;"><?php echo $totalAdmins; ?></h4>
                                    <p>Administrators</p>
                                </div>
                                <div style="text-align: center; padding: 1rem;  border:2px solid white;  border-radius: 5px;">
                                    <h4 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $totalStudents; ?></h4>
                                    <p>Students</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
