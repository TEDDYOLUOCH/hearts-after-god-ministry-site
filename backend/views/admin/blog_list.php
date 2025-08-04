<?php
// Get all blog posts with author information
$stmt = $pdo->query("
    SELECT p.*, u.name as author_name
    FROM blog_posts p 
    LEFT JOIN users u ON p.author_id = u.id 
    ORDER BY p.created_at DESC
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex flex-col space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Blog Posts</h2>
        <a href="?page=blog&action=add" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Add New Post
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Author</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (count($posts) > 0): ?>
                        <?php foreach ($posts as $post): 
                            $statusColor = [
                                'draft' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                'published' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'archived' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                            ][$post['status'] ?? 'draft'];
                        ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if (!empty($post['featured_image'])): ?>
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-md object-cover" src="/hearts-after-god-ministry-site/<?= htmlspecialchars($post['featured_image']) ?>" alt="">
                                            </div>
                                        <?php endif; ?>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($post['title']) ?></div>
                                            <?php if (!empty($post['excerpt'])): ?>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1"><?= htmlspecialchars($post['excerpt']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        <?= !empty($post['author_name']) ? htmlspecialchars($post['author_name']) : 'N/A' ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-300">
                                        <?= date('M j, Y', strtotime($post['created_at'])) ?>
                                        <?php if ($post['created_at'] != $post['updated_at']): ?>
                                            <span class="text-xs text-gray-400" title="Last updated: <?= date('M j, Y g:i A', strtotime($post['updated_at'])) ?>">
                                                (edited)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusColor ?>">
                                        <?= ucfirst($post['status'] ?? 'draft') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-3">
                                        <a href="?page=blog&action=edit&edit=<?= $post['id'] ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="Edit">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <a href="#" onclick="return confirmDelete(<?= $post['id'] ?>)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Delete">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No blog posts</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new post.</p>
                                <div class="mt-6">
                                    <a href="?page=blog&action=add" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                        New Post
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDelete(postId) {
    if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        // Create a form and submit it to delete the post
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const postIdInput = document.createElement('input');
        postIdInput.type = 'hidden';
        postIdInput.name = 'post_id';
        postIdInput.value = postId;
        
        // Add CSRF token if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
        
        form.appendChild(actionInput);
        form.appendChild(postIdInput);
        document.body.appendChild(form);
        form.submit();
    }
    return false;
}
</script>
