<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once __DIR__ . '/config/db.php';

// Default admin credentials (change these in production!)
$adminEmail = 'admin@example.com';
$adminPassword = 'Admin@123'; // This will be hashed
$adminName = 'Administrator';

// Function to check if admin user exists
function adminExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
    $stmt->execute([$email]);
    return (bool)$stmt->fetch();
}

try {
    // Get database connection
    $pdo = getDbConnection();
    
    // Check if admin already exists
    if (adminExists($pdo, $adminEmail)) {
        die('<div style="max-width: 800px; margin: 50px auto; padding: 20px; background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 5px;">
                <h2 style="color: #2e7d32; margin-top: 0;">Admin User Already Exists</h2>
                <p>An admin user with this email already exists in the database.</p>
                <p><a href="/hearts-after-god-ministry-site/backend/users/login.php" style="display: inline-block; background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 15px;">Go to Login Page</a></p>
              </div>');
    }
    
    // Hash the password
    $hashedPassword = password_hash($adminPassword, PASSWORD_BCRYPT);
    
    // Create admin user
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role, email_verified_at, created_at, updated_at)
        VALUES (?, ?, ?, 'admin', NOW(), NOW(), NOW())
    ");
    
    $stmt->execute([$adminName, $adminEmail, $hashedPassword]);
    
    echo '<div style="max-width: 800px; margin: 50px auto; padding: 20px; background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 5px;">
            <h2 style="color: #2e7d32; margin-top: 0;">Admin User Created Successfully</h2>
            <div style="background: #ffffff; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <p><strong>Email:</strong> ' . htmlspecialchars($adminEmail) . '</p>
                <p><strong>Password:</strong> ' . htmlspecialchars($adminPassword) . '</p>
                <p><strong>Role:</strong> Admin</p>
                <p style="color: #d32f2f; font-weight: bold;">IMPORTANT: Change this password immediately after first login!</p>
            </div>
            <p><a href="/hearts-after-god-ministry-site/backend/users/login.php" style="display: inline-block; background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 15px;">Go to Login Page</a></p>
            <p><a href="/hearts-after-god-ministry-site/dashboard/" style="display: inline-block; color: #1976d2; text-decoration: none; margin-top: 10px;">Go to Dashboard</a></p>
          </div>';
    
} catch (Exception $e) {
    $errorMessage = '<div style="max-width: 800px; margin: 50px auto; padding: 20px; background: #ffebee; border: 1px solid #ffcdd2; border-radius: 5px;">
            <h2 style="color: #c62828; margin-top: 0;">Error Creating Admin User</h2>
            <div style="background: #ffffff; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                <p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ' on line ' . $e->getLine() . '</p>';
                
    if (strpos($e->getMessage(), 'users_email_unique') !== false) {
        $errorMessage .= '<p>An account with this email already exists. Please use a different email address or reset the password for the existing account.</p>';
    }
    
    $errorMessage .= '  </div>
            <p><a href="javascript:history.back()" style="display: inline-block; background: #f44336; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 15px;">Go Back</a></p>
          </div>';
          
    die($errorMessage);
}
?>
