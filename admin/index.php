<?php
session_start();
if (isset($_POST['password']) && $_POST['password'] === 'YOUR_ADMIN_PASSWORD') {
  $_SESSION['admin'] = true;
  header('Location: dashboard.php');
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Login</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
  <form method="POST" class="bg-white p-8 rounded shadow max-w-xs w-full">
    <h2 class="text-xl font-bold mb-4 text-center">Admin Login</h2>
    <input type="password" name="password" placeholder="Admin Password" class="w-full px-4 py-2 border rounded mb-4" required>
    <button type="submit" class="w-full bg-[#7C3AED] text-white font-bold py-2 rounded">Login</button>
  </form>
</body>
</html> 