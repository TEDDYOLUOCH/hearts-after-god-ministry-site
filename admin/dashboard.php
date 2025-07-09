<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: index.php');
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <div class="flex items-center gap-6">
      <a href="dashboard.php" class="font-bold text-[#7C3AED] text-xl">Admin Dashboard</a>
      <a href="analytics.php" class="bg-[#FDBA17] text-[#2046B3] px-4 py-2 rounded font-bold shadow hover:bg-[#7C3AED] hover:text-white transition">Analytics</a>
    </div>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-4xl mx-auto mt-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <a href="users.php" class="bg-white rounded-xl shadow p-6 flex flex-col items-center hover:bg-[#F3E8FF] transition">
        <span class="text-3xl mb-2">👤</span>
        <span class="font-bold text-lg text-[#7C3AED]">User Management</span>
      </a>
      <!-- Add more admin panels here (mentors, modules, resources, etc.) -->
    </div>
  </main>
</body>
</html> 