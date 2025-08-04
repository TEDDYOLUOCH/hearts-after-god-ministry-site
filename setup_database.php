<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'hearts_after_god_db',
    'charset' => 'utf8mb4'
];

// Create connection without selecting a database first
$dsn = "mysql:host={$config['host']};charset={$config['charset']}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Connect to MySQL server
    $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$config['name']}`");
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('admin', 'author', 'editor') NOT NULL DEFAULT 'author',
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Create blog_posts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `blog_posts` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `title` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL,
        `content` LONGTEXT NOT NULL,
        `excerpt` TEXT DEFAULT NULL,
        `featured_image` VARCHAR(255) DEFAULT NULL,
        `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
        `author_id` INT NOT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        `published_at` DATETIME DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`),
        KEY `author_id` (`author_id`),
        KEY `status` (`status`),
        CONSTRAINT `fk_blog_posts_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Create categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `categories` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(100) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Create blog_post_categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `blog_post_categories` (
        `post_id` INT NOT NULL,
        `category_id` INT NOT NULL,
        PRIMARY KEY (`post_id`, `category_id`),
        KEY `category_id` (`category_id`),
        CONSTRAINT `fk_blog_post_categories_post` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_blog_post_categories_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Create admin user if not exists
    $stmt = $pdo->prepare("SELECT id FROM `users` WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    
    if (!$stmt->fetch()) {
        $password = password_hash('password', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO `users` (name, email, password, role) VALUES (?, ?, ?, 'admin')")
           ->execute(['Admin', 'admin@example.com', $password]);
    }
    
    echo "<h1>Database setup completed successfully!</h1>";
    echo "<p>You can now <a href='/hearts-after-god-ministry-site/backend/users/login.php'>login</a> with:</p>";
    echo "<ul>";
    echo "<li>Email: admin@example.com</li>";
    echo "<li>Password: password</li>";
    echo "</ul>";
    echo "<p>Make sure to change the password after logging in.</p>";
    
} catch (PDOException $e) {
    die("<h1>Database Setup Error</h1>" . 
        "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>" .
        "<p>File: " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>");
}
