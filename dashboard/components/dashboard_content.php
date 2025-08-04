<?php
// Dashboard Content Component
?>
<div class="space-y-8">
  <!-- Live Stats -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Total Users -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">Total Users</p>
          <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($realTimeStats['total_users']); ?></h3>
        </div>
        <div class="p-3 rounded-full bg-blue-50 text-blue-600">
          <i data-lucide="users" class="w-6 h-6"></i>
        </div>
      </div>
      <div class="mt-4 flex items-center text-sm text-green-600">
        <i data-lucide="trending-up" class="w-4 h-4 mr-1"></i>
        <span>12% from last month</span>
      </div>
    </div>

    <!-- Active Programs -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">Active Programs</p>
          <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($realTimeStats['active_programs']); ?></h3>
        </div>
        <div class="p-3 rounded-full bg-purple-50 text-purple-600">
          <i data-lucide="layers" class="w-6 h-6"></i>
        </div>
      </div>
      <div class="mt-4 flex items-center text-sm text-green-600">
        <i data-lucide="trending-up" class="w-4 h-4 mr-1"></i>
        <span>5 new this month</span>
      </div>
    </div>

    <!-- Upcoming Events -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">Upcoming Events</p>
          <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($realTimeStats['upcoming_events']); ?></h3>
        </div>
        <div class="p-3 rounded-full bg-green-50 text-green-600">
          <i data-lucide="calendar" class="w-6 h-6"></i>
        </div>
      </div>
      <div class="mt-4">
        <a href="#events" class="text-sm text-blue-600 hover:underline">View all events</a>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-500">Recent Activity</p>
          <h3 class="text-2xl font-bold text-gray-900"><?php echo count($recentActivities); ?>+</h3>
        </div>
        <div class="p-3 rounded-full bg-amber-50 text-amber-600">
          <i data-lucide="activity" class="w-6 h-6"></i>
        </div>
      </div>
      <div class="mt-4">
        <a href="#activity" class="text-sm text-blue-600 hover:underline">View activity log</a>
      </div>
    </div>
  </div>

  <!-- Recent Blog Posts -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-900">Recent Blog Posts</h3>
      <a href="?page=blog" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</a>
    </div>
    <div class="divide-y divide-gray-100">
      <?php 
      try {
        $stmt = $pdo->query("
          SELECT p.id, p.title, p.slug, p.created_at, p.status, u.name as author_name 
          FROM blog_posts p 
          LEFT JOIN users u ON p.author_id = u.id 
          ORDER BY p.created_at DESC 
          LIMIT 5
        ");
        $recentPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($recentPosts)): 
          foreach ($recentPosts as $post): 
            $statusClass = $post['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
      ?>
            <div class="p-4 hover:bg-gray-50 transition-colors">
              <div class="flex items-start">
                <div class="flex-shrink-0 mr-3">
                  <div class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">
                    <a href="/hearts-after-god-ministry-site/blog.php?post=<?php echo htmlspecialchars($post['slug']); ?>" 
                       target="_blank" 
                       class="hover:text-blue-600 hover:underline">
                      <?php echo htmlspecialchars($post['title']); ?>
                    </a>
                  </p>
                  <div class="flex items-center mt-1 text-xs text-gray-500">
                    <span class="mr-2">By <?php echo htmlspecialchars($post['author_name']); ?></span>
                    <span class="mx-2">•</span>
                    <span class="mr-2"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                    <span class="mx-2">•</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                      <?php echo ucfirst($post['status']); ?>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="p-8 text-center text-gray-500">
            <p>No blog posts found</p>
            <a href="?page=blog&action=new" class="mt-2 inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium">
              <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
              Create your first post
            </a>
          </div>
        <?php endif; ?>
      <?php } catch (PDOException $e) { ?>
        <div class="p-8 text-center text-red-500">
          <p>Error loading blog posts: <?php echo htmlspecialchars($e->getMessage()); ?></p>
        </div>
      <?php } ?>
    </div>
  </div>
</div>
