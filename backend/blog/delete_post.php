<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up error logging
$docRoot = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site';
$logDir = $docRoot . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}
$errorLog = $logDir . '/delete_errors.log';

// Function to log errors
function logError($message) {
    global $errorLog;
    error_log('[' . date('Y-m-d H:i:s') . '] ' . $message . "\n", 3, $errorLog);
}

// Function to send JSON response
function sendJsonResponse($success, $message, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'code' => $code
    ]);
    exit;
}

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    sendJsonResponse(false, 'Unauthorized', 403);
}

// Use POST for deletion
$id = $_POST['id'] ?? '';
if (!$id) {
    sendJsonResponse(false, 'No ID', 400);
}

require_once __DIR__ . '/../../config/db.php';
$db = getDbConnection();

try {
    $stmt = $db->prepare("DELETE FROM blog_posts WHERE id=?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() > 0) {
        sendJsonResponse(true, 'Post deleted successfully');
    } else {
        sendJsonResponse(false, 'Post not found', 404);
    }
} catch (Exception $e) {
    logError('Delete failed: ' . $e->getMessage());
    sendJsonResponse(false, 'Database error', 500);
}

// Example delete AJAX call
fetch('/hearts-after-god-ministry-site/backend/blog/delete_post.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'id=' + postId
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Post deleted, remove from UI
        document.getElementById('post-' + postId).remove();
    } else {
        alert('Error: ' + data.message);
    }
})
.catch(error => {
    console.error('Error:', error);
});
