<?php
// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once __DIR__ . '/../../../config/database.php';

// Set timezone to Nairobi
date_default_timezone_set('Africa/Nairobi');

// Get current date and time
$currentDate = date('Y-m-d H:i:s');

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    // Get database connection
    $db = getDbConnection();
    if (!$db) {
        throw new Exception('Could not connect to the database');
    }

    // Build the query to get upcoming events
    $query = "SELECT 
                id, 
                title, 
                description, 
                location,
                start_datetime,
                end_datetime,
                featured_image as image_url,
                status,
                created_at
              FROM events 
              WHERE status = 'published'
              AND start_datetime >= NOW()
              ORDER BY start_datetime ASC 
              LIMIT 5";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $events = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $start = new DateTime($row['start_datetime']);
        $end = new DateTime($row['end_datetime']);
        
        $events[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'date' => $start->format('Y-m-d'),
            'time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'location' => $row['location'],
            'type' => 'event', // Default type
            'image_url' => $row['image_url'] ?? null,
            'created_at' => $row['created_at'],
            'is_recurring' => false, // You can add this from the row if needed
            'status' => $row['status']
        ];
    }
    
    // Return success response
    sendJsonResponse([
        'status' => 'success',
        'data' => $events,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    // Log the error
    error_log('Upcoming Events API Error (PDO): ' . $e->getMessage());
    
    // Return error response
    sendJsonResponse([
        'status' => 'error',
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ], 500);
    
} catch (Exception $e) {
    // Log the error
    error_log('Upcoming Events API Error: ' . $e->getMessage());
    
    // Return error response
    sendJsonResponse([
        'status' => 'error',
        'message' => 'An error occurred while fetching upcoming events',
        'error' => $e->getMessage()
    ], 500);
}
?>
