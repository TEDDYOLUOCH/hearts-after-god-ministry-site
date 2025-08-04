<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set test admin user session if not set
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 11; // Teddy Oluoch's ID
    $_SESSION['user_name'] = 'Teddy Oluoch';
    $_SESSION['user_email'] = 'oluochteddyochieng@gmail.com';
    $_SESSION['user_role'] = 'admin';
}

// Include database config
require_once __DIR__ . '/config/db.php';

// Function to test database connection and queries
function testDatabaseConnection() {
    try {
        $pdo = getDbConnection();
        
        $tables = [
            'users' => 'SELECT COUNT(*) as count FROM users',
            'blog_posts' => 'SELECT COUNT(*) as count FROM blog_posts',
            'sermons' => 'SELECT COUNT(*) as count FROM sermons',
            'events' => 'SELECT COUNT(*) as count FROM events',
            'gallery' => 'SELECT COUNT(*) as count FROM gallery'
        ];
        
        $results = [];
        foreach ($tables as $table => $query) {
            $stmt = $pdo->query($query);
            $results[$table] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        }
        
        return [
            'status' => 'success',
            'data' => $results
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

// Function to test session data
function testSession() {
    return [
        'user_id' => $_SESSION['user_id'] ?? 'Not set',
        'user_name' => $_SESSION['user_name'] ?? 'Not set',
        'user_email' => $_SESSION['user_email'] ?? 'Not set',
        'user_role' => $_SESSION['user_role'] ?? 'Not set'
    ];
}

// Function to test file permissions
function testFilePermissions() {
    $paths = [
        '/cache' => is_writable(__DIR__ . '/cache'),
        '/uploads' => is_writable(__DIR__ . '/uploads'),
        '/config/db.php' => is_readable(__DIR__ . '/config/db.php')
    ];
    
    return $paths;
}

// Run tests
$dbTest = testDatabaseConnection();
$sessionTest = testSession();
$fileTest = testFilePermissions();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Diagnostic Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-center">Admin Diagnostic Tool</h1>
        
        <!-- Session Info -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Session Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($sessionTest as $key => $value): ?>
                    <div class="p-3 bg-gray-50 rounded">
                        <span class="font-medium"><?= htmlspecialchars($key) ?>:</span>
                        <span class="text-gray-700"><?= htmlspecialchars($value) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Database Test -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Database Connection</h2>
            <?php if ($dbTest['status'] === 'success'): ?>
                <div class="mb-4 p-4 bg-green-50 text-green-800 rounded">
                    ✅ Successfully connected to the database
                </div>
                <h3 class="text-lg font-medium mb-2">Table Records Count:</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($dbTest['data'] as $table => $count): ?>
                        <div class="p-3 bg-gray-50 rounded border">
                            <span class="font-medium"><?= htmlspecialchars($table) ?>:</span>
                            <span class="text-blue-600 font-semibold"><?= $count ?></span> records
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="p-4 bg-red-50 text-red-800 rounded">
                    ❌ Database connection failed: <?= htmlspecialchars($dbTest['message']) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- File Permissions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">File Permissions</h2>
            <div class="space-y-2">
                <?php foreach ($fileTest as $file => $isWritable): ?>
                    <div class="flex items-center p-2 <?= $isWritable ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' ?> rounded">
                        <?= $isWritable ? '✅' : '❌' ?>
                        <span class="ml-2">
                            <?= htmlspecialchars($file) ?>: 
                            <span class="font-medium"><?= $isWritable ? 'Writable' : 'Not Writable' ?></span>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="/hearts-after-god-ministry-site/dashboard/" 
               class="p-4 bg-blue-600 text-white rounded-lg text-center hover:bg-blue-700 transition-colors">
                Go to Admin Dashboard
            </a>
            <a href="/hearts-after-god-ministry-site/test_blog_posts.php" 
               class="p-4 bg-green-600 text-white rounded-lg text-center hover:bg-green-700 transition-colors">
                Test Blog Posts
            </a>
            <a href="/hearts-after-god-ministry-site/test_layout.php" 
               class="p-4 bg-purple-600 text-white rounded-lg text-center hover:bg-purple-700 transition-colors">
                Test Layout
            </a>
        </div>
    </div>
</body>
</html>
