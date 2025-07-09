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

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 1;

$sql = "SELECT graduation_date, certificate_path FROM graduations WHERE user_id = $user_id LIMIT 1";
$result = $conn->query($sql);

if ($row = $result->fetch_assoc()) {
    echo json_encode(['graduation' => $row]);
} else {
    echo json_encode(['graduation' => null]);
}
$conn->close();
?> 