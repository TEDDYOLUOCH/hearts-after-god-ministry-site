<div class="space-y-8">
    <!-- Dashboard Page Title -->
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Dashboard Overview</h2>
    <!-- Stats Cards -->
    <h3 class="text-xl font-bold text-gray-800 mb-4">Statistics</h3>
    <div class="grid gap-6" style="grid-template-columns: repeat(5, minmax(0, 1fr));">
        <!-- Manage Users -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Manage Users</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($realTimeStats['total_users']); ?></h3>
                </div>
                <div class="p-3 rounded-lg bg-rose-50">
                    <i data-lucide="user-cog" class="w-6 h-6 text-rose-600"></i>
                </div>
            </div>
            <div class="mt-4">
                <a href="?page=users" class="inline-flex items-center text-sm font-medium text-rose-600 hover:text-rose-700 hover:underline">
                    Manage Users
                    <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Active Programs -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Active Programs</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($realTimeStats['active_programs']); ?></h3>
                </div>
                <div class="p-3 rounded-lg bg-green-50">
                    <i data-lucide="layers" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-green-600 font-medium">+2 new this month</span>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Upcoming Events</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($realTimeStats['upcoming_events']); ?></h3>
                </div>
                <div class="p-3 rounded-lg bg-purple-50">
                    <i data-lucide="calendar" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-green-600 font-medium">+3 new events</span>
            </div>
        </div>

        <!-- Gallery Items -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Gallery Items</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($realTimeStats['gallery_items']); ?></h3>
                </div>
                <div class="p-3 rounded-lg bg-amber-50">
                    <i data-lucide="image" class="w-6 h-6 text-amber-600"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-green-600 font-medium">+12 new items</span>
            </div>
        </div>

        <!-- Ministry Leaders -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Ministry Leaders</p>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($realTimeStats['ministry_leaders']); ?></h3>
                </div>
                <div class="p-3 rounded-lg bg-green-50">
                    <i data-lucide="award" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
            <div class="mt-4">
                Leaders guiding the ministry
                <br>
                <a href="?page=leaders" class="inline-flex items-center text-sm font-medium text-green-600 hover:text-green-700 hover:underline">
                    Manage Leaders
                    <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h3 class="text-xl font-bold text-gray-800 mb-6">Quick Actions</h3>
    <div class="mt-8">
        <h3 class="text-xl font-bold text-gray-800 mb-6">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="?page=blog&action=create" class="group block">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-blue-200 transition-colors h-full">
                    <div class="flex flex-col items-center text-center">
                        <div class="p-3 rounded-full bg-blue-50 mb-4 group-hover:bg-blue-100 transition-colors">
                            <i data-lucide="edit-3" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <h4 class="font-medium text-gray-900 mb-1">New Blog Post</h4>
                        <p class="text-sm text-gray-500">Create and publish a new blog article</p>
                    </div>
                </div>
            </a>

            <a href="?page=events&action=create" class="group block">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-purple-200 transition-colors h-full">
                    <div class="flex flex-col items-center text-center">
                        <div class="p-3 rounded-full bg-purple-50 mb-4 group-hover:bg-purple-100 transition-colors">
                            <i data-lucide="calendar-plus" class="w-6 h-6 text-purple-600"></i>
                        </div>
                        <h4 class="font-medium text-gray-900 mb-1">Add New Event</h4>
                        <p class="text-sm text-gray-500">Schedule a new ministry event</p>
                    </div>
                </div>
            </a>

            <a href="?page=gallery&action=upload" class="group block">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-green-200 transition-colors h-full">
                    <div class="flex flex-col items-center text-center">
                        <div class="p-3 rounded-full bg-green-50 mb-4 group-hover:bg-green-100 transition-colors">
                            <i data-lucide="image-plus" class="w-6 h-6 text-green-600"></i>
                        </div>
                        <h4 class="font-medium text-gray-900 mb-1">Upload to Gallery</h4>
                        <p class="text-sm text-gray-500">Add new images to the gallery</p>
                    </div>
                </div>
            </a>

            <a href="?page=sermons&action=add" class="group block">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-amber-200 transition-colors h-full">
                    <div class="flex flex-col items-center text-center">
                        <div class="p-3 rounded-full bg-amber-50 mb-4 group-hover:bg-amber-100 transition-colors">
                            <i data-lucide="mic" class="w-6 h-6 text-amber-600"></i>
                        </div>
                        <h4 class="font-medium text-gray-900 mb-1">Add Sermon</h4>
                        <p class="text-sm text-gray-500">Upload a new sermon audio/video</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activities -->
    <h3 class="text-xl font-bold text-gray-800 mb-6">Recent Activities & Blog Posts</h3>
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800">Recent Activities</h3>
                <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-800">View All</a>
            </div>
            <div class="space-y-4">
                <?php if (!empty($recentActivities)): ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="flex items-start pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                            <div class="p-2 rounded-lg mr-4 <?php 
                                echo $activity['type'] === 'user' ? 'bg-blue-50 text-blue-600' : 
                                    ($activity['type'] === 'event' ? 'bg-purple-50 text-purple-600' : 'bg-green-50 text-green-600');
                            ?>">
                                <i data-lucide="<?php 
                                    echo $activity['type'] === 'user' ? 'user' : 
                                        ($activity['type'] === 'event' ? 'calendar' : 'users');
                                ?>" class="w-4 h-4"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($activity['message']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo $activity['time']; ?> ago</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-sm text-gray-500 py-4 text-center">No recent activities found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Blog Posts -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800">Recent Blog Posts</h3>
                <a href="?page=blog" class="text-sm font-medium text-blue-600 hover:text-blue-800">View All</a>
            </div>
            <div class="space-y-4">
                <?php
                try {
                    $stmt = $pdo->query("
                        SELECT p.*, u.name as author_name 
                        FROM blog_posts p 
                        LEFT JOIN users u ON p.author_id = u.id 
                        ORDER BY p.created_at DESC 
                        LIMIT 5
                    ")->fetchAll();

                    if (count($stmt) > 0) {
                        foreach ($stmt as $post) {
                            $excerpt = strlen($post['content']) > 100 ? substr($post['content'], 0, 100) . '...' : $post['content'];
                            echo "
                            <div class='pb-4 border-b border-gray-100 last:border-0 last:pb-0'>
                                <h4 class='font-medium text-gray-900'><a href='?page=blog&action=edit&id={$post['id']}' class='hover:text-blue-600'>{$post['title']}</a></h4>
                                <p class='text-sm text-gray-600 mt-1'>$excerpt</p>
                                <div class='flex items-center justify-between mt-2'>
                                    <span class='text-xs text-gray-500'>By {$post['author_name']}</span>
                                    <span class='text-xs text-gray-400'>{$post['created_at']}</span>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<p class='text-sm text-gray-500 py-4 text-center'>No blog posts found.</p>";
                    }
                } catch (PDOException $e) {
                    echo "<p class='text-sm text-red-500 py-4'>Error loading blog posts: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Recent Users Grid for Dashboard Overview -->
    <h3 class="text-xl font-bold text-gray-800 mb-6">Recent Users</h3>
    <?php
    require_once __DIR__ . '/../../config/db.php';
    $recentUsers = [];
    try {
        $stmt = $pdo->query("SELECT id, name, email, role, is_active FROM users ORDER BY id DESC LIMIT 5");
        $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $roleLabels = [
            'admin' => 'Admin',
            'ministry_leader' => 'Ministry Leader',
            'blogger' => 'Blogger',
            'media_team' => 'Media Team',
            'registered_member' => 'Member'
        ];
    } catch (Exception $e) {
        $recentUsers = [];
    }
    ?>
    <div class="mt-8">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-gray-800">Recent Users</h3>
        <a href="?page=users" class="text-sm font-medium text-blue-600 hover:text-blue-800">View All</a>
      </div>
      <div class="overflow-x-auto rounded-lg border border-gray-100 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <?php if (empty($recentUsers)): ?>
              <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-400">No users found.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentUsers as $user): ?>
                <tr>
                  <td class="px-6 py-4"><?php echo htmlspecialchars($user['name']); ?></td>
                  <td class="px-6 py-4"><?php echo htmlspecialchars($user['email']); ?></td>
                  <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                      <?php echo $roleLabels[$user['role']] ?? ucfirst($user['role']); ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <?php if ($user['is_active']): ?>
                      <span class="inline-block px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                    <?php else: ?>
                      <span class="inline-block px-2 py-1 text-xs rounded-full bg-gray-200 text-gray-600">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <a href="?page=users&edit=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-800 hover:underline text-sm mr-2">Edit</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
</div>
