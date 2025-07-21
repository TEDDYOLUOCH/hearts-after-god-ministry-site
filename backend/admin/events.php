<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
$events = $pdo->query("SELECT * FROM events ORDER BY event_date ASC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Events</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100">
  <div class="max-w-3xl mx-auto mt-10 p-8 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Events</h1>
    <form method="POST" action="events.php" class="mb-8 flex flex-col md:flex-row gap-4">
      <input name="title" placeholder="Title" class="border p-2 rounded flex-1" required>
      <input name="description" placeholder="Description" class="border p-2 rounded flex-1">
      <input name="event_date" type="date" class="border p-2 rounded flex-1" required>
      <input name="coordinator" placeholder="Coordinator" class="border p-2 rounded flex-1">
      <button class="bg-yellow-600 text-white px-4 py-2 rounded font-bold">Add Event</button>
    </form>
    <table class="w-full border">
      <thead>
        <tr class="bg-gray-200">
          <th class="p-2">Title</th>
          <th class="p-2">Description</th>
          <th class="p-2">Date</th>
          <th class="p-2">Coordinator</th>
          <th class="p-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($events as $e): ?>
        <tr>
          <td class="p-2"><?= htmlspecialchars($e['title']) ?></td>
          <td class="p-2"><?= htmlspecialchars($e['description']) ?></td>
          <td class="p-2"><?= htmlspecialchars($e['event_date']) ?></td>
          <td class="p-2"><?= htmlspecialchars($e['coordinator']) ?></td>
          <td class="p-2">
            <form method="POST" action="events.php" style="display:inline;">
              <input type="hidden" name="delete_id" value="<?= $e['id'] ?>">
              <button class="text-red-600" onclick="return confirm('Delete this event?')">Delete</button>
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
        $stmt = $pdo->prepare("DELETE FROM events WHERE id=?");
        $stmt->execute([$_POST['delete_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, coordinator) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['title'] ?? '',
            $_POST['description'] ?? '',
            $_POST['event_date'] ?? '',
            $_POST['coordinator'] ?? ''
        ]);
    }
    header('Location: events.php');
    exit;
}
?> 