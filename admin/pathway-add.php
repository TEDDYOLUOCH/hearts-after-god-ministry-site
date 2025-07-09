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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $conn->real_escape_string($_POST['title']);
  $description = $conn->real_escape_string($_POST['description']);
  $conn->query("INSERT INTO pathways (title, description) VALUES ('$title', '$description')");
  $conn->close();
  header('Location: pathways.php');
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Pathway</title>
  <link href='https://cdn.tailwindcss.com' rel='stylesheet'>
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="pathways.php" class="font-bold text-[#7C3AED] text-xl">← Pathways</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-xl mx-auto mt-8 bg-white p-8 rounded-xl shadow">
    <h2 class="text-2xl font-bold text-[#7C3AED] mb-6">Add Pathway</h2>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block font-semibold mb-1">Title</label>
        <input type="text" name="title" class="w-full px-4 py-2 border rounded" required>
      </div>
      <div>
        <label class="block font-semibold mb-1">Description</label>
        <textarea name="description" class="w-full px-4 py-2 border rounded" rows="4" required></textarea>
      </div>
      <button type="submit" class="bg-[#7C3AED] text-white px-6 py-2 rounded font-bold shadow hover:bg-[#FDBA17] hover:text-[#2046B3] transition">Create Pathway</button>
    </form>
  </main>
</body>
</html>
<?php $conn->close(); ?> 