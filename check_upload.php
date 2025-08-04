<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if file uploads are enabled
$fileUploads = ini_get('file_uploads') ? 'Enabled' : 'Disabled';
$uploadMaxFilesize = ini_get('upload_max_filesize');
$postMaxSize = ini_get('post_max_size');
$uploadTmpDir = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
$maxFileUploads = ini_get('max_file_uploads');

// Check if upload_tmp_dir is writable
$isTmpWritable = is_writable($uploadTmpDir);

// Check uploads directory permissions
$uploadsDir = __DIR__ . '/uploads';
$isUploadsWritable = is_writable($uploadsDir);
$isGalleryDirWritable = is_writable($uploadsDir . '/gallery');

// Check if we can create a test file
$testFile = $uploadsDir . '/test_write.txt';
$canWriteTest = false;
if (@file_put_contents($testFile, 'test') !== false) {
    $canWriteTest = true;
    unlink($testFile);
}

// Check for common issues
$issues = [];
if (!$isTmpWritable) {
    $issues[] = "Temporary directory ($uploadTmpDir) is not writable";
}
if (!is_writable($uploadsDir)) {
    $issues[] = "Uploads directory ($uploadsDir) is not writable";
}
if (!is_writable($uploadsDir . '/gallery')) {
    $issues[] = "Gallery directory ({$uploadsDir}/gallery) is not writable";
}
if ($postMaxSize < $uploadMaxFilesize) {
    $issues[] = "post_max_size ($postMaxSize) is less than upload_max_filesize ($uploadMaxFilesize)";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Configuration Check</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        .ok { color: green; }
        .error { color: red; font-weight: bold; }
        pre { background: #f4f4f4; padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>File Upload Configuration Check</h1>
    
    <h2>PHP Settings</h2>
    <ul>
        <li>file_uploads: <span class="ok"><?php echo $fileUploads; ?></span></li>
        <li>upload_max_filesize: <?php echo $uploadMaxFilesize; ?></li>
        <li>post_max_size: <?php echo $postMaxSize; ?></li>
        <li>max_file_uploads: <?php echo $maxFileUploads; ?></li>
        <li>upload_tmp_dir: <?php echo $uploadTmpDir; ?> 
            (<?php echo $isTmpWritable ? '<span class="ok">Writable</span>' : '<span class="error">Not Writable</span>'; ?>)
        </li>
    </ul>
    
    <h2>Directory Permissions</h2>
    <ul>
        <li>Uploads directory (<?php echo $uploadsDir; ?>): 
            <?php echo $isUploadsWritable ? '<span class="ok">Writable</span>' : '<span class="error">Not Writable</span>'; ?>
        </li>
        <li>Gallery directory (<?php echo $uploadsDir; ?>/gallery): 
            <?php echo $isGalleryDirWritable ? '<span class="ok">Writable</span>' : '<span class="error">Not Writable</span>'; ?>
        </li>
        <li>Can write test file: 
            <?php echo $canWriteTest ? '<span class="ok">Yes</span>' : '<span class="error">No</span>'; ?>
        </li>
    </ul>
    
    <?php if (!empty($issues)): ?>
    <h2 class="error">Potential Issues</h2>
    <ul>
        <?php foreach ($issues as $issue): ?>
            <li class="error"><?php echo $issue; ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    
    <h2>Test File Upload</h2>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" onsubmit="return validateForm(this);">
        <div style="margin-bottom: 10px;">
            <input type="file" name="test_file" id="test_file" required>
            <div id="file-info" style="margin-top: 5px; color: #666;">No file selected</div>
        </div>
        <input type="submit" value="Test Upload" class="btn">
    </form>
    
    <script>
    // Update file info when a file is selected
    document.getElementById('test_file').addEventListener('change', function(e) {
        const fileInput = e.target;
        const fileInfo = document.getElementById('file-info');
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileInfo.innerHTML = `
                <strong>Selected file:</strong> ${file.name}<br>
                <strong>Type:</strong> ${file.type}<br>
                <strong>Size:</strong> ${(file.size / 1024).toFixed(2)} KB
            `;
        } else {
            fileInfo.textContent = 'No file selected';
        }
    });
    
    function validateForm(form) {
        const fileInput = form.test_file;
        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Please select a file to upload');
            return false;
        }
        return true;
    }
    </script>
    <style>
    .btn {
        background: #4CAF50;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn:hover {
        background: #45a049;
    }
    </style>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
        echo '<h3>Upload Test Results:</h3>';
        echo '<pre>';
        echo 'File Info:\n';
        print_r($_FILES['test_file']);
        
        $targetFile = $uploadsDir . '/' . basename($_FILES['test_file']['name']);
        
        if (move_uploaded_file($_FILES['test_file']['tmp_name'], $targetFile)) {
            echo "\nFile was successfully uploaded to: " . $targetFile;
            unlink($targetFile); // Clean up
        } else {
            echo "\nFailed to move uploaded file. Error: " . $_FILES['test_file']['error'];
            echo "\nError details: ";
            switch ($_FILES['test_file']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    echo 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    echo 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    echo 'The uploaded file was only partially uploaded';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    echo 'No file was uploaded';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    echo 'Missing a temporary folder';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    echo 'Failed to write file to disk';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    echo 'A PHP extension stopped the file upload';
                    break;
                default:
                    echo 'Unknown upload error';
            }
        }
        echo '</pre>';
    }
    ?>
    
    <h2>PHP Info</h2>
    <a href="phpinfo.php" target="_blank">View Full PHP Info</a>
</body>
</html>
