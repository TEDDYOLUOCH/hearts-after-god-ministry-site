<?php
require_once __DIR__ . '/../BaseApiHandler.php';

// Require admin authentication for all user operations
requireAdminAuth();

$db = getDb();
$method = $_SERVER['REQUEST_METHOD'];

// Helper function to get user by ID
function getUserById($id) {
    global $db;
    $stmt = $db->prepare("SELECT id, username, email, role, created_at, last_login FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        // Get single user by ID
        if (isset($_GET['id'])) {
            $user = getUserById($_GET['id']);
            if (!$user) {
                sendError('User not found', 404);
            }
            sendSuccess($user);
        } 
        // Get all users with optional filters
        else {
            $role = $_GET['role'] ?? null;
            $search = $_GET['search'] ?? null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT id, username, email, role, created_at, last_login 
                     FROM users WHERE 1=1";
            $countQuery = "SELECT COUNT(*) as total FROM users WHERE 1=1";
            $params = [];
            $countParams = [];
            
            // Apply role filter
            if ($role) {
                $query .= " AND role = ?";
                $countQuery .= " AND role = ?";
                $params[] = $role;
                $countParams[] = $role;
            }
            
            // Apply search filter
            if ($search) {
                $searchTerm = "%$search%";
                $query .= " AND (username LIKE ? OR email LIKE ?)";
                $countQuery .= " AND (username LIKE ? OR email LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
            }
            
            // Add sorting and pagination
            $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            // Get users
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $countStmt = $db->prepare($countQuery);
            $countStmt->execute($countParams);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            sendSuccess([
                'users' => $users,
                'pagination' => [
                    'total' => (int)$total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => ceil($total / $limit)
                ]
            ]);
        }
        break;
        
    case 'POST':
        // Create new user
        $data = getJsonInput();
        $requiredFields = ['username', 'email', 'password', 'role'];
        validateRequiredFields($data, $requiredFields);
        
        // Validate role
        $allowedRoles = ['admin', 'editor', 'user'];
        if (!in_array($data['role'], $allowedRoles)) {
            sendError('Invalid role. Allowed roles: ' . implode(', ', $allowedRoles), 400);
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            sendError('Invalid email format', 400);
        }
        
        // Validate password strength
        if (strlen($data['password']) < 8) {
            sendError('Password must be at least 8 characters long', 400);
        }
        
        try {
            // Check if username or email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$data['username'], $data['email']]);
            if ($stmt->fetch()) {
                sendError('Username or email already exists', 400);
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $db->prepare("
                INSERT INTO users 
                (username, email, password, role, created_at)
                VALUES (?, ?, ?, ?, NOW())
            
            ");
            
            $stmt->execute([
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['role']
            ]);
            
            $userId = $db->lastInsertId();
            
            // Get the created user (without password)
            $user = getUserById($userId);
            
            sendSuccess($user, 'User created successfully');
            
        } catch (Exception $e) {
            sendError('Failed to create user: ' . $e->getMessage(), 500);
        }
        break;
        
    case 'PUT':
        // Update existing user
        $data = getJsonInput();
        $requiredFields = ['id', 'username', 'email', 'role'];
        validateRequiredFields($data, $requiredFields);
        
        // Validate role
        $allowedRoles = ['admin', 'editor', 'user'];
        if (!in_array($data['role'], $allowedRoles)) {
            sendError('Invalid role. Allowed roles: ' . implode(', ', $allowedRoles), 400);
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            sendError('Invalid email format', 400);
        }
        
        $user = getUserById($data['id']);
        if (!$user) {
            sendError('User not found', 404);
        }
        
        try {
            // Check if username or email already exists for another user
            $stmt = $db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$data['username'], $data['email'], $data['id']]);
            if ($stmt->fetch()) {
                sendError('Username or email already in use by another account', 400);
            }
            
            // Prepare base update query
            $query = "UPDATE users SET username = ?, email = ?, role = ?";
            $params = [
                $data['username'],
                $data['email'],
                $data['role']
            ];
            
            // Add password update if provided
            if (!empty($data['password'])) {
                if (strlen($data['password']) < 8) {
                    sendError('Password must be at least 8 characters long', 400);
                }
                $query .= ", password = ?";
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $query .= " WHERE id = ?";
            $params[] = $data['id'];
            
            // Update user
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            
            // Get the updated user (without password)
            $updatedUser = getUserById($data['id']);
            
            sendSuccess($updatedUser, 'User updated successfully');
            
        } catch (Exception $e) {
            sendError('Failed to update user: ' . $e->getMessage(), 500);
        }
        break;
        
    case 'DELETE':
        // Delete user
        $data = getJsonInput();
        if (!isset($data['id'])) {
            sendError('User ID is required', 400);
        }
        
        // Prevent deleting the current user
        if ($data['id'] == ($_SESSION['user_id'] ?? null)) {
            sendError('You cannot delete your own account', 400);
        }
        
        $user = getUserById($data['id']);
        if (!$user) {
            sendError('User not found', 404);
        }
        
        try {
            // Delete user
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$data['id']]);
            
            // Here you might want to handle any related data (e.g., posts, comments, etc.)
            // For example, you might want to reassign or delete the user's content
            
            sendSuccess(null, 'User deleted successfully');
            
        } catch (Exception $e) {
            sendError('Failed to delete user: ' . $e->getMessage(), 500);
        }
        break;
        
    default:
        sendError('Method not allowed', 405);
}
