<?php
/**
 * Database Migration Script
 * 
 * This script creates all necessary database tables for the application.
 * Run this script once after setting up the database connection.
 */

// Load environment variables
require_once __DIR__ . '/../config/db.php';

// Create database connection
try {
    $db = getDbConnection();
    
    // Enable foreign key constraints
    $db->exec('SET FOREIGN_KEY_CHECKS = 0');
    
    // Create users table
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'editor', 'user') DEFAULT 'user',
            reset_token VARCHAR(100) DEFAULT NULL,
            reset_token_expires_at DATETIME DEFAULT NULL,
            last_login DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    
    ");
    
    // Create blog tables
    $db->exec("
        CREATE TABLE IF NOT EXISTS blog_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            excerpt TEXT,
            content LONGTEXT NOT NULL,
            featured_image VARCHAR(255),
            author_id INT,
            status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
            published_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    
    ");
    
    $db->exec("
        CREATE TABLE IF NOT EXISTS blog_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    
    ");
    
    $db->exec("
        CREATE TABLE IF NOT EXISTS blog_post_categories (
            post_id INT,
            category_id INT,
            PRIMARY KEY (post_id, category_id),
            FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    
    ");
    
    // Create events tables
    $db->exec("
        CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            start_datetime DATETIME NOT NULL,
            end_datetime DATETIME,
            location VARCHAR(255),
            image_url VARCHAR(255),
            is_featured BOOLEAN DEFAULT FALSE,
            registration_url VARCHAR(255),
            status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    
    ");
    
    $db->exec("
        CREATE TABLE IF NOT EXISTS event_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    
    ");
    
    $db->exec("
        CREATE TABLE IF NOT EXISTS event_category_mapping (
            event_id INT,
            category_id INT,
            PRIMARY KEY (event_id, category_id),
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES event_categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    
    ");
    
    // Create other essential tables
    $db->exec("
        CREATE TABLE IF NOT EXISTS sermons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            preacher VARCHAR(255) NOT NULL,
            bible_reference VARCHAR(100),
            sermon_date DATE NOT NULL,
            audio_url VARCHAR(255),
            video_url VARCHAR(255),
            description TEXT,
            thumbnail_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    
    ");
    
    $db->exec("
        CREATE TABLE IF NOT EXISTS gallery (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            image_path VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    
    ");
    
    $db->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(255) NOT NULL UNIQUE,
            setting_value LONGTEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    
    ");
    
    // Create default admin user if not exists
    $stmt = $db->query("SELECT id FROM users WHERE email = 'admin@example.com'");
    if ($stmt->rowCount() === 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $db->exec("
            INSERT INTO users (username, email, password, role, is_active) 
            VALUES ('admin', 'admin@example.com', '$password', 'admin', 1)
        
        ");
        echo "Created default admin user (username: admin, password: admin123)\n";
    }
    
    // Create upload directories
    $directories = [
        __DIR__ . '/../public/uploads',
        __DIR__ . '/../public/uploads/blog',
        __DIR__ . '/../public/uploads/events',
        __DIR__ . '/../public/uploads/sermons',
        __DIR__ . '/../public/uploads/gallery'
    ];
    
    foreach ($directories as $directory) {
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
            echo "Created directory: $directory\n";
        }
    }
    
    // Add .htaccess to prevent directory listing
    $htaccessContent = "Options -Indexes\n<FilesMatch '^\\.(env|json|config|lock|gitignore|gitattributes|editorconfig|env\\.example|env\\.production|env\\.local|env\\.development|env\\.testing|phpunit\\.xml|phpunit\\.xml\\.dist|phpcs\\.xml|phpcs\\.xml\\.dist|phpmd\\.xml|phpmd\\.xml\\.dist|travis\\.yml|phpstan\\.neon|phpstan\\.neon\\.dist|phpstan-baseline\\.neon|phpstan-baseline\\.neon\\.dist)'>
    Order allow,deny
    Deny from all
</FilesMatch>";
    
    file_put_contents(__DIR__ . '/../public/uploads/.htaccess', $htaccessContent);
    
    echo "Database migration completed successfully!\n";
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
