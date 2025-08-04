<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    exit;
}
// Connect to the database
require_once __DIR__ . '/../../config/db.php';

// Handle image upload
$error_message = '';
$success_message = '';
if (isset($_POST['upload_image'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site/uploads/gallery/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $allowed)) {
            $error_message = 'Invalid image type. Allowed: jpg, jpeg, png, gif.';
        } else {
            $new_name = 'img_' . uniqid() . '.' . $ext;
            $target = $upload_dir . $new_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $db_path = '/uploads/gallery/' . $new_name;
                $stmt = $pdo->prepare('INSERT INTO gallery (title, description, image_path) VALUES (?, ?, ?)');
                if ($stmt->execute([$title, $description, $db_path])) {
                    $success_message = 'Image uploaded successfully!';
                } else {
                    $error_message = 'Failed to save image to database.';
                }
            } else {
                $error_message = 'Failed to upload image.';
            }
        }
    } else {
        $error_message = 'Please select an image to upload.';
    }
    // Refresh images after upload
    if ($success_message) {
        $stmt = $pdo->query('SELECT * FROM gallery ORDER BY created_at DESC');
        $images = $stmt->fetchAll();
    }
}

// Handle delete
if (isset($_POST['delete_image']) && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $pdo->prepare('SELECT image_path FROM gallery WHERE id = ?');
    $stmt->execute([$delete_id]);
    $row = $stmt->fetch();
    if ($row) {
        $file_path = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site' . $row['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $stmt = $pdo->prepare('DELETE FROM gallery WHERE id = ?');
        $stmt->execute([$delete_id]);
        $success_message = 'Image deleted successfully!';
        // Refresh images
        $stmt = $pdo->query('SELECT * FROM gallery ORDER BY created_at DESC');
        $images = $stmt->fetchAll();
    }
}
// Handle update
if (isset($_POST['update_image']) && isset($_POST['update_id'])) {
    $update_id = intval($_POST['update_id']);
    $new_title = trim($_POST['new_title']);
    $new_desc = trim($_POST['new_desc']);
    $stmt = $pdo->prepare('UPDATE gallery SET title = ?, description = ? WHERE id = ?');
    if ($stmt->execute([$new_title, $new_desc, $update_id])) {
        $success_message = 'Image updated successfully!';
        // Refresh images
        $stmt = $pdo->query('SELECT * FROM gallery ORDER BY created_at DESC');
        $images = $stmt->fetchAll();
    } else {
        $error_message = 'Failed to update image.';
    }
    if (!empty($_FILES['new_image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['new_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $allowed)) {
            $error_message = 'Invalid image type. Allowed: jpg, jpeg, png, gif.';
        } else {
            $new_name = 'img_' . uniqid() . '.' . $ext;
            $target = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site/uploads/gallery/' . $new_name;
            if (move_uploaded_file($_FILES['new_image']['tmp_name'], $target)) {
                $db_path = '/uploads/gallery/' . $new_name;
                $stmt = $pdo->prepare('UPDATE gallery SET image_path = ? WHERE id = ?');
                if ($stmt->execute([$db_path, $update_id])) {
                    $success_message = 'Image updated successfully!';
                } else {
                    $error_message = 'Failed to update image.';
                }
            } else {
                $error_message = 'Failed to upload new image.';
            }
        }
    }
}

// Fetch gallery images
$images = [];
try {
    // Get all gallery items
    $stmt = $pdo->query('SELECT * FROM gallery ORDER BY created_at DESC');
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get list of all image files in the uploads directory
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site/uploads/gallery/';
    $image_files = [];
    if (is_dir($upload_dir)) {
        $files = array_diff(scandir($upload_dir), ['.', '..']);
        foreach ($files as $file) {
            if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                $image_files[] = $file;
            }
        }
    }
    
    // Match database entries with image files using ID as the connection
    foreach ($images as &$image) {
        $image_id = $image['id'];
        $matching_file = '';
        
        // Try to find a file that starts with the ID
        foreach ($image_files as $file) {
            if (strpos($file, 'img_' . $image_id . '_') === 0 || 
                strpos($file, $image_id . '.') !== false) {
                $matching_file = $file;
                break;
            }
        }
        
        // If no match found, try to match by position (not ideal but works as fallback)
        if (empty($matching_file) && isset($image_files[$image_id - 1])) {
            $matching_file = $image_files[$image_id - 1];
        }
        
        $image['image_url'] = !empty($matching_file) 
            ? '/hearts-after-god-ministry-site/uploads/gallery/' . $matching_file 
            : '';
    }
    unset($image); // Break the reference
    
} catch (PDOException $e) {
    die('<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
        <p class="font-bold">Database Error</p>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
    </div>');
}
?>

<div class="max-w-7xl mx-auto">
    <!-- Image Upload Form -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Add New Image</h2>
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                <input type="text" name="title" id="title" required 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="2"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Image *</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Upload a file</span>
                                <input id="image" name="image" type="file" class="sr-only" accept="image/*" required>
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" name="upload_image" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Upload Image
                </button>
            </div>
        </form>
    </div>

    <!-- Gallery Grid -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Gallery Images</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (empty($images)): ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500">No images found in the gallery.</p>
                </div>
            <?php else: ?>
                <?php foreach ($images as $img): ?>
                    <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                            <?php if (!empty($img['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($img['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($img['title']); ?>" 
                                     class="h-full w-full object-cover"
                                     onerror="this.src='/hearts-after-god-ministry-site/assets/img/placeholder.jpg'">
                            <?php else: ?>
                                <div class="text-center p-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-xs text-gray-500 mt-2">No image available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($img['title']); ?></h3>
                            <?php if (!empty($img['description'])): ?>
                                <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($img['description']); ?></p>
                            <?php endif; ?>
                            <div class="mt-3 flex justify-between items-center">
                                <span class="text-xs text-gray-500">
                                    <?php echo date('M j, Y', strtotime($img['created_at'])); ?>
                                </span>
                                <div class="flex space-x-2">
                                    <form method="post" class="inline-block">
                                        <input type="hidden" name="edit_id" value="<?php echo $img['id']; ?>">
                                        <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Edit
                                        </button>
                                    </form>
                                    <span class="text-gray-300">|</span>
                                    <form method="post" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $img['id']; ?>">
                                        <button type="submit" name="delete_image" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
    <?php if (isset($_POST['edit_id'])): ?>
    window.addEventListener('DOMContentLoaded', function() {
        var card = document.getElementById('img-card-<?php echo (int)$_POST['edit_id']; ?>');
        if (card) {
            card.scrollIntoView({behavior: 'smooth', block: 'center'});
            card.classList.add('ring', 'ring-blue-400');
            setTimeout(() => card.classList.remove('ring', 'ring-blue-400'), 1500);
        }
    });
    <?php endif; ?>
</script>
