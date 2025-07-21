<?php
require_once '../config/db.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// GET: List all leaders
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM ministry_leaders ORDER BY id DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

// POST: Add a new leader
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO ministry_leaders (name, title, bio, image_url) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['name'] ?? '',
        $data['title'] ?? '',
        $data['bio'] ?? '',
        $data['image_url'] ?? ''
    ]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

// PUT: Edit a leader
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE ministry_leaders SET name=?, title=?, bio=?, image_url=? WHERE id=?");
    $stmt->execute([
        $data['name'] ?? '',
        $data['title'] ?? '',
        $data['bio'] ?? '',
        $data['image_url'] ?? '',
        $data['id'] ?? 0
    ]);
    echo json_encode(['success' => true]);
    exit;
}

// DELETE: Remove a leader
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("DELETE FROM ministry_leaders WHERE id=?");
    $stmt->execute([$data['id'] ?? 0]);
    echo json_encode(['success' => true]);
    exit;
}
?> 