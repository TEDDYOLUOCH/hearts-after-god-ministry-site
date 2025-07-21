<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        // Redirect based on role
        switch ($user['role']) {
            case 'admin':
                header('Location: /dashboard/admin-dashboard.php');
                exit;
            case 'media_team':
                header('Location: /dashboard/media-team.php');
                exit;
            case 'ministry_leader':
                header('Location: /dashboard/ministry-leader.php');
                exit;
            case 'event_coordinator':
                header('Location: /dashboard/event-coordinator.php');
                exit;
            case 'discipleship_leader':
                header('Location: /dashboard/discipleship-leader.php');
                exit;
            case 'registered_member':
                header('Location: /user-area.php');
                exit;
            default:
                header('Location: /index.html');
                exit;
        }
    } else {
        $_SESSION['login_error'] = 'Invalid email or password.';
        header('Location: login.html');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Login</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100">
<form method="POST" class="max-w-md mx-auto mt-20 p-8 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Admin Login</h2>
    <?php if (!empty($error)) echo "<div class='text-red-600 mb-4'>$error</div>"; ?>
    <input name="username" class="block w-full mb-4 p-2 border rounded" placeholder="Username" required>
    <input name="password" type="password" class="block w-full mb-4 p-2 border rounded" placeholder="Password" required>
    <button class="w-full bg-purple-700 text-white py-2 rounded font-bold">Login</button>
</form>
</body>
</html> 