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

// Initialize variables
$error = null;
$success = null;
$post = [
    'id' => null,
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '',
    'status' => 'draft',
    'featured_image' => '',
    'meta_title' => '',
    'meta_description' => '',
    'category_ids' => []
];
$categories = [];

// Check if we're editing an existing post
$is_edit = isset($_GET['edit']) && is_numeric($_GET['edit']);
$post_id = $is_edit ? (int)$_GET['edit'] : null;

// Get database connection
try {
    $pdo = getDbConnection();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Invalid CSRF token. Please try again.';
        } else {
            // Sanitize input
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived']) ? $_POST['status'] : 'draft';
            $meta_title = trim($_POST['meta_title'] ?? '');
            $meta_description = trim($_POST['meta_description'] ?? '');
            $category_ids = isset($_POST['categories']) && is_array($_POST['categories']) ? 
                array_map('intval', $_POST['categories']) : [];
            
            // Validate required fields
            if (empty($title)) {
                $error = 'Title is required.';
            } elseif (empty($slug)) {
                $error = 'Slug is required.';
            } else {
                // Generate slug if empty
                if (empty($slug)) {
                    $slug = createSlug($title, $pdo, $post_id);
                } else {
                    $slug = createSlug($slug, $pdo, $post_id);
                }
                
                // Handle featured image upload
                $featured_image = $post['featured_image'] ?? '';
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = handleFileUpload('featured_image');
                    if (isset($upload_result['error'])) {
                        $error = $upload_result['error'];
                    } else {
                        $featured_image = $upload_result['path'];
                    }
                }
                
                if (!$error) {
                    $pdo->beginTransaction();
                    
                    try {
                        // Get current user ID from session
                        $user_id = $_SESSION['user_id'] ?? 1; // Fallback to admin user if not set
                        
                        if ($is_edit && $post_id) {
                            // Update existing post
                            $stmt = $pdo->prepare("UPDATE blog_posts SET 
                                title = :title,
                                slug = :slug,
                                excerpt = :excerpt,
                                content = :content,
                                status = :status,
                                featured_image = :featured_image,
                                meta_title = :meta_title,
                                meta_description = :meta_description,
                                updated_at = NOW()
                                WHERE id = :id");
                                
                            $stmt->execute([
                                ':title' => $title,
                                ':slug' => $slug,
                                ':excerpt' => $excerpt,
                                ':content' => $content,
                                ':status' => $status,
                                ':featured_image' => $featured_image,
                                ':meta_title' => $meta_title,
                                ':meta_description' => $meta_description,
                                ':id' => $post_id
                            ]);
                            
                            $success = 'Post updated successfully!';
                        } else {
                            // Create new post
                            $stmt = $pdo->prepare("INSERT INTO blog_posts 
                                (title, slug, excerpt, content, status, featured_image, meta_title, meta_description, author_id, created_at, updated_at)
                                VALUES (:title, :slug, :excerpt, :content, :status, :featured_image, :meta_title, :meta_description, :author_id, NOW(), NOW())");
                                
                            $stmt->execute([
                                ':title' => $title,
                                ':slug' => $slug,
                                ':excerpt' => $excerpt,
                                ':content' => $content,
                                ':status' => $status,
                                ':featured_image' => $featured_image,
                                ':meta_title' => $meta_title,
                                ':meta_description' => $meta_description,
                                ':author_id' => $user_id
                            ]);
                            
                            $post_id = $pdo->lastInsertId();
                            $success = 'Post created successfully!';
                        }
                        
                        // Update categories
                        if ($post_id) {
                            // Remove existing categories
                            $stmt = $pdo->prepare("DELETE FROM blog_post_categories WHERE post_id = ?");
                            $stmt->execute([$post_id]);
                            
                            // Add new categories
                            if (!empty($category_ids)) {
                                $stmt = $pdo->prepare("INSERT INTO blog_post_categories (post_id, category_id) VALUES (?, ?)");
                                foreach ($category_ids as $category_id) {
                                    $stmt->execute([$post_id, $category_id]);
                                }
                            }
                            
                            $pdo->commit();
                            
                            // Redirect to list view with success message
                            $_SESSION['success'] = $success;
                            header('Location: admin_blog_list.php');
                            exit;
                        }
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error = 'An error occurred while saving the post: ' . $e->getMessage();
                        error_log($error);
                    }
                }
            }
        }
    }
    
    // Handle delete action
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $post_id = (int)$_GET['delete'];
        
        try {
            // Begin transaction
            $pdo->beginTransaction();
            
            // Delete from blog_post_categories first (foreign key constraint)
            $stmt = $pdo->prepare("DELETE FROM blog_post_categories WHERE post_id = ?");
            $stmt->execute([$post_id]);
            
            // Then delete the post
            $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
            $stmt->execute([$post_id]);
            
            $pdo->commit();
            
            // Redirect to list view with success message
            $_SESSION['success'] = 'Post deleted successfully!';
            header('Location: admin_blog_list.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'An error occurred while deleting the post: ' . $e->getMessage();
            error_log($error);
        }
    }
    
    // Load post data if editing
    if ($is_edit && $post_id) {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) {
            $error = 'Post not found.';
            $is_edit = false;
            $post_id = null;
        } else {
            // Get post categories
            $stmt = $pdo->prepare("SELECT category_id FROM blog_post_categories WHERE post_id = ?");
            $stmt->execute([$post_id]);
            $post['category_ids'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }
    
    // Get all categories for the form
    $categories = $pdo->query("SELECT * FROM categories WHERE type = 'blog' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate CSRF token if not exists
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    error_log($error);
}

// Set page title
$page_title = $is_edit ? 'Edit Blog Post' : 'Create New Blog Post';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        .status-draft { background-color: #fef3c7; color: #92400e; }
        .status-published { background-color: #d1fae5; color: #065f46; }
        .status-archived { background-color: #e5e7eb; color: #374151; }
        .drag-active { border-color: #4f46e5; background-color: #eef2ff; }
        #featured-image-preview { display: none; }
        #featured-image-preview.has-image { display: block; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back to list -->
        <div class="mb-6">
            <a href="admin_blog_list.php" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to Blog Posts
            </a>
        </div>

        <!-- Page header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    <?= htmlspecialchars($page_title) ?>
                </h2>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <?php if ($is_edit): ?>
                    <a href="admin_blog_list.php?delete=<?= $post['id'] ?>" 
                       onclick="return confirm('Are you sure you want to delete this post? This action cannot be undone.');"
                       class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Delete Post
                    </a>
                <?php endif; ?>
                <button type="submit" form="blog-post-form" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <?= $is_edit ? 'Update Post' : 'Publish Post' ?>
                </button>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form id="blog-post-form" method="POST" enctype="multipart/form-data" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <!-- Title -->
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" required
                               value="<?= htmlspecialchars($post['title']) ?>"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <!-- Slug -->
                    <div class="mb-6">
                        <label for="slug" class="block text-sm font-medium text-gray-700">Slug <span class="text-red-500">*</span></label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                <?= htmlspecialchars($_SERVER['HTTP_HOST'] . '/blog/') ?>
                            </span>
                            <input type="text" name="slug" id="slug" required
                                   value="<?= htmlspecialchars($post['slug']) ?>"
                                   class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">The slug is the URL-friendly version of the name.</p>
                    </div>

                    <!-- Excerpt -->
                    <div class="mb-6">
                        <label for="excerpt" class="block text-sm font-medium text-gray-700">Excerpt</label>
                        <div class="mt-1">
                            <textarea id="excerpt" name="excerpt" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md"><?= htmlspecialchars($post['excerpt']) ?></textarea>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">A short description of your post for search engines and social sharing.</p>
                    </div>

                    <!-- Content -->
                    <div class="mb-6">
                        <label for="content" class="block text-sm font-medium text-gray-700">Content <span class="text-red-500">*</span></label>
                        <div class="mt-1">
                            <textarea id="content" name="content" rows="20" required class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md"><?= htmlspecialchars($post['content']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Right sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Status -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Status</h3>
                            <div class="mt-4">
                                <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                    <option value="archived" <?= $post['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Featured Image</h3>
                            <div class="mt-4">
                                <div id="featured-image-upload" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg id="upload-icon" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div id="featured-image-preview" class="text-center <?= !empty($post['featured_image']) ? 'has-image' : '' ?>">
                                            <?php if (!empty($post['featured_image'])): ?>
                                                <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="Featured Image" class="mx-auto h-32 w-auto object-cover rounded">
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="featured-image" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload a file</span>
                                                <input id="featured-image" name="featured_image" type="file" class="sr-only">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    mkdir($logDir, 0755, true);
}

// Categories -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Categories</h3>
                            <div class="mt-4 space-y-2">
                                <?php foreach ($categories as $category): ?>
                                    <div class="flex items-center">
                                        <input id="category-<?= $category['id'] ?>" 
                                               name="categories[]" 
                                               type="checkbox" 
                                               value="<?= $category['id'] ?>"
                                               <?= in_array($category['id'], $post['category_ids']) ? 'checked' : '' ?>
                                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="category-<?= $category['id'] ?>" 
                                               class="ml-3 block text-sm font-medium text-gray-700">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- SEO -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">SEO</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="meta_title" class="block text-sm font-medium text-gray-700">
                                        Meta Title
                                    </label>
                                    <input type="text" 
                                           name="meta_title" 
                                           id="meta_title"
                                           value="<?= htmlspecialchars($post['meta_title']) ?>"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <p class="mt-1 text-xs text-gray-500">Recommended: 50-60 characters</p>
                                </div>
                                <div>
                                    <label for="meta_description" class="block text-sm font-medium text-gray-700">
                                        Meta Description
                                    </label>
                                    <textarea id="meta_description" 
                                              name="meta_description" 
                                              rows="3" 
                                              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md"><?= htmlspecialchars($post['meta_description']) ?></textarea>
                                    <p class="mt-1 text-xs text-gray-500">Recommended: 150-160 characters</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Auto-generate slug from title
        document.getElementById('title').addEventListener('blur', function() {
            const title = this.value;
            const slugInput = document.getElementById('slug');
            
            if (!slugInput.value) {
                fetch('generate_slug.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'text=' + encodeURIComponent(title) + '&table=blog_posts&field=slug'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        slugInput.value = data.slug;
                    }
                });
            }
        });

        // Initialize TinyMCE
        tinymce.init({
            selector: '#content',
            plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            height: 500,
            menubar: false,
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; line-height: 1.6; }',
            file_picker_callback: function (callback, value, meta) {
                // File upload logic here
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                
                input.onchange = function () {
                    const file = this.files[0];
                    const formData = new FormData();
                    formData.append('file', file);
                    
                    fetch('upload_image.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            callback(data.url);
                        } else {
                            alert('Error uploading image: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error uploading image');
                    });
                };
                
                input.click();
            }
        });

        // Featured image upload handling
        const featuredImageInput = document.getElementById('featured-image');
        const featuredImagePreview = document.getElementById('featured-image-preview');
        const uploadIcon = document.getElementById('upload-icon');
        const dropZone = document.getElementById('featured-image-upload');

        // Handle file selection
        featuredImageInput.addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Featured Image Preview';
                    img.className = 'mx-auto h-32 w-auto object-cover rounded';
                    
                    featuredImagePreview.innerHTML = '';
                    featuredImagePreview.appendChild(img);
                    featuredImagePreview.classList.add('has-image');
                    uploadIcon.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropZone.classList.add('drag-active');
        }

        function unhighlight() {
            dropZone.classList.remove('drag-active');
        }

        // Handle dropped files
        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                featuredImageInput.files = files;
                const event = new Event('change');
                featuredImageInput.dispatchEvent(event);
            }
        }
    </script>
</body>
</html>


<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="/hearts-after-god-ministry-site/dashboard/js/blog-posts-manager.js"></script>
    <script src="/hearts-after-god-ministry-site/dashboard/js/blog-realtime.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            },
            plugins: [
                require('@tailwindcss/forms')({
                    strategy: 'class',
                }),
            ]
        }

        // Initialize TinyMCE with enhanced configuration
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '#content',
                height: 500,
                menubar: 'edit insert view format table tools help',
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen codesample',
                    'insertdatetime media table paste code help wordcount',
                    'autoresize codesample hr pagebreak nonbreaking',
                    'textcolor colorpicker textpattern'
                ],
                toolbar: 'undo redo | formatselect | bold italic backcolor | \
                    alignleft aligncenter alignright alignjustify | \
                    bullist numlist outdent indent | removeformat | \
                    codesample code | table tabledelete | \
                    tableprops tablerowprops tablecellprops | \
                    tableinsertrowbefore tableinsertrowafter tabledeleterow | \
                    tableinsertcolbefore tableinsertcolafter tabledeletecol | help',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333; }',
                
                // Disable image uploads in the editor
                images_upload_url: 'postAcceptor.php',
                images_upload_handler: function (blobInfo, success, failure) {
                    failure('Image uploads are disabled. Please use the Featured Image upload section above.');
                },
                
                // Auto-resize the editor to fit content
                autoresize_bottom_margin: 50,
                
                // Add paste as plain text by default
                paste_as_text: true,
                paste_data_images: false,
                paste_auto_cleanup_on_paste: true,
                paste_remove_styles: true,
                paste_remove_styles_if_webkit: true,
                paste_strip_class_attributes: 'all',
                
                // Improve accessibility
                a11y_advanced_options: true,
                
                // Better table handling
                table_default_attributes: {
                    'class': 'table-auto w-full border-collapse mb-4',
                    'border': '1',
                    'cellspacing': '0',
                    'cellpadding': '5'
                },
                table_default_styles: {
                    'width': '100%',
                    'border-collapse': 'collapse',
                    'margin-bottom': '1rem'
                },
                table_class_list: [
                    {title: 'None', value: 'table-auto'},
                    {title: 'Striped', value: 'table-striped'},
                    {title: 'Bordered', value: 'table-bordered'}
                ],
                
                // Custom styles for the editor
                style_formats: [
                    { 
                        title: 'Headings', 
                        items: [
                            { title: 'Heading 2', format: 'h2' },
                            { title: 'Heading 3', format: 'h3' },
                            { title: 'Heading 4', format: 'h4' },
                            { title: 'Heading 5', format: 'h5' },
                            { title: 'Heading 6', format: 'h6' }
                        ]
                    },
                    { 
                        title: 'Inline', 
                        items: [
                            { title: 'Bold', icon: 'bold', format: 'bold' },
                            { title: 'Italic', icon: 'italic', format: 'italic' },
                            { title: 'Underline', icon: 'underline', format: 'underline' },
                            { title: 'Strikethrough', icon: 'strikethrough', format: 'strikethrough' },
                            { title: 'Superscript', icon: 'superscript', format: 'superscript' },
                            { title: 'Subscript', icon: 'subscript', format: 'subscript' },
                            { title: 'Code', icon: 'code', format: 'code' }
                        ]
                    },
                    { 
                        title: 'Blocks', 
                        items: [
                            { title: 'Paragraph', format: 'p' },
                            { title: 'Blockquote', format: 'blockquote' },
                            { title: 'Div', format: 'div' },
                            { title: 'Pre', format: 'pre' },
                            { title: 'Code Block', format: 'pre', classes: 'code-block' }
                        ]
                    },
                    { 
                        title: 'Alignment', 
                        items: [
                            { title: 'Left', icon: 'alignleft', format: 'alignleft' },
                            { title: 'Center', icon: 'aligncenter', format: 'aligncenter' },
                            { title: 'Right', icon: 'alignright', format: 'alignright' },
                            { title: 'Justify', icon: 'alignjustify', format: 'alignjustify' }
                        ]
                    },
                    {
                        title: 'Text Color',
                        items: [
                            { title: 'Primary', inline: 'span', styles: { color: '#3b82f6' } },
                            { title: 'Success', inline: 'span', styles: { color: '#10b981' } },
                            { title: 'Warning', inline: 'span', styles: { color: '#f59e0b' } },
                            { title: 'Danger', inline: 'span', styles: { color: '#ef4444' } },
                            { title: 'Gray', inline: 'span', styles: { color: '#6b7280' } }
                        ]
                    },
                    {
                        title: 'Background Color',
                        items: [
                            { title: 'Primary', inline: 'span', styles: { backgroundColor: '#dbeafe', color: '#1e40af' } },
                            { title: 'Success', inline: 'span', styles: { backgroundColor: '#d1fae5', color: '#065f46' } },
                            { title: 'Warning', inline: 'span', styles: { backgroundColor: '#fef3c7', color: '#92400e' } },
                            { title: 'Danger', inline: 'span', styles: { backgroundColor: '#fee2e2', color: '#991b1b' } },
                            { title: 'Gray', inline: 'span', styles: { backgroundColor: '#f3f4f6', color: '#374151' } }
                        ]
                    }
                ],
                
                // Custom formats
                formats: {
                    alignleft: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,img,table,img', classes: 'text-left' },
                    aligncenter: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,img,table,img', classes: 'text-center' },
                    alignright: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,img,table,img', classes: 'text-right' },
                    alignjustify: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,img,table,img', classes: 'text-justify' },
                    bold: { inline: 'strong', 'classes': 'font-bold' },
                    italic: { inline: 'em', 'classes': 'italic' },
                    underline: { inline: 'span', 'classes': 'underline', exact: true },
                    strikethrough: { inline: 'del' },
                    code: { inline: 'code', 'classes': 'bg-gray-100 px-1 rounded' }
                },
                
                // Custom styles for the editor UI
                skin: 'oxide',
                content_css: 'default',
                
                // Custom file picker callback
                file_picker_callback: function (callback, value, meta) {
                    // Provide a simple file picker for links
                    if (meta.filetype === 'file') {
                        const input = document.createElement('input');
                        input.setAttribute('type', 'file');
                        input.setAttribute('accept', '.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt');
                        
                        input.onchange = function () {
                            const file = this.files[0];
                            
                            // In a real implementation, you would upload the file to your server
                            // and then call the callback with the URL to the file
                            callback('files/' + file.name);
                        };
                        
                        input.click();
                    }
                },
                
                // Custom setup
                setup: function (editor) {
                    // Add a custom button for inserting a horizontal rule
                    editor.ui.registry.addButton('hr', {
                        icon: 'horizontal-rule',
                        tooltip: 'Horizontal line',
                        onAction: function () {
                            editor.insertContent('<hr />');
                        }
                    });
                    
                    // Add a custom button for inserting a page break
                    editor.ui.registry.addButton('pagebreak', {
                        icon: 'page-break',
                        tooltip: 'Page break',
                        onAction: function () {
                            editor.insertContent('<div style="page-break-after: always;">&nbsp;</div>');
                        }
                    });
                    
                    // Add a custom button for inserting a code block
                    editor.ui.registry.addButton('codesample', {
                        icon: 'code-sample',
                        tooltip: 'Insert code sample',
                        onAction: function () {
                            editor.execCommand('mceInsertContent', false, '<pre><code class="language-html">// Your code here\n</code></pre>');
                        }
                    });
                },
                
                // Custom templates
                templates: [
                    {
                        title: 'Two Column Layout',
                        description: 'A two column layout with headings and content',
                        content: '<div class="grid grid-cols-2 gap-4"><div class="p-4 bg-gray-50"><h3>Column 1</h3><p>Content for column 1</p></div><div class="p-4 bg-gray-50"><h3>Column 2</h3><p>Content for column 2</p></div></div>'
                    },
                    {
                        title: 'Call to Action',
                        description: 'A call to action section',
                        content: '<div class="p-6 bg-blue-50 border border-blue-200 rounded-lg text-center"><h3 class="text-2xl font-bold text-blue-800 mb-2">Ready to get started?</h3><p class="text-blue-700 mb-4">Sign up today and experience the difference.</p><a href="#" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md transition duration-150 ease-in-out">Sign Up Now</a></div>'
                    }
                ]
            });(featuredImagePlaceholder) {
                                    featuredImagePlaceholder.classList.add('hidden');
                                }
                            }
                            if (removeFeaturedImage) {
                                removeFeaturedImage.value = '0';
                            }
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Auto-generate slug from title
            const titleInput = document.getElementById('title');
            const slugInput = document.getElementById('slug');
            
            if (titleInput && slugInput) {
                titleInput.addEventListener('blur', function() {
                    if (!slugInput.value) {
                        const slug = this.value
                            .toLowerCase()
                            .replace(/[^\w\s-]/g, '') // Remove special chars
                            .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with -
                            .replace(/^-+|-+$/g, ''); // Trim - from start/end
                        slugInput.value = slug;
                    }
                });
            }

            // Drag and drop functionality for featured image
            const dropZone = document.getElementById('drop-zone');
            const fileInput = document.getElementById('featured_image');
            
            if (dropZone && fileInput) {
                // Prevent default drag behaviors
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, preventDefaults, false);
                    document.body.addEventListener(eventName, preventDefaults, false);
                });

                // Highlight drop zone when item is dragged over it
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, unhighlight, false);
                });

                // Handle dropped files
                dropZone.addEventListener('drop', handleDrop, false);

                // Click on drop zone to trigger file input
                dropZone.addEventListener('click', () => {
                    if (fileInput) fileInput.click();
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                function highlight() {
                    dropZone.classList.add('border-primary-400', 'bg-primary-50');
                }

                function unhighlight() {
                    dropZone.classList.remove('border-primary-400', 'bg-primary-50');
                }

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    
                    if (files.length > 0) {
                        // Check if the file is an image
                        if (files[0].type.match('image.*')) {
                            // Create a new DataTransfer object and add the file
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(files[0]);
                            
                            // Set the files property of the input to the new file
                            fileInput.files = dataTransfer.files;
                            
                            // Trigger the change event to handle the file
                            const event = new Event('change');
                            fileInput.dispatchEvent(event);
                        } else {
                            alert('Please select a valid image file (JPEG, PNG, GIF)');
                        }
                    }
                }

                // Show file name when selected
                fileInput.addEventListener('change', function() {
                    const fileName = this.files[0]?.name;
                    if (fileName) {
                        const uploadStatus = document.getElementById('upload-status');
                        if (uploadStatus) {
                            uploadStatus.textContent = `Selected: ${fileName}`;
                        }
                    }
                });
            }

            // Preview modal functionality
            const previewBtn = document.getElementById('preview-btn');
            const previewModal = document.getElementById('preview-modal');
            const closePreviewBtns = [
                document.getElementById('close-preview'),
                document.getElementById('close-preview-btn')
            ];
            const previewTitle = document.getElementById('preview-title');
            const previewBody = document.getElementById('preview-body');

            if (previewBtn && previewModal) {
                // Open preview modal
                previewBtn.addEventListener('click', function() {
                    // Get the content from TinyMCE if available
                    if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                        const content = tinymce.get('content').getContent();
                        previewBody.innerHTML = content;
                    } else {
                        const content = document.getElementById('content')?.value || '';
                        previewBody.textContent = content;
                    }
                    
                    // Set the title
                    const title = document.getElementById('title')?.value || 'Untitled Post';
                    previewTitle.textContent = title;
                    
                    // Show the modal
                    previewModal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                });
                
                // Close preview modal
                closePreviewBtns.forEach(btn => {
                    if (btn) {
                        btn.addEventListener('click', function() {
                            previewModal.classList.add('hidden');
                            document.body.classList.remove('overflow-hidden');
                        });
                    }
                });
                
                // Close modal when clicking outside content
                previewModal.addEventListener('click', function(e) {
                    if (e.target === previewModal) {
                        previewModal.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                });
                
                // Close modal with Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !previewModal.classList.contains('hidden')) {
                        previewModal.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                });
            }
        });
    </script>
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">
                    <?= isset($_GET['edit']) ? 'Edit Blog Post' : 'Blog Management' ?>
                </h1>
                <div class="flex space-x-3">
                    <?php if (isset($_GET['edit']) || !isset($_GET['list'])): ?>
                        <a href="?list=1" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Back to List
                        </a>
                    <?php else: ?>
                        <a href="?" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            New Post
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="rounded-md bg-green-50 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <?php unset($_SESSION['success']); ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="rounded-md bg-red-50 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            <?= htmlspecialchars($error) ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Blog Post Form -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">
                    <?= isset($_GET['edit']) ? 'Edit Blog Post' : 'Create New Blog Post' ?>
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Fill in the details below to <?= isset($_GET['edit']) ? 'update' : 'create' ?> your blog post.
                </p>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="divide-y divide-gray-200">
                <input type="hidden" name="action" value="<?= isset($_GET['edit']) ? 'update' : 'create' ?>">
                <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="post_id" value="<?= htmlspecialchars($_GET['edit']) ?>">
                <?php endif; ?>
                
                <div class="px-4 py-5 sm:p-6 space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   required
                                   class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   value="<?= isset($editingPost['title']) ? htmlspecialchars($editingPost['title']) : '' ?>">
                        </div>
                    </div>
                    
                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700">
                            URL Slug
                        </label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                /blog/
                            </span>
                            <input type="text" 
                                   name="slug" 
                                   id="slug"
                                   class="focus:ring-primary-500 focus:border-primary-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300"
                                   value="<?= isset($editingPost['slug']) ? htmlspecialchars($editingPost['slug']) : '' ?>"
                                   placeholder="post-url-slug">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Leave blank to auto-generate from title.
                        </p>
                    </div>
                    
                    <!-- Content -->
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <div class="rounded-md border border-gray-300 overflow-hidden">
                            <textarea id="content" name="content" rows="15" class="w-full"><?= isset($editingPost['content']) ? htmlspecialchars($editingPost['content']) : '' ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Excerpt -->
                    <div>
                        <label for="excerpt" class="block text-sm font-medium text-gray-700">
                            Excerpt
                        </label>
                        <div class="mt-1">
                            <textarea id="excerpt" 
                                      name="excerpt" 
                                      rows="3" 
                                      class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border border-gray-300 rounded-md"
                                      placeholder="A brief summary of your post (optional)"><?= isset($editingPost['excerpt']) ? htmlspecialchars($editingPost['excerpt']) : '' ?></textarea>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            A short summary that appears in blog listings and search results.
                        </p>
                    </div>
                    
                    <!-- Featured Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Featured Image
                            <span class="text-gray-400 font-normal">(Optional)</span>
                        </label>
                        <div id="drop-zone" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-primary-300 transition-colors duration-200">
                            <div class="space-y-1 text-center w-full">
                                <?php if (isset($editingPost['featured_image']) && !empty($editingPost['featured_image'])): ?>
                                    <div id="featured-image-container" class="relative group">
                                        <img id="featured-image-preview" 
                                             src="/<?= htmlspecialchars($editingPost['featured_image']) ?>" 
                                             alt="Featured Image Preview" 
                                             class="mx-auto max-h-64 w-auto max-w-full object-cover rounded-md shadow-sm">
                                        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-md">
                                            <div class="flex space-x-2">
                                                <label class="cursor-pointer bg-white bg-opacity-90 rounded-full p-2 text-gray-700 hover:text-primary-600 transition-colors duration-200">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <input id="featured_image" name="featured_image" type="file" class="sr-only" accept="image/jpeg,image/png,image/gif">
                                                </label>
                                                <button type="button" 
                                                        onclick="document.getElementById('remove_featured_image').value = '1'; 
                                                                 document.getElementById('featured-image-container').innerHTML = document.getElementById('featured-image-placeholder').innerHTML;" 
                                                        class="bg-white bg-opacity-90 rounded-full p-2 text-red-600 hover:bg-red-50 transition-colors duration-200">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="remove_featured_image" id="remove_featured_image" value="0">
                                <?php else: ?>
                                    <div id="featured-image-container" class="w-full">
                                        <div id="featured-image-placeholder" class="space-y-3">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex flex-col items-center text-sm text-gray-600">
                                                <div class="flex items-center">
                                                    <label class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                                        <span>Upload an image</span>
                                                        <input id="featured_image" name="featured_image" type="file" class="sr-only" accept="image/jpeg,image/png,image/gif">
                                                    </label>
                                                    <p class="pl-1">or drag and drop</p>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    PNG, JPG, GIF up to 2MB
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div id="upload-progress" class="hidden mt-2">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div id="progress-bar" class="bg-primary-600 h-2.5 rounded-full transition-all duration-300 ease-in-out" style="width: 0%"></div>
                                    </div>
                                    <p id="upload-status" class="text-xs text-gray-500 mt-1">Uploading: <span>0%</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Categories -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Categories
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <?php foreach ($categories as $category): ?>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="category-<?= $category['id'] ?>" 
                                               name="categories[]" 
                                               type="checkbox" 
                                               value="<?= $category['id'] ?>"
                                               class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded"
                                               <?= (isset($editingPost) && in_array($category['id'], $postCategories[$editingPost['id']] ?? [])) ? 'checked' : '' ?>>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="category-<?= $category['id'] ?>" class="font-medium text-gray-700">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </label>
                                        <?php if (!empty($category['description'])): ?>
                                            <p class="text-gray-500 text-xs"><?= htmlspecialchars($category['description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">
                            Status
                        </label>
                        <select id="status" 
                                name="status" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                            <option value="draft" <?= (isset($editingPost['status']) && $editingPost['status'] === 'draft') ? 'selected' : '' ?>>
                                Draft
                            </option>
                            <option value="published" <?= (!isset($editingPost['status']) || $editingPost['status'] === 'published') ? 'selected' : '' ?>>
                                Published
                            </option>
                        </select>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 flex justify-between items-center">
                    <div>
                        <button type="button" id="preview-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Preview
                        </button>
                    </div>
                    <div class="space-x-3">
                        <a href="?list=1" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <?= isset($_GET['edit']) ? 'Update Post' : 'Create Post' ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Preview Modal -->
        <div id="preview-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <!-- Modal panel -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Post Preview
                                    </h3>
                                    <button type="button" id="close-preview" class="text-gray-400 hover:text-gray-500">
                                        <span class="sr-only">Close</span>
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="mt-2">
                                    <div class="bg-white overflow-hidden">
                                        <!-- Preview content will be inserted here -->
                                        <div id="preview-content" class="prose max-w-none">
                                            <h1 id="preview-title" class="text-3xl font-bold text-gray-900 mb-4"></h1>
                                            <div id="preview-body" class="text-gray-700"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" id="close-preview-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
                    <?= isset($_GET['edit']) ? 'Edit Blog Post' : 'Blog Management' ?>
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    <?= isset($_GET['edit']) ? 'Update your blog post details below' : 'Manage your blog posts and content' ?>
                </p>
            </div>
            <?php if (!isset($_GET['edit']) && !isset($_GET['list'])): ?>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="?list=1" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V7.414L10.586 4H6z" clip-rule="evenodd" />
                    </svg>
                    View All Posts
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <?php unset($_SESSION['success']); ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="mb-6 rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            <?= htmlspecialchars($error) ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['edit']) || !isset($_GET['list'])): ?>
        <!-- Blog Post Form -->
        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            <input type="hidden" name="action" value="<?= isset($_GET['edit']) ? 'update' : 'create' ?>">
            <?php if (isset($_GET['edit'])): ?>
                <input type="hidden" name="post_id" value="<?= htmlspecialchars($_GET['edit']) ?>">
            <?php endif; ?>
            
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <!-- Form Header -->
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        <?= isset($_GET['edit']) ? 'Edit Blog Post' : 'Create New Blog Post' ?>
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Fill in the details below to <?= isset($_GET['edit']) ? 'update' : 'create' ?> your blog post.
                    </p>
                </div>
                
                <!-- Form Content -->
                <div class="px-4 py-5 sm:p-6">
                    <div class="space-y-8">
                        <!-- Title & Slug -->
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">
                                    Title <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1">
                                    <input type="text" 
                                           name="title" 
                                           id="title" 
                                           required
                                           class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                           value="<?= isset($editingPost['title']) ? htmlspecialchars($editingPost['title']) : '' ?>">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    A clear and descriptive title for your blog post.
                                </p>
                            </div>
                            
                            <!-- Slug -->
                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700">
                                    URL Slug
                                </label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                        /blog/
                                    </span>
                                    <input type="text" 
                                           name="slug" 
                                           id="slug"
                                           class="focus:ring-primary-500 focus:border-primary-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300"
                                           value="<?= isset($editingPost['slug']) ? htmlspecialchars($editingPost['slug']) : '' ?>"
                                           placeholder="post-url-slug">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Leave blank to auto-generate from title.
                                </p>
                            </div>
                        </div>
                            <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6 space-y-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
                                <div class="mt-1">
                                    <input type="text" name="title" id="title" required
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        value="<?= isset($editingPost['title']) ? htmlspecialchars($editingPost['title']) : '' ?>">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">A clear and descriptive title for your blog post.</p>
                            </div>
                        
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">Content</h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <div class="space-y-8">
                                <!-- Content Editor -->
                                <div class="border-t border-gray-200 pt-6">
                                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                        Content <span class="text-red-500">*</span>
                                    </label>
                                    <div class="rounded-md border border-gray-300 overflow-hidden">
                                        <textarea id="content" name="content" rows="15" class="w-full"><?= isset($editingPost['content']) ? htmlspecialchars($editingPost['content']) : '' ?></textarea>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Write your blog post content here. Use the formatting tools for rich text editing.
                                    </p>
                                </div>
                                
                                <!-- Excerpt -->
                                <div class="border-t border-gray-200 pt-6">
                                    <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">
                                        Excerpt
                                    </label>
                                    <div class="mt-1">
                                        <textarea id="excerpt" name="excerpt" rows="3" 
                                            class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border border-gray-300 rounded-md"
                                            placeholder="A brief summary of your post (optional)"><?= isset($editingPost['excerpt']) ? htmlspecialchars($editingPost['excerpt']) : '' ?></textarea>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        A short summary that appears in blog listings and search results.
                                    </p>
                                </div>
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
      
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tiny.cloud/1/YOUR-API-KEY/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        .status-draft { background-color: #fef3c7; color: #92400e; }
        .status-published { background-color: #d1fae5; color: #065f46; }
        .status-archived { background-color: #e5e7eb; color: #374151; }
        .sortable { cursor: pointer; }
        .sortable:hover { background-color: #f3f4f6; }
        .pagination .active { background-color: #3b82f6; color: white; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <?= htmlspecialchars($_SESSION['success']); ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <?= htmlspecialchars($_SESSION['error']); ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Header and Actions -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Blog Management
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage your blog posts, create new content, and organize your categories.
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="?action=create" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    New Post
                </a>
            </div>
        </div>
        
        <!-- Search and Filters -->
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <form action="" method="get" class="space-y-4 sm:space-y-0 sm:flex sm:space-x-4">
                <!-- Search Input -->
                <div class="flex-1">
                    <label for="search" class="sr-only">Search posts</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md h-10" placeholder="Search posts...">
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="w-full sm:w-48">
                    <label for="status" class="sr-only">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md h-10">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $status_filter === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="archived" <?= $status_filter === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                
                <!-- Category Filter -->
                <div class="w-full sm:w-48">
                    <label for="category" class="sr-only">Category</label>
                    <select id="category" name="category" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md h-10">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Sort Controls -->
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_by) ?>">
                <input type="hidden" name="order" value="<?= htmlspecialchars($sort_order) ?>">
                
                <!-- Submit Button -->
                <div class="flex-shrink-0">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 h-10">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-1 1A1 1 0 0110 17H8a1 1 0 01-1-1v-3.586l-3.707-3.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                        </svg>
                        Filter
                    </button>
                </div>
                
                <!-- Reset Button -->
                <div class="flex-shrink-0">
                    <a href="?" class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 h-10">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>
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
