<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'hearts_after_god_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Create connection only if not already created
if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        static $pdo;
        
        if ($pdo === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (\PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                if (!headers_sent()) {
                    http_response_code(500);
                    header('Content-Type: application/json');
                }
                die(json_encode(['error' => 'Database connection failed. Please try again later.']));
            }
        }
        
        return $pdo;
    }
}

// Optional: Helper function for notifications (if you use a notifications table)
function addNotification($pdo, $user_id, $message) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->execute([$user_id, $message]);
}