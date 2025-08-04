<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Database connection
require_once __DIR__ . '/config/db.php';

// Set CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get all gallery images from database with additional checks
try {
    $stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add file existence and URL information
    foreach ($images as &$image) {
        $imagePath = $image['image_path'] ?? '';
        
        // Check if path is absolute or relative
        if (!empty($imagePath)) {
            // Handle different path formats
            if (strpos($imagePath, '/hearts-after-god-ministry-site/') === 0) {
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
            } elseif (strpos($imagePath, 'http') === 0) {
                $fullPath = $imagePath; // Full URL
            } else {
                // Assume it's relative to the document root
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site/' . ltrim($imagePath, '/');
            }
            
            // Check if file exists
            $image['file_exists'] = file_exists($fullPath);
            $image['full_path'] = $fullPath;
            $image['web_url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . 
                              $_SERVER['HTTP_HOST'] . 
                              (strpos($imagePath, '/') === 0 ? '' : '/hearts-after-god-ministry-site/') . 
                              ltrim($imagePath, '/');
        } else {
            $image['file_exists'] = false;
            $image['full_path'] = 'No path specified';
            $image['web_url'] = 'No URL available';
        }
    }
    unset($image); // Break reference
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gallery Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .image-container { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; }
        .image-card { border: 1px solid #ddd; padding: 10px; border-radius: 5px; width: 300px; }
        .image-card img { max-width: 100%; height: auto; }
        .path-info { font-family: monospace; font-size: 12px; margin: 5px 0; }
        .exists-yes { color: green; }
        .exists-no { color: red; }
    </style>
</head>
<body>
    <h1>Gallery Debug</h1>
    <p>This page helps debug the gallery image paths and display.</p>
    
    <h2>Server Information</h2>
    <ul>
        <li>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?></li>
        <li>Request URI: <?php echo $_SERVER['REQUEST_URI']; ?></li>
        <li>Base URL: <?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?></li>
    </ul>
    
    <h2>Gallery Images</h2>
    <div class="image-container">
        <?php foreach ($images as $image): 
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $image['image_path'];
            <div class="image-card">
                <div class="path-info">ID: <?php echo htmlspecialchars($image['id']); ?></div>
                <div class="path-info">
                    <strong>Database Path:</strong> <?php echo htmlspecialchars($image['image_path']); ?>
                </div>
                <div class="path-info">
                    <strong>Full Server Path:</strong> <?php echo htmlspecialchars($image['full_path']); ?>
                </div>
                <div class="path-info">
                    <strong>Web URL:</strong> 
                    <a href="<?php echo htmlspecialchars($image['web_url']); ?>" target="_blank">
                        <?php echo htmlspecialchars($image['web_url']); ?>
                    </a>
                </div>
                <div class="path-info">
                    <strong>Exists:</strong> 
                    <span class="exists-<?php echo $image['file_exists'] ? 'yes' : 'no'; ?>">
                        <?php echo $image['file_exists'] ? 'Yes' : 'No'; ?>
                    </span>
                </div>
                <?php if ($image['file_exists'] && is_file($image['full_path'])): ?>
                    <img src="<?php echo htmlspecialchars($image['web_url']); ?>?t=<?php echo time(); ?>" 
                         alt="Gallery Image" 
                         style="max-height: 200px; width: auto;"
                         onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiB2aWV3Qm94PSIwIDAgNDAwIDMwMCI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iI2YzZjRmNiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiIGZpbGw9IiM2YzcyODAiPkVycm9yIGxvYWRpbmcgaW1hZ2U8L3RleHQ+PC9zdmc+'; this.alt='Error loading image';">
                <?php else: ?>
                    <div style="background: #f0f0f0; height: 150px; display: flex; align-items: center; justify-content: center; border: 1px dashed #999;">
                        Image not found or not accessible
                    </div>
                <?php endif; ?>
                <div class="path-info"><strong>Title:</strong> <?php echo htmlspecialchars($image['title'] ?? 'N/A'); ?></div>
                <div class="path-info"><strong>Description:</strong> <?php echo htmlspecialchars(substr($image['description'] ?? 'N/A', 0, 50)) . (strlen($image['description'] ?? '') > 50 ? '...' : ''); ?></div>
                <div class="path-info"><strong>Created:</strong> <?php echo htmlspecialchars($image['created_at'] ?? 'N/A'); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (!isset($_SESSION['admin_logged_in'])): ?>
        <div class="alert alert-warning">
            Note: You need to be logged in to the admin dashboard for uploads to work.
            <a href="/hearts-after-god-ministry-site/dashboard/login.php">Go to Login</a>
        </div>
    <?php else: ?>
        <h2>Upload Test</h2>
        <form action="/hearts-after-god-ministry-site/api/gallery/upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <div style="margin-bottom: 10px;">
                <label>Title: <input type="text" name="title" required></label>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Description: <textarea name="description" style="width: 100%;"></textarea></label>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Image: <input type="file" name="image" accept="image/*" required></label>
            </div>
            <button type="submit" class="btn">Upload Test Image</button>
        </form>
        <div id="uploadResult" style="margin-top: 10px; padding: 10px; display: none;"></div>
        
        <script>
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const resultDiv = document.getElementById('uploadResult');
            
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Uploading...';
            resultDiv.className = 'alert alert-info';
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin' // Important for sending cookies/session
                });
                
                const result = await response.json();
                
                if (result.success) {
                    resultDiv.innerHTML = 'Upload successful! Page will refresh...';
                    resultDiv.className = 'alert alert-success';
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    resultDiv.innerHTML = `Error: ${result.message || 'Unknown error'}`;
                    resultDiv.className = 'alert alert-danger';
                    console.error('Upload error:', result);
                }
            } catch (error) {
                resultDiv.innerHTML = 'Network error during upload';
                resultDiv.className = 'alert alert-danger';
                console.error('Upload error:', error);
            }
        });
        </script>
    <?php endif; ?>
</body>
</html>
