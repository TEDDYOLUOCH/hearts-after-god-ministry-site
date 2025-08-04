<?php
// Session is already started in admin-dashboard.php
require_once __DIR__ . '/../../backend/config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    exit;
}

// Handle CRUD Operations
$message = '';
$message_type = '';

// CREATE or UPDATE user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        $role = $_POST['role'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if ($id) {
            // UPDATE
            $sql = "UPDATE users SET name=?, email=?, address=?, role=?, is_active=?";
            $params = [$name, $email, $address, $role, $is_active];
            
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $sql .= ", password=?";
                $params[] = $password;
            }
            
            $sql .= " WHERE id=?";
            $params[] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = "User updated successfully.";
            $message_type = 'success';
            // Redirect to clear edit param and reset form
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        } else {
            // INSERT
            if (empty($_POST['password'])) {
                throw new Exception("Password is required for new users.");
            }
            
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, address, password, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $address, $password, $role, $is_active]);
            $message = "User created successfully.";
            $message_type = 'success';
            // Redirect to clear edit param and reset form
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        }
        // Clear the form
        unset($editUser);
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = 'error';
    }
}

// DELETE user
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "User deleted successfully.";
        $message_type = 'success';
    } catch (Exception $e) {
        $message = "Error deleting user: " . $e->getMessage();
        $message_type = 'error';
    }
}

// EDIT user (populate form)
$editId = $_GET['edit'] ?? null;
$editUser = null;
if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$editUser) {
        $message = "User not found.";
        $message_type = 'error';
    }
}

// Fetch all users
$stmt = $pdo->prepare("SELECT * FROM users WHERE id != ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]); // Exclude current admin
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Function to clear the form and reset the URL
    function clearForm() {
      window.location.href = window.location.pathname;
    }
  </script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Manage Users</h1>
      <?php if (isset($editUser)): ?>
        <button onclick="clearForm()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
          Cancel Edit
        </button>
      <?php endif; ?>
    </div>

    <?php if ($message): ?>
      <div class="mb-6 p-4 rounded <?= $message_type === 'error' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($editUser)): ?>
      <pre class="bg-yellow-100 text-yellow-900 p-2 rounded mb-4">
        <?php print_r($editUser); ?>
      </pre>
    <?php endif; ?>

    <form method="POST" class="bg-white shadow-md p-6 rounded mb-6">
      <input type="hidden" name="id" value="<?= $editUser['id'] ?? '' ?>">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
          <input type="text" name="name" required 
                 class="w-full p-2 border rounded" 
                 value="<?= htmlspecialchars($editUser['name'] ?? '') ?>">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input type="email" name="email" required 
                 class="w-full p-2 border rounded" 
                 value="<?= htmlspecialchars($editUser['email'] ?? '') ?>">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
          <input type="text" name="address" 
                 class="w-full p-2 border rounded" 
                 value="<?= htmlspecialchars($editUser['address'] ?? '') ?>">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            <?= isset($editUser) ? 'New Password (leave blank to keep current)' : 'Password' ?>
          </label>
          <input type="password" name="password" 
                 <?= !isset($editUser) ? 'required' : '' ?> 
                 class="w-full p-2 border rounded" 
                 placeholder="<?= isset($editUser) ? '••••••••' : '********' ?>">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
          <select name="role" required class="w-full p-2 border rounded">
            <?php
            $roles = ['admin' => 'Administrator', 'media_team' => 'Media Team', 'ministry_leader' => 'Ministry Leader', 
                     'event_coordinator' => 'Event Coordinator', 'discipleship_leader' => 'Discipleship Leader', 
                     'registered_member' => 'Registered Member'];
            foreach ($roles as $value => $label):
            ?>
              <option value="<?= $value ?>" <?= ($editUser['role'] ?? '') === $value ? 'selected' : '' ?>>
                <?= $label ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex items-end">
          <label class="flex items-center space-x-2">
            <input type="checkbox" name="is_active" value="1" 
                   <?= ($editUser['is_active'] ?? 0) ? 'checked' : '' ?>>
            <span>Active</span>
          </label>
        </div>
      </div>
      <div class="mt-6">
        <button type="submit" class="bg-purple-700 text-white px-6 py-2 rounded hover:bg-purple-800">
          <?= isset($editUser) ? 'Update User' : 'Create User' ?>
        </button>
        <?php if (isset($editUser)): ?>
          <button type="button" onclick="clearForm()" 
                  class="ml-2 bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
            Cancel
          </button>
        <?php endif; ?>
      </div>
    </form>

    <div class="bg-white shadow-md rounded overflow-hidden">
      <table class="min-w-full">
        <thead class="bg-purple-700 text-white">
          <tr>
            <th class="p-3 text-left">Name</th>
            <th class="p-3 text-left">Email</th>
            <th class="p-3 text-left">Role</th>
            <th class="p-3 text-center">Status</th>
            <th class="p-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="p-3"><?= htmlspecialchars($user['name']) ?></td>
              <td class="p-3"><?= htmlspecialchars($user['email']) ?></td>
              <td class="p-3">
                <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                  <?= $roles[$user['role']] ?? $user['role'] ?>
                </span>
              </td>
              <td class="p-3 text-center">
                <span class="px-2 py-1 text-xs rounded-full <?= $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                  <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td class="p-3 text-right space-x-2">
                <a href="admin-dashboard.php?page=users&edit=<?= $user['id'] ?>" 
                   class="text-blue-600 hover:text-blue-800 hover:underline">Edit</a>
                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                  <a href="admin-dashboard.php?page=users&delete=<?= $user['id'] ?>" 
                     class="text-red-600 hover:text-red-800 hover:underline"
                     onclick="return confirm('Are you sure you want to delete this user?')">
                    Delete
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($users)): ?>
            <tr>
              <td colspan="5" class="p-4 text-center text-gray-500">No users found</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
