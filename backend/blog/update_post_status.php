<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get and validate input
$data = json_decode(file_get_contents('php://input'), true);
$postId = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
$status = in_array(strtolower($data['status'] ?? ''), ['published', 'draft']) ? strtolower($data['status']) : null;

if (!$postId || !$status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

try {
    // Update the post status
    $stmt = $pdo->prepare("UPDATE blog_posts SET status = ?, updated_at = NOW() WHERE id = ?");
    $success = $stmt->execute([$status, $postId]);
    
    if ($success && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Post status updated successfully',
            'status' => $status,
            'status_label' => ucfirst($status)
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Post not found or no changes made']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
