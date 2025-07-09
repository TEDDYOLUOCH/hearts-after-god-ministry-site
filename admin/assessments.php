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
$assessments = $conn->query("SELECT a.*, m.title AS module_title, (SELECT COUNT(*) FROM assessment_questions q WHERE q.assessment_id = a.id) AS question_count FROM assessments a LEFT JOIN modules m ON a.module_id = m.id ORDER BY a.id DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Assessment Management</title>
  <link href='https://cdn.tailwindcss.com' rel='stylesheet'>
  <style>
    #assessments-table thead th { position: sticky; top: 0; background: #fff; z-index: 2; }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="dashboard.php" class="font-bold text-[#7C3AED] text-xl">← Admin Dashboard</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-4xl mx-auto mt-4 bg-white p-2 sm:p-4 md:p-8 rounded-xl shadow">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2 sm:gap-4">
      <h2 class="text-xl sm:text-2xl font-bold text-[#7C3AED]">Assessments</h2>
      <input type="text" id="assessment-search" placeholder="Search by title or module..." class="border rounded px-3 py-2 sm:px-4 sm:py-2 w-full md:w-72 focus:ring-2 focus:ring-[#7C3AED] text-sm sm:text-base" />
    </div>
    <form id="bulk-assessments-form" method="POST">
      <div class="flex gap-2 items-center mb-2 sm:mb-4">
        <select id="bulk-action-assessments" name="bulk_action" class="border rounded px-2 py-2 sm:px-3 sm:py-2 text-sm sm:text-base">
          <option value="">Bulk Actions</option>
          <option value="delete">Delete Assessments</option>
        </select>
        <button type="submit" class="bg-[#7C3AED] text-white px-3 py-2 sm:px-4 sm:py-2 rounded font-bold hover:bg-[#FDBA17] hover:text-[#2046B3] transition text-sm sm:text-base">Apply</button>
      </div>
      <div class="overflow-x-auto rounded-lg">
        <table id="assessments-table" class="min-w-full border-collapse text-xs sm:text-sm">
          <thead>
            <tr class="bg-gray-100 text-[#2046B3]">
              <th class="p-2"><input type="checkbox" id="select-all-assessments" class="w-5 h-5 sm:w-6 sm:h-6" /></th>
              <th class="p-2 text-left">Title</th>
              <th class="p-2 text-left">Module</th>
              <th class="p-2 text-center">Questions</th>
              <th class="p-2 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($a = $assessments->fetch_assoc()): ?>
            <tr class="border-b">
              <td class="p-2"><input type="checkbox" name="assessment_ids[]" value="<?= $a['id'] ?>" class="w-5 h-5 sm:w-6 sm:h-6" /></td>
              <td class="p-2"><?= htmlspecialchars($a['title']) ?></td>
              <td class="p-2"><?= htmlspecialchars($a['module_title']) ?></td>
              <td class="p-2 text-center"><?= $a['question_count'] ?></td>
              <td class="p-2 text-center">
                <a href="assessment-edit.php?id=<?= $a['id'] ?>" class="text-[#7C3AED] font-bold mr-2 text-xs sm:text-sm">Edit</a>
                <a href="assessment-delete.php?id=<?= $a['id'] ?>" class="text-red-500 font-bold text-xs sm:text-sm" onclick="return confirm('Delete this assessment?')">Delete</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </form>
    <!-- Confirmation Modal -->
    <div id="confirm-modal-assessments" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden" tabindex="-1">
      <div class="bg-white rounded-xl p-4 sm:p-8 shadow-xl max-w-xs sm:max-w-sm w-full text-center relative" role="dialog" aria-modal="true" aria-labelledby="modal-title-assessments">
        <button id="close-modal-assessments" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 focus:outline-none" aria-label="Close">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <h3 id="modal-title-assessments" class="text-lg sm:text-xl font-bold mb-2 sm:mb-4">Confirm Bulk Delete</h3>
        <p class="mb-4 sm:mb-6 text-sm sm:text-base">Are you sure you want to delete the selected assessments? This action cannot be undone.</p>
        <div class="flex justify-center gap-2 sm:gap-4">
          <button id="confirm-delete-assessments" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded font-bold text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-red-400">Delete</button>
          <button id="cancel-delete-assessments" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 sm:px-4 sm:py-2 rounded font-bold text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-gray-400">Cancel</button>
        </div>
      </div>
    </div>
  </main>
  <script>
    document.getElementById('assessment-search').addEventListener('input', function() {
      const val = this.value.toLowerCase();
      document.querySelectorAll('#assessments-table tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(val) ? '' : 'none';
      });
    });
    document.getElementById('select-all-assessments').addEventListener('change', function() {
      document.querySelectorAll('#assessments-table tbody input[type="checkbox"]').forEach(cb => cb.checked = this.checked);
    });
    // Modal accessibility: close on Esc or click outside
    const modalAssess = document.getElementById('confirm-modal-assessments');
    const closeModalAssess = () => modalAssess.classList.add('hidden');
    document.getElementById('close-modal-assessments').onclick = closeModalAssess;
    document.getElementById('cancel-delete-assessments').onclick = closeModalAssess;
    document.addEventListener('keydown', function(e) {
      if (!modalAssess.classList.contains('hidden') && e.key === 'Escape') closeModalAssess();
    });
    modalAssess.addEventListener('mousedown', function(e) {
      if (e.target === modalAssess) closeModalAssess();
    });
    // Focus trap for modal
    document.getElementById('bulk-assessments-form').addEventListener('submit', function(e) {
      const action = document.getElementById('bulk-action-assessments').value;
      if (action === 'delete') {
        e.preventDefault();
        modalAssess.classList.remove('hidden');
        setTimeout(() => document.getElementById('confirm-delete-assessments').focus(), 100);
      }
    });
  </script>
</body>
</html>
<?php $conn->close(); ?> 