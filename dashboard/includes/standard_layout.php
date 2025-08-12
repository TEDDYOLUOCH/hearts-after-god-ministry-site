<?php
/**
 * Standard Admin Layout
 * 
 * A consistent layout to be used across all admin dashboard pages
 */

/**
 * Renders the standard admin layout with the given content
 * 
 * @param string $title The page title
 * @param callable $contentFunction Function that renders the page content
 * @param array $params Additional parameters to pass to the content function
 */
function renderStandardLayout($title, $content = null) {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
        exit;
    }

    // Navigation items
    $navItems = [
        ['title' => 'Dashboard', 'icon' => 'layout-dashboard', 'section' => 'dashboard'],
        ['title' => 'Blog', 'icon' => 'file-text', 'section' => 'blog'],
        ['title' => 'Events', 'icon' => 'calendar', 'section' => 'events'],
        ['title' => 'Sermons', 'icon' => 'mic-2', 'section' => 'sermons'],
        ['title' => 'Gallery', 'icon' => 'image', 'section' => 'gallery'],
    ];

    // Add admin-only items
    if (($_SESSION['user_role'] ?? '') === 'admin') {
        $navItems[] = ['title' => 'Users', 'icon' => 'users', 'section' => 'users'];
        $navItems[] = ['title' => 'Settings', 'icon' => 'settings', 'section' => 'settings'];
    }

    // Get current section from URL or default to dashboard
    $currentSection = $_GET['section'] ?? 'dashboard';
    
    // Prepare Alpine.js data
    $alpineData = [
        'darkMode' => false,
        'mobileMenuOpen' => false,
        'currentSection' => $currentSection,
        'loading' => false
    ];
    
    // Check for dark mode preference
    if (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true') {
        $alpineData['darkMode'] = true;
    }
    
    // Get logo path
    $logoPath = '/hearts-after-god-ministry-site/assets/images/logo-white.png';
    $logoHtml = file_exists($_SERVER['DOCUMENT_ROOT'] . $logoPath) 
        ? '<img src="' . $logoPath . '" alt="Logo" class="h-8">' 
        : '<div class="text-white font-bold text-lg">Hearts After God</div>';
    
    ?>
    <!DOCTYPE html>
    <html lang="en" x-data='<?= json_encode($alpineData) ?>' :class="{ 'dark': darkMode }">
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
            }
        </script>
        
        <!-- Lucide Icons -->
        <script src="https://unpkg.com/lucide@latest"></script>
        
        <!-- Tippy.js for tooltips -->
        <script src="https://unpkg.com/@tippyjs/6"></script>
        
        <!-- Alpine.js Core -->
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
        
        <!-- Initialize Alpine Store -->
        <script>
        // Make sure Alpine is loaded
        document.addEventListener('alpine:init', () => {
            // Create and register the store
            Alpine.store('app', {
                darkMode: <?= ($_COOKIE['darkMode'] ?? 'false') === 'true' ? 'true' : 'false' ?>,
                mobileMenuOpen: false,
                currentSection: '<?= $currentSection ?>',
                loading: false,
                error: null,
                initialLoad: true,
                
                init() {
                    // Set initial dark mode
                    document.documentElement.classList.toggle('dark', this.darkMode);
                    
                    // Initialize components
                    if (window.lucide) lucide.createIcons();
                    if (window.tippy) tippy('[data-tippy-content]');
                    
                    // Load initial section
                    if (this.initialLoad) {
                        this.loadSection(this.currentSection, false);
                    }
                },
                
                toggleDarkMode() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode);
                    document.cookie = `darkMode=${this.darkMode}; path=/; max-age=31536000; samesite=lax`;
                    document.documentElement.classList.toggle('dark', this.darkMode);
                },
                
                toggleMobileMenu() {
                    this.mobileMenuOpen = !this.mobileMenuOpen;
                },
                
                async loadSection(section, pushState = true) {
                    if (this.currentSection === section || this.loading) return;
                    
                    this.loading = true;
                    this.currentSection = section;
                    this.error = null;
                    
                    try {
                        const response = await fetch(`?section=${section}&ajax=1`);
                        if (!response.ok) throw new Error('Failed to load section');
                        
                        const data = await response.json();
                        
                        if (data.success && data.html) {
                            document.getElementById('dynamic-content').innerHTML = data.html;
                            
                            if (pushState) {
                                const url = new URL(window.location);
                                url.searchParams.set('section', section);
                                window.history.pushState({ section }, '', url);
                            }
                            
                            // Re-initialize components
                            if (window.lucide) lucide.createIcons();
                            if (window.tippy) tippy('[data-tippy-content]');
                            if (window.Alpine) Alpine.initTree(document.body);
                        } else {
                            throw new Error(data.message || 'Failed to load section');
                        }
                    } catch (error) {
                        console.error('Error loading section:', error);
                        this.error = 'Error loading content. Please try again.';
                    } finally {
                        this.loading = false;
                        this.initialLoad = false;
                    }
                }
            });
        });
        
        // Handle browser back/forward navigation
        window.addEventListener('popstate', (event) => {
            if (event.state?.section) {
                const app = Alpine.store('app');
                if (app && typeof app.loadSection === 'function') {
                    app.loadSection(event.state.section, false);
                }
            }
        });
        </script>
        
        <!-- Custom CSS -->
        <style>
            [x-cloak] { display: none !important; }
            body { font-family: 'Inter', sans-serif; }
            .sidebar { transition: all 0.3s; }
            .content-wrapper { transition: all 0.3s; }
            @media (max-width: 1023px) {
                .sidebar { transform: translateX(-100%); }
                .sidebar.open { transform: translateX(0); }
                .content-wrapper { margin-left: 0; }
            }
        </style>
    </head>
    <body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex" 
          x-data="{}"
          x-init="
            // Wait for Alpine to be fully initialized
            document.addEventListener('alpine:initialized', () => {
                // Initialize the app
                $store.app.init();
                
                // Load initial section if not already loaded
                if ($store.app.initialLoad) {
                    $store.app.loadSection($store.app.currentSection, false);
                }
            });
          "
          :class="{ 'dark': $store.app.darkMode }">
        <!-- Mobile menu button -->
        <div class="fixed top-4 left-4 z-40 lg:hidden">
            <button @click="$store.app.toggleMobileMenu()" class="p-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none">
                <i x-show="!$store.app.mobileMenuOpen" data-lucide="menu" class="w-6 h-6"></i>
                <i x-show="$store.app.mobileMenuOpen" data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-800 text-white transform lg:translate-x-0 transition-transform duration-300 ease-in-out" 
               :class="{ 'translate-x-0': $store.app.mobileMenuOpen, '-translate-x-full': !$store.app.mobileMenuOpen }">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-center h-16 px-6 border-b border-gray-700">
                    <?= $logoHtml ?>
                </div>
                
                <!-- User profile -->
                <div class="px-6 py-4 border-b border-gray-700">
                    <div class="flex items-center">
                        <img src="<?= $_SESSION['user_avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name'] ?? 'User') ?>" 
                             alt="User" class="h-10 w-10 rounded-full">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></p>
                            <p class="text-xs text-gray-400"><?= ucfirst($_SESSION['user_role'] ?? 'user') ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-2">
                    <ul class="px-2 space-y-1">
                        <?php foreach ($navItems as $item): ?>
    <li>
        <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php?section=<?= $item['section'] ?>"
           @click.prevent="$store.app.loadSection('<?= $item['section'] ?>')"
           class="flex items-center px-4 py-3 text-sm font-medium rounded-md transition-colors duration-200"
           :class="$store.app.currentSection === '<?= $item['section'] ?>' 
               ? 'bg-gray-700 text-white' 
               : 'text-gray-300 hover:bg-gray-700 hover:text-white'">
            <i data-lucide="<?= $item['icon'] ?>" class="w-5 h-5 mr-3"></i>
            <?= $item['title'] ?>
        </a>
    </li>
<?php endforeach; ?>
                    </ul>
                </nav>
                
                <!-- Bottom section -->
                <div class="p-4 border-t border-gray-700">
                    <div class="flex items-center justify-between">
                        <button @click="$store.app.toggleDarkMode()" class="text-gray-300 hover:text-white">
                            <i x-show="!$store.app.darkMode" data-lucide="moon" class="w-5 h-5"></i>
                            <i x-show="$store.app.darkMode" data-lucide="sun" class="w-5 h-5"></i>
                        </button>
                        <a href="/hearts-after-god-ministry-site/backend/users/logout.php" 
                           class="text-gray-300 hover:text-white">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main content -->
        <div class="content-wrapper min-h-screen bg-gray-50 dark:bg-gray-900 transition-all duration-300 lg:ml-64 w-full">
            <!-- Loading overlay -->
            <div x-show="$store.app.loading" 
                 x-transition:enter="transition-opacity ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl">
                    <div class="flex items-center space-x-3">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
                        <span class="text-gray-700 dark:text-gray-200">Loading...</span>
                    </div>
                </div>
            </div>
            
            <!-- Dynamic content area -->
            <div id="dynamic-content" class="min-h-screen p-6">
                <?php if (is_callable($content)) echo $content(); ?>
            </div>
        </div>
    </body>
    </html>
    <?php
}
