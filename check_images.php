<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require_once __DIR__ . '/config/db.php';

// Get all gallery images
try {
    $stmt = $pdo->query("SELECT id, title, image_path, created_at FROM gallery ORDER BY created_at DESC");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Image Checker</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .image { margin: 20px 0; padding: 10px; border: 1px solid #ddd; }
        .exists { color: green; }
        .missing { color: red; }
        img { max-width: 200px; max-height: 200px; border: 1px solid #eee; }
    </style>
</head>
<body>
    <h1>Image Checker</h1>
    <p>Found <?php echo count($images); ?> images in the database.</p>
    
    <?php foreach ($images as $image): 
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site' . $image['image_path'];
        $exists = file_exists($fullPath);
    ?>
    <div class="image">
        <h3>ID: <?php echo $image['id']; ?> - <?php echo htmlspecialchars($image['title']); ?></h3>
        <p>Path: <?php echo htmlspecialchars($image['image_path']); ?></p>
        <p>Full Path: <?php echo htmlspecialchars($fullPath); ?></p>
        <p>Status: 
            <span class="<?php echo $exists ? 'exists' : 'missing'; ?>">
                <?php echo $exists ? 'EXISTS' : 'MISSING'; ?>
            </span>
        </p>
        <?php if ($exists): ?>
            <img src="<?php echo $image['image_path']; ?>" alt="<?php echo htmlspecialchars($image['title']); ?>">
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    
    <h2>Server Info</h2>
    <p>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
    <p>PHP Version: <?php echo phpversion(); ?></p>
</body>
</html>
