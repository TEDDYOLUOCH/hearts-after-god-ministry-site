<?php
require_once __DIR__ . '/../BaseApiHandler.php';

// Require admin authentication for all gallery operations
requireAdminAuth();

$db = getDb();
$method = $_SERVER['REQUEST_METHOD'];

// Helper function to get gallery item by ID
function getGalleryItemById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        // Get single gallery item by ID
        if (isset($_GET['id'])) {
            $item = getGalleryItemById($_GET['id']);
            if (!$item) {
                sendError('Gallery item not found', 404);
            }
            sendSuccess($item);
        } 
        // Get all gallery items with optional filters
        else {
            $search = $_GET['search'] ?? null;
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $query = "SELECT * FROM gallery WHERE 1=1";
            $params = [];
            
            // Apply search filter
            if ($search) {
                $query .= " AND (title LIKE ? OR description LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Apply date range filter
            if ($dateFrom) {
                $query .= " AND DATE(created_at) >= ?";
                $params[] = $dateFrom;
            }
            
            if ($dateTo) {
                $query .= " AND DATE(created_at) <= ?";
                $params[] = $dateTo;
            }
            
            $query .= " ORDER BY created_at DESC";
            
            // Apply pagination
            if ($limit !== null) {
                $query .= " LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
            }
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $countQuery = "SELECT COUNT(*) as total FROM gallery";
            $countStmt = $db->query($countQuery);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            sendSuccess([
                'items' => $items,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        }
        break;
        
    case 'POST':
        // Create new gallery item
        $data = $_POST;
        $requiredFields = ['title'];
        validateRequiredFields($data, $requiredFields);
        
        try {
            // Handle image upload
            if (!isset($_FILES['image'])) {
                sendError('Image file is required', 400);
            }
            
            $uploadDir = __DIR__ . '/../../uploads/gallery';
            $imagePath = handleFileUpload('image', $uploadDir);
            
            // Insert gallery item
            $stmt = $db->prepare("
                INSERT INTO gallery 
                (title, description, image_path)
                VALUES (?, ?, ?)
            
            ");
            
            $stmt->execute([
                $data['title'],
                $data['description'] ?? null,
                $imagePath
            ]);
            
            $itemId = $db->lastInsertId();
            sendSuccess(['id' => $itemId], 'Gallery item added successfully');
            
        } catch (Exception $e) {
            // Clean up uploaded file if something went wrong
            if (isset($imagePath) && file_exists($uploadDir . '/' . $imagePath)) {
                unlink($uploadDir . '/' . $imagePath);
            }
            sendError('Failed to add gallery item: ' . $e->getMessage(), 500);
        }
        break;
        
    case 'PUT':
        // Update existing gallery item
        parse_str(file_get_contents('php://input'), $data);
        $requiredFields = ['id', 'title'];
        validateRequiredFields($data, $requiredFields);
        
        $item = getGalleryItemById($data['id']);
        if (!$item) {
            sendError('Gallery item not found', 404);
        }
        
        try {
            $uploadDir = __DIR__ . '/../../uploads/gallery';
            $imagePath = $item['image_path'];
            
            // Handle new image upload if provided
            if (isset($_FILES['image'])) {
                $newImagePath = handleFileUpload('image', $uploadDir);
                
                // Delete old image if it exists
                if ($imagePath && file_exists($uploadDir . '/' . $imagePath)) {
                    unlink($uploadDir . '/' . $imagePath);
                }
                
                $imagePath = $newImagePath;
            }
            
            // Update gallery item
            $stmt = $db->prepare("
                UPDATE gallery 
                SET title = ?, description = ?, 
                    image_path = ?, updated_at = NOW()
                WHERE id = ?
            
            ");
            
            $stmt->execute([
                $data['title'],
                $data['description'] ?? $item['description'],
                $imagePath,
                $data['id']
            ]);
            
            sendSuccess(null, 'Gallery item updated successfully');
            
        } catch (Exception $e) {
            // Clean up new uploaded file if something went wrong
            if (isset($newImagePath) && file_exists($uploadDir . '/' . $newImagePath)) {
                unlink($uploadDir . '/' . $newImagePath);
            }
            sendError('Failed to update gallery item: ' . $e->getMessage(), 500);
        }
        break;
        
    case 'DELETE':
        // Delete gallery item
        $data = getJsonInput();
        if (!isset($data['id'])) {
            sendError('Gallery item ID is required', 400);
        }
        
        $item = getGalleryItemById($data['id']);
        if (!$item) {
            sendError('Gallery item not found', 404);
        }
        
        try {
            $uploadDir = __DIR__ . '/../../uploads/gallery';
            
            // Delete associated image file
            if ($item['image_path'] && file_exists($uploadDir . '/' . $item['image_path'])) {
                unlink($uploadDir . '/' . $item['image_path']);
            }
            
            // Delete gallery item from database
            $db->prepare("DELETE FROM gallery WHERE id = ?")->execute([$data['id']]);
            
            sendSuccess(null, 'Gallery item deleted successfully');
            
        } catch (Exception $e) {
            sendError('Failed to delete gallery item: ' . $e->getMessage(), 500);
        }
        break;
        
    case 'BULK_DELETE':
        // Bulk delete gallery items
        $data = getJsonInput();
        if (empty($data['ids']) || !is_array($data['ids'])) {
            sendError('Array of gallery item IDs is required', 400);
        }
        
        try {
            $db->beginTransaction();
            
            // Get all items to be deleted
            $placeholders = str_repeat('?,', count($data['ids']) - 1) . '?';
            $stmt = $db->prepare("SELECT id, image_path FROM gallery WHERE id IN ($placeholders)");
            $stmt->execute($data['ids']);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Delete associated image files
            $uploadDir = __DIR__ . '/../../uploads/gallery';
            foreach ($items as $item) {
                if ($item['image_path'] && file_exists($uploadDir . '/' . $item['image_path'])) {
                    unlink($uploadDir . '/' . $item['image_path']);
                }
            }
            
            // Delete gallery items from database
            $stmt = $db->prepare("DELETE FROM gallery WHERE id IN ($placeholders)");
            $stmt->execute($data['ids']);
            
            $db->commit();
            sendSuccess(null, count($items) . ' gallery items deleted successfully');
            
        } catch (Exception $e) {
            $db->rollBack();
            sendError('Failed to delete gallery items: ' . $e->getMessage(), 500);
        }
        break;
        
    default:
        sendError('Method not allowed', 405);
}
