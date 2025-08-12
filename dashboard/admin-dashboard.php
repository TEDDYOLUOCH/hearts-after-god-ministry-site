<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hearts After God Ministry</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/hearts-after-god-ministry-site/dashboard/css/admin-dashboard.css">
    
    <!-- Blog Posts JavaScript -->
    <script src="/hearts-after-god-ministry-site/dashboard/js/blog-posts.js"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Tippy.js for tooltips -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    
    <!-- Chart.js for real-time charts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <style>
        .glass-morphism {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .dark .glass-morphism {
            background: rgba(31, 41, 55, 0.95);
            border: 1px solid rgba(55, 65, 81, 0.3);
        }
        .gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }
        .sidebar-item {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .sidebar-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .sidebar-item:hover::before {
            left: 100%;
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .notification-dot {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 10px;
        }
        .modal {
            transition: opacity 0.25s ease;
        }
        .modal.show {
            opacity: 1;
            visibility: visible;
        }
        .modal.hide {
            opacity: 0;
            visibility: hidden;
        }
        .modal-content {
            animation: slideUp 0.3s ease-out;
        }
        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .modal-overlay {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        .content-section {
            display: none;
        }
        .content-section.active {
            display: block;
        }
        .stat-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        @keyframes countUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .count-up {
            animation: countUp 0.5s ease-out;
        }
        .data-table {
            border-collapse: separate;
            border-spacing: 0;
        }
        .data-table th,
        .data-table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 16px;
        }
        .data-table th {
            background: #f9fafb;
        }
        .dark .data-table th {
            background: #374151;
        }
        .dark .data-table th,
        .dark .data-table td {
            border-bottom: 1px solid #374151;
        }
        .activity-item {
            animation: slideInRight 0.5s ease-out;
        }
        @keyframes slideInRight {
            from { 
                opacity: 0; 
                transform: translateX(20px); 
            }
            to { 
                opacity: 1; 
                transform: translateX(0); 
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 w-72 z-50 transform transition-transform duration-300 ease-in-out lg:translate-x-0 -translate-x-full">
        <div class="glass-morphism h-full shadow-2xl">
            <!-- Logo Section -->
            <div class="gradient-primary text-white p-6 relative overflow-hidden">
                <div class="absolute inset-0 bg-black opacity-10"></div>
                <div class="relative z-10">
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-heart text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold">Hearts After God</h1>
                            <p class="text-xs opacity-80">Ministry Dashboard</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Profile -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full flex items-center justify-center text-white font-semibold">
                            AU
                        </div>
                        <div id="onlineStatus" class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-white notification-dot"></div>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-white">Admin User</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Administrator</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2 custom-scrollbar overflow-y-auto" style="height: calc(100vh - 200px);">
                <div class="sidebar-item">
                    <a href="#" class="nav-link flex items-center px-4 py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg" data-section="dashboard">
                        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center mr-3">
                            <i class="fas fa-home text-sm"></i>
                        </div>
                        <span class="font-medium">Dashboard</span>
                        <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                    </a>
                </div>

                <div class="sidebar-item">
                    <a href="#" class="nav-link flex items-center px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 group" data-section="blog">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900 flex items-center justify-center mr-3 group-hover:bg-blue-200 dark:group-hover:bg-blue-800">
                            <i class="fas fa-blog text-blue-600 dark:text-blue-400 text-sm"></i>
                        </div>
                        <span class="font-medium">Blog Posts</span>
                        <span id="blogCount" class="ml-auto bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 px-2 py-1 rounded-full text-xs font-semibold">24</span>
                    </a>
                </div>

                <div class="sidebar-item">
                    <a href="#" class="nav-link flex items-center px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 group" data-section="events">
                        <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900 flex items-center justify-center mr-3 group-hover:bg-green-200 dark:group-hover:bg-green-800">
                            <i class="fas fa-calendar text-green-600 dark:text-green-400 text-sm"></i>
                        </div>
                        <span class="font-medium">Events</span>
                        <span id="eventsCount" class="ml-auto bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 px-2 py-1 rounded-full text-xs font-semibold">3</span>
                    </a>
                </div>

                <div class="sidebar-item">
                    <a href="#" class="nav-link flex items-center px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 group" data-section="sermons">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900 flex items-center justify-center mr-3 group-hover:bg-purple-200 dark:group-hover:bg-purple-800">
                            <i class="fas fa-microphone text-purple-600 dark:text-purple-400 text-sm"></i>
                        </div>
                        <span class="font-medium">Sermons</span>
                        <span id="sermonsCount" class="ml-auto bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 px-2 py-1 rounded-full text-xs font-semibold">New</span>
                    </a>
                </div>

                <div class="sidebar-item">
                    <a href="#" class="nav-link flex items-center px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 group" data-section="gallery">
                        <div class="w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center mr-3 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800">
                            <i class="fas fa-images text-yellow-600 dark:text-yellow-400 text-sm"></i>
                        </div>
                        <span class="font-medium">Gallery</span>
                        <span id="galleryCount" class="ml-auto bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400 px-2 py-1 rounded-full text-xs font-semibold">156</span>
                    </a>
                </div>

                <div class="sidebar-item">
                    <a href="#" class="nav-link flex items-center px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 group" data-section="leaders">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mr-3 group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800">
                            <i class="fas fa-user-tie text-indigo-600 dark:text-indigo-400 text-sm"></i>
                        </div>
                        <span class="font-medium">Leaders</span>
                        <span id="leadersCount" class="ml-auto bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 px-2 py-1 rounded-full text-xs font-semibold">8</span>
                    </a>
                </div>

                <div class="sidebar-item">
                    <a href="#" class="nav-link flex items-center px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 group" data-section="team">
                        <div class="w-8 h-8 rounded-lg bg-pink-100 dark:bg-pink-900 flex items-center justify-center mr-3 group-hover:bg-pink-200 dark:group-hover:bg-pink-800">
                            <i class="fas fa-users text-pink-600 dark:text-pink-400 text-sm"></i>
                        </div>
                        <span class="font-medium">Team</span>
                        <span id="teamCount" class="ml-auto bg-pink-100 dark:bg-pink-900 text-pink-600 dark:text-pink-400 px-2 py-1 rounded-full text-xs font-semibold">45</span>
                    </a>
                </div>

                <div class="sidebar-item">
                    <a href="#" class="nav-link flex items-center px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 group" data-section="users">
                        <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900 flex items-center justify-center mr-3 group-hover:bg-red-200 dark:group-hover:bg-red-800">
                            <i class="fas fa-users-cog text-red-600 dark:text-red-400 text-sm"></i>
                        </div>
                        <span class="font-medium">Users</span>
                        <span id="usersCount" class="ml-auto bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 px-2 py-1 rounded-full text-xs font-semibold">2,341</span>
                    </a>
                </div>

                <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="sidebar-item">
                        <a href="#" class="flex items-center px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900 group">
                            <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900 flex items-center justify-center mr-3 group-hover:bg-red-200 dark:group-hover:bg-red-800">
                                <i class="fas fa-sign-out-alt text-red-600 dark:text-red-400 text-sm"></i>
                            </div>
                            <span class="font-medium">Logout</span>
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </div>

    <!-- Mobile Sidebar Toggle -->
    <button id="sidebarToggle" class="lg:hidden fixed top-4 left-4 z-40 w-12 h-12 bg-white rounded-xl shadow-lg flex items-center justify-center">
        <i class="fas fa-bars text-gray-600"></i>
    </button>

    <!-- Main Content -->
    <div class="lg:ml-72 min-h-screen" id="main-content">
        <!-- Header -->
        <header class="bg-white/80 backdrop-blur-sm border-b border-gray-200 p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 id="pageTitle" class="text-3xl font-bold text-gray-800 mb-1">Dashboard Overview</h1>
                    <p id="pageSubtitle" class="text-gray-600">Welcome back, Admin. Here's what's happening today.</p>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Real-time clock and refresh -->
                    <div class="text-right">
                        <div class="flex items-center">
                            <div>
                                <div id="currentTime" class="text-lg font-semibold text-gray-800"></div>
                                <div id="currentDate" class="text-sm text-gray-500"></div>
                                <div id="lastUpdated" class="text-xs text-gray-400 mt-1">Loading...</div>
                            </div>
                            <button id="refreshDashboard" class="ml-3 p-2 text-gray-400 hover:text-blue-600 transition-colors duration-200" 
                                    title="Refresh Dashboard">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Notifications -->
                    <div class="relative">
                        <button id="notificationBtn" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center hover:bg-gray-200 relative">
                            <i class="fas fa-bell text-gray-600"></i>
                            <span id="notificationCount" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                        </button>
                        <!-- Notification Dropdown -->
                        <div id="notificationPanel" class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border hidden z-50">
                            <div class="p-4 border-b">
                                <h3 class="font-semibold text-gray-800">Recent Activities</h3>
                            </div>
                            <div id="notificationList" class="max-h-80 overflow-y-auto">
                                <!-- Notifications will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div id="dashboardContent" class="content-section active p-6">
            <!-- Welcome Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome back, <span class="text-blue-600">Admin</span> 👋</h1>
                <p class="text-gray-600">Here's what's happening with your ministry today</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
                <!-- Users Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Users</p>
                                <p id="totalUsers" class="mt-1 text-3xl font-semibold text-gray-900">2,341</p>
                            </div>
                            <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center">
                            <span class="text-green-500 text-sm font-medium flex items-center">
                                <i class="fas fa-arrow-up mr-1 text-xs"></i> 12.5%
                            </span>
                            <span class="text-gray-500 text-sm ml-2">vs last month</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 text-sm text-gray-500">
                            <a href="#" onclick="showSection('users'); return false;" class="text-blue-600 hover:text-blue-800 font-medium">View all users</a>
                    </div>
                </div>

                <!-- Events Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Active Events</p>
                                <p id="activeEvents" class="mt-1 text-3xl font-semibold text-gray-900">8</p>
                            </div>
                            <div class="p-3 rounded-lg bg-green-50 text-green-600">
                                <i class="fas fa-calendar-alt text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-1">
                                <span>Upcoming</span>
                                <span id="upcomingEventsCount" class="font-medium">Loading...</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 text-sm text-gray-500">
                            <a href="#" onclick="showSection('events'); return false;" class="text-blue-600 hover:text-blue-800 font-medium">View calendar</a>
                    </div>
                </div>

                <!-- Blog Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Blog Posts</p>
                                <p id="totalBlogs" class="mt-1 text-3xl font-semibold text-gray-900">47</p>
                            </div>
                            <div class="p-3 rounded-lg bg-purple-50 text-purple-600">
                                <i class="fas fa-blog text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center space-x-2 text-sm">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">24 Published</span>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">3 Drafts</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 text-sm text-gray-500">
                            <a href="#" onclick="showSection('blog'); return false;" class="text-blue-600 hover:text-blue-800 font-medium">View all posts</a>
                    </div>
                </div>

                <!-- Gallery Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Gallery</p>
                                <p id="totalGallery" class="mt-1 text-3xl font-semibold text-gray-900">342</p>
                            </div>
                            <div class="p-3 rounded-lg bg-amber-50 text-amber-600">
                                <i class="fas fa-images text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="grid grid-cols-4 gap-1">
                                <div class="aspect-square bg-gray-200 rounded-md overflow-hidden">
                                    <img src="https://source.unsplash.com/random/200x200?church" class="w-full h-full object-cover">
                                </div>
                                <div class="aspect-square bg-gray-200 rounded-md overflow-hidden">
                                    <img src="https://source.unsplash.com/random/200x201?worship" class="w-full h-full object-cover">
                                </div>
                                <div class="aspect-square bg-gray-200 rounded-md overflow-hidden">
                                    <img src="https://source.unsplash.com/random/200x202?bible" class="w-full h-full object-cover">
                                </div>
                                <div class="aspect-square bg-gray-100 rounded-md flex items-center justify-center text-gray-400">
                                    <span class="text-xs">+12 more</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 text-sm text-gray-500">
                            <a href="#" onclick="showSection('gallery'); return false;" class="text-blue-600 hover:text-blue-800 font-medium">View gallery</a>
                    </div>
                </div>

                <!-- Ministries Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Ministries</p>
                                <p id="totalMinistries" class="mt-1 text-3xl font-semibold text-gray-900">0</p>
                            </div>
                            <div class="p-3 rounded-lg bg-indigo-50 text-indigo-600">
                                <i class="fas fa-hands-helping text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center">
                            <span class="text-green-500 text-sm font-medium flex items-center">
                                <i class="fas fa-users text-xs mr-1"></i> Active
                            </span>
                            <span class="text-gray-500 text-sm ml-2">ministries</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 text-sm text-gray-500">
                        <a href="#" onclick="showSection('ministries'); return false;" class="text-blue-600 hover:text-blue-800 font-medium">View all</a>
                    </div>
                </div>

                <!-- Sermons Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Sermons</p>
                                <p id="totalSermons" class="mt-1 text-3xl font-semibold text-gray-900">0</p>
                            </div>
                            <div class="p-3 rounded-lg bg-purple-50 text-purple-600">
                                <i class="fas fa-bible text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center space-x-2 text-sm">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">Audio</span>
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">Video</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 text-sm text-gray-500">
                        <a href="#" onclick="showSection('sermons'); return false;" class="text-blue-600 hover:text-blue-800 font-medium">Browse all</a>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Charts -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- User Growth Chart -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">User Growth</h3>
                                <p class="text-sm text-gray-500">New members joined this month</p>
                            </div>
                            <div class="mt-3 sm:mt-0">
                                <select id="growthTimeframe" class="text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option>Last 7 days</option>
                                    <option>Last 30 days</option>
                                    <option>Last 3 months</option>
                                </select>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas id="userGrowthChart"></canvas>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
                                <button onclick="showSection('events'); return false;" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</button>
                            </div>
                        </div>
                        <div id="recentActivities" class="divide-y divide-gray-100">
                            <!-- Activities will be populated here -->
                        </div>
                    </div>
                </div>

                <!-- Right Column - Quick Actions -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button onclick="showSection('blog')" class="w-full flex items-center justify-between px-4 py-3 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-colors duration-200">
                                <span class="font-medium">New Blog Post</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                            <button onclick="showSection('events')" class="w-full flex items-center justify-between px-4 py-3 bg-green-50 hover:bg-green-100 text-green-700 rounded-lg transition-colors duration-200">
                                <span class="font-medium">Create Event</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                            <button onclick="showSection('sermons')" class="w-full flex items-center justify-between px-4 py-3 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg transition-colors duration-200">
                                <span class="font-medium">Upload Sermon</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                            <button onclick="showSection('gallery')" class="w-full flex items-center justify-between px-4 py-3 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg transition-colors duration-200">
                                <span class="font-medium">Add to Gallery</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900">Upcoming Events</h3>
                            <button onclick="updateUpcomingEvents()" class="text-gray-400 hover:text-blue-600 transition-colors duration-200" 
                                    aria-label="Refresh events" data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh events">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div id="upcomingEventsContainer" class="divide-y divide-gray-100">
                            <!-- Events will be loaded here dynamically -->
                            <div class="p-6 text-center">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
                                <p class="mt-2 text-sm text-gray-500">Loading events...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blog Section -->
        <div id="blogContent" class="content-section p-6">
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Blog Management</h2>
                        <p class="text-gray-600">Create and manage your ministry blog posts</p>
                    </div>
                    <button onclick="openCreateBlogModal()" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-plus mr-2"></i>New Post
                    </button>
                </div>

                <!-- Blog Posts Table -->
                <div class="overflow-x-auto">
                    <table class="data-table w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left font-semibold text-gray-700">Title</th>
                                <th class="text-left font-semibold text-gray-700">Author</th>
                                <th class="text-left font-semibold text-gray-700">Status</th>
                                <th class="text-left font-semibold text-gray-700">Date</th>
                                <th class="text-left font-semibold text-gray-700">Views</th>
                                <th class="text-left font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="blogPostsTable">
                            <!-- Blog posts will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Events Section -->
        <div id="eventsContent" class="content-section p-6">
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Events Management</h2>
                        <p class="text-gray-600">Schedule and manage ministry events</p>
                    </div>
                    <button onclick="openCreateEventModal()" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-plus mr-2"></i>New Event
                    </button>
                </div>

                <!-- Events Grid -->
                <div id="eventsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Events will be populated here -->
                </div>
            </div>
        </div>

        <!-- Sermons Section -->
        <div id="sermonsContent" class="content-section p-6">
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Sermons Management</h2>
                        <p class="text-gray-600">Upload and manage sermon recordings and transcripts</p>
                    </div>
                    <button onclick="openCreateSermonModal()" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-plus mr-2"></i>New Sermon
                    </button>
                </div>

                <!-- Sermons Grid -->
                <div id="sermonsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Sermons will be populated here -->
                </div>
            </div>
        </div>

        <!-- Gallery Section -->
        <div id="galleryContent" class="content-section p-6">
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Gallery Management</h2>
                        <p class="text-gray-600">Organize and manage ministry photos and media</p>
                    </div>
                    <button onclick="openUploadModal()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-upload mr-2"></i>Upload Media
                    </button>
                </div>

                <!-- Gallery Grid -->
                <div id="galleryGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <!-- Gallery items will be populated here -->
                </div>
            </div>
        </div>

        <!-- Leaders Section -->
        <div id="leadersContent" class="content-section p-6">
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Leaders Management</h2>
                        <p class="text-gray-600">Manage ministry leadership profiles</p>
                    </div>
                    <button onclick="openAddLeaderModal()" class="bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-plus mr-2"></i>Add Leader
                    </button>
                </div>

                <!-- Leaders Grid -->
                <div id="leadersGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Leaders will be populated here -->
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div id="teamContent" class="content-section p-6">
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Team Management</h2>
                        <p class="text-gray-600">Manage team members and their roles</p>
                    </div>
                    <button onclick="openAddTeamMemberModal()" class="bg-pink-500 hover:bg-pink-600 text-white px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-plus mr-2"></i>Add Member
                    </button>
                </div>

                <!-- Team Table -->
                <div class="overflow-x-auto">
                    <table class="data-table w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left font-semibold text-gray-700">Member</th>
                                <th class="text-left font-semibold text-gray-700">Role</th>
                                <th class="text-left font-semibold text-gray-700">Department</th>
                                <th class="text-left font-semibold text-gray-700">Status</th>
                                <th class="text-left font-semibold text-gray-700">Joined</th>
                                <th class="text-left font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="teamTable">
                            <!-- Team members will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Users Section -->
        <div id="usersContent" class="content-section p-6">
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Users Management</h2>
                        <p class="text-gray-600">Manage website users and permissions</p>
                    </div>
                    <button onclick="openAddUserModal()" class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-plus mr-2"></i>Add User
                    </button>
                </div>

                <!-- Users Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-users text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Users</p>
                                <p class="text-xl font-bold text-gray-800">2,341</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-user-check text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Active Users</p>
                                <p class="text-xl font-bold text-gray-800">2,198</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-user-clock text-yellow-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Pending</p>
                                <p class="text-xl font-bold text-gray-800">87</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-user-times text-red-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Inactive</p>
                                <p class="text-xl font-bold text-gray-800">56</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="overflow-x-auto">
                    <table class="data-table w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left font-semibold text-gray-700">User</th>
                                <th class="text-left font-semibold text-gray-700">Email</th>
                                <th class="text-left font-semibold text-gray-700">Role</th>
                                <th class="text-left font-semibold text-gray-700">Status</th>
                                <th class="text-left font-semibold text-gray-700">Last Login</th>
                                <th class="text-left font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTable">
                            <!-- Users will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALS -->

    <!-- Create Blog Modal -->
    <div id="createBlogModal" class="modal hide fixed inset-0 z-50 overflow-y-auto">
        <div class="modal-overlay flex items-center justify-center min-h-screen px-4">
            <div class="modal-content glass-morphism max-w-4xl w-full rounded-2xl shadow-2xl">
                <div class="gradient-primary text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-plus text-2xl"></i>
                            <h2 class="text-2xl font-bold">Create New Blog Post</h2>
                        </div>
                        <button onclick="closeModal('createBlogModal')" class="text-white hover:text-gray-200 text-xl">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="p-6">
                    <form id="createBlogForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                                <input type="text" name="title" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter blog post title">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select name="category" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Select Category</option>
                                    <option value="spirituality">Spirituality</option>
                                    <option value="faith">Faith</option>
                                    <option value="community">Community</option>
                                    <option value="events">Events</option>
                                    <option value="testimonies">Testimonies</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                            <textarea name="content" rows="8" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Write your blog post content here..."></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Featured Image</label>
                                <input type="file" name="image" accept="image/*" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="scheduled">Scheduled</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                            <button type="button" onclick="closeModal('createBlogModal')" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200">
                                Cancel
                            </button>
                            <button type="submit" class="btn-primary px-6 py-3 text-white rounded-lg font-semibold">
                                Create Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Event Modal -->
    <div id="createEventModal" class="modal hide fixed inset-0 z-50 overflow-y-auto">
        <div class="modal-overlay flex items-center justify-center min-h-screen px-4">
            <div class="modal-content glass-morphism max-w-4xl w-full rounded-2xl shadow-2xl">
                <div class="bg-green-500 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-calendar-plus text-2xl"></i>
                            <h2 class="text-2xl font-bold">Create New Event</h2>
                        </div>
                        <button onclick="closeModal('createEventModal')" class="text-white hover:text-gray-200 text-xl">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="p-6">
                    <form id="createEventForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Event Title</label>
                                <input type="text" name="title" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Enter event title">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Event Type</label>
                                <select name="type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">Select Type</option>
                                    <option value="worship">Worship Service</option>
                                    <option value="bible_study">Bible Study</option>
                                    <option value="youth">Youth Meeting</option>
                                    <option value="prayer">Prayer Meeting</option>
                                    <option value="community">Community Event</option>
                                    <option value="conference">Conference</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="4" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Event description..."></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                <input type="date" name="date" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                                <input type="time" name="start_time" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                                <input type="time" name="end_time" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                <input type="text" name="location" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Event location">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Max Attendees</label>
                                <input type="number" name="max_attendees" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="0 for unlimited">
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                            <button type="button" onclick="closeModal('createEventModal')" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200">
                                Cancel
                            </button>
                            <button type="submit" class="bg-green-500 hover:bg-green-600 px-6 py-3 text-white rounded-lg font-semibold">
                                Create Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Application State
        const appState = {
            currentSection: 'dashboard',
            data: {
                blogPosts: [
                    { id: 1, title: 'Walking in Faith During Difficult Times', author: 'Pastor John', status: 'published', date: '2025-01-15', views: 342 },
                    { id: 2, title: 'The Power of Prayer in Our Daily Lives', author: 'Elder Mary', status: 'draft', date: '2025-01-14', views: 0 },
                    { id: 3, title: 'Community Service: Loving Our Neighbors', author: 'Pastor John', status: 'published', date: '2025-01-12', views: 187 }
                ],
                events: [
                    { id: 1, title: 'Sunday Worship Service', type: 'worship', date: '2025-01-19', time: '10:00 AM', location: 'Main Sanctuary', attendees: 245 },
                    { id: 2, title: 'Youth Bible Study', type: 'youth', date: '2025-01-20', time: '6:00 PM', location: 'Youth Hall', attendees: 32 },
                    { id: 3, title: 'Prayer Meeting', type: 'prayer', date: '2025-01-21', time: '7:00 PM', location: 'Prayer Room', attendees: 45 }
                ],
                sermons: [
                    { id: 1, title: 'Faith That Moves Mountains', speaker: 'Pastor John', date: '2025-01-12', duration: '45 min', downloads: 123 },
                    { id: 2, title: 'Love Your Enemies', speaker: 'Elder Mary', date: '2025-01-05', duration: '38 min', downloads: 89 }
                ],
                leaders: [
                    { id: 1, name: 'Pastor John Smith', role: 'Senior Pastor', image: 'PJ', bio: 'Leading our community for over 15 years' },
                    { id: 2, name: 'Elder Mary Johnson', role: 'Elder', image: 'MJ', bio: 'Dedicated to youth ministry and community outreach' },
                    { id: 3, name: 'Deacon David Brown', role: 'Deacon', image: 'DB', bio: 'Financial stewardship and church operations' }
                ],
                team: [
                    { id: 1, name: 'Sarah Miller', email: 'sarah@heartsaftergod.com', role: 'Youth Leader', department: 'Youth Ministry', status: 'active', joined: '2023-03-15' },
                    { id: 2, name: 'Michael Davis', email: 'michael@heartsaftergod.com', role: 'Music Director', department: 'Worship', status: 'active', joined: '2022-08-20' },
                    { id: 3, name: 'Jennifer Wilson', email: 'jennifer@heartsaftergod.com', role: 'Children\'s Coordinator', department: 'Children\'s Ministry', status: 'active', joined: '2024-01-10' }
                ],
                users: [
                    { id: 1, name: 'John Doe', email: 'john@example.com', role: 'Member', status: 'active', lastLogin: '2025-01-18' },
                    { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'Volunteer', status: 'active', lastLogin: '2025-01-17' },
                    { id: 3, name: 'Bob Johnson', email: 'bob@example.com', role: 'Member', status: 'pending', lastLogin: 'Never' }
                ],
                activities: [
                    { type: 'blog', action: 'New blog post published', details: 'Walking in Faith During Difficult Times', time: '2 hours ago', icon: 'fas fa-blog', color: 'blue' },
                    { type: 'event', action: 'Event created', details: 'Youth Bible Study scheduled', time: '4 hours ago', icon: 'fas fa-calendar', color: 'green' },
                    { type: 'user', action: 'New user registered', details: 'John Doe joined the community', time: '6 hours ago', icon: 'fas fa-user-plus', color: 'purple' },
                    { type: 'gallery', action: 'Images uploaded', details: '12 photos from Sunday service', time: '1 day ago', icon: 'fas fa-images', color: 'yellow' }
                ]
            },
            notifications: [
                { id: 1, title: 'New User Registration', message: 'John Doe has registered', time: '5 minutes ago', read: false },
                { id: 2, title: 'Event Reminder', message: 'Youth meeting tomorrow at 6 PM', time: '1 hour ago', read: false },
                { id: 3, title: 'Blog Post Approved', message: 'Your post has been published', time: '2 hours ago', read: true }
            ]
        };

        // Real-time clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            const dateString = now.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            document.getElementById('currentTime').textContent = timeString;
            document.getElementById('currentDate').textContent = dateString;
        }

        // Fetch events from the server
        // Fetch dashboard statistics from the server and update the UI
        async function fetchStats() {
            try {
                const response = await fetch('/hearts-after-god-ministry-site/backend/api/dashboard/stats.php');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.status !== 'success') {
                    throw new Error(data.message || 'Failed to fetch statistics');
                }
                
                // Update the UI with the real data
                if (data.data) {
                    // Update the stats cards
                    updateStatCard('totalUsers', data.data.totalUsers || 0);
                    updateStatCard('activeEvents', data.data.activeEvents || 0);
                    updateStatCard('totalBlogs', data.data.totalBlogs || 0);
                    updateStatCard('totalGallery', data.data.totalGallery || 0);
                    updateStatCard('totalMinistries', data.data.totalMinistries || 0);
                    updateStatCard('totalSermons', data.data.totalSermons || 0);
                    updateStatCard('upcomingEvents', data.data.upcomingEvents || 0);
                    
                    // Update the last updated time
                    const lastUpdated = new Date().toLocaleTimeString();
                    const lastUpdatedElement = document.getElementById('lastUpdated');
                    if (lastUpdatedElement) {
                        lastUpdatedElement.textContent = `Last updated: ${lastUpdated}`;
                    }
                }
                
                return data.data || {};
                
            } catch (error) {
                console.error('Error fetching statistics:', error);
                showNotification(`Error: ${error.message}`, 'error');
                return {};
            }
        }
        
        // Helper function to update a stat card with animation
        function updateStatCard(elementId, value) {
            const element = document.getElementById(elementId);
            if (element) {
                // Animate the counter if the value has changed
                if (element.textContent !== value.toString()) {
                    animateCounter(elementId, value);
                }
            }
        }

        // Fetch upcoming events from the server
        async function fetchEvents() {
            try {
                const response = await fetch('/hearts-after-god-ministry-site/backend/api/events/upcoming.php');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.status !== 'success') {
                    throw new Error(data.message || 'Failed to fetch events');
                }
                
                // Transform the data to match the expected format
                return (data.data || []).map(event => ({
                    id: event.id,
                    title: event.title,
                    description: event.description || '',
                    start_datetime: event.start_datetime,
                    end_datetime: event.end_datetime || event.start_datetime,
                    location: event.location || 'TBA',
                    status: event.status || 'scheduled',
                    is_recurring: event.is_recurring || false,
                    featured_image: event.featured_image || '',
                    registration_deadline: event.registration_deadline || null,
                    max_attendees: event.max_attendees || 0,
                    registered_attendees: event.registered_attendees || 0,
                    available_spots: event.max_attendees > 0 
                        ? Math.max(0, event.max_attendees - (event.registered_attendees || 0))
                        : null
                }));
            } catch (error) {
                console.error('Error in fetchEvents:', error);
                showNotification('Failed to load upcoming events. ' + (error.message || 'Please try again later.'), 'error');
                return [];
            }
        }

        // Update upcoming events display
        async function updateUpcomingEvents() {
            const eventsContainer = document.querySelector('#upcomingEventsContainer');
            if (!eventsContainer) return;

            // Show loading state
            eventsContainer.innerHTML = `
                <div class="flex items-center justify-center p-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    <span class="ml-3 text-gray-600">Loading events...</span>
                </div>
            `;

            try {
                // Fetch events from the API
                const events = await fetchEvents();
                
                // Update the events list with real data
                renderEvents(events);
                
            } catch (error) {
                console.error('Error loading events:', error);
                eventsContainer.innerHTML = `
                    <div class="p-4 text-red-600 bg-red-50 rounded-lg">
                        <p>Failed to load events. ${error.message || 'Please try again later.'}</p>
                        <button onclick="updateUpcomingEvents()" class="mt-2 text-sm text-blue-600 hover:underline">
                            <i class="fas fa-sync-alt mr-1"></i> Retry
                        </button>
                    </div>
                `;
            }
        }

        // Render events in the UI
        function renderEvents(events) {
            const eventsContainer = document.querySelector('#upcomingEventsContainer');
            if (!eventsContainer) return;

            if (!events || events.length === 0) {
                eventsContainer.innerHTML = `
                    <div class="p-4 text-center text-gray-500">
                        <p>No upcoming events scheduled.</p>
                    </div>
                `;
                return;
            }

            // Sort events by start_datetime
            const sortedEvents = [...events].sort((a, b) => {
                return new Date(a.start_datetime) - new Date(b.start_datetime);
            });

            // Render events list
            eventsContainer.innerHTML = `
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    ${sortedEvents.map(event => {
                        const startDate = new Date(event.start_datetime);
                        const endDate = new Date(event.end_datetime);
                        const timeString = startDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        const dateString = startDate.toLocaleDateString([], { month: 'short', day: 'numeric' });
                        
                        return `
                        <li class="py-3 hover:bg-gray-50 dark:hover:bg-gray-800 px-2 rounded-lg transition-colors">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-lg p-2 mr-3">
                                    <i class="fas ${event.is_recurring ? 'fa-sync-alt' : 'fa-calendar-day'} text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        ${event.title}
                                        ${event.status === 'cancelled' ? 
                                            '<span class="ml-2 px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800 rounded-full">Cancelled</span>' : ''}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        ${dateString} • ${timeString} • ${event.location || 'TBA'}
                                    </p>
                                    ${event.description ? 
                                        `<p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                            ${event.description}
                                        </p>` : ''
                                    }
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <a href="/admin/events/edit.php?id=${event.id}" 
                                       class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                                        View
                                    </a>
                                </div>
                            </div>
                        </li>`;
                    }).join('')}
                </ul>
                <div class="mt-4 text-center">
                    <a href="/admin/events/" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                        View all events <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            `;
        }

        // Helper function to get event type styling
        function getEventTypeClass(type) {
            const types = {
                'worship': {
                    bg: 'bg-green-50',
                    text: 'text-green-600',
                    badge: 'bg-green-100 text-green-800',
                    icon: 'fas fa-church'
                },
                'youth': {
                    bg: 'bg-blue-50',
                    text: 'text-blue-600',
                    badge: 'bg-blue-100 text-blue-800',
                    icon: 'fas fa-users'
                },
                'prayer': {
                    bg: 'bg-purple-50',
                    text: 'text-purple-600',
                    badge: 'bg-purple-100 text-purple-800',
                    icon: 'fas fa-hands-praying'
                },
                'default': {
                    bg: 'bg-gray-50',
                    text: 'text-gray-600',
                    badge: 'bg-gray-100 text-gray-800',
                    icon: 'fas fa-calendar-day'
                }
            };
            return types[type] || types['default'];
        }

        // Format event date and time
        function formatEventDate(dateString, timeString) {
            const eventDate = new Date(`${dateString}T${timeString}`);
            const now = new Date();
            
            // If event is today
            if (eventDate.toDateString() === now.toDateString()) {
                return `Today, ${formatTime(timeString)}`;
            }
            
            // If event is tomorrow
            const tomorrow = new Date(now);
            tomorrow.setDate(tomorrow.getDate() + 1);
            if (eventDate.toDateString() === tomorrow.toDateString()) {
                return `Tomorrow, ${formatTime(timeString)}`;
            }
            
            // Otherwise, return day and time
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const day = days[eventDate.getDay()];
            return `${day}, ${formatTime(timeString)}`;
        }

        // Format time to 12-hour format
        function formatTime(timeString) {
            const [hours, minutes] = timeString.split(':');
            const hour = parseInt(hours, 10);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${minutes} ${ampm}`;
        }

        // Initialize the application when DOM is loaded
        // Function to initialize the dashboard
        async function initializeDashboard() {
            // Initialize tooltips
            initTooltips();
            
            // Initialize modals
            
            // Load initial data
            await fetchStats();
            
            // Set up periodic refresh (every 30 seconds)
            setInterval(fetchStats, 30000);
            
            // Set up manual refresh button
            const refreshBtn = document.getElementById('refreshStatsBtn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', async () => {
                    refreshBtn.innerHTML = '<i class="fas fa-sync-alt animate-spin"></i>';
                    await fetchStats();
                    refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i>';
                    
                    // Show a brief confirmation
                    const originalText = refreshBtn.querySelector('.sr-only').textContent;
                    refreshBtn.querySelector('.sr-only').textContent = 'Refreshed!';
                    setTimeout(() => {
                        refreshBtn.querySelector('.sr-only').textContent = originalText;
                    }, 2000);
                });
            }
        }

        // Initialize the dashboard when the DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the dashboard
            initializeDashboard();
            
            // Initialize other components
            if (typeof initModals === 'function') initModals();
            
            // Initialize sidebar toggle
            if (typeof initSidebarToggle === 'function') initSidebarToggle();
            
            // Initialize tabs
            if (typeof initTabs === 'function') initTabs();
            
            // Initialize charts
            if (typeof initCharts === 'function') initCharts();
            
            // Initialize datepickers
            if (typeof initDatepickers === 'function') initDatepickers();
            
            // Initialize select2
            if (typeof initSelect2 === 'function') initSelect2();
            
            // Initialize file uploads
            if (typeof initFileUploads === 'function') initFileUploads();
            
            // Initialize notifications
            if (typeof initNotifications === 'function') initNotifications();
            
            // Set current year in footer if the element exists
            const currentYearElement = document.getElementById('current-year');
            if (currentYearElement) {
                currentYearElement.textContent = new Date().getFullYear();
            }
            
            // Initialize charts
            if (typeof initializeCharts === 'function') initializeCharts();
            
            // Show dashboard content by default
            const dashboardContent = document.getElementById('dashboardContent');
            if (dashboardContent) dashboardContent.classList.add('active');
            
            // Set up event listeners
            if (typeof setupEventListeners === 'function') setupEventListeners();
            
            // Initial stats load
            if (typeof updateStats === 'function') updateStats();
            
            // Update stats every 30 seconds
            if (typeof updateStats === 'function') setInterval(updateStats, 30000);
            
            // Load blog posts if on blog section
            if (window.location.hash === '#blog' && typeof fetchBlogPosts === 'function') {
                fetchBlogPosts();
            }
            
            // Manual refresh button event listener
            const refreshButton = document.getElementById('refreshDashboard');
            if (refreshButton) {
                refreshButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (typeof updateStats === 'function') updateStats();
                    if (typeof updateUpcomingEvents === 'function') updateUpcomingEvents();
                    
                    // Add visual feedback
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.add('animate-spin');
                        setTimeout(() => {
                            icon.classList.remove('animate-spin');
                        }, 1000);
                    }
                });
            }
            
            // Set up navigation event listeners
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const section = this.getAttribute('data-section');
                    if (section && typeof showSection === 'function') {
                        // Update URL without page reload
                        window.history.pushState(null, '', `#${section}`);
                        showSection(section);
                    }
                });
            });
            
            // Handle browser back/forward buttons - using a single handler
            window.addEventListener('popstate', function() {
                const section = window.location.hash ? window.location.hash.substring(1) : 'dashboard';
                if (typeof showSection === 'function') showSection(section);
            });
            
            // Initialize real-time stats and set up refresh button
            if (typeof initStats === 'function') initStats();
            const refreshBtn = document.getElementById('refreshStatsBtn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (typeof updateStats === 'function') updateStats();
                    if (typeof showNotification === 'function') showNotification('Refreshing dashboard...', 'info');
                });
            }
        });

        // Store the current stats for comparison
        let currentStats = {
            totalUsers: 0,
            activeEvents: 0,
            totalBlogs: 0,
            totalGallery: 0,
            totalMinistries: 0,
            totalSermons: 0,
            upcomingEvents: 0
        };

        // Initialize SSE connection
        let eventSource = null;
        let isConnected = false;
        let reconnectAttempts = 0;
        const MAX_RECONNECT_ATTEMPTS = 5;
        const RECONNECT_DELAY = 5000; // 5 seconds

        // Function to update stats from SSE data
        function updateStatsFromSSE(stats) {
            const statElements = getStatElements();
            
            // Check if any stat has changed
            let hasChanges = false;
            const changedStats = [];
            
            Object.keys(currentStats).forEach(key => {
                if (currentStats[key] !== stats[key]) {
                    hasChanges = true;
                    changedStats.push(key);
                    currentStats[key] = stats[key];
                }
            });
            
            // Only update the UI if there are changes
            if (hasChanges) {
                console.log('Updating stats. Changes detected in:', changedStats);
                
                // Update all stat elements with actual data
                updateCounter('totalUsers', stats.totalUsers);
                updateCounter('activeEvents', stats.activeEvents);
                updateCounter('totalBlogs', stats.totalBlogs);
                updateCounter('totalGallery', stats.totalGallery);
                updateCounter('totalMinistries', stats.totalMinistries);
                updateCounter('totalSermons', stats.totalSermons);
                
                // Handle upcoming events with proper pluralization
                if (statElements.upcomingEvents) {
                    const upcomingCount = stats.upcomingEvents || 0;
                    const eventText = upcomingCount === 1 ? 'event' : 'events';
                    statElements.upcomingEvents.textContent = `${upcomingCount} ${eventText}`;
                }
                
                // Update last updated time
                const lastUpdated = document.getElementById('lastUpdated');
                if (lastUpdated) {
                    lastUpdated.textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
                }
            }
        }

        // Function to get all stat elements
        function getStatElements() {
            return {
                'totalUsers': document.getElementById('totalUsers'),
                'activeEvents': document.getElementById('activeEvents'),
                'totalBlogs': document.getElementById('totalBlogs'),
                'totalGallery': document.getElementById('totalGallery'),
                'totalMinistries': document.getElementById('totalMinistries'),
                'totalSermons': document.getElementById('totalSermons'),
                'upcomingEvents': document.getElementById('upcomingEventsCount')
            };
        }

        // Function to safely update a counter
        function updateCounter(elementId, value, prefix = '', suffix = '') {
            const element = document.getElementById(elementId);
            if (element) {
                const numValue = parseInt(value) || 0;
                element.textContent = `${prefix}${numValue.toLocaleString()}${suffix}`;
                animateCounter(elementId, numValue);
            }
        }

        // Function to initialize SSE connection
        function initSSEConnection() {
            if (eventSource) {
                eventSource.close();
            }

            const sseUrl = '/hearts-after-god-ministry-site/backend/api/dashboard/sse.php';
            
            try {
                eventSource = new EventSource(sseUrl);
                
                eventSource.onopen = () => {
                    console.log('SSE connection established');
                    isConnected = true;
                    reconnectAttempts = 0;
                    showNotification('Connected to real-time updates', 'success');
                };

                eventSource.onmessage = (event) => {
                    // Handle generic messages
                    console.log('Message from server:', event.data);
                };

                eventSource.addEventListener('stats_update', (event) => {
                    try {
                        const data = JSON.parse(event.data);
                        updateStatsFromSSE(data.data);
                    } catch (e) {
                        console.error('Error parsing stats update:', e);
                    }
                });

                eventSource.addEventListener('error', (error) => {
                    console.error('SSE Error:', error);
                    isConnected = false;
                    
                    // Try to reconnect
                    if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
                        reconnectAttempts++;
                        const delay = RECONNECT_DELAY * reconnectAttempts;
                        console.log(`Reconnecting in ${delay/1000} seconds... (Attempt ${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS})`);
                        
                        setTimeout(() => {
                            initSSEConnection();
                        }, delay);
                    } else {
                        console.error('Max reconnection attempts reached');
                        showNotification('Disconnected from real-time updates', 'error');
                        // Fall back to polling
                        startPolling();
                    }
                });
                
            } catch (e) {
                console.error('Error initializing SSE:', e);
                // Fall back to polling if SSE is not supported
                startPolling();
            }
        }

        // Fallback polling function
        let pollingInterval = null;
        function startPolling(interval = 10000) { // 10 seconds
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
            
            // Initial fetch
            fetchStats();
            
            // Set up interval
            pollingInterval = setInterval(fetchStats, interval);
            
            console.log(`Started polling for updates every ${interval/1000} seconds`);
        }

        // Function to fetch stats via AJAX
        async function fetchStats() {
            try {
                const response = await fetch('/hearts-after-god-ministry-site/backend/api/dashboard/stats.php', {
                    headers: {
                        'Cache-Control': 'no-cache, no-store, must-revalidate',
                        'Pragma': 'no-cache',
                        'Expires': '0'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    updateStatsFromSSE(result.data);
                } else {
                    throw new Error(result.message || 'Failed to fetch statistics');
                }
                
            } catch (error) {
                console.error('Error fetching stats:', error);
            }
        }

        // Initialize the stats system
        function initStats() {
            // Try SSE first
            if (typeof EventSource !== 'undefined') {
                initSSEConnection();
            } else {
                // Fall back to polling if SSE is not supported
                startPolling();
            }
            
            // Initial fetch
            fetchStats();
        }

        // Update dashboard statistics (legacy function, now uses SSE)
        function updateStats() {
            if (isConnected) {
                // If SSE is connected, the stats will update automatically
                console.log('Using real-time updates via SSE');
            } else {
                // Fall back to manual fetch
                fetchStats();
            }
        }
        
        // Animate counter from current value to target value
        function animateCounter(elementId, targetValue) {
            const element = document.getElementById(elementId);
            if (!element) return;
            
            // Get current value, default to 0 if not a number
            const currentValue = parseInt(element.textContent.replace(/[^0-9]/g, '')) || 0;
            const difference = targetValue - currentValue;
            
            // Don't animate if the value hasn't changed
            if (difference === 0) return;
            
            const duration = 1000; // Animation duration in ms
            const startTime = performance.now();
            
            function updateCounter(timestamp) {
                const elapsedTime = timestamp - startTime;
                const progress = Math.min(elapsedTime / duration, 1);
                
                // Ease out function for smoother animation
                const easeOut = (t) => 1 - Math.pow(1 - t, 3);
                const easedProgress = easeOut(progress);
                
                // Calculate new value
                const newValue = Math.round(currentValue + (difference * easedProgress));
                element.textContent = newValue.toLocaleString();
                
                // Continue animation if not complete
                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    // Ensure final value is exact
                    element.textContent = targetValue.toLocaleString();
                }
            }
            
            // Start the animation
            requestAnimationFrame(updateCounter);
        }

        // Navigation
        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
                section.style.display = 'none';
            });
            
            // Show selected section
            const section = document.getElementById(sectionName + 'Content');
            if (section) {
                section.classList.add('active');
                section.style.display = 'block';
            }
            
            // Update navigation
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('bg-gradient-to-r', 'from-indigo-500', 'to-purple-600', 'text-white', 'shadow-lg');
                link.classList.add('text-gray-700', 'hover:bg-gray-100');
            });
            
            const activeLink = document.querySelector(`[data-section="${sectionName}"]`);
            if (activeLink) {
                activeLink.classList.remove('text-gray-700', 'hover:bg-gray-100');
                activeLink.classList.add('bg-gradient-to-r', 'from-indigo-500', 'to-purple-600', 'text-white', 'shadow-lg');
            }
            
            // Update page title and subtitle
            const titleElement = document.getElementById('pageTitle');
            const subtitleElement = document.getElementById('pageSubtitle');
            
            const titles = {
                dashboard: { 
                    title: 'Dashboard Overview', 
                    subtitle: 'Welcome back, Admin. Here\'s what\'s happening today.' 
                },
                blog: { 
                    title: 'Blog Management', 
                    subtitle: 'Create and manage your ministry blog posts' 
                },
                events: { 
                    title: 'Events Management', 
                    subtitle: 'Schedule and manage ministry events' 
                },
                sermons: { 
                    title: 'Sermons Management', 
                    subtitle: 'Upload and manage sermon recordings' 
                },
                gallery: { 
                    title: 'Gallery Management', 
                    subtitle: 'Organize and manage ministry photos' 
                },
                leaders: { 
                    title: 'Leaders Management', 
                    subtitle: 'Manage ministry leadership profiles' 
                },
                team: { 
                    title: 'Team Management', 
                    subtitle: 'Manage team members and their roles' 
                },
                users: { 
                    title: 'Users Management', 
                    subtitle: 'Manage website users and permissions' 
                }
            };
            
            if (titleElement && subtitleElement && titles[sectionName]) {
                titleElement.textContent = titles[sectionName].title;
                subtitleElement.textContent = titles[sectionName].subtitle;
            }
            
            appState.currentSection = sectionName;
            loadSectionData(sectionName);
        }

        // Load section-specific data
        function loadSectionData(sectionName) {
            switch (sectionName) {
                case 'dashboard':
                    loadDashboardData();
                    break;
                case 'blog':
                    loadBlogData();
                    break;
                case 'events':
                    loadEventsData();
                    break;
                case 'sermons':
                    loadSermonsData();
                    break;
                case 'gallery':
                    loadGalleryData();
                    break;
                case 'leaders':
                    loadLeadersData();
                    break;
                case 'team':
                    loadTeamData();
                    break;
                case 'users':
                    loadUsersData();
                    break;
            }
        }

        // Load dashboard data
        function loadDashboardData() {
            // Load recent activities
            const activitiesContainer = document.getElementById('recentActivities');
            if (activitiesContainer) {
                activitiesContainer.innerHTML = appState.data.activities.map(activity => `
                    <div class="activity-item flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="w-10 h-10 bg-${activity.color}-100 rounded-lg flex items-center justify-center">
                            <i class="${activity.icon} text-${activity.color}-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">${activity.action}</p>
                            <p class="text-sm text-gray-600">${activity.details}</p>
                            <p class="text-xs text-gray-400 mt-1">${activity.time}</p>
                        </div>
                    </div>
                `).join('');
            }

            // Initialize charts
            initializeCharts();
        }

        // Load blog data
        function loadBlogData() {
            const tableBody = document.getElementById('blogPostsTable');
            if (tableBody) {
                tableBody.innerHTML = appState.data.blogPosts.map(post => `
                    <tr class="hover:bg-gray-50">
                        <td class="font-medium text-gray-900">${post.title}</td>
                        <td class="text-gray-500">${post.author}</td>
                        <td>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${
                                post.status === 'published' ? 'bg-green-100 text-green-800' : 
                                post.status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 
                                'bg-blue-100 text-blue-800'
                            }">
                                ${post.status.charAt(0).toUpperCase() + post.status.slice(1)}
                            </span>
                        </td>
                        <td class="text-gray-500">${new Date(post.date).toLocaleDateString()}</td>
                        <td class="text-gray-500">${post.views.toLocaleString()}</td>
                        <td>
                            <div class="flex space-x-2">
                                <button onclick="editBlogPost(${post.id})" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteBlogPost(${post.id})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
        }

        // Load events data
        function loadEventsData() {
            const eventsGrid = document.getElementById('eventsGrid');
            if (eventsGrid) {
                eventsGrid.innerHTML = appState.data.events.map(event => `
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow card-hover">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-4 text-white">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold">${event.title}</h3>
                                <i class="fas fa-calendar text-lg"></i>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="space-y-2 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-day w-4 mr-2"></i>
                                    <span>${new Date(event.date).toLocaleDateString()}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-clock w-4 mr-2"></i>
                                    <span>${event.time}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt w-4 mr-2"></i>
                                    <span>${event.location}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-users w-4 mr-2"></i>
                                    <span>${event.attendees} attendees</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center mt-4 pt-4 border-t">
                                <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">${event.type}</span>
                                <div class="flex space-x-2">
                                    <button onclick="editEvent(${event.id})" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteEvent(${event.id})" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }

        // Load sermons data
        function loadSermonsData() {
            const sermonsGrid = document.getElementById('sermonsGrid');
            if (sermonsGrid) {
                sermonsGrid.innerHTML = appState.data.sermons.map(sermon => `
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow card-hover">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-600 p-4 text-white">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold">${sermon.title}</h3>
                                <i class="fas fa-microphone text-lg"></i>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="space-y-2 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-user w-4 mr-2"></i>
                                    <span>${sermon.speaker}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-day w-4 mr-2"></i>
                                    <span>${new Date(sermon.date).toLocaleDateString()}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-clock w-4 mr-2"></i>
                                    <span>${sermon.duration}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-download w-4 mr-2"></i>
                                    <span>${sermon.downloads} downloads</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center mt-4 pt-4 border-t">
                                <button class="text-sm bg-purple-100 text-purple-700 px-3 py-1 rounded-full hover:bg-purple-200">
                                    <i class="fas fa-play mr-1"></i> Play
                                </button>
                                <div class="flex space-x-2">
                                    <button onclick="editSermon(${sermon.id})" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteSermon(${sermon.id})" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }

        // Load gallery data
        function loadGalleryData() {
            const galleryGrid = document.getElementById('galleryGrid');
            if (galleryGrid) {
                const sampleImages = [
                    'https://picsum.photos/300/200?random=1',
                    'https://picsum.photos/300/200?random=2',
                    'https://picsum.photos/300/200?random=3',
                    'https://picsum.photos/300/200?random=4',
                    'https://picsum.photos/300/200?random=5',
                    'https://picsum.photos/300/200?random=6',
                    'https://picsum.photos/300/200?random=7',
                    'https://picsum.photos/300/200?random=8'
                ];
                
                galleryGrid.innerHTML = sampleImages.map((img, index) => `
                    <div class="relative group overflow-hidden rounded-xl bg-gray-100 aspect-square card-hover">
                        <img src="${img}" alt="Gallery Image ${index + 1}" class="w-full h-full object-cover transition-transform group-hover:scale-110">
                        <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-2">
                            <button class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-red-600 hover:bg-gray-100">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            }
        }

        // Load leaders data
        function loadLeadersData() {
            const leadersGrid = document.getElementById('leadersGrid');
            if (leadersGrid) {
                leadersGrid.innerHTML = appState.data.leaders.map(leader => `
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow card-hover">
                        <div class="p-6 text-center">
                            <div class="w-20 h-20 bg-gradient-to-r from-indigo-500 to-blue-600 rounded-full mx-auto mb-4 flex items-center justify-center text-white text-2xl font-bold">
                                ${leader.image}
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">${leader.name}</h3>
                            <p class="text-indigo-600 text-sm font-medium mb-3">${leader.role}</p>
                            <p class="text-gray-600 text-sm mb-4">${leader.bio}</p>
                            <div class="flex justify-center space-x-2">
                                <button onclick="editLeader(${leader.id})" class="text-gray-400 hover:text-indigo-600 p-2">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteLeader(${leader.id})" class="text-gray-400 hover:text-red-600 p-2">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }

        // Load team data
        function loadTeamData() {
            const teamTable = document.getElementById('teamTable');
            if (teamTable) {
                teamTable.innerHTML = appState.data.team.map(member => `
                    <tr class="hover:bg-gray-50">
                        <td>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-r from-pink-400 to-rose-400 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                    ${member.name.split(' ').map(n => n[0]).join('')}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">${member.name}</div>
                                    <div class="text-sm text-gray-500">${member.email}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-gray-500">${member.role}</td>
                        <td class="text-gray-500">${member.department}</td>
                        <td>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${
                                member.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                            }">
                                ${member.status.charAt(0).toUpperCase() + member.status.slice(1)}
                            </span>
                        </td>
                        <td class="text-gray-500">${new Date(member.joined).toLocaleDateString()}</td>
                        <td>
                            <div class="flex space-x-2">
                                <button onclick="editTeamMember(${member.id})" class="text-blue-600 hover:text-blue-900">Edit</button>
                                <button onclick="deleteTeamMember(${member.id})" class="text-red-600 hover:text-red-900">Remove</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
        }

        // Load users data
        function loadUsersData() {
            const usersTable = document.getElementById('usersTable');
            if (usersTable) {
                usersTable.innerHTML = appState.data.users.map(user => `
                    <tr class="hover:bg-gray-50">
                        <td>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-purple-400 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                    ${user.name.split(' ').map(n => n[0]).join('')}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">${user.name}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-gray-500">${user.email}</td>
                        <td>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                ${user.role}
                            </span>
                        </td>
                        <td>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${
                                user.status === 'active' ? 'bg-green-100 text-green-800' : 
                                user.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                'bg-red-100 text-red-800'
                            }">
                                ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                            </span>
                        </td>
                        <td class="text-gray-500">${user.lastLogin === 'Never' ? user.lastLogin : new Date(user.lastLogin).toLocaleDateString()}</td>
                        <td>
                            <div class="flex space-x-2">
                                <button onclick="editUser(${user.id})" class="text-blue-600 hover:text-blue-900">Edit</button>
                                <button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-900">Delete</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
        }

        // Initialize charts
        function initializeCharts() {
            // Fetch Blog Posts from API
            async function fetchBlogPosts() {
                try {
                    const response = await fetch('/hearts-after-god-ministry-site/backend/api/blog/posts.php');
                    if (!response.ok) {
                        throw new Error('Failed to fetch blog posts');
                    }
                    const result = await response.json();
                    
                    if (result.success) {
                        // Map the API response to our expected format
                        const posts = result.data.map(post => ({
                            id: post.id,
                            title: post.title,
                            author: 'Admin', // Default author since we don't have author info in the API
                            status: post.status || 'draft',
                            date: post.created_at,
                            content: post.content,
                            views: 0 // Not currently tracked in the database
                        }));
                        
                        renderBlogPosts(blogPosts);
                    } else {
                        throw new Error(result.message || 'Failed to load blog posts');
                    }
                } catch (error) {
                    console.error('Error fetching blog posts:', error);
                    showNotification('Error loading blog posts: ' + error.message, 'error');
                } finally {
                    hideLoading();
                }
            }

            // Render Blog Posts Table
            function renderBlogPosts(posts) {
                const tbody = document.querySelector('#blog-posts-table tbody');
                if (!tbody) return;
                
                tbody.innerHTML = '';
                
                if (!posts || posts.length === 0) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="6" class="text-center">No blog posts found</td>';
                    tbody.appendChild(tr);
                    
                    // Update counts to 0
                    document.getElementById('total-blog-posts').textContent = '0';
                    document.getElementById('published-blog-posts').textContent = '0';
                    document.getElementById('draft-blog-posts').textContent = '0';
                    return;
                }
                
                const filteredPosts = statusFilter === 'all' 
                    ? posts 
                    : posts.filter(post => post.status === statusFilter);
                
                const sortedPosts = [...filteredPosts].sort((a, b) => new Date(b.date) - new Date(a.date));
                
                sortedPosts.forEach(post => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(post.title)}</td>
                        <td>${escapeHtml(post.author)}</td>
                        <td><span class="status ${post.status || 'draft'}">${post.status || 'draft'}</span></td>
                        <td>${formatDate(post.date)}</td>
                        <td>${post.views || 0}</td>
                        <td class="actions">
                            <button class="btn-edit" onclick="editBlogPost(${post.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-delete" onclick="deleteBlogPost(${post.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                
                // Update counts
                document.getElementById('total-blog-posts').textContent = posts.length;
                document.getElementById('published-blog-posts').textContent = 
                    posts.filter(post => post.status === 'published').length;
                document.getElementById('draft-blog-posts').textContent = 
                    posts.filter(post => post.status === 'draft').length;
            }
            
            // Helper function to escape HTML
            function escapeHtml(unsafe) {
                if (typeof unsafe !== 'string') return unsafe;
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('show');
                modal.classList.add('hide');
                document.body.classList.remove('overflow-hidden');
                
                // Reset form if exists
                const form = modal.querySelector('form');
                if (form) {
                    form.reset();
                }
            }
        }

        // Modal opener functions
        function openCreateBlogModal() {
            openModal('createBlogModal');
        }

        function openCreateEventModal() {
            openModal('createEventModal');
        }

        function openCreateSermonModal() {
            showNotification('Sermon creation modal would open here', 'info');
        }

        function openUploadModal() {
            showNotification('Gallery upload modal would open here', 'info');
        }

        function openAddLeaderModal() {
            showNotification('Add leader modal would open here', 'info');
        }

        function openAddTeamMemberModal() {
            showNotification('Add team member modal would open here', 'info');
        }

        function openAddUserModal() {
            showNotification('Add user modal would open here', 'info');
        }

        // CRUD functions
        async function editBlogPost(id) {
            try {
                const url = `/hearts-after-god-ministry-site/backend/api/blog/posts.php?id=${id}`;
                console.log('Fetching blog post from:', url);
                
                const response = await fetch(url);
                
                // Get the response text first to check what we received
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                // Try to parse as JSON
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    throw new Error(`Invalid JSON response from server: ${responseText.substring(0, 100)}...`);
                }
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}, message: ${result.message || 'Unknown error'}`);
                }
                
                if (!result.success) {
                    throw new Error(result.message || 'Failed to fetch blog post');
                }
                
                const post = result.data;
                const modal = document.getElementById('editBlogModal');
                
                // Populate form
                document.getElementById('editBlogId').value = post.id;
                document.getElementById('editBlogTitle').value = post.title;
                document.getElementById('editBlogExcerpt').value = post.excerpt || '';
                document.getElementById('editBlogContent').value = post.content;
                document.getElementById('editBlogStatus').value = post.status;
                
                // Show modal
                modal.classList.remove('hide');
                document.body.classList.add('modal-open');
                
            } catch (error) {
                console.error('Error fetching blog post:', error);
                showNotification('Failed to load blog post: ' + error.message, 'error');
            }
        }

        function deleteBlogPost(id) {
            if (confirm('Are you sure you want to delete this blog post?')) {
                appState.data.blogPosts = appState.data.blogPosts.filter(post => post.id !== id);
                loadBlogData();
                showNotification('Blog post deleted successfully', 'success');
            }
        }

        async function editEvent(id) {
            try {
                const response = await fetch(`/hearts-after-god-ministry-site/backend/api/events/events.php?id=${id}`);
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message || 'Failed to fetch event');
                }
                
                const event = result.data;
                const modal = document.getElementById('editEventModal');
                
                // Format dates for datetime-local input
                const formatDateTime = (dateString) => {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toISOString().slice(0, 16);
                };
                
                // Populate form
                document.getElementById('editEventId').value = event.id;
                document.getElementById('editEventTitle').value = event.title;
                document.getElementById('editEventDescription').value = event.description || '';
                document.getElementById('editEventLocation').value = event.location || '';
                document.getElementById('editEventStart').value = formatDateTime(event.start_datetime);
                document.getElementById('editEventEnd').value = formatDateTime(event.end_datetime);
                document.getElementById('editEventStatus').value = event.status || 'draft';
                
                // Show modal
                modal.classList.remove('hide');
                document.body.classList.add('modal-open');
                
            } catch (error) {
                console.error('Error fetching event:', error);
                showNotification('Failed to load event: ' + error.message, 'error');
            }
        }

        function deleteEvent(id) {
            if (confirm('Are you sure you want to delete this event?')) {
                appState.data.events = appState.data.events.filter(event => event.id !== id);
                loadEventsData();
                showNotification('Event deleted successfully', 'success');
            }
        }

        async function editSermon(id) {
            try {
                const response = await fetch(`/hearts-after-god-ministry-site/backend/api/sermons/sermons.php?id=${id}`);
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message || 'Failed to fetch sermon');
                }
                
                const sermon = result.data;
                const modal = document.getElementById('editSermonModal');
                
                // Populate form
                document.getElementById('editSermonId').value = sermon.id;
                document.getElementById('editSermonTitle').value = sermon.title;
                document.getElementById('editSermonSpeaker').value = sermon.speaker || '';
                document.getElementById('editSermonDescription').value = sermon.description || '';
                document.getElementById('editSermonScripture').value = sermon.scripture || '';
                document.getElementById('editSermonDate').value = sermon.date ? sermon.date.split(' ')[0] : '';
                document.getElementById('editSermonStatus').value = sermon.status || 'draft';
                
                // Show modal
                modal.classList.remove('hide');
                document.body.classList.add('modal-open');
                
            } catch (error) {
                console.error('Error fetching sermon:', error);
                showNotification('Failed to load sermon: ' + error.message, 'error');
            }
        }

        function deleteSermon(id) {
            if (confirm('Are you sure you want to delete this sermon?')) {
                appState.data.sermons = appState.data.sermons.filter(sermon => sermon.id !== id);
                loadSermonsData();
                showNotification('Sermon deleted successfully', 'success');
            }
        }

        async function editLeader(id) {
            try {
                const leader = appState.data.leaders.find(l => l.id === id);
                if (!leader) {
                    throw new Error('Leader not found');
                }
                
                const modal = document.getElementById('editLeaderModal');
                
                // Populate form
                document.getElementById('editLeaderId').value = leader.id;
                document.getElementById('editLeaderName').value = leader.name || '';
                document.getElementById('editLeaderRole').value = leader.role || '';
                document.getElementById('editLeaderBio').value = leader.bio || '';
                document.getElementById('editLeaderEmail').value = leader.email || '';
                document.getElementById('editLeaderPhone').value = leader.phone || '';
                document.getElementById('editLeaderStatus').value = leader.status || 'active';
                
                // Show modal
                modal.classList.remove('hide');
                document.body.classList.add('modal-open');
                
            } catch (error) {
                console.error('Error preparing leader edit:', error);
                showNotification('Failed to load leader: ' + error.message, 'error');
            }
        }

        function deleteLeader(id) {
            if (confirm('Are you sure you want to remove this leader?')) {
                appState.data.leaders = appState.data.leaders.filter(leader => leader.id !== id);
                loadLeadersData();
                showNotification('Leader removed successfully', 'success');
            }
        }

        async function editTeamMember(id) {
            try {
                const member = appState.data.team.find(m => m.id === id);
                if (!member) {
                    throw new Error('Team member not found');
                }
                
                const modal = document.getElementById('editTeamMemberModal');
                
                // Populate form
                document.getElementById('editTeamMemberId').value = member.id;
                document.getElementById('editTeamMemberName').value = member.name || '';
                document.getElementById('editTeamMemberRole').value = member.role || '';
                document.getElementById('editTeamMemberDepartment').value = member.department || '';
                document.getElementById('editTeamMemberBio').value = member.bio || '';
                document.getElementById('editTeamMemberEmail').value = member.email || '';
                document.getElementById('editTeamMemberStatus').value = member.status || 'active';
                
                // Show modal
                modal.classList.remove('hide');
                document.body.classList.add('modal-open');
                
            } catch (error) {
                console.error('Error preparing team member edit:', error);
                showNotification('Failed to load team member: ' + error.message, 'error');
            }
        }

        function deleteTeamMember(id) {
            if (confirm('Are you sure you want to remove this team member?')) {
                appState.data.team = appState.data.team.filter(member => member.id !== id);
                loadTeamData();
                showNotification('Team member removed successfully', 'success');
            }
        }

        async function editUser(id) {
            try {
                const response = await fetch(`/hearts-after-god-ministry-site/backend/api/users/users.php?id=${id}`);
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message || 'Failed to fetch user');
                }
                
                const user = result.data;
                const modal = document.getElementById('editUserModal');
                
                // Populate form
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editUserUsername').value = user.username || '';
                document.getElementById('editUserEmail').value = user.email || '';
                document.getElementById('editUserFirstName').value = user.first_name || '';
                document.getElementById('editUserLastName').value = user.last_name || '';
                document.getElementById('editUserRole').value = user.role || 'user';
                document.getElementById('editUserStatus').value = user.status || 'active';
                
                // Show modal
                modal.classList.remove('hide');
                document.body.classList.add('modal-open');
                
            } catch (error) {
                console.error('Error fetching user:', error);
                showNotification('Failed to load user: ' + error.message, 'error');
            }
        }

        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user?')) {
                appState.data.users = appState.data.users.filter(user => user.id !== id);
                loadUsersData();
                showNotification('User deleted successfully', 'success');
            }
        }

        // Notification system
        function showNotification(message, type = 'info') {
            // Create notification container if it doesn't exist
            let container = document.getElementById('notification-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'notification-container';
                container.className = 'fixed top-4 right-4 z-50 space-y-3 w-80 max-w-full';
                document.body.appendChild(container);
            }
            
            // Create notification element
            const notification = document.createElement('div');
            const notificationId = 'notification-' + Date.now();
            notification.id = notificationId;
            
            // Set notification classes based on type
            const typeClasses = {
                success: 'bg-green-500 border-green-600',
                error: 'bg-red-500 border-red-600',
                warning: 'bg-yellow-500 border-yellow-600',
                info: 'bg-blue-500 border-blue-600'
            };
            
            // Set icon based on type
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            // Initial styles for slide-in animation
            notification.className = `relative p-4 pr-10 rounded-lg shadow-lg text-white ${typeClasses[type] || typeClasses.info} border-l-4 transform transition-all duration-300 translate-x-full opacity-0`;
            
            notification.innerHTML = `
                <div class="flex items-start">
                    <i class="fas ${icons[type] || icons.info} mt-0.5 mr-3 text-lg"></i>
                    <div class="flex-1">
                        <p class="font-medium">${type.charAt(0).toUpperCase() + type.slice(1)}</p>
                        <p class="text-sm opacity-90">${message}</p>
                    </div>
                    <button type="button" class="absolute top-2 right-2 text-white opacity-70 hover:opacity-100 focus:outline-none" onclick="removeNotification('${notificationId}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="absolute bottom-0 left-0 h-1 bg-black bg-opacity-20 w-full overflow-hidden rounded-b">
                    <div class="h-full bg-white bg-opacity-80 progress-bar" style="width: 100%;"></div>
                </div>
            `;
            
            // Add to container
            container.insertBefore(notification, container.firstChild);
            
            // Trigger reflow for animation
            void notification.offsetWidth;
            
            // Slide in animation
            notification.classList.remove('translate-x-full', 'opacity-0');
            notification.classList.add('translate-x-0', 'opacity-100');
            
            // Start progress bar animation
            const progressBar = notification.querySelector('.progress-bar');
            if (progressBar) {
                progressBar.style.transition = 'width 4.5s linear';
                setTimeout(() => {
                    progressBar.style.width = '0%';
                }, 100);
            }
            
            // Auto remove after 5 seconds
            setTimeout(() => removeNotification(notificationId), 5000);
            
            return notification;
        }

        // Remove notification function (global scope)
        function removeNotification(id) {
            const notif = document.getElementById(id);
            if (notif) {
                notif.classList.remove('translate-x-0', 'opacity-100');
                notif.classList.add('translate-x-full', 'opacity-0');
                
                // Remove from DOM after animation
                setTimeout(() => {
                    notif.remove();
                    
                    // Remove container if no more notifications
                    const container = document.getElementById('notification-container');
                    if (container && container.children.length === 0) {
                        container.remove();
                    }
                }, 300);
            }
        }

        // Form submission handlers
        function handleCreateBlog(event) {
            event.preventDefault();
            
            const form = event.target;
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            try {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating...';
                
                const formData = new FormData(form);
                
                const blogData = {
                    id: Date.now(),
                    title: formData.get('title').trim(),
                    author: 'Admin',
                    status: formData.get('status') || 'published',
                    date: new Date().toISOString().split('T')[0],
                    views: 0
                };
                
                // Simulate API call
                setTimeout(() => {
                    appState.data.blogPosts.unshift(blogData);
                    loadBlogData();
                    form.reset();
                    closeModal('createBlogModal');
                    showNotification('Blog post created successfully!', 'success');
                    
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }, 1000);
                
            } catch (error) {
                showNotification(error.message || 'An error occurred while creating the blog post', 'error');
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        }

        function handleCreateEvent(event) {
            event.preventDefault();
            
            const form = event.target;
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            try {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating...';
                
                const formData = new FormData(form);
                
                const eventData = {
                    id: Date.now(),
                    title: formData.get('title').trim(),
                    type: formData.get('type'),
                    date: formData.get('date'),
                    time: formData.get('start_time'),
                    location: formData.get('location').trim(),
                    attendees: 0
                };
                
                // Simulate API call
                setTimeout(() => {
                    appState.data.events.push(eventData);
                    loadEventsData();
                    form.reset();
                    closeModal('createEventModal');
                    showNotification('Event created successfully!', 'success');
                    
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }, 1000);
                
            } catch (error) {
                showNotification(error.message || 'An error occurred while creating the event', 'error');
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        }

        // Update notifications
        function updateNotifications() {
            const notificationCount = appState.notifications.filter(n => !n.read).length;
            const countElement = document.getElementById('notificationCount');
            if (countElement) {
                countElement.textContent = notificationCount;
            }
            
            const notificationList = document.getElementById('notificationList');
            if (notificationList) {
                notificationList.innerHTML = appState.notifications.map(notification => `
                    <div class="p-4 border-b hover:bg-gray-50 ${notification.read ? 'opacity-60' : ''}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">${notification.title}</p>
                                <p class="text-sm text-gray-600">${notification.message}</p>
                                <p class="text-xs text-gray-400 mt-1">${notification.time}</p>
                            </div>
                            ${!notification.read ? '<div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>' : ''}
                        </div>
                    </div>
                `).join('');
            }
        }

        // Initialize tooltips using Tippy.js
        function initTooltips() {
            // Check if Tippy is available
            if (typeof tippy === 'function') {
                tippy('[data-tippy-content]', {
                    theme: 'light',
                    animation: 'scale',
                    arrow: true,
                    delay: [100, 50],
                    duration: [200, 150],
                    interactive: true,
                    placement: 'top',
                    touch: ['hold', 500],
                    zIndex: 9999
                });
            } else {
                console.warn('Tippy.js not loaded - tooltips will not be available');
            }
        }

        // Initialize the application when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize sidebar toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-bars');
                        icon.classList.toggle('fa-times');
                    }
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 1024 && sidebar && sidebarToggle) {
                    const isClickInsideSidebar = sidebar.contains(event.target);
                    const isClickOnToggle = sidebarToggle.contains(event.target);
                    
                    if (!isClickInsideSidebar && !isClickOnToggle && !sidebar.classList.contains('-translate-x-full')) {
                        sidebar.classList.add('-translate-x-full');
                        const icon = sidebarToggle.querySelector('i');
                        if (icon) {
                            icon.classList.add('fa-bars');
                            icon.classList.remove('fa-times');
                        }
                    }
                }
                
                // Close modals when clicking outside
                const modal = event.target.closest('.modal');
                if (modal && event.target.classList.contains('modal-overlay')) {
                    modal.classList.remove('show');
                    modal.classList.add('hide');
                }
                document.body.classList.remove('overflow-hidden');
            });
            
            // Escape key to close modals
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const openModals = document.querySelectorAll('.modal.show');
                    openModals.forEach(modal => {
                        modal.classList.remove('show');
                        modal.classList.add('hide');
                        document.body.classList.remove('overflow-hidden');
                    });
                }
            });
            
            // Form event listeners
            const createBlogForm = document.getElementById('createBlogForm');
            if (createBlogForm) {
                createBlogForm.addEventListener('submit', handleCreateBlog);
            }
            
            const createEventForm = document.getElementById('createEventForm');
            if (createEventForm) {
                createEventForm.addEventListener('submit', handleCreateEvent);
            }
            
            // Notification panel toggle
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationPanel = document.getElementById('notificationPanel');
            
            if (notificationBtn && notificationPanel) {
                notificationBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notificationPanel.classList.toggle('hidden');
                });
            }
            
            // Update notifications
            updateNotifications();
            
            console.log('Hearts After God Ministry Admin Dashboard loaded successfully!');
        });
    </script>

    <!-- Edit Blog Post Modal -->
    <div id="editBlogModal" class="modal hide fixed inset-0 z-50 overflow-y-auto">
        <div class="modal-overlay flex items-center justify-center min-h-screen px-4">
            <div class="modal-content glass-morphism max-w-4xl w-full rounded-2xl shadow-2xl">
                <div class="bg-blue-500 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-edit text-2xl"></i>
                            <h3 class="text-xl font-bold">Edit Blog Post</h3>
                        </div>
                        <button onclick="closeModal('editBlogModal')" class="text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <form id="editBlogForm" class="space-y-6">
                        <input type="hidden" id="editBlogId" name="id">
                        
                        <div>
                            <label for="editBlogTitle" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                            <input type="text" id="editBlogTitle" name="title" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label for="editBlogExcerpt" class="block text-sm font-medium text-gray-700 mb-2">Excerpt</label>
                            <textarea id="editBlogExcerpt" name="excerpt" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        
                        <div>
                            <label for="editBlogContent" class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                            <textarea id="editBlogContent" name="content" rows="6" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editBlogStatus" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="editBlogStatus" name="status" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="editBlogCategories" class="block text-sm font-medium text-gray-700 mb-2">Categories</label>
                                <select id="editBlogCategories" name="categories[]" multiple
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="1">Ministry</option>
                                    <option value="2">Devotional</option>
                                    <option value="3">Events</option>
                                    <option value="4">Announcements</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500">Hold Ctrl/Cmd to select multiple categories</p>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 mt-6">
                            <div class="flex justify-end space-x-4">
                                <button type="button" onclick="closeModal('editBlogModal')"
                                    class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add form submission handler for edit blog form -->
    <script>
        document.getElementById('editBlogForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            try {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
                
                // Convert form data to JSON
                const data = {};
                formData.forEach((value, key) => {
                    if (key === 'categories') {
                        data[key] = formData.getAll(key);
                    } else {
                        data[key] = value;
                    }
                });
                
                // Send update request
                const response = await fetch(`/hearts-after-god-ministry-site/backend/api/blog/posts.php?id=${data.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Failed to update blog post');
                }
                
                // Update UI
                const index = appState.data.blogPosts.findIndex(post => post.id == data.id);
                if (index !== -1) {
                    appState.data.blogPosts[index] = { ...appState.data.blogPosts[index], ...data };
                }
                
                loadBlogData();
                closeModal('editBlogModal');
                showNotification('Blog post updated successfully!', 'success');
                
            } catch (error) {
                console.error('Error updating blog post:', error);
                showNotification('Failed to update blog post: ' + error.message, 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    </script>

    <!-- Edit Event Modal -->
    <div id="editEventModal" class="modal hide fixed inset-0 z-50 overflow-y-auto">
        <div class="modal-overlay flex items-center justify-center min-h-screen px-4">
            <div class="modal-content glass-morphism max-w-4xl w-full rounded-2xl shadow-2xl">
                <div class="bg-green-500 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-calendar-edit text-2xl"></i>
                            <h3 class="text-xl font-bold">Edit Event</h3>
                        </div>
                        <button onclick="closeModal('editEventModal')" class="text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <form id="editEventForm" class="space-y-6">
                        <input type="hidden" id="editEventId" name="id">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editEventTitle" class="block text-sm font-medium text-gray-700 mb-2">Event Title</label>
                                <input type="text" id="editEventTitle" name="title" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                            
                            <div>
                                <label for="editEventType" class="block text-sm font-medium text-gray-700 mb-2">Event Type</label>
                                <select id="editEventType" name="type" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="worship">Worship Service</option>
                                    <option value="bible_study">Bible Study</option>
                                    <option value="prayer">Prayer Meeting</option>
                                    <option value="fellowship">Fellowship</option>
                                    <option value="outreach">Outreach</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editEventStart" class="block text-sm font-medium text-gray-700 mb-2">Start Date & Time</label>
                                <input type="datetime-local" id="editEventStart" name="start_datetime" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                            
                            <div>
                                <label for="editEventEnd" class="block text-sm font-medium text-gray-700 mb-2">End Date & Time</label>
                                <input type="datetime-local" id="editEventEnd" name="end_datetime" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="editEventLocation" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <input type="text" id="editEventLocation" name="location"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="editEventDescription" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="editEventDescription" name="description" rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editEventStatus" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="editEventStatus" name="status" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="scheduled">Scheduled</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="postponed">Postponed</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <div class="w-full">
                                    <label class="flex items-center">
                                        <input type="checkbox" id="editEventFeatured" name="is_featured" value="1"
                                            class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700">Feature this event on the homepage</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 mt-6">
                            <div class="flex justify-end space-x-4">
                                <button type="button" onclick="closeModal('editEventModal')"
                                    class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-6 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add form submission handler for edit event form -->
    <script>
        document.getElementById('editEventForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            try {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
                
                // Convert form data to JSON
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });
                
                // Convert featured checkbox to boolean
                data.is_featured = formData.get('is_featured') === '1';
                
                // Send update request
                const response = await fetch(`/hearts-after-god-ministry-site/backend/api/events/events.php?id=${data.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Failed to update event');
                }
                
                // Update UI
                const index = appState.data.events.findIndex(event => event.id == data.id);
                if (index !== -1) {
                    appState.data.events[index] = { ...appState.data.events[index], ...data };
                }
                
                loadEventsData();
                closeModal('editEventModal');
                showNotification('Event updated successfully!', 'success');
                
            } catch (error) {
                console.error('Error updating event:', error);
                showNotification('Failed to update event: ' + error.message, 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    </script>

    <!-- Edit Sermon Modal -->
    <div id="editSermonModal" class="modal hide fixed inset-0 z-50 overflow-y-auto">
        <div class="modal-overlay flex items-center justify-center min-h-screen px-4">
            <div class="modal-content glass-morphism max-w-4xl w-full rounded-2xl shadow-2xl">
                <div class="bg-purple-500 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-microphone-alt text-2xl"></i>
                            <h3 class="text-xl font-bold">Edit Sermon</h3>
                        </div>
                        <button onclick="closeModal('editSermonModal')" class="text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <form id="editSermonForm" class="space-y-6">
                        <input type="hidden" id="editSermonId" name="id">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editSermonTitle" class="block text-sm font-medium text-gray-700 mb-2">Sermon Title</label>
                                <input type="text" id="editSermonTitle" name="title" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            
                            <div>
                                <label for="editSermonSpeaker" class="block text-sm font-medium text-gray-700 mb-2">Speaker</label>
                                <input type="text" id="editSermonSpeaker" name="speaker" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editSermonDate" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                <input type="date" id="editSermonDate" name="date" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            
                            <div>
                                <label for="editSermonDuration" class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                                <input type="number" id="editSermonDuration" name="duration" min="1"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editSermonScripture" class="block text-sm font-medium text-gray-700 mb-2">Scripture Reference</label>
                                <input type="text" id="editSermonScripture" name="scripture"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="e.g., John 3:16-18">
                            </div>
                            
                            <div>
                                <label for="editSermonSeries" class="block text-sm font-medium text-gray-700 mb-2">Series</label>
                                <input type="text" id="editSermonSeries" name="series"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="editSermonDescription" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="editSermonDescription" name="description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editSermonAudio" class="block text-sm font-medium text-gray-700 mb-2">Audio File</label>
                                <div class="flex items-center">
                                    <input type="file" id="editSermonAudio" name="audio" accept="audio/*"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                </div>
                                <p id="currentAudioFile" class="mt-1 text-sm text-gray-500"></p>
                            </div>
                            
                            <div>
                                <label for="editSermonVideo" class="block text-sm font-medium text-gray-700 mb-2">Video URL (Optional)</label>
                                <input type="url" id="editSermonVideo" name="video_url"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="https://youtube.com/watch?v=...">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editSermonStatus" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="editSermonStatus" name="status" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <div class="w-full">
                                    <label class="flex items-center">
                                        <input type="checkbox" id="editSermonFeatured" name="is_featured" value="1"
                                            class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500">
                                        <span class="ml-2 text-sm text-gray-700">Feature this sermon on the homepage</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 mt-6">
                            <div class="flex justify-end space-x-4">
                                <button type="button" onclick="closeModal('editSermonModal')"
                                    class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-6 py-2 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add form submission handler for edit sermon form -->
    <script>
        document.getElementById('editSermonForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            try {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
                
                // Handle file upload if a new audio file is selected
                const audioFile = document.getElementById('editSermonAudio').files[0];
                if (audioFile) {
                    // In a real implementation, you would upload the file first
                    // and then update the sermon with the new file URL
                    console.log('New audio file selected:', audioFile.name);
                    // For now, we'll just add it to the form data
                    formData.append('audio_file', audioFile);
                }
                
                // Convert form data to JSON (except file)
                const data = {};
                formData.forEach((value, key) => {
                    if (key !== 'audio') { // Skip the file input
                        data[key] = value;
                    }
                });
                
                // Convert featured checkbox to boolean
                data.is_featured = formData.get('is_featured') === '1';
                
                // Send update request
                const response = await fetch(`/hearts-after-god-ministry-site/backend/api/sermons/sermons.php?id=${data.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Failed to update sermon');
                }
                
                // Update UI
                const index = appState.data.sermons.findIndex(sermon => sermon.id == data.id);
                if (index !== -1) {
                    appState.data.sermons[index] = { ...appState.data.sermons[index], ...data };
                }
                
                loadSermonsData();
                closeModal('editSermonModal');
                showNotification('Sermon updated successfully!', 'success');
                
            } catch (error) {
                console.error('Error updating sermon:', error);
                showNotification('Failed to update sermon: ' + error.message, 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    </script>

    <!-- Edit Leader Modal -->
    <div id="editLeaderModal" class="modal hide fixed inset-0 z-50 overflow-y-auto">
        <div class="modal-overlay flex items-center justify-center min-h-screen px-4">
            <div class="modal-content glass-morphism max-w-4xl w-full rounded-2xl shadow-2xl">
                <div class="bg-indigo-500 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-user-shield text-2xl"></i>
                            <h3 class="text-xl font-bold">Edit Leader</h3>
                        </div>
                        <button onclick="closeModal('editLeaderModal')" class="text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <form id="editLeaderForm" class="space-y-6">
                        <input type="hidden" id="editLeaderId" name="id">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editLeaderFirstName" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input type="text" id="editLeaderFirstName" name="first_name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            
                            <div>
                                <label for="editLeaderLastName" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input type="text" id="editLeaderLastName" name="last_name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editLeaderEmail" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" id="editLeaderEmail" name="email"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            
                            <div>
                                <label for="editLeaderPhone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <input type="tel" id="editLeaderPhone" name="phone"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="editLeaderRole" class="block text-sm font-medium text-gray-700 mb-2">Role/Position</label>
                            <input type="text" id="editLeaderRole" name="role" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editLeaderDepartment" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                <select id="editLeaderDepartment" name="department"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="pastoral">Pastoral</option>
                                    <option value="worship">Worship</option>
                                    <option value="youth">Youth Ministry</option>
                                    <option value="children">Children's Ministry</option>
                                    <option value="outreach">Outreach</option>
                                    <option value="administration">Administration</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="editLeaderOrder" class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                                <input type="number" id="editLeaderOrder" name="display_order" min="1" value="1"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="editLeaderBio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                            <textarea id="editLeaderBio" name="bio" rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editLeaderPhoto" class="block text-sm font-medium text-gray-700 mb-2">Photo</label>
                                <div class="flex items-center">
                                    <input type="file" id="editLeaderPhoto" name="photo" accept="image/*"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                                <div id="currentLeaderPhoto" class="mt-2"></div>
                            </div>
                            
                            <div class="flex flex-col justify-end">
                                <div class="mb-4">
                                    <label for="editLeaderStatus" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select id="editLeaderStatus" name="is_active" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                
                                <div class="flex items-center">
                                    <input type="checkbox" id="editLeaderFeatured" name="is_featured" value="1"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <label for="editLeaderFeatured" class="ml-2 block text-sm text-gray-700">
                                        Feature on leadership page
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 mt-6">
                            <div class="flex justify-end space-x-4">
                                <button type="button" onclick="closeModal('editLeaderModal')"
                                    class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add form submission handler for edit leader form -->
    <script>
        document.getElementById('editLeaderForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            try {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
                
                // Handle file upload if a new photo is selected
                const photoFile = document.getElementById('editLeaderPhoto').files[0];
                if (photoFile) {
                    // In a real implementation, you would upload the file first
                    // and then update the leader with the new photo URL
                    console.log('New photo selected:', photoFile.name);
                    // For now, we'll just add it to the form data
                    formData.append('photo_file', photoFile);
                }
                
                // Convert form data to JSON (except file)
                const data = {};
                formData.forEach((value, key) => {
                    if (key !== 'photo') { // Skip the file input
                        data[key] = value;
                    }
                });
                
                // Convert checkboxes to boolean
                data.is_featured = formData.get('is_featured') === '1';
                data.is_active = formData.get('is_active') === '1';
                
                // Send update request
                const response = await fetch(`/hearts-after-god-ministry-site/backend/api/leaders/leaders.php?id=${data.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Failed to update leader');
                }
                
                // Update UI
                const index = appState.data.leaders.findIndex(leader => leader.id == data.id);
                if (index !== -1) {
                    appState.data.leaders[index] = { ...appState.data.leaders[index], ...data };
                }
                
                loadLeadersData();
                closeModal('editLeaderModal');
                showNotification('Leader updated successfully!', 'success');
                
            } catch (error) {
                console.error('Error updating leader:', error);
                showNotification('Failed to update leader: ' + error.message, 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    </script>

    <!-- Edit Team Member Modal -->
    <div id="editTeamMemberModal" class="modal hide fixed inset-0 z-50 overflow-y-auto">
        <div class="modal-overlay flex items-center justify-center min-h-screen px-4">
            <div class="modal-content glass-morphism max-w-4xl w-full rounded-2xl shadow-2xl">
                <div class="bg-amber-500 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-users text-2xl"></i>
                            <h3 class="text-xl font-bold">Edit Team Member</h3>
                        </div>
                        <button onclick="closeModal('editTeamMemberModal')" class="text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <form id="editTeamMemberForm" class="space-y-6">
                        <input type="hidden" id="editTeamMemberId" name="id">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editTeamMemberFirstName" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input type="text" id="editTeamMemberFirstName" name="first_name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                            
                            <div>
                                <label for="editTeamMemberLastName" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input type="text" id="editTeamMemberLastName" name="last_name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editTeamMemberEmail" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" id="editTeamMemberEmail" name="email"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                            
                            <div>
                                <label for="editTeamMemberPhone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <input type="tel" id="editTeamMemberPhone" name="phone"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editTeamMemberPosition" class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                                <input type="text" id="editTeamMemberPosition" name="position" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                            
                            <div>
                                <label for="editTeamMemberDepartment" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                <select id="editTeamMemberDepartment" name="department"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                    <option value="worship">Worship Team</option>
                                    <option value="media">Media Team</option>
                                    <option value="hospitality">Hospitality Team</option>
                                    <option value="children">Children's Ministry</option>
                                    <option value="youth">Youth Ministry</option>
                                    <option value="outreach">Outreach Team</option>
                                    <option value="prayer">Prayer Team</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label for="editTeamMemberBio" class="block text-sm font-medium text-gray-700 mb-2">Bio/Description</label>
                            <textarea id="editTeamMemberBio" name="bio" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editTeamMemberPhoto" class="block text-sm font-medium text-gray-700 mb-2">Photo</label>
                                <div class="flex items-center">
                                    <input type="file" id="editTeamMemberPhoto" name="photo" accept="image/*"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                                </div>
                                <div id="currentTeamMemberPhoto" class="mt-2"></div>
                            </div>
                            
                            <div class="flex flex-col justify-end">
                                <div class="mb-4">
                                    <label for="editTeamMemberStatus" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select id="editTeamMemberStatus" name="is_active" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                
                                <div class="flex items-center">
                                    <input type="checkbox" id="editTeamMemberFeatured" name="is_featured" value="1"
                                        class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                                    <label for="editTeamMemberFeatured" class="ml-2 block text-sm text-gray-700">
                                        Feature on team page
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 mt-6">
                            <div class="flex justify-end space-x-4">
                                <button type="button" onclick="closeModal('editTeamMemberModal')"
                                    class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-6 py-2 bg-amber-600 text-white rounded-lg font-medium hover:bg-amber-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add form submission handler for edit team member form -->
    <script>
        document.getElementById('editTeamMemberForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            try {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
                
                // Handle file upload if a new photo is selected
                const photoFile = document.getElementById('editTeamMemberPhoto').files[0];
                if (photoFile) {
                    // In a real implementation, you would upload the file first
                    // and then update the team member with the new photo URL
                    console.log('New photo selected:', photoFile.name);
                    // For now, we'll just add it to the form data
                    formData.append('photo_file', photoFile);
                }
                
                // Convert form data to JSON (except file)
                const data = {};
                formData.forEach((value, key) => {
                    if (key !== 'photo') { // Skip the file input
                        data[key] = value;
                    }
                });
                
                // Convert checkboxes to boolean
                data.is_featured = formData.get('is_featured') === '1';
                data.is_active = formData.get('is_active') === '1';
                
                // Send update request
                const response = await fetch(`/hearts-after-god-ministry-site/backend/api/team/team_members.php?id=${data.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Failed to update team member');
                }
                
                // Update UI
                const index = appState.data.teamMembers.findIndex(member => member.id == data.id);
                if (index !== -1) {
                    appState.data.teamMembers[index] = { ...appState.data.teamMembers[index], ...data };
                }
                
                loadTeamMembersData();
                closeModal('editTeamMemberModal');
                showNotification('Team member updated successfully!', 'success');
                
            } catch (error) {
                console.error('Error updating team member:', error);
                showNotification('Failed to update team member: ' + error.message, 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    </script>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal hide fixed inset-0 z-50 overflow-y-auto">
        <div class="modal-overlay flex items-center justify-center min-h-screen px-4">
            <div class="modal-content glass-morphism max-w-2xl w-full rounded-2xl shadow-2xl">
                <div class="bg-rose-500 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-user-edit text-2xl"></i>
                            <h3 class="text-xl font-bold">Edit User</h3>
                        </div>
                        <button onclick="closeModal('editUserModal')" class="text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <form id="editUserForm" class="space-y-6">
                        <input type="hidden" id="editUserId" name="id">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editUserFirstName" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input type="text" id="editUserFirstName" name="first_name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                            </div>
                            
                            <div>
                                <label for="editUserLastName" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input type="text" id="editUserLastName" name="last_name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="editUserEmail" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="editUserEmail" name="email" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editUserRole" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                <select id="editUserRole" name="role" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                                    <option value="user">User</option>
                                    <option value="editor">Editor</option>
                                    <option value="admin">Administrator</option>
                                    <option value="super_admin">Super Admin</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="editUserStatus" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="editUserStatus" name="status" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="pending">Pending Approval</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Change Password (leave blank to keep current password)</h4>
                            <div class="space-y-4">
                                <div>
                                    <label for="editUserNewPassword" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                    <div class="relative">
                                        <input type="password" id="editUserNewPassword" name="new_password"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                                            autocomplete="new-password">
                                        <button type="button" onclick="togglePasswordVisibility('editUserNewPassword')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="editUserConfirmPassword" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                    <div class="relative">
                                        <input type="password" id="editUserConfirmPassword" name="confirm_password"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                                            autocomplete="new-password">
                                        <button type="button" onclick="togglePasswordVisibility('editUserConfirmPassword')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 mt-6">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    Last login: <span id="editUserLastLogin">-</span>
                                </div>
                                <div class="flex space-x-4">
                                    <button type="button" onclick="closeModal('editUserModal')"
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-6 py-2 bg-rose-600 text-white rounded-lg font-medium hover:bg-rose-700 transition-colors">
                                        <i class="fas fa-save mr-2"></i>Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add form submission handler for edit user form -->
    <script>
        // Toggle password visibility
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('editUserForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            // Get password values
            const newPassword = document.getElementById('editUserNewPassword').value;
            const confirmPassword = document.getElementById('editUserConfirmPassword').value;
            
            // Validate passwords if either is filled
            if (newPassword || confirmPassword) {
                if (newPassword !== confirmPassword) {
                    showNotification('Passwords do not match', 'error');
                    return;
                }
                
                if (newPassword.length < 8) {
                    showNotification('Password must be at least 8 characters long', 'error');
                    return;
                }
                
                // Add password to form data if it's being changed
                formData.append('password', newPassword);
            }
            
            try {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
                
                // Convert form data to JSON
                const data = {};
                formData.forEach((value, key) => {
                    // Only include password if it's being changed
                    if (key === 'password' && !value) return;
                    data[key] = value;
                });
                
                // Remove the password fields that were just for validation
                delete data.new_password;
                delete data.confirm_password;
                
                // Send update request
                const response = await fetch(`/hearts-after-god-ministry-site/backend/api/users/users.php?id=${data.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Failed to update user');
                }
                
                // Update UI
                const index = appState.data.users.findIndex(user => user.id == data.id);
                if (index !== -1) {
                    // Update the user data but preserve sensitive fields that might not be in the response
                    const updatedUser = { ...appState.data.users[index], ...data };
                    appState.data.users[index] = updatedUser;
                }
                
                loadUsersData();
                closeModal('editUserModal');
                showNotification('User updated successfully!', 'success');
                
            } catch (error) {
                console.error('Error updating user:', error);
                showNotification('Failed to update user: ' + error.message, 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    </script>
    
    <!-- Dashboard initialization is handled in the main script -->
</body>
</html>