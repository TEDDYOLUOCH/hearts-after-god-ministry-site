<?php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hearts After God Ministry</title>
    <link href="/dist/css/output.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 fixed h-full">
            <div class="p-4 border-b">
                <img src="/assets/logo.png" alt="Logo" class="h-8">
            </div>
            <nav class="p-4 space-y-2">
                <a href="/dashboard" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-primary-50 text-gray-700 hover:text-primary-600 group">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span>Dashboard</span>
                </a>
                <!-- Add more navigation items -->
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="ml-64 flex-1">
            <!-- Top Navigation -->
            <header class="bg-white border-b border-gray-200 h-16 fixed right-0 left-64 z-30">
                <div class="flex items-center justify-between h-full px-6">
                    <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
                    <div class="flex items-center space-x-4">
                        <button class="p-2 hover:bg-gray-100 rounded-full">
                            <i data-lucide="bell" class="w-5 h-5 text-gray-500"></i>
                        </button>
                        <div class="relative">
                            <button class="flex items-center space-x-2">
                                <img src="/assets/avatar.jpg" alt="Profile" class="w-8 h-8 rounded-full">
                                <span class="text-sm font-medium text-gray-700">Admin</span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="pt-16 p-6">
                <?php include $content; ?>
            </div>
        </main>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Add active state to current navigation item
        document.querySelectorAll('nav a').forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('bg-primary-50', 'text-primary-600');
            }
        });
    </script>
</body>
</html>