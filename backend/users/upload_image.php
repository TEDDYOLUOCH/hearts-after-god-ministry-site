<?php
/**
 * Handle file uploads for blog post images
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is authenticated and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

// Check if file was uploaded without errors
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $error = 'No file uploaded or upload error occurred.';
    if (isset($_FILES['file']['error'])) {
        $error = getUploadErrorMessage($_FILES['file']['error']);
    }
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => $error]));
}

// File validation
$file = $_FILES['file'];
$maxFileSize = 2 * 1024 * 1024; // 2MB
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Validate file size
if ($file['size'] > $maxFileSize) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'File size exceeds maximum allowed size of 2MB']));
}

// Validate file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Only JPG, PNG, GIF, and WebP images are allowed']));
}

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/../../../uploads/blog/' . date('Y/m/');
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$filename = uniqid('img_') . '.' . $fileExtension;
$destination = $uploadDir . $filename;
$relativePath = '/uploads/blog/' . date('Y/m/') . $filename;

// Move the uploaded file
if (move_uploaded_file($file['tmp_name'], $destination)) {
    // Return the URL to the uploaded file
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $fileUrl = $baseUrl . $relativePath;
    
    // Create a thumbnail for the image
    createThumbnail($destination, $uploadDir . 'thumbs/' . $filename, 300, 200);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'url' => $fileUrl,
        'path' => $relativePath,
        'filename' => $filename,
        'size' => $file['size'],
        'type' => $mimeType
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
}

/**
 * Get a user-friendly error message for file upload errors
 */
function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'A PHP extension stopped the file upload';
        default:
            return 'Unknown upload error';
    }
}

/**
 * Create a thumbnail for an image
 */
function createThumbnail($sourcePath, $destinationPath, $maxWidth, $maxHeight) {
    // Create the thumbnails directory if it doesn't exist
    $thumbDir = dirname($destinationPath);
    if (!file_exists($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }
    
    // Get the image info
    $info = getimagesize($sourcePath);
    if (!$info) {
        return false;
    }
    
    list($width, $height, $type) = $info;
    
    // Calculate the new dimensions while maintaining aspect ratio
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);
    
    // Create a new image with the new dimensions
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
        imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }
    
    // Load the source image based on its type
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }
    
    // Resize the image
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save the thumbnail
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($thumb, $destinationPath, 90);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($thumb, $destinationPath, 9);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($thumb, $destinationPath);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($thumb, $destinationPath, 90);
            break;
    }
    
    // Free up memory
    imagedestroy($source);
    imagedestroy($thumb);
    
    return $result;
}
