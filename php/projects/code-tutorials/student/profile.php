<?php
/**
 * Student Profile - User profile management
 */

require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../login.php');
}

// Check if user is student (not admin)
if (isAdmin()) {
    redirect('../admin/dashboard.php');
}

// Get user information
$user_query = "SELECT id, username, email, full_name, created_at FROM users WHERE id = :user_id";
$user_stmt = $db->prepare($user_query);
$user_stmt->bindParam(':user_id', $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Update user profile
        $update_query = "UPDATE users SET full_name = :full_name, email = :email WHERE id = :user_id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':full_name', $full_name);
        $update_stmt->bindParam(':email', $email);
        $update_stmt->bindParam(':user_id', $_SESSION['user_id']);
        
        if ($update_stmt->execute()) {
            $success = "Profile updated successfully!";
            // Refresh user data
            $user_stmt->execute();
            $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Failed to update profile";
        }
    }
}

// Get user statistics
$enrolled_courses_query = "SELECT COUNT(*) as count FROM enrollments WHERE user_id = :user_id";
$enrolled_stmt = $db->prepare($enrolled_courses_query);
$enrolled_stmt->bindParam(':user_id', $_SESSION['user_id']);
$enrolled_stmt->execute();
$enrolled_count = $enrolled_stmt->fetch(PDO::FETCH_ASSOC)['count'];

$favorites_query = "SELECT COUNT(*) as count FROM user_favorites WHERE user_id = :user_id";
$favorites_stmt = $db->prepare($favorites_query);
$favorites_stmt->bindParam(':user_id', $_SESSION['user_id']);
$favorites_stmt->execute();
$favorites_count = $favorites_stmt->fetch(PDO::FETCH_ASSOC)['count'];

$watched_query = "SELECT COUNT(*) as count FROM video_progress WHERE user_id = :user_id";
$watched_stmt = $db->prepare($watched_query);
$watched_stmt->bindParam(':user_id', $_SESSION['user_id']);
$watched_stmt->execute();
$watched_count = $watched_stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CodeTutorials</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/mobile-menu.js" defer></script>
    <style>
        .student-nav {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%);
            padding: 1rem 0;
        }
        .student-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .student-nav .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .student-nav .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .student-nav .nav-links a:hover,
        .student-nav .nav-links a.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .profile-header {
            background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-light) 100%);
            color: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
        }
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-dark);
        }
        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }
        .profile-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-medium);
        }
        .btn {
            background: var(--primary-medium);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background: var(--primary-dark);
        }
        .alert {
            padding: 1rem;
            border-radius: 4px;
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
        @media (max-width: 768px) {
            .student-nav .nav-links {
                display: none;
            }
            .mobile-menu-toggle {
                display: block;
            }
            .student-nav .container {
                position: relative;
            }
            .student-nav .nav-links.mobile-open {
                display: flex;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--primary-dark);
                flex-direction: column;
                padding: 1rem;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="student-nav">
        <div class="container">
            <h1 style="color: white; margin: 0;">CodeTutorials</h1>
            <button class="mobile-menu-toggle">☰</button>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="../courses.php">Browse Courses</a></li>
                <li><a href="favorites.php">My Favorites</a></li>
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
            </div>
            <h2><?= htmlspecialchars($user['username']) ?></h2>
            <p>Member since <?= formatDate($user['created_at']) ?></p>
        </div>

        <div class="profile-stats">
            <div class="stat-card">
                <div class="stat-number"><?= $enrolled_count ?></div>
                <div class="stat-label">Enrolled Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $favorites_count ?></div>
                <div class="stat-label">Favorite Videos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $watched_count ?></div>
                <div class="stat-label">Videos Watched</div>
            </div>
        </div>

        <div class="profile-form">
            <h3>Edit Profile</h3>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                    <small style="color: #666;">Username cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <button type="submit" class="btn">Update Profile</button>
            </form>
        </div>
    </div>
</body>
</html>
