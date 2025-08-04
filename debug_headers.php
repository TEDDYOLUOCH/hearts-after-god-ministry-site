<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering at the very beginning
ob_start();

// Function to check for output
function checkForOutput($message) {
    if (ob_get_length() > 0) {
        $output = ob_get_clean();
        die("Output detected before $message: " . htmlspecialchars($output));
    }
}

// Check before session start
checkForOutput("session_start()");

// Start session
session_start();

// Check after session start
checkForOutput("database connection");

// Include database configuration
require_once __DIR__ . '/config/db.php';

try {
    $pdo = getDbConnection();
    
    // Check after database connection
    checkForOutput("header redirect");
    
    // Test redirect
    if (!isset($_GET['test'])) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?test=1');
        exit;
    }
    
    // If we get here, headers worked
    echo "<h1>Header Test Successful!</h1>";
    echo "<p>No output detected before headers were sent.</p>";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// End output buffering
ob_end_flush();
?>
