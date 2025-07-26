<?php
session_start();
require_once '../../config/db.php'; // Adjust path if needed

// Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    exit;
}

// Handle form submission (Add or Delete)
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!empty($_POST['delete_id'])) {
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$_POST['delete_id']]);
            $message = "Event deleted successfully.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, coordinator) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                $_POST['event_date'] ?? '',
                $_POST['coordinator'] ?? ''
            ]);
            $message = "Event added successfully.";
        }
    } catch (PDOException $e) {
        $message = "An error occurred: " . $e->getMessage();
    }
}

// Fetch updated events list
$events = $pdo->query("SELECT * FROM events ORDER BY event_date ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Events</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="max-w-4xl mx-auto mt-10 p-8 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6 text-center">Manage Events</h1>

    <?php if (!empty($message)): ?>
      <div class="mb-4 p-4 bg-green-100 text-green-800 rounded"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-4">
      <input name="title" placeholder="Title" class="border p-2 rounded" required>
      <input name="description" placeholder="Description" class="border p-2 rounded">
      <input name="event_date" type="date" class="border p-2 rounded" required>
      <input name="coordinator" placeholder="Coordinator" class="border p-2 rounded">
      <button class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded font-semibold md:col-span-4">Add Event</button>
    </form>

    <div class="overflow-x-auto">
      <table class="w-full table-auto border border-gray-300">
        <thead>
          <tr class="bg-gray-200 text-left">
            <th class="p-2 border">Title</th>
            <th class="p-2 border">Description</th>
            <th class="p-2 border">Date</th>
            <th class="p-2 border">Coordinator</th>
            <th class="p-2 border">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($events as $e): ?>
          <tr class="hover:bg-gray-50">
            <td class="p-2 border"><?= htmlspecialchars($e['title']) ?></td>
            <td class="p-2 border"><?= htmlspecialchars($e['description']) ?></td>
            <td class="p-2 border"><?= htmlspecialchars($e['event_date']) ?></td>
            <td class="p-2 border"><?= htmlspecialchars($e['coordinator']) ?></td>
            <td class="p-2 border">
              <form method="POST" onsubmit="return confirm('Are you sure you want to delete this event?');" class="inline">
                <input type="hidden" name="delete_id" value="<?= $e['id'] ?>">
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-6 text-center">
      <a href="../../dashboard/admin-dashboard.php" class="text-blue-700 hover:underline">← Back to Dashboard</a>
    </div>
  </div>
</body>
</html>
