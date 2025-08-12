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
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
    $offset = ($page - 1) * $limit;
    
    // Get filter parameters
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
    
    // Build the base query
    $query = "SELECT 
                g.id, 
                g.title, 
                g.description, 
                g.image_url,
                g.thumbnail_url,
                g.category_id,
                c.name as category_name,
                g.event_date,
                g.is_featured,
                g.views,
                g.created_at,
                g.updated_at
              FROM gallery g
              LEFT JOIN gallery_categories c ON g.category_id = c.id
              WHERE 1=1";
    
    $params = [];
    
    // Add filters
    if ($category) {
        $query .= " AND c.slug = :category";
        $params[':category'] = $category;
    }
    
    if ($year) {
        $query .= " AND YEAR(g.event_date) = :year";
        $params[':year'] = $year;
    }
    
    // Add ordering and pagination
    $query .= " ORDER BY g.event_date DESC, g.created_at DESC
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
    $galleryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM gallery g";
    if ($category) {
        $countQuery .= " LEFT JOIN gallery_categories c ON g.category_id = c.id";
    }
    $countQuery .= " WHERE 1=1";
    
    if ($category) {
        $countQuery .= " AND c.slug = :category";
    }
    if ($year) {
        $countQuery .= " AND YEAR(g.event_date) = :year";
    }
    
    $countStmt = $db->prepare($countQuery);
    
    // Bind parameters for count query
    if ($category) {
        $countStmt->bindValue(':category', $category);
    }
    if ($year) {
        $countStmt->bindValue(':year', $year, PDO::PARAM_INT);
    }
    
    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get available years for filtering
    $yearsStmt = $db->query("SELECT DISTINCT YEAR(event_date) as year FROM gallery WHERE event_date IS NOT NULL ORDER BY year DESC");
    $years = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get categories for filtering
    $categoriesStmt = $db->query("SELECT id, name, slug FROM gallery_categories WHERE status = 'active' ORDER BY name");
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return the response
    echo json_encode([
        'status' => 'success',
        'data' => [
            'items' => $galleryItems,
            'filters' => [
                'years' => $years,
                'categories' => $categories,
                'selected' => [
                    'category' => $category,
                    'year' => $year
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
        'message' => 'Failed to fetch gallery items',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
