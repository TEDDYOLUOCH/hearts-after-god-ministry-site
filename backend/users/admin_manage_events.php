<?php
// Session is already started in admin-dashboard.php

// Include database configuration
require_once __DIR__ . '/../../config/db.php';

// Restrict access to admins only - check session
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    exit;
}

// Get database connection
$pdo = getDbConnection();

$message = '';
$messageType = 'success';

// Create uploads folder if not exists
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site/uploads/';
$uploadUrl = '/hearts-after-god-ministry-site/uploads/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $event_date = $_POST['event_date'] ?? '';
        $coordinator = $_POST['coordinator'] ?? '';
        $photo_path = null;

        // Handle image upload
        if (!empty($_FILES['photo']['name'])) {
            $photoTmp = $_FILES['photo']['tmp_name'];
            $photoName = basename($_FILES['photo']['name']);
            $uniqueName = time() . '_' . $photoName;
            $destination = $uploadDir . $uniqueName;

            if (move_uploaded_file($photoTmp, $uploadDir . $uniqueName)) {
                $photo_path = $uploadUrl . $uniqueName;
            }
        }

        if (!empty($_POST['delete_id'])) {
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$_POST['delete_id']]);
            $message = "Event deleted.";
        } elseif (!empty($_POST['edit_id'])) {
            // Update existing event
            $stmt = $pdo->prepare("UPDATE events SET title=?, description=?, event_date=?, coordinator=?, photo=? WHERE id=?");
            $stmt->execute([$title, $description, $event_date, $coordinator, $photo_path, $_POST['edit_id']]);
            $message = "Event updated.";
        } else {
            // Add new event
            $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, coordinator, photo) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $event_date, $coordinator, $photo_path]);
            $message = "Event added.";
        }
    } catch (PDOException $e) {
        $message = "DB Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch events
$events = $pdo->query("SELECT * FROM events ORDER BY event_date ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Events</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.min.js"></script>
</head>
<body class="bg-gray-100 font-sans">

  <div class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-purple-700">Events Manager</h1>
      <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php" class="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        <span>Back to Dashboard</span>
      </a>
    </div>

    <?php if (!empty($message)): ?>
      <div class="mb-4 p-4 <?= $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <!-- Event Form -->
    <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow mb-8">
      <input type="hidden" name="edit_id" id="edit_id">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input name="title" id="title" placeholder="Title" required class="border p-3 rounded" />
        <input type="date" name="event_date" id="event_date" required class="border p-3 rounded" />
        <input name="coordinator" id="coordinator" placeholder="Coordinator" class="border p-3 rounded" />
        <input type="file" name="photo" id="photo" accept="image/*" class="border p-3 rounded" />
        <textarea name="description" id="description" rows="3" placeholder="Description" class="border p-3 rounded md:col-span-2"></textarea>
      </div>
      <div class="mt-4 flex gap-3">
        <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition">Save Event</button>
        <button type="button" onclick="resetForm()" class="px-4 py-2 rounded border hidden" id="cancelBtn">Cancel Edit</button>
      </div>
    </form>

    <!-- Events Table -->
    <div class="bg-white p-6 rounded shadow">
      <h2 class="text-xl font-semibold mb-4">All Events</h2>
      <table class="w-full table-auto border-collapse">
        <thead>
          <tr class="bg-gray-200 text-left">
            <th class="p-2">Title</th>
            <th class="p-2">Date</th>
            <th class="p-2">Coordinator</th>
            <th class="p-2">Photo</th>
            <th class="p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($events as $event): ?>
            <tr class="border-t">
              <td class="p-2"><?= htmlspecialchars($event['title']) ?></td>
              <td class="p-2"><?= htmlspecialchars($event['event_date']) ?></td>
              <td class="p-2"><?= htmlspecialchars($event['coordinator']) ?></td>
              <td class="p-2">
                <?php if (!empty($event['photo'])): ?>
                  <img src="<?= $event['photo'] ?>" alt="Event Photo" class="w-16 h-16 object-cover rounded" />
                <?php else: ?>
                  <span class="text-gray-400 italic">No image</span>
                <?php endif; ?>
              </td>
              <td class="p-2 flex gap-2">
                <button onclick='editEvent(<?= json_encode($event) ?>)' class="text-blue-600 hover:underline">Edit</button>
                <form method="POST" onsubmit="return confirm('Delete this event?')">
                  <input type="hidden" name="delete_id" value="<?= $event['id'] ?>" />
                  <button type="submit" class="text-red-600 hover:underline">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function editEvent(event) {
      document.getElementById('edit_id').value = event.id;
      document.getElementById('title').value = event.title;
      document.getElementById('event_date').value = event.event_date;
      document.getElementById('coordinator').value = event.coordinator || '';
      document.getElementById('description').value = event.description || '';
      document.getElementById('cancelBtn').classList.remove('hidden');
    }

    function resetForm() {
      document.getElementById('edit_id').value = '';
      document.getElementById('title').value = '';
      document.getElementById('event_date').value = '';
      document.getElementById('coordinator').value = '';
      document.getElementById('description').value = '';
      document.getElementById('photo').value = '';
      document.getElementById('cancelBtn').classList.add('hidden');
    }

    lucide.createIcons();
  </script>
</body>
</html>
