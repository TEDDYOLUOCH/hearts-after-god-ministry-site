<?php
require_once __DIR__ . '/../BaseApiHandler.php';

// Require admin authentication for all event operations
requireAdminAuth();

$db = getDb();
$method = $_SERVER['REQUEST_METHOD'];

// Helper function to get event by ID
function getEventById($id, $includeCategories = true) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event && $includeCategories) {
        $stmt = $db->prepare("
            SELECT c.id, c.name, c.slug 
            FROM event_categories c
            JOIN event_category_mapping ecm ON c.id = ecm.category_id
            WHERE ecm.event_id = ?
        ");
        $stmt->execute([$id]);
        $event['categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return $event;
}

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        // Get single event by ID
        if (isset($_GET['id'])) {
            $event = getEventById($_GET['id']);
            if (!$event) {
                sendError('Event not found', 404);
            }
            sendSuccess($event);
        } 
        // Get all events with optional filters
        else {
            $status = $_GET['status'] ?? 'all';
            $category = $_GET['category'] ?? null;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            
            $query = "SELECT e.*, u.username as creator_name 
                     FROM events e 
                     LEFT JOIN users u ON e.created_by = u.id 
                     WHERE 1=1";
            
            $params = [];
            
            // Apply status filter
            if ($status !== 'all') {
                $query .= " AND e.status = ?";
                $params[] = $status;
            }
            
            // Apply date range filter
            if ($startDate) {
                $query .= " AND e.start_datetime >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $query .= " AND e.end_datetime <= ?";
                $params[] = $endDate . ' 23:59:59'; // Include the entire end date
            }
            
            // Apply category filter
            if ($category) {
                $query .= " AND EXISTS (
                    SELECT 1 FROM event_category_mapping ecm 
                    JOIN event_categories ec ON ecm.category_id = ec.id 
                    WHERE ecm.event_id = e.id AND (ec.id = ? OR ec.slug = ?)
                )";
                $params[] = $category;
                $params[] = $category;
            }
            
            $query .= " ORDER BY e.start_datetime ASC";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get categories for each event
            if (!empty($events)) {
                $eventIds = array_column($events, 'id');
                $placeholders = str_repeat('?,', count($eventIds) - 1) . '?';
                
                $stmt = $db->prepare("
                    SELECT ecm.event_id, ec.id, ec.name, ec.slug 
                    FROM event_categories ec
                    JOIN event_category_mapping ecm ON ec.id = ecm.category_id
                    WHERE ecm.event_id IN ($placeholders)
                ");
                $stmt->execute($eventIds);
                $categories = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
                
                // Attach categories to events
                foreach ($events as &$event) {
                    $event['categories'] = $categories[$event['id']] ?? [];
                }
            }
            
            sendSuccess($events);
        }
        break;
        
    case 'POST':
        // Create new event
        $data = $_POST;
        $requiredFields = ['title', 'start_datetime', 'status'];
        validateRequiredFields($data, $requiredFields);
        
        try {
            $db->beginTransaction();
            
            // Handle image upload
            $imageUrl = null;
            if (isset($_FILES['image'])) {
                $uploadDir = __DIR__ . '/../../uploads/events';
                $imageUrl = handleFileUpload('image', $uploadDir);
            }
            
            // Insert event
            $stmt = $db->prepare("
                INSERT INTO events 
                (title, description, start_datetime, end_datetime, 
                 location, image_url, is_featured, registration_url, 
                 status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['title'],
                $data['description'] ?? null,
                $data['start_datetime'],
                $data['end_datetime'] ?? null,
                $data['location'] ?? null,
                $imageUrl,
                isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
                $data['registration_url'] ?? null,
                $data['status'],
                $_SESSION['user_id']
            ]);
            
            $eventId = $db->lastInsertId();
            
            // Handle categories
            if (!empty($data['categories']) && is_array($data['categories'])) {
                $stmt = $db->prepare("INSERT INTO event_category_mapping (event_id, category_id) VALUES (?, ?)");
                foreach ($data['categories'] as $categoryId) {
                    $stmt->execute([$eventId, $categoryId]);
                }
            }
            
            $db->commit();
            sendSuccess(['id' => $eventId], 'Event created successfully');
            
        } catch (Exception $e) {
            $db->rollBack();
            sendError('Failed to create event: ' . $e->getMessage(), 500);
        }
        break;
        
    case 'PUT':
        // Update existing event
        parse_str(file_get_contents('php://input'), $data);
        $requiredFields = ['id', 'title', 'start_datetime', 'status'];
        validateRequiredFields($data, $requiredFields);
        
        $event = getEventById($data['id'], false);
        if (!$event) {
            sendError('Event not found', 404);
        }
        
        try {
            $db->beginTransaction();
            
            // Handle image upload if new file is provided
            $imageUrl = $event['image_url'];
            if (isset($_FILES['image'])) {
                $uploadDir = __DIR__ . '/../../uploads/events';
                $imageUrl = handleFileUpload('image', $uploadDir);
                
                // Delete old image if it exists
                if ($event['image_url'] && file_exists($uploadDir . '/' . $event['image_url'])) {
                    unlink($uploadDir . '/' . $event['image_url']);
                }
            }
            
            // Update event
            $stmt = $db->prepare("
                UPDATE events 
                SET title = ?, description = ?, start_datetime = ?, end_datetime = ?,
                    location = ?, image_url = ?, is_featured = ?, registration_url = ?,
                    status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['title'],
                $data['description'] ?? $event['description'],
                $data['start_datetime'],
                $data['end_datetime'] ?? $event['end_datetime'],
                $data['location'] ?? $event['location'],
                $imageUrl,
                isset($data['is_featured']) ? (int)$data['is_featured'] : $event['is_featured'],
                $data['registration_url'] ?? $event['registration_url'],
                $data['status'],
                $data['id']
            ]);
            
            // Update categories if provided
            if (isset($data['categories']) && is_array($data['categories'])) {
                // Remove existing categories
                $db->prepare("DELETE FROM event_category_mapping WHERE event_id = ?")
                   ->execute([$data['id']]);
                
                // Add new categories
                $stmt = $db->prepare("INSERT INTO event_category_mapping (event_id, category_id) VALUES (?, ?)");
                foreach ($data['categories'] as $categoryId) {
                    $stmt->execute([$data['id'], $categoryId]);
                }
            }
            
            $db->commit();
            sendSuccess(null, 'Event updated successfully');
            
        } catch (Exception $e) {
            $db->rollBack();
            sendError('Failed to update event: ' . $e->getMessage(), 500);
        }
        break;
        
    case 'DELETE':
        // Delete event
        $data = getJsonInput();
        if (!isset($data['id'])) {
            sendError('Event ID is required', 400);
        }
        
        $event = getEventById($data['id'], false);
        if (!$event) {
            sendError('Event not found', 404);
        }
        
        try {
            $db->beginTransaction();
            
            // Delete event categories
            $db->prepare("DELETE FROM event_category_mapping WHERE event_id = ?")
               ->execute([$data['id']]);
            
            // Delete event
            $db->prepare("DELETE FROM events WHERE id = ?")->execute([$data['id']]);
            
            // Delete image if exists
            if ($event['image_url']) {
                $uploadDir = __DIR__ . '/../../uploads/events';
                $filePath = $uploadDir . '/' . $event['image_url'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $db->commit();
            sendSuccess(null, 'Event deleted successfully');
            
        } catch (Exception $e) {
            $db->rollBack();
            sendError('Failed to delete event: ' . $e->getMessage(), 500);
        }
        break;
        
    default:
        sendError('Method not allowed', 405);
}
