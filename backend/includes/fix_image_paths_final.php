<?php
require_once __DIR__ . '/config/db.php';

header('Content-Type: text/plain');

try {
    // Get all gallery entries
    $stmt = $pdo->query("SELECT id, image_path FROM gallery");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Updating image paths to use absolute URLs...\n\n";
    
    foreach ($images as $image) {
        $oldPath = $image['image_path'];
        
        // If path doesn't start with http, make it an absolute URL
        if (!preg_match('/^https?:\/\//i', $oldPath)) {
            // Remove leading slash if present
            $cleanPath = ltrim($oldPath, '/');
            // Create absolute URL
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $newPath = "$protocol://$host/$cleanPath";
            
            // Update the database
            $updateStmt = $pdo->prepare("UPDATE gallery SET image_path = ? WHERE id = ?");
            $updateStmt->execute([$newPath, $image['id']]);
            
            echo "Updated ID {$image['id']}:\n";
            echo "  Old path: $oldPath\n";
            echo "  New path: $newPath\n\n";
        } else {
            echo "Skipping ID {$image['id']} (already absolute URL): $oldPath\n\n";
        }
    }
    
    echo "\nAll image paths have been updated successfully!\n";
    echo "Please refresh your admin dashboard to see the changes.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
