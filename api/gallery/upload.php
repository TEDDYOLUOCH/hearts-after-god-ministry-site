<?php
header('Content-Type: application/json');
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function for debugging
function logMessage($message, $data = null) {
    $logFile = __DIR__ . '/../../uploads/upload_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    if ($data !== null) {
        $logMessage .= 'Data: ' . print_r($data, true) . PHP_EOL;
    }
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    logMessage('Unauthorized access attempt', [
        'user_id' => $_SESSION['user_id'] ?? null,
        'user_role' => $_SESSION['user_role'] ?? null,
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Log the start of the upload process
logMessage('Starting file upload', [
    'post_data' => $_POST,
    'files' => $_FILES,
    'session' => $_SESSION
]);

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    logMessage('Invalid CSRF token', [
        'received_token' => $_POST['csrf_token'] ?? null,
        'expected_token' => $_SESSION['csrf_token'] ?? null
    ]);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/../../uploads/gallery/';
if (!file_exists($uploadDir)) {
    logMessage('Creating upload directory', ['path' => $uploadDir]);
    if (!mkdir($uploadDir, 0755, true)) {
        $error = error_get_last();
        logMessage('Failed to create upload directory', [
            'path' => $uploadDir,
            'error' => $error
        ]);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit;
    }
}

// Set maximum file size (5MB)
$maxFileSize = 5 * 1024 * 1024;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    logMessage('Checking uploaded files', $_FILES);

    if (!isset($_FILES['files'])) {
        throw new Exception('No files were uploaded');
    }

    // Initialize array to store uploaded files info
    $uploadedFiles = [];
    
    // Process each file
    $files = $_FILES['files'];
    $fileCount = count($files['name']);
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];
        
        $errorMessage = $errorMessages[$file['error']] ?? 'Unknown upload error';
        logMessage('Upload error', [
            'error_code' => $file['error'],
            'error_message' => $errorMessage,
            'file_info' => $file
        ]);
        throw new Exception($errorMessage);
    }
    
    // Check file size
    if ($file['size'] > $maxFileSize) {
        $errorMessage = 'File is too large. Maximum size allowed is 5MB';
        logMessage('File too large', [
            'file_size' => $file['size'],
            'max_size' => $maxFileSize
        ]);
        throw new Exception($errorMessage);
    }

    // Process each file
    for ($i = 0; $i < $fileCount; $i++) {
        try {
            // Skip if there was an error with this file
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
                ];
                
                $errorMessage = $errorMessages[$files['error'][$i]] ?? 'Unknown upload error';
                logMessage('Upload error', [
                    'file_index' => $i,
                    'error_code' => $files['error'][$i],
                    'error_message' => $errorMessage,
                    'file_info' => [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'size' => $files['size'][$i],
                        'tmp_name' => $files['tmp_name'][$i]
                    ]
                ]);
                continue; // Skip to next file
            }
            
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            $title = pathinfo($file['name'], PATHINFO_FILENAME);
            $description = '';

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Only JPG, PNG, GIF, and WebP images are allowed');
            }

            // Create uploads directory if it doesn't exist
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site/uploads/gallery/';
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception('Failed to create upload directory');
                }
            }

        // Generate unique filename with original extension
        $originalName = basename($file['name']);
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $uniqueId = uniqid('img_', true);
        $filename = $uniqueId . '.' . $fileExtension;
    
        // Set paths - store the full path relative to the site root
        $relativePath = 'uploads/gallery/' . $filename;
        $destination = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site/' . $relativePath;
    
            // Ensure upload directory exists
            if (!is_dir(dirname($destination))) {
                if (!mkdir(dirname($destination), 0755, true)) {
                    throw new Exception('Failed to create upload directory: ' . dirname($destination));
                }
            }
    
        // Ensure destination directory exists
        if (!is_dir(dirname($destination))) {
            if (!mkdir(dirname($destination), 0755, true)) {
                $error = error_get_last();
                logMessage('Failed to create directory', [
                    'directory' => dirname($destination),
                    'error' => $error
                ]);
                throw new Exception('Failed to create upload directory: ' . ($error['message'] ?? 'Unknown error'));
            }
        }
        
        // Move the uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $error = error_get_last();
            logMessage('Failed to move uploaded file', [
                'source' => $file['tmp_name'],
                'destination' => $destination,
                'error' => $error
            ]);
            throw new Exception('Failed to move uploaded file: ' . ($error['message'] ?? 'Unknown error'));
        }
    
            // Verify the file was saved and is readable
            if (!is_readable($destination)) {
                throw new Exception('Uploaded file is not readable');
            }
    
        // Get file info for response
        $fileInfo = [
            'id' => null,
            'filename' => $filename,
            'original_name' => $originalName,
            'size' => filesize($destination),
            'mime_type' => mime_content_type($destination),
            'path' => $relativePath,
            'url' => '/hearts-after-god-ministry-site/' . ltrim($relativePath, '/'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Save to database
        try {
            $stmt = $pdo->prepare("INSERT INTO gallery (title, description, image_path, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([
                $title ?: 'Untitled',
                $description ?: '',
                $relativePath  // Store relative path
            ]);
            
            $fileInfo['id'] = $pdo->lastInsertId();
            
            // Log the upload
            logMessage('File uploaded successfully', [
                'file_info' => $fileInfo,
                'file_exists' => file_exists($destination) ? 'yes' : 'no',
                'file_size' => filesize($destination) . ' bytes',
                'is_readable' => is_readable($destination) ? 'yes' : 'no',
                'file_perms' => substr(sprintf('%o', fileperms($destination)), -4)
            ]);
            
            // Add to response
            $uploadedFiles[] = $fileInfo;
            
        } catch (PDOException $e) {
            logMessage('Database error', [
                'error' => $e->getMessage(),
                'file_info' => $fileInfo
            ]);
            throw new Exception('Failed to save file information to database');
        }

        } catch (Exception $e) {
            logMessage('Error processing file: ' . $e->getMessage(), [
                'file_index' => $i,
                'file_name' => $files['name'][$i] ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            // Continue with next file even if one fails
            continue;
        }
    } // End of for loop

    // Return success response with all uploaded files
    if (count($uploadedFiles) > 0) {
        echo json_encode([
            'success' => true,
            'message' => count($uploadedFiles) . ' file(s) uploaded successfully',
            'data' => $uploadedFiles
        ]);
    } else {
        throw new Exception('No files were successfully uploaded');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
