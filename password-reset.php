<?php
require_once __DIR__ . '/backend/config/db.php';
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } else {
        // Check if user exists in users or admin_users
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            $stmt = $pdo->prepare('SELECT id, username as email FROM admin_users WHERE username = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        }
        if ($user) {
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            // Create password_resets table if not exists
            $pdo->exec('CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                token VARCHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )');
            // Insert token
            $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)');
            $stmt->execute([$email, $token, $expires]);
            // Send email
            $resetLink = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/reset-password.php?token=$token";
            $subject = "Password Reset Request - Hearts After God Ministry";
            $body = "Hello,\n\nWe received a request to reset your password. Click the link below to reset it:\n$resetLink\n\nIf you did not request this, please ignore this email.\n\nBlessings,\nHearts After God Ministry";
            $headers = "From: no-reply@heartsaftergod.org\r\nContent-Type: text/plain; charset=UTF-8";
            if (mail($email, $subject, $body, $headers)) {
                $message = 'A password reset link has been sent to your email.';
                $success = true;
            } else {
                $message = 'Failed to send reset email. Please try again later.';
            }
        } else {
            $message = 'No account found with that email address.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-100 via-[#F3F4F6] to-[#E0E7FF] min-h-screen flex items-center justify-center">
  <div class="w-full max-w-md p-4">
    <div class="bg-white rounded-2xl shadow-2xl px-8 py-10">
      <h2 class="text-3xl font-extrabold text-center mb-2">Forgot Password?</h2>
      <p class="text-center text-gray-500 mb-6">Enter your email address and we'll send you a link to reset your password.</p>
      <?php if (!empty($message)) : ?>
        <div class="mb-4 text-center font-semibold <?php echo $success ? 'text-green-600' : 'text-red-600'; ?>">
          <?php echo $message; ?>
        </div>
      <?php endif; ?>
      <form method="POST" class="space-y-4">
        <div class="relative">
          <span class="absolute left-3 top-3 text-gray-400">
            <!-- Email Icon -->
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.5 6.5l7.5 5 7.5-5"/><rect x="2.5" y="6.5" width="15" height="7" rx="2"/></svg>
          </span>
          <input name="email" type="email" placeholder="Email" class="w-full border border-gray-300 pl-10 p-2 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] transition" required>
        </div>
        <button type="submit" class="w-full bg-[#7C3AED] text-white py-2 rounded-full font-bold shadow hover:bg-[#5B21B6] transition-all duration-200">Send Reset Link</button>
      </form>
    </div>
  </div>
</body>
</html> 