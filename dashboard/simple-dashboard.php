<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    exit;
}

// Include database config
require_once __DIR__ . '/../config/db.php';

// Simple layout function
function renderSimpleLayout($title, $content) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?> - Admin Dashboard</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.0/dist/cdn.min.js" defer></script>
        <script src="https://unpkg.com/lucide@latest"></script>
        <style>
            [x-cloak] { display: none !important; }
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="bg-gray-100">
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <div class="bg-gray-800 text-white w-64 p-4">
                <h1 class="text-xl font-bold mb-6">Admin Panel</h1>
                <nav>
                    <a href="admin-dashboard.php" class="block py-2 px-4 bg-gray-700 rounded">Dashboard</a>
                    <a href="#" class="block py-2 px-4 hover:bg-gray-700 rounded">Sermons</a>
                    <a href="#" class="block py-2 px-4 hover:bg-gray-700 rounded">Blog</a>
                    <a href="#" class="block py-2 px-4 hover:bg-gray-700 rounded">Events</a>
                    <a href="#" class="block py-2 px-4 hover:bg-gray-700 rounded">Gallery</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="flex-1 p-8">
                <header class="bg-white shadow-sm rounded-lg p-4 mb-6">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-bold"><?= htmlspecialchars($title) ?></h1>
                        <div class="flex items-center space-x-4">
                            <span><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
                            <a href="/hearts-after-god-ministry-site/backend/users/logout.php" class="text-red-600 hover:text-red-800">
                                Logout
                            </a>
                        </div>
                    </div>
                </header>
                
                <main class="bg-white rounded-lg shadow-sm p-6">
                    <?php 
                    if (is_callable($content)) {
                        $content();
                    } else {
                        echo $content;
                    }
                    ?>
                </main>
            </div>
        </div>
        
        <script>
            // Initialize Lucide Icons
            lucide.createIcons();
            
            // Simple Alpine.js app
            document.addEventListener('alpine:init', () => {
                console.log('Alpine.js initialized');
            });
        </script>
    </body>
    </html>
    <?php
}

// Get database connection
try {
    $pdo = getDbConnection();
    
    // Get some stats (simplified)
    $stats = [
        'sermons' => 0,
        'blog_posts' => 0,
        'upcoming_events' => 0,
        'gallery_items' => 0
    ];
    
    // Try to get actual stats
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM sermons");
        $stats['sermons'] = $stmt->fetchColumn() ?: 0;
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM blog_posts");
        $stats['blog_posts'] = $stmt->fetchColumn() ?: 0;
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE()");
        $stats['upcoming_events'] = $stmt->fetchColumn() ?: 0;
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery");
        $stats['gallery_items'] = $stmt->fetchColumn() ?: 0;
    } catch (PDOException $e) {
        error_log("Error fetching stats: " . $e->getMessage());
    }
    
    // Define the content
    $content = function() use ($stats) {
        ?>
        <h2 class="text-xl font-semibold mb-6">Dashboard Overview</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Sermons Card -->
            <div class="bg-white border rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Sermons</p>
                        <h3 class="text-2xl font-bold"><?= $stats['sermons'] ?></h3>
                    </div>
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-full">
                        <i data-lucide="mic-2" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
            
            <!-- Blog Posts Card -->
            <div class="bg-white border rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Blog Posts</p>
                        <h3 class="text-2xl font-bold"><?= $stats['blog_posts'] ?></h3>
                    </div>
                    <div class="p-3 bg-green-100 text-green-600 rounded-full">
                        <i data-lucide="newspaper" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
            
            <!-- Events Card -->
            <div class="bg-white border rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Upcoming Events</p>
                        <h3 class="text-2xl font-bold"><?= $stats['upcoming_events'] ?></h3>
                    </div>
                    <div class="p-3 bg-purple-100 text-purple-600 rounded-full">
                        <i data-lucide="calendar" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
            
            <!-- Gallery Card -->
            <div class="bg-white border rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Gallery Items</p>
                        <h3 class="text-2xl font-bold"><?= $stats['gallery_items'] ?></h3>
                    </div>
                    <div class="p-3 bg-yellow-100 text-yellow-600 rounded-full">
                        <i data-lucide="image" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
            <p class="text-blue-700">This is a simplified version of the dashboard. If this works, the issue is likely in the original layout or content files.</p>
        </div>
        <?php
    };
    
    // Render the layout with content
    renderSimpleLayout('Dashboard', $content);
    
} catch (Exception $e) {
    // Simple error display if something goes wrong
    die("<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>");
}
