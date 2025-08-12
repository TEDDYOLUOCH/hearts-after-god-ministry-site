<?php
// Blog Management Component
?>
<div class="space-y-6 p-6">
  <div class="flex justify-between items-center">
    <div>
      <h2 class="text-2xl font-bold text-gray-800">Blog Management</h2>
      <p class="text-gray-600 mt-1">Manage your blog posts, drafts and publications</p>
    </div>
    <button id="openCreateBlogModal" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all transform hover:scale-105 shadow-md">
      <i data-lucide="plus-circle" class="w-5 h-5"></i>
      Create New Post
    </button>
  </div>

  <!-- Stats Overview -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
      <h4 class="text-gray-500 text-sm">Total Posts</h4>
      <div class="flex items-center mt-2">
        <i data-lucide="file-text" class="w-5 h-5 text-blue-500"></i>
        <span class="text-2xl font-bold ml-2"><?= count($posts) ?></span>
      </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
      <h4 class="text-gray-500 text-sm">Published</h4>
      <div class="flex items-center mt-2">
        <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
        <span class="text-2xl font-bold ml-2"><?= count(array_filter($posts, fn($p) => $p['status'] === 'published')) ?></span>
      </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
      <h4 class="text-gray-500 text-sm">Drafts</h4>
      <div class="flex items-center mt-2">
        <i data-lucide="edit-3" class="w-5 h-5 text-yellow-500"></i>
        <span class="text-2xl font-bold ml-2"><?= count(array_filter($posts, fn($p) => $p['status'] === 'draft')) ?></span>
      </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
      <h4 class="text-gray-500 text-sm">This Month</h4>
      <div class="flex items-center mt-2">
        <i data-lucide="calendar" class="w-5 h-5 text-purple-500"></i>
        <span class="text-2xl font-bold ml-2"><?= count(array_filter($posts, fn($p) => strtotime($p['created_at']) > strtotime('-1 month'))) ?></span>
      </div>
    </div>
  </div>

  <!-- Search and Filter -->
  <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
    <div class="flex flex-wrap gap-4">
      <div class="flex-1">
        <input type="text" id="searchPosts" placeholder="Search posts..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <select id="statusFilter" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
        <option value="">All Status</option>
        <option value="published">Published</option>
        <option value="draft">Draft</option>
      </select>
      <select id="sortBy" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
        <option value="newest">Newest First</option>
        <option value="oldest">Oldest First</option>
        <option value="title">Title A-Z</option>
      </select>
    </div>
  </div>

  <!-- Posts Table -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php foreach ($posts as $post): ?>
        <tr class="hover:bg-gray-50 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center">
              <i data-lucide="file-text" class="w-5 h-5 text-gray-400 mr-3"></i>
              <div>
                <div class="font-medium text-gray-900"><?= htmlspecialchars($post['title']) ?></div>
                <div class="text-sm text-gray-500"><?= substr(htmlspecialchars($post['content']), 0, 50) ?>...</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4">
            <span class="px-3 py-1 rounded-full text-sm font-medium <?= $post['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
              <?= ucfirst(htmlspecialchars($post['status'])) ?>
            </span>
          </td>
          <td class="px-6 py-4 text-sm text-gray-500">
            <?= date('M j, Y', strtotime($post['created_at'])) ?>
          </td>
          <td class="px-6 py-4">
            <div class="flex items-center space-x-4">
              <button class="text-blue-600 hover:text-blue-800 transition-colors edit-blog-btn flex items-center" data-id="<?= $post['id'] ?>">
                <i data-lucide="edit" class="w-4 h-4 mr-1"></i> Edit
              </button>
              <button class="text-red-600 hover:text-red-800 transition-colors delete-blog-btn flex items-center" data-id="<?= $post['id'] ?>">
                <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?>
        <tr>
          <td colspan="4" class="px-6 py-8 text-center">
            <div class="flex flex-col items-center">
              <i data-lucide="file-question" class="w-12 h-12 text-gray-300 mb-2"></i>
              <p class="text-gray-500">No blog posts found</p>
              <button onclick="document.getElementById('openCreateBlogModal').click()" class="mt-2 text-blue-600 hover:underline">Create your first post</button>
            </div>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal for Create/Edit Blog Post -->
<div id="blogModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-white rounded-lg p-6 w-full max-w-lg relative">
    <button id="closeBlogModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
    <h3 id="blogModalTitle" class="text-lg font-bold mb-4">New Blog Post</h3>
    <form id="blogForm">
      <input type="hidden" name="id" id="blogId">
      <div class="mb-4">
        <label class="block mb-1 font-medium">Title</label>
        <input type="text" name="title" id="blogTitle" class="w-full border rounded px-3 py-2" required>
      </div>
      <div class="mb-4">
        <label class="block mb-1 font-medium">Content</label>
        <textarea name="content" id="blogContent" class="w-full border rounded px-3 py-2" required></textarea>
      </div>
      <div class="mb-4">
        <label class="block mb-1 font-medium">Status</label>
        <select name="status" id="blogStatus" class="w-full border rounded px-3 py-2">
          <option value="draft">Draft</option>
          <option value="published">Published</option>
        </select>
      </div>
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
    </form>
  </div>
</div>

<script>
// Open modal for new post
document.getElementById('openCreateBlogModal').onclick = function() {
  document.getElementById('blogModalTitle').textContent = 'New Blog Post';
  document.getElementById('blogForm').reset();
  document.getElementById('blogId').value = '';
  document.getElementById('blogModal').classList.remove('hidden');
};

// Close modal
document.getElementById('closeBlogModal').onclick = function() {
  document.getElementById('blogModal').classList.add('hidden');
};

// Edit post
document.querySelectorAll('.edit-blog-btn').forEach(btn => {
  btn.onclick = async function() {
    const id = this.dataset.id;
    const res = await fetch('/hearts-after-god-ministry-site/backend/blog/get_post.php?id=' + id);
    const post = await res.json();
    document.getElementById('blogModalTitle').textContent = 'Edit Blog Post';
    document.getElementById('blogId').value = post.id;
    document.getElementById('blogTitle').value = post.title;
    document.getElementById('blogContent').value = post.content;
    document.getElementById('blogStatus').value = post.status;
    document.getElementById('blogModal').classList.remove('hidden');
  };
});

// Submit form (create or update)
document.getElementById('blogForm').onsubmit = async function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  const res = await fetch('/hearts-after-god-ministry-site/backend/blog/save_post.php', {
    method: 'POST',
    body: formData,
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  });
  const result = await res.json();
  if (result.success) {
    document.getElementById('blogModal').classList.add('hidden');
    // Reload the blog section via AJAX
    if (window.$store && $store.app && $store.app.loadSection) {
      $store.app.loadSection('blog');
    } else {
      location.reload();
    }
  } else {
    alert(result.message || 'Failed to save post.');
  }
};

// Delete post
document.querySelectorAll('.delete-blog-btn').forEach(btn => {
  btn.onclick = async function() {
    if (!confirm('Delete this post?')) return;
    const id = this.dataset.id;
    const formData = new FormData();
    formData.append('id', id);

    const res = await fetch('/hearts-after-god-ministry-site/backend/blog/delete_post.php', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const result = await res.json();
    if (result.success) {
      if (window.$store && $store.app && $store.app.loadSection) {
        $store.app.loadSection('blog');
      } else {
        location.reload();
      }
    } else {
      alert(result.message || 'Failed to delete post.');
    }
  };
});

// Search and filter functionality
document.getElementById('searchPosts').addEventListener('input', filterPosts);
document.getElementById('statusFilter').addEventListener('change', filterPosts);
document.getElementById('sortBy').addEventListener('change', sortPosts);

function filterPosts() {
  const searchTerm = document.getElementById('searchPosts').value.toLowerCase();
  const statusFilter = document.getElementById('statusFilter').value;
  const rows = document.querySelectorAll('tbody tr');

  rows.forEach(row => {
    const title = row.querySelector('td:first-child').textContent.toLowerCase();
    const status = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
    
    const matchesSearch = title.includes(searchTerm);
    const matchesStatus = statusFilter === '' || status.includes(statusFilter);
    
    row.style.display = matchesSearch && matchesStatus ? '' : 'none';
  });
}

function sortPosts() {
  const sortBy = document.getElementById('sortBy').value;
  const tbody = document.querySelector('tbody');
  const rows = Array.from(tbody.querySelectorAll('tr'));

  rows.sort((a, b) => {
    const aVal = a.querySelector('td:first-child').textContent;
    const bVal = b.querySelector('td:first-child').textContent;
    
    switch(sortBy) {
      case 'newest':
        return new Date(b.querySelector('td:nth-child(3)').textContent) - 
               new Date(a.querySelector('td:nth-child(3)').textContent);
      case 'oldest':
        return new Date(a.querySelector('td:nth-child(3)').textContent) - 
               new Date(b.querySelector('td:nth-child(3)').textContent);
      case 'title':
        return aVal.localeCompare(bVal);
      default:
        return 0;
    }
  });

  rows.forEach(row => tbody.appendChild(row));
}
</script>
