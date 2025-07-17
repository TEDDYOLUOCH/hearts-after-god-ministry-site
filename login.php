<?php
session_start();
require_once 'db.php';

// Handle form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $_SESSION['login_error'] = 'Email and password are required.';
        header('Location: login.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        // Redirect based on role
        switch ($user['role']) {
            case 'admin':
                header('Location: /admin/dashboard.php');
                exit;
            case 'blogger':
                header('Location: /dashboard/blogger-dashboard.php');
                exit;
            case 'ministry_leader':
                header('Location: /dashboard/ministry-leader-dashboard.php');
                exit;
            case 'media_team':
                header('Location: /dashboard/media-team-dashboard.php');
                exit;
            case 'discipleship_user':
                header('Location: /dashboard/discipleship-user.php');
                exit;
            default:
                header('Location: /index.html');
                exit;
        }
    } else {
        $_SESSION['login_error'] = 'Invalid email or password.';
        header('Location: login.php');
        exit;
    }
} else {
    // If not POST, show login form (or redirect as needed)
    header('Location: login.html');
    exit;
} 