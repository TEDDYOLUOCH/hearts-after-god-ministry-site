<?php
require_once __DIR__ . '/config/db.php';

try {
    // Get all gallery entries
    $stmt = $pdo->query("SELECT id, image_path FROM gallery");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($images as $image) {
        $oldPath = $image['image_path'];
        // Extract just the filename from the path
        $filename = basename($oldPath);
        // Create the correct path
        $newPath = '/hearts-after-god-ministry-site/uploads/gallery/' . $filename;
        
        // Update the database with the correct path
        $updateStmt = $pdo->prepare("UPDATE gallery SET image_path = ? WHERE id = ?");
        $updateStmt->execute([$newPath, $image['id']]);
        
        echo "Updated ID {$image['id']}: {$oldPath} -> {$newPath}<br>";
    }
    
    echo "<br>All image paths have been updated successfully!";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
