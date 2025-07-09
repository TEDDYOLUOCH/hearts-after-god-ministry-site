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
if ($id > 0) {
  $conn->query("DELETE FROM resources WHERE id=$id");
}
$conn->close();
header('Location: resources.php');
exit;
?> 