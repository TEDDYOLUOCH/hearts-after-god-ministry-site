<?php
/**
 * Blog Management Functions
 */

/**
 * Get all blog posts
 */
function get_all_blog_posts($pdo) {
    try {
        $query = "
            SELECT p.*, u.name as author_name 
            FROM blog_posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            ORDER BY p.created_at DESC
        ";
        error_log("Executing query: " . $query);
        
        $stmt = $pdo->query($query);
        if ($stmt === false) {
            $error = $pdo->errorInfo();
            error_log("Query failed: " . print_r($error, true));
            return [];
        }
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Fetched " . count($result) . " blog posts");
        return $result;
    } catch (PDOException $e) {
        $errorMsg = "Error in " . __FUNCTION__ . ": " . $e->getMessage() . 
                  " in " . $e->getFile() . " on line " . $e->getLine();
        error_log($errorMsg);
        return [];
    }
}

/**
 * Get a single blog post by ID
 */
function get_blog_post($pdo, $post_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, GROUP_CONCAT(pc.category_id) as categories 
            FROM blog_posts p 
            LEFT JOIN blog_post_categories pc ON p.id = pc.post_id 
            WHERE p.id = ? 
            GROUP BY p.id
        ");
        $stmt->execute([$post_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching blog post: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all categories
 * @param PDO $pdo Database connection
 * @return array List of categories
 */
function get_all_categories($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure consistent data types
        foreach ($categories as &$category) {
            $category['id'] = (int)$category['id'];
        }
        
        return $categories;
        
    } catch (PDOException $e) {
        error_log('Error getting categories: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get categories for a specific blog post
 * @param PDO $pdo Database connection
 * @param int $post_id Post ID
 * @return array List of categories
 */
function get_post_categories($pdo, $post_id) {
    try {
        $post_id = (int)$post_id;
        if ($post_id <= 0) {
            return [];
        }
        
        $stmt = $pdo->prepare("
            SELECT c.* FROM categories c
            JOIN blog_post_categories pc ON c.id = pc.category_id
            WHERE pc.post_id = ?
            ORDER BY c.name
        ");
        $stmt->execute([$post_id]);
        
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure consistent data types
        foreach ($categories as &$category) {
            $category['id'] = (int)$category['id'];
        }
        
        return $categories;
        
    } catch (PDOException $e) {
        error_log('Error getting post categories: ' . $e->getMessage());
        return [];
    }
}

/**
 * Create URL-friendly slug from string
 * @param string $string Input string
 * @return string URL-friendly slug
 */
function create_slug($string) {
    if (empty($string)) {
        return '';
    }
    
    // Replace non-letter or non-number with -
    $slug = preg_replace('~[^\pL\d]+~u', '-', $string);
    
    // Transliterate non-ASCII characters
    if (function_exists('iconv')) {
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
    }
    
    // Remove unwanted characters
    $slug = preg_replace('~[^-\w]+~', '', $slug);
    $slug = trim($slug, '-');
    $slug = preg_replace('~-+~', '-', $slug);
    $slug = strtolower($slug);
    
    if (empty($slug)) {
        // If we end up with an empty string, generate a random one
        return 'post-' . bin2hex(random_bytes(4));
    }
    
    return $slug;
}
