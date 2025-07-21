<?php
require_once '../config/db.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// GET: List all events
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
    echo json_encode($stmt->fetchAll());
    exit;
}

// POST: Add a new event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, coordinator) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['title'] ?? '',
        $data['description'] ?? '',
        $data['event_date'] ?? '',
        $data['coordinator'] ?? ''
    ]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

// PUT: Edit an event
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE events SET title=?, description=?, event_date=?, coordinator=? WHERE id=?");
    $stmt->execute([
        $data['title'] ?? '',
        $data['description'] ?? '',
        $data['event_date'] ?? '',
        $data['coordinator'] ?? '',
        $data['id'] ?? 0
    ]);
    echo json_encode(['success' => true]);
    exit;
}

// DELETE: Remove an event
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("DELETE FROM events WHERE id=?");
    $stmt->execute([$data['id'] ?? 0]);
    echo json_encode(['success' => true]);
    exit;
}
?> 