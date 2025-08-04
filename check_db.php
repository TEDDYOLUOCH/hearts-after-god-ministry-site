<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = [
    'host' => 'localhost',
    'db'   => 'hearts_after_god_db',
    'user' => 'root',
    'pass' => ''
];

// Test database connection
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};",
        $config['user'],
        $config['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    echo "<h2>✅ Database connection successful!</h2>";
    
    // Check if gallery table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'gallery'");
    if ($stmt->rowCount() > 0) {
        echo "<h3>✅ Gallery table exists</h3>";
        
        // Show table structure
        echo "<h4>Table structure:</h4>";
        $stmt = $pdo->query("DESCRIBE gallery");
        echo "<pre>";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
        
        // Show sample data
        echo "<h4>Sample data (first 5 rows):</h4>";
        $stmt = $pdo->query("SELECT * FROM gallery LIMIT 5");
        echo "<pre>";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
    } else {
        echo "<h3>❌ Gallery table does not exist</h3>";
        echo "<p>Please run the following SQL to create the table:</p>";
        echo "<pre>";
        echo file_get_contents(__DIR__ . '/database/create_gallery_table.sql');
        echo "</pre>";
    }
    
} catch (PDOException $e) {
    echo "<h2>❌ Database connection failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    
    if ($e->getCode() == 1049) {
        echo "<p>The database '{$config['db']}' doesn't exist. Please create it first.</p>";
    } elseif ($e->getCode() == 2002) {
        echo "<p>Could not connect to MySQL server. Make sure MySQL is running.</p>";
    } elseif ($e->getCode() == 1045) {
        echo "<p>Access denied for user '{$config['user']}'. Please check your database credentials.</p>";
    }
}

// Show PHP info
// echo "<h2>PHP Info</h2>";
// phpinfo();
?>
