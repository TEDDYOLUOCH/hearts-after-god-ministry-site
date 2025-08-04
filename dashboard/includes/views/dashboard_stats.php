<?php
/**
 * Dashboard Stats View
 * Displays key metrics in a responsive grid layout
 * 
 * Expected variables:
 * - $stats: Array containing statistics (total_posts, total_users, total_events, total_sermons)
 * - $isAdmin: Boolean indicating if the current user is an admin
 */

// Set default values if not provided
$stats = $stats ?? [
    'total_posts' => 0,
    'total_users' => 0,
    'total_events' => 0,
    'total_sermons' => 0
];

$isAdmin = $isAdmin ?? false;
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Blog Posts Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">
                    Content
                </span>
            </div>
            <div class="mt-6">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white" id="stat-total_posts">
                    <?= number_format($stats['total_posts']) ?>
                </h3>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Blog Posts</p>
            </div>
        </div>
        <a href="?section=blog" class="block bg-gray-50 dark:bg-gray-700/50 px-6 py-3 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <span>Manage Posts</span>
                <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform duration-200 group-hover:translate-x-1"></i>
            </div>
        </a>
    </div>

    <?php if ($isAdmin): ?>
    <!-- Users Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200">
                    Admin
                </span>
            </div>
            <div class="mt-6">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white" id="stat-total_users">
                    <?= number_format($stats['total_users']) ?>
                </h3>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Total Users</p>
            </div>
        </div>
        <a href="?section=users" class="block bg-gray-50 dark:bg-gray-700/50 px-6 py-3 text-sm font-medium text-purple-600 dark:text-purple-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <span>Manage Users</span>
                <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform duration-200 group-hover:translate-x-1"></i>
            </div>
        </a>
    </div>
    <?php endif; ?>

    <!-- Events Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                    <i data-lucide="calendar" class="w-6 h-6"></i>
                </div>
                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200">
                    Events
                </span>
            </div>
            <div class="mt-6">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white" id="stat-total_events">
                    <?= number_format($stats['total_events']) ?>
                </h3>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Total Events</p>
            </div>
        </div>
        <a href="?section=events" class="block bg-gray-50 dark:bg-gray-700/50 px-6 py-3 text-sm font-medium text-green-600 dark:text-green-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <span>Manage Events</span>
                <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform duration-200 group-hover:translate-x-1"></i>
            </div>
        </a>
    </div>

    <!-- Sermons Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400">
                    <i data-lucide="volume-2" class="w-6 h-6"></i>
                </div>
                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200">
                    Media
                </span>
            </div>
            <div class="mt-6">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white" id="stat-total_sermons">
                    <?= number_format($stats['total_sermons']) ?>
                </h3>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Sermons</p>
            </div>
        </div>
        <a href="?section=sermons" class="block bg-gray-50 dark:bg-gray-700/50 px-6 py-3 text-sm font-medium text-yellow-600 dark:text-yellow-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <span>Manage Sermons</span>
                <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform duration-200 group-hover:translate-x-1"></i>
            </div>
        </a>
    </div>
</div>
