<?php
// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once __DIR__ . '/../../../config/database.php';

try {
    // Get database connection
    $db = getDbConnection();
    
    // Get pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    
    // Get filter parameters
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $month = isset($_GET['month']) ? (int)$_GET['month'] : null;
    $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
    $upcoming = isset($_GET['upcoming']) ? (bool)$_GET['upcoming'] : false;
    
    // Build the base query
    $query = "SELECT 
                e.id, 
                e.title, 
                e.description, 
                e.location,
                e.start_datetime,
                e.end_datetime,
                e.registration_deadline,
                e.max_attendees,
                e.featured_image,
                e.is_recurring,
                e.recurrence_pattern,
                e.recurrence_until,
                e.status,
                e.created_at,
                e.updated_at,
                ec.id as category_id,
                ec.name as category_name,
                ec.color as category_color,
                (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registered_attendees
              FROM events e
              LEFT JOIN event_categories ec ON e.category_id = ec.id
              WHERE e.status = 'published'";
    
    $params = [];
    
    // Add filters
    if ($category) {
        $query .= " AND ec.slug = :category";
        $params[':category'] = $category;
    }
    
    if ($month) {
        $query .= " AND MONTH(e.start_datetime) = :month";
        $params[':month'] = $month;
    }
    
    if ($year) {
        $query .= " AND YEAR(e.start_datetime) = :year";
        $params[':year'] = $year;
    }
    
    if ($upcoming) {
        $query .= " AND e.start_datetime >= NOW()";
    }
    
    // Add ordering and pagination
    $query .= " ORDER BY e.start_datetime ASC
               LIMIT :limit OFFSET :offset";
    
    // Prepare and execute the query
    $stmt = $db->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process events data
    foreach ($events as &$event) {
        // Format dates
        $startDate = new DateTime($event['start_datetime']);
        $endDate = new DateTime($event['end_datetime']);
        
        $event['start_date'] = $startDate->format('Y-m-d');
        $event['start_time'] = $startDate->format('H:i:s');
        $event['end_date'] = $endDate->format('Y-m-d');
        $event['end_time'] = $endDate->format('H:i:s');
        
        // Calculate available spots
        $event['available_spots'] = $event['max_attendees'] > 0 
            ? max(0, $event['max_attendees'] - $event['registered_attendees'])
            : null;
            
        // Add human-readable date formats
        $event['formatted_date'] = $startDate->format('F j, Y');
        $event['formatted_time'] = $startDate->format('g:i A');
        $event['formatted_duration'] = $endDate->diff($startDate)->format('%h hours %i minutes');
    }
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM events e WHERE e.status = 'published'";
    
    if ($category) {
        $countQuery .= " AND EXISTS (SELECT 1 FROM event_categories ec WHERE ec.id = e.category_id AND ec.slug = :category)";
    }
    if ($month) {
        $countQuery .= " AND MONTH(e.start_datetime) = :month";
    }
    if ($year) {
        $countQuery .= " AND YEAR(e.start_datetime) = :year";
    }
    if ($upcoming) {
        $countQuery .= " AND e.start_datetime >= NOW()";
    }
    
    $countStmt = $db->prepare($countQuery);
    
    // Bind parameters for count query
    if ($category) {
        $countStmt->bindValue(':category', $category);
    }
    if ($month) {
        $countStmt->bindValue(':month', $month, PDO::PARAM_INT);
    }
    if ($year) {
        $countStmt->bindValue(':year', $year, PDO::PARAM_INT);
    }
    
    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get available years for filtering
    $yearsStmt = $db->query("SELECT DISTINCT YEAR(start_datetime) as year FROM events WHERE status = 'published' ORDER BY year DESC");
    $years = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get categories for filtering
    $categoriesStmt = $db->query("SELECT id, name, slug, color FROM event_categories WHERE status = 'active' ORDER BY name");
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return the response
    echo json_encode([
        'status' => 'success',
        'data' => [
            'events' => $events,
            'filters' => [
                'years' => $years,
                'categories' => $categories,
                'selected' => [
                    'category' => $category,
                    'month' => $month,
                    'year' => $year,
                    'upcoming' => $upcoming
                ]
            ],
            'pagination' => [
                'total' => (int)$total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch events',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
