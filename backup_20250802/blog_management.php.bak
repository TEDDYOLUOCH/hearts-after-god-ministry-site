<?php
// Blog Management Component
?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-bold text-gray-800">Blog Management</h2>
    <a href="/hearts-after-god-ministry-site/backend/blog/create_post.php" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>
      New Blog Post
    </a>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1 max-w-md">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
            </div>
            <input 
              type="text" 
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
              placeholder="Search blog posts..."
              x-model="searchQuery"
              @input="filterPosts()"
            >
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
            <option>All Categories</option>
            <option>Devotionals</option>
            <option>Teachings</option>
            <option>Testimonies</option>
            <option>Announcements</option>
          </select>
          <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
            <option>All Status</option>
            <option>Published</option>
            <option>Draft</option>
            <option>Scheduled</option>
          </select>
        </div>
      </div>
    </div>
    
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categories</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php
          try {
            // Fetch blog posts from the database
            $stmt = $pdo->query("
              SELECT p.*, u.name as author_name 
              FROM blog_posts p 
              LEFT JOIN users u ON p.author_id = u.id 
              ORDER BY p.created_at DESC
              LIMIT 10
            ")->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($stmt) > 0) {
              foreach ($stmt as $post) {
                $statusClass = [
                  'published' => 'bg-green-100 text-green-800',
                  'draft' => 'bg-yellow-100 text-yellow-800',
                  'scheduled' => 'bg-blue-100 text-blue-800',
                  'archived' => 'bg-gray-100 text-gray-800'
                ][$post['status']] ?? 'bg-gray-100 text-gray-800';
                
                echo "
                <tr class='hover:bg-gray-50'>
                  <td class='px-6 py-4 whitespace-nowrap'>
                    <div class='text-sm font-medium text-gray-900'>{$post['title']}</div>
                  </td>
                  <td class='px-6 py-4 whitespace-nowrap'>
                    <div class='text-sm text-gray-900'>{$post['author_name']}</div>
                  </td>
                  <td class='px-6 py-4 whitespace-nowrap'>
                    <div class='text-sm text-gray-500'>{$post['categories']}</div>
                  </td>
                  <td class='px-6 py-4 whitespace-nowrap'>
                    <span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full {$statusClass}'>
                      " . ucfirst($post['status']) . "
                    </span>
                  </td>
                  <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>
                    " . date('M d, Y', strtotime($post['created_at'])) . "
                  </td>
                  <td class='px-6 py-4 whitespace-nowrap text-right text-sm font-medium'>
                    <a href='/hearts-after-god-ministry-site/backend/blog/edit_post.php?id={$post['id']}' class='text-blue-600 hover:text-blue-900 mr-3'>Edit</a>
                    <a href='#' class='text-red-600 hover:text-red-900' onclick='return confirm(\"Are you sure you want to delete this post?\")'>Delete</a>
                  </td>
                </tr>";
              }
            } else {
              echo "
              <tr>
                <td colspan='6' class='px-6 py-8 text-center text-gray-500'>
                  No blog posts found. Create your first post to get started.
                </td>
              </tr>";
            }
          } catch (PDOException $e) {
            echo "
            <tr>
              <td colspan='6' class='px-6 py-8 text-center text-red-500'>
                Error loading blog posts: " . htmlspecialchars($e->getMessage()) . "
              </td>
            </tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <div class="bg-white px-6 py-3 flex items-center justify-between border-t border-gray-200">
      <div class="flex-1 flex justify-between sm:hidden">
        <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
          Previous
        </a>
        <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
          Next
        </a>
      </div>
      <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
          <p class="text-sm text-gray-700">
            Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">25</span> results
          </p>
        </div>
        <div>
          <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
              <span class="sr-only">Previous</span>
              <i data-lucide="chevron-left" class="h-5 w-5"></i>
            </a>
            <a href="#" aria-current="page" class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
              1
            </a>
            <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
              2
            </a>
            <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
              3
            </a>
            <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
              <span class="sr-only">Next</span>
              <i data-lucide="chevron-right" class="h-5 w-5"></i>
            </a>
          </nav>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('blogManagement', () => ({
    searchQuery: '',
    posts: [],
    filteredPosts: [],
    
    init() {
      // This would be replaced with an actual API call
      this.posts = [
        {
          id: 1,
          title: 'The Power of Prayer',
          author: 'John Doe',
          categories: 'Devotionals',
          status: 'published',
          date: '2023-06-15'
        },
        // Add more sample posts as needed
      ];
      this.filteredPosts = [...this.posts];
    },
    
    filterPosts() {
      if (!this.searchQuery) {
        this.filteredPosts = [...this.posts];
        return;
      }
      
      const query = this.searchQuery.toLowerCase();
      this.filteredPosts = this.posts.filter(post => 
        post.title.toLowerCase().includes(query) || 
        post.author.toLowerCase().includes(query) ||
        post.categories.toLowerCase().includes(query)
      );
    },
    
    getStatusClass(status) {
      return {
        'published': 'bg-green-100 text-green-800',
        'draft': 'bg-yellow-100 text-yellow-800',
        'scheduled': 'bg-blue-100 text-blue-800',
        'archived': 'bg-gray-100 text-gray-800'
      }[status] || 'bg-gray-100 text-gray-800';
    }
  }));
});
</script>
