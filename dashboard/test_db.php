<?php
// Start session
session_start();

// Output session info
echo "<h2>Session Info</h2>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    require_once __DIR__ . '/../config/db.php';
    $db = getDbConnection();
    
    // Test query
    $stmt = $db->query("SELECT DATABASE() as db");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<div style='color: green;'>✓ Successfully connected to database: " . htmlspecialchars($result['db']) . "</div>";
    
    // Check if tables exist
    $tables = ['users', 'blog_posts', 'events', 'sermons', 'gallery'];
    $stmt = $db->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Existing Tables:</h3><ul>";
    foreach ($existingTables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Test file permissions
echo "<h2>File Permissions</h2>";
$directories = [
    '/',
    '/dashboard',
    '/dashboard/includes',
    '/public',
    '/public/uploads',
    '/public/uploads/blog',
    '/public/uploads/events',
    '/public/uploads/sermons',
    '/public/uploads/gallery'
];

echo "<ul>";
foreach ($directories as $dir) {
    $path = dirname(__DIR__) . $dir;
    $exists = file_exists($path);
    $writable = is_writable($path);
    
    echo sprintf(
        "<li>%s - %s, %s</li>",
        htmlspecialchars($dir),
        $exists ? '<span style="color: green;">Exists</span>' : '<span style="color: red;">Missing</span>',
        $writable ? '<span style="color: green;">Writable</span>' : '<span style="color: red;">Not Writable</span>'
    );
}
echo "</ul>";

// Test PHP configuration
echo "<h2>PHP Configuration</h2>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Memory Limit: " . ini_get('memory_limit') . "</li>";
echo "<li>Upload Max Filesize: " . ini_get('upload_max_filesize') . "</li>";
echo "<li>Post Max Size: " . ini_get('post_max_size') . "</li>";
echo "<li>Max Execution Time: " . ini_get('max_execution_time') . "</li>";
echo "<li>Session Save Path: " . session_save_path() . "</li>";
echo "</ul>";

// Test JavaScript/AJAX
echo "<h2>JavaScript Test</h2>";
echo "<div id='js-test'>JavaScript is not working</div>";
echo "<script>document.getElementById('js-test').textContent = '✓ JavaScript is working properly';</script>";

// Test AJAX
echo "<h2>AJAX Test</h2>";
echo "<div id='ajax-test'>AJAX test in progress...</div>";
echo "<script>
    fetch('?test=ajax&' + new URLSearchParams(window.location.search).toString())
        .then(response => response.text())
        .then(html => {
            document.getElementById('ajax-test').innerHTML = '✓ AJAX is working properly: ' + html;
        })
        .catch(error => {
            document.getElementById('ajax-test').innerHTML = '✗ AJAX Error: ' + error;
        });
</script>";

// Handle AJAX test request
if (isset($_GET['test']) && $_GET['test'] === 'ajax') {
    die('AJAX response received at ' . date('Y-m-d H:i:s'));
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Check if you're logged in by looking at the Session Info section above</li>";
echo "<li>Verify the database connection is successful</li>";
echo "<li>Check that all required tables exist</li>";
echo "<li>Verify file permissions for upload directories</li>";
echo "<li>Check JavaScript console for any errors (F12 > Console)</li>";
echo "<li>Look for PHP errors in your web server's error log</li>";
echo "</ol>";

echo "<h2>Debug Info</h2>";
echo "<pre>";
var_dump([
    'GET' => $_GET,
    'POST' => $_POST,
    'SERVER' => array_diff_key($_SERVER, array_flip(['HTTP_COOKIE', 'PHP_AUTH_PW'])),
    'ENV' => $_ENV
]);
echo "</pre>";
?>
