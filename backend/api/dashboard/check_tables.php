<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once __DIR__ . '/../../../config/database.php';

try {
    // Get database connection
    $db = getDbConnection();
    
    // Get list of all tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    $results = [];
    
    // Check each table
    foreach ($tables as $table) {
        $count = $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $results[$table] = [
            'count' => (int)$count,
            'columns' => []
        ];
        
        // Get column information
        $columns = $db->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            $results[$table]['columns'][] = $col['Field'] . ' (' . $col['Type'] . ')';
        }
    }
    
    // Output results
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'tables' => $results,
        'debug' => [
            'database' => DB_NAME,
            'host' => DB_HOST,
            'user' => DB_USER,
            'time' => date('Y-m-d H:i:s')
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
