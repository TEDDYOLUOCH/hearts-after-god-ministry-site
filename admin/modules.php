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
$modules = [];
$result = $conn->query("SELECT id, title, description, order_num FROM modules ORDER BY order_num ASC");
while ($row = $result->fetch_assoc()) {
  $modules[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Module Management</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="dashboard.php" class="font-bold text-[#7C3AED] text-xl">← Admin Dashboard</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-4xl mx-auto mt-8">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-[#7C3AED]">Module Management</h2>
      <a href="module-add.php" class="bg-[#7C3AED] text-white px-4 py-2 rounded font-bold shadow hover:bg-[#FDBA17] hover:text-[#2046B3] transition">Add Module</a>
    </div>
    <table class="w-full bg-white rounded-xl shadow">
      <thead>
        <tr>
          <th class="p-3 text-left">Order</th>
          <th class="p-3 text-left">Title</th>
          <th class="p-3 text-left">Description</th>
          <th class="p-3 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($modules as $module): ?>
        <tr class="border-t">
          <td class="p-3"><?php echo $module['order_num']; ?></td>
          <td class="p-3"><?php echo htmlspecialchars($module['title']); ?></td>
          <td class="p-3"><?php echo htmlspecialchars($module['description']); ?></td>
          <td class="p-3">
            <a href="module-edit.php?id=<?php echo $module['id']; ?>" class="text-[#2046B3] underline">Edit</a>
            <a href="module-delete.php?id=<?php echo $module['id']; ?>" class="text-red-500 underline ml-2" onclick="return confirm('Are you sure you want to delete this module?');">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</body>
</html> 