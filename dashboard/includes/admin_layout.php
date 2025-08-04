<?php
/**
 * Admin Layout Template
 * 
 * This file contains the common layout structure for admin pages
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/../../config/db.php';

// Function to check if user is logged in and has admin role
function checkAdminAccess() {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
        exit;
    }
    
    // Check if user has admin role
    $userRole = strtolower($_SESSION['user_role'] ?? '');
    if ($userRole !== 'admin') {
        http_response_code(403);
        die('<div style="max-width: 800px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h2 style="color: #dc3545; margin-top: 0;">Access Denied</h2>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <p>You do not have permission to access this page.</p>
                    <p>Your role: ' . htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'Guest')) . '</p>
                    <p>Required role: Admin</p>
                </div>
                <p>Please contact the administrator if you believe this is an error.</p>
                <p><a href="/hearts-after-god-ministry-site/backend/users/login.php" style="display: inline-block; background: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 15px;">Go to Login</a></p>
              </div>');
    }
    
    return true;
}

// Verify database connection and admin access
try {
    // Test database connection
    $pdo = getDbConnection();
    
    // Check if user is logged in and has admin role
    checkAdminAccess();
    
    // Get current user info
    $userName = htmlspecialchars($_SESSION['user_name'] ?? 'Admin');
    $userAvatar = htmlspecialchars($_SESSION['user_avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=4f46e5&color=fff');
    
} catch (PDOException $e) {
    die('<div style="max-width: 800px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h2 style="color: #dc3545; margin-top: 0;">Database Error</h2>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                <p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ' on line ' . $e->getLine() . '</p>
            </div>
            <p>Please check the database configuration and try again.</p>
            <p><a href="/hearts-after-god-ministry-site/" style="display: inline-block; background: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 15px;">Return to Home</a></p>
          </div>');
} catch (Exception $e) {
    // Show detailed error on screen for debugging
    die('<div style="max-width: 800px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h2 style="color: #dc3545; margin-top: 0;">Error Initializing Admin Area</h2>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                <p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ' on line ' . $e->getLine() . '</p>
                <pre style="background: #f5f5f5; padding: 10px; overflow: auto; margin-top: 10px; max-height: 200px;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>
                <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
                    <h4>Session Data:</h4>
                    <pre style="margin: 0; font-size: 12px; max-height: 150px; overflow: auto;">' . htmlspecialchars(print_r($_SESSION, true)) . '</pre>
                </div>
            </div>
            <p>Please contact the administrator if this error persists.</p>
            <p>
                <a href="/hearts-after-god-ministry-site/" style="display: inline-block; background: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 15px; margin-right: 10px;">Return to Home</a>
                <a href="javascript:history.back()" style="display: inline-block; background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 15px;">Go Back</a>
            </p>
          </div>');
}

// Get current user info
$userName = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';
$userAvatar = isset($_SESSION['user_avatar']) ? htmlspecialchars($_SESSION['user_avatar']) : 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=4f46e5&color=fff';

// Include database configuration
require_once __DIR__ . '/../../config/db.php';

// Function to get page title from filename
function getPageTitle($page) {
    $titles = [
        'dashboard' => 'Dashboard',
        'sermons' => 'Sermons',
        'blog' => 'Blog',
        'events' => 'Events',
        'gallery' => 'Gallery',
        'users' => 'Users',
        'settings' => 'Settings'
    ];
    return $titles[$page] ?? ucfirst($page);
}

/**
 * Renders the admin layout with the given content
 * 
 * @param string $title Page title
 * @param callable $contentFunction Function that returns the page content
 * @param array $params Optional parameters to pass to the content function
 */
function renderAdminLayout($title, $contentFunction, $params = []) {
    global $userName, $userAvatar;
    $currentPage = $_GET['page'] ?? 'dashboard';
    $pageTitle = getPageTitle($currentPage);
    
    // Get database connection if not provided in params
    $pdo = $params['pdo'] ?? getDbConnection();
    
    // Start output buffering with gzip compression if available
    if (!headers_sent() && ob_get_level() === 0) {
        if (!ob_start('ob_gzhandler')) {
            ob_start();
        }
    }
    ?>
    <!DOCTYPE html>
<html lang="en" x-data="app()" x-init="init()" :class="{ 'dark': darkMode }" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - Admin Dashboard</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="https://www.w3.org/favicon.ico">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom styles -->
    <style>
        [x-cloak] { display: none !important; }
        body { 
            font-family: 'Inter', sans-serif;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }
        .sidebar {
            width: 16rem;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            transition: transform 0.3s ease-in-out;
            z-index: 40;
        }
        .content-wrapper {
            margin-left: 17rem;
            width: calc(100% - 17rem);
            min-height: 100vh;
            transition: margin 0.3s ease-in-out;
            padding: 2rem;
            box-sizing: border-box;
            overflow-x: auto;
            max-width: 100%;
        }
        .content-area { 
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            padding: 0;
            margin: 0 auto;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .content-wrapper {
                margin-left: 0;
            }
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 30;
                display: none;
            }
            .sidebar-overlay.open {
                display: block;
            }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar -->
    <div class="sidebar bg-gray-800 text-white" 
         :class="{'open': mobileMenuOpen}">
        <?php 
        // Include sidebar content only once
        static $sidebarIncluded = false;
        if (!$sidebarIncluded) {
            include __DIR__ . '/../../backend/includes/admin_header.php';
            $sidebarIncluded = true;
        }
        ?>
    </div>
    
    <!-- Mobile sidebar overlay -->
    <div class="sidebar-overlay"
         :class="{'open': mobileMenuOpen}"
         @click="mobileMenuOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>
        
    <!-- Main content area -->
    <div class="content-wrapper">
        <!-- Mobile header (hidden on desktop) -->
        <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-4 flex items-center justify-between sticky top-0 z-20 md:hidden">
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white"><?= $pageTitle ?? 'Dashboard' ?></h1>
            <div class="flex items-center space-x-4">
                <!-- Dark mode toggle -->
                <button @click="toggleDarkMode" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i x-show="!darkMode" data-lucide="moon" class="w-5 h-5 text-gray-500 dark:text-gray-400"></i>
                    <i x-show="darkMode" data-lucide="sun" class="w-5 h-5 text-yellow-400"></i>
                </button>
            </div>
        </header>
        
        <!-- Page content will be loaded here dynamically -->
        <main id="contentArea" class="content-area">
            <div class="w-full max-w-full mx-auto">
                <?php 
                try {
                    call_user_func_array($contentFunction, array_merge([$pdo], $params));
                } catch (Exception $e) {
                    echo '<div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6">';
                    echo '<div class="flex">';
                    echo '<div class="flex-shrink-0">';
                    echo '<svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">';
                    echo '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />';
                    echo '</svg>';
                    echo '</div>';
                    echo '<div class="ml-3">';
                    echo '<p class="text-sm text-red-700 dark:text-red-300">' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </main>
    </div>

    <!-- Mobile sidebar overlay -->
    <div id="mobileSidebarOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden"></div>

    <script>
        // Initialize Alpine.js
        document.addEventListener('alpine:init', () => {
            Alpine.data('app', () => ({
                darkMode: localStorage.getItem('darkMode') === 'true' || 
                           (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                mobileMenuOpen: false,
                isLoading: false,
                
                init() {
                    // Apply dark mode class on initial load
                    this.applyDarkMode();
                    
                    // Handle navigation
                    this.setupNavigation();
                    
                    // Handle back/forward browser navigation
                    window.addEventListener('popstate', (e) => {
                        if (e.state && e.state.url) {
                            this.loadPage(e.state.url, false);
                        }
                    });
                    
                    // Close mobile menu when clicking on a link on mobile
                    document.querySelectorAll('.sidebar a').forEach(link => {
                        link.addEventListener('click', (e) => {
                            if (window.innerWidth < 768) {
                                this.mobileMenuOpen = false;
                            }
                        });
                    });
                    
                    // Initialize Lucide Icons
                    lucide.createIcons();
                },
                
                applyDarkMode() {
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                },
                
                toggleDarkMode() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode);
                    this.applyDarkMode();
                },
                
                setupNavigation() {
                    // Handle all internal navigation links
                    document.addEventListener('click', (e) => {
                        // Find the closest anchor tag
                        let target = e.target.closest('a');
                        if (!target) return;
                        
                        const href = target.getAttribute('href');
                        if (!href) return;
                        
                        // Only handle internal links
                        if (target.getAttribute('target') === '_blank' || 
                            href.startsWith('http') || 
                            href.startsWith('#')) {
                            return;
                        }
                        
                        // Don't handle if it's a form submission or has a special modifier key
                        if (e.button !== 0 || e.ctrlKey || e.shiftKey || e.altKey || e.metaKey) {
                            return;
                        }
                        
                        // Prevent default navigation
                        e.preventDefault();
                        
                        // Load the page content
                        this.loadPage(href, true);
                    });
                },
                
                async loadPage(url, updateHistory = true) {
                    try {
                        this.isLoading = true;
                        
                        // Show loading state
                        const contentArea = document.getElementById('contentArea');
                        if (contentArea) {
                            contentArea.innerHTML = `
                                <div class="flex items-center justify-center h-64">
                                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                                </div>
                            `;
                        }
                        
                        // Add loading class to body
                        document.body.classList.add('loading');
                        
                        // Fetch the page content
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        const html = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        // Extract the content area from the loaded page
                        const newContent = doc.getElementById('contentArea') || 
                                         doc.querySelector('.content-area') || 
                                         doc.querySelector('main');
                        
                        if (!newContent || !contentArea) {
                            throw new Error('Content area not found');
                        }
                        
                        // Update the content area with a fade transition
                        contentArea.style.opacity = '0';
                        
                        setTimeout(() => {
                            contentArea.innerHTML = newContent.innerHTML;
                            contentArea.style.opacity = '1';
                            
                            // Update the page title
                            const newTitle = doc.title || 'Admin Dashboard';
                            document.title = newTitle;
                            
                            // Update browser history
                            if (updateHistory) {
                                window.history.pushState({ url }, newTitle, url);
                            }
                            
                            // Re-initialize any JavaScript components
                            this.initializePageScripts();
                            
                            // Update active nav item
                            this.updateActiveNav(url);
                            
                            // Scroll to top
                            window.scrollTo(0, 0);
                            
                            // Remove loading class
                            document.body.classList.remove('loading');
                            
                        }, 200);
                        
                    } catch (error) {
                        console.error('Error loading page:', error);
                        const contentArea = document.getElementById('contentArea');
                        if (contentArea) {
                            contentArea.innerHTML = `
                                <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-red-700 dark:text-red-300">Error loading page. Please try again.</p>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        
                        // Remove loading class
                        document.body.classList.remove('loading');
                    } finally {
                        this.isLoading = false;
                    }
                },
                
                updateActiveNav(url) {
                    // Remove active class from all nav items
                    document.querySelectorAll('.nav-item').forEach(item => {
                        item.classList.remove('bg-gray-900', 'text-white');
                        item.classList.add('text-gray-300', 'hover:bg-gray-700', 'hover:text-white');
                    });
                    
                    // Find the matching nav item and make it active
                    const navLinks = document.querySelectorAll('.sidebar a');
                    navLinks.forEach(link => {
                        if (link.getAttribute('href') === url) {
                            link.classList.add('bg-gray-900', 'text-white');
                            link.classList.remove('text-gray-300', 'hover:bg-gray-700', 'hover:text-white');
                        }
                    });
                },
                
                initializePageScripts() {
                    // Re-initialize Lucide Icons
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                    
                    // Re-initialize any other page-specific scripts here
                    if (typeof initPageScripts === 'function') {
                        initPageScripts();
                    }
                }
            }));
        });
        
        // Add a global function that can be called from other scripts
        function navigateTo(url) {
            const app = document.querySelector('[x-data]')?.__x?.$data;
            if (app && typeof app.loadPage === 'function') {
                app.loadPage(url, true);
            } else {
                window.location.href = url;
            }
        }
    </script>
    <script>
            // Initialize everything when the DOM is loaded
            // Initialize dynamic content
            function initDynamicContent() {
                // Initialize any dynamic content here
                console.log('Dynamic content initialized');
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Mobile menu toggle
                document.getElementById('mobileMenuButton')?.addEventListener('click', function() {
                    const sidebar = document.getElementById('mobileSidebar');
                    const overlay = document.getElementById('mobileSidebarOverlay');
                    
                    if (sidebar.classList.contains('hidden')) {
                        sidebar.classList.remove('hidden');
                        overlay.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                    } else {
                        sidebar.classList.add('hidden');
                        overlay.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                });

                // Close mobile menu when clicking overlay
                document.getElementById('mobileSidebarOverlay')?.addEventListener('click', function() {
                    document.getElementById('mobileSidebar').classList.add('hidden');
                    this.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                });

                // Handle navigation link clicks
                document.querySelectorAll('.nav-item').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const url = this.getAttribute('href');
                        loadContent(url);
                    });
                });

                // Handle browser back/forward buttons
                window.addEventListener('popstate', function(event) {
                    if (event.state && event.state.url) {
                        loadContent(event.state.url, false);
                    }
                });

                // Handle dark mode toggle
                const darkModeToggle = document.getElementById('darkModeToggle');
                if (darkModeToggle) {
                    darkModeToggle.addEventListener('click', function() {
                        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                            localStorage.theme = 'light';
                            document.documentElement.classList.remove('dark');
                        } else {
                            localStorage.theme = 'dark';
                            document.documentElement.classList.add('dark');
                        }
                    });
                }
                
                // Initialize dynamic content
                initDynamicContent();
            });
        </script>
    </body>
    </html>
    <?php
    
    // Flush output buffer
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}
?>
