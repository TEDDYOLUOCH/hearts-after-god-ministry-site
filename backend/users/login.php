<?php
session_start();
require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header('Location:../../dashboard/admin-dashboard.php');
                    exit;
                case 'media_team':
                    header('Location:../../dashboard/media-team.php');
                    exit;
                case 'ministry_leader':
                    header('Location: ../../dashboard/ministry-leader.php');
                    exit;
                case 'event_coordinator':
                    header('Location:../../dashboard/event-coordinator.php');
                    exit;
                case 'discipleship_leader':
                    header('Location: ../../dashboard/discipleship-leader.php');
                    exit;
                case 'registered_member':
                    header('Location:user-area.php');
                    exit;
                default:
                    header('Location: index.html');
                    exit;
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">
  <form method="POST" class="w-full max-w-md p-8 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-center text-purple-700">Login</h2>

    <?php if (!empty($error)): ?>
      <div class="mb-4 p-3 bg-red-100 text-red-700 border border-red-400 rounded">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <div class="mb-4">
      <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
      <input name="email" type="email" id="email" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-purple-600" placeholder="you@example.com" required>
    </div>

    <div class="mb-6">
      <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
      <input name="password" type="password" id="password" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-purple-600" placeholder="********" required>
    </div>

    <button type="submit" class="w-full bg-purple-700 text-white py-2 rounded hover:bg-purple-800 transition font-semibold">
      Login
    </button>

    <p class="mt-4 text-center text-sm text-gray-600">Need an account? <a href="/register.php" class="text-purple-700 hover:underline">Register</a></p>
  </form>
</body>
</html>
