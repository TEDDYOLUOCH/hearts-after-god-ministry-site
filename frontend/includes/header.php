<?php
// includes/header.php - Fixed Header with Working Tailwind CSS
require_once __DIR__ . '/../../config/paths.php';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= isset($page_title) ? $page_title : 'Hearts After God Ministry | Leading Revival & Discipleship in Kenya' ?></title>
  <meta name="description" content="<?= isset($page_description) ? $page_description : 'Join Hearts After God Ministry - a global online ministry empowering believers through discipleship, prayer, worship, and outreach. Experience God\'s transforming power today.' ?>"/>
  <meta name="keywords" content="<?= isset($page_keywords) ? $page_keywords : 'Hearts After God Ministry, revival ministry, discipleship, prayer, worship, Kenya church, online ministry, Bible study, youth ministry, Christian community, evangelism, missions' ?>"/>
  <meta name="author" content="Hearts After God Ministry"/>
  <meta name="robots" content="index, follow"/>
  <meta name="language" content="English"/>
  <meta name="revisit-after" content="7 days"/>
  
  <!-- Open Graph Meta Tags -->
  <meta property="og:title" content="<?= isset($page_title) ? $page_title : 'Hearts After God Ministry' ?>"/>
  <meta property="og:description" content="<?= isset($page_description) ? $page_description : 'Global online ministry empowering believers through discipleship, prayer, and outreach.' ?>"/>
  <meta property="og:type" content="website"/>
  <meta property="og:url" content="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>"/>
  <meta property="og:site_name" content="Hearts After God Ministry"/>
  
  <!-- Twitter Card Meta Tags -->
  <meta name="twitter:card" content="summary_large_image"/>
  <meta name="twitter:site" content="@heartsaftergodmin"/>
  <meta name="twitter:creator" content="@heartsaftergodmin"/>
  <meta name="twitter:title" content="<?= isset($page_title) ? $page_title : 'Hearts After God Ministry' ?>"/>
  <meta name="twitter:description" content="<?= isset($page_description) ? $page_description : 'Global online ministry empowering believers through discipleship, prayer, and outreach.' ?>"/>
  
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⛪</text></svg>">
  <meta name="theme-color" content="#7C3AED">
  
  <!-- Preconnect to external domains for performance -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://cdnjs.cloudflare.com">
  
  <!-- Tailwind CSS -->
  <link href="http://<?= $_SERVER['HTTP_HOST'] ?>/hearts-after-god-ministry-site/frontend/assets/css/tailwind.css?<?= time() ?>" rel="stylesheet">
  <!-- Debug Info -->
  <script>
    console.log('Host:', '<?= $_SERVER['HTTP_HOST'] ?>');
    console.log('CSS Path:', 'http://<?= $_SERVER['HTTP_HOST'] ?>/hearts-after-god-ministry-site/frontend/assets/css/tailwind.css');
  </script>
  <!-- Google Fonts: Montserrat & Open Sans -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
  <script>
    // Disable Tailwind CDN warning
    window.tailwind = { config: {} };
    tailwind.config = {
      theme: {
        fontFamily: {
          sans: ['Open Sans', 'sans-serif'],
          heading: ['Montserrat', 'sans-serif'],
        },
        extend: {
          colors: {
            primary: {
              purple: '#7C3AED',
              amber: '#F59E0B',
              blue: '#1E40AF'
            }
          },
          fontFamily: {
            'display': ['Playfair Display', 'serif'],
            'body': ['Inter', 'sans-serif']
          },
          animation: {
            'fade-in-up': 'fadeInUp 0.6s ease-out',
            'fade-in-down': 'fadeInDown 0.6s ease-out',
            'slide-in-right': 'slideInRight 0.6s ease-out',
            'float': 'float 6s ease-in-out infinite',
            'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            'bounce-slow': 'bounce 2s infinite'
          },
          keyframes: {
            fadeInUp: {
              '0%': { opacity: '0', transform: 'translateY(30px)' },
              '100%': { opacity: '1', transform: 'translateY(0)' }
            },
            fadeInDown: {
              '0%': { opacity: '0', transform: 'translateY(-30px)' },
              '100%': { opacity: '1', transform: 'translateY(0)' }
            },
            slideInRight: {
              '0%': { opacity: '0', transform: 'translateX(30px)' },
              '100%': { opacity: '1', transform: 'translateX(0)' }
            },
            float: {
              '0%, 100%': { transform: 'translateY(0px)' },
              '50%': { transform: 'translateY(-10px)' }
            }
          }
        }
      }
    }
  </script>
  
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  
  <!-- Schema.org Structured Data -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Hearts After God Ministry",
    "alternateName": "HAGM",
    "url": "<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] ?>",
    "description": "Leading revival ministry for soul-winning, discipleship, and global outreach in Kenya and beyond.",
    "foundingDate": "2020",
    "founders": [
      {
        "@type": "Person",
        "name": "Minister Cyrilla Chikamai"
      },
      {
        "@type": "Person", 
        "name": "Minister Humphrey Mulanda"
      }
    ],
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "Nairobi",
      "addressCountry": "Kenya"
    },
    "contactPoint": {
      "@type": "ContactPoint",
      "telephone": "+254-707-529-090",
      "contactType": "customer service",
      "email": "heartsaftergodministries254@gmail.com"
    },
    "sameAs": [
      "https://www.facebook.com/share/g/16NwpW8sCB/",
      "https://www.instagram.com/reel/DK2MTiVCR-k/?igsh=NngyM2p4anFmaG9l",
      "https://youtube.com/@heartsaftergodministries",
      "https://www.tiktok.com/@heartsaftergodmin7",
      "https://t.me/+ZnRxd1gF7AcwMzY0"
    ],
    "areaServed": "Worldwide",
    "serviceType": "Religious Organization"
  }
  </script>
  
  <!-- Enhanced Custom CSS -->
  <style>
    /* Hero Section Styles */
    .hero-swiper {
      width: 100%;
      height: 100vh;
      min-height: 600px;
      overflow: hidden;
    }
    
    .hero-swiper .swiper-slide {
      position: relative;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      overflow: hidden;
      transition: transform 0.6s ease-out;
    }
    
    /* Navigation Arrows */
    .hero-swiper .swiper-button-next,
    .hero-swiper .swiper-button-prev {
      width: 50px;
      height: 50px;
      background: rgba(0, 0, 0, 0.2);
      backdrop-filter: blur(5px);
      border-radius: 50%;
      transition: all 0.3s ease;
      margin-top: -25px;
      opacity: 0.7;
    }
    
    .hero-swiper .swiper-button-next:hover,
    .hero-swiper .swiper-button-prev:hover {
      background: rgba(0, 33, 71, 0.8);
      transform: scale(1.1);
      opacity: 1;
    }
    
    .hero-swiper .swiper-button-next {
      right: 30px;
    }
    
    .hero-swiper .swiper-button-prev {
      left: 30px;
    }
    
    .hero-swiper .swiper-button-next::after,
    .hero-swiper .swiper-button-prev::after {
      font-size: 24px;
      color: #D4AF37;
      font-weight: bold;
    }
    
    /* Pagination */
    .hero-swiper .swiper-pagination {
      bottom: 30px !important;
    }
    
    .hero-swiper .swiper-pagination-bullet {
      width: 12px;
      height: 12px;
      background: rgba(255, 255, 255, 0.5);
      opacity: 1;
      margin: 0 6px !important;
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }
    
    .hero-swiper .swiper-pagination-bullet-active {
      background: #D4AF37;
      transform: scale(1.2);
      border-color: rgba(255, 255, 255, 0.8);
    }
    
    /* Scroll Down Indicator */
    .scroll-down-indicator {
      position: absolute;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10;
      animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% {
        transform: translateY(0) translateX(-50%);
      }
      40% {
        transform: translateY(-20px) translateX(-50%);
      }
      60% {
        transform: translateY(-10px) translateX(-50%);
      }
    }
    
    /* Content Animation */
    [data-swiper-parallax] {
      transition: transform 0.5s ease-out;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 1024px) {
      .hero-swiper .swiper-button-next,
      .hero-swiper .swiper-button-prev {
        width: 40px;
        height: 40px;
      }
      
      .hero-swiper .swiper-button-next::after,
      .hero-swiper .swiper-button-prev::after {
        font-size: 20px;
      }
    }
    
    @media (max-width: 768px) {
      .hero-swiper .swiper-button-next,
      .hero-swiper .swiper-button-prev {
        display: none;
      }
      
      .hero-swiper .swiper-pagination-bullet {
        width: 10px;
        height: 10px;
      }
    }
    
    :root {
      --primary-purple: #7C3AED;
      --primary-amber: #F59E0B;
      --primary-blue: #1E40AF;
      --secondary-red: #DC2626;
      --text-dark: #1F2937;
      --text-light: #6B7280;
      --bg-light: #F8FAFC;
      --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
      --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    
    * {
      scroll-behavior: smooth;
    }
    
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }
    
    .font-display {
      font-family: 'Playfair Display', Georgia, serif;
    }
    
    /* Enhanced Header Styles */
    .nav-link {
      position: relative;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .nav-link::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, var(--primary-purple), var(--primary-amber));
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      transform: translateX(-50%);
      border-radius: 1px;
    }
    
    .nav-link:hover::after,
    .nav-link.active::after {
      width: 80%;
    }
    
    .nav-link:hover {
      background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(245, 158, 11, 0.1));
      transform: translateY(-1px);
      color: var(--primary-purple);
    }
    
    .nav-link.active {
      background: linear-gradient(135deg, rgba(124, 58, 237, 0.15), rgba(245, 158, 11, 0.15));
      color: var(--primary-purple);
      font-weight: 600;
    }
    
    /* Gradient Text */
    .gradient-text {
      background: linear-gradient(135deg, var(--primary-purple), var(--primary-amber));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      color: transparent;
    }
    
    /* Enhanced Button Styles */
    .btn-primary {
      background: linear-gradient(135deg, var(--primary-amber), #f97316);
      color: white;
      padding: 0.75rem 2rem;
      border-radius: 9999px;
      font-weight: 700;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      text-decoration: none;
      border: none;
      cursor: pointer;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
      background: linear-gradient(135deg, #f97316, var(--primary-amber));
    }
    
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
    }
    
    ::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg, var(--primary-purple), var(--primary-amber));
      border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(135deg, var(--primary-amber), var(--primary-purple));
    }
    
    /* Loading Screen Styles */
    .loading-screen {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue), var(--primary-amber));
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
    }
    
    .loading-screen.hidden {
      opacity: 0;
      visibility: hidden;
    }
    
    .loading-spinner {
      width: 60px;
      height: 60px;
      border: 4px solid rgba(255, 255, 255, 0.3);
      border-top: 4px solid white;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .loading-text {
      animation: pulse 2s ease-in-out infinite;
    }
    
    /* Mobile Menu Styles */
    .mobile-menu-backdrop {
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(4px);
    }
    
    .mobile-menu-panel {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.9));
      backdrop-filter: blur(20px);
      border-left: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    /* Header Animation */
    .header-slide-down {
      animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
      from { transform: translateY(-100%); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    
    /* Focus Styles for Accessibility */
    .focus-ring:focus {
      outline: 2px solid var(--primary-amber);
      outline-offset: 2px;
    }
    
    /* Animation Delays */
    .animation-delay-200 { animation-delay: 0.2s; }
    .animation-delay-400 { animation-delay: 0.4s; }
    .animation-delay-600 { animation-delay: 0.6s; }
    
    /* Notification Styles */
    .notification {
      position: fixed;
      top: 100px;
      right: 20px;
      max-width: 400px;
      padding: 1rem 1.5rem;
      border-radius: 0.75rem;
      color: white;
      font-weight: 500;
      box-shadow: var(--shadow-xl);
      transform: translateX(100%);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      z-index: 1000;
    }
    
    .notification.show {
      transform: translateX(0);
    }
    
    .notification.success { background: linear-gradient(135deg, #10B981, #059669); }
    .notification.error { background: linear-gradient(135deg, #EF4444, #DC2626); }
    .notification.warning { background: linear-gradient(135deg, #F59E0B, #D97706); }
    .notification.info { background: linear-gradient(135deg, #3B82F6, #2563EB); }

    /* Logo Placeholder */
    .logo-placeholder {
      width: 3rem;
      height: 3rem;
      background: linear-gradient(135deg, var(--primary-purple), var(--primary-amber));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.5rem;
      font-weight: bold;
    }
  </style>
  
  <!-- Custom CSS for specific pages -->
  <?php if(isset($custom_css)): ?>
    <style><?= $custom_css ?></style>
  <?php endif; ?>
  
  <!-- Swiper JS -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</head>

<body class="font-sans bg-white text-gray-900" style="font-family: 'Open Sans', sans-serif;">
  
  <!-- Skip to Content Link for Accessibility -->
  <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded-md z-50 focus-ring">
    Skip to main content
  </a>

  <!-- Loading Screen -->
  <div id="loading-screen" class="loading-screen" role="status" aria-label="Loading">
    <div class="text-center text-white">
      <div class="loading-spinner mb-6 mx-auto"></div>
      <div class="flex items-center justify-center mb-4">
        <div class="logo-placeholder mr-4">⛪</div>
        <div>
          <h3 class="text-2xl font-bold font-display">Hearts After God</h3>
          <p class="text-amber-300 font-semibold">MINISTRY</p>
        </div>
      </div>
      <p class="text-lg loading-text">Preparing your spiritual journey...</p>
    </div>
  </div>

  <!-- Header -->
  <header id="main-header" class="fixed top-0 w-full z-50 transition-all duration-300 header-slide-down" role="banner">
    <nav class="bg-white/95 backdrop-blur-md shadow-lg border-b border-gray-100" role="navigation" aria-label="Main navigation">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
          <!-- Logo -->
          <div class="flex items-center space-x-4">
            <a href="index.php" class="flex items-center space-x-4 hover:opacity-90 transition-opacity focus-ring rounded-lg p-2" aria-label="Hearts After God Ministry Home">
              <div class="logo-placeholder">⛪</div>
              <div class="hidden sm:block">
                <h1 class="text-2xl font-bold text-gray-900 font-display">Hearts After God</h1>
                <p class="text-xs text-purple-600 font-semibold -mt-1 tracking-widest">MINISTRY</p>
              </div>
            </a>
          </div>

          <!-- Desktop Navigation -->
          <div class="hidden lg:flex items-center space-x-2">
            <?php
            // Main navigation items
            $nav_items = [
              ['url' => 'index.php', 'label' => 'Home', 'page' => 'home', 'icon' => 'fas fa-home'],
              ['url' => 'about.php', 'label' => 'About', 'page' => 'about', 'icon' => 'fas fa-info-circle'],
              ['url' => 'ministries.php', 'label' => 'Ministries', 'page' => 'ministries', 'icon' => 'fas fa-church'],
              ['url' => 'programmes.php', 'label' => 'Programmes', 'page' => 'programmes', 'icon' => 'fas fa-users'],
              [
                'label' => 'Resources', 
                'page' => 'resources',
                'icon' => 'fas fa-book-open',
                'children' => [
                  ['url' => 'events.php', 'label' => 'Events', 'page' => 'events', 'icon' => 'fas fa-calendar'],
                  ['url' => 'sermons.php', 'label' => 'Sermons', 'page' => 'sermons', 'icon' => 'fas fa-play-circle'],
                  ['url' => 'blog.php', 'label' => 'Blog', 'page' => 'blog', 'icon' => 'fas fa-blog']
                ]
              ],
              ['url' => 'team.php', 'label' => 'Team', 'page' => 'team', 'icon' => 'fas fa-user-friends'],
              ['url' => 'contact.php', 'label' => 'Contact', 'page' => 'contact', 'icon' => 'fas fa-envelope']
            ];
            
            foreach($nav_items as $item):
              $active_class = (isset($current_page) && $current_page === $item['page']) ? 'active' : '';
              $has_children = !empty($item['children']);
              $is_active_dropdown = $has_children && in_array($current_page, array_column($item['children'], 'page'));
              
              if ($has_children):
            ?>
              <div class="relative group" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open" class="flex items-center space-x-1 px-3 py-2 text-gray-700 hover:text-purple-600 font-medium transition-colors duration-200 focus-ring rounded-lg <?= $is_active_dropdown ? 'text-purple-600' : '' ?>">
                  <i class="<?= $item['icon'] ?> text-sm"></i>
                  <span><?= $item['label'] ?></span>
                  <i class="fas fa-chevron-down text-xs ml-1 transition-transform duration-200 group-hover:rotate-180" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute left-0 mt-2 w-56 origin-top-left bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50" 
                     role="menu" 
                     aria-orientation="vertical" 
                     aria-labelledby="menu-button" 
                     tabindex="-1">
                  <div class="py-1" role="none">
                    <?php foreach($item['children'] as $child): 
                      $child_active = (isset($current_page) && $current_page === $child['page']) ? 'bg-gray-100 text-purple-600' : 'text-gray-700';
                    ?>
                      <a href="<?= $child['url'] ?>" 
                         class="<?= $child_active ?> hover:bg-gray-50 hover:text-purple-600 block px-4 py-2 text-sm transition-colors duration-150 flex items-center" 
                         role="menuitem" 
                         tabindex="-1">
                        <i class="<?= $child['icon'] ?> text-sm w-5 mr-2 text-center"></i>
                        <span><?= $child['label'] ?></span>
                      </a>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <a href="<?= $item['url'] ?>" 
                 class="nav-link text-gray-700 hover:text-purple-600 font-medium transition-all duration-200 <?= $active_class ?> focus-ring" 
                 aria-current="<?= $active_class ? 'page' : 'false' ?>">
                <i class="<?= $item['icon'] ?> text-sm"></i>
                <span><?= $item['label'] ?></span>
              </a>
            <?php 
              endif;
            endforeach; 
            ?>
          </div>

          <!-- CTA Button & Search -->
          <div class="hidden md:flex items-center space-x-4">
            <!-- Quick Search -->
            <div class="relative">
              <button id="search-toggle" class="p-2 text-gray-600 hover:text-purple-600 transition-colors focus-ring rounded-lg" aria-label="Toggle search">
                <i class="fas fa-search text-lg"></i>
              </button>
              <div id="search-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 p-4 z-50">
                <div class="relative">
                  <input type="search" placeholder="Search sermons, events, articles..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                  <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
              </div>
            </div>
            
            <!-- CTA Button -->
            <a href="contact.php#get-involved" class="btn-primary focus-ring">
              <i class="fas fa-hands-helping"></i>
              <span>Get Involved</span>
            </a>
          </div>

          <!-- Mobile Menu Button -->
          <div class="lg:hidden">
            <button id="mobile-menu-btn" 
                    class="p-2 text-gray-700 hover:text-purple-600 focus:outline-none focus:text-purple-600 transition-colors focus-ring rounded-lg" 
                    aria-expanded="false"
                    aria-controls="mobile-menu"
                    aria-label="Toggle mobile menu">
              <span class="sr-only">Open main menu</span>
              <i class="fas fa-bars text-xl" id="menu-icon"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <div id="mobile-menu" class="lg:hidden hidden fixed inset-0 top-20 z-40" role="dialog" aria-modal="true">
        <div class="mobile-menu-backdrop absolute inset-0" aria-hidden="true"></div>
        <div class="mobile-menu-panel absolute right-0 top-0 bottom-0 w-80 max-w-full p-6 overflow-y-auto">
          <div class="space-y-4">
            <!-- Mobile Search -->
            <div class="relative mb-6">
              <input type="search" placeholder="Search..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
              <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
            
            <!-- Mobile Nav Items -->
            <?php foreach($nav_items as $item): 
              $has_children = !empty($item['children']);
              $is_active = (isset($current_page) && $current_page === $item['page']);
              $is_active_dropdown = $has_children && in_array($current_page, array_column($item['children'] ?? [], 'page'));
              $active_class = ($is_active || $is_active_dropdown) ? 'text-purple-600 font-semibold bg-purple-50' : '';
              
              if ($has_children):
            ?>
              <div x-data="{ open: false }" class="border-b border-gray-100 last:border-0 pb-2">
                <button @click="open = !open" 
                        class="w-full flex items-center justify-between gap-3 text-gray-700 hover:text-purple-600 font-medium py-3 px-4 rounded-lg transition-colors <?= $active_class ?> focus-ring">
                  <div class="flex items-center gap-3">
                    <i class="<?= $item['icon'] ?> w-5"></i>
                    <?= $item['label'] ?>
                  </div>
                  <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                
                <div x-show="open" 
                     x-collapse
                     class="pl-8 pr-4 space-y-2 mt-1">
                  <?php foreach($item['children'] as $child): 
                    $child_active = (isset($current_page) && $current_page === $child['page']) ? 'text-purple-600 font-semibold bg-purple-50' : 'text-gray-600';
                  ?>
                    <a href="<?= $child['url'] ?>" 
                       class="block py-2.5 px-4 rounded-lg hover:bg-gray-50 transition-colors <?= $child_active ?> focus-ring">
                      <div class="flex items-center gap-3">
                        <i class="<?= $child['icon'] ?> w-5 text-center"></i>
                        <?= $child['label'] ?>
                      </div>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php else: ?>
              <a href="<?= $item['url'] ?>" 
                 class="flex items-center gap-3 text-gray-700 hover:text-purple-600 font-medium py-3 px-4 rounded-lg transition-colors border-b border-gray-100 last:border-0 <?= $active_class ?> focus-ring">
                <i class="<?= $item['icon'] ?> w-5"></i>
                <?= $item['label'] ?>
              </a>
            <?php 
              endif;
            endforeach; 
            ?>
            
            <!-- Mobile CTA -->
            <div class="pt-6 border-t border-gray-200">
              <a href="contact.php#get-involved" class="btn-primary w-full text-center justify-center focus-ring">
                <i class="fas fa-hands-helping"></i>
                <span>Get Involved</span>
              </a>
            </div>
            
            <!-- Quick Links -->
            <div class="pt-4">
              <h4 class="text-sm font-semibold text-gray-900 mb-3">Quick Actions</h4>
              <div class="space-y-2">
                <a href="https://youtube.com/@heartsaftergodministries" target="_blank" class="flex items-center gap-3 text-gray-600 hover:text-red-600 py-2 focus-ring rounded transition-colors">
                  <i class="fab fa-youtube"></i>
                  Watch Live
                </a>
                <a href="mailto:heartsaftergodministries254@gmail.com" class="flex items-center gap-3 text-gray-600 hover:text-blue-600 py-2 focus-ring rounded transition-colors">
                  <i class="fas fa-envelope"></i>
                  Email Us
                </a>
                <a href="tel:+254707529090" class="flex items-center gap-3 text-gray-600 hover:text-green-600 py-2 focus-ring rounded transition-colors">
                  <i class="fas fa-phone"></i>
                  Call Now
                </a>
              </div>
            </div>
            
            <!-- Social Media Links -->
            <div class="pt-4 border-t border-gray-200">
              <h4 class="text-sm font-semibold text-gray-900 mb-3">Connect With Us</h4>
              <div class="flex flex-wrap gap-3">
                <?php
                $social_links = [
                  ['url' => 'https://www.facebook.com/share/g/16NwpW8sCB/', 'icon' => 'fab fa-facebook-f', 'color' => 'hover:bg-blue-600 hover:text-white'],
                  ['url' => 'https://www.instagram.com/reel/DK2MTiVCR-k/?igsh=NngyM2p4anFmaG9l', 'icon' => 'fab fa-instagram', 'color' => 'hover:bg-pink-600 hover:text-white'],
                  ['url' => 'https://youtube.com/@heartsaftergodministries', 'icon' => 'fab fa-youtube', 'color' => 'hover:bg-red-600 hover:text-white'],
                  ['url' => 'https://chat.whatsapp.com/F1BIzqQTulA5t5XlUDLWhK', 'icon' => 'fab fa-whatsapp', 'color' => 'hover:bg-green-600 hover:text-white'],
                  ['url' => 'https://www.tiktok.com/@heartsaftergodmin7', 'icon' => 'fab fa-tiktok', 'color' => 'hover:bg-gray-800 hover:text-white']
                ];
                
                foreach($social_links as $social): ?>
                  <a href="<?= $social['url'] ?>" target="_blank" class="p-3 bg-gray-100 <?= $social['color'] ?> rounded-lg transition-colors focus-ring">
                    <i class="<?= $social['icon'] ?>"></i>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <!-- Main Content Wrapper -->
  <main id="main-content" role="main">
    <!-- Page content goes here -->
    
  <!-- Enhanced Header JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Loading Screen with enhanced UX
      const loadingScreen = document.getElementById('loading-screen');
      const minLoadTime = 1200; // Minimum loading time for better UX
      const startTime = Date.now();
      
      window.addEventListener('load', () => {
        const elapsedTime = Date.now() - startTime;
        const remainingTime = Math.max(0, minLoadTime - elapsedTime);
        
        setTimeout(() => {
          if (loadingScreen) {
            loadingScreen.classList.add('hidden');
          }
        }, remainingTime);
      });

      // Enhanced Mobile Menu
      const mobileMenuBtn = document.getElementById('mobile-menu-btn');
      const mobileMenu = document.getElementById('mobile-menu');
      const mobileMenuBackdrop = mobileMenu?.querySelector('.mobile-menu-backdrop');
      const menuIcon = document.getElementById('menu-icon');
      
      function toggleMobileMenu() {
        if (mobileMenu) {
          const isHidden = mobileMenu.classList.contains('hidden');
          
          if (isHidden) {
            mobileMenu.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            mobileMenuBtn.setAttribute('aria-expanded', 'true');
            menuIcon.classList.replace('fa-bars', 'fa-times');
          } else {
            mobileMenu.classList.add('hidden');
            document.body.style.overflow = '';
            mobileMenuBtn.setAttribute('aria-expanded', 'false');
            menuIcon.classList.replace('fa-times', 'fa-bars');
          }
        }
      }

      if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
      }

      if (mobileMenuBackdrop) {
        mobileMenuBackdrop.addEventListener('click', toggleMobileMenu);
      }

      // Close mobile menu on link clicks
      const mobileLinks = mobileMenu?.querySelectorAll('a:not([target="_blank"])');
      mobileLinks?.forEach(link => {
        link.addEventListener('click', () => {
          toggleMobileMenu();
        });
      });

      // Enhanced Search Functionality
      const searchToggle = document.getElementById('search-toggle');
      const searchDropdown = document.getElementById('search-dropdown');
      
      if (searchToggle && searchDropdown) {
        searchToggle.addEventListener('click', () => {
          searchDropdown.classList.toggle('hidden');
          if (!searchDropdown.classList.contains('hidden')) {
            const searchInput = searchDropdown.querySelector('input');
            searchInput.focus();
          }
        });

        // Close search dropdown when clicking outside
        document.addEventListener('click', (e) => {
          if (!searchToggle.contains(e.target) && !searchDropdown.contains(e.target)) {
            searchDropdown.classList.add('hidden');
          }
        });

        // Handle search input
        const searchInput = searchDropdown.querySelector('input');
        searchInput?.addEventListener('keypress', (e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            const query = e.target.value.trim();
            if (query) {
              // Simulate search - replace with actual search functionality
              console.log('Searching for:', query);
              Hearts.showNotification(`Searching for "${query}"...`, 'info');
              searchDropdown.classList.add('hidden');
            }
          }
        });
      }

      // Enhanced Header Scroll Effects
      let lastScrollTop = 0;
      let ticking = false;
      const header = document.getElementById('main-header');
      
      function updateHeader() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Background and blur effects
        if (scrollTop > 100) {
          header.style.background = 'rgba(255, 255, 255, 0.98)';
          header.style.backdropFilter = 'blur(20px)';
          header.style.borderBottom = '1px solid rgba(0, 0, 0, 0.1)';
          header.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
        } else {
          header.style.background = 'rgba(255, 255, 255, 0.95)';
          header.style.backdropFilter = 'blur(16px)';
          header.style.borderBottom = '1px solid rgba(0, 0, 0, 0.05)';
          header.style.boxShadow = '0 1px 3px 0 rgba(0, 0, 0, 0.1)';
        }
        
        // Auto-hide on scroll down (except on mobile)
        if (window.innerWidth > 768) {
          if (scrollTop > lastScrollTop && scrollTop > 200) {
            header.style.transform = 'translateY(-100%)';
          } else {
            header.style.transform = 'translateY(0)';
          }
        }
        
        lastScrollTop = scrollTop;
        ticking = false;
      }
      
      window.addEventListener('scroll', () => {
        if (!ticking) {
          requestAnimationFrame(updateHeader);
          ticking = true;
        }
      });

      // Enhanced Keyboard Navigation
      document.addEventListener('keydown', function(e) {
        // Escape key closes mobile menu and search
        if (e.key === 'Escape') {
          if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
            toggleMobileMenu();
          }
          if (searchDropdown && !searchDropdown.classList.contains('hidden')) {
            searchDropdown.classList.add('hidden');
          }
        }
        
        // Ctrl+K opens search
        if (e.ctrlKey && e.key === 'k') {
          e.preventDefault();
          if (searchDropdown) {
            searchDropdown.classList.remove('hidden');
            searchDropdown.querySelector('input').focus();
          }
        }
        
        // Alt+M toggles mobile menu
        if (e.altKey && e.key === 'm') {
          e.preventDefault();
          toggleMobileMenu();
        }
      });

      // Accessibility: Focus management
      const focusableElements = 'a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select';
      
      function trapFocus(element) {
        const focusable = element.querySelectorAll(focusableElements);
        const firstFocusable = focusable[0];
        const lastFocusable = focusable[focusable.length - 1];
        
        element.addEventListener('keydown', (e) => {
          if (e.key === 'Tab') {
            if (e.shiftKey) {
              if (document.activeElement === firstFocusable) {
                lastFocusable.focus();
                e.preventDefault();
              }
            } else {
              if (document.activeElement === lastFocusable) {
                firstFocusable.focus();
                e.preventDefault();
              }
            }
          }
        });
      }

      // Apply focus trap to mobile menu
      if (mobileMenu) {
        trapFocus(mobileMenu);
      }

      // Add smooth scroll behavior for anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            const headerHeight = header.offsetHeight;
            const targetPosition = target.offsetTop - headerHeight - 20;
            
            window.scrollTo({
              top: targetPosition,
              behavior: 'smooth'
            });
          }
        });
      });
    });

    // Enhanced Global utility functions
    window.Hearts = window.Hearts || {
      // Enhanced notification system
      showNotification: function(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        const icons = {
          success: 'fas fa-check-circle',
          error: 'fas fa-exclamation-circle',
          warning: 'fas fa-exclamation-triangle',
          info: 'fas fa-info-circle'
        };
        
        notification.className = `notification ${type}`;
        notification.innerHTML = `
          <div class="flex items-center">
            <i class="${icons[type] || icons.info} mr-3 text-xl"></i>
            <div class="flex-1">
              <p class="font-medium">${message}</p>
            </div>
            <button class="ml-4 text-white hover:text-gray-200 focus:outline-none" onclick="this.parentElement.parentElement.remove()">
              <i class="fas fa-times"></i>
            </button>
          </div>
        `;
        
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);
        
        // Auto remove
        setTimeout(() => {
          notification.classList.remove('show');
          setTimeout(() => notification.remove(), 300);
        }, duration);
      },
      
      // Enhanced email validation
      validateEmail: function(email) {
        const re = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
        return re.test(email);
      },
      
      // Phone formatting
      formatPhone: function(phone) {
        return phone.replace(/(\d{3})(\d{3})(\d{3})(\d{3})/, '+$1 $2 $3 $4');
      },
      
      // Scroll to element with header offset
      scrollTo: function(element, offset = 100) {
        const target = typeof element === 'string' ? document.querySelector(element) : element;
        if (target) {
          const headerHeight = document.getElementById('main-header').offsetHeight;
          const targetPosition = target.offsetTop - headerHeight - offset;
          
          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
          });
        }
      },
      
      // Copy text to clipboard
      copyToClipboard: function(text) {
        if (navigator.clipboard) {
          navigator.clipboard.writeText(text).then(() => {
            this.showNotification('Copied to clipboard!', 'success', 2000);
          });
        } else {
          // Fallback for older browsers
          const textArea = document.createElement('textarea');
          textArea.value = text;
          document.body.appendChild(textArea);
          textArea.select();
          document.execCommand('copy');
          document.body.removeChild(textArea);
          this.showNotification('Copied to clipboard!', 'success', 2000);
        }
      }
    };
  </script>

  <!-- Include Swiper.js -->
  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>