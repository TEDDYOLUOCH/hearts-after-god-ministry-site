<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../../logs/php_errors.log');

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/../../../logs')) {
    mkdir(__DIR__ . '/../../../logs', 0777, true);
}

// Require the base API handler
require_once __DIR__ . '/../BaseApiHandler.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Require admin authentication for all blog post operations
    requireAdminAuth();

    $db = getDb();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // Log request for debugging
    error_log('Blog Posts API Request: ' . $method . ' ' . ($_SERVER['REQUEST_URI'] ?? '') . ' ' . ($_SERVER['REQUEST_METHOD'] ?? ''));
    error_log('Query Params: ' . json_encode($_GET ?? []));
    error_log('Request Body: ' . file_get_contents('php://input'));

/**
 * Get post by ID
 */
function getPostById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get post by slug
 */
function getPostBySlug($slug) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get categories for a post
 */
function getPostCategories($db, $postId) {
    try {
        $stmt = $db->prepare("
            SELECT c.id, c.name, c.slug, c.description 
            FROM blog_categories c
            JOIN blog_post_categories pc ON c.id = pc.category_id
            WHERE pc.post_id = ?
            ORDER BY c.name
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching post categories: ' . $e->getMessage());
        return [];
    }
}

/**
 * Update post categories
 */
function updatePostCategories($db, $postId, $categoryIds) {
    try {
        // Delete existing categories
        $db->prepare("DELETE FROM blog_post_categories WHERE post_id = ?")
           ->execute([$postId]);
        
        // Insert new categories
        if (!empty($categoryIds)) {
            $stmt = $db->prepare("INSERT INTO blog_post_categories (post_id, category_id) VALUES (?, ?)");
            foreach ($categoryIds as $categoryId) {
                $stmt->execute([$postId, $categoryId]);
            }
        }
        
        return true;
    } catch (Exception $e) {
        error_log('Error updating post categories: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Validate post data
 */
function validatePostData($data, $isUpdate = false) {
    $errors = [];
    
    if (empty($data['title'])) {
        $errors[] = 'Title is required';
    } elseif (mb_strlen($data['title']) > 255) {
        $errors[] = 'Title cannot be longer than 255 characters';
    }
    
    if (empty($data['content'])) {
        $errors[] = 'Content is required';
    }
    
    if (empty($data['status']) || !in_array($data['status'], ['draft', 'published', 'archived'])) {
        $errors[] = 'Invalid status';
    }
    
    if ($isUpdate && empty($data['id'])) {
        $errors[] = 'Post ID is required for update';
    }
    
    return $errors;
}

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        // Get single post by ID or slug
        if (isset($_GET['id']) || isset($_GET['slug'])) {
            $post = null;
            if (isset($_GET['id'])) {
                $post = getPostById($_GET['id']);
            } elseif (isset($_GET['slug'])) {
                $post = getPostBySlug($_GET['slug']);
            }
            
            if (!$post) {
                sendError('Post not found', 404);
            }
            
            // Get author details
            $author = null;
            if ($post['author_id']) {
                $stmt = $db->prepare("SELECT id, name, email, avatar FROM users WHERE id = ?");
                $stmt->execute([$post['author_id']]);
                $author = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            $post['author'] = $author;
            
            // Get categories
            $post['categories'] = getPostCategories($db, $post['id']);
            
            // Increment view count (for non-admin requests)
            if (!isset($_GET['admin']) || !$_GET['admin']) {
                $stmt = $db->prepare("UPDATE blog_posts SET views = COALESCE(views, 0) + 1 WHERE id = ?");
                $stmt->execute([$post['id']]);
            }
            
            sendSuccess($post);
        } 
        // Get all posts with pagination and filtering
        else {
            // Pagination
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = min(20, max(1, intval($_GET['per_page'] ?? 10)));
            $offset = ($page - 1) * $perPage;
            
            // Build base query
            $query = "FROM blog_posts p ";
            $where = [];
            $params = [];
            
            // Filter by status
            $status = $_GET['status'] ?? 'published';
            if ($status !== 'all') {
                $where[] = "p.status = ?";
                $params[] = $status;
            }
            
            // Filter by category
            if (!empty($_GET['category'])) {
                $query .= "JOIN blog_post_categories pc ON p.id = pc.post_id ";
                $query .= "JOIN blog_categories c ON pc.category_id = c.id ";
                $where[] = "(c.slug = ? OR c.id = ?)";
                $params[] = $_GET['category'];
                $params[] = is_numeric($_GET['category']) ? intval($_GET['category']) : 0;
            }
            
            // Search query
            if (!empty($_GET['search'])) {
                $search = "%{$_GET['search']}%";
                $where[] = "(p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)";
                $params = array_merge($params, [$search, $search, $search]);
            }
            
            // Build WHERE clause
            if (!empty($where)) {
                $query .= " WHERE " . implode(" AND ", $where);
            }
            
            // Get total count
            $countStmt = $db->prepare("SELECT COUNT(*) " . $query);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            // Get paginated results
            $query = "SELECT p.*, u.name as author_name, u.avatar as author_avatar " . $query;
            $query .= " LEFT JOIN users u ON p.author_id = u.id ";
            $query .= " ORDER BY p.published_at DESC, p.created_at DESC ";
            $query .= " LIMIT ? OFFSET ?";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get categories for each post
            foreach ($posts as &$post) {
                $post['categories'] = getPostCategories($db, $post['id']);
                
                // Format dates
                $post['created_at_formatted'] = date('M j, Y', strtotime($post['created_at']));
                $post['published_at_formatted'] = $post['published_at'] 
                    ? date('M j, Y', strtotime($post['published_at'])) 
                    : null;
                
                // Add excerpt if not set
                if (empty($post['excerpt']) && !empty($post['content'])) {
                    $post['excerpt'] = substr(strip_tags($post['content']), 0, 200) . '...';
                }
                
                // Ensure default values
                $post['status'] = $post['status'] ?? 'draft';
                $post['views'] = $post['views'] ?? 0;
            }
            
            // Return paginated response
            sendSuccess([
                'data' => $posts,
                'pagination' => [
                    'total' => (int)$total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total)
                ]
            ]);
        }
        break;
        
    case 'POST':
        // Create new post
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $errors = validatePostData($data);
        if (!empty($errors)) {
            sendError(['errors' => $errors], 'Validation failed', 400);
        }
        
        try {
            $db->beginTransaction();
            
            // Generate slug from title if not provided
            $data['slug'] = !empty($data['slug']) ? createSlug($data['slug']) : createSlug($data['title']);
            
            // Ensure slug is unique
            $slug = $data['slug'];
            $counter = 1;
            while (getPostBySlug($slug)) {
                $slug = $data['slug'] . '-' . $counter++;
            }
            $data['slug'] = $slug;
            
            // Set published_at if status is published and not already set
            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
            
            // Set author_id to current user if not provided
            if (empty($data['author_id'])) {
                $data['author_id'] = $_SESSION['user_id'] ?? null;
            }
            
            // Insert post
            $query = "INSERT INTO blog_posts (
                title, slug, excerpt, content, featured_image, 
                author_id, status, published_at, meta_title, 
                meta_description, meta_keywords
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                $data['title'],
                $data['slug'],
                $data['excerpt'] ?? null,
                $data['content'],
                $data['featured_image'] ?? null,
                $data['author_id'],
                $data['status'],
                $data['published_at'] ?? null,
                $data['meta_title'] ?? $data['title'],
                $data['meta_description'] ?? null,
                $data['meta_keywords'] ?? null
            ]);
            
            $postId = $db->lastInsertId();
            
            // Handle categories if provided
            if (!empty($data['categories'])) {
                updatePostCategories($db, $postId, $data['categories']);
            }
            
            $db->commit();
            
            // Return the created post
            $post = getPostById($postId);
            $post['categories'] = getPostCategories($db, $postId);
            
            sendSuccess($post, 'Post created successfully', 201);
            
        } catch (Exception $e) {
            $db->rollBack();
            sendError('Failed to create post: ' . $e->getMessage(), 500);
        }
        break;
        
    case 'PUT':
        // Update existing post
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $errors = validatePostData($data, true);
        if (!empty($errors)) {
            sendError(['errors' => $errors], 'Validation failed', 400);
        }
        
        try {
            $db->beginTransaction();
            
            // Check if post exists
            $post = getPostById($data['id']);
            if (!$post) {
                sendError('Post not found', 404);
            }
            
            // Generate new slug if title changed
            if ($data['title'] !== $post['title'] && empty($data['slug'])) {
                $data['slug'] = createSlug($data['title']);
                
                // Ensure slug is unique
                $slug = $data['slug'];
                $counter = 1;
                while (getPostBySlug($slug) && getPostBySlug($slug)['id'] != $data['id']) {
                    $slug = $data['slug'] . '-' . $counter++;
                }
                $data['slug'] = $slug;
            } elseif (empty($data['slug'])) {
                $data['slug'] = $post['slug'];
            }
            
            // Set published_at if status changed to published and wasn't published before
            if ($data['status'] === 'published' && $post['status'] !== 'published') {
                $data['published_at'] = date('Y-m-d H:i:s');
            } elseif ($data['status'] !== 'published') {
                $data['published_at'] = null;
            } else {
                $data['published_at'] = $post['published_at'];
            }
            
            // Update post
            $query = "
                UPDATE blog_posts 
                SET 
                    title = ?,
                    slug = ?,
                    excerpt = ?, 
                    content = ?, 
                    featured_image = ?,
                    status = ?, 
                    published_at = ?,
                    meta_title = ?,
                    meta_description = ?,
                    meta_keywords = ?,
                    updated_at = NOW()
                WHERE id = ?
            ";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                $data['title'],
                $data['slug'],
                $data['excerpt'] ?? null,
                $data['content'],
                $data['featured_image'] ?? $post['featured_image'],
                $data['status'],
                $data['published_at'],
                $data['meta_title'] ?? $data['title'],
                $data['meta_description'] ?? null,
                $data['meta_keywords'] ?? null,
                $data['id']
            ]);
            
            // Handle categories if provided
            if (isset($data['categories'])) {
                updatePostCategories($db, $data['id'], $data['categories']);
            }
            
            $db->commit();
            
            // Return updated post with categories
            $updatedPost = getPostById($data['id']);
            $updatedPost['categories'] = getPostCategories($db, $data['id']);
            
            sendSuccess($updatedPost, 'Post updated successfully');
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Error updating post: ' . $e->getMessage());
            sendError('Failed to update post: ' . $e->getMessage(), 500);
        }
        break;
        
case 'DELETE':
    // Delete post
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? $_GET['id'] ?? null;
    
    if (!$id) {
        sendError('Post ID is required', 400);
    }
    
    try {
        $db->beginTransaction();
        
        // Get post details before deletion for cleanup
        $post = getPostById($id);
        if (!$post) {
            sendError('Post not found', 404);
        }
        
        // Delete post categories
        $db->prepare("DELETE FROM blog_post_categories WHERE post_id = ?")->execute([$id]);
        
        // Delete any comments associated with the post (if you have a comments table)
        try {
            $db->prepare("DELETE FROM blog_comments WHERE post_id = ?")->execute([$id]);
        } catch (Exception $e) {
            // Ignore if comments table doesn't exist
            if ($db->errorCode() !== '42S02') { // Table doesn't exist
                throw $e;
            }
        }
        
        // Delete post
        $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete featured image if it exists
        if (!empty($post['featured_image'])) {
            $imagePath = __DIR__ . '/../../' . ltrim($post['featured_image'], '/');
            if (file_exists($imagePath) && is_file($imagePath)) {
                @unlink($imagePath);
            }
            
            // Also delete any generated thumbnails if they exist
            $uploadDir = dirname($imagePath);
            $filename = pathinfo($imagePath, PATHINFO_FILENAME);
            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
            
            $thumbnailPattern = $uploadDir . '/' . $filename . '-*x*.' . $extension;
            array_map('unlink', glob($thumbnailPattern) ?: []);
        }
        
        $db->commit();
        
        // Clear any relevant caches
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
        
        sendSuccess(['id' => $id], 'Post deleted successfully');
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log('Error deleting post: ' . $e->getMessage());
        sendError('Failed to delete post: ' . $e->getMessage(), 500);
    }
    break;
        
default:
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
        exit;
}

} catch (Exception $e) {
    error_log('Error in blog posts API: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Internal Server Error',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
}

// Helper function to create URL-friendly slug
function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}
