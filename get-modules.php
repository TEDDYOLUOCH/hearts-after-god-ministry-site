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

$sql = "SELECT id, title, description, content, order_num FROM modules ORDER BY order_num ASC";
$result = $conn->query($sql);

$modules = [];
while ($row = $result->fetch_assoc()) {
    $modules[] = $row;
}

echo json_encode(['modules' => $modules]);
$conn->close();
?> 