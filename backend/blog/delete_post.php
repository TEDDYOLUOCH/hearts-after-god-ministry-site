<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the document root - adjust this if your directory structure is different
$docRoot = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site';
$logDir = $docRoot . '/logs';

// Create logs directory if it doesn't exist
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

// Set error log file
ini_set('log_errors', 1);
$errorLog = $logDir . '/delete_errors.log';
error_log('[' . date('Y-m-d H:i:s') . '] Delete script started' . "\n", 3, $errorLog);

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

try {
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        throw new Exception('Unauthorized access', 403);
    }

    // Get post ID from query parameters
    $postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($postId <= 0) {
        throw new Exception('Invalid post ID', 400);
    }

    // Include database configuration
    $dbConfig = $docRoot . '/config/db.php';
    if (!file_exists($dbConfig)) {
        throw new Exception('Database configuration not found at: ' . $dbConfig, 500);
    }
    
    // Include the database configuration
    require_once $dbConfig;

    // Check if database connection was established
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Failed to initialize database connection', 500);
    }

    // Check if table exists
    try {
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'blog_posts'");
        if ($tableCheck->rowCount() == 0) {
            throw new Exception('Blog posts table does not exist', 500);
        }
    } catch (PDOException $e) {
        throw new Exception('Database error checking for blog_posts table: ' . $e->getMessage(), 500);
    }

    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        // Check if post exists
        $stmt = $pdo->prepare("SELECT id FROM blog_posts WHERE id = ?");
        $stmt->execute([$postId]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Post not found', 404);
        }
        
        // Delete the post
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$postId]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('No post was deleted. The post may have already been deleted.', 404);
        }
        
        $pdo->commit();
        sendJsonResponse(true, 'Post deleted successfully');
        
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
    
} catch (PDOException $e) {
    logError('Database Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    sendJsonResponse(false, 'A database error occurred', 500);
} catch (Exception $e) {
    logError('Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    sendJsonResponse(false, $e->getMessage(), $code);
} finally {
    // Clean any output buffer
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
}
