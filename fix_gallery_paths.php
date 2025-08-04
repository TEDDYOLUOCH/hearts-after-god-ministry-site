<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require_once __DIR__ . '/config/db.php';

try {
    // Get all gallery images
    $stmt = $pdo->query("SELECT id, image_path FROM gallery");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updated = 0;
    
    foreach ($images as $image) {
        $oldPath = $image['image_path'];
        $newPath = $oldPath;
        
        // Fix paths that include full URL
        if (strpos($oldPath, 'http') === 0) {
            // Extract path from URL
            $parsed = parse_url($oldPath);
            $newPath = $parsed['path'] ?? '';
        }
        
        // Ensure path starts with /uploads/gallery/
        if (strpos($newPath, '/uploads/gallery/') !== 0) {
            $newPath = '/uploads/gallery/' . basename($newPath);
        }
        
        // Update if path was changed
        if ($newPath !== $oldPath) {
            $update = $pdo->prepare("UPDATE gallery SET image_path = ? WHERE id = ?");
            $update->execute([$newPath, $image['id']]);
            $updated++;
            echo "Updated ID {$image['id']}: '{$oldPath}' -> '{$newPath}'<br>\n";
        }
    }
    
    echo "\nFixed $updated gallery image paths. <a href='/hearts-after-god-ministry-site/debug_gallery.php'>Check the debug page</a> to verify.";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
