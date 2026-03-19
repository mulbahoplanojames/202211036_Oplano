<?php
/**
 * Login Page - User Authentication
 */

require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('student/dashboard.php');
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    // CSRF validation
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = "Invalid request. Please try again.";
    } else {
        // Validate inputs
        if (empty($email) || empty($password)) {
            $error_message = "Please fill in all fields.";
        } else {
            // Check user credentials
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch_assoc();
            
            if ($user && verifyPassword($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Set remember me cookie if checked
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database (you'd need a remember_tokens table for production)
                    setcookie('remember_token', $token, $expiry, '/', '', false, true);
                }
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('student/dashboard.php');
                }
            } else {
                $error_message = "Invalid email or password.";
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
    <title>Login - CodeTutorials Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/mobile-menu.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <div class="logo-icon">CT</div>
                    <span>CodeTutorials</span>
                </a>
                <button class="mobile-menu-toggle" aria-expanded="false" aria-label="Toggle navigation menu">☰</button>
                <nav>
                    <ul class="nav-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="courses.php">Courses</a></li>
                        <li><a href="register.php" class="active">Register</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Login Section -->
    <section class="min-h-screen flex items-center justify-center py-12">
        <div class="container">
            <div class="max-w-md mx-auto">
                <div class="form-container">
                    <!-- Logo and Welcome -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-dark rounded-2xl mb-4">
                            <span class="text-white text-2xl font-bold">CT</span>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h1>
                        <p class="text-gray-600">Sign in to access your programming tutorials</p>
                    </div>

                    <!-- Error Message -->
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error mb-6">
                            <div class="alert-icon">⚠️</div>
                            <div class="alert-content">
                                <div class="alert-message"><?= htmlspecialchars($error_message); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form method="POST" action="login.php" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="relative">
                                <input type="email" id="email" name="email" required 
                                       class="form-control"
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       placeholder="Enter your email"
                                       autocomplete="email">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 018 0zm-4 8a4 4 0 00-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required 
                                       class="form-control pr-12"
                                       placeholder="Enter your password"
                                       autocomplete="current-password">
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center text-gray-400 hover:text-gray-600">
                                    <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-3 8a6 6 0 110-12 6 6 0 0112 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="form-check">
                                <input type="checkbox" id="remember" name="remember" class="form-check-input">
                                <label for="remember" class="form-check-label">Remember me</label>
                            </div>
                            <a href="#" class="text-sm text-primary-accent hover:text-primary-medium transition-colors">
                                Forgot password?
                            </a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-full">
                            Sign In
                        </button>
                    </form>

                    <!-- Register Link -->
                    <div class="mt-6 text-center">
                        <p class="text-gray-600">Don't have an account?</p>
                        <a href="register.php" class="btn btn-outline btn-lg w-full mt-3">
                            Create Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-minimal">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2024 CodeTutorials. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Password toggle functionality
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Update icon
            if (type === 'text') {
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.843 7c1.275 4.057 5.065 7 9.543 7a10.025 10.025 0 009.543-7c1.275-4.057 1.843-7 1.843-7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>';
            } else {
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-3 8a6 6 0 110-12 6 6 0 0112 0z"/>';
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                showAlert('Please fill in all fields.', 'error');
                return false;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showAlert('Please enter a valid email address.', 'error');
                return false;
            }
        });

        // Show alert function
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} mb-6 animate-fade-in`;
            alertDiv.innerHTML = `
                <div class="alert-icon">⚠️</div>
                <div class="alert-content">
                    <div class="alert-message">${message}</div>
                </div>
            `;
            
            const form = document.querySelector('form');
            form.parentNode.insertBefore(alertDiv, form);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>
