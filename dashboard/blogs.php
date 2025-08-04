<?php
/**
 * Blog Management System
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/blog_errors.log');

// Set the current page for the header
$_GET['page'] = 'blogs';

// Include the admin layout
require_once __DIR__ . '/includes/admin_layout.php';

// Create logs directory if it doesn't exist
$logDir = __DIR__ . '/../logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

// Include database configuration
require_once __DIR__ . '/../config/db.php';

// Blog management functions
require_once __DIR__ . '/includes/blog_functions.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_blog_form_submission($pdo);
}

// Get all blog posts
$posts = get_all_blog_posts($pdo);

// Get all categories
$categories = get_all_categories($pdo);

// Get post for editing if in edit mode
$editing_post = null;
if (isset($_GET['edit'])) {
    $editing_post = get_blog_post($pdo, $_GET['edit']);
}

// Function to render the blog management content
function getBlogsContent($pdo) {
    global $posts, $categories, $editing_post;
    
    // Start output buffering
    ob_start();
    
    // Show success/error messages
    if (isset($_SESSION['success'])) {
        echo '<div class="mb-4 p-4 bg-green-100 text-green-700 rounded">' . 
             htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    
    if (isset($_SESSION['error'])) {
        echo '<div class="mb-4 p-4 bg-red-100 text-red-700 rounded">' . 
             htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    
    // Show either the form or the list view
    if (isset($_GET['new']) || isset($_GET['edit'])) {
        include __DIR__ . '/includes/views/blog_form.php';
    } else {
        include __DIR__ . '/includes/views/blog_list.php';
    }
    
    return ob_get_clean();
}

// Render the page using the admin layout
renderAdminLayout('Blog Management', 'getBlogsContent');
