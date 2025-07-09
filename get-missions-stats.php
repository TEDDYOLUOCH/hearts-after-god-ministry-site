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

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$user_id = intval($data['user_id']);
$module_id = intval($data['module_id']);
$status = $conn->real_escape_string($data['status']);
$score = isset($data['score']) ? intval($data['score']) : null;

$now = date('Y-m-d H:i:s');
$date_completed = ($status === 'completed') ? "'$now'" : "NULL";

// Upsert logic
$sql = "INSERT INTO user_module_progress (user_id, module_id, status, score, date_started, date_completed)
        VALUES ($user_id, $module_id, '$status', " . ($score === null ? "NULL" : $score) . ", '$now', $date_completed)
        ON DUPLICATE KEY UPDATE
            status = '$status',
            score = " . ($score === null ? "NULL" : $score) . ",
            date_completed = $date_completed";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $conn->error]);
}
$conn->close();
?> 