<?php
session_start();
header('Content-Type: application/json');

// Only allow admins
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Connect to DB
require_once __DIR__ . '/../../config/db.php';
$db = getDbConnection();

// Validate input
$site_name = trim($_POST['site_name'] ?? '');
$contact_email = trim($_POST['contact_email'] ?? '');

if ($site_name === '' || $contact_email === '') {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

try {
    // Update or insert settings
    $settings = [
        'site_name' => $site_name,
        'contact_email' => $contact_email
    ];
    foreach ($settings as $key => $value) {
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$key, $value]);
    }
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}