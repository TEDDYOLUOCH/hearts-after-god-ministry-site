<?php
/**
 * Admin Blog List View
 * 
 * Displays a list of blog posts with search, filter, and sort functionality
 */

// Include database configuration and functions
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/admin_manage_blog.php';

// Initialize variables
$error = null;
$posts = [];
$total_posts = 0;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sort_order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

// Get database connection
try {
    $pdo = getDbConnection();
    
    // Build base query
    $query = "SELECT SQL_CALC_FOUND_ROWS p.*, 
              u.name as author_name, 
              GROUP_CONCAT(DISTINCT c.name ORDER BY c.name ASC SEPARATOR ', ') as categories
              FROM blog_posts p
              LEFT JOIN users u ON p.author_id = u.id
              LEFT JOIN blog_post_categories pc ON p.id = pc.post_id
              LEFT JOIN categories c ON pc.category_id = c.id";
    
    $where = [];
    $params = [];
    
    // Apply search filter
    if (!empty($search)) {
        $where[] = "(p.title LIKE :search OR p.excerpt LIKE :search OR p.content LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    // Apply status filter
    if ($status_filter !== 'all') {
        $where[] = "p.status = :status";
        $params[':status'] = $status_filter;
    }
    
    // Apply category filter
    if ($category_filter > 0) {
        $where[] = "EXISTS (SELECT 1 FROM blog_post_categories pc2 WHERE pc2.post_id = p.id AND pc2.category_id = :category_id)";
        $params[':category_id'] = $category_filter;
    }
    
    // Add WHERE clause if we have any conditions
    if (!empty($where)) {
        $query .= " WHERE " . implode(' AND ', $where);
    }
    
    // Group by post ID
    $query .= " GROUP BY p.id";
    
    // Validate sort column to prevent SQL injection
    $valid_sort_columns = ['title', 'created_at', 'updated_at', 'status'];
    if (!in_array($sort_by, $valid_sort_columns)) {
        $sort_by = 'created_at';
    }
    
    // Add sorting
    $query .= " ORDER BY $sort_by $sort_order";
    
    // Add pagination
    $offset = ($current_page - 1) * $per_page;
    $query .= " LIMIT :offset, :per_page";
    
    // Prepare and execute the query
    $stmt = $pdo->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    // Bind pagination parameters
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total number of posts for pagination
    $total_posts = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
    $total_pages = ceil($total_posts / $per_page);
    
    // Get all categories for filter dropdown
    $categories = $pdo->query("SELECT * FROM categories WHERE type = 'blog' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    error_log($error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .status-draft { background-color: #fef3c7; color: #92400e; }
        .status-published { background-color: #d1fae5; color: #065f46; }
        .status-archived { background-color: #e5e7eb; color: #374151; }
        .sortable { cursor: pointer; }
        .sortable:hover { background-color: #f3f4f6; }
        .pagination .active { background-color: #3b82f6; color: white; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Messages will be displayed here -->
        
        <!-- Header and Actions -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Blog Management
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage your blog posts, create new content, and organize your categories.
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="admin_manage_blog.php?action=create" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    New Post
                </a>
            </div>
        </div>
        
        <!-- Search and Filters -->
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <form action="" method="get" class="space-y-4 sm:space-y-0 sm:flex sm:space-x-4">
                <!-- Search Input -->
                <div class="flex-1">
                    <label for="search" class="sr-only">Search posts</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md h-10" placeholder="Search posts...">
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="w-full sm:w-48">
                    <label for="status" class="sr-only">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md h-10">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $status_filter === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="archived" <?= $status_filter === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                
                <!-- Category Filter -->
                <div class="w-full sm:w-48">
                    <label for="category" class="sr-only">Category</label>
                    <select id="category" name="category" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md h-10">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Sort Controls -->
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_by) ?>">
                <input type="hidden" name="order" value="<?= htmlspecialchars($sort_order) ?>">
                
                <!-- Submit Button -->
                <div class="flex-shrink-0">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 h-10">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-1 1A1 1 0 0110 17H8a1 1 0 01-1-1v-3.586l-3.707-3.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                        </svg>
                        Filter
                    </button>
                </div>
                
                <!-- Reset Button -->
                <div class="flex-shrink-0">
                    <a href="?" class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 h-10">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Blog Posts Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'title', 'order' => $sort_by === 'title' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])) ?>" class="group inline-flex">
                                    Title
                                    <?php if ($sort_by === 'title'): ?>
                                        <span class="ml-1">
                                            <?= $sort_order === 'ASC' ? '↑' : '↓' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="ml-1 opacity-0 group-hover:opacity-100">↑↓</span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Author
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'status', 'order' => $sort_by === 'status' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])) ?>" class="group inline-flex">
                                    Status
                                    <?php if ($sort_by === 'status'): ?>
                                        <span class="ml-1">
                                            <?= $sort_order === 'ASC' ? '↑' : '↓' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="ml-1 opacity-0 group-hover:opacity-100">↑↓</span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Categories
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'created_at', 'order' => $sort_by === 'created_at' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])) ?>" class="group inline-flex">
                                    Created
                                    <?php if ($sort_by === 'created_at'): ?>
                                        <span class="ml-1">
                                            <?= $sort_order === 'ASC' ? '↑' : '↓' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="ml-1 opacity-0 group-hover:opacity-100">↑↓</span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No blog posts found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php if (!empty($post['featured_image'])): ?>
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-md object-cover" src="<?= htmlspecialchars($post['featured_image']) ?>" alt="">
                                                </div>
                                            <?php endif; ?>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($post['title']) ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    /<?= htmlspecialchars($post['slug']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($post['author_name'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusClasses = [
                                            'draft' => 'status-draft',
                                            'published' => 'status-published',
                                            'archived' => 'status-archived'
                                        ];
                                        $statusText = ucfirst($post['status']);
                                        $statusClass = $statusClasses[$post['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= $post['categories'] ? htmlspecialchars($post['categories']) : '—' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M j, Y', strtotime($post['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="admin_manage_blog.php?edit=<?= $post['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <a href="#" onclick="confirmDelete(<?= $post['id'] ?>)" class="text-red-600 hover:text-red-900">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium"><?= (($current_page - 1) * $per_page) + 1 ?></span>
                                to <span class="font-medium"><?= min($current_page * $per_page, $total_posts) ?></span>
                                of <span class="font-medium"><?= $total_posts ?></span> results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <!-- Previous Page Link -->
                                <?php if ($current_page > 1): ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Previous</span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Page Numbers -->
                                <?php
                                $start = max(1, $current_page - 2);
                                $end = min($start + 4, $total_pages);
                                $start = max(1, $end - 4);
                                
                                if ($start > 1) {
                                    echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>';
                                    if ($start > 2) {
                                        echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                    }
                                }
                                
                                for ($i = $start; $i <= $end; $i++):
                                    if ($i == $current_page): ?>
                                        <span class="relative inline-flex items-center px-4 py-2 border border-indigo-500 bg-indigo-50 text-sm font-medium text-indigo-600">
                                            <?= $i ?>
                                        </span>
                                    <?php else: ?>
                                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            <?= $i ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($end < $total_pages): ?>
                                    <?php if ($end < $total_pages - 1): ?>
                                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>
                                    <?php endif; ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        <?= $total_pages ?>
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Next Page Link -->
                                <?php if ($current_page < $total_pages): ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Next</span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Delete Post
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to delete this post? This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <form id="deleteForm" method="post" class="inline-flex">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                    </form>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Delete confirmation
        function confirmDelete(postId) {
            const form = document.getElementById('deleteForm');
            form.action = `?delete=${postId}`;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        
        function closeModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
