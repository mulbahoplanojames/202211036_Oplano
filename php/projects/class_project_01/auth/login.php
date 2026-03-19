<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

startSession();

// If user is already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('../admin/dashboard.php');
    } else {
        redirect('../user/dashboard.php');
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Get and sanitize form data
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];

        // Validation
        if (empty($username) || empty($password)) {
            $errors[] = 'Username and password are required.';
        }

        // Authenticate user
        if (empty($errors)) {
            try {
                $conn = getConnection();
                
                $stmt = $conn->prepare("SELECT id, username, email, password_hash, full_name, role FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    // Login successful - set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        redirect('../admin/dashboard.php');
                    } else {
                        redirect('../user/dashboard.php');
                    }
                } else {
                    $errors[] = 'Invalid username or password.';
                }
                
            } catch(PDOException $e) {
                $errors[] = 'Login failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Course Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">Course Management System</div>
            <nav>
                <ul class="nav-links">
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../courses/index.php">Courses</a></li>
                    <li><a href="register.php">Create Account</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Login to your account</p>
            </div>

            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="username" class="form-label">Username or Email</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>

            <p class="text-center mt-3">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>
