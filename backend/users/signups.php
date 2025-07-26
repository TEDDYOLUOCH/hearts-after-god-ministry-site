<?php
session_start();

// ✅ Ensure the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// ✅ Connect to the database
require_once '../config/db.php';

// ✅ Fetch signups using PDO
try {
    $stmt = $pdo->query("SELECT * FROM discipleship_signups ORDER BY signup_date DESC");
    $signups = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching signups: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Discipleship Signups</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="max-w-4xl mx-auto mt-10 p-8 bg-white rounded shadow">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Discipleship Class Signups</h1>

    <?php if (count($signups) > 0): ?>
      <div class="overflow-x-auto">
        <table class="w-full border border-gray-300">
          <thead>
            <tr class="bg-blue-100 text-left">
              <th class="p-3 border-b">Name</th>
              <th class="p-3 border-b">Phone</th>
              <th class="p-3 border-b">Email</th>
              <th class="p-3 border-b">Message</th>
              <th class="p-3 border-b">Signup Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($signups as $s): ?>
            <tr class="hover:bg-gray-50">
              <td class="p-3 border-b"><?= htmlspecialchars($s['name']) ?></td>
              <td class="p-3 border-b"><?= htmlspecialchars($s['phone']) ?></td>
              <td class="p-3 border-b"><?= htmlspecialchars($s['email']) ?></td>
              <td class="p-3 border-b"><?= htmlspecialchars($s['message']) ?></td>
              <td class="p-3 border-b"><?= htmlspecialchars($s['signup_date']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-gray-600">No signups found.</p>
    <?php endif; ?>

     <a href="../../dashboard/admin-dashboard.php" class="text-blue-700 hover:underline">← Back to Dashboard</a>
  </div>
</body>
</html>
