<?php
/**
 * Dashboard Main View
 * Main dashboard template that includes stats and recent activity
 */
?>
<div class="py-6" x-data="{}">
    <!-- Header -->
    <div class="pb-5 border-b border-gray-200 dark:border-gray-700 sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold leading-6 text-gray-900 dark:text-white">
                Dashboard Overview
            </h1>
            <p class="mt-1 max-w-4xl text-sm text-gray-500 dark:text-gray-400">
                Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>! <?= date('l, F j, Y') ?>
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button @click="$store.app.loadSection('dashboard', true)" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-colors duration-150"
                    :disabled="$store.app.loading">
                <i data-lucide="refresh-cw" class="w-4 h-4 mr-2" :class="{'animate-spin': $store.app.loading}"></i>
                <span x-text="$store.app.loading ? 'Refreshing...' : 'Refresh'"></span>
            </button>
        </div>
    </div>

    <!-- Error message -->
    <div x-show="$store.app.error" class="mb-6" x-cloak>
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 dark:text-red-300" x-text="$store.app.error"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading state -->
    <div x-show="$store.app.loading && $store.app.initialLoad" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Dashboard Content -->
    <div x-show="!$store.app.loading || !$store.app.initialLoad" x-cloak>
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-5 mt-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Posts -->
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <i data-lucide="file-text" class="w-6 h-6"></i>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Posts</p>
                        <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white" id="stat-total_posts"><?= $stats['total_posts'] ?? 0 ?></p>
                    </div>
                </div>
            </div>

            <?php if ($isAdmin): ?>
            <!-- Total Users -->
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-500/10 text-green-600 dark:text-green-400">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Users</p>
                        <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white" id="stat-total_users"><?= $stats['total_users'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Total Events -->
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-500/10 text-purple-600 dark:text-purple-400">
                        <i data-lucide="calendar" class="w-6 h-6"></i>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Events</p>
                        <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white" id="stat-total_events"><?= $stats['total_events'] ?? 0 ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Sermons -->
            <div class="p-5 bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-500/10 text-yellow-600 dark:text-yellow-400">
                        <i data-lucide="mic-2" class="w-6 h-6"></i>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Sermons</p>
                        <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white" id="stat-total_sermons"><?= $stats['total_sermons'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="mt-8">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Activity</h2>
            <?php include __DIR__ . '/recent_activity.php'; ?>
        </div>
    </div>
</div>

<script>
// Pass server-side data to JavaScript
<?php if (isset($dashboardData)): ?>
window.dashboardData = <?= json_encode($dashboardData) ?>;
<?php endif; ?>
</script>
