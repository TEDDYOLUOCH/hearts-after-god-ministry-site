<?php
require_once '../config/db.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// GET: List all programmes
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM programmes ORDER BY id DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

// POST: Add a new programme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO programmes (title, description, coordinator) VALUES (?, ?, ?)");
    $stmt->execute([
        $data['title'] ?? '',
        $data['description'] ?? '',
        $data['coordinator'] ?? ''
    ]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

// PUT: Edit a programme
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE programmes SET title=?, description=?, coordinator=? WHERE id=?");
    $stmt->execute([
        $data['title'] ?? '',
        $data['description'] ?? '',
        $data['coordinator'] ?? '',
        $data['id'] ?? 0
    ]);
    echo json_encode(['success' => true]);
    exit;
}

// DELETE: Remove a programme
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("DELETE FROM programmes WHERE id=?");
    $stmt->execute([$data['id'] ?? 0]);
    echo json_encode(['success' => true]);
    exit;
}
?> 