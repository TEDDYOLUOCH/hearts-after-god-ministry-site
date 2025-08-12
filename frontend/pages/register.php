<?php
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');
$email = trim(strtolower($data['email'] ?? ''));
$password = $data['password'] ?? '';
$whatsapp = trim($data['whatsapp'] ?? '');
$believer_type = trim($data['believer_type'] ?? '');

if (!$name || !$email || !$password || !$whatsapp || !$believer_type) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Email already registered.']);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'user';
$stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, whatsapp, believer_type) VALUES (?, ?, ?, ?, ?, ?)");
if ($stmt->execute([$name, $email, $hash, $role, $whatsapp, $believer_type])) {
    echo json_encode(['success' => true, 'message' => 'Registration successful!']);
    // Notify admins
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll();
    foreach ($admins as $admin) {
        if (function_exists('addNotification')) {
            addNotification($pdo, $admin['id'], "A new user registration is pending approval.");
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed.']);
} 