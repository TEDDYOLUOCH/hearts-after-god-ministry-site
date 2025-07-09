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
if ($id <= 0) {
  header('Location: assessments.php');
  exit;
}
$modules = $conn->query("SELECT id, title FROM modules ORDER BY id");
$assessment = $conn->query("SELECT * FROM assessments WHERE id=$id")->fetch_assoc();
$questions = $conn->query("SELECT * FROM assessment_questions WHERE assessment_id=$id");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $conn->real_escape_string($_POST['title']);
  $module_id = intval($_POST['module_id']);
  $conn->query("UPDATE assessments SET title='$title', module_id=$module_id WHERE id=$id");
  // Remove all old questions
  $conn->query("DELETE FROM assessment_questions WHERE assessment_id=$id");
  // Add new questions
  foreach ($_POST['questions'] as $q) {
    $q = $conn->real_escape_string($q);
    if (trim($q) !== '') {
      $conn->query("INSERT INTO assessment_questions (assessment_id, question) VALUES ($id, '$q')");
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
  <title>Edit Assessment</title>
  <link href='https://cdn.tailwindcss.com' rel='stylesheet'>
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <a href="assessments.php" class="font-bold text-[#7C3AED] text-xl">← Assessments</a>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-xl mx-auto mt-8 bg-white p-8 rounded-xl shadow">
    <h2 class="text-2xl font-bold text-[#7C3AED] mb-6">Edit Assessment</h2>
    <form method="POST" class="space-y-4" id="assessment-form">
      <div>
        <label class="block font-semibold mb-1">Module</label>
        <select name="module_id" class="w-full px-4 py-2 border rounded" required>
          <option value="">Select Module</option>
          <?php foreach ($modules as $m): ?>
            <option value="<?= $m['id'] ?>" <?= $m['id'] == $assessment['module_id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block font-semibold mb-1">Assessment Title</label>
        <input type="text" name="title" class="w-full px-4 py-2 border rounded" value="<?= htmlspecialchars($assessment['title']) ?>" required>
      </div>
      <div id="questions-section">
        <label class="block font-semibold mb-1">Questions</label>
        <div class="space-y-2" id="questions-list">
          <?php foreach ($questions as $q): ?>
            <div class="flex gap-2 mb-2">
              <input type="text" name="questions[]" class="w-full px-4 py-2 border rounded" value="<?= htmlspecialchars($q['question']) ?>" required>
              <button type="button" class="remove-question bg-red-500 text-white px-2 rounded">&times;</button>
            </div>
          <?php endforeach; ?>
        </div>
        <button type="button" id="add-question" class="mt-2 px-4 py-1 bg-[#FDBA17] text-[#2046B3] rounded font-bold hover:bg-[#7C3AED] hover:text-white transition">+ Add Question</button>
      </div>
      <button type="submit" class="bg-[#7C3AED] text-white px-6 py-2 rounded font-bold shadow hover:bg-[#FDBA17] hover:text-[#2046B3] transition">Save Changes</button>
    </form>
  </main>
  <script>
    document.getElementById('add-question').addEventListener('click', function() {
      const list = document.getElementById('questions-list');
      const wrapper = document.createElement('div');
      wrapper.className = 'flex gap-2 mb-2';
      const input = document.createElement('input');
      input.type = 'text';
      input.name = 'questions[]';
      input.className = 'w-full px-4 py-2 border rounded';
      input.placeholder = 'Enter question';
      input.required = true;
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'remove-question bg-red-500 text-white px-2 rounded';
      btn.innerHTML = '&times;';
      btn.onclick = function() { wrapper.remove(); };
      wrapper.appendChild(input);
      wrapper.appendChild(btn);
      list.appendChild(wrapper);
    });
    document.querySelectorAll('.remove-question').forEach(btn => {
      btn.onclick = function() { btn.parentElement.remove(); };
    });
  </script>
</body>
</html>
<?php $conn->close(); ?> 