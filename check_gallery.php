<?php
require_once __DIR__ . '/config/db.php';

try {
    // Check if gallery table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'gallery'");
    if ($stmt->rowCount() === 0) {
        die("Error: Gallery table does not exist. Please run the database migration script.");
    }
    
    // Check table structure
    $stmt = $pdo->query("DESCRIBE gallery");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Gallery table columns: " . implode(', ', $columns) . "\n\n";
    
    // Count rows in gallery table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery");
    $count = $stmt->fetch()['count'];
    echo "Number of images in gallery: $count\n\n";
    
    // Show first few records if any exist
    if ($count > 0) {
        $stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC LIMIT 5");
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Sample gallery entries:\n";
        print_r($images);
    }
    
    // Check uploads directory
    $uploadDir = __DIR__ . '/uploads/gallery/';
    echo "\nUpload directory exists: " . (file_exists($uploadDir) ? 'Yes' : 'No') . "\n";
    if (file_exists($uploadDir)) {
        $files = scandir($uploadDir);
        $imageFiles = array_filter($files, function($file) {
            return !in_array($file, ['.', '..']);
        });
        echo "Number of files in uploads/gallery: " . count($imageFiles) . "\n";
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
