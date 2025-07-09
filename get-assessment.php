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

$module_id = isset($_GET['module_id']) ? intval($_GET['module_id']) : 1;

$sql = "SELECT id, question, answer_type, options FROM assessments WHERE module_id = $module_id";
$result = $conn->query($sql);

$questions = [];
while ($row = $result->fetch_assoc()) {
    $row['options'] = $row['options'] ? json_decode($row['options'], true) : null;
    $questions[] = $row;
}

echo json_encode(['questions' => $questions]);
$conn->close();
?> 