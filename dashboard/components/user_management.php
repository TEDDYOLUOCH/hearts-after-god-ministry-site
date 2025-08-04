<?php
// User Management Component
?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
    <button class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
      <i data-lucide="user-plus" class="w-4 h-4"></i>
      Add New User
    </button>
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
              placeholder="Search users..."
              x-model="searchQuery"
              @input="filterUsers()"
            >
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
            <option>All Roles</option>
            <option>Admin</option>
            <option>Ministry Leader</option>
            <option>Blogger</option>
            <option>Media Team</option>
            <option>Member</option>
          </select>
          <button class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            <i data-lucide="filter" class="w-5 h-5 text-gray-500"></i>
          </button>
        </div>
      </div>
    </div>
    
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Active</th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php
          // Sample user data - replace with actual database query
          $users = [
            [
              'id' => 1,
              'name' => 'John Doe',
              'email' => 'john@example.com',
              'role' => 'Admin',
              'status' => 'active',
              'last_active' => '2 hours ago',
              'avatar' => 'https://randomuser.me/api/portraits/men/1.jpg'
            ],
            // Add more sample users as needed
          ];
          
          foreach ($users as $user): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <img class="h-10 w-10 rounded-full" src="<?php echo $user['avatar']; ?>" alt="">
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                  <?php echo htmlspecialchars($user['role']); ?>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                  <?php echo ucfirst($user['status']); ?>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <?php echo $user['last_active']; ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                <button class="text-red-600 hover:text-red-900">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
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
            Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">20</span> results
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
  Alpine.data('userManagement', () => ({
    searchQuery: '',
    users: [],
    filteredUsers: [],
    
    init() {
      // Initialize with sample data - replace with actual API call
      this.users = [
        {
          id: 1,
          name: 'John Doe',
          email: 'john@example.com',
          role: 'Admin',
          status: 'active',
          lastActive: '2 hours ago',
          avatar: 'https://randomuser.me/api/portraits/men/1.jpg'
        },
        // Add more sample users as needed
      ];
      this.filteredUsers = [...this.users];
    },
    
    filterUsers() {
      if (!this.searchQuery) {
        this.filteredUsers = [...this.users];
        return;
      }
      
      const query = this.searchQuery.toLowerCase();
      this.filteredUsers = this.users.filter(user => 
        user.name.toLowerCase().includes(query) || 
        user.email.toLowerCase().includes(query) ||
        user.role.toLowerCase().includes(query)
      );
    }
  }));
});
</script>
