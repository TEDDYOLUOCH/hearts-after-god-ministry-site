<?php
require_once '../config/db.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// GET: List all signups (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM discipleship_signups ORDER BY signup_date DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

// POST: Add a new signup (public)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO discipleship_signups (name, phone, email, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['name'] ?? '',
        $data['phone'] ?? '',
        $data['email'] ?? '',
        $data['message'] ?? ''
    ]);
    echo json_encode(['success' => true]);
    exit;
}
?> 