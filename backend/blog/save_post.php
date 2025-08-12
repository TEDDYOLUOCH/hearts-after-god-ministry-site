<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../../config/db.php';
$db = getDbConnection();

$id = $_POST['id'] ?? '';
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$status = $_POST['status'] ?? 'draft';

if ($title === '' || $content === '') {
    echo json_encode(['success' => false, 'message' => 'Title and content required.']);
    exit;
}

try {
    if ($id) {
        $stmt = $db->prepare("UPDATE blog_posts SET title=?, content=?, status=? WHERE id=?");
        $stmt->execute([$title, $content, $status, $id]);
    } else {
        $stmt = $db->prepare("INSERT INTO blog_posts (title, content, status, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$title, $content, $status]);
    }
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}