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
$modules = $conn->query("SELECT id, title FROM modules ORDER BY id");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $conn->real_escape_string($_POST['title']);
  $module_id = intval($_POST['module_id']);
  $conn->query("INSERT INTO assessments (title, module_id) VALUES ('$title', $module_id)");
  $assessment_id = $conn->insert_id;
  foreach ($_POST['questions'] as $q) {
    $q = $conn->real_escape_string($q);
    if (trim($q) !== '') {
      $conn->query("INSERT INTO assessment_questions (assessment_id, question) VALUES ($assessment_id, '$q')");
    }
  }
  $conn->close();
  header('Location: assessments.php');
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Assessment</title>
  <link href='https://cdn.tailwindcss.com' rel='stylesheet'>
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="assessments.php" class="font-bold text-[#7C3AED] text-xl">← Assessments</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-xl mx-auto mt-8 bg-white p-8 rounded-xl shadow">
    <h2 class="text-2xl font-bold text-[#7C3AED] mb-6">Add Assessment</h2>
    <form method="POST" class="space-y-4" id="assessment-form">
      <div>
        <label class="block font-semibold mb-1">Module</label>
        <select name="module_id" class="w-full px-4 py-2 border rounded" required>
          <option value="">Select Module</option>
          <?php while($m = $modules->fetch_assoc()): ?>
            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div>
        <label class="block font-semibold mb-1">Assessment Title</label>
        <input type="text" name="title" class="w-full px-4 py-2 border rounded" required>
      </div>
      <div id="questions-section">
        <label class="block font-semibold mb-1">Questions</label>
        <div class="space-y-2" id="questions-list">
          <input type="text" name="questions[]" class="w-full px-4 py-2 border rounded" placeholder="Enter question" required>
        </div>
        <button type="button" id="add-question" class="mt-2 px-4 py-1 bg-[#FDBA17] text-[#2046B3] rounded font-bold hover:bg-[#7C3AED] hover:text-white transition">+ Add Question</button>
      </div>
      <button type="submit" class="bg-[#7C3AED] text-white px-6 py-2 rounded font-bold shadow hover:bg-[#FDBA17] hover:text-[#2046B3] transition">Create Assessment</button>
    </form>
  </main>
  <script>
    document.getElementById('add-question').addEventListener('click', function() {
      const list = document.getElementById('questions-list');
      const input = document.createElement('input');
      input.type = 'text';
      input.name = 'questions[]';
      input.className = 'w-full px-4 py-2 border rounded mt-2';
      input.placeholder = 'Enter question';
      list.appendChild(input);
    });
  </script>
</body>
</html>
<?php $conn->close(); ?> 