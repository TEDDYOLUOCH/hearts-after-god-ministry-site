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
// Handle mentor assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['mentor_id'])) {
  $user_id = intval($_POST['user_id']);
  $mentor_id = intval($_POST['mentor_id']);
  $exists = $conn->query("SELECT * FROM mentor_assignments WHERE user_id=$user_id")->num_rows;
  if ($exists) {
    $conn->query("UPDATE mentor_assignments SET mentor_id=$mentor_id WHERE user_id=$user_id");
  } else {
    $conn->query("INSERT INTO mentor_assignments (user_id, mentor_id) VALUES ($user_id, $mentor_id)");
  }
}
$users = $conn->query("SELECT id, name FROM users ORDER BY name");
$mentors = $conn->query("SELECT id, name FROM mentors ORDER BY name");
$mentors_arr = [];
while ($m = $mentors->fetch_assoc()) $mentors_arr[] = $m;
?>
<!DOCTYPE html>
<html>
<head>
  <title>Mentor Assignment Management</title>
  <link href='https://cdn.tailwindcss.com' rel='stylesheet'>
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="dashboard.php" class="font-bold text-[#7C3AED] text-xl">← Admin Dashboard</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-4xl mx-auto mt-8 bg-white p-8 rounded-xl shadow">
    <h2 class="text-2xl font-bold text-[#7C3AED] mb-6">Mentor Assignment Management</h2>
    <table class="w-full table-auto border-collapse">
      <thead>
        <tr class="bg-gray-100 text-[#2046B3]">
          <th class="p-2 text-left">User</th>
          <th class="p-2 text-left">Current Mentor</th>
          <th class="p-2 text-center">Change Mentor</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u):
          $assign = $conn->query("SELECT mentor_id FROM mentor_assignments WHERE user_id={$u['id']}")->fetch_assoc();
          $current_mentor = $assign ? $assign['mentor_id'] : 0;
        ?>
        <tr class="border-b">
          <td class="p-2 font-semibold text-[#7C3AED]"><?= htmlspecialchars($u['name']) ?></td>
          <td class="p-2">
            <?php
              $mentor_name = '';
              foreach ($mentors_arr as $m) {
                if ($m['id'] == $current_mentor) $mentor_name = $m['name'];
              }
              echo $mentor_name ? htmlspecialchars($mentor_name) : '<span class="text-gray-400">None</span>';
            ?>
          </td>
          <td class="p-2 text-center">
            <form method="POST" class="inline">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <select name="mentor_id" class="px-3 py-1 rounded border">
                <option value="0">None</option>
                <?php foreach ($mentors_arr as $m): ?>
                  <option value="<?= $m['id'] ?>" <?= $m['id'] == $current_mentor ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="ml-2 px-3 py-1 bg-[#7C3AED] text-white rounded font-bold hover:bg-[#FDBA17] hover:text-[#2046B3] transition">Update</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</body>
</html>
<?php $conn->close(); ?> 