<?php
// Start session
session_start();

// Include database configuration
require_once '../config/database.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    // Enable error logging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Log the incoming POST data (for debugging)
    error_log('Login attempt - POST data: ' . print_r($_POST, true));
    
    header('Content-Type: application/json');
    
    try {
        // Get database connection
        try {
            $pdo = getDbConnection();
            error_log('Database connection successful');
        } catch (Exception $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new Exception('Could not connect to the database. Please try again later.');
        }
        
        // Get form data
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember-me']);
        
        // Log the sanitized input (for debugging)
        error_log("Sanitized email: " . $email);
        error_log("Password received: " . (!empty($password) ? '[PROVIDED]' : '[EMPTY]'));
        
        // Validate input
        if (empty($email) || empty($password)) {
            error_log('Validation failed: Email or password empty');
            throw new Exception('Email and password are required');
        }
        
        // Prepare and execute query
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                error_log("User not found in database: " . $email);
                throw new Exception('Invalid email or password');
            }
            
            // Log user data (without password for security)
            $userData = $user;
            unset($userData['password']);
            error_log("User found: " . print_r($userData, true));
            
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            throw new Exception('An error occurred while processing your request');
        }
        
        // Verify password is correct
        if (!password_verify($password, $user['password'])) {
            error_log("Password verification failed for user: " . $email);
            error_log("Stored hash: " . $user['password']);
            error_log("Provided password hash: " . password_hash($password, PASSWORD_DEFAULT));
            throw new Exception('Invalid email or password');
        }
        
        // Update last login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'] ?? $user['email'];
        $_SESSION['user_role'] = $user['role'] ?? 'member';
        
        // Set remember me cookie if requested (30 days)
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (30 * 24 * 60 * 60); // 30 days
            setcookie('remember_token', $token, $expires, '/', '', false, true);
            
            // Store token in database
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$hashedToken, $user['id']]);
        }
        
        // Determine redirect URL based on role
        $baseUrl = '/hearts-after-god-ministry-site';
        $redirectUrl = $baseUrl . '/dashboard/';
        
        switch (strtolower($user['role'] ?? 'member')) {
            case 'admin':
                $redirectUrl = $baseUrl . '/dashboard/admin-dashboard.php';
                break;
            case 'pastor':
                $redirectUrl = $baseUrl . '/dashboard/pastor/';
                break;
            case 'leader':
                $redirectUrl = $baseUrl . '/dashboard/leader/';
                break;
            case 'member':
            default:
                $redirectUrl = $baseUrl . '/dashboard/member/';
                break;
        }
        
        // Return success response with redirect URL
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'redirect' => $redirectUrl
        ]);
        exit;
        
    } catch (Exception $e) {
        // Return error response
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hearts After God Ministry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-card {
            backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        
        .input-field {
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .login-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 10;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .login-btn:hover::before {
            left: 100%;
        }
        
        .floating-element {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Alert styles */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid;
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background-color: #f0f9ff;
            border-color: #0ea5e9;
            color: #0c4a6e;
        }
        
        .alert-error {
            background-color: #fef2f2;
            border-color: #ef4444;
            color: #991b1b;
        }
        
        .alert-info {
            background-color: #f0f9ff;
            border-color: #3b82f6;
            color: #1e40af;
        }
        
        /* Loading overlay */
        .loading-overlay {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="h-full">
    <!-- Background -->
    <div class="min-h-screen gradient-bg relative overflow-hidden">
        <!-- Floating background elements -->
        <div class="absolute top-10 left-10 w-20 h-20 bg-white bg-opacity-10 rounded-full floating-element"></div>
        <div class="absolute top-20 right-20 w-16 h-16 bg-white bg-opacity-10 rounded-full floating-element" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-20 left-20 w-24 h-24 bg-white bg-opacity-10 rounded-full floating-element" style="animation-delay: 4s;"></div>
        <div class="absolute bottom-10 right-10 w-12 h-12 bg-white bg-opacity-10 rounded-full floating-element" style="animation-delay: 1s;"></div>
        
        <!-- Main container -->
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="w-full max-w-md">
                <!-- Logo and title -->
                <div class="text-center mb-8 fade-in">
                    <div class="mx-auto mb-6 w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-xl">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-white mb-2">Hearts After God</h1>
                    <p class="text-blue-100 text-lg">Ministry Portal</p>
                </div>

                <!-- Login form -->
                <div class="glass-card rounded-2xl p-8 shadow-2xl fade-in" style="animation-delay: 0.2s;">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome Back</h2>
                        <p class="text-gray-600">Sign in to access your dashboard</p>
                    </div>

                    <!-- Alert container -->
                    <div id="alertContainer" class="mb-4"></div>

                    <!-- Login form -->
                    <form id="loginForm" method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="login">
                        
                        <!-- Email field -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   required 
                                   autocomplete="email"
                                   class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-gray-900 placeholder-gray-500"
                                   placeholder="Enter your email address">
                        </div>

                        <!-- Password field -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       required 
                                       autocomplete="current-password"
                                       class="input-field w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-gray-900 placeholder-gray-500"
                                       placeholder="Enter your password">
                                <button type="button" 
                                        id="togglePassword" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Remember me and forgot password -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       id="remember-me" 
                                       name="remember-me"
                                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                            <a href="#" id="forgotPassword" class="text-sm text-purple-600 hover:text-purple-500">
                                Forgot password?
                            </a>
                        </div>

                        <!-- Submit button - HIGHLY VISIBLE -->
                        <div class="pt-4">
                            <button type="submit" 
                                    id="loginBtn" 
                                    class="login-btn w-full py-4 px-6 text-white font-bold text-lg rounded-lg shadow-lg hover:shadow-xl transform transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-purple-300 focus:ring-offset-2">
                                <span id="loginBtnText" class="relative z-10">Sign In</span>
                            </button>
                        </div>
                    </form>

                    <!-- Demo users info -->
                    <div class="mt-8 p-4 bg-gray-50 rounded-lg border">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Demo User Roles
                        </h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between p-2 bg-white rounded">
                                <span class="font-medium text-red-600">Admin</span>
                                <span class="text-gray-600">Full Access</span>
                            </div>
                            <div class="flex justify-between p-2 bg-white rounded">
                                <span class="font-medium text-blue-600">User</span>
                                <span class="text-gray-600">Basic Access</span>
                            </div>
                            <div class="flex justify-between p-2 bg-white rounded">
                                <span class="font-medium text-green-600">Ministry Leader</span>
                                <span class="text-gray-600">Ministry Management</span>
                            </div>
                        </div>
                    </div>

                    <!-- Contact admin -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Need an account? 
                            <a href="#" class="text-purple-600 hover:text-purple-500 font-medium">Contact Admin</a>
                        </p>
                    </div>
                </div>

                <!-- Security badges -->
                <div class="mt-6 flex items-center justify-center space-x-4 text-xs text-blue-100">
                    <div class="flex items-center space-x-1">
                        <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                        <span>Secure</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Encrypted</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Verified</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading overlay -->
    <div id="loadingOverlay" class="hidden fixed inset-0 loading-overlay flex items-center justify-center z-50">
        <div class="text-center">
            <div class="spinner mx-auto mb-4"></div>
            <p class="text-white text-lg font-medium">Signing you in...</p>
            <p class="text-blue-200 text-sm">Please wait a moment</p>
        </div>
    </div>

    <!-- Password Reset Modal -->
    <div id="resetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 transform transition-all duration-300">
            <div class="text-center mb-6">
                <div class="mx-auto mb-4 w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Reset Password</h3>
                <p class="text-gray-600 text-sm">Enter your email to receive reset instructions</p>
            </div>
            
            <form id="resetForm" class="space-y-4">
                <div>
                    <label for="resetEmail" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" 
                           id="resetEmail" 
                           name="resetEmail" 
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="Enter your email">
                </div>
                
                <div class="flex space-x-3 pt-4">
                    <button type="button" 
                            id="cancelReset"
                            class="flex-1 py-3 px-4 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-3 px-4 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700">
                        Send Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Utility functions
        const Utils = {
            showAlert: function(message, type = 'info') {
                const alertContainer = document.getElementById('alertContainer');
                const alertClass = type === 'error' ? 'alert-error' : 
                                 type === 'success' ? 'alert-success' : 'alert-info';
                
                const alertHtml = `
                    <div class="alert ${alertClass} flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            ${type === 'error' ? 
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>' :
                                type === 'success' ?
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>' :
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                            }
                        </svg>
                        <span>${message}</span>
                    </div>
                `;
                
                alertContainer.innerHTML = alertHtml;
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    alertContainer.innerHTML = '';
                }, 5000);
            },
            
            showLoading: function(show = true) {
                const overlay = document.getElementById('loadingOverlay');
                const loginBtn = document.getElementById('loginBtn');
                const loginBtnText = document.getElementById('loginBtnText');
                
                if (show) {
                    overlay.classList.remove('hidden');
                    loginBtn.disabled = true;
                    loginBtnText.textContent = 'Signing In...';
                } else {
                    overlay.classList.add('hidden');
                    loginBtn.disabled = false;
                    loginBtnText.textContent = 'Sign In';
                }
            },
            
            validateEmail: function(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
        };

        // Login form handler
        class LoginForm {
            constructor() {
                this.form = document.getElementById('loginForm');
                this.emailInput = document.getElementById('email');
                this.passwordInput = document.getElementById('password');
                this.init();
            }
            
            init() {
                this.form.addEventListener('submit', this.handleSubmit.bind(this));
                this.emailInput.focus();
                
                // Password visibility toggle
                document.getElementById('togglePassword').addEventListener('click', this.togglePasswordVisibility.bind(this));
                
                // Real-time validation
                this.emailInput.addEventListener('blur', this.validateEmail.bind(this));
                
                // Enter key handling
                this.emailInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') this.passwordInput.focus();
                });
                
                this.passwordInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') this.form.dispatchEvent(new Event('submit'));
                });
            }
            
            async handleSubmit(e) {
                e.preventDefault();
                
                // Validation
                if (!this.validateForm()) {
                    return;
                }
                
                Utils.showLoading(true);
                
                try {
                    const formData = new FormData(this.form);
                    const formDataObj = {};
                    formData.forEach((value, key) => formDataObj[key] = value);
                    
                    // Log the form data being sent
                    console.log('Submitting form data:', formDataObj);
                    
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        credentials: 'same-origin', // Include cookies with the request
                        body: new URLSearchParams(formData).toString()
                    });
                    
                    console.log('Response status:', response.status);
                    const responseText = await response.text();
                    console.log('Response text:', responseText);
                    
                    let data = {};
                    const contentType = response.headers.get('content-type');
                    
                    // Parse the response data
                    if (contentType && contentType.includes('application/json')) {
                        try {
                            data = JSON.parse(responseText);
                        } catch (e) {
                            console.error('Error parsing JSON response:', e);
                            throw new Error('Invalid response from server');
                        }
                    }
                    
                    // Handle the response based on status code
                    if (response.ok) {
                        // Successful login
                        if (data.redirect) {
                            console.log('Login successful, redirecting to:', data.redirect);
                            Utils.showAlert('Login successful! Redirecting...', 'success');
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1000);
                        } else {
                            console.warn('No redirect URL provided, defaulting to dashboard');
                            window.location.href = '/hearts-after-god-ministry-site/dashboard/';
                        }
                    } else {
                        // Handle error response
                        console.error('Login failed:', data);
                        const errorMessage = data.message || 
                                          (response.status === 401 ? 'Invalid email or password' : 
                                          'An error occurred. Please try again.');
                        Utils.showAlert(errorMessage, 'error');
                        
                        // Clear password field on error
                        this.passwordInput.value = '';
                        this.passwordInput.focus();
                    }
                    
                } catch (error) {
                    console.error('Login error:', error);
                    Utils.showAlert('Connection error. Please check your internet and try again.', 'error');
                } finally {
                    Utils.showLoading(false);
                }
            }
            
            validateForm() {
                const email = this.emailInput.value.trim();
                const password = this.passwordInput.value.trim();
                
                if (!email) {
                    Utils.showAlert('Please enter your email address', 'error');
                    this.emailInput.focus();
                    return false;
                }
                
                if (!Utils.validateEmail(email)) {
                    Utils.showAlert('Please enter a valid email address', 'error');
                    this.emailInput.focus();
                    return false;
                }
                
                if (!password) {
                    Utils.showAlert('Please enter your password', 'error');
                    this.passwordInput.focus();
                    return false;
                }
                
                return true;
            }
            
            validateEmail() {
                const email = this.emailInput.value.trim();
                if (email && !Utils.validateEmail(email)) {
                    this.emailInput.style.borderColor = '#ef4444';
                    Utils.showAlert('Please enter a valid email address', 'error');
                } else if (email) {
                    this.emailInput.style.borderColor = '#10b981';
                } else {
                    this.emailInput.style.borderColor = '#d1d5db';
                }
            }
            
            togglePasswordVisibility() {
                const type = this.passwordInput.type === 'password' ? 'text' : 'password';
                this.passwordInput.type = type;
                
                const eyeIcon = document.getElementById('eyeIcon');
                if (type === 'text') {
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                    `;
                } else {
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    `;
                }
            }
        }

        // Password reset modal
        class PasswordResetModal {
            constructor() {
                this.modal = document.getElementById('resetModal');
                this.form = document.getElementById('resetForm');
                this.emailInput = document.getElementById('resetEmail');
                this.init();
            }
            
            init() {
                document.getElementById('forgotPassword').addEventListener('click', (e) => {
                    e.preventDefault();
                    this.show();
                });
                
                document.getElementById('cancelReset').addEventListener('click', () => {
                    this.hide();
                });
                
                this.modal.addEventListener('click', (e) => {
                    if (e.target === this.modal) this.hide();
                });
                
                this.form.addEventListener('submit', this.handleSubmit.bind(this));
            }
            
            show() {
                this.modal.classList.remove('hidden');
                setTimeout(() => this.emailInput.focus(), 100);
            }
            
            hide() {
                this.modal.classList.add('hidden');
                this.form.reset();
            }
            
            handleSubmit(e) {
                e.preventDefault();
                
                const email = this.emailInput.value.trim();
                
                if (!email) {
                    Utils.showAlert('Please enter your email address', 'error');
                    this.emailInput.focus();
                    return;
                }
                
                if (!Utils.validateEmail(email)) {
                    Utils.showAlert('Please enter a valid email address', 'error');
                    this.emailInput.focus();
                    return;
                }
                
                // Simulate sending reset email
                Utils.showAlert('Password reset instructions sent to your email!', 'success');
                this.hide();
            }
        }

        // Initialize everything when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = new LoginForm();
            const resetModal = new PasswordResetModal();
            
            console.log('Hearts After God Ministry Portal - Login System Ready');
        });

        // Handle browser back/forward
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                Utils.showLoading(false);
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Escape to close modal
            if (e.key === 'Escape') {
                document.getElementById('resetModal').classList.add('hidden');
            }
            
            // Ctrl/Cmd + Enter to submit form
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>