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
// Handle manual override
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['module_id'], $_POST['status'])) {
  $user_id = intval($_POST['user_id']);
  $module_id = intval($_POST['module_id']);
  $status = $_POST['status'] === '1' ? 1 : 0;
  $exists = $conn->query("SELECT * FROM user_module_progress WHERE user_id=$user_id AND module_id=$module_id")->num_rows;
  if ($exists) {
    $conn->query("UPDATE user_module_progress SET completed=$status WHERE user_id=$user_id AND module_id=$module_id");
  } else {
    $conn->query("INSERT INTO user_module_progress (user_id, module_id, completed) VALUES ($user_id, $module_id, $status)");
  }
}
$users = $conn->query("SELECT id, name, email FROM users ORDER BY name");
$modules = $conn->query("SELECT id, title FROM modules ORDER BY id");
$modules_arr = [];
while ($m = $modules->fetch_assoc()) $modules_arr[] = $m;
?>
<!DOCTYPE html>
<html>
<head>
  <title>User Progress Management</title>
  <link href='https://cdn.tailwindcss.com' rel='stylesheet'>
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="dashboard.php" class="font-bold text-[#7C3AED] text-xl">← Admin Dashboard</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-6xl mx-auto mt-8 bg-white p-8 rounded-xl shadow">
    <h2 class="text-2xl font-bold text-[#7C3AED] mb-6">User Progress Management</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse">
        <thead>
          <tr class="bg-gray-100 text-[#2046B3]">
            <th class="p-2 text-left">User</th>
            <?php foreach ($modules_arr as $m): ?>
              <th class="p-2 text-center"><?= htmlspecialchars($m['title']) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php while($u = $users->fetch_assoc()): ?>
            <tr class="border-b">
              <td class="p-2 font-semibold text-[#7C3AED]">
                <?= htmlspecialchars($u['name']) ?><br><span class="text-xs text-gray-500"><?= htmlspecialchars($u['email']) ?></span>
              </td>
              <?php foreach ($modules_arr as $m):
                $prog = $conn->query("SELECT completed FROM user_module_progress WHERE user_id={$u['id']} AND module_id={$m['id']}")->fetch_assoc();
                $completed = $prog ? $prog['completed'] : 0;
              ?>
              <td class="p-2 text-center">
                <form method="POST" class="inline">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="module_id" value="<?= $m['id'] ?>">
                  <input type="hidden" name="status" value="<?= $completed ? 0 : 1 ?>">
                  <button type="submit" class="px-3 py-1 rounded font-bold text-xs <?= $completed ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700' ?> hover:bg-[#FDBA17] hover:text-[#2046B3] transition">
                    <?= $completed ? 'Complete' : 'Incomplete' ?>
                  </button>
                </form>
              </td>
              <?php endforeach; ?>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
<?php $conn->close(); ?> 