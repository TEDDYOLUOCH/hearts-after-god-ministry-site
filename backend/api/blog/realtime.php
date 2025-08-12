<?php
/**
 * Real-time Blog Updates API
 * Provides real-time updates for blog management
 */

// Set headers for SSE (Server-Sent Events)
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable buffering for Nginx

// Include required files
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/BaseApiHandler.php';

// Set time limit to prevent timeouts
set_time_limit(0);
ob_implicit_flush(true);
ob_end_flush();

// Function to send SSE message
function sendSSE($event, $data) {
    echo "event: {$event}\n";
    echo 'data: ' . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

try {
    // Get database connection
    $pdo = getDbConnection();
    
    // Get last event ID from client
    $lastEventId = isset($_SERVER['HTTP_LAST_EVENT_ID']) ? intval($_SERVER['HTTP_LAST_EVENT_ID']) : 0;
    
    // Initial data
    $initialData = [
        'type' => 'init',
        'timestamp' => time(),
        'data' => []
    ];
    
    // Send initial data
    sendSSE('init', $initialData);
    
    // Keep connection open and check for updates
    while (true) {
        // Check for new posts
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM blog_posts WHERE created_at > FROM_UNIXTIME(?)");
        $stmt->execute([$lastEventId]);
        $newPosts = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Check for updated posts
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM blog_posts WHERE updated_at > FROM_UNIXTIME(?)");
        $stmt->execute([$lastEventId]);
        $updatedPosts = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // If there are updates, send them
        if ($newPosts > 0 || $updatedPosts > 0) {
            $updateData = [
                'type' => 'update',
                'timestamp' => time(),
                'data' => [
                    'new_posts' => $newPosts,
                    'updated_posts' => $updatedPosts
                ]
            ];
            
            sendSSE('update', $updateData);
            $lastEventId = time(); // Update last event ID
        }
        
        // Check if client is still connected
        if (connection_aborted()) {
            break;
        }
        
        // Wait before next check (5 seconds)
        sleep(5);
    }
    
} catch (Exception $e) {
    // Log error
    error_log('SSE Error: ' . $e->getMessage());
    
    // Send error to client
    sendSSE('error', [
        'message' => 'An error occurred',
        'code' => $e->getCode()
    ]);
}
