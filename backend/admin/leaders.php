<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
$leaders = $pdo->query("SELECT * FROM ministry_leaders ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Ministry Leaders</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100">
  <div class="max-w-3xl mx-auto mt-10 p-8 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Ministry Leaders</h1>
    <form method="POST" action="leaders.php" class="mb-8 flex flex-col md:flex-row gap-4">
      <input name="name" placeholder="Name" class="border p-2 rounded flex-1" required>
      <input name="title" placeholder="Title" class="border p-2 rounded flex-1" required>
      <input name="image_url" placeholder="Image URL" class="border p-2 rounded flex-1">
      <input name="bio" placeholder="Bio" class="border p-2 rounded flex-1">
      <button class="bg-purple-700 text-white px-4 py-2 rounded font-bold">Add Leader</button>
    </form>
    <table class="w-full border">
      <thead>
        <tr class="bg-gray-200">
          <th class="p-2">Name</th>
          <th class="p-2">Title</th>
          <th class="p-2">Bio</th>
          <th class="p-2">Image</th>
          <th class="p-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($leaders as $l): ?>
        <tr>
          <td class="p-2"><?= htmlspecialchars($l['name']) ?></td>
          <td class="p-2"><?= htmlspecialchars($l['title']) ?></td>
          <td class="p-2"><?= htmlspecialchars($l['bio']) ?></td>
          <td class="p-2"><img src="<?= htmlspecialchars($l['image_url']) ?>" class="w-12 h-12 rounded-full"></td>
          <td class="p-2">
            <form method="POST" action="leaders.php" style="display:inline;">
              <input type="hidden" name="delete_id" value="<?= $l['id'] ?>">
              <button class="text-red-600" onclick="return confirm('Delete this leader?')">Delete</button>
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
        $stmt = $pdo->prepare("DELETE FROM ministry_leaders WHERE id=?");
        $stmt->execute([$_POST['delete_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO ministry_leaders (name, title, bio, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'] ?? '',
            $_POST['title'] ?? '',
            $_POST['bio'] ?? '',
            $_POST['image_url'] ?? ''
        ]);
    }
    header('Location: leaders.php');
    exit;
}
?> 