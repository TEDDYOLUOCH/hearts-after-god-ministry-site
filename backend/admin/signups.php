<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
$signups = $pdo->query("SELECT * FROM discipleship_signups ORDER BY signup_date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Discipleship Signups</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100">
  <div class="max-w-3xl mx-auto mt-10 p-8 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Discipleship Class Signups</h1>
    <table class="w-full border">
      <thead>
        <tr class="bg-gray-200">
          <th class="p-2">Name</th>
          <th class="p-2">Phone</th>
          <th class="p-2">Email</th>
          <th class="p-2">Message</th>
          <th class="p-2">Signup Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($signups as $s): ?>
        <tr>
          <td class="p-2"><?= htmlspecialchars($s['name']) ?></td>
          <td class="p-2"><?= htmlspecialchars($s['phone']) ?></td>
          <td class="p-2"><?= htmlspecialchars($s['email']) ?></td>
          <td class="p-2"><?= htmlspecialchars($s['message']) ?></td>
          <td class="p-2"><?= htmlspecialchars($s['signup_date']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <a href="dashboard.php" class="block mt-8 text-blue-700 underline">Back to Dashboard</a>
  </div>
</body>
</html> 