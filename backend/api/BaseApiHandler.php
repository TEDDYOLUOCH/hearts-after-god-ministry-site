<?php
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
function requireAdminAuth() {
    if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }
}

// Get JSON input from request body
function getJsonInput() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    return $input;
}

// Validate required fields
function validateRequiredFields($data, $requiredFields) {
    $missing = [];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Missing required fields: ' . implode(', ', $missing)
        ]);
        exit;
    }
}

// Handle file upload
function handleFileUpload($fieldName, $uploadDir, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']) {
    if (!isset($_FILES[$fieldName])) {
        return null;
    }

    $file = $_FILES[$fieldName];
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed with error code: ' . $file['error']);
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowedTypes));
    }
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $destination = rtrim($uploadDir, '/') . '/' . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    return $filename;
}

// Send success response
function sendSuccess($data = null, $message = 'Operation completed successfully') {
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Send error response
function sendError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

// Get database connection
function getDb() {
    static $db = null;
    if ($db === null) {
        require_once __DIR__ . '/../../config/database.php';
        try {
            $db = getDbConnection();
        } catch (Exception $e) {
            error_log('Database connection error: ' . $e->getMessage());
            sendError('Database connection failed: ' . $e->getMessage(), 500);
        }
    }
    return $db;
}

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
