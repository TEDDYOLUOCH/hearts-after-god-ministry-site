<?php
require_once __DIR__ . '/../BaseApiHandler.php';

// Require admin authentication for all category operations
requireAdminAuth();

$db = getDb();
$method = $_SERVER['REQUEST_METHOD'];

// Helper function to get category by ID
function getCategoryById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM blog_categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        // Get single category by ID
        if (isset($_GET['id'])) {
            $category = getCategoryById($_GET['id']);
            if (!$category) {
                sendError('Category not found', 404);
            }
            sendSuccess($category);
        } 
        // Get all categories
        else {
            $stmt = $db->query("SELECT * FROM blog_categories ORDER BY name");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get post count for each category
            foreach ($categories as &$category) {
                $stmt = $db->prepare("
                    SELECT COUNT(*) as post_count 
                    FROM blog_post_categories 
                    WHERE category_id = ?
                ");
                $stmt->execute([$category['id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $category['post_count'] = (int)$result['post_count'];
            }
            
            sendSuccess($categories);
        }
        break;
        
    case 'POST':
        // Create new category
        $data = getJsonInput();
        $requiredFields = ['name'];
        validateRequiredFields($data, $requiredFields);
        
        try {
            // Create slug from name
            $slug = createSlug($data['name']);
            
            // Check if slug already exists
            $stmt = $db->prepare("SELECT id FROM blog_categories WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                sendError('A category with this name already exists', 400);
            }
            
            // Insert category
            $stmt = $db->prepare("
                INSERT INTO blog_categories (name, slug, description)
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([
                $data['name'],
                $slug,
                $data['description'] ?? null
            ]);
            
            $categoryId = $db->lastInsertId();
            sendSuccess(['id' => $categoryId], 'Category created successfully');
            
        } catch (Exception $e) {
            sendError('Failed to create category: ' . $e->getMessage(), 500);
        }
        break;
        
    case 'PUT':
        // Update existing category
        $data = getJsonInput();
        $requiredFields = ['id', 'name'];
        validateRequiredFields($data, $requiredFields);
        
        $category = getCategoryById($data['id']);
        if (!$category) {
            sendError('Category not found', 404);
        }
        
        try {
            // Create slug from name if name changed
            $slug = ($data['name'] !== $category['name']) ? createSlug($data['name']) : $category['slug'];
            
            // Check if new slug conflicts with existing category
            if ($slug !== $category['slug']) {
                $stmt = $db->prepare("SELECT id FROM blog_categories WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $data['id']]);
                if ($stmt->fetch()) {
                    sendError('A category with this name already exists', 400);
                }
            }
            
            // Update category
            $stmt = $db->prepare("
                UPDATE blog_categories 
                SET name = ?, slug = ?, description = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['name'],
                $slug,
                $data['description'] ?? $category['description'],
                $data['id']
            ]);
            
            sendSuccess(null, 'Category updated successfully');
            
        } catch (Exception $e) {
            sendError('Failed to update category: ' . $e->getMessage(), 500);
        }
        break;
        
    case 'DELETE':
        // Delete category
        $data = getJsonInput();
        if (!isset($data['id'])) {
            sendError('Category ID is required', 400);
        }
        
        $category = getCategoryById($data['id']);
        if (!$category) {
            sendError('Category not found', 404);
        }
        
        try {
            $db->beginTransaction();
            
            // Check if category is in use
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM blog_post_categories WHERE category_id = ?");
            $stmt->execute([$data['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                // Option 1: Don't allow deletion if category is in use
                // sendError('Cannot delete category that is in use by ' . $result['count'] . ' posts', 400);
                
                // Option 2: Remove category from all posts and then delete it
                $db->prepare("DELETE FROM blog_post_categories WHERE category_id = ?")
                   ->execute([$data['id']]);
            }
            
            // Delete category
            $db->prepare("DELETE FROM blog_categories WHERE id = ?")->execute([$data['id']]);
            
            $db->commit();
            sendSuccess(null, 'Category deleted successfully');
            
        } catch (Exception $e) {
            $db->rollBack();
            sendError('Failed to delete category: ' . $e->getMessage(), 500);
        }
        break;
        
    default:
        sendError('Method not allowed', 405);
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
