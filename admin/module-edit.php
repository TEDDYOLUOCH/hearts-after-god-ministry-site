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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
  header('Location: modules.php');
  exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $order_num = intval($_POST['order_num']);
  $title = $conn->real_escape_string($_POST['title']);
  $description = $conn->real_escape_string($_POST['description']);
  $content = $conn->real_escape_string($_POST['content']);
  $conn->query("UPDATE modules SET order_num=$order_num, title='$title', description='$description', content='$content' WHERE id=$id");
  $conn->close();
  header('Location: modules.php');
  exit;
}

// Fetch module data
$result = $conn->query("SELECT * FROM modules WHERE id=$id LIMIT 1");
$module = $result->fetch_assoc();
$conn->close();
if (!$module) {
  header('Location: modules.php');
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Module</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="modules.php" class="font-bold text-[#7C3AED] text-xl">← Module Management</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-xl mx-auto mt-8 bg-white p-8 rounded-xl shadow">
    <h2 class="text-2xl font-bold text-[#7C3AED] mb-6">Edit Module</h2>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block font-semibold mb-1">Order</label>
        <input type="number" name="order_num" class="w-full px-4 py-2 border rounded" value="<?php echo htmlspecialchars($module['order_num']); ?>" required>
      </div>
      <div>
        <label class="block font-semibold mb-1">Title</label>
        <input type="text" name="title" class="w-full px-4 py-2 border rounded" value="<?php echo htmlspecialchars($module['title']); ?>" required>
      </div>
      <div>
        <label class="block font-semibold mb-1">Description</label>
        <input type="text" name="description" class="w-full px-4 py-2 border rounded" value="<?php echo htmlspecialchars($module['description']); ?>">
      </div>
      <div>
        <label class="block font-semibold mb-1">Content</label>
        <textarea name="content" class="w-full px-4 py-2 border rounded"><?php echo htmlspecialchars($module['content']); ?></textarea>
      </div>
      <button type="submit" class="bg-[#7C3AED] text-white px-6 py-2 rounded font-bold shadow hover:bg-[#FDBA17] hover:text-[#2046B3] transition">Save Changes</button>
    </form>
  </main>
</body>
</html> 