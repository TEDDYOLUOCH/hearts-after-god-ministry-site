<?php
session_start();

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    exit;
}

// Include database connection
require_once __DIR__ . '/../../backend/config/db.php';

$error = '';
$success = '';

// Function to create URL-friendly slug
function createSlug($string, $pdo) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Check if slug already exists
    $originalSlug = $slug;
    $counter = 1;
    
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ?");
        $stmt->execute([$slug]);
        if (!$stmt->fetch()) {
            break;
        }
        $slug = $originalSlug . '-' . $counter++;
    }
    
    return $slug;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Check if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        // Get form data
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        
        // Basic validation
        if (empty($title) || empty($content)) {
            throw new Exception('Title and content are required.');
        }
        
        // Generate slug if not provided
        $slug = !empty($_POST['slug']) ? createSlug($_POST['slug'], $pdo) : createSlug($title, $pdo);
        
        // Handle file upload
        $featuredImage = null;
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../uploads/blog/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetPath)) {
                $featuredImage = '/uploads/blog/' . $fileName;
            }
        }
        
        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, featured_image, status, author_id) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $success = $stmt->execute([
            $title,
            $slug,
            $content,
            $excerpt,
            $featuredImage,
            $status,
            $_SESSION['user_id']
        ]);
        
        if (!$success) {
            throw new Exception('Failed to save the post. Please try again.');
        }
        
        $postId = $pdo->lastInsertId();
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => $status === 'published' ? 'Post published successfully!' : 'Draft saved successfully!',
            'redirect' => '/hearts-after-god-ministry-site/dashboard/admin-dashboard.php?page=blog&saved=1'
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Post - Hearts After God Ministry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    backgroundImage: {
                        'sidebar-gradient': 'linear-gradient(195deg, #1a237e 0%, #283593 100%)',
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar-gradient {
            background: linear-gradient(195deg, #1a237e 0%, #283593 100%);
        }
    </style>
    <!-- Load Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- TinyMCE with your API key -->
    <script src="https://cdn.tiny.cloud/1/i38pss4bqtngsukqnqz64u22q4oophmsc3xdqwl3k95pmaa5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body class="bg-gradient-to-br from-slate-100 via-blue-50 to-indigo-100 min-h-screen">
    <?php 
    // Set the active page for the sidebar
    $activePage = 'blog';
    $userName = $_SESSION['username'] ?? 'Admin';
    include __DIR__ . '/../includes/admin_header.php'; 
    ?>

    <!-- Main Content -->
    <div class="ml-72 min-h-screen">
        <div class="p-8">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-2xl font-bold text-gray-800">Create New Post</h1>
                    <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php?page=blog" class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Back to Posts
                    </a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline"><?= htmlspecialchars($success) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" id="post-form" class="space-y-6" enctype="multipart/form-data">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100">
                            <div class="space-y-4">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Post Title</label>
                                    <input type="text" name="title" id="title" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                                </div>
                                
                                <div>
                                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
                                    <input type="text" name="slug" id="slug" required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>">
                                    <p class="mt-1 text-xs text-gray-500">The URL-friendly version of the title (lowercase, hyphens instead of spaces)</p>
                                </div>
                                
                                <div>
                                    <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                                    <textarea name="excerpt" id="excerpt" rows="3"
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
                                </div>
                                
                                <div>
                                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                                    <textarea name="content" id="content" rows="15" required
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1">
                                        <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
                                        <input type="file" name="featured_image" id="featured_image" 
                                               class="block w-full text-sm text-gray-500
                                                      file:mr-4 file:py-2 file:px-4
                                                      file:rounded-md file:border-0
                                                      file:text-sm file:font-semibold
                                                      file:bg-blue-50 file:text-blue-700
                                                      hover:file:bg-blue-100">
                                    </div>
                                    
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select name="status" id="status" 
                                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="draft" <?= (($_POST['status'] ?? 'draft') === 'draft') ? 'selected' : '' ?>>Draft</option>
                                            <option value="published" <?= (($_POST['status'] ?? '') === 'published') ? 'selected' : '' ?>>Published</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                            <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php?page=blog" 
                               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" name="save_draft" value="1" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-transparent rounded-lg hover:bg-gray-200">
                                Save as Draft
                            </button>
                            <button type="submit" name="publish" value="1" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700">
                                Publish Post
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        document.addEventListener('DOMContentLoaded', function() {
            // Auto-generate slug from title
            const titleInput = document.getElementById('title');
            if (titleInput) {
                titleInput.addEventListener('input', function() {
                    const slugInput = document.getElementById('slug');
                    if (slugInput && !slugInput.value) {
                        const slug = this.value
                            .toLowerCase()
                            .replace(/[^\w\s-]/g, '')
                            .replace(/\s+/g, '-')
                            .replace(/-+/g, '-');
                        slugInput.value = slug;
                    }
                });
            }

            // Initialize TinyMCE
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#content',
                    plugins: 'advlist autolink lists link image charmap print preview anchor',
                    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | removeformat | help',
                    menubar: false,
                    height: 500,
                    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 16px; line-height: 1.6; }',
                    setup: function(editor) {
                        editor.on('change', function() {
                            editor.save();
                        });
                    }
                });
            }

            // Handle form submission
            const postForm = document.getElementById('post-form');
            if (postForm) {
                postForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const submitButton = e.submitter || document.activeElement;
                    const isPublish = submitButton && submitButton.name === 'publish';
                    const isDraft = submitButton && submitButton.name === 'save_draft';
                    
                    if (!isPublish && !isDraft) return;
                    
                    // Show loading state
                    const originalHTML = submitButton.innerHTML;
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> ' + 
                                          (isPublish ? 'Publishing...' : 'Saving...');
                    
                    try {
                        // Update TinyMCE content before form submission
                        if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                            tinymce.get('content').save();
                        }
                        
                        // Create or update status field
                        let statusField = postForm.querySelector('input[name="status"]');
                        if (!statusField) {
                            statusField = document.createElement('input');
                            statusField.type = 'hidden';
                            statusField.name = 'status';
                            postForm.appendChild(statusField);
                        }
                        statusField.value = isPublish ? 'published' : 'draft';
                        
                        // Submit the form using fetch
                        const formData = new FormData(postForm);
                        
                        const response = await fetch(postForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            showNotification(data.message || 'Operation completed successfully!', 'success');
                            if (data.redirect) {
                                setTimeout(() => {
                                    window.location.href = data.redirect;
                                }, 1500);
                            }
                        } else {
                            throw new Error(data.message || 'An error occurred');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showNotification(
                            error.message || 'An error occurred while saving the post. Please try again.', 
                            'error'
                        );
                        submitButton.innerHTML = originalHTML;
                        submitButton.disabled = false;
                        lucide.createIcons();
                    }
                });
            }
        });

        // Show notification function
        function showNotification(message, type = 'success') {
            // Remove any existing notifications
            const existingNotifications = document.querySelectorAll('.notification-toast');
            existingNotifications.forEach(notification => notification.remove());
            
            const notification = document.createElement('div');
            notification.className = `notification-toast fixed top-4 right-4 p-4 rounded-lg shadow-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            notification.style.transition = 'opacity 0.3s, transform 0.3s';
            notification.style.transform = 'translateX(120%)';
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Trigger reflow
            notification.offsetHeight;
            
            // Slide in
            notification.style.transform = 'translateX(0)';
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(120%)';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
    </script>
</body>
</html>
