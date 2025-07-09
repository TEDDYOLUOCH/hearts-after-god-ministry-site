<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: index.php');
  exit;
}
$host = 'localhost';
$db   = 'hearts_after_god';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
$users = [];
$result = $conn->query("SELECT id, name, email FROM users");
while ($row = $result->fetch_assoc()) {
  $users[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
  <title>User Management</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
  <style>
    #users-table thead th { position: sticky; top: 0; background: #fff; z-index: 2; }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="dashboard.php" class="font-bold text-[#7C3AED] text-xl">← Admin Dashboard</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-5xl mx-auto mt-4 bg-white p-2 sm:p-4 md:p-8 rounded-xl shadow">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2 sm:gap-4">
      <h2 class="text-xl sm:text-2xl font-bold text-[#7C3AED]">Users</h2>
      <input type="text" id="user-search" placeholder="Search by name or email..." class="border rounded px-3 py-2 sm:px-4 sm:py-2 w-full md:w-72 focus:ring-2 focus:ring-[#7C3AED] text-sm sm:text-base" />
    </div>
    <form id="bulk-users-form" method="POST">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-2 gap-2 sm:gap-4">
        <div class="flex gap-2 items-center">
          <select id="bulk-action" name="bulk_action" class="border rounded px-2 py-2 sm:px-3 sm:py-2 text-sm sm:text-base">
            <option value="">Bulk Actions</option>
            <option value="assign_mentor">Assign Mentor</option>
            <option value="delete">Delete Users</option>
          </select>
          <button type="submit" class="bg-[#7C3AED] text-white px-3 py-2 sm:px-4 sm:py-2 rounded font-bold hover:bg-[#FDBA17] hover:text-[#2046B3] transition text-sm sm:text-base">Apply</button>
        </div>
        <div id="mentor-select-wrapper" class="hidden">
          <select name="mentor_id" class="border rounded px-2 py-2 sm:px-3 sm:py-2 text-sm sm:text-base">
            <option value="">Select Mentor</option>
            <?php $mentors = $conn->query("SELECT id, name FROM mentors ORDER BY name");
            while($m = $mentors->fetch_assoc()): ?>
              <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <div class="overflow-x-auto rounded-lg">
        <table id="users-table" class="min-w-full border-collapse text-xs sm:text-sm">
          <thead>
            <tr>
              <th class="p-2"><input type="checkbox" id="select-all-users" class="w-5 h-5 sm:w-6 sm:h-6" /></th>
              <th class="p-3 text-left">ID</th>
              <th class="p-3 text-left">Name</th>
              <th class="p-3 text-left">Email</th>
              <th class="p-3 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr class="border-t">
              <td class="p-2"><input type="checkbox" name="user_ids[]" value="<?= $u['id'] ?>" class="w-5 h-5 sm:w-6 sm:h-6" /></td>
              <td class="p-3"><?php echo $u['id']; ?></td>
              <td class="p-3"><?php echo htmlspecialchars($u['name']); ?></td>
              <td class="p-3"><?php echo htmlspecialchars($u['email']); ?></td>
              <td class="p-3">
                <a href="user-detail.php?id=<?php echo $u['id']; ?>" class="text-[#2046B3] underline text-xs sm:text-sm">View</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </form>
    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden" tabindex="-1">
      <div class="bg-white rounded-xl p-4 sm:p-8 shadow-xl max-w-xs sm:max-w-sm w-full text-center relative" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <button id="close-modal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 focus:outline-none" aria-label="Close">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <h3 id="modal-title" class="text-lg sm:text-xl font-bold mb-2 sm:mb-4">Confirm Bulk Delete</h3>
        <p class="mb-4 sm:mb-6 text-sm sm:text-base">Are you sure you want to delete the selected users? This action cannot be undone.</p>
        <div class="flex justify-center gap-2 sm:gap-4">
          <button id="confirm-delete" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded font-bold text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-red-400">Delete</button>
          <button id="cancel-delete" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 sm:px-4 sm:py-2 rounded font-bold text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-gray-400">Cancel</button>
        </div>
      </div>
    </div>
  </main>
  <script>
    // Real-time search filter for users table
    document.getElementById('user-search').addEventListener('input', function() {
      const val = this.value.toLowerCase();
      document.querySelectorAll('#users-table tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(val) ? '' : 'none';
      });
    });
    // Select all checkboxes
    document.getElementById('select-all-users').addEventListener('change', function() {
      document.querySelectorAll('#users-table tbody input[type="checkbox"]').forEach(cb => cb.checked = this.checked);
    });
    // Show mentor select if assign_mentor is chosen
    document.getElementById('bulk-action').addEventListener('change', function() {
      document.getElementById('mentor-select-wrapper').classList.toggle('hidden', this.value !== 'assign_mentor');
    });
    // Modal accessibility: close on Esc or click outside
    const modal = document.getElementById('confirm-modal');
    const closeModal = () => modal.classList.add('hidden');
    document.getElementById('close-modal').onclick = closeModal;
    document.getElementById('cancel-delete').onclick = closeModal;
    document.addEventListener('keydown', function(e) {
      if (!modal.classList.contains('hidden') && e.key === 'Escape') closeModal();
    });
    modal.addEventListener('mousedown', function(e) {
      if (e.target === modal) closeModal();
    });
    // Focus trap for modal
    document.getElementById('bulk-users-form').addEventListener('submit', function(e) {
      const action = document.getElementById('bulk-action').value;
      if (action === 'delete') {
        e.preventDefault();
        modal.classList.remove('hidden');
        setTimeout(() => document.getElementById('confirm-delete').focus(), 100);
      }
    });
  </script>
</body>
</html> 