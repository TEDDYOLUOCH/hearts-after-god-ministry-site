<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: index.php');
  exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $host = 'localhost';
  $db   = 'hearts_after_god';
  $user = 'root';
  $pass = '';
  $conn = new mysqli($host, $user, $pass, $db);
  $title = $conn->real_escape_string($_POST['title']);
  $description = $conn->real_escape_string($_POST['description']);
  $file_type = $conn->real_escape_string($_POST['file_type']);
  $access_level = $conn->real_escape_string($_POST['access_level']);
  $file_path = '';
  if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('resource_') . '.' . $ext;
    $upload_dir = '../assets/docs/';
    move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . $filename);
    $file_path = 'assets/docs/' . $filename;
  }
  $conn->query("INSERT INTO resources (title, description, file_path, file_type, access_level) VALUES ('$title', '$description', '$file_path', '$file_type', '$access_level')");
  $conn->close();
  header('Location: resources.php');
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Resource</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="resources.php" class="font-bold text-[#7C3AED] text-xl">← Resource Management</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-xl mx-auto mt-8 bg-white p-8 rounded-xl shadow">
    <h2 class="text-2xl font-bold text-[#7C3AED] mb-6">Add Resource</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block font-semibold mb-1">Title</label>
        <input type="text" name="title" class="w-full px-4 py-2 border rounded" required>
      </div>
      <div>
        <label class="block font-semibold mb-1">Description</label>
        <textarea name="description" class="w-full px-4 py-2 border rounded"></textarea>
      </div>
      <div>
        <label class="block font-semibold mb-1">File Type</label>
        <input type="text" name="file_type" class="w-full px-4 py-2 border rounded" placeholder="pdf, pptx, docx, etc." required>
      </div>
      <div>
        <label class="block font-semibold mb-1">Access Level</label>
        <input type="text" name="access_level" class="w-full px-4 py-2 border rounded" placeholder="all, completed, advanced" value="all">
      </div>
      <div>
        <label class="block font-semibold mb-1">Upload File</label>
        <input type="file" name="file" class="w-full px-4 py-2 border rounded" required>
      </div>
      <button type="submit" class="bg-[#7C3AED] text-white px-6 py-2 rounded font-bold shadow hover:bg-[#FDBA17] hover:text-[#2046B3] transition">Add Resource</button>
    </form>
  </main>
</body>
</html> 