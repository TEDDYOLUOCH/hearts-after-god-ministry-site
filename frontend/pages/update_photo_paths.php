<?php
// Include database configuration
require_once __DIR__ . '/config/db.php';

// Get database connection
$pdo = getDbConnection();

try {
    // Get all events with photos
    $stmt = $pdo->query("SELECT id, photo FROM events WHERE photo IS NOT NULL AND photo != ''");
    $events = $stmt->fetchAll();
    
    $updated = 0;
    
    foreach ($events as $event) {
        $oldPath = $event['photo'];
        
        // Skip if already using the new path format
        if (strpos($oldPath, '/hearts-after-god-ministry-site/') !== false) {
            continue;
        }
        
        // Convert old path to new format
        $filename = basename($oldPath);
        $newPath = '/hearts-after-god-ministry-site/uploads/' . $filename;
        
        // Update the database
        $updateStmt = $pdo->prepare("UPDATE events SET photo = ? WHERE id = ?");
        $updateStmt->execute([$newPath, $event['id']]);
        
        $updated++;
        echo "Updated: ID {$event['id']} - $oldPath => $newPath<br>\n";
    }
    
    echo "\nTotal records updated: $updated\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
