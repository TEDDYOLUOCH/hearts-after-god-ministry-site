<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    file_put_contents('login_debug.txt', print_r([$email, $user], true));

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        // Set redirect URL based on role
        $redirect = '';
        switch ($user['role']) {
            case 'admin':
                $redirect = '/hearts-after-god-ministry-site/dashboard/admin-dashboard.php';
                break;
            case 'media_team':
                $redirect = '/hearts-after-god-ministry-site/dashboard/media-team.php';
                break;
            case 'ministry_leader':
                $redirect = '/hearts-after-god-ministry-site/dashboard/ministry-leader.php';
                break;
            case 'event_coordinator':
                $redirect = '/hearts-after-god-ministry-site/dashboard/event-coordinator.php';
                break;
            case 'discipleship_leader':
                $redirect = '/hearts-after-god-ministry-site/dashboard/discipleship-leader.php';
                break;
            case 'registered_member':
                $redirect = '/hearts-after-god-ministry-site/user-area.php';
                break;
            default:
                $redirect = '/hearts-after-god-ministry-site/index.html';
                break;
        }
        echo json_encode([
            'success' => true,
            'role' => $user['role'],
            'redirect' => $redirect
        ]);
        exit;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password.'
        ]);
        exit;
    }
}
?> 