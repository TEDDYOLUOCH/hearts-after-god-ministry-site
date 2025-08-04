<?php
/**
 * Simple Admin Layout
 * 
 * A more reliable version of the admin layout that loads all required assets directly
 */

/**
 * Renders the admin layout with the given content
 * 
 * @param string $title The page title
 * @param callable $contentFunction Function that renders the page content
 * @param array $params Additional parameters to pass to the content function
 */
function renderAdminLayout($title, $contentFunction, $params = []) {
    // Start output buffering
    ob_start();
    
    // Extract params for easier access in the layout
    $pdo = $params['pdo'] ?? null;
    
    // Get user info from session
    $userName = htmlspecialchars($_SESSION['user_name'] ?? 'Admin');
    $userEmail = htmlspecialchars($_SESSION['user_email'] ?? '');
    $userRole = htmlspecialchars($_SESSION['user_role'] ?? 'user');
    
    // Current page for active state
    $currentPage = basename($_SERVER['PHP_SELF'], '.php');
    
    // Check if dark mode is enabled (stored in session or cookie)
    $darkMode = isset($_COOKIE['dark_mode']) ? $_COOKIE['dark_mode'] === 'true' : true;
    
    ?>
    <!DOCTYPE html>
    <html lang="en" x-data="{ darkMode: <?= $darkMode ? 'true' : 'false' ?>, sidebarOpen: window.innerWidth >= 1024 }" 
          x-init="
            $watch('darkMode', value => {
              document.documentElement.classList.toggle('dark', value);
              document.cookie = 'dark_mode=' + value + ';path=/;max-age=' + (60*60*24*365);
            });
            
            // Close mobile menu when clicking outside
            $el.addEventListener('click', (e) => {
              if (window.innerWidth < 1024 && !e.target.closest('.sidebar') && !e.target.closest('[data-sidebar-toggle]')) {
                sidebarOpen = false;
              }
            });
            
            // Handle window resize
            const handleResize = () => {
              if (window.innerWidth >= 1024) {
                sidebarOpen = true;
              } else {
                sidebarOpen = false;
              }
            };
            
            window.addEventListener('resize', handleResize);
            handleResize();
          "
          :class="{ 'dark': darkMode }" class="h-full">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?> - Admin Dashboard</title>
        
        <!-- Favicon -->
        <link rel="icon" type="image/png" href="/hearts-after-god-ministry-site/assets/images/favicon.png">
        
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Inter', 'sans-serif'],
                        },
                        colors: {
                            primary: {
                                50: '#f0f9ff',
                                100: '#e0f2fe',
                                200: '#bae6fd',
                                300: '#7dd3fc',
                                400: '#38bdf8',
                                500: '#0ea5e9',
                                600: '#0284c7',
                                700: '#0369a1',
                                800: '#075985',
                                900: '#0c4a6e',
                            },
                        },
                    },
                },
            };
        </script>
        
        <!-- Alpine.js -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.0/dist/cdn.min.js"></script>
        
        <!-- Lucide Icons -->
        <script src="https://unpkg.com/lucide@latest"></script>
        
        <style>
            [x-cloak] { display: none !important; }
            body { 
                font-family: 'Inter', sans-serif; 
                overflow-x: hidden;
            }
            
            /* Sidebar styles */
            .sidebar { 
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 40;
                width: 16rem;
                transition: transform 0.3s ease-in-out, width 0.3s ease-in-out;
                transform: translateX(-100%);
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: #4b5563 #1f2937;
            }
            
            .sidebar::-webkit-scrollbar {
                width: 6px;
            }
            
            .sidebar::-webkit-scrollbar-track {
                background: #1f2937;
            }
            
            .sidebar::-webkit-scrollbar-thumb {
                background-color: #4b5563;
                border-radius: 3px;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            @media (min-width: 1024px) {
                .sidebar {
                    transform: translateX(0);
                }
            }
            
            /* Content area styles */
            .content-wrapper {
                min-height: 100vh;
                transition: margin-left 0.3s ease-in-out;
                margin-left: 0;
                background-color: #f9fafb;
            }
            
            @media (min-width: 1024px) {
                .content-wrapper {
                    margin-left: 16rem;
                }
            }
            
            /* Smooth transitions */
            .transition-all {
                transition-property: all;
                transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                transition-duration: 150ms;
            }
            
            /* Overlay for mobile */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 30;
                opacity: 0;
                transition: opacity 0.3s ease-in-out;
            }
            
            .sidebar-overlay.open {
                display: block;
                opacity: 1;
            }
            
            @media (min-width: 1024px) {
                .sidebar-overlay {
                    display: none !important;
                }
            }
        </style>
    </head>
    <body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">
        <!-- Mobile sidebar overlay -->
        <div class="sidebar-overlay" 
             :class="{ 'open': sidebarOpen }"
             x-show="sidebarOpen"
             @click="sidebarOpen = false"
             x-transition:enter="ease-in-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
        </div>
        
        <!-- Mobile menu button -->
        <button 
            data-sidebar-toggle
            @click="sidebarOpen = !sidebarOpen" 
            class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-md bg-white dark:bg-gray-800 shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" 
            aria-label="Toggle menu">
            <i data-lucide="menu" class="w-6 h-6" x-show="!sidebarOpen"></i>
            <i data-lucide="x" class="w-6 h-6" x-show="sidebarOpen" x-cloak></i>
        </button>
        
        <!-- Sidebar -->
        <aside class="sidebar bg-gray-800 text-white shadow-xl">
            <div class="h-full flex flex-col">
                <!-- Logo -->
                <div class="p-4 border-b border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <img src="/hearts-after-god-ministry-site/assets/images/logo-white.png" alt="Logo" class="h-8">
                            <span class="text-xl font-bold">Admin Panel</span>
                        </div>
                        <button 
                            @click="sidebarOpen = false"
                            class="lg:hidden p-1 rounded-md text-gray-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-white">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
                
                <!-- User Profile -->
                <div class="p-4 border-b border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 rounded-full bg-gray-600 flex items-center justify-center text-white font-semibold flex-shrink-0">
                            <?= strtoupper(substr($userName, 0, 1)) ?>
                        </div>
                        <div class="min-w-0">
                            <div class="font-medium truncate"><?= $userName ?></div>
                            <div class="text-xs text-gray-400 truncate"><?= $userEmail ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-2">
                    <ul class="space-y-1 px-2">
                        <li>
                            <a href="admin-dashboard.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-md <?= $currentPage === 'admin-dashboard' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> transition-colors duration-150">
                                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="sermons.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-md <?= $currentPage === 'sermons' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> transition-colors duration-150">
                                <i data-lucide="mic-2" class="w-5 h-5 mr-3"></i>
                                <span>Sermons</span>
                            </a>
                        </li>
                        <li>
                            <a href="blog.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-md <?= $currentPage === 'blog' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> transition-colors duration-150">
                                <i data-lucide="newspaper" class="w-5 h-5 mr-3"></i>
                                <span>Blog</span>
                            </a>
                        </li>
                        <li>
                            <a href="events.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-md <?= $currentPage === 'events' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> transition-colors duration-150">
                                <i data-lucide="calendar" class="w-5 h-5 mr-3"></i>
                                <span>Events</span>
                            </a>
                        </li>
                        <li>
                            <a href="gallery.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-md <?= $currentPage === 'gallery' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> transition-colors duration-150">
                                <i data-lucide="image" class="w-5 h-5 mr-3"></i>
                                <span>Gallery</span>
                            </a>
                        </li>
                        <li>
                            <a href="users.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-md <?= $currentPage === 'users' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> transition-colors duration-150">
                                <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                                <span>Users</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <!-- Bottom section -->
                <div class="p-4 border-t border-gray-700 mt-auto">
                    <div class="flex items-center justify-between">
                        <button @click="darkMode = !darkMode" class="flex items-center text-sm text-gray-300 hover:text-white focus:outline-none transition-colors duration-150">
                            <i data-lucide="moon" class="w-5 h-5 mr-2" x-show="!darkMode"></i>
                            <i data-lucide="sun" class="w-5 h-5 mr-2" x-show="darkMode" x-cloak></i>
                            <span x-text="darkMode ? 'Light Mode' : 'Dark Mode'"></span>
                        </button>
                        <a href="/hearts-after-god-ministry-site/backend/users/logout.php" class="flex items-center text-sm text-red-400 hover:text-red-300 transition-colors duration-150">
                            <i data-lucide="log-out" class="w-5 h-5 mr-1"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main content -->
        <div class="content-wrapper">
            <?php 
            try {
                // Call the content function with the PDO connection and any additional params
                $contentFunction($pdo, ...array_values($params));
            } catch (Exception $e) {
                echo '<div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6">';
                echo '<div class="flex">';
                echo '<div class="flex-shrink-0">';
                echo '<svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">';
                echo '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />';
                echo '</svg>';
                echo '</div>';
                echo '<div class="ml-3">';
                echo '<p class="text-sm text-red-700 dark:text-red-200">';
                echo 'An error occurred while loading this content. Please try again later.';
                echo '<br><span class="text-xs text-red-600 dark:text-red-300">' . htmlspecialchars($e->getMessage()) . '</span>';
                echo '</p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                
                // Log the error
                error_log("Error in content function: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
            }
            ?>
        </div>
        
        <!-- Initialize Lucide Icons and other scripts -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Initialize Lucide icons
                if (window.lucide) {
                    lucide.createIcons();
                }
                
                // Initialize Tippy.js if available
                if (window.tippy) {
                    tippy('[data-tippy-content]');
                }
                
                // Initialize Alpine.js if available
                if (window.Alpine) {
                    Alpine.initTree(document.body);
                }
                
                // Close sidebar when clicking on a nav item on mobile
                document.querySelectorAll('.sidebar a').forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 1024) {
                            const sidebar = document.querySelector('.sidebar');
                            if (sidebar) {
                                sidebar.classList.remove('open');
                                document.dispatchEvent(new CustomEvent('sidebar-closed'));
                            }
                        }
                    });
                });
                
                // Close sidebar when pressing Escape key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && window.innerWidth < 1024) {
                        const sidebar = document.querySelector('.sidebar');
                        if (sidebar && sidebar.classList.contains('open')) {
                            sidebar.classList.remove('open');
                            document.dispatchEvent(new CustomEvent('sidebar-closed'));
                        }
                    }
                });
                
                // Initialize dark mode from cookie
                const darkModeCookie = document.cookie.split('; ').find(row => row.startsWith('darkMode='));
                const darkMode = darkModeCookie ? darkModeCookie.split('=')[1] === 'true' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                
                // Apply dark mode class if needed
                if (darkMode) {
                    document.documentElement.classList.add('dark');
                }
                
                // Make sure Alpine.js is properly initialized
                if (window.Alpine) {
                    window.Alpine.start();
                }
            } catch (error) {
                console.error('Error initializing scripts:', error);
            }
        });
        
        // Toggle dark mode function
        function toggleDarkMode() {
            const darkMode = !document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', darkMode);
            document.cookie = `darkMode=${darkMode}; path=/; max-age=31536000; samesite=lax`;
            
            // Dispatch an event in case other components need to react to theme changes
            document.dispatchEvent(new CustomEvent('theme-changed', { detail: { darkMode } }));
        }
        
        // Handle browser back/forward for dynamic content
        window.addEventListener('popstate', (event) => {
            if (event.state?.section) {
                if (typeof loadSection === 'function') {
                    loadSection(event.state.section, false);
                }
            }
        });
        </script>
    </body>
    </html>
    <?php
    
    // Flush the output buffer
    ob_end_flush();
}
