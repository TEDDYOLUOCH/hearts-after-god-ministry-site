<?php
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    exit;
}
?>

<!-- Sidebar -->
<aside class="fixed left-0 top-0 z-40 h-screen w-72 bg-white border-r border-gray-200 overflow-y-auto">
    <div class="p-6">
        <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php" class="flex items-center space-x-3">
            <img src="/hearts-after-god-ministry-site/assets/images/logo.png" alt="Logo" class="h-8">
            <span class="text-xl font-semibold text-gray-800">Admin Panel</span>
        </a>
    </div>
    
    <nav class="mt-6">
        <div class="px-4 mb-6">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Main</h3>
        </div>
        
        <ul class="space-y-1 px-2">
            <li>
                <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php" 
                   class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 <?= (basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php') ? 'bg-gray-100' : '' ?>">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-gray-500"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php?page=blog" 
                   class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 <?= (isset($_GET['page']) && $_GET['page'] == 'blog') ? 'bg-gray-100' : '' ?>">
                    <i data-lucide="file-text" class="w-5 h-5 mr-3 text-blue-500"></i>
                    Blog Posts
                </a>
            </li>
            <li>
                <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php?page=categories" 
                   class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 <?= (isset($_GET['page']) && $_GET['page'] == 'categories') ? 'bg-gray-100' : '' ?>">
                    <i data-lucide="tag" class="w-5 h-5 mr-3 text-green-500"></i>
                    Categories
                </a>
            </li>
            <li>
                <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php?page=users" 
                   class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 <?= (isset($_GET['page']) && $_GET['page'] == 'users') ? 'bg-gray-100' : '' ?>">
                    <i data-lucide="users" class="w-5 h-5 mr-3 text-purple-500"></i>
                    Users
                </a>
            </li>
        </ul>
        
        <div class="px-4 mt-8 mb-2">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</h3>
        </div>
        
        <ul class="px-2 space-y-1">
            <li>
                <a href="/hearts-after-god-ministry-site/backend/users/profile.php" 
                   class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                    <i data-lucide="user" class="w-5 h-5 mr-3 text-gray-500"></i>
                    Profile
                </a>
            </li>
            <li>
                <a href="/hearts-after-god-ministry-site/backend/users/logout.php" 
                   class="flex items-center px-4 py-3 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50">
                    <i data-lucide="log-out" class="w-5 h-5 mr-3"></i>
                    Logout
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 bg-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <img class="h-9 w-9 rounded-full" 
                     src="<?= !empty($_SESSION['profile_image']) ? htmlspecialchars($_SESSION['profile_image']) : '/hearts-after-god-ministry-site/assets/images/default-avatar.png' ?>" 
                     alt="User avatar">
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-700">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
                </p>
                <p class="text-xs font-medium text-gray-500">
                    <?= ucfirst(htmlspecialchars($_SESSION['user_role'] ?? 'Admin')) ?>
                </p>
            </div>
        </div>
    </div>
</aside>

<!-- Overlay for mobile -->
<div class="fixed inset-0 z-30 bg-black bg-opacity-50 lg:hidden sidebar-overlay"></div>

<script>
// Toggle mobile sidebar
function toggleSidebar() {
    const sidebar = document.querySelector('aside');
    const overlay = document.querySelector('.sidebar-overlay');
    
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('aside');
    const sidebarToggle = document.querySelector('[data-drawer-toggle="sidebar"]');
    const isClickInsideSidebar = sidebar.contains(event.target);
    const isClickOnToggle = sidebarToggle && (sidebarToggle === event.target || sidebarToggle.contains(event.target));
    
    if (!isClickInsideSidebar && !isClickOnToggle && window.innerWidth < 1024) {
        sidebar.classList.add('-translate-x-full');
        document.querySelector('.sidebar-overlay').classList.add('hidden');
    }
});

// Initialize Lucide icons
lucide.createIcons();
</script>
