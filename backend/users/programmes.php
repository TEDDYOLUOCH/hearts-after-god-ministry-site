<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    exit;
}

require_once '../config/db.php';

$feedback = '';
$feedback_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete_id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM programmes WHERE id = ?");
            $stmt->execute([$_POST['delete_id']]);
            $feedback = "Programme deleted successfully.";
            $feedback_type = "success";
        } catch (Exception $e) {
            $feedback = "Failed to delete programme.";
            $feedback_type = "error";
        }
    } elseif (!empty($_POST['title'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO programmes (title, description, coordinator) VALUES (?, ?, ?)");
            $stmt->execute([
                $_POST['title'],
                $_POST['description'] ?? '',
                $_POST['coordinator'] ?? ''
            ]);
            $feedback = "Programme added successfully.";
            $feedback_type = "success";
        } catch (Exception $e) {
            $feedback = "Failed to add programme.";
            $feedback_type = "error";
        }
    }
}

$programmes = $pdo->query("SELECT * FROM programmes ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Programmes</title>
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

    <h1 class="text-3xl font-bold text-blue-700 mb-6 text-center">Manage Programmes</h1>

    <!-- Feedback message -->
    <?php if ($feedback): ?>
      <div class="mb-4 p-4 rounded text-sm font-medium 
        <?= $feedback_type === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?>">
        <?= htmlspecialchars($feedback) ?>
      </div>
    <?php endif; ?>

    <!-- Add programme form -->
    <form method="POST" action="programmes.php" class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <input name="title" placeholder="Title" class="border p-2 rounded" required>
      <input name="description" placeholder="Description" class="border p-2 rounded">
      <input name="coordinator" placeholder="Coordinator" class="border p-2 rounded">
      <div class="md:col-span-2">
        <button class="w-full bg-blue-700 text-white py-2 rounded hover:bg-blue-800 transition">Add Programme</button>
      </div>
    </form>

    <!-- Programmes table -->
    <table class="w-full border rounded overflow-hidden">
      <thead class="bg-gray-200">
        <tr>
          <th class="p-2 text-left">Title</th>
          <th class="p-2 text-left">Description</th>
          <th class="p-2 text-left">Coordinator</th>
          <th class="p-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($programmes as $p): ?>
        <tr class="border-t hover:bg-gray-50 transition">
          <td class="p-2"><?= htmlspecialchars($p['title']) ?></td>
          <td class="p-2 text-sm"><?= htmlspecialchars($p['description']) ?></td>
          <td class="p-2"><?= htmlspecialchars($p['coordinator']) ?></td>
          <td class="p-2">
            <button onclick="openModal(<?= $p['id'] ?>)" class="text-red-600 hover:underline">Delete</button>

            <!-- Modal -->
            <div id="modal-<?= $p['id'] ?>" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
              <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
                <h2 class="text-lg font-semibold mb-2 text-red-600">Confirm Deletion</h2>
                <p class="mb-4 text-sm">Are you sure you want to delete <strong><?= htmlspecialchars($p['title']) ?></strong>?</p>
                <form method="POST" action="programmes.php" class="flex gap-2">
                  <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                  <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">Yes, Delete</button>
                  <button type="button" onclick="closeModal(<?= $p['id'] ?>)" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400 transition">Cancel</button>
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
  </div>
</body>
</html>
