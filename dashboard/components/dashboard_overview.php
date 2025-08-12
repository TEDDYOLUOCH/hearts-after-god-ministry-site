<?php
<div class="space-y-6 p-6">
  <!-- Welcome Section -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-800">Welcome back, <?= htmlspecialchars($user['name']) ?></h2>
        <p class="text-gray-600 mt-1">Here's what's happening with your ministry today.</p>
      </div>
      <div class="text-right">
        <p class="text-sm text-gray-500"><?= date('l, F j, Y') ?></p>
        <p class="text-xs text-gray-400">Last login: <?= date('M j, Y H:i', strtotime($user['last_login'])) ?></p>
      </div>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-sm p-6 text-white">
      <div class="flex justify-between items-start">
        <div>
          <p class="text-blue-100">Total Views</p>
          <h3 class="text-3xl font-bold mt-2"><?= number_format($stats['total_views']) ?></h3>
        </div>
        <i data-lucide="eye" class="w-8 h-8 text-blue-100"></i>
      </div>
      <div class="mt-4 text-sm text-blue-100">
        <span class="<?= $stats['views_trend'] >= 0 ? 'text-green-300' : 'text-red-300' ?>">
          <i data-lucide="<?= $stats['views_trend'] >= 0 ? 'trend-up' : 'trend-down' ?>" class="w-4 h-4 inline"></i>
          <?= abs($stats['views_trend']) ?>%
        </span>
        vs last month
      </div>
    </div>

    <!-- Add more stat cards here -->
  </div>

  <!-- Recent Activity -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h3>
    <div class="space-y-4">
      <?php foreach ($recentActivities as $activity): ?>
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-100">
            <i data-lucide="<?= $activity['icon'] ?>" class="w-4 h-4 text-blue-600"></i>
          </span>
        </div>
        <div class="ml-4">
          <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($activity['description']) ?></p>
          <p class="text-sm text-gray-500"><?= timeAgo($activity['created_at']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
