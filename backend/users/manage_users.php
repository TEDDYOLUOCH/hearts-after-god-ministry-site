<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    exit;
}

// Handle CRUD Operations
$message = '';

// CREATE or UPDATE user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    if ($id) {
        // UPDATE
        $sql = "UPDATE users SET name=?, email=?, address=?, role=?, is_active=?";
        $params = [$name, $email, $address, $role, $is_active];
        if (!empty($_POST['password'])) {
            $sql .= ", password=?";
            $params[] = $password;
        }
        $sql .= " WHERE id=?";
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $message = "User updated successfully.";
    } else {
        // INSERT
        if (!empty($_POST['password'])) {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, address, password, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $address, $password, $role, $is_active]);
            $message = "User created successfully.";
        } else {
            $message = "Password is required for new users.";
        }
    }
}

// DELETE user
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $message = "User deleted successfully.";
}

// EDIT user (populate form)
$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <h1 class="text-3xl font-bold mb-4">Manage Users</h1>

  <?php if ($message): ?>
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4"> <?= $message ?> </div>
  <?php endif; ?>

  <form method="POST" class="bg-white shadow-md p-6 rounded mb-6">
    <input type="hidden" name="id" value="<?= $editUser['id'] ?? '' ?>">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <input type="text" name="name" placeholder="Name" required class="p-2 border rounded" value="<?= $editUser['name'] ?? '' ?>">
      <input type="email" name="email" placeholder="Email" required class="p-2 border rounded" value="<?= $editUser['email'] ?? '' ?>">
      <input type="text" name="address" placeholder="Address" class="p-2 border rounded" value="<?= $editUser['address'] ?? '' ?>">
      <input type="password" name="password" placeholder="<?= $editUser ? 'New Password (optional)' : 'Password' ?>" class="p-2 border rounded">
      <select name="role" required class="p-2 border rounded">
        <?php
        $roles = ['admin','media_team','ministry_leader','event_coordinator','discipleship_leader','registered_member'];
        foreach ($roles as $role):
        ?>
          <option value="<?= $role ?>" <?= ($editUser['role'] ?? '') === $role ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $role)) ?></option>
        <?php endforeach; ?>
      </select>
      <label class="flex items-center space-x-2">
        <input type="checkbox" name="is_active" value="1" <?= ($editUser['is_active'] ?? 0) ? 'checked' : '' ?>>
        <span>Active</span>
      </label>
    </div>
    <button class="mt-4 bg-purple-700 text-white px-4 py-2 rounded hover:bg-purple-800">Save User</button>
  </form>

  <table class="w-full bg-white shadow-md rounded">
    <thead class="bg-purple-700 text-white">
      <tr>
        <th class="p-2">ID</th>
        <th class="p-2">Name</th>
        <th class="p-2">Email</th>
        <th class="p-2">Role</th>
        <th class="p-2">Active</th>
        <th class="p-2">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user): ?>
        <tr class="border-b">
          <td class="p-2"><?= $user['id'] ?></td>
          <td class="p-2"><?= htmlspecialchars($user['name']) ?></td>
          <td class="p-2"><?= htmlspecialchars($user['email']) ?></td>
          <td class="p-2"><?= $user['role'] ?></td>
          <td class="p-2 text-center"><?= $user['is_active'] ? '✅' : '❌' ?></td>
          <td class="p-2 space-x-2">
            <a href="?edit=<?= $user['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
            <a href="?delete=<?= $user['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
