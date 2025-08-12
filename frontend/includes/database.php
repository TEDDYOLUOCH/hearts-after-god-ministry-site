<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'hearts_after_god_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Set timezone
date_default_timezone_set('Africa/Nairobi');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Create PDO instance globally
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection failed. Please try again later.');
}

// Optional: function to check if database exists
function databaseExists($dbname) {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $dbname . "'");
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        return false;
    }
}
