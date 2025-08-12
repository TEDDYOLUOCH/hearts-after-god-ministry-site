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
    
    // Build the query
    $query = "SELECT 
                id, 
                title, 
                slug, 
                excerpt, 
                featured_image,
                created_at,
                updated_at,
                status,
                (SELECT COUNT(*) FROM blog_comments WHERE blog_id = blog_posts.id) as comment_count
              FROM blog_posts 
              WHERE status = 'published'
              ORDER BY created_at DESC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count for pagination
    $totalStmt = $db->query("SELECT COUNT(*) as total FROM blog_posts WHERE status = 'published'");
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Return the response
    echo json_encode([
        'status' => 'success',
        'data' => [
            'posts' => $posts,
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
        'message' => 'Failed to fetch blog posts',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
