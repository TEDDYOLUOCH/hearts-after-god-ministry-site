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
$post = null;

// Get post ID from URL
$post_id = $_GET['id'] ?? 0;

// Fetch the post to edit
try {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
    
    if (!$post) {
        header('Location: /hearts-after-god-ministry-site/dashboard/admin-dashboard.php?page=blog');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Error loading post: ' . $e->getMessage();
}

// Function to create URL-friendly slug
function createSlug($string, $pdo, $current_id = null) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Check if slug already exists (excluding current post)
    $originalSlug = $slug;
    $counter = 1;
    
    while (true) {
        $sql = "SELECT id FROM blog_posts WHERE slug = ?";
        $params = [$slug];
        
        if ($current_id) {
            $sql .= " AND id != ?";
            $params[] = $current_id;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if (!$stmt->fetch()) {
            break;
        }
        $slug = $originalSlug . '-' . $counter++;
    }
    
    return $slug;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $excerpt = trim($_POST['excerpt']);
    $status = $_POST['status'] ?? 'draft';
    $slug = createSlug($title, $pdo, $post_id);
    
    // Validate input
    if (empty($title) || empty($content)) {
        $error = 'Title and content are required.';
    } else {
        try {
            // Handle published_at date
            $published_at = $post['published_at'];
            if ($status === 'published' && empty($published_at)) {
                $published_at = date('Y-m-d H:i:s');
            } elseif ($status !== 'published') {
                $published_at = null;
            }
            
            $stmt = $pdo->prepare("UPDATE blog_posts 
                                 SET title = :title, 
                                     slug = :slug,
                                     content = :content, 
                                     excerpt = :excerpt, 
                                     status = :status,
                                     published_at = :published_at,
                                     updated_at = NOW()
                                 WHERE id = :id");
            
            $result = $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':content' => $content,
                ':excerpt' => $excerpt,
                ':status' => $status,
                ':published_at' => $published_at,
                ':id' => $post_id
            ]);
            
            if ($result) {
                $success = 'Post updated successfully!';
                // Update the post data to reflect changes
                $post['title'] = $title;
                $post['content'] = $content;
                $post['excerpt'] = $excerpt;
                $post['status'] = $status;
            }
        } catch (PDOException $e) {
            $error = 'Error updating post: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Hearts After God Ministry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tiny.cloud/1/i38pss4bqtngsukqnqz64u22q4oophmsc3xdqwl3k95pmaa5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body class="bg-gray-50">
    <!-- Include Admin Header with Sidebar -->
    <?php 
    // Set the active page for the sidebar
    $activePage = 'blog';
    include __DIR__ . '/../includes/admin_header.php'; 
    ?>

    <!-- Main Content -->
    <main class="ml-72 min-h-screen p-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Edit Post</h1>
                <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php?page=blog" class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Back to Posts
                </a>
            </div>

            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($post): ?>
                <form method="POST" class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100">
                            <div class="space-y-4">
                                <!-- Post Title -->
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                    <input type="text" id="title" name="title" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        value="<?= htmlspecialchars($post['title']) ?>">
                                </div>

                                <!-- Post Excerpt -->
                                <div>
                                    <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                                    <textarea id="excerpt" name="excerpt" rows="2"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
                                    <p class="mt-1 text-xs text-gray-500">A short summary of your post (optional).</p>
                                </div>

                                <!-- Post Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select id="status" name="status"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Post Content -->
                        <div class="p-6">
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                            <textarea id="content" name="content" rows="15"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($post['content']) ?></textarea>
                        </div>

                        <!-- Form Actions -->
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                Last updated: <?= date('M j, Y \a\t g:i a', strtotime($post['updated_at'])) ?>
                            </div>
                            <div class="flex gap-3">
                                <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php?page=blog" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="submit" name="save_draft" value="1" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-transparent rounded-lg hover:bg-gray-200">
                                    Save as Draft
                                </button>
                                <button type="submit" name="publish" value="1" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700">
                                    Update Post
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                    <div class="text-gray-500 mb-4">
                        <i data-lucide="alert-circle" class="w-12 h-12 mx-auto text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Post not found</h3>
                    <p class="text-gray-500 mb-6">The post you're trying to edit doesn't exist or has been deleted.</p>
                    <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php?page=blog" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Back to Posts
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Initialize TinyMCE
        tinymce.init({
            selector: '#content',
            plugins: 'advlist autolink lists link image charmap print preview anchor',
            toolbar_mode: 'floating',
            height: 500,
            menubar: false,
            statusbar: false,
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; font-size: 16px; }',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });
    </script>
</body>
</html>
