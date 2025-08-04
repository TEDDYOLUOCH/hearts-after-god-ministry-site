<?php
/**
 * Admin Header Navigation
 * 
 * This file should be included after any session or authentication checks
 * and before any output is sent to the browser.
 */

// Prevent direct access
if (!defined('ADMIN_HEADER_INCLUDED')) {
    define('ADMIN_HEADER_INCLUDED', true);
    
    // Start output buffering at the very top
    if (ob_get_level() === 0) {
        ob_start();
    }

    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Get the current page from the URL
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

    // Define the navigation menu
    $menu = [
        'dashboard' => [
            'title' => 'Dashboard',
            'icon' => 'layout-dashboard',
            'url' => '/hearts-after-god-ministry-site/dashboard/admin-dashboard.php',
            'active' => (basename(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH)) === 'admin-dashboard.php')
        ],
        'sermons' => [
            'title' => 'Sermons',
            'icon' => 'mic-2',
            'url' => '/hearts-after-god-ministry-site/dashboard/sermons.php',
            'active' => (basename(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH)) === 'sermons.php')
        ],
        'blog' => [
            'title' => 'Blog',
            'icon' => 'newspaper',
            'url' => '/hearts-after-god-ministry-site/dashboard/blog.php',
            'active' => (basename(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH)) === 'blog.php')
        ],
        'events' => [
            'title' => 'Events',
            'icon' => 'calendar',
            'url' => '/hearts-after-god-ministry-site/dashboard/events.php',
            'active' => (basename(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH)) === 'events.php')
        ],
        'gallery' => [
            'title' => 'Gallery',
            'icon' => 'image',
            'url' => '/hearts-after-god-ministry-site/dashboard/gallery.php',
            'active' => (basename(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH)) === 'gallery.php')
        ],
        'users' => [
            'title' => 'Users',
            'icon' => 'users',
            'url' => '/hearts-after-god-ministry-site/dashboard/users.php',
            'active' => (basename(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH)) === 'users.php')
        ],
        'settings' => [
            'title' => 'Settings',
            'icon' => 'settings',
            'url' => '/hearts-after-god-ministry-site/dashboard/settings.php',
            'active' => (basename(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH)) === 'settings.php')
        ]
    ];

    // Get username and role from session with a fallback
    $userName = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';
    $userRole = isset($_SESSION['user_role']) ? ucfirst($_SESSION['user_role']) : 'User';

    // Start output buffering for the header content
    ob_start();
?>
<div x-data="{
    darkMode: localStorage.getItem('darkMode') === 'true',
    mobileMenuOpen: false,
    init() {
        // Apply dark mode on initial load
        this.$watch('darkMode', value => {
            localStorage.setItem('darkMode', value);
            if (value) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
        
        // Check for saved preference or system preference
        if (localStorage.getItem('darkMode') === null) {
            this.darkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        
        // Apply the saved preference
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        }
    }
}" class="min-h-screen flex flex-col md:flex-row">
<!-- Mobile menu button -->
<div class="md:hidden fixed top-4 right-4 z-50">
    <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-lg bg-white dark:bg-gray-800 shadow-md">
        <i x-show="!mobileMenuOpen" data-lucide="menu" class="w-6 h-6"></i>
        <i x-show="mobileMenuOpen" data-lucide="x" class="w-6 h-6"></i>
    </button>
</div>

<!-- Main Sidebar -->
<aside 
    x-show="mobileMenuOpen || window.innerWidth >= 768"
    x-transition:enter="transition ease-in-out duration-300 transform"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in-out duration-300 transform"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    @click.away="mobileMenuOpen = false"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-800 text-white flex flex-col transform transition-transform duration-300 ease-in-out md:translate-x-0 -translate-x-full"
    :class="{'translate-x-0': mobileMenuOpen}"
    style="top: 0; bottom: 0;"
>
    <!-- Sidebar -->
    <div class="flex-1 flex flex-col overflow-y-auto">
        <!-- Logo and close button (mobile) -->
        <div class="flex items-center justify-between p-4 border-b border-gray-700 md:hidden">
            <div class="flex items-center">
                <img class="h-8 w-auto" src="/hearts-after-god-ministry-site/assets/images/logo-light.png" alt="Hearts After God Ministry">
            </div>
            <button @click="mobileMenuOpen = false" class="text-gray-400 hover:text-white focus:outline-none">
                <i data-lucide="x" class="h-6 w-6"></i>
            </button>
        </div>

    <!-- User profile -->
    <div class="p-4 border-b border-gray-700">
        <div class="flex items-center space-x-3">
            <div class="relative">
                <img src="<?= $userAvatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=4b5563&color=fff' ?>" 
                     alt="User Avatar" 
                     class="h-10 w-10 rounded-full bg-gray-600">
                <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-green-500 ring-2 ring-gray-800"></span>
            </div>
            <div>
                <p class="text-sm font-medium"><?= htmlspecialchars($userName) ?></p>
                <p class="text-xs text-gray-400">Administrator</p>
            </div>
        </div>
    </div>
        
        <!-- Dark mode toggle -->
        <button @click="darkMode = !darkMode" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <i x-show="!darkMode" data-lucide="moon" class="w-5 h-5 text-gray-500"></i>
            <i x-show="darkMode" data-lucide="sun" class="w-5 h-5 text-yellow-400"></i>
        </button>
        
        <!-- Navigation -->
        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
            <!-- Dashboard -->
            <div class="space-y-1">
                <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Main</h3>
                <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php" 
                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white <?= strpos(basename($_SERVER['PHP_SELF']), 'dashboard') !== false ? 'bg-gray-700 text-white' : '' ?>">
                    <i data-lucide="layout-dashboard" class="mr-3 h-5 w-5 flex-shrink-0 <?= strpos(basename($_SERVER['PHP_SELF']), 'dashboard') !== false ? 'text-blue-400' : 'text-gray-400 group-hover:text-gray-300' ?>"></i>
                    Dashboard
                </a>
            </div>

            <!-- Content -->
            <div class="space-y-1">
                <h3 class="px-3 mt-6 text-xs font-semibold text-gray-400 uppercase tracking-wider">Content</h3>
                
                <!-- Sermons -->
                <a href="/hearts-after-god-ministry-site/dashboard/sermons.php" 
                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white <?= strpos(basename($_SERVER['PHP_SELF']), 'sermons') !== false ? 'bg-gray-700 text-white' : '' ?>">
                    <i data-lucide="mic-2" class="mr-3 h-5 w-5 flex-shrink-0 <?= strpos(basename($_SERVER['PHP_SELF']), 'sermons') !== false ? 'text-blue-400' : 'text-gray-400 group-hover:text-gray-300' ?>"></i>
                    Sermons
                </a>
                
                <!-- Blog -->
                <a href="/hearts-after-god-ministry-site/dashboard/blog.php" 
                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white <?= strpos(basename($_SERVER['PHP_SELF']), 'blog') !== false ? 'bg-gray-700 text-white' : '' ?>">
                    <i data-lucide="newspaper" class="mr-3 h-5 w-5 flex-shrink-0 <?= strpos(basename($_SERVER['PHP_SELF']), 'blog') !== false ? 'text-blue-400' : 'text-gray-400 group-hover:text-gray-300' ?>"></i>
                    Blog Posts
                </a>
                
                <!-- Events -->
                <a href="/hearts-after-god-ministry-site/dashboard/events.php" 
                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white <?= strpos(basename($_SERVER['PHP_SELF']), 'events') !== false ? 'bg-gray-700 text-white' : '' ?>">
                    <i data-lucide="calendar" class="mr-3 h-5 w-5 flex-shrink-0 <?= strpos(basename($_SERVER['PHP_SELF']), 'events') !== false ? 'text-blue-400' : 'text-gray-400 group-hover:text-gray-300' ?>"></i>
                    Events
                </a>
                
                <!-- Gallery -->
                <a href="/hearts-after-god-ministry-site/dashboard/gallery.php" 
                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white <?= strpos(basename($_SERVER['PHP_SELF']), 'gallery') !== false ? 'bg-gray-700 text-white' : '' ?>">
                    <i data-lucide="image" class="mr-3 h-5 w-5 flex-shrink-0 <?= strpos(basename($_SERVER['PHP_SELF']), 'gallery') !== false ? 'text-blue-400' : 'text-gray-400 group-hover:text-gray-300' ?>"></i>
                    Gallery
                </a>
            </div>

            <!-- Administration -->
            <div class="space-y-1">
                <h3 class="px-3 mt-6 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administration</h3>
                
                <!-- Users -->
                <a href="/hearts-after-god-ministry-site/dashboard/users.php" 
                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white <?= strpos(basename($_SERVER['PHP_SELF']), 'users') !== false ? 'bg-gray-700 text-white' : '' ?>">
                    <i data-lucide="users" class="mr-3 h-5 w-5 flex-shrink-0 <?= strpos(basename($_SERVER['PHP_SELF']), 'users') !== false ? 'text-blue-400' : 'text-gray-400 group-hover:text-gray-300' ?>"></i>
                    Users
                </a>
                
                <!-- Settings -->
                <a href="/hearts-after-god-ministry-site/dashboard/settings.php" 
                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white <?= strpos(basename($_SERVER['PHP_SELF']), 'settings') !== false ? 'bg-gray-700 text-white' : '' ?>">
                    <i data-lucide="settings" class="mr-3 h-5 w-5 flex-shrink-0 <?= strpos(basename($_SERVER['PHP_SELF']), 'settings') !== false ? 'text-blue-400' : 'text-gray-400 group-hover:text-gray-300' ?>"></i>
                    Settings
                </a>
            </div>
        </nav>

        <!-- Footer -->
        <div class="p-4 border-t border-gray-700">
            <div class="flex items-center justify-between text-sm text-gray-400 mb-2">
                <span>v1.0.0</span>
                <span> <?= date('Y') ?> Hearts After God</span>
            </div>
            <a href="/hearts-after-god-ministry-site/backend/users/logout.php" class="group flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-300 rounded-md bg-gray-700 hover:bg-gray-600 hover:text-white transition-colors">
                <i data-lucide="log-out" class="mr-2 h-4 w-4"></i>
                Sign Out
            </a>
        </div>
    </div>
</aside>
<?php
    // Get the buffered content
    $headerContent = ob_get_clean();

    // Output the header content only if it hasn't been output yet
    static $headerOutput = false;
    if (!$headerOutput) {
        echo $headerContent;
        $headerOutput = true;
    }
}
