<?php
// Admin navigation items
$navItems = [
    'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'layout-dashboard',
        'url' => '/dashboard/admin-dashboard.php?page=dashboard'
    ],
    'sermons' => [
        'title' => 'Sermons',
        'icon' => 'volume-2',
        'url' => '/backend/users/admin_manage_sermon.php'
    ],
    'team' => [
        'title' => 'Team Members',
        'icon' => 'users',
        'url' => '/backend/users/admin_manage_team.php'
    ],
    'events' => [
        'title' => 'Events',
        'icon' => 'calendar',
        'url' => '/dashboard/admin-dashboard.php?page=events'
    ],
    'blog' => [
        'title' => 'Blog',
        'icon' => 'file-text',
        'url' => '/hearts-after-god-ministry-site/backend/users/admin_manage_blog.php'
    ],
    'users' => [
        'title' => 'Users',
        'icon' => 'user',
        'url' => '/dashboard/admin-dashboard.php?page=users'
    ],
    'settings' => [
        'title' => 'Settings',
        'icon' => 'settings',
        'url' => '/dashboard/admin-dashboard.php?page=settings'
    ]
];
?>

<!-- Navigation -->
<nav class="flex-1 px-3 py-4 space-y-1">
    <?php foreach ($navItems as $key => $item): ?>
        <a href="<?= $item['url'] ?>" 
           class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 group <?= (basename($_SERVER['PHP_SELF']) === basename($item['url']) || (isset($_GET['page']) && $_GET['page'] === $key)) ? 'bg-white/10 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>">
            <i data-lucide="<?= $item['icon'] ?>" class="w-5 h-5 mr-3"></i>
            <?= $item['title'] ?>
        </a>
    <?php endforeach; ?>
</nav>
