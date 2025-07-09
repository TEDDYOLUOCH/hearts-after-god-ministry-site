<?php
header('Content-Type: application/json');
$host = 'localhost';
$db   = 'hearts_after_god';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = intval($data['user_id']);
$pathway_id = intval($data['pathway_id']);

$sql = "INSERT IGNORE INTO user_pathways (user_id, pathway_id, enrolled_at) VALUES ($user_id, $pathway_id, NOW())";
if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $conn->error]);
}
$conn->close();
?> 