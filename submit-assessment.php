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
$assessment_id = intval($data['assessment_id']);
$answer = $conn->real_escape_string($data['answer']);
$score = isset($data['score']) ? intval($data['score']) : null;
$now = date('Y-m-d H:i:s');

$sql = "REPLACE INTO assessment_results (user_id, assessment_id, answer, score, completed_at)
        VALUES ($user_id, $assessment_id, '$answer', " . ($score === null ? "NULL" : $score) . ", '$now')";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $conn->error]);
}
$conn->close();
?> 