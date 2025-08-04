<div class="w-full max-w-3xl mx-auto px-4 sm:px-6 py-6">
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-3">
            <div class="space-y-0.5">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    <?= isset($editing_post) ? 'Edit Blog Post' : 'Add New Blog Post' ?>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <?= isset($editing_post) ? 'Update your blog post details' : 'Fill in the details to create a new blog post' ?>
                </p>
            </div>
            <a href="?page=blog" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Posts
            </a>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="action" value="<?= isset($editing_post) ? 'update' : 'create' ?>">
        <?php if (isset($editing_post)): ?>
            <input type="hidden" name="post_id" value="<?= $editing_post['id'] ?>">
        <?php endif; ?>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-5">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Title <span class="text-red-500">*</span>
                </label>
                <input type="text" id="title" name="title" required
                       value="<?= htmlspecialchars($editing_post['title'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Slug -->
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    URL Slug
                </label>
                <input type="text" id="slug" name="slug" 
                       value="<?= htmlspecialchars($editing_post['slug'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Leave empty to auto-generate from title
                </p>
            </div>

            <!-- Featured Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Featured Image
                </label>
                <div class="mt-1 flex items-center">
                    <?php if (!empty($editing_post['featured_image'])): ?>
                        <img id="image-preview" 
                             src="/hearts-after-god-ministry-site/<?= htmlspecialchars($editing_post['featured_image']) ?>" 
                             class="h-20 w-20 object-cover rounded-md mr-4">
                    <?php else: ?>
                        <div id="image-preview" class="h-20 w-20 bg-gray-200 dark:bg-gray-700 rounded-md flex items-center justify-center text-gray-400">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    <?php endif; ?>
                    <div class="ml-4">
                        <input type="file" id="featured_image" name="featured_image" 
                               class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-blue-300 dark:hover:file:bg-gray-600"
                               onchange="previewImage(this)">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            JPG, PNG or GIF (Max 2MB)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Excerpt -->
            <div>
                <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Excerpt
                </label>
                <textarea id="excerpt" name="excerpt" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"><?= htmlspecialchars($editing_post['excerpt'] ?? '') ?></textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    A short summary of your post (optional)
                </p>
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Content <span class="text-red-500">*</span>
                </label>
                <textarea id="content" name="content" rows="10" required
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"><?= htmlspecialchars($editing_post['content'] ?? '') ?></textarea>
            </div>

            <!-- Categories -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Categories
                </label>
                <div class="space-y-2">
                    <?php foreach ($categories as $category): ?>
                        <div class="flex items-center">
                            <input id="category-<?= $category['id'] ?>" 
                                   name="categories[]" 
                                   type="checkbox" 
                                   value="<?= $category['id'] ?>"
                                   <?= (isset($editing_post) && in_array($category['id'], explode(',', $editing_post['categories'] ?? ''))) ? 'checked' : '' ?>
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                            <label for="category-<?= $category['id'] ?>" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                <?= htmlspecialchars($category['name']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Status
                </label>
                <select id="status" name="status"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="draft" <?= (isset($editing_post) && $editing_post['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= (isset($editing_post) && $editing_post['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                </select>
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                <a href="?page=blogs" 
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <?= isset($editing_post) ? 'Update Post' : 'Create Post' ?>
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

// Image preview
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'text-gray-400');
            preview.classList.add('object-cover', 'rounded-md');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
