<?php
require_once '../config/db.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $name = trim($data['name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $email = trim($data['email'] ?? '');
    $message = trim($data['message'] ?? '');

    if (!$name || !$email) {
        echo json_encode(['success' => false, 'message' => 'Name and email are required.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO discipleship_signups (name, phone, email, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $phone, $email, $message]);

    echo json_encode(['success' => true, 'message' => 'Thank you for signing up!']);
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid request.']);
?> 