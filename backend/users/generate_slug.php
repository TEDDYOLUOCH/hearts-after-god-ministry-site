<?php
/**
 * Generate a URL-friendly slug from a given text
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit(json_encode(['success' => false, 'error' => 'Method not allowed']));
}

// Get input data
$text = $_POST['text'] ?? '';
$table = $_POST['table'] ?? '';
$field = $_POST['field'] ?? '';
$excludeId = $_POST['exclude_id'] ?? null;

// Validate input
if (empty($text) || empty($table) || empty($field)) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['success' => false, 'error' => 'Missing required parameters']));
}

// Include database configuration
require_once __DIR__ . '/../../config/db.php';

try {
    // Create slug from text
    $slug = createSlug($text);
    
    // Check if slug already exists in the database
    $pdo = getDbConnection();
    $query = "SELECT COUNT(*) as count FROM $table WHERE $field = :slug";
    $params = [':slug' => $slug];
    
    if ($excludeId) {
        $query .= " AND id != :id";
        $params[':id'] = $excludeId;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If slug exists, append a number to make it unique
    $originalSlug = $slug;
    $counter = 1;
    
    while ($result['count'] > 0) {
        $slug = $originalSlug . '-' . $counter;
        $stmt->execute([':slug' => $slug] + $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $counter++;
    }
    
    // Return the generated slug
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'slug' => $slug]);
    
} catch (Exception $e) {
    // Log the error
    error_log('Error generating slug: ' . $e->getMessage());
    
    // Return error response
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'error' => 'Failed to generate slug']);
}

/**
 * Create a URL-friendly slug from a string
 * 
 * @param string $text The text to convert to a slug
 * @return string The generated slug
 */
function createSlug($text) {
    // Replace non-letter or non-number characters with -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    
    // Transliterate to ASCII
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    
    // Remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    
    // Trim and convert to lowercase
    $text = trim($text, '-');
    $text = strtolower($text);
    
    // If empty, return a random string
    if (empty($text)) {
        return 'n-a' . uniqid();
    }
    
    return $text;
}
