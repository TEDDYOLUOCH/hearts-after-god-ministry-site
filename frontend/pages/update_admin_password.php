<?php
// Include configuration and hashing functions
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/hash.php';

// Admin credentials
$email = 'admin@example.com';
$password = 'admin123';

try {
    // Hash the password
    $hashedPassword = hash_password($password);
    
    if ($hashedPassword === false) {
        throw new Exception('Failed to hash password');
    }
    
    // Get database configuration
    $dbConfig = require __DIR__ . '/config/db.php';
    
    // Create database connection
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
    $db = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $dbConfig['options']);
    
    // Check if admin exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        // Update existing admin
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
        $result = $stmt->execute([$hashedPassword, $email]);
        $message = "Admin password updated successfully!";
    } else {
        // Create new admin
        $username = 'admin';
        $stmt = $db->prepare("INSERT INTO users (username, email, password, role, is_active) VALUES (?, ?, ?, 'admin', 1)");
        $result = $stmt->execute([$username, $email, $hashedPassword]);
        $message = "Admin user created successfully!";
    }
    
    if ($result) {
        echo "✅ $message\n";
        echo "Email: $email\n";
        echo "Password: $password\n";
        echo "Hashed Password: $hashedPassword\n";
    } else {
        echo "❌ Failed to update admin user.\n";
    }
    
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}

echo "\nYou can now log in with:\n";
echo "Email: $email\n";
echo "Password: $password\n";
