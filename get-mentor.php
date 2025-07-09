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

$sql = "SELECT m.id, m.name, m.email, m.bio, m.photo
        FROM mentor_assignments a
        JOIN mentors m ON a.mentor_id = m.id
        WHERE a.user_id = $user_id
        LIMIT 1";
$result = $conn->query($sql);

if ($mentor = $result->fetch_assoc()) {
    echo json_encode(['mentor' => $mentor]);
} else {
    echo json_encode(['mentor' => null]);
}
$conn->close();
?> 