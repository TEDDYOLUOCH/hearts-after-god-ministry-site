<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$required_fields = ['name', 'email', 'subject', 'message'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

$name = htmlspecialchars(trim($input['name']));
$email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars(trim($input['subject']));
$message = htmlspecialchars(trim($input['message']));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// --- DATABASE INSERT SECTION ---
// Update these variables with your DB credentials:
$host = 'localhost';           // Database server (usually 'localhost' for XAMPP)
$db   = 'hearts_after_god_db'; // The name of your database (must match what you created in phpMyAdmin)
$user = 'root';                // Database username (default is 'root' for XAMPP)
$pass = '';                    // Database password (default is empty for XAMPP)

// Create a connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Prepare and execute insert statement
$stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    $conn->close();
    exit;
}

$stmt->bind_param("ssss", $name, $email, $subject, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Message received and saved! Thank you for contacting us.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save message: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
exit;
?>