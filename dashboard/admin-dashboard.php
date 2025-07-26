<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /hearts-after-god-ministry-site/backend/admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-gradient-to-b from-purple-800 to-purple-600 text-white flex flex-col p-6 space-y-6">
      <div class="flex items-center space-x-3 mb-8">
        <span class="inline-block bg-white rounded-full p-2"><svg class="w-8 h-8 text-purple-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2zm0 0c-4 0-8 2-8 6v2h16v-2c0-4-4-6-8-6z"/></svg></span>
        <span class="text-2xl font-bold">Admin Panel</span>
      </div>
      <nav class="flex-1 space-y-2">
        <a href="/hearts-after-god-ministry-site/dashboard/admin-dashboard.php" class="block py-2 px-4 rounded bg-purple-700 font-semibold">Dashboard Home</a>
        <a href="/hearts-after-god-ministry-site/backend/users/leaders.php" class="block py-2 px-4 rounded hover:bg-purple-700">Manage Ministry Leaders</a>
        <a href="/hearts-after-god-ministry-site/backend/users/programmes.php" class="block py-2 px-4 rounded hover:bg-purple-700">Manage Programmes</a>
        <a href="/hearts-after-god-ministry-site/backend/users/Manage_Users.php" class="block py-2 px-4 rounded hover:bg-purple-700">Manage Users</a>
        
        <a href="/hearts-after-god-ministry-site/backend/users/events.php" class="block py-2 px-4 rounded hover:bg-purple-700">Manage Events</a>
        <a href="/hearts-after-god-ministry-site/backend/users/signups.php" class="block py-2 px-4 rounded hover:bg-purple-700">View Discipleship Signups</a>
      </nav>
      <a href="/hearts-after-god-ministry-site/backend/users/logout.php" class="block mt-auto py-2 px-4 rounded bg-red-600 text-white text-center font-bold hover:bg-red-700">Logout</a>
    </aside>
    <!-- Main Content -->
    <main class="flex-1 p-8">
      <!-- User Info -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div class="flex items-center space-x-4">
          <div class="w-16 h-16 rounded-full bg-purple-200 flex items-center justify-center text-3xl font-bold text-purple-700">
            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?>
          </div>
          <div>
            <div class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
            <div class="text-sm text-gray-500">Role: Admin</div>
            <div class="text-xs text-gray-400">Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
          </div>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-2">
          <a href="/hearts-after-god-ministry-site/backend/users/leaders.php" class="px-4 py-2 bg-purple-700 text-white rounded font-semibold hover:bg-purple-800">+ Add Leader</a>
          <a href="/hearts-after-god-ministry-site/backend/users/events.php" class="px-4 py-2 bg-yellow-500 text-white rounded font-semibold hover:bg-yellow-600">+ Add Event</a>
        </div>
      </div>
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl text-purple-700 mb-2"><i class="fas fa-users"></i></div>
          <div class="text-2xl font-bold" id="stat-users">--</div>
          <div class="text-gray-500">Total Users</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl text-yellow-500 mb-2"><i class="fas fa-calendar-alt"></i></div>
          <div class="text-2xl font-bold" id="stat-events">--</div>
          <div class="text-gray-500">Upcoming Events</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl text-green-600 mb-2"><i class="fas fa-user-check"></i></div>
          <div class="text-2xl font-bold" id="stat-signups">--</div>
          <div class="text-gray-500">Discipleship Signups</div>
        </div>
      </div>
      <!-- Recent Activity (placeholder) -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="text-lg font-bold mb-4">Recent Activity</div>
        <ul class="divide-y divide-gray-200" id="recent-activity">
          <li class="py-2 text-gray-600">No recent activity yet.</li>
        </ul>
      </div>
    </main>
  </div>
  <!-- Font Awesome for icons -->
  <script src="https://kit.fontawesome.com/4b2b1b6a0a.js" crossorigin="anonymous"></script>
  <script>
    // Example: Fetch stats from backend (replace with real endpoints)
    fetch('/hearts-after-god-ministry-site/backend/actions/leaders.php')
      .then(res => res.json())
      .then(data => {
        document.getElementById('stat-users').textContent = data.length;
      });
    fetch('/hearts-after-god-ministry-site/backend/actions/events.php')
      .then(res => res.json())
      .then(data => {
        document.getElementById('stat-events').textContent = data.length;
      });
    fetch('/hearts-after-god-ministry-site/backend/actions/signups.php')
      .then(res => res.json())
      .then(data => {
        document.getElementById('stat-signups').textContent = data.length;
      });
  </script>
</body>
</html> 