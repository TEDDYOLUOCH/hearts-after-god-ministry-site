<?php
// filepath: c:\xampp\htdocs\hearts-after-god-ministry-site\dashboard\components\users_management.php
?>
<div class="space-y-6">
  <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
  <a href="/hearts-after-god-ministry-site/backend/users/create_user.php" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
    <i data-lucide="plus" class="w-4 h-4"></i>
    New User
  </a>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="min-w-full">
      <thead>
        <tr>
          <th class="px-4 py-2">Username</th>
          <th class="px-4 py-2">Email</th>
          <th class="px-4 py-2">Role</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
          <td class="px-4 py-2"><?= htmlspecialchars($user['username']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($user['email']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($user['role']) ?></td>
          <td class="px-4 py-2">
            <a href="/hearts-after-god-ministry-site/backend/users/edit_user.php?id=<?= $user['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
            |
            <a href="/hearts-after-god-ministry-site/backend/users/delete_user.php?id=<?= $user['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Delete this user?')">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
        <tr>
          <td colspan="4" class="text-center text-gray-400 py-4">No users found.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
if (!isset($users) || !is_array($users)) $users = [];
?>