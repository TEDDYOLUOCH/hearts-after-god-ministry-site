<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');

// Include main configuration
require_once __DIR__ . '/../../config/config.php';

try {
    // Create database connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    $admin = $stmt->fetch();

    if (!$admin) {
        // Create admin user if not exists
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, role, is_active) VALUES (?, ?, ?, ?, 'admin', 1)");
        $stmt->execute(['Administrator', 'admin', 'admin@example.com', $hashedPassword]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Admin user created successfully',
            'email' => 'admin@example.com',
            'password' => 'password123'
        ]);
    } else {
        // Update admin password
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, is_active = 1, role = 'admin' WHERE email = ?");
        $stmt->execute([$hashedPassword, 'admin@example.com']);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Admin password updated successfully',
            'email' => 'admin@example.com',
            'password' => 'password123'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update admin password: ' . $e->getMessage()
    ]);
}
