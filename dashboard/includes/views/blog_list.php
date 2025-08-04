<div class="w-full max-w-6xl mx-auto px-4 sm:px-6 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
        <div class="space-y-0.5">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Blog Posts</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage your blog posts and content</p>
        </div>
        <a href="?page=blog&new=1" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 text-sm rounded-md transition-colors text-center font-medium inline-flex items-center justify-center space-x-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>New Post</span>
        </a>
    </div>

    <?php if (empty($posts)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
            <p class="text-gray-600 dark:text-gray-300">No blog posts found.</p>
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($posts as $post): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if (!empty($post['featured_image'])): ?>
                                        <div class="flex-shrink-0 h-10 w-10 mr-3">
                                            <img class="h-10 w-10 rounded-md object-cover" 
                                                 src="/hearts-after-god-ministry-site/<?= htmlspecialchars($post['featured_image']) ?>" 
                                                 alt="<?= htmlspecialchars($post['title']) ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                <?= htmlspecialchars($post['author_name'] ?? 'N/A') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?= $post['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= ucfirst($post['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                <?= date('M j, Y', strtotime($post['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="?page=blogs&edit=<?= $post['id'] ?>" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                                    Edit
                                </a>
                                <form action="?page=blogs" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
