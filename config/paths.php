<?php
/**
 * Path Configuration
 * Centralized configuration for all application paths and redirects
 */

// Base URLs
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/hearts-after-god-ministry-site');
define('DASHBOARD_URL', BASE_URL . '/dashboard');

// Frontend Paths
define('FRONTEND_URL', BASE_URL);
$frontendPaths = [
    'index' => BASE_URL . '/',
    'home' => BASE_URL . '/',
    'about' => BASE_URL . '/about',
    'contact' => BASE_URL . '/contact',
    'blog' => BASE_URL . '/blog',
    'sermons' => BASE_URL . '/sermons',
    'events' => BASE_URL . '/events',
    'gallery' => BASE_URL . '/gallery',
    'login' => BASE_URL . '/login',
    'register' => BASE_URL . '/register',
    '404' => BASE_URL . '/404'
];

// Dashboard Paths
$dashboardPaths = [
    'dashboard' => DASHBOARD_URL . '/admin-dashboard.php',
    'profile' => DASHBOARD_URL . '/profile.php',
    'users' => DASHBOARD_URL . '/users.php',
    'settings' => DASHBOARD_URL . '/settings.php',
    'logout' => DASHBOARD_URL . '/logout.php'
];

// API Endpoints
define('API_BASE', BASE_URL . '/backend/api');
$apiEndpoints = [
    'auth' => API_BASE . '/auth',
    'users' => API_BASE . '/users',
    'posts' => API_BASE . '/posts',
    'media' => API_BASE . '/media',
    'settings' => API_BASE . '/settings'
];

/**
 * Redirect to a specific URL
 * @param string $path The path to redirect to (from the paths arrays)
 * @param string $type The type of path ('frontend', 'dashboard', or 'api')
 * @param int $statusCode HTTP status code (default: 302)
 */
function redirect($path, $type = 'frontend', $statusCode = 302) {
    global $frontendPaths, $dashboardPaths, $apiEndpoints;
    
    $url = '';
    
    switch ($type) {
        case 'frontend':
            $url = $frontendPaths[$path] ?? BASE_URL . '/' . ltrim($path, '/');
            break;
            
        case 'dashboard':
            $url = $dashboardPaths[$path] ?? DASHBOARD_URL . '/' . ltrim($path, '/');
            break;
            
        case 'api':
            $url = $apiEndpoints[$path] ?? API_BASE . '/' . ltrim($path, '/');
            break;
            
        default:
            $url = BASE_URL . '/' . ltrim($path, '/');
    }
    
    header('Location: ' . $url, true, $statusCode);
    exit();
}

/**
 * Get the full URL for a path
 * @param string $path The path to get the URL for
 * @param string $type The type of path ('frontend', 'dashboard', or 'api')
 * @return string The full URL
 */
function get_url($path, $type = 'frontend') {
    global $frontendPaths, $dashboardPaths, $apiEndpoints;
    
    // Handle asset paths (images, CSS, JS, etc.)
    $asset_dirs = ['assets', 'images', 'css', 'js', 'fonts', 'vendor'];
    $asset_extensions = ['.jpg', '.jpeg', '.png', '.gif', '.svg', '.css', '.js', '.woff', '.woff2', '.ttf', '.eot'];
    
    // Check if path is an asset
    $is_asset = false;
    foreach ($asset_dirs as $dir) {
        if (strpos($path, $dir . '/') === 0) {
            $is_asset = true;
            break;
        }
    }
    
    // Also check by file extension
    if (!$is_asset) {
        foreach ($asset_extensions as $ext) {
            if (str_ends_with(strtolower($path), $ext)) {
                $is_asset = true;
                break;
            }
        }
    }
    
    if ($is_asset) {
        // For assets, always use the exact path from the root
        return BASE_URL . '/' . ltrim($path, '/');
    }
    
    // Handle frontend paths
    if ($type === 'frontend') {
        // If the path is in our frontend paths, use it
        if (isset($frontendPaths[$path])) {
            return $frontendPaths[$path];
        }
        
        // Check if this is a frontend page path
        $page_path = FRONTEND_PATH . '/pages/' . $path . '.php';
        if (file_exists($page_path)) {
            return BASE_URL . '/' . $path;
        }
        
        // Default to the path as is
        return BASE_URL . '/' . ltrim($path, '/');
    }
    
    // Handle dashboard paths
    if ($type === 'dashboard') {
        return $dashboardPaths[$path] ?? DASHBOARD_URL . '/' . ltrim($path, '/');
    }
    
    // Handle API paths
    if ($type === 'api') {
        return $apiEndpoints[$path] ?? API_BASE . '/' . ltrim($path, '/');
    }
    
    // Default case
    return BASE_URL . '/' . ltrim($path, '/');
}
