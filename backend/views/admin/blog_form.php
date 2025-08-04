<?php
// Initialize variables for edit mode
$post = [
    'id' => '',
    'title' => '',
    'slug' => '',
    'content' => '',
    'excerpt' => '',
    'status' => 'draft',
    'featured_image' => ''
];

$isEdit = false;

// If in edit mode, fetch the post data
if (isset($_GET['edit'])) {
    $isEdit = true;
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$post) {
        $_SESSION['error'] = 'Post not found';
        header('Location: ?page=blog');
        exit;
    }
}

// Get all categories
$categories = $pdo->query("SELECT * FROM blog_categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Get selected categories for the post
$selectedCategories = [];
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT category_id FROM blog_post_categories WHERE post_id = ?");
    $stmt->execute([$post['id']]);
    $selectedCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            <?= $isEdit ? 'Edit Blog Post' : 'Add New Blog Post' ?>
        </h3>
    </div>
    
    <form method="POST" enctype="multipart/form-data" class="space-y-6 p-6">
        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['id']) ?>">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <!-- Title -->
            <div class="sm:col-span-6">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                <div class="mt-1">
                    <input type="text" name="title" id="title" required
                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md"
                           value="<?= htmlspecialchars($post['title'] ?? '') ?>">
                </div>
            </div>
            
            <!-- Slug -->
            <div class="sm:col-span-6">
                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 sm:text-sm">
                        /blog/
                    </span>
                    <input type="text" name="slug" id="slug" required
                           class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md sm:text-sm border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           value="<?= htmlspecialchars($post['slug'] ?? '') ?>">
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" id="slug-hint">The slug is the URL-friendly version of the name.</p>
            </div>
            
            <!-- Featured Image -->
            <div class="sm:col-span-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Featured Image</label>
                <?php if (!empty($post['featured_image'])): ?>
                    <div class="mt-2">
                        <img src="/hearts-after-god-ministry-site/<?= htmlspecialchars($post['featured_image']) ?>" alt="Featured Image" class="h-32 w-auto">
                        <div class="mt-2 flex items-center">
                            <input type="checkbox" name="remove_featured_image" id="remove_featured_image" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="remove_featured_image" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Remove featured image</label>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="mt-2">
                    <input type="file" name="featured_image" id="featured_image" class="block w-full text-sm text-gray-500 dark:text-gray-300">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, or GIF (Max 2MB)</p>
                </div>
            </div>
            
            <!-- Status -->
            <div class="sm:col-span-3">
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>
            
            <!-- Categories -->
            <div class="sm:col-span-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categories</label>
                <div class="space-y-2">
                    <?php foreach ($categories as $category): ?>
                        <div class="flex items-center">
                            <input id="category-<?= $category['id'] ?>" name="categories[]" type="checkbox" value="<?= $category['id'] ?>"
                                <?= in_array($category['id'], $selectedCategories) ? 'checked' : '' ?>
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="category-<?= $category['id'] ?>" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                <?= htmlspecialchars($category['name']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Excerpt -->
            <div class="sm:col-span-6">
                <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Excerpt</label>
                <div class="mt-1">
                    <textarea id="excerpt" name="excerpt" rows="3" 
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">A short description of the post.</p>
            </div>
            
            <!-- Content -->
            <div class="sm:col-span-6">
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Content</label>
                <div class="mt-1">
                    <textarea id="content" name="content" rows="15" 
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        
        <div class="pt-5">
            <div class="flex justify-end space-x-3">
                <a href="?page=blog" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <?= $isEdit ? 'Update Post' : 'Create Post' ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Auto-generate slug from title
const titleInput = document.getElementById('title');
const slugInput = document.getElementById('slug');

function createSlug(text) {
    return text
        .toLowerCase()
        .replace(/[^\w\s-]/g, '') // Remove special characters
        .replace(/\s+/g, '-')       // Replace spaces with -
        .replace(/--+/g, '-')       // Replace multiple - with single -
        .trim();
}

let isManualSlugEdit = false;

// Only auto-generate slug if the slug field is empty or hasn't been manually edited
titleInput?.addEventListener('input', () => {
    if (!isManualSlugEdit) {
        slugInput.value = createSlug(titleInput.value);
    }
});

slugInput?.addEventListener('input', () => {
    isManualSlugEdit = true;
});

// Add basic styling to the content textarea
const style = document.createElement('style');
style.textContent = `
    #content {
        min-height: 400px;
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-family: 'Inter', sans-serif;
        line-height: 1.5;
        resize: vertical;
    }
    #content:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 1px #6366f1;
    }
`;
document.head.appendChild(style);
</script>
            </div>
        </div>
        
        <div class="pt-5">
            <div class="flex justify-end">
                <a href="?page=blog" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <?= $isEdit ? 'Update Post' : 'Create Post' ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Auto-generate slug from title
const titleInput = document.getElementById('title');
const slugInput = document.getElementById('slug');

if (titleInput && slugInput) {
    titleInput.addEventListener('input', function() {
        if (!slugInput.dataset.manualEdit) {
            const slug = titleInput.value
                .toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove special chars
                .replace(/[\s_-]+/g, '-')   // Replace spaces, underscores, and hyphens with a single dash
                .replace(/^-+|-+$/g, '');    // Trim dashes from start and end
            slugInput.value = slug;
        }
    });
    
    // Track manual edits to the slug
    slugInput.addEventListener('input', function() {
        this.dataset.manualEdit = 'true';
    });
}

// Initialize rich text editor for content
if (typeof tinymce !== 'undefined') {
    tinymce.init({
        selector: '#content',
        plugins: 'link lists table code',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        menubar: false,
        skin: 'oxide-dark',
        content_css: 'dark',
        height: 400
    });
}
</script>
