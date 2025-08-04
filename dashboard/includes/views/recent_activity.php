<?php
/**
 * Recent Activity View
 * Displays recent blog posts and user registrations in a timeline
 * 
 * Expected variables:
 * - $recent_posts: Array of recent blog posts
 * - $recent_users: Array of recent users (only for admins)
 * - $isAdmin: Boolean indicating if the current user is an admin
 */

// Set default values if not provided
$recent_posts = $recent_posts ?? [];
$recent_users = $recent_users ?? [];
$isAdmin = $isAdmin ?? false;
?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Blog Posts -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                <i data-lucide="file-text" class="w-5 h-5 mr-2 text-blue-500"></i>
                Recent Blog Posts
            </h3>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (!empty($recent_posts)): ?>
                <?php foreach (array_slice($recent_posts, 0, 5) as $post): ?>
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-500 dark:text-blue-400">
                                <i data-lucide="file-text" class="w-5 h-5"></i>
                            </div>
                            <div class="ml-4 flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        <a href="?section=blog&action=edit&id=<?= htmlspecialchars($post['id']) ?>" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-150">
                                            <?= htmlspecialchars($post['title'] ?? 'Untitled Post') ?>
                                        </a>
                                    </p>
                                    <?php if (isset($post['status'])): ?>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            <?= $post['status'] === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200' ?>">
                                            <?= ucfirst(htmlspecialchars($post['status'])) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    <?php if (isset($post['created_at'])): ?>
                                        <span title="Created: <?= htmlspecialchars($post['created_at']) ?>">
                                            <?= date('M j, Y', strtotime($post['created_at'])) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (isset($post['updated_at']) && $post['updated_at'] !== $post['created_at']): ?>
                                        <span class="ml-1" title="Updated: <?= htmlspecialchars($post['updated_at']) ?>">
                                            • Updated <?= date('M j', strtotime($post['updated_at'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($post['excerpt'])): ?>
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                    <?= htmlspecialchars($post['excerpt']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-6 text-center">
                    <div class="text-gray-400 dark:text-gray-500">
                        <i data-lucide="file-text" class="mx-auto h-12 w-12 opacity-50"></i>
                        <p class="mt-2 text-sm font-medium">No blog posts found</p>
                        <p class="text-xs mt-1">Create your first blog post to get started</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-3 text-right border-t border-gray-200 dark:border-gray-700">
            <a href="?section=blog" class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-150 inline-flex items-center">
                View all posts
                <i data-lucide="arrow-right" class="ml-1 w-4 h-4"></i>
            </a>
        </div>
    </div>

    <?php if ($isAdmin): ?>
    <!-- Recent Users (Admin Only) -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                <i data-lucide="users" class="w-5 h-5 mr-2 text-purple-500"></i>
                Recent Users
            </h3>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (!empty($recent_users)): ?>
                <?php foreach ($recent_users as $user): ?>
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400">
                                <i data-lucide="user" class="w-5 h-5"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($user['username'] ?? 'Unknown User') ?>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($user['email'] ?? 'No email') ?>
                                </div>
                                <?php if (isset($user['created_at'])): ?>
                                <div class="mt-1 text-xs text-gray-400">
                                    Joined <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php if (isset($user['role'])): ?>
                            <div class="ml-auto">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' ?>">
                                    <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-6 text-center">
                    <div class="text-gray-400 dark:text-gray-500">
                        <i data-lucide="user-x" class="mx-auto h-12 w-12 opacity-50"></i>
                        <p class="mt-2 text-sm font-medium">No users found</p>
                        <p class="text-xs mt-1">User data will appear here when available</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-3 text-right border-t border-gray-200 dark:border-gray-700">
            <a href="?section=users" class="text-sm font-medium text-purple-600 hover:text-purple-500 dark:text-purple-400 dark:hover:text-purple-300 transition-colors duration-150 inline-flex items-center">
                Manage users
                <i data-lucide="arrow-right" class="ml-1 w-4 h-4"></i>
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>
