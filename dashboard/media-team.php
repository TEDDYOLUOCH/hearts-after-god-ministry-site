<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'media_team') {
    header('Location: /hearts-after-god-ministry-site/frontend/login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Media Team Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-gradient-to-b from-blue-800 to-blue-600 text-white flex flex-col p-6 space-y-6">
      <div class="flex items-center space-x-3 mb-8">
        <span class="inline-block bg-white rounded-full p-2"><svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A2 2 0 0020 6.382V5a2 2 0 00-2-2H6a2 2 0 00-2 2v1.382a2 2 0 00.447 1.342L9 10m6 0v10a2 2 0 01-2 2H9a2 2 0 01-2-2V10m6 0H9"/></svg></span>
        <span class="text-2xl font-bold">Media Team</span>
      </div>
      <nav class="flex-1 space-y-2">
        <a href="/hearts-after-god-ministry-site/dashboard/media-team.php" class="block py-2 px-4 rounded bg-blue-700 font-semibold">Dashboard Home</a>
        <a href="#" class="block py-2 px-4 rounded hover:bg-blue-700">Upload Media</a>
        <a href="#" class="block py-2 px-4 rounded hover:bg-blue-700">Manage Blog</a>
        <a href="#" class="block py-2 px-4 rounded hover:bg-blue-700">Share Event Updates</a>
      </nav>
      <a href="/hearts-after-god-ministry-site/backend/users/logout.php" class="block mt-auto py-2 px-4 rounded bg-red-600 text-white text-center font-bold hover:bg-red-700">Logout</a>
    </aside>
    <!-- Main Content -->
    <main class="flex-1 p-8">
      <!-- User Info -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div class="flex items-center space-x-4">
          <div class="w-16 h-16 rounded-full bg-blue-200 flex items-center justify-center text-3xl font-bold text-blue-700">
            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?>
          </div>
          <div>
            <div class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
            <div class="text-sm text-gray-500">Role: Media Team</div>
            <div class="text-xs text-gray-400">Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
          </div>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-2">
          <a href="#" class="px-4 py-2 bg-blue-700 text-white rounded font-semibold hover:bg-blue-800">+ Upload Media</a>
          <a href="#" class="px-4 py-2 bg-green-600 text-white rounded font-semibold hover:bg-green-700">+ New Blog Post</a>
        </div>
      </div>
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl text-blue-700 mb-2"><i class="fas fa-photo-video"></i></div>
          <div class="text-2xl font-bold" id="stat-media">--</div>
          <div class="text-gray-500">Media Items</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl text-green-600 mb-2"><i class="fas fa-blog"></i></div>
          <div class="text-2xl font-bold" id="stat-blogs">--</div>
          <div class="text-gray-500">Blog Posts</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl text-yellow-500 mb-2"><i class="fas fa-calendar-alt"></i></div>
          <div class="text-2xl font-bold" id="stat-events">--</div>
          <div class="text-gray-500">Event Updates</div>
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
    // These would be replaced with real endpoints for media, blogs, and events
    document.getElementById('stat-media').textContent = '--';
    document.getElementById('stat-blogs').textContent = '--';
    document.getElementById('stat-events').textContent = '--';
  </script>
</body>
</html> 