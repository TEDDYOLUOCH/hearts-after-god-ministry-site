<?php
<?php
require_once '../../includes/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $pdo = getDbConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['id'])) {
            // Fetch single post
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$post) {
                http_response_code(404);
                echo json_encode(['error' => 'Post not found']);
                exit;
            }
            
            echo json_encode($post);
        } else {
            // Fetch all posts
            $posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($posts);
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    error_log("Blog post error: " . $e->getMessage());
}