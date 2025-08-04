<?php
// Session is already started in admin-dashboard.php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    exit;
}

require_once __DIR__ . '/../../backend/config/db.php';

$feedback = '';
$feedback_type = '';

// Handle add/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete_id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM ministry_leaders WHERE id=?");
            $stmt->execute([$_POST['delete_id']]);
            $feedback = 'Leader deleted successfully.';
            $feedback_type = 'success';
        } catch (Exception $e) {
            $feedback = 'Failed to delete leader.';
            $feedback_type = 'error';
        }
    } elseif (!empty($_POST['name']) && !empty($_POST['title'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO ministry_leaders (name, title, bio, image_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['title'],
                $_POST['bio'] ?? '',
                $_POST['image_url'] ?? ''
            ]);
            $feedback = 'Leader added successfully.';
            $feedback_type = 'success';
        } catch (Exception $e) {
            $feedback = 'Failed to add leader.';
            $feedback_type = 'error';
        }
    }
}

$leaders = $pdo->query("SELECT * FROM ministry_leaders ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Ministry Leaders</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function openModal(id) {
      document.getElementById('modal-' + id).classList.remove('hidden');
    }
    function closeModal(id) {
      document.getElementById('modal-' + id).classList.add('hidden');
    }
  </script>
</head>
<body class="bg-gray-100 min-h-screen px-4 py-8">

  <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow transition-all">
    <h1 class="text-3xl font-bold text-purple-700 mb-6 text-center">Manage Ministry Leaders</h1>

    <!-- Feedback -->
    <?php if ($feedback): ?>
      <div class="mb-4 p-4 rounded text-sm font-medium 
        <?= $feedback_type === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?>">
        <?= htmlspecialchars($feedback) ?>
      </div>
    <?php endif; ?>

    <!-- Add Leader Form -->
    <form method="POST" action="leaders.php" class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <input name="name" placeholder="Name" class="border p-2 rounded" required>
      <input name="title" placeholder="Title" class="border p-2 rounded" required>
      <input name="image_url" placeholder="Image URL (optional)" class="border p-2 rounded">
      <input name="bio" placeholder="Short Bio (optional)" class="border p-2 rounded">
      <div class="md:col-span-2">
        <button class="w-full bg-purple-700 text-white py-2 rounded hover:bg-purple-800 transition">Add Leader</button>
      </div>
    </form>

    <!-- Leaders Table -->
    <table class="w-full border rounded overflow-hidden">
      <thead class="bg-gray-200">
        <tr>
          <th class="p-2 text-left">Name</th>
          <th class="p-2 text-left">Title</th>
          <th class="p-2 text-left">Bio</th>
          <th class="p-2 text-left">Image</th>
          <th class="p-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($leaders as $l): ?>
        <tr class="border-t hover:bg-gray-50 transition">
          <td class="p-2"><?= htmlspecialchars($l['name']) ?></td>
          <td class="p-2"><?= htmlspecialchars($l['title']) ?></td>
          <td class="p-2 text-sm"><?= htmlspecialchars($l['bio']) ?></td>
          <td class="p-2">
            <?php if ($l['image_url']): ?>
              <img src="<?= htmlspecialchars($l['image_url']) ?>" class="w-12 h-12 rounded-full object-cover">
            <?php else: ?>
              <span class="text-gray-400 italic">No image</span>
            <?php endif; ?>
          </td>
          <td class="p-2">
            <button onclick="openModal(<?= $l['id'] ?>)" class="text-red-600 hover:underline">Delete</button>

            <!-- Modal -->
            <div id="modal-<?= $l['id'] ?>" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
              <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
                <h2 class="text-lg font-semibold mb-2 text-red-600">Confirm Deletion</h2>
                <p class="mb-4 text-sm">Are you sure you want to delete <strong><?= htmlspecialchars($l['name']) ?></strong>?</p>
                <form method="POST" action="leaders.php" class="flex gap-2">
                  <input type="hidden" name="delete_id" value="<?= $l['id'] ?>">
                  <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">Yes, Delete</button>
                  <button type="button" onclick="closeModal(<?= $l['id'] ?>)" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400 transition">Cancel</button>
                </form>
              </div>
            </div>
            <!-- End Modal -->
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

        <a href="../../dashboard/admin-dashboard.php" class="text-blue-700 hover:underline">← Back to Dashboard</a>
</body>
</html>
