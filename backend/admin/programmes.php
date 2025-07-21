<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
$programmes = $pdo->query("SELECT * FROM programmes ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Programmes</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100">
  <div class="max-w-3xl mx-auto mt-10 p-8 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Programmes</h1>
    <form method="POST" action="programmes.php" class="mb-8 flex flex-col md:flex-row gap-4">
      <input name="title" placeholder="Title" class="border p-2 rounded flex-1" required>
      <input name="description" placeholder="Description" class="border p-2 rounded flex-1">
      <input name="coordinator" placeholder="Coordinator" class="border p-2 rounded flex-1">
      <button class="bg-blue-700 text-white px-4 py-2 rounded font-bold">Add Programme</button>
    </form>
    <table class="w-full border">
      <thead>
        <tr class="bg-gray-200">
          <th class="p-2">Title</th>
          <th class="p-2">Description</th>
          <th class="p-2">Coordinator</th>
          <th class="p-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($programmes as $p): ?>
        <tr>
          <td class="p-2"><?= htmlspecialchars($p['title']) ?></td>
          <td class="p-2"><?= htmlspecialchars($p['description']) ?></td>
          <td class="p-2"><?= htmlspecialchars($p['coordinator']) ?></td>
          <td class="p-2">
            <form method="POST" action="programmes.php" style="display:inline;">
              <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
              <button class="text-red-600" onclick="return confirm('Delete this programme?')">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <a href="dashboard.php" class="block mt-8 text-blue-700 underline">Back to Dashboard</a>
  </div>
</body>
</html>
<?php
// Handle add/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete_id'])) {
        $stmt = $pdo->prepare("DELETE FROM programmes WHERE id=?");
        $stmt->execute([$_POST['delete_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO programmes (title, description, coordinator) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST['title'] ?? '',
            $_POST['description'] ?? '',
            $_POST['coordinator'] ?? ''
        ]);
    }
    header('Location: programmes.php');
    exit;
}
?> 