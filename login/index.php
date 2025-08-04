<?php
session_start();

// Include database configuration
require_once '../config/database.php';

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $db = db();
            
            // Get user from database
            $user = $db->fetch("SELECT id, username, password, role, status, first_name, last_name FROM users WHERE username = ?", [$username]);
            
            if ($user && password_verify($password, $user['password'])) {
                // Check if user is active
                if ($user['status'] === 'active') {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['logged_in'] = true;
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: ../admin/dashboard/dashboard.php');
                    } else {
                        header('Location: ../admin/dashboard/dashboard.php');
                    }
                    exit();
                } else {
                    $error = 'Your account is inactive. Please contact administrator.';
                }
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Triple 7 Auto Supply</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#065f46',
                        secondary: '#dc2626',
                        accent: '#047857',
                        'green-light': '#10b981',
                        'green-dark': '#064e3b'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-green-50 via-green-100 to-green-200 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary to-accent p-8 text-center">
                <div class="flex justify-center mb-4">
                    <img src="../assets/images/logo.png" alt="Triple 7 Auto Supply" class="h-16 w-auto">
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Welcome Back</h1>
                <p class="text-green-100">Sign in to your account</p>
            </div>
            
            <!-- Login Form -->
            <div class="p-8">
                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                            <span class="text-red-700 text-sm"><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-green-700 text-sm"><?php echo htmlspecialchars($success); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm" class="space-y-6">
                    <!-- Username Field -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-green-light"></i>Username
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                class="w-full pl-12 pr-4 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300"
                                placeholder="Enter your username"
                                required
                            >
                            <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <div id="username-error" class="hidden text-red-500 text-sm mt-1"></div>
                    </div>
                    
                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-green-light"></i>Password
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="w-full pl-12 pr-12 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300"
                                placeholder="Enter your password"
                                required
                            >
                            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <button 
                                type="button" 
                                id="togglePassword" 
                                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors duration-300"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="password-error" class="hidden text-red-500 text-sm mt-1"></div>
                    </div>
                    
                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-primary to-accent hover:from-accent hover:to-primary text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 hover:scale-105 shadow-lg transform hover:shadow-xl"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </button>
                </form>
                

            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-sm text-gray-600">
                &copy; 2024 Triple 7 Auto Supply. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        // Password toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            
            if (togglePassword && password) {
                togglePassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-eye');
                        icon.classList.toggle('fa-eye-slash');
                    }
                });
            }
        });
        
        // Form validation
        const form = document.getElementById('loginForm');
        const username = document.getElementById('username');
        const password = document.getElementById('password');
        const usernameError = document.getElementById('username-error');
        const passwordError = document.getElementById('password-error');
        
        // Real-time validation
        username.addEventListener('input', function() {
            if (this.value.trim() === '') {
                showError(usernameError, 'Username is required');
                this.classList.add('border-red-500');
            } else {
                hideError(usernameError);
                this.classList.remove('border-red-500');
            }
        });
        
        password.addEventListener('input', function() {
            if (this.value === '') {
                showError(passwordError, 'Password is required');
                this.classList.add('border-red-500');
            } else {
                hideError(passwordError);
                this.classList.remove('border-red-500');
            }
        });
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate username
            if (username.value.trim() === '') {
                showError(usernameError, 'Username is required');
                username.classList.add('border-red-500');
                isValid = false;
            }
            
            // Validate password
            if (password.value === '') {
                showError(passwordError, 'Password is required');
                password.classList.add('border-red-500');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        function showError(element, message) {
            element.textContent = message;
            element.classList.remove('hidden');
        }
        
        function hideError(element) {
            element.classList.add('hidden');
        }
        
        // Auto-focus username field
        document.addEventListener('DOMContentLoaded', function() {
            const username = document.getElementById('username');
            if (username) {
                username.focus();
            }
        });
    </script>
</body>
</html>
