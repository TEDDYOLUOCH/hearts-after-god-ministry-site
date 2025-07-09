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

$sql = "SELECT id, title, description, file_path, file_type, access_level FROM resources ORDER BY created_at DESC";
$result = $conn->query($sql);

$resources = [];
while ($row = $result->fetch_assoc()) {
    $resources[] = $row;
}

echo json_encode(['resources' => $resources]);
$conn->close();
?> 