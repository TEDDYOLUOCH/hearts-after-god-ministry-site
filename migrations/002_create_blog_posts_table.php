<?php
/**
 * Migration: Create blog_posts table
 */

class CreateBlogPostsTable {
    public function up($pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS blog_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            excerpt TEXT,
            content LONGTEXT NOT NULL,
            featured_image VARCHAR(255),
            status ENUM('draft', 'published') DEFAULT 'draft',
            author_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            published_at TIMESTAMP NULL,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $pdo->exec($sql);
        
        // Add indexes
        $pdo->exec("CREATE INDEX idx_blog_posts_slug ON blog_posts(slug)");
        $pdo->exec("CREATE INDEX idx_blog_posts_status ON blog_posts(status)");
        $pdo->exec("CREATE INDEX idx_blog_posts_author ON blog_posts(author_id)");
    }
    
    public function down($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS blog_posts");
    }
}

// Run migration if executed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = getDbConnection();
    
    $migration = new CreateBlogPostsTable();
    $migration->up($pdo);
    
    echo "Migration completed successfully.\n";
}
