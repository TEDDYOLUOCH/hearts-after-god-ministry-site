<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define paths
$baseDir = __DIR__ . '/uploads';
$galleryDir = $baseDir . '/gallery';

// Create directories if they don't exist
$directories = [
    $baseDir => 0755,
    $galleryDir => 0755
];

$results = [];

// Check and create directories
foreach ($directories as $dir => $permissions) {
    if (!file_exists($dir)) {
        if (mkdir($dir, $permissions, true)) {
            $results[] = "Created directory: $dir";
        } else {
            $results[] = "<span style='color:red'>Failed to create directory: $dir</span>";
            continue;
        }
    } else {
        $results[] = "Directory exists: $dir";
    }
    
    // Check if directory is writable
    if (!is_writable($dir)) {
        if (chmod($dir, $permissions)) {
            $results[] = "Set permissions on $dir to " . decoct($permissions);
        } else {
            $results[] = "<span style='color:red'>Failed to set permissions on $dir</span>";
        }
    } else {
        $results[] = "Directory is writable: $dir";
    }
}

// Create a test file to check write permissions
$testFile = $galleryDir . '/test_write.txt';
if (file_put_contents($testFile, 'test') !== false) {
    $results[] = "Successfully wrote to test file: $testFile";
    unlink($testFile);
} else {
    $results[] = "<span style='color:red'>Failed to write to test file: $testFile</span>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Directory Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>Upload Directory Check</h1>
    <ul>
        <?php foreach ($results as $result): ?>
            <li><?php echo $result; ?></li>
        <?php endforeach; ?>
    </ul>
    
    <h2>Current Directory Structure</h2>
    <pre>
<?php
function listDir($dir, $prefix = '') {
    $files = array_diff(scandir($dir), ['.', '..']);
    $output = '';
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        $output .= $prefix . $file . "\n";
        if (is_dir($path)) {
            $output .= listDir($path, $prefix . '    ');
        }
    }
    return $output;
}

echo listDir(__DIR__ . '/uploads');
?>
    </pre>
    
    <p><a href="/hearts-after-god-ministry-site/debug_gallery.php">Back to Gallery Debug</a></p>
</body>
</html>
