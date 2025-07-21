<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100">
  <div class="max-w-2xl mx-auto mt-16 p-8 bg-white rounded shadow">
    <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>
    <div class="space-y-4">
      <a href="leaders.php" class="block px-6 py-3 bg-purple-700 text-white rounded font-bold hover:bg-purple-800">Manage Ministry Leaders</a>
      <a href="programmes.php" class="block px-6 py-3 bg-blue-700 text-white rounded font-bold hover:bg-blue-800">Manage Programmes</a>
      <a href="events.php" class="block px-6 py-3 bg-yellow-600 text-white rounded font-bold hover:bg-yellow-700">Manage Events</a>
      <a href="signups.php" class="block px-6 py-3 bg-green-700 text-white rounded font-bold hover:bg-green-800">View Discipleship Signups</a>
      <a href="logout.php" class="block px-6 py-3 bg-red-600 text-white rounded font-bold hover:bg-red-700">Logout</a>
    </div>
  </div>
</body>
</html> 