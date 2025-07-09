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
$mentors = [];
$result = $conn->query("SELECT id, name, email FROM mentors");
while ($row = $result->fetch_assoc()) {
  $mentors[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Mentor Management</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="dashboard.php" class="font-bold text-[#7C3AED] text-xl">← Admin Dashboard</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-4xl mx-auto mt-8 bg-white p-8 rounded-xl shadow">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
      <h2 class="text-2xl font-bold text-[#7C3AED]">Mentors</h2>
      <input type="text" id="mentor-search" placeholder="Search by name or email..." class="border rounded px-4 py-2 w-full md:w-72 focus:ring-2 focus:ring-[#7C3AED]" />
    </div>
    <div class="overflow-x-auto">
      <table id="mentors-table" class="min-w-full border-collapse">
        <thead>
          <tr>
            <th class="p-3 text-left">ID</th>
            <th class="p-3 text-left">Name</th>
            <th class="p-3 text-left">Email</th>
            <th class="p-3 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($mentors as $mentor): ?>
          <tr class="border-t">
            <td class="p-3"><?php echo $mentor['id']; ?></td>
            <td class="p-3"><?php echo htmlspecialchars($mentor['name']); ?></td>
            <td class="p-3"><?php echo htmlspecialchars($mentor['email']); ?></td>
            <td class="p-3">
              <a href="mentor-edit.php?id=<?php echo $mentor['id']; ?>" class="text-[#2046B3] underline">Edit</a>
              <!-- Add delete or more actions as needed -->
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
  <script>
    document.getElementById('mentor-search').addEventListener('input', function() {
      const val = this.value.toLowerCase();
      document.querySelectorAll('#mentors-table tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(val) ? '' : 'none';
      });
    });
  </script>
</body>
</html> 