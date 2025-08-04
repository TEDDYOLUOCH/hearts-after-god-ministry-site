<?php
require_once __DIR__ . '/includes/standard_layout.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    } else {
        header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    }
    exit;
}

// Include database configuration
require_once __DIR__ . '/../config/db.php';

// Create database connection
try {
    $db = getDbConnection();
    // Test the connection
    $db->query('SELECT 1');
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    error_log('Connection details: ' . 
              'host=' . ($_ENV['DB_HOST'] ?? 'not set') . 
              ', dbname=' . ($_ENV['DB_NAME'] ?? 'not set'));
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Database connection failed',
            'error' => 'Check server logs for details'
        ]);
    } else {
        die('<div class="p-6">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <strong>Database Error:</strong> Could not connect to the database. Please check your configuration.
                ' . (ENVIRONMENT === 'development' ? '<br><small>' . htmlspecialchars($e->getMessage()) . '</small>' : '') . '
            </div>
        </div>');
    }
    exit;
}

// Function to get dashboard data
function getDashboardData($db, $isAdmin) {
    $data = [
        'stats' => [
            'total_posts' => 0,
            'total_users' => 0,
            'total_events' => 0,
            'total_sermons' => 0
        ],
        'recent_posts' => [],
        'recent_users' => [],
        'errors' => []
    ];
    
    try {
        // Get total blog posts
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM blog_posts");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['stats']['total_posts'] = (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            error_log('Error counting blog posts: ' . $e->getMessage());
            $data['errors'][] = 'Could not load blog post count';
        }
        
        // Get total users (only if admin)
        if ($isAdmin) {
            try {
                $stmt = $db->query("SELECT COUNT(*) as count FROM users");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $data['stats']['total_users'] = (int)($result['count'] ?? 0);
            } catch (Exception $e) {
                error_log('Error counting users: ' . $e->getMessage());
                $data['errors'][] = 'Could not load user count';
            }
        }
        
        // Get total events
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM events");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['stats']['total_events'] = (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            error_log('Error counting events: ' . $e->getMessage());
            $data['errors'][] = 'Could not load events count';
        }
        
        // Get total sermons
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM sermons");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['stats']['total_sermons'] = (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            error_log('Error counting sermons: ' . $e->getMessage());
            $data['errors'][] = 'Could not load sermons count';
        }
        
        // Get recent blog posts
        try {
            $stmt = $db->query("SELECT id, title, status, created_at, updated_at, excerpt FROM blog_posts ORDER BY created_at DESC LIMIT 5");
            $data['recent_posts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error loading recent posts: ' . $e->getMessage());
            $data['errors'][] = 'Could not load recent posts';
        }
        
        // Get recent users (only if admin)
        if ($isAdmin) {
            try {
                $stmt = $db->query("
                    SELECT id, username, email, role, created_at, last_login 
                    FROM users 
                    ORDER BY created_at DESC 
                    LIMIT 5
                ");
                $data['recent_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log('Error loading recent users: ' . $e->getMessage());
                $data['recent_users'] = [];
                $data['errors'][] = 'Could not load recent users';
            }
        } else {
            $data['recent_users'] = [];
        }
        
        return $data;
        
    } catch (Exception $e) {
        error_log('Unexpected error in getDashboardData: ' . $e->getMessage());
        $data['errors'][] = 'An unexpected error occurred while loading dashboard data';
        return $data;
    }
}

// Check if this is an AJAX request
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    try {
        $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
        $data = getDashboardData($db, $isAdmin);
        
        if ($data === null) {
            throw new Exception('Failed to load dashboard data');
        }
        
        // For AJAX requests, return JSON data
        echo json_encode([
            'success' => true,
            'data' => $data,
            'html' => getDashboardHtml($data, $isAdmin)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'html' => '<div class="p-6"><div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4">Error: ' . htmlspecialchars($e->getMessage()) . '</div></div>'
        ]);
    }
    exit;
}

// For regular page load, render the full dashboard
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
$dashboardData = getDashboardData($db, $isAdmin);

// Function to generate dashboard HTML
function getDashboardHtml($dashboardData, $isAdmin) {
    if ($dashboardData === null) {
        return '<div class="p-6"><div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4">Error: Could not load dashboard data</div></div>';
    }
    
    $stats = $dashboardData['stats'] ?? [
        'total_posts' => 0,
        'total_users' => 0,
        'total_events' => 0,
        'total_sermons' => 0
    ];
    
    $recent_posts = $dashboardData['recent_posts'] ?? [];
    $recent_users = ($isAdmin && isset($dashboardData['recent_users'])) ? $dashboardData['recent_users'] : [];
    $errors = $dashboardData['errors'] ?? [];
    
    ob_start();
    ?>
    <?php if (!empty($errors)): ?>
        <div class="p-4">
            <?php foreach ($errors as $error): ?>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 dark:text-yellow-300"><?= htmlspecialchars($error) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Rest of your dashboard HTML -->
    <?php
    include __DIR__ . '/includes/views/dashboard_view.php';
    return ob_get_clean();
}

// Render the standard layout with dashboard content
renderStandardLayout('Dashboard', function() use ($dashboardData, $isAdmin) {
    echo getDashboardHtml($dashboardData, $isAdmin);
});
