<?php
// Start session at the very beginning
session_start();

// Include main configuration which handles error reporting
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /hearts-after-god-ministry-site/dashboard/');
    exit();
}

$error = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } else {
            $db = getDbConnection();
            
            // Check if username or email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Username or email already exists.';
            } else {
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (username, email, password, role, is_active, created_at) VALUES (?, ?, ?, 'user', 1, NOW())");
                
                if ($stmt->execute([$username, $email, $hashed_password])) {
                    // Log the user in after successful registration
                    $user_id = $db->lastInsertId();
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['last_activity'] = time();
                    
                    // Redirect to dashboard
                    header('Location: /hearts-after-god-ministry-site/dashboard/');
                    exit();
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    } catch (Exception $e) {
        error_log('Registration error: ' . $e->getMessage());
        $error = 'An error occurred during registration. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Hearts After God Ministry</title>
    <link href="/hearts-after-god-ministry-site/dist/output.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="description" content="Create a new account for Hearts After God Ministry">
    <meta name="theme-color" content="#0ea5e9">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">Create Account</h1>
                    <p class="text-gray-600 mt-2">Join Hearts After God Ministry</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   value="<?php echo htmlspecialchars($username); ?>"
                                   required
                                   minlength="3"
                                   maxlength="30"
                                   pattern="[A-Za-z0-9_]{3,30}"
                                   title="Username must be 3-30 characters (letters, numbers, underscores)"
                                   class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-4">
                        </div>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($email); ?>"
                                   required
                                   class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-4">
                        </div>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required
                                   minlength="8"
                                   class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-4"
                                   placeholder="At least 8 characters">
                        </div>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required
                                   minlength="8"
                                   class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-4">
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <input id="terms" 
                               name="terms" 
                               type="checkbox" 
                               required
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="terms" class="ml-2 block text-sm text-gray-700">
                            I agree to the <a href="/hearts-after-god-ministry-site/terms" class="text-blue-600 hover:underline">Terms of Service</a> and <a href="/hearts-after-god-ministry-site/privacy" class="text-blue-600 hover:underline">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            Create Account
                        </button>
                    </div>
                </form>
                
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Or sign in with</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <div>
                            <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fab fa-google text-red-500"></i>
                            </a>
                        </div>
                        <div>
                            <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fab fa-facebook text-blue-600"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 rounded-b-xl">
                <p class="text-sm text-center text-gray-600">
                    Already have an account? 
                    <a href="/hearts-after-god-ministry-site/backend/users/login.php" class="font-medium text-blue-600 hover:text-blue-500 hover:underline">
                        Sign in
                    </a>
                </p>
            </div>
        </div>
        
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> Hearts After God Ministry. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
