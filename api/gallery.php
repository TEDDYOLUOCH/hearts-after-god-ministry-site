<?php
// Set headers first
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Database connection
    $dbFile = __DIR__ . '/../config/db.php';
    if (!file_exists($dbFile)) {
        throw new Exception('Database config not found');
    }
    
    require_once $dbFile;
    $pdo = getDbConnection();
    
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    // Get gallery items with unique IDs
    $stmt = $pdo->query('SELECT id, title, description, image_path, created_at FROM gallery ORDER BY created_at DESC');
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response
    $response = [
        'success' => true,
        'data' => []
    ];

    $processedIds = [];
    foreach ($images as $image) {
        // Skip if we've already processed this ID
        $imageId = (int)$image['id'];
        if (in_array($imageId, $processedIds, true)) {
            continue;
        }
        $processedIds[] = $imageId;

        // Get the image path and ensure it's clean
        $imagePath = ltrim($image['image_path'] ?? '', '/\\');
        
        // Remove the base path if it exists
        $basePath = '/hearts-after-god-ministry-site';
        if (strpos($imagePath, $basePath) === 0) {
            $imagePath = ltrim(substr($imagePath, strlen($basePath)), '/');
        }
        
        // Skip if image path is empty
        if (empty($imagePath)) {
            error_log("Skipping empty image path for ID: $imageId");
            continue;
        }
        
        // Ensure the path is relative to the site root
        $imagePath = ltrim(str_replace('\\', '/', $imagePath), '/');
        
        // Build the full server path
        $basePath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/hearts-after-god-ministry-site';
        $fullPath = $basePath . '/' . ltrim($imagePath, '/');
        
        // Check if file exists and is readable
        $fileExists = file_exists($fullPath) && is_readable($fullPath);
        
        // Log for debugging
        error_log(sprintf(
            'Image check - ID: %d, Path: %s, Exists: %s, Readable: %s',
            $imageId,
            $fullPath,
            $fileExists ? 'yes' : 'no',
            $fileExists ? (is_readable($fullPath) ? 'yes' : 'no') : 'n/a'
        ));
        
        // Skip if file doesn't exist or isn't readable
        if (!$fileExists) {
            error_log("Skipping missing or unreadable file: " . $fullPath);
            continue;
        }
        
        // Return the path as stored in the database, let the frontend handle the URL construction
        $imageUrl = $imagePath;
        
        $response['data'][] = [
            'id' => $imageId,
            'title' => $image['title'] ?? '',
            'description' => $image['description'] ?? '',
            'image_url' => $imageUrl,
            'created_at' => $image['created_at']
        ];
    }

    // Output JSON
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
    exit;
}