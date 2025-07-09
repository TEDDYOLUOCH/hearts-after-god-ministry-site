<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: index.php');
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Analytics Dashboard</title>
  <link href='https://cdn.tailwindcss.com' rel='stylesheet'>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <div class="flex items-center gap-6">
      <a href="dashboard.php" class="font-bold text-[#7C3AED] text-xl">Admin Dashboard</a>
      <a href="analytics.php" class="bg-[#FDBA17] text-[#2046B3] px-4 py-2 rounded font-bold shadow hover:bg-[#7C3AED] hover:text-white transition">Analytics</a>
    </div>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-7xl mx-auto mt-8 bg-white p-8 rounded-xl shadow">
    <h2 class="text-3xl font-bold text-[#7C3AED] mb-6">Analytics Dashboard</h2>
    <!-- Date Range Filter -->
    <form class="flex flex-wrap gap-4 mb-8 items-center">
      <label class="font-semibold">Date Range:</label>
      <input type="date" name="start" class="border rounded px-3 py-1" />
      <span>to</span>
      <input type="date" name="end" class="border rounded px-3 py-1" />
      <button type="submit" class="bg-[#7C3AED] text-white px-4 py-1 rounded font-bold hover:bg-[#FDBA17] hover:text-[#2046B3] transition">Filter</button>
    </form>
    <!-- Metrics Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10">
      <div class="bg-[#F3E8FF] rounded-xl p-6 text-center shadow">
        <div class="text-3xl font-extrabold text-[#7C3AED]" id="total-users">--</div>
        <div class="text-gray-700 mt-2">Total Users</div>
      </div>
      <div class="bg-[#FEF3C7] rounded-xl p-6 text-center shadow">
        <div class="text-3xl font-extrabold text-[#FDBA17]" id="new-users">--</div>
        <div class="text-gray-700 mt-2">New Users</div>
      </div>
      <div class="bg-[#E0E7FF] rounded-xl p-6 text-center shadow">
        <div class="text-3xl font-extrabold text-[#2046B3]" id="active-users">--</div>
        <div class="text-gray-700 mt-2">Active Users</div>
      </div>
      <div class="bg-[#F3E8FF] rounded-xl p-6 text-center shadow">
        <div class="text-3xl font-extrabold text-[#7C3AED]" id="module-completions">--</div>
        <div class="text-gray-700 mt-2">Module Completions</div>
      </div>
      <div class="bg-[#FEF3C7] rounded-xl p-6 text-center shadow">
        <div class="text-3xl font-extrabold text-[#FDBA17]" id="assessment-submissions">--</div>
        <div class="text-gray-700 mt-2">Assessment Submissions</div>
      </div>
      <div class="bg-[#E0E7FF] rounded-xl p-6 text-center shadow">
        <div class="text-3xl font-extrabold text-[#2046B3]" id="avg-assessment-score">--</div>
        <div class="text-gray-700 mt-2">Avg. Assessment Score</div>
      </div>
      <div class="bg-[#F3E8FF] rounded-xl p-6 text-center shadow">
        <div class="text-3xl font-extrabold text-[#7C3AED]" id="total-mentors">--</div>
        <div class="text-gray-700 mt-2">Total Mentors</div>
      </div>
      <div class="bg-[#FEF3C7] rounded-xl p-6 text-center shadow">
        <div class="text-3xl font-extrabold text-[#FDBA17]" id="mentor-assignments">--</div>
        <div class="text-gray-700 mt-2">Mentor Assignments</div>
      </div>
      <div class="bg-[#E0E7FF] rounded-xl p-6 text-center shadow">
        <div class="text-3xl font-extrabold text-[#2046B3]" id="total-resources">--</div>
        <div class="text-gray-700 mt-2">Total Resources</div>
      </div>
      <div class="bg-[#F3E8FF] rounded-xl p-6 text-center shadow">
        <div class="text-3xl font-extrabold text-[#7C3AED]" id="resource-downloads">--</div>
        <div class="text-gray-700 mt-2">Resource Downloads</div>
      </div>
      <div class="bg-[#FEF3C7] rounded-xl p-6 text-center shadow">
        <div class="text-3xl font-extrabold text-[#FDBA17]" id="total-pathways">--</div>
        <div class="text-gray-700 mt-2">Total Pathways</div>
      </div>
      <div class="bg-[#E0E7FF] rounded-xl p-6 text-center shadow">
        <div class="text-3xl font-extrabold text-[#2046B3]" id="pathway-enrollments">--</div>
        <div class="text-gray-700 mt-2">Pathway Enrollments</div>
      </div>
      <div class="bg-[#F3E8FF] rounded-xl p-6 text-center shadow">
        <div class="text-3xl font-extrabold text-[#7C3AED]" id="graduations">--</div>
        <div class="text-gray-700 mt-2">Graduations</div>
      </div>
    </div>
    <!-- Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 my-12">
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-bold text-lg text-[#7C3AED] mb-2">User Growth Over Time</h3>
        <canvas id="userGrowthChart" height="120"></canvas>
      </div>
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-bold text-lg text-[#FDBA17] mb-2">Module Completion Rates</h3>
        <canvas id="moduleCompletionChart" height="120"></canvas>
      </div>
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-bold text-lg text-[#2046B3] mb-2">Assessment Scores Distribution</h3>
        <canvas id="assessmentScoresChart" height="120"></canvas>
      </div>
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-bold text-lg text-[#7C3AED] mb-2">Resource Downloads</h3>
        <canvas id="resourceDownloadsChart" height="120"></canvas>
      </div>
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-bold text-lg text-[#FDBA17] mb-2">Mentor Assignments</h3>
        <canvas id="mentorAssignmentsChart" height="120"></canvas>
      </div>
    </div>
    <!-- Recent Activity Feed -->
    <div class="bg-white rounded-xl shadow p-6 mt-8">
      <h3 class="font-bold text-lg text-[#2046B3] mb-4">Recent Activity</h3>
      <ul id="recent-activity" class="space-y-2">
        <li class="text-gray-500 italic">Loading...</li>
      </ul>
    </div>
  </main>
  <script>
    async function fetchAnalytics(start = '', end = '') {
      let url = 'get-analytics.php';
      if (start && end) url += `?start=${start}&end=${end}`;
      const res = await fetch(url);
      return await res.json();
    }

    function setMetric(id, value) {
      document.getElementById(id).textContent = value !== undefined ? value : '--';
    }

    function setActivity(list) {
      const ul = document.getElementById('recent-activity');
      ul.innerHTML = '';
      if (!list || !list.length) {
        ul.innerHTML = '<li class="text-gray-500 italic">No recent activity.</li>';
        return;
      }
      for (const item of list) {
        const li = document.createElement('li');
        li.textContent = item;
        ul.appendChild(li);
      }
    }

    // Chart.js chart instances
    let userGrowthChart, moduleCompletionChart, assessmentScoresChart, resourceDownloadsChart, mentorAssignmentsChart;

    function updateCharts(data) {
      // User Growth
      const ugLabels = data.user_growth.map(x => x.label);
      const ugData = data.user_growth.map(x => x.count);
      if (!userGrowthChart) {
        userGrowthChart = new Chart(document.getElementById('userGrowthChart').getContext('2d'), {
          type: 'line', data: { labels: ugLabels, datasets: [{ label: 'Users', data: ugData, backgroundColor: '#7C3AED', borderColor: '#7C3AED', fill: false }] }, options: { responsive: true }
        });
      } else {
        userGrowthChart.data.labels = ugLabels;
        userGrowthChart.data.datasets[0].data = ugData;
        userGrowthChart.update();
      }
      // Module Completion
      const mcLabels = data.module_completion_rates.map(x => x.label);
      const mcData = data.module_completion_rates.map(x => x.count);
      if (!moduleCompletionChart) {
        moduleCompletionChart = new Chart(document.getElementById('moduleCompletionChart').getContext('2d'), {
          type: 'bar', data: { labels: mcLabels, datasets: [{ label: 'Completions', data: mcData, backgroundColor: '#FDBA17' }] }, options: { responsive: true }
        });
      } else {
        moduleCompletionChart.data.labels = mcLabels;
        moduleCompletionChart.data.datasets[0].data = mcData;
        moduleCompletionChart.update();
      }
      // Assessment Scores
      const asLabels = data.assessment_scores.map(x => x.label);
      const asData = data.assessment_scores.map(x => x.count);
      if (!assessmentScoresChart) {
        assessmentScoresChart = new Chart(document.getElementById('assessmentScoresChart').getContext('2d'), {
          type: 'bar', data: { labels: asLabels, datasets: [{ label: 'Scores', data: asData, backgroundColor: '#2046B3' }] }, options: { responsive: true }
        });
      } else {
        assessmentScoresChart.data.labels = asLabels;
        assessmentScoresChart.data.datasets[0].data = asData;
        assessmentScoresChart.update();
      }
      // Resource Downloads
      const rdLabels = data.resource_downloads_chart.map(x => x.label);
      const rdData = data.resource_downloads_chart.map(x => x.count);
      if (!resourceDownloadsChart) {
        resourceDownloadsChart = new Chart(document.getElementById('resourceDownloadsChart').getContext('2d'), {
          type: 'line', data: { labels: rdLabels, datasets: [{ label: 'Downloads', data: rdData, backgroundColor: '#7C3AED', borderColor: '#7C3AED', fill: false }] }, options: { responsive: true }
        });
      } else {
        resourceDownloadsChart.data.labels = rdLabels;
        resourceDownloadsChart.data.datasets[0].data = rdData;
        resourceDownloadsChart.update();
      }
      // Mentor Assignments
      const maLabels = data.mentor_assignments_chart.map(x => x.label);
      const maData = data.mentor_assignments_chart.map(x => x.count);
      if (!mentorAssignmentsChart) {
        mentorAssignmentsChart = new Chart(document.getElementById('mentorAssignmentsChart').getContext('2d'), {
          type: 'pie', data: { labels: maLabels, datasets: [{ label: 'Mentors', data: maData, backgroundColor: ['#7C3AED', '#FDBA17', '#2046B3', '#A3A3A3'] }] }, options: { responsive: true }
        });
      } else {
        mentorAssignmentsChart.data.labels = maLabels;
        mentorAssignmentsChart.data.datasets[0].data = maData;
        mentorAssignmentsChart.update();
      }
    }

    async function loadAnalytics(start = '', end = '') {
      // Show loading
      [
        'total-users','new-users','active-users','module-completions','assessment-submissions','avg-assessment-score',
        'total-mentors','mentor-assignments','total-resources','resource-downloads','total-pathways','pathway-enrollments','graduations'
      ].forEach(id => setMetric(id, '...'));
      setActivity(['Loading...']);
      const data = await fetchAnalytics(start, end);
      setMetric('total-users', data.total_users);
      setMetric('new-users', data.new_users);
      setMetric('active-users', data.active_users);
      setMetric('module-completions', data.module_completions);
      setMetric('assessment-submissions', data.assessment_submissions);
      setMetric('avg-assessment-score', data.avg_assessment_score);
      setMetric('total-mentors', data.total_mentors);
      setMetric('mentor-assignments', data.mentor_assignments);
      setMetric('total-resources', data.total_resources);
      setMetric('resource-downloads', data.resource_downloads);
      setMetric('total-pathways', data.total_pathways);
      setMetric('pathway-enrollments', data.pathway_enrollments);
      setMetric('graduations', data.graduations);
      setActivity(data.recent_activity);
      updateCharts(data);
    }

    // Date range filter
    document.querySelector('form').addEventListener('submit', function(e) {
      e.preventDefault();
      const start = this.start.value;
      const end = this.end.value;
      loadAnalytics(start, end);
    });

    // Initial load
    loadAnalytics();
  </script>
</body>
</html> 