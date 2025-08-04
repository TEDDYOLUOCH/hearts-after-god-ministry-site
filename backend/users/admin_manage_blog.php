<?php
/**
 * Admin Blog Management
 * 
 * Handles CRUD operations for blog posts
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../../logs/php_errors.log');

// Include database configuration
require_once __DIR__ . '/../../config/db.php';

// Initialize error variable
$error = null;

// Create logs directory if it doesn't exist
$logDir = __DIR__ . '/../../../logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

// Function to handle file uploads
function handleFileUpload($fileInputName, $targetDir = '../uploads/blog/') {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fileInputName];
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed with error code: ' . $file['error']);
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Only JPG, PNG, and GIF files are allowed.');
    }
    
    // Create target directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    // Move the uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to move uploaded file.');
    }
    
    // Return the relative path
    return 'uploads/blog/' . $filename;
}

// Function to create a slug from title
function createSlug($title, $pdo, $excludeId = null) {
    // Replace non-alphanumeric characters with dashes
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
    $slug = trim($slug, '-');
    
    // If empty, create a default slug
    if (empty($slug)) {
        $slug = 'post-' . uniqid();
    }
    
    $originalSlug = $slug;
    $counter = 1;
    
    // Check if slug already exists
    while (true) {
        $query = "SELECT id FROM blog_posts WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        if (!$stmt->fetch()) {
            break;
        }
        
        $slug = $originalSlug . '-' . $counter++;
    }
    
    return $slug;
}

try {
    // Get database connection
    $pdo = getDbConnection();

    // Ensure only admin can access
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
        exit;
    }

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'create':
                case 'update':
                    $isUpdate = ($_POST['action'] === 'update');
                    $postId = $_POST['post_id'] ?? null;
                    
                    // Validate required fields
                    if (empty($_POST['title']) || empty($_POST['content'])) {
                        throw new Exception('Title and content are required.');
                    }
                    
                    // Handle file upload
                    $featuredImage = null;
                    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                        $featuredImage = handleFileUpload('featured_image');
                    } elseif ($isUpdate) {
                        // Keep existing image if not uploading a new one
                        $stmt = $pdo->prepare("SELECT featured_image FROM blog_posts WHERE id = ?");
                        $stmt->execute([$postId]);
                        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                        $featuredImage = $existing['featured_image'] ?? null;
                    }
                    
                    // Generate or get slug
                    $slug = $isUpdate 
                        ? createSlug($_POST['title'], $pdo, $postId)
                        : createSlug($_POST['title'], $pdo);
                    
                    // Prepare post data
                    $postData = [
                        'title' => $_POST['title'],
                        'slug' => $slug,
                        'content' => $_POST['content'],
                        'excerpt' => $_POST['excerpt'] ?? '',
                        'status' => $_POST['status'] ?? 'draft',
                        'author_id' => $_SESSION['user_id']
                    ];
                    
                    if ($featuredImage) {
                        $postData['featured_image'] = $featuredImage;
                    }
                    
                    if ($isUpdate) {
                        // Update existing post
                        $setClause = implode(', ', array_map(function($key) { 
                            return "$key = :$key"; 
                        }, array_keys($postData)));
                        
                        $sql = "UPDATE blog_posts SET $setClause, updated_at = NOW() WHERE id = :id";
                        $postData['id'] = $postId;
                        $message = 'Post updated successfully';
                    } else {
                        // Create new post
                        $columns = implode(', ', array_keys($postData));
                        $placeholders = ':' . implode(', :', array_keys($postData));
                        $sql = "INSERT INTO blog_posts ($columns, created_at, updated_at) VALUES ($placeholders, NOW(), NOW())";
                        $message = 'Post created successfully';
                    }
                    
                    // Execute the query
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($postData);
                    
                    if (!$isUpdate) {
                        $postId = $pdo->lastInsertId();
                    }
                    
                    // Handle categories
                    if (isset($_POST['categories']) && is_array($_POST['categories'])) {
                        // Delete existing categories for this post
                        $stmt = $pdo->prepare("DELETE FROM blog_post_categories WHERE post_id = ?");
                        $stmt->execute([$postId]);
                        
                        // Insert new categories
                        $stmt = $pdo->prepare("INSERT INTO blog_post_categories (post_id, category_id) VALUES (?, ?)");
                        foreach ($_POST['categories'] as $categoryId) {
                            $stmt->execute([$postId, $categoryId]);
                        }
                    }
                    
                    $_SESSION['success'] = $message;
                    header('Location: ?page=blog&action=edit&id=' . $postId);
                    exit;
                    
                case 'delete':
                    if (empty($_POST['post_id'])) {
                        throw new Exception('Post ID is required for deletion.');
                    }
                    
                    // Optional: Delete associated image
                    $stmt = $pdo->prepare("SELECT featured_image FROM blog_posts WHERE id = ?");
                    $stmt->execute([$_POST['post_id']]);
                    $post = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($post && !empty($post['featured_image'])) {
                        $imagePath = __DIR__ . '/../../' . $post['featured_image'];
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                    
                    // Delete the post
                    $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
                    $stmt->execute([$_POST['post_id']]);
                    
                    $_SESSION['success'] = 'Post deleted successfully';
                    header('Location: ?page=blog');
                    exit;
                    
                default:
                    throw new Exception('Invalid action specified.');
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    // Get all blog posts
    $posts = $pdo->query("SELECT p.*, u.name as author_name FROM blog_posts p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC")->fetchAll();
    
    // Get all categories
    $categories = $pdo->query("SELECT * FROM blog_categories ORDER BY name")->fetchAll();
    
    // Get categories for each post
    $postCategories = [];
    foreach ($posts as $post) {
        $stmt = $pdo->prepare("SELECT category_id FROM blog_post_categories WHERE post_id = ?");
        $stmt->execute([$post['id']]);
        $postCategories[$post['id']] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Get editing post if editing
    $editingPost = null;
    if (isset($_GET['edit'])) {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $editingPost = $stmt->fetch();
    }
    
} catch (Exception $e) {
    // Log the error
    error_log('Error in admin_manage_blog.php: ' . $e->getMessage());
    
    // Set error message
    $error = 'An error occurred while processing your request. Please try again.';
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        $error = $e->getMessage();
    }
}

// Clean any output buffers
while (ob_get_level() > 0) {
    ob_end_clean();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tiny.cloud/1/YOUR_TINYMCE_API_KEY/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#content',
            plugins: 'advlist autolink lists link image charmap preview anchor',
            toolbar_mode: 'floating',
            height: 500,
            content_style: 'body { font-family: Arial, sans-serif; font-size: 16px; }',
            images_upload_url: 'upload_image.php',
            automatic_uploads: true
        });
    </script>
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <!-- Blog Post Form -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    <?= isset($_GET['edit']) ? 'Edit Blog Post' : 'Add New Blog Post' ?>
                </h3>
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="<?= isset($_GET['edit']) ? 'update' : 'create' ?>">
                    <?php if (isset($_GET['edit'])): ?>
                        <input type="hidden" name="post_id" value="<?= htmlspecialchars($_GET['edit']) ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <div class="mt-1">
                                <input type="text" name="title" id="title" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    value="<?= isset($editingPost['title']) ? htmlspecialchars($editingPost['title']) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="sm:col-span-6">
                            <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                            <div class="mt-1">
                                <textarea id="content" name="content" rows="10" 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md"><?= isset($editingPost['content']) ? htmlspecialchars($editingPost['content']) : '' ?></textarea>
                            </div>
                        </div>
                        
                        <div class="sm:col-span-6">
                            <label for="excerpt" class="block text-sm font-medium text-gray-700">Excerpt</label>
                            <div class="mt-1">
                                <textarea id="excerpt" name="excerpt" rows="3" 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md"><?= isset($editingPost['excerpt']) ? htmlspecialchars($editingPost['excerpt']) : '' ?></textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">A short excerpt that summarizes your post.</p>
                        </div>
                        
                        <div class="sm:col-span-6">
                            <label for="featured_image" class="block text-sm font-medium text-gray-700">Featured Image</label>
                            <div class="mt-1">
                                <input type="file" name="featured_image" id="featured_image" 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <?php if (isset($editingPost['featured_image']) && !empty($editingPost['featured_image'])): ?>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Current image:</p>
                                    <img src="/hearts-after-god-ministry-site/<?= htmlspecialchars($editingPost['featured_image']) ?>" 
                                         alt="Featured Image" class="h-32 w-auto mt-2">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="sm:col-span-3">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <div class="mt-1">
                                <select name="status" id="status" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="draft" <?= (isset($editingPost['status']) && $editingPost['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= (isset($editingPost['status']) && $editingPost['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categories</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                <?php foreach ($categories as $category): ?>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="categories[]" id="category-<?= $category['id'] ?>" 
                                            value="<?= $category['id'] ?>"
                                            <?= (isset($editingPost) && in_array($category['id'], $postCategories[$editingPost['id']] ?? [])) ? 'checked' : '' ?>
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="category-<?= $category['id'] ?>" class="ml-2 block text-sm text-gray-700">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="sm:col-span-6 pt-5">
                            <div class="flex justify-end space-x-3">
                                <?php if (isset($_GET['edit'])): ?>
                                    <a href="?page=blog" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Cancel
                                    </a>
                                <?php endif; ?>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <?= isset($_GET['edit']) ? 'Update Post' : 'Create Post' ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Blog Posts List -->
        <?php if (!isset($_GET['edit'])): ?>
        <div class="mt-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h2 class="text-xl font-semibold text-gray-900">Blog Posts</h2>
                    <p class="mt-2 text-sm text-gray-700">A list of all blog posts in the system.</p>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                    <a href="?page=blog&action=new" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                        Add New Post
                    </a>
                </div>
            </div>
            
            <div class="mt-8 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Title</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Author</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Created</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <?php foreach ($posts as $post): ?>
                                        <tr>
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                <?= htmlspecialchars($post['title']) ?>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <?= htmlspecialchars($post['author_name'] ?? 'Unknown') ?>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $post['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                    <?= ucfirst($post['status']) ?>
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <?= date('M j, Y', strtotime($post['created_at'])) ?>
                                            </td>
                                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <a href="?page=blog&edit=<?= $post['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this post?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Auto-generate slug from title
        document.getElementById('title')?.addEventListener('input', function() {
            const slugInput = document.getElementById('slug');
            if (slugInput) {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^\w\s-]/g, '') // Remove special chars
                    .replace(/\s+/g, '-')      // Replace spaces with -
                    .replace(/--+/g, '-')       // Replace multiple - with single -
                    .trim();
                slugInput.value = slug;
            }
        });
    </script>
</body>
</html>
                    
                case 'delete':
                    if (empty($_POST['post_id'])) {
                        throw new Exception('Post ID is required for deletion.');
                    }
                    
                    // Optional: Delete associated image
                    $stmt = $pdo->prepare("SELECT featured_image FROM blog_posts WHERE id = ?");
                    $stmt->execute([$_POST['post_id']]);
                    $post = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($post && !empty($post['featured_image'])) {
                        $imagePath = __DIR__ . '/../../' . $post['featured_image'];
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                    
                    // Delete the post
                    $pdo->prepare("DELETE FROM blog_post_categories WHERE post_id = ?")->execute([$_POST['post_id']]);
                    $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$_POST['post_id']]);
                    
                    $_SESSION['success'] = 'Post deleted successfully';
                    header('Location: ?page=blog');
                    exit;
                    
                default:
                    throw new Exception('Invalid action specified.');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '?page=blog'));
            exit;
        }
}

// Clean any unwanted output but keep one buffer level
while (ob_get_level() > 1) {
    ob_end_clean();
}

// Get all blog posts
$posts = $pdo->query("SELECT p.*, u.name as author_name FROM blog_posts p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC")->fetchAll();

// Get all categories
$categories = $pdo->query("SELECT * FROM blog_categories ORDER BY name")->fetchAll();

// Get categories for each post
$postCategories = [];
foreach ($posts as $post) {
    $stmt = $pdo->prepare("SELECT category_id FROM blog_post_categories WHERE post_id = ?");
    $stmt->execute([$post['id']]);
    $postCategories[$post['id']] = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get editing post if editing
$editingPost = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editingPost = $stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    <?= isset($_GET['edit']) ? 'Edit Blog Post' : 'Add New Blog Post' ?>
                </h3>
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="<?= isset($_GET['edit']) ? 'update' : 'create' ?>">
                    <?php if (isset($_GET['edit'])): ?>
                        <input type="hidden" name="post_id" value="<?= htmlspecialchars($_GET['edit']) ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <div class="mt-1">
                                <input type="text" name="title" id="title" required
                                    value="<?= htmlspecialchars($editingPost['title'] ?? '') ?>"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                        
                        <div class="sm:col-span-6">
                            <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                            <div class="mt-1">
                                <input type="text" name="slug" id="slug" required
                                    value="<?= htmlspecialchars($editingPost['slug'] ?? '') ?>"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <p class="mt-1 text-sm text-gray-500">URL-friendly version of the title. Will be auto-generated if left empty.</p>
                            </div>
                        </div>
                        
                        <div class="sm:col-span-6">
                            <label for="excerpt" class="block text-sm font-medium text-gray-700">Excerpt</label>
                            <div class="mt-1">
                                <textarea name="excerpt" id="excerpt" rows="3" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"><?= htmlspecialchars($editingPost['excerpt'] ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <div class="sm:col-span-6">
                            <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                            <div class="mt-1">
                                <textarea name="content" id="content" rows="10" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"><?= htmlspecialchars($editingPost['content'] ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <div class="sm:col-span-6">
                            <label for="featured_image" class="block text-sm font-medium text-gray-700">Featured Image URL</label>
                            <div class="mt-1">
                                <input type="url" name="featured_image" id="featured_image"
                                    value="<?= htmlspecialchars($editingPost['featured_image'] ?? '') ?>"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                        
                        <div class="sm:col-span-3">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <div class="mt-1">
                                <select name="status" id="status" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="draft" <?= (isset($editingPost['status']) && $editingPost['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= (isset($editingPost['status']) && $editingPost['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categories</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                <?php foreach ($categories as $category): ?>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="categories[]" id="category-<?= $category['id'] ?>" 
                                            value="<?= $category['id'] ?>"
                                            <?= (isset($editingPost) && in_array($category['id'], $postCategories[$editingPost['id']] ?? [])) ? 'checked' : '' ?>
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="category-<?= $category['id'] ?>" class="ml-2 block text-sm text-gray-700">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="sm:col-span-6 pt-5">
                            <div class="flex justify-end space-x-3">
                                <?php if (isset($_GET['edit'])): ?>
                                    <a href="?page=blog" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Cancel
                                    </a>
                                <?php endif; ?>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <?= isset($_GET['edit']) ? 'Update Post' : 'Create Post' ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Blog Posts List -->
        <?php if (!isset($_GET['edit'])): ?>
        <div class="mt-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h2 class="text-xl font-semibold text-gray-900">Blog Posts</h2>
                    <p class="mt-2 text-sm text-gray-700">A list of all blog posts in the system.</p>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                    <a href="?page=blog&action=new" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                        Add New Post
                    </a>
                </div>
            </div>
            
            <div class="mt-8 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Title</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Author</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Created</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <?php foreach ($posts as $post): ?>
                                        <tr>
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                <?= htmlspecialchars($post['title']) ?>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <?= htmlspecialchars($post['author_name'] ?? 'Unknown') ?>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $post['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                    <?= ucfirst($post['status']) ?>
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <?= date('M j, Y', strtotime($post['created_at'])) ?>
                                            </td>
                                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <a href="?page=blog&edit=<?= $post['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this post?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#content',
            plugins: 'advlist autolink lists link image charmap print preview anchor',
            toolbar_mode: 'floating',
            height: 500,
            content_style: 'body { font-family: Arial, sans-serif; font-size: 16px; }',
            images_upload_url: '/hearts-after-god-ministry-site/backend/upload_image.php',
            automatic_uploads: true
        });
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Auto-generate slug from title
        document.getElementById('title')?.addEventListener('input', function() {
            const slugInput = document.getElementById('slug');
            if (slugInput) {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^\w\s-]/g, '') // Remove special chars
                    .replace(/\s+/g, '-')      // Replace spaces with -
                    .replace(/--+/g, '-')       // Replace multiple - with single -
                    .trim();
                slugInput.value = slug;
            }
        });
    </script>
</body>
</html>
