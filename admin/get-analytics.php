<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['admin'])) {
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}
$host = 'localhost';
$db   = 'hearts_after_god';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
$start = isset($_GET['start']) ? $_GET['start'] : null;
$end = isset($_GET['end']) ? $_GET['end'] : null;
$date_filter = '';
if ($start && $end) {
  $date_filter = "AND created_at >= '$start' AND created_at <= '$end'";
}
// Users
$total_users = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'];
$new_users = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['cnt'];
$active_users = $conn->query("SELECT COUNT(DISTINCT user_id) AS cnt FROM user_module_progress WHERE completed=1 AND updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['cnt'];
// Modules
$module_completions = $conn->query("SELECT COUNT(*) AS cnt FROM user_module_progress WHERE completed=1")->fetch_assoc()['cnt'];
// Assessments
$assessment_submissions = $conn->query("SELECT COUNT(*) AS cnt FROM assessment_results")->fetch_assoc()['cnt'];
$avg_assessment_score = $conn->query("SELECT AVG(score) AS avg FROM assessment_results")->fetch_assoc()['avg'] ?? 0;
// Mentors
$total_mentors = $conn->query("SELECT COUNT(*) AS cnt FROM mentors")->fetch_assoc()['cnt'];
$mentor_assignments = $conn->query("SELECT COUNT(*) AS cnt FROM mentor_assignments")->fetch_assoc()['cnt'];
// Resources
$total_resources = $conn->query("SELECT COUNT(*) AS cnt FROM resources")->fetch_assoc()['cnt'];
$resource_downloads = $conn->query("SELECT SUM(downloads) AS sum FROM resources")->fetch_assoc()['sum'] ?? 0;
// Pathways
$total_pathways = $conn->query("SELECT COUNT(*) AS cnt FROM pathways")->fetch_assoc()['cnt'];
$pathway_enrollments = $conn->query("SELECT COUNT(*) AS cnt FROM user_pathways")->fetch_assoc()['cnt'];
// Graduations
$graduations = $conn->query("SELECT COUNT(*) AS cnt FROM graduations WHERE graduated=1")->fetch_assoc()['cnt'];
// User growth (last 12 months)
$user_growth = [];
for ($i = 11; $i >= 0; $i--) {
  $label = date('M Y', strtotime("-$i months"));
  $from = date('Y-m-01', strtotime("-$i months"));
  $to = date('Y-m-t', strtotime("-$i months"));
  $cnt = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE created_at >= '$from' AND created_at <= '$to'")->fetch_assoc()['cnt'];
  $user_growth[] = ['label' => $label, 'count' => $cnt];
}
// Module completion rates
$modules = $conn->query("SELECT id, title FROM modules ORDER BY id");
$module_completion_rates = [];
while ($m = $modules->fetch_assoc()) {
  $cnt = $conn->query("SELECT COUNT(*) AS cnt FROM user_module_progress WHERE module_id={$m['id']} AND completed=1")->fetch_assoc()['cnt'];
  $module_completion_rates[] = ['label' => $m['title'], 'count' => $cnt];
}
// Assessment scores distribution
$assessment_scores = [];
for ($score = 0; $score <= 100; $score += 10) {
  $cnt = $conn->query("SELECT COUNT(*) AS cnt FROM assessment_results WHERE score >= $score AND score < " . ($score+10))->fetch_assoc()['cnt'];
  $assessment_scores[] = ['label' => "$score-".($score+9), 'count' => $cnt];
}
// Resource downloads chart (last 12 months)
$resource_downloads_chart = [];
for ($i = 11; $i >= 0; $i--) {
  $label = date('M Y', strtotime("-$i months"));
  $from = date('Y-m-01', strtotime("-$i months"));
  $to = date('Y-m-t', strtotime("-$i months"));
  $cnt = $conn->query("SELECT SUM(downloads) AS sum FROM resources WHERE updated_at >= '$from' AND updated_at <= '$to'")->fetch_assoc()['sum'] ?? 0;
  $resource_downloads_chart[] = ['label' => $label, 'count' => $cnt];
}
// Mentor assignments chart
$mentor_assignments_chart = [];
$mentors = $conn->query("SELECT id, name FROM mentors");
while ($m = $mentors->fetch_assoc()) {
  $cnt = $conn->query("SELECT COUNT(*) AS cnt FROM mentor_assignments WHERE mentor_id={$m['id']}")->fetch_assoc()['cnt'];
  $mentor_assignments_chart[] = ['label' => $m['name'], 'count' => $cnt];
}
// Recent activity (last 10)
$recent_activity = [];
$res = $conn->query("SELECT user_id, module_id, completed, updated_at FROM user_module_progress ORDER BY updated_at DESC LIMIT 10");
while ($row = $res->fetch_assoc()) {
  $user = $conn->query("SELECT name FROM users WHERE id={$row['user_id']}")->fetch_assoc()['name'] ?? 'User';
  $module = $conn->query("SELECT title FROM modules WHERE id={$row['module_id']}")->fetch_assoc()['title'] ?? 'Module';
  $recent_activity[] = $user . ' ' . ($row['completed'] ? 'completed' : 'started') . ' ' . $module . ' (' . $row['updated_at'] . ')';
}
// Sample data fallback
if ($total_users == 0) {
  $total_users = 10;
  $new_users = 2;
  $active_users = 5;
  $module_completions = 20;
  $assessment_submissions = 8;
  $avg_assessment_score = 75;
  $total_mentors = 3;
  $mentor_assignments = 8;
  $total_resources = 4;
  $resource_downloads = 12;
  $total_pathways = 2;
  $pathway_enrollments = 6;
  $graduations = 3;
  $user_growth = [
    ['label'=>'Jan','count'=>1],['label'=>'Feb','count'=>1],['label'=>'Mar','count'=>1],['label'=>'Apr','count'=>1],['label'=>'May','count'=>1],['label'=>'Jun','count'=>1],['label'=>'Jul','count'=>1],['label'=>'Aug','count'=>1],['label'=>'Sep','count'=>1],['label'=>'Oct','count'=>1],['label'=>'Nov','count'=>1],['label'=>'Dec','count'=>1]
  ];
  $module_completion_rates = [
    ['label'=>'Module 1','count'=>2],['label'=>'Module 2','count'=>3]
  ];
  $assessment_scores = [
    ['label'=>'0-9','count'=>0],['label'=>'10-19','count'=>0],['label'=>'20-29','count'=>1],['label'=>'30-39','count'=>1],['label'=>'40-49','count'=>2],['label'=>'50-59','count'=>1],['label'=>'60-69','count'=>1],['label'=>'70-79','count'=>1],['label'=>'80-89','count'=>1],['label'=>'90-99','count'=>0],['label'=>'100-109','count'=>1]
  ];
  $resource_downloads_chart = [
    ['label'=>'Jan','count'=>1],['label'=>'Feb','count'=>1],['label'=>'Mar','count'=>1],['label'=>'Apr','count'=>1],['label'=>'May','count'=>1],['label'=>'Jun','count'=>1],['label'=>'Jul','count'=>1],['label'=>'Aug','count'=>1],['label'=>'Sep','count'=>1],['label'=>'Oct','count'=>1],['label'=>'Nov','count'=>1],['label'=>'Dec','count'=>1]
  ];
  $mentor_assignments_chart = [
    ['label'=>'Mentor 1','count'=>2],['label'=>'Mentor 2','count'=>3]
  ];
  $recent_activity = ['Sample user completed Module 1 (2024-01-01)'];
}
echo json_encode([
  'total_users' => $total_users,
  'new_users' => $new_users,
  'active_users' => $active_users,
  'module_completions' => $module_completions,
  'assessment_submissions' => $assessment_submissions,
  'avg_assessment_score' => round($avg_assessment_score,1),
  'total_mentors' => $total_mentors,
  'mentor_assignments' => $mentor_assignments,
  'total_resources' => $total_resources,
  'resource_downloads' => $resource_downloads,
  'total_pathways' => $total_pathways,
  'pathway_enrollments' => $pathway_enrollments,
  'graduations' => $graduations,
  'user_growth' => $user_growth,
  'module_completion_rates' => $module_completion_rates,
  'assessment_scores' => $assessment_scores,
  'resource_downloads_chart' => $resource_downloads_chart,
  'mentor_assignments_chart' => $mentor_assignments_chart,
  'recent_activity' => $recent_activity
]);
$conn->close(); 