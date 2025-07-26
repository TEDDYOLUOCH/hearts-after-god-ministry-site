<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'discipleship_leader') {
    header('Location: /hearts-after-god-ministry-site/frontend/login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Discipleship Leader Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-gradient-to-b from-purple-900 to-purple-600 text-white flex flex-col p-6 space-y-6">
      <div class="flex items-center space-x-3 mb-8">
        <span class="inline-block bg-white rounded-full p-2"><svg class="w-8 h-8 text-purple-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2zm0 0c-4 0-8 2-8 6v2h16v-2c0-4-4-6-8-6z"/></svg></span>
        <span class="text-2xl font-bold">Discipleship Leader</span>
      </div>
      <nav class="flex-1 space-y-2">
        <a href="/hearts-after-god-ministry-site/dashboard/discipleship-leader.php" class="block py-2 px-4 rounded bg-purple-800 font-semibold">Dashboard Home</a>
        <a href="#" class="block py-2 px-4 rounded hover:bg-purple-800">View Sign-ups</a>
        <a href="#" class="block py-2 px-4 rounded hover:bg-purple-800">Update Class Progress</a>
        <a href="#" class="block py-2 px-4 rounded hover:bg-purple-800">Send Reminders</a>
      </nav>
      <a href="/hearts-after-god-ministry-site/backend/users/logout.php" class="block mt-auto py-2 px-4 rounded bg-red-600 text-white text-center font-bold hover:bg-red-700">Logout</a>
    </aside>
    <!-- Main Content -->
    <main class="flex-1 p-8">
      <!-- User Info -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div class="flex items-center space-x-4">
          <div class="w-16 h-16 rounded-full bg-purple-200 flex items-center justify-center text-3xl font-bold text-purple-800">
            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?>
          </div>
          <div>
            <div class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
            <div class="text-sm text-gray-500">Role: Discipleship Leader</div>
            <div class="text-xs text-gray-400">Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
          </div>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-2">
          <a href="#" class="px-4 py-2 bg-purple-800 text-white rounded font-semibold hover:bg-purple-900">+ View Sign-ups</a>
          <a href="#" class="px-4 py-2 bg-green-600 text-white rounded font-semibold hover:bg-green-700">+ Update Progress</a>
        </div>
      </div>
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl text-purple-800 mb-2"><i class="fas fa-user-check"></i></div>
          <div class="text-2xl font-bold" id="stat-signups">--</div>
          <div class="text-gray-500">Sign-ups</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl text-green-600 mb-2"><i class="fas fa-tasks"></i></div>
          <div class="text-2xl font-bold" id="stat-progress">--</div>
          <div class="text-gray-500">Class Progress</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl text-yellow-500 mb-2"><i class="fas fa-bell"></i></div>
          <div class="text-2xl font-bold" id="stat-reminders">--</div>
          <div class="text-gray-500">Reminders Sent</div>
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
    document.getElementById('stat-signups').textContent = '--';
    document.getElementById('stat-progress').textContent = '--';
    document.getElementById('stat-reminders').textContent = '--';
  </script>
</body>
</html> 