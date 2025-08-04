<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid image ID']);
    exit;
}

$image_id = (int)$_GET['id'];

try {
    // Begin transaction
    $pdo->beginTransaction();
    
    // First, get the image record to find the file
    $stmt = $pdo->prepare('SELECT * FROM gallery WHERE id = ?');
    $stmt->execute([$image_id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$image) {
        throw new Exception('Image not found');
    }
    
    // Delete the database record
    $stmt = $pdo->prepare('DELETE FROM gallery WHERE id = ?');
    $stmt->execute([$image_id]);
    
    // Try to find and delete the associated file
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site/uploads/gallery/';
    $deleted_file = false;
    
    // Check for files that might match this ID
    if (is_dir($upload_dir)) {
        $files = array_diff(scandir($upload_dir), ['.', '..']);
        foreach ($files as $file) {
            if (strpos($file, 'img_' . $image_id . '_') === 0 || 
                strpos($file, (string)$image_id . '.') !== false) {
                $file_path = $upload_dir . $file;
                if (file_exists($file_path)) {
                    unlink($file_path);
                    $deleted_file = true;
                }
                break;
            }
        }
    }
    
    // Commit the transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Image deleted successfully' . (!$deleted_file ? ' (file not found)' : '')
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to delete image: ' . $e->getMessage()
    ]);
}
