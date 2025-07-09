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

$sql = "SELECT id, name, description, requirements FROM pathways";
$result = $conn->query($sql);

$pathways = [];
while ($row = $result->fetch_assoc()) {
    $pathways[] = $row;
}

echo json_encode(['pathways' => $pathways]);
$conn->close();
?> 