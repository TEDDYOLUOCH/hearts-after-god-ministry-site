<?php
/**
 * Main entry point for the application
 * Handles routing and includes the appropriate file based on the URL
 */

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base paths
define('BASE_PATH', __DIR__);
define('FRONTEND_PATH', BASE_PATH . '/frontend');

// Include configuration and functions
require_once __DIR__ . '/config/paths.php';

// Include the redirect functions
require_once __DIR__ . '/includes/redirect.php';

// Get the request URI and remove query string
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_path = trim(str_replace('/hearts-after-god-ministry-site', '', $request_uri), '/');

// Default to index if empty
if (empty($request_path) || $request_path === 'index.php') {
    $request_path = 'index';
}

// Function to render a page with header and footer
function render_page($content_path, $is_html = false) {
    // Start output buffering
    ob_start();
    
    try {
        // Include header first
        $header_path = FRONTEND_PATH . '/includes/header.html';
        if (!file_exists($header_path)) {
            throw new Exception("Header file not found at: " . $header_path);
        }
        include $header_path;
        
        // Include the main content
        if ($is_html) {
            if (!file_exists($content_path)) {
                throw new Exception("Content file not found: " . $content_path);
            }
            readfile($content_path);
        } else {
            if (!file_exists($content_path)) {
                throw new Exception("Content file not found: " . $content_path);
            }
            include $content_path;
        }
        
        // Include footer
        $footer_path = FRONTEND_PATH . '/includes/footer.html';
        if (!file_exists($footer_path)) {
            throw new Exception("Footer file not found at: " . $footer_path);
        }
        include $footer_path;
    } catch (Exception $e) {
        // Clean any output buffer
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Log the error
        error_log("Error rendering page: " . $e->getMessage());
        
        // Show a user-friendly error message
        echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px;'>";
        echo "<h2>Oops! Something went wrong.</h2>";
        echo "<p>We're having trouble loading this page. Please try again later.</p>";
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        echo "</div>";
    }
    
    // Get the buffered content
    $content = ob_get_clean();
    
    // Output the final content
    echo $content;
}

// Map URLs to their corresponding files
$routes = [
    // Frontend pages
    'index' => ['path' => FRONTEND_PATH . '/pages/index.php', 'is_html' => false],
    'about' => ['path' => FRONTEND_PATH . '/pages/about.php', 'is_html' => false],
    'contact' => ['path' => FRONTEND_PATH . '/pages/contact.php', 'is_html' => false],
    'blog' => ['path' => FRONTEND_PATH . '/pages/blog.php', 'is_html' => false],
    'sermons' => ['path' => FRONTEND_PATH . '/pages/sermons.php', 'is_html' => false],
    'events' => ['path' => FRONTEND_PATH . '/pages/events.php', 'is_html' => false],
    'gallery' => ['path' => FRONTEND_PATH . '/pages/gallery.php', 'is_html' => false],
    'team' => ['path' => FRONTEND_PATH . '/pages/team.php', 'is_html' => false],
    'ministries' => ['path' => FRONTEND_PATH . '/pages/ministries.php', 'is_html' => false],
    'discipleship' => ['path' => FRONTEND_PATH . '/pages/discipleship.php', 'is_html' => false],
    'register' => ['path' => FRONTEND_PATH . '/pages/register.php', 'is_html' => false],
    'login' => ['path' => FRONTEND_PATH . '/pages/login.php', 'is_html' => false],
    'logout' => ['path' => FRONTEND_PATH . '/pages/logout.php', 'is_html' => false],
    
    // Dashboard - these will be handled by dashboard's own routing
    'dashboard' => ['path' => BASE_PATH . '/dashboard/index.php', 'is_html' => false, 'no_layout' => true],
    'dashboard/users' => ['path' => BASE_PATH . '/dashboard/users.php', 'is_html' => false, 'no_layout' => true],
    'dashboard/settings' => ['path' => BASE_PATH . '/dashboard/settings.php', 'is_html' => false, 'no_layout' => true],
];

// Check if the requested route exists
if (array_key_exists($request_path, $routes)) {
    $route = $routes[$request_path];
    $file_path = $route['path'];
    
    // Check if the file exists with .php or .html extension
    $file_found = false;
    $actual_path = '';
    $is_html = false;
    
    // Check for .php file
    if (file_exists($route['path'])) {
        $file_found = true;
        $actual_path = $route['path'];
        $is_html = $route['is_html'];
    } 
    // If .php not found, try .html
    else {
        $html_path = str_replace('.php', '.html', $route['path']);
        if (file_exists($html_path)) {
            $file_found = true;
            $actual_path = $html_path;
            $is_html = true;
        }
    }
    
    // If the file doesn't exist, show 404
    if (!$file_found) {
        header("HTTP/1.0 404 Not Found");
        $not_found_path = file_exists(FRONTEND_PATH . '/pages/404.html') ? 
            FRONTEND_PATH . '/pages/404.html' : 
            FRONTEND_PATH . '/pages/404.php';
        render_page($not_found_path, file_exists(FRONTEND_PATH . '/pages/404.html'));
        exit;
    }
    
    // If it's a dashboard route, include directly without layout
    if (isset($route['no_layout']) && $route['no_layout']) {
        include $actual_path;
    } else {
        // For frontend pages, use the render_page function
        render_page($actual_path, $is_html);
    }
} else {
    // Handle 404 - Page not found
    header("HTTP/1.0 404 Not Found");
    render_page(FRONTEND_PATH . '/pages/404.php', false);
}

exit();
