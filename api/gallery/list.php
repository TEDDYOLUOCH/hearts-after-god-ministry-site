<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

try {
    // Include database configuration
    require_once __DIR__ . '/../../config/db.php';
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verify user is logged in (if required)
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $pdo = getDbConnection();
    
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not properly initialized');
    }
    
    // Get the base URL for images including the subdirectory
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/hearts-after-god-ministry-site';
    
    // First, get all gallery items from the database
    $stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get list of all image files in the uploads directory
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site/uploads/gallery/';
    $image_files = [];
    
    if (is_dir($upload_dir)) {
        $files = array_diff(scandir($upload_dir), ['.', '..']);
        foreach ($files as $file) {
            if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                $image_files[] = $file;
            }
        }
    }
    
    // Match database entries with image files
    foreach ($images as &$image) {
        $image_id = $image['id'];
        $matching_file = '';
        
        // Try to find a file that starts with the ID
        foreach ($image_files as $file) {
            if (strpos($file, 'img_' . $image_id . '_') === 0 || 
                strpos($file, (string)$image_id . '.') !== false) {
                $matching_file = $file;
                break;
            }
        }
        
        // Add the full URL to the image
        $image['image_url'] = !empty($matching_file) 
            ? $baseUrl . '/uploads/gallery/' . $matching_file 
            : '';
            
        // Add thumbnail URL (same as image_url for now)
        $image['thumbnail_url'] = $image['image_url'];
        
        // Add file info
        $image['type'] = 'image';
        $image['size'] = !empty($matching_file) ? filesize($upload_dir . $matching_file) : 0;
    }
    
    // Log successful response for debugging
    error_log('Gallery list API success - ' . count($images) . ' items found');
    
    // Return the response
    echo json_encode([
        'success' => true,
        'data' => $images
    ]);
    
} catch (PDOException $e) {
    error_log('Gallery list API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('Gallery list API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    echo str_replace('\/','/',$json);
} catch (Exception $e) {
    error_log('Gallery list API error: ' . $e->getMessage());
    http_response_code(500);
    $json = json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    echo str_replace('\/','/',$json);
}
