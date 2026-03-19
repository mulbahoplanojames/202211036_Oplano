<?php
/**
 * Registration Page - User Registration
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

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize($_POST['full_name']);
    
    // CSRF validation
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = "Invalid request. Please try again.";
    } else {
        // Validate inputs
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error_message = "Please fill in all required fields.";
        } elseif ($password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error_message = "Password must be at least 6 characters long.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email address.";
        } elseif (strlen($username) < 3) {
            $error_message = "Username must be at least 3 characters long.";
        } else {
            // Check if username or email already exists
            $check_query = "SELECT id FROM users WHERE username = :username OR email = :email";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(':username', $username);
            $check_stmt->bindParam(':email', $email);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                $error_message = "Username or email already exists.";
            } else {
                // Create new user
                $password_hash = hashPassword($password);
                
                $insert_query = "INSERT INTO users (username, email, password_hash, full_name, role) 
                                VALUES (:username, :email, :password_hash, :full_name, 'student')";
                $insert_stmt = $db->prepare($insert_query);
                $insert_stmt->bindParam(':username', $username);
                $insert_stmt->bindParam(':email', $email);
                $insert_stmt->bindParam(':password_hash', $password_hash);
                $insert_stmt->bindParam(':full_name', $full_name);
                
                if ($insert_stmt->execute()) {
                    // Registration successful, log user in
                    $user_id = $db->lastInsertId();
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['user_role'] = 'student';
                    
                    redirect('student/dashboard.php');
                } else {
                    $error_message = "Registration failed. Please try again.";
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
    <title>Register - CodeTutorials Platform</title>
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
                        <li><a href="login.php" class="active">Login</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Registration Section -->
    <section class="min-h-screen flex items-center justify-center py-12">
        <div class="container">
            <div class="max-w-lg mx-auto">
                <div class="form-container">
                    <!-- Logo and Welcome -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-dark rounded-2xl mb-4">
                            <span class="text-white text-2xl font-bold">CT</span>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Account</h1>
                        <p class="text-gray-600">Join CodeTutorials and start learning programming</p>
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

                    <!-- Registration Form -->
                    <form method="POST" action="register.php" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label for="username" class="form-label">Username *</label>
                                <div class="relative">
                                    <input type="text" id="username" name="username" required 
                                           class="form-control"
                                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                           placeholder="Choose a username"
                                           autocomplete="username">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="full_name" class="form-label">Full Name</label>
                                <div class="relative">
                                    <input type="text" id="full_name" name="full_name" 
                                           class="form-control"
                                           value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                           placeholder="Enter your full name"
                                           autocomplete="name">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email Address *</label>
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
                            <label for="password" class="form-label">Password *</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required 
                                       class="form-control pr-10"
                                       placeholder="Create a password (min. 6 characters)"
                                       autocomplete="new-password">
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                                    <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-3 8a6 6 0 110-12 6 6 0 0112 0z"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Password must be at least 6 characters long</p>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <div class="relative">
                                <input type="password" id="confirm_password" name="confirm_password" required 
                                       class="form-control"
                                       placeholder="Confirm your password"
                                       autocomplete="new-password">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4-6-6z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="terms" name="terms" required class="form-check-input">
                                <label for="terms" class="form-check-label">
                                    I agree to the <a href="#" class="text-primary-accent hover:text-primary-medium">Terms of Service</a> and 
                                    <a href="#" class="text-primary-accent hover:text-primary-medium">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-full">
                            Create Account
                        </button>
                    </form>

                    <!-- Benefits Section -->
                    <div class="mt-8 p-6 bg-primary-light rounded-lg">
                        <h3 class="text-lg font-semibold text-primary-dark mb-4 text-center">Why Join CodeTutorials?</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="flex items-start space-x-3">
                                <span class="text-primary-accent text-lg">✓</span>
                                <span class="text-gray-700">Access curated high-quality tutorials</span>
                            </div>
                            <div class="flex items-start space-x-3">
                                <span class="text-primary-accent text-lg">✓</span>
                                <span class="text-gray-700">Track your learning progress</span>
                            </div>
                            <div class="flex items-start space-x-3">
                                <span class="text-primary-accent text-lg">✓</span>
                                <span class="text-gray-700">Save favorite videos</span>
                            </div>
                            <div class="flex items-start space-x-3">
                                <span class="text-primary-accent text-lg">✓</span>
                                <span class="text-gray-700">Get personalized recommendations</span>
                            </div>
                            <div class="flex items-start space-x-3">
                                <span class="text-primary-accent text-lg">✓</span>
                                <span class="text-gray-700">Join a community of learners</span>
                            </div>
                            <div class="flex items-start space-x-3">
                                <span class="text-primary-accent text-lg">✓</span>
                                <span class="text-gray-700">Certificate of completion</span>
                            </div>
                        </div>
                    </div>

                    <!-- Login Link -->
                    <div class="mt-6 text-center">
                        <p class="text-gray-600">Already have an account?</p>
                        <a href="login.php" class="btn btn-outline btn-lg w-full mt-3">
                            Sign In
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
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.querySelector('input[name="terms"]').checked;
            
            // Basic validation
            if (!username || !email || !password || !confirmPassword) {
                e.preventDefault();
                showAlert('Please fill in all required fields.', 'error');
                return false;
            }
            
            if (username.length < 3) {
                e.preventDefault();
                showAlert('Username must be at least 3 characters long.', 'error');
                return false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showAlert('Please enter a valid email address.', 'error');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showAlert('Password must be at least 6 characters long.', 'error');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showAlert('Passwords do not match.', 'error');
                return false;
            }
            
            if (!terms) {
                e.preventDefault();
                showAlert('Please agree to the Terms of Service and Privacy Policy.', 'error');
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
            
            // Insert alert at the top of the form
            const form = document.querySelector('form');
            form.insertBefore(alertDiv, form.firstChild);
            
            // Remove alert after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Password strength validation
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            // You could display a strength indicator here
        });

        // Check if passwords match in real-time
        confirmInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#dc3545';
            } else if (confirmPassword && password === confirmPassword) {
                this.style.borderColor = '#28a745';
            } else {
                this.style.borderColor = '#ddd';
            }
        });

        // Show/hide password functionality
        function addPasswordToggle(input) {
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.textContent = '👁️';
            toggleBtn.style.cssText = 'position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.2rem;';
            toggleBtn.onclick = function() {
                input.type = input.type === 'password' ? 'text' : 'password';
                this.textContent = input.type === 'password' ? '👁️' : '👁️‍🗨️';
            };
            
            input.parentElement.style.position = 'relative';
            input.parentElement.appendChild(toggleBtn);
        }
        
        addPasswordToggle(passwordInput);
        addPasswordToggle(confirmInput);

        // Auto-focus on username field
        document.getElementById('username').focus();
    </script>
</body>
</html>
