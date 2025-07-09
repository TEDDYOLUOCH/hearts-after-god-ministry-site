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
  header('Location: mentors.php');
  exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $conn->real_escape_string($_POST['name']);
  $email = $conn->real_escape_string($_POST['email']);
  $bio = $conn->real_escape_string($_POST['bio']);
  $conn->query("UPDATE mentors SET name='$name', email='$email', bio='$bio' WHERE id=$id");
  $conn->close();
  header('Location: mentors.php');
  exit;
}

// Fetch mentor data
$result = $conn->query("SELECT * FROM mentors WHERE id=$id LIMIT 1");
$mentor = $result->fetch_assoc();
$conn->close();
if (!$mentor) {
  header('Location: mentors.php');
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Mentor</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="mentors.php" class="font-bold text-[#7C3AED] text-xl">← Mentor Management</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-xl mx-auto mt-8 bg-white p-8 rounded-xl shadow">
    <h2 class="text-2xl font-bold text-[#7C3AED] mb-6">Edit Mentor</h2>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block font-semibold mb-1">Name</label>
        <input type="text" name="name" class="w-full px-4 py-2 border rounded" value="<?php echo htmlspecialchars($mentor['name']); ?>" required>
      </div>
      <div>
        <label class="block font-semibold mb-1">Email</label>
        <input type="email" name="email" class="w-full px-4 py-2 border rounded" value="<?php echo htmlspecialchars($mentor['email']); ?>" required>
      </div>
      <div>
        <label class="block font-semibold mb-1">Bio</label>
        <textarea name="bio" class="w-full px-4 py-2 border rounded"><?php echo htmlspecialchars($mentor['bio']); ?></textarea>
      </div>
      <button type="submit" class="bg-[#7C3AED] text-white px-6 py-2 rounded font-bold shadow hover:bg-[#FDBA17] hover:text-[#2046B3] transition">Save Changes</button>
    </form>
  </main>
</body>
</html> 