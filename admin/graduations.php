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
// Handle manual graduation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['graduated'])) {
  $user_id = intval($_POST['user_id']);
  $graduated = $_POST['graduated'] === '1' ? 1 : 0;
  $date = $graduated ? date('Y-m-d') : null;
  $cert_path = $_POST['existing_cert_path'] ?? '';
  if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['certificate']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('cert_') . '.' . $ext;
    $upload_dir = '../assets/docs/';
    move_uploaded_file($_FILES['certificate']['tmp_name'], $upload_dir . $filename);
    $cert_path = 'assets/docs/' . $filename;
  }
  $exists = $conn->query("SELECT * FROM graduations WHERE user_id=$user_id")->num_rows;
  if ($exists) {
    $sql = $graduated ?
      "UPDATE graduations SET graduated=1, graduation_date='$date', certificate_path='$cert_path' WHERE user_id=$user_id" :
      "UPDATE graduations SET graduated=0, graduation_date=NULL, certificate_path='' WHERE user_id=$user_id";
    $conn->query($sql);
  } else {
    if ($graduated) {
      $conn->query("INSERT INTO graduations (user_id, graduated, graduation_date, certificate_path) VALUES ($user_id, 1, '$date', '$cert_path')");
    }
  }
}
$users = $conn->query("SELECT id, name, email FROM users ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Graduation Management</title>
  <link href='https://cdn.tailwindcss.com' rel='stylesheet'>
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="dashboard.php" class="font-bold text-[#7C3AED] text-xl">← Admin Dashboard</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-4xl mx-auto mt-8 bg-white p-8 rounded-xl shadow">
    <h2 class="text-2xl font-bold text-[#7C3AED] mb-6">Graduation Management</h2>
    <table class="w-full table-auto border-collapse">
      <thead>
        <tr class="bg-gray-100 text-[#2046B3]">
          <th class="p-2 text-left">User</th>
          <th class="p-2 text-center">Graduated?</th>
          <th class="p-2 text-center">Date</th>
          <th class="p-2 text-center">Certificate</th>
          <th class="p-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u):
          $grad = $conn->query("SELECT * FROM graduations WHERE user_id={$u['id']}")->fetch_assoc();
          $graduated = $grad ? $grad['graduated'] : 0;
          $date = $grad ? $grad['graduation_date'] : '';
          $cert = $grad ? $grad['certificate_path'] : '';
        ?>
        <tr class="border-b">
          <td class="p-2 font-semibold text-[#7C3AED]"><?= htmlspecialchars($u['name']) ?><br><span class="text-xs text-gray-500"><?= htmlspecialchars($u['email']) ?></span></td>
          <td class="p-2 text-center">
            <?= $graduated ? '<span class="bg-green-500 text-white px-2 py-1 rounded text-xs">Yes</span>' : '<span class="bg-gray-300 text-gray-700 px-2 py-1 rounded text-xs">No</span>' ?>
          </td>
          <td class="p-2 text-center">
            <?= $graduated && $date ? htmlspecialchars($date) : '-' ?>
          </td>
          <td class="p-2 text-center">
            <?php if ($graduated && $cert): ?>
              <a href="../<?= htmlspecialchars($cert) ?>" target="_blank" class="text-[#2046B3] underline">Download</a>
            <?php else: ?>
              <span class="text-gray-400">None</span>
            <?php endif; ?>
          </td>
          <td class="p-2 text-center">
            <form method="POST" enctype="multipart/form-data" class="inline">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <input type="hidden" name="existing_cert_path" value="<?= htmlspecialchars($cert) ?>">
              <input type="hidden" name="graduated" value="<?= $graduated ? 0 : 1 ?>">
              <?php if (!$graduated): ?>
                <input type="file" name="certificate" class="mb-1">
              <?php endif; ?>
              <button type="submit" class="px-3 py-1 rounded font-bold text-xs <?= $graduated ? 'bg-gray-300 text-gray-700' : 'bg-green-500 text-white' ?> hover:bg-[#FDBA17] hover:text-[#2046B3] transition">
                <?= $graduated ? 'Revoke' : 'Graduate' ?>
              </button>
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