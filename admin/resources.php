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
$resources = [];
$result = $conn->query("SELECT id, title, file_type, file_path FROM resources ORDER BY id DESC");
while ($row = $result->fetch_assoc()) {
  $resources[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Resource Management</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
  <style>
    #resources-table thead th { position: sticky; top: 0; background: #fff; z-index: 2; }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="dashboard.php" class="font-bold text-[#7C3AED] text-xl">← Admin Dashboard</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-4xl mx-auto mt-4 bg-white p-2 sm:p-4 md:p-8 rounded-xl shadow">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2 sm:gap-4">
      <h2 class="text-xl sm:text-2xl font-bold text-[#7C3AED]">Resources</h2>
      <input type="text" id="resource-search" placeholder="Search by title, description, or type..." class="border rounded px-3 py-2 sm:px-4 sm:py-2 w-full md:w-72 focus:ring-2 focus:ring-[#7C3AED] text-sm sm:text-base" />
    </div>
    <form id="bulk-resources-form" method="POST">
      <div class="flex gap-2 items-center mb-2 sm:mb-4">
        <select id="bulk-action-resources" name="bulk_action" class="border rounded px-2 py-2 sm:px-3 sm:py-2 text-sm sm:text-base">
          <option value="">Bulk Actions</option>
          <option value="delete">Delete Resources</option>
        </select>
        <button type="submit" class="bg-[#7C3AED] text-white px-3 py-2 sm:px-4 sm:py-2 rounded font-bold hover:bg-[#FDBA17] hover:text-[#2046B3] transition text-sm sm:text-base">Apply</button>
      </div>
      <div class="overflow-x-auto rounded-lg">
        <table id="resources-table" class="min-w-full border-collapse text-xs sm:text-sm">
          <thead>
            <tr>
              <th class="p-2"><input type="checkbox" id="select-all-resources" class="w-5 h-5 sm:w-6 sm:h-6" /></th>
              <th class="p-3 text-left">Title</th>
              <th class="p-3 text-left">Type</th>
              <th class="p-3 text-left">File</th>
              <th class="p-3 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($resources as $r): ?>
            <tr class="border-t">
              <td class="p-2"><input type="checkbox" name="resource_ids[]" value="<?= $r['id'] ?>" class="w-5 h-5 sm:w-6 sm:h-6" /></td>
              <td class="p-3"><?php echo htmlspecialchars($r['title']); ?></td>
              <td class="p-3"><?php echo htmlspecialchars($r['file_type']); ?></td>
              <td class="p-3"><a href="../<?php echo htmlspecialchars($r['file_path']); ?>" target="_blank" class="text-[#2046B3] underline text-xs sm:text-sm">View</a></td>
              <td class="p-3">
                <a href="resource-edit.php?id=<?php echo $r['id']; ?>" class="text-[#2046B3] underline text-xs sm:text-sm">Edit</a>
                <a href="resource-delete.php?id=<?php echo $r['id']; ?>" class="text-red-500 underline ml-2 text-xs sm:text-sm" onclick="return confirm('Are you sure you want to delete this resource?');">Delete</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </form>
    <!-- Confirmation Modal -->
    <div id="confirm-modal-resources" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden" tabindex="-1">
      <div class="bg-white rounded-xl p-4 sm:p-8 shadow-xl max-w-xs sm:max-w-sm w-full text-center relative" role="dialog" aria-modal="true" aria-labelledby="modal-title-resources">
        <button id="close-modal-resources" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 focus:outline-none" aria-label="Close">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <h3 id="modal-title-resources" class="text-lg sm:text-xl font-bold mb-2 sm:mb-4">Confirm Bulk Delete</h3>
        <p class="mb-4 sm:mb-6 text-sm sm:text-base">Are you sure you want to delete the selected resources? This action cannot be undone.</p>
        <div class="flex justify-center gap-2 sm:gap-4">
          <button id="confirm-delete-resources" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded font-bold text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-red-400">Delete</button>
          <button id="cancel-delete-resources" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 sm:px-4 sm:py-2 rounded font-bold text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-gray-400">Cancel</button>
        </div>
      </div>
    </div>
  </main>
  <script>
    document.getElementById('resource-search').addEventListener('input', function() {
      const val = this.value.toLowerCase();
      document.querySelectorAll('#resources-table tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(val) ? '' : 'none';
      });
    });
    document.getElementById('select-all-resources').addEventListener('change', function() {
      document.querySelectorAll('#resources-table tbody input[type="checkbox"]').forEach(cb => cb.checked = this.checked);
    });
    document.getElementById('bulk-resources-form').addEventListener('submit', function(e) {
      const action = document.getElementById('bulk-action-resources').value;
      if (action === 'delete') {
        e.preventDefault();
        modalRes.classList.remove('hidden');
        setTimeout(() => document.getElementById('confirm-delete-resources').focus(), 100);
      }
    });
    document.getElementById('cancel-delete-resources').onclick = function() {
      document.getElementById('confirm-modal-resources').classList.add('hidden');
    };
    document.getElementById('confirm-delete-resources').onclick = function() {
      document.getElementById('confirm-modal-resources').classList.add('hidden');
      document.getElementById('bulk-resources-form').submit();
    };
    // Modal accessibility: close on Esc or click outside
    const modalRes = document.getElementById('confirm-modal-resources');
    const closeModalRes = () => modalRes.classList.add('hidden');
    document.getElementById('close-modal-resources').onclick = closeModalRes;
    document.getElementById('cancel-delete-resources').onclick = closeModalRes;
    document.addEventListener('keydown', function(e) {
      if (!modalRes.classList.contains('hidden') && e.key === 'Escape') closeModalRes();
    });
    modalRes.addEventListener('mousedown', function(e) {
      if (e.target === modalRes) closeModalRes();
    });
  </script>
</body>
</html> 