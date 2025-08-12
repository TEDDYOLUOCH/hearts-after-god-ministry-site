<?php
// Database configuration
$host = 'localhost';
$dbname = 'hearts_after_god_db';
$username = 'root';
$password = '';

// Admin credentials
$adminEmail = 'oluochteddyochieng@gmail.com';
$adminPassword = 'teddy0722853859';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "Connected to database successfully.\n";
    
    // Check if users table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Create users table if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `email` VARCHAR(100) NOT NULL UNIQUE,
                `password` VARCHAR(255) NOT NULL,
                `role` ENUM('admin', 'pastor', 'leader', 'member') NOT NULL DEFAULT 'member',
                `remember_token` VARCHAR(100) DEFAULT NULL,
                `last_login` DATETIME DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "Created 'users' table.\n";
    }
    
    // Hash the password
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Update existing admin user
        $stmt = $pdo->prepare("
            UPDATE users 
            SET password = ?, role = 'admin', name = 'Teddy Ochieng', updated_at = NOW() 
            WHERE email = ?
        ");
        $stmt->execute([$hashedPassword, $adminEmail]);
        echo "Updated admin user with email: $adminEmail\n";
    } else {
        // Insert new admin user
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role) 
            VALUES (?, ?, ?, 'admin')
        ");
        $stmt->execute(['Teddy Ochieng', $adminEmail, $hashedPassword]);
        echo "Created new admin user with email: $adminEmail\n";
    }
    
    echo "Operation completed successfully!\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}

echo "\nYou can now log in with:\n";
echo "Email: $adminEmail\n";
echo "Password: $adminPassword\n\n";

// Security note: This file should be deleted after use
if (file_exists(__FILE__)) {
    echo "IMPORTANT: For security reasons, please delete this file (" . basename(__FILE__) . ") after use.\n";
}
?>
