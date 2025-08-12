<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for Server-Sent Events
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable buffering for Nginx
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once __DIR__ . '/../../../config/database.php';

// Function to send SSE message
function sendSSE($event, $data) {
    echo "event: $event\n";
    echo 'data: ' . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

// Set time limit to 0 to prevent timeout
set_time_limit(0);

// Store the last event ID
$lastEventId = isset($_SERVER['HTTP_LAST_EVENT_ID']) ? intval($_SERVER['HTTP_LAST_EVENT_ID']) : 0;

// Keep the connection alive
while (true) {
    try {
        // Get fresh stats
        $db = getDbConnection();
        if (!$db) {
            throw new Exception('Failed to connect to database');
        }

        // Get counts
        $stats = [
            'totalUsers' => getTableCount($db, 'users'),
            'activeEvents' => getTableCount($db, 'events', "status = 'published'"),
            'totalBlogs' => getTableCount($db, 'blog_posts'),
            'totalGallery' => getTableCount($db, 'gallery'),
            'totalMinistries' => getTableCount($db, 'ministries'),
            'totalSermons' => getTableCount($db, 'sermons'),
            'upcomingEvents' => getTableCount($db, 'events', "event_date >= CURDATE() AND status = 'published'"),
            'timestamp' => time()
        ];

        // Send the update
        sendSSE('stats_update', [
            'data' => $stats,
            'id' => time()
        ]);

        // Wait for 5 seconds before next update
        sleep(5);

    } catch (Exception $e) {
        // Send error event
        sendSSE('error', [
            'message' => $e->getMessage(),
            'code' => $e->getCode()
        ]);
        
        // Wait a bit before retrying
        sleep(5);
    }
}

// Function to safely get count from a table with error handling
function getTableCount($db, $table, $where = '') {
    try {
        $query = "SELECT COUNT(*) as count FROM `$table`";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        $result = $db->query($query);
        if ($result) {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return (int)($row['count'] ?? 0);
        }
    } catch (Exception $e) {
        error_log("Error counting $table: " . $e->getMessage());
    }
    return 0;
}
?>
