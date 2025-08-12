<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers to prevent caching
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once __DIR__ . '/../../../config/database.php';

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

try {
    // Get database connection
    $db = getDbConnection();
    
    // Check if database connection is successful
    if (!$db) {
        throw new Exception('Failed to connect to database');
    }
    
    // Initialize stats array with debug info
    $stats = [
        'totalUsers' => 0,
        'activeEvents' => 0,
        'totalBlogs' => 0,
        'totalGallery' => 0,
        'totalMinistries' => 0,
        'totalSermons' => 0,
        'upcomingEvents' => 0,
        'debug' => [
            'server_time' => date('Y-m-d H:i:s'),
            'request_time' => $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true),
            'database' => DB_NAME,
            'tables' => []
        ]
    ];
    
    // Get list of all tables for debugging
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $stats['debug']['tables'][$table] = (int)$db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    }
    
    // Get counts from each table in parallel using the helper function
    $stats['totalUsers'] = getTableCount($db, 'users');
    $stats['activeEvents'] = getTableCount($db, 'events', "status = 'published'");
    $stats['totalBlogs'] = getTableCount($db, 'blog_posts');
    $stats['totalGallery'] = getTableCount($db, 'gallery');
    $stats['totalMinistries'] = getTableCount($db, 'ministries');
    $stats['totalSermons'] = getTableCount($db, 'sermons');
    
    // Get upcoming events count
    $stats['upcomingEvents'] = getTableCount(
        $db, 
        'events', 
        "event_date >= CURDATE() AND status = 'published'"
    );
    
    // If no upcoming events, ensure it's 0 not null
    $stats['upcomingEvents'] = $stats['upcomingEvents'] ?? 0;
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'data' => $stats
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch statistics: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}