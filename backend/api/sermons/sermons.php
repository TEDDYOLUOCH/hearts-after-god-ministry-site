<?php
require_once __DIR__ . '/../BaseApiHandler.php';

// Require admin authentication for all sermon operations
requireAdminAuth();

$db = getDb();
$method = $_SERVER['REQUEST_METHOD'];

// Helper function to get sermon by ID
function getSermonById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM sermons WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        // Get single sermon by ID
        if (isset($_GET['id'])) {
            $sermon = getSermonById($_GET['id']);
            if (!$sermon) {
                sendError('Sermon not found', 404);
            }
            sendSuccess($sermon);
        } 
        // Get all sermons with optional filters
        else {
            $preacher = $_GET['preacher'] ?? null;
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;
            $search = $_GET['search'] ?? null;
            
            $query = "SELECT * FROM sermons WHERE 1=1";
            $params = [];
            
            // Apply filters
            if ($preacher) {
                $query .= " AND preacher LIKE ?";
                $params[] = "%$preacher%";
            }
            
            if ($dateFrom) {
                $query .= " AND sermon_date >= ?";
                $params[] = $dateFrom;
            }
            
            if ($dateTo) {
                $query .= " AND sermon_date <= ?";
                $params[] = $dateTo;
            }
            
            if ($search) {
                $query .= " AND (title LIKE ? OR description LIKE ? OR bible_reference LIKE ?)";
                $searchTerm = "%$search%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            }
            
            $query .= " ORDER BY sermon_date DESC";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $sermons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendSuccess($sermons);
        }
        break;
        
    case 'POST':
        // Create new sermon
        $data = $_POST;
        $requiredFields = ['title', 'preacher', 'sermon_date'];
        validateRequiredFields($data, $requiredFields);
        
        try {
            // Handle file uploads
            $thumbnailUrl = null;
            $audioUrl = null;
            $videoUrl = null;
            
            $uploadDir = __DIR__ . '/../../uploads/sermons';
            
            // Handle thumbnail upload
            if (isset($_FILES['thumbnail'])) {
                $thumbnailUrl = handleFileUpload('thumbnail', $uploadDir);
            }
            
            // Handle audio upload
            if (isset($_FILES['audio'])) {
                $audioUrl = handleFileUpload('audio', $uploadDir, [
                    'audio/mpeg', 'audio/wav', 'audio/mp3', 'audio/ogg'
                ]);
            }
            
            // Handle video upload (or URL)
            if (isset($_FILES['video'])) {
                $videoUrl = handleFileUpload('video', $uploadDir, [
                    'video/mp4', 'video/webm', 'video/ogg'
                ]);
            } elseif (!empty($data['video_url'])) {
                $videoUrl = $data['video_url'];
            }
            
            // Insert sermon
            $stmt = $db->prepare("
                INSERT INTO sermons 
                (title, preacher, bible_reference, sermon_date, 
                 audio_url, video_url, description, thumbnail_url)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            
            ");
            
            $stmt->execute([
                $data['title'],
                $data['preacher'],
                $data['bible_reference'] ?? null,
                $data['sermon_date'],
                $audioUrl,
                $videoUrl,
                $data['description'] ?? null,
                $thumbnailUrl
            ]);
            
            $sermonId = $db->lastInsertId();
            sendSuccess(['id' => $sermonId], 'Sermon created successfully');
            
        } catch (Exception $e) {
            sendError('Failed to create sermon: ' . $e->getMessage(), 500);
        }
        break;
        
    case 'PUT':
        // Update existing sermon
        parse_str(file_get_contents('php://input'), $data);
        $requiredFields = ['id', 'title', 'preacher', 'sermon_date'];
        validateRequiredFields($data, $requiredFields);
        
        $sermon = getSermonById($data['id']);
        if (!$sermon) {
            sendError('Sermon not found', 404);
        }
        
        try {
            $uploadDir = __DIR__ . '/../../uploads/sermons';
            
            // Handle file uploads if new files are provided
            $thumbnailUrl = $sermon['thumbnail_url'];
            $audioUrl = $sermon['audio_url'];
            $videoUrl = $sermon['video_url'];
            
            // Handle thumbnail upload
            if (isset($_FILES['thumbnail'])) {
                $newThumbnail = handleFileUpload('thumbnail', $uploadDir);
                if ($newThumbnail) {
                    // Delete old thumbnail if it exists
                    if ($thumbnailUrl && file_exists($uploadDir . '/' . $thumbnailUrl)) {
                        unlink($uploadDir . '/' . $thumbnailUrl);
                    }
                    $thumbnailUrl = $newThumbnail;
                }
            }
            
            // Handle audio upload
            if (isset($_FILES['audio'])) {
                $newAudio = handleFileUpload('audio', $uploadDir, [
                    'audio/mpeg', 'audio/wav', 'audio/mp3', 'audio/ogg'
                ]);
                if ($newAudio) {
                    // Delete old audio if it exists
                    if ($audioUrl && file_exists($uploadDir . '/' . $audioUrl)) {
                        unlink($uploadDir . '/' . $audioUrl);
                    }
                    $audioUrl = $newAudio;
                }
            }
            
            // Handle video upload or URL
            if (isset($_FILES['video'])) {
                $newVideo = handleFileUpload('video', $uploadDir, [
                    'video/mp4', 'video/webm', 'video/ogg'
                ]);
                if ($newVideo) {
                    // Delete old video if it exists and was an uploaded file
                    if ($videoUrl && strpos($videoUrl, 'http') !== 0 && file_exists($uploadDir . '/' . $videoUrl)) {
                        unlink($uploadDir . '/' . $videoUrl);
                    }
                    $videoUrl = $newVideo;
                }
            } elseif (isset($data['video_url'])) {
                // If video_url is explicitly set (could be empty to remove the URL)
                // Only update if it's different from the current value
                if ($data['video_url'] !== $sermon['video_url']) {
                    // If we had a local video file before, delete it
                    if ($videoUrl && strpos($videoUrl, 'http') !== 0 && file_exists($uploadDir . '/' . $videoUrl)) {
                        unlink($uploadDir . '/' . $videoUrl);
                    }
                    $videoUrl = $data['video_url'] ?: null;
                }
            }
            
            // Update sermon
            $stmt = $db->prepare("
                UPDATE sermons 
                SET title = ?, preacher = ?, bible_reference = ?, sermon_date = ?,
                    audio_url = ?, video_url = ?, description = ?, thumbnail_url = ?,
                    updated_at = NOW()
                WHERE id = ?
            
            ");
            
            $stmt->execute([
                $data['title'],
                $data['preacher'],
                $data['bible_reference'] ?? $sermon['bible_reference'],
                $data['sermon_date'],
                $audioUrl,
                $videoUrl,
                $data['description'] ?? $sermon['description'],
                $thumbnailUrl,
                $data['id']
            ]);
            
            sendSuccess(null, 'Sermon updated successfully');
            
        } catch (Exception $e) {
            sendError('Failed to update sermon: ' . $e->getMessage(), 500);
        }
        break;
        
    case 'DELETE':
        // Delete sermon
        $data = getJsonInput();
        if (!isset($data['id'])) {
            sendError('Sermon ID is required', 400);
        }
        
        $sermon = getSermonById($data['id']);
        if (!$sermon) {
            sendError('Sermon not found', 404);
        }
        
        try {
            $uploadDir = __DIR__ . '/../../uploads/sermons';
            
            // Delete associated files
            if ($sermon['thumbnail_url'] && file_exists($uploadDir . '/' . $sermon['thumbnail_url'])) {
                unlink($uploadDir . '/' . $sermon['thumbnail_url']);
            }
            
            if ($sermon['audio_url'] && strpos($sermon['audio_url'], 'http') !== 0 && 
                file_exists($uploadDir . '/' . $sermon['audio_url'])) {
                unlink($uploadDir . '/' . $sermon['audio_url']);
            }
            
            if ($sermon['video_url'] && strpos($sermon['video_url'], 'http') !== 0 && 
                file_exists($uploadDir . '/' . $sermon['video_url'])) {
                unlink($uploadDir . '/' . $sermon['video_url']);
            }
            
            // Delete sermon from database
            $db->prepare("DELETE FROM sermons WHERE id = ?")->execute([$data['id']]);
            
            sendSuccess(null, 'Sermon deleted successfully');
            
        } catch (Exception $e) {
            sendError('Failed to delete sermon: ' . $e->getMessage(), 500);
        }
        break;
        
    default:
        sendError('Method not allowed', 405);
}
