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
                s.id, 
                s.title, 
                s.description, 
                s.speaker,
                s.sermon_date,
                s.audio_url,
                s.video_url,
                s.sermon_notes,
                s.bible_references,
                s.duration,
                s.views,
                s.downloads,
                s.featured_image,
                s.created_at,
                s.updated_at,
                s.status,
                u.name as speaker_name,
                u.profile_image as speaker_image
              FROM sermons s
              LEFT JOIN users u ON s.speaker = u.id
              WHERE s.status = 'published'
              ORDER BY s.sermon_date DESC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $sermons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count for pagination
    $totalStmt = $db->query("SELECT COUNT(*) as total FROM sermons WHERE status = 'published'");
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Return the response
    echo json_encode([
        'status' => 'success',
        'data' => [
            'sermons' => $sermons,
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
        'message' => 'Failed to fetch sermons',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
