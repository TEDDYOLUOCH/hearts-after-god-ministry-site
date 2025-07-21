<?php
require_once __DIR__ . '/backend/config/db.php';
$message = '';
$success = false;
$showForm = false;
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($token) {
    // Check if token exists and is not expired
    $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()');
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    if ($reset) {
        $showForm = true;
        $email = $reset['email'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
            $password = $_POST['password'];
            if (strlen($password) < 8) {
                $message = 'Password must be at least 8 characters.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                // Try to update users table first
                $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
                $stmt->execute([$passwordHash, $email]);
                if ($stmt->rowCount() === 0) {
                    // Try admin_users table
                    $stmt = $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE username = ?');
                    $stmt->execute([$passwordHash, $email]);
                }
                // Invalidate token
                $stmt = $pdo->prepare('DELETE FROM password_resets WHERE token = ?');
                $stmt->execute([$token]);
                $message = 'Your password has been reset successfully. You can now <a href="frontend/auth.html" class="text-[#7C3AED] underline font-bold">login</a>.';
                $success = true;
                $showForm = false;
            }
        }
    } else {
        $message = 'Invalid or expired reset link.';
    }
} else {
    $message = 'No reset token provided.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-100 via-[#F3F4F6] to-[#E0E7FF] min-h-screen flex items-center justify-center">
  <div class="w-full max-w-md p-4">
    <div class="bg-white rounded-2xl shadow-2xl px-8 py-10">
      <h2 class="text-3xl font-extrabold text-center mb-2">Reset Password</h2>
      <?php if (!empty($message)) : ?>
        <div class="mb-4 text-center font-semibold <?php echo $success ? 'text-green-600' : 'text-red-600'; ?>">
          <?php echo $message; ?>
        </div>
      <?php endif; ?>
      <?php if ($showForm) : ?>
      <form method="POST" class="space-y-4">
        <div class="relative">
          <span class="absolute left-3 top-3 text-gray-400">
            <!-- Lock Icon -->
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="9" width="10" height="7" rx="2"/><path d="M7 9V7a3 3 0 016 0v2"/></svg>
          </span>
          <input name="password" type="password" placeholder="New Password" class="w-full border border-gray-300 pl-10 p-2 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] transition" required>
        </div>
        <button type="submit" class="w-full bg-[#7C3AED] text-white py-2 rounded-full font-bold shadow hover:bg-[#5B21B6] transition-all duration-200">Reset Password</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html> 