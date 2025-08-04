<?php
header('Content-Type: application/json');
session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

// Get image ID from query parameters
$imageId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$imageId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // First, get the image path
    $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$image) {
        throw new Exception('Image not found');
    }
    
    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->execute([$imageId]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('No image was deleted');
    }
    
    // Delete the file
    $filePath = __DIR__ . '/../../' . ltrim($image['image_path'], '/');
    if (file_exists($filePath) && is_file($filePath)) {
        unlink($filePath);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Image deleted successfully'
    ]);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete image: ' . $e->getMessage()
    ]);
}
