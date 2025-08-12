<?php
require_once __DIR__ . '/../includes/standard_layout.php';

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Function to get users data
function getUsersData($db) {
    try {
        $stmt = $db->query("
            SELECT id, username, email, role, created_at, last_login 
            FROM users 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Error loading users: ' . $e->getMessage());
        return [];
    }
}

// Handle AJAX request
if ($isAjax) {
    header('Content-Type: application/json');
    
    try {
        // Get database connection
        require_once __DIR__ . '/../config/database.php';
        $db = getDbConnection();
        
        $users = getUsersData($db);
        
        // Render the users table
        ob_start();
        ?>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Manage Users</h2>
                <button class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i> Add New User
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Login</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                <?= htmlspecialchars($user['username']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                <?= htmlspecialchars($user['email']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?= $user['role'] === 'admin' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' ?>">
                                    <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                <?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        $html = ob_get_clean();
        
        echo json_encode([
            'success' => true,
            'html' => $html
        ]);
        exit;
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to load users: ' . $e->getMessage()
        ]);
        exit;
    }
}

// For non-AJAX requests, render the full layout
renderStandardLayout('Users Management', function() {
    // This will be loaded via AJAX
    ?>
    <div id="dynamic-content">
        <div class="flex items-center justify-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
        </div>
    </div>
    <?php
});
