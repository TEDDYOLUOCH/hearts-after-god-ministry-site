// Real-time User Activity Dashboard
// Live user activity monitoring and analytics

(function() {
  const backend = window.DiscipleshipBackend;
  
  // Activity tracking
  let userActivities = [];
  let onlineUsers = new Set();
  let activityChart = null;

  // Initialize activity dashboard
  function initActivityDashboard() {
    createActivityFeed();
    createOnlineUsersPanel();
    createActivityCharts();
    startActivityPolling();
  }

  // Create real-time activity feed
  function createActivityFeed() {
    const dashboardSection = document.getElementById('dashboard');
    if (!dashboardSection) return;

    // Find the activity section and enhance it
    const activitySection = dashboardSection.querySelector('.bg-white.rounded-2xl.p-6.shadow-lg.border.border-gray-100');
    if (activitySection) {
      const activityHeader = activitySection.querySelector('h3');
      if (activityHeader) {
        activityHeader.innerHTML = `
          <svg class="w-5 h-5 text-[#FDBA17]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19h6a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          Live Activity Feed
          <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
            <span class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></span>
            Live
          </span>
        `;
      }

      // Replace static content with dynamic feed
      const activityContent = activitySection.querySelector('.space-y-4');
      if (activityContent) {
        activityContent.innerHTML = `
          <div id="live-activity-feed" class="space-y-3 max-h-64 overflow-y-auto">
            <!-- Live activities will be populated here -->
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-gray-200">
            <span class="text-xs text-gray-500">Auto-refresh every 5 seconds</span>
            <button id="refresh-activity" class="text-xs text-[#7C3AED] hover:text-[#6D28D9] font-medium">
              Refresh Now
            </button>
          </div>
        `;

        // Add refresh button event
        document.getElementById('refresh-activity').addEventListener('click', updateActivityFeed);
      }
    }
  }

  // Create online users panel
  function createOnlineUsersPanel() {
    const dashboardSection = document.getElementById('dashboard');
    if (!dashboardSection) return;

    // Find the analytics section and enhance it
    const analyticsSection = dashboardSection.querySelectorAll('.bg-white.rounded-2xl.p-6.shadow-lg.border.border-gray-100')[1];
    if (analyticsSection) {
      const analyticsHeader = analyticsSection.querySelector('h3');
      if (analyticsHeader) {
        analyticsHeader.innerHTML = `
          <svg class="w-5 h-5 text-[#7C3AED]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          Live Analytics & Online Users
        `;
      }

      // Replace static content with dynamic content
      const analyticsContent = analyticsSection.querySelector('.space-y-4');
      if (analyticsContent) {
        analyticsContent.innerHTML = `
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-green-600 text-sm font-medium">Online Users</p>
                  <p class="text-2xl font-bold text-green-900" id="online-users-count">0</p>
                </div>
                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                  </svg>
                </div>
              </div>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-blue-600 text-sm font-medium">Active Sessions</p>
                  <p class="text-2xl font-bold text-blue-900" id="active-sessions-count">0</p>
                </div>
                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
              </div>
            </div>
          </div>
          <div id="online-users-list" class="space-y-2 max-h-32 overflow-y-auto">
            <!-- Online users will be listed here -->
          </div>
        `;
      }
    }
  }

  // Create activity charts
  function createActivityCharts() {
    // Create a new section for charts
    const mainContent = document.querySelector('main');
    if (!mainContent) return;

    const chartsSection = document.createElement('section');
    chartsSection.id = 'activity-charts';
    chartsSection.className = 'dashboard-section rounded-2xl shadow-xl p-8';
    chartsSection.innerHTML = `
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-3xl font-bold text-[#2046B3] mb-2 flex items-center gap-3">
            <svg class="w-8 h-8 text-[#7C3AED]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Activity Analytics
          </h2>
          <p class="text-gray-600">Real-time user activity and engagement metrics</p>
        </div>
        <div class="flex gap-3">
          <select id="chart-time-range" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] outline-none">
            <option value="1h">Last Hour</option>
            <option value="24h" selected>Last 24 Hours</option>
            <option value="7d">Last 7 Days</option>
            <option value="30d">Last 30 Days</option>
          </select>
        </div>
      </div>
      
      <div class="grid md:grid-cols-2 gap-8">
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
          <h3 class="text-lg font-bold text-[#2046B3] mb-4">User Activity Timeline</h3>
          <div id="activity-timeline-chart" class="h-64">
            <!-- Chart will be rendered here -->
          </div>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
          <h3 class="text-lg font-bold text-[#2046B3] mb-4">Activity Distribution</h3>
          <div id="activity-distribution-chart" class="h-64">
            <!-- Chart will be rendered here -->
          </div>
        </div>
      </div>
    `;

    // Insert after dashboard section
    const dashboardSection = document.getElementById('dashboard');
    if (dashboardSection) {
      dashboardSection.parentNode.insertBefore(chartsSection, dashboardSection.nextSibling);
    }

    // Add chart time range event listener
    document.getElementById('chart-time-range').addEventListener('change', updateCharts);
  }

  // Update activity feed
  function updateActivityFeed() {
    const feed = document.getElementById('live-activity-feed');
    if (!feed) return;

    const recentActivities = getRecentActivities(10);
    
    if (recentActivities.length === 0) {
      feed.innerHTML = `
        <div class="text-center text-gray-500 py-8">
          <div class="text-4xl mb-2">📊</div>
          <p>No recent activity</p>
        </div>
      `;
      return;
    }

    feed.innerHTML = recentActivities.map(activity => {
      const config = getActivityConfig(activity.type);
      return `
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
          <div class="w-8 h-8 ${config.bgColor} rounded-full flex items-center justify-center">
            <span class="text-white text-sm">${config.icon}</span>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">${activity.message}</p>
            <p class="text-xs text-gray-500">${new Date(activity.timestamp).toLocaleString()}</p>
          </div>
          <div class="text-xs text-gray-400">
            ${getTimeAgo(activity.timestamp)}
          </div>
        </div>
      `;
    }).join('');
  }

  // Update online users
  function updateOnlineUsers() {
    const users = backend.getAllUsers();
    const onlineUsersList = document.getElementById('online-users-list');
    const onlineCount = document.getElementById('online-users-count');
    const activeSessionsCount = document.getElementById('active-sessions-count');
    
    if (!onlineUsersList || !onlineCount || !activeSessionsCount) return;

    const online = users.filter(user => user.isOnline);
    const activeSessions = users.filter(user => 
      new Date(user.lastActivity) > new Date(Date.now() - 5 * 60 * 1000) // Active in last 5 minutes
    );

    onlineCount.textContent = online.length;
    activeSessionsCount.textContent = activeSessions.length;

    if (online.length === 0) {
      onlineUsersList.innerHTML = `
        <div class="text-center text-gray-500 py-4">
          <p class="text-sm">No users online</p>
        </div>
      `;
      return;
    }

    onlineUsersList.innerHTML = online.map(user => `
      <div class="flex items-center gap-3 p-2 bg-green-50 rounded-lg border border-green-200">
        <div class="w-6 h-6 bg-gradient-to-br from-[#7C3AED] to-[#2046B3] rounded-full flex items-center justify-center text-white text-xs font-semibold">
          ${user.profile?.avatar?.initials || user.name.substring(0, 2).toUpperCase()}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 truncate">${user.name}</p>
          <p class="text-xs text-gray-500">${getTimeAgo(user.lastActivity)}</p>
        </div>
        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
      </div>
    `).join('');
  }

  // Get recent activities
  function getRecentActivities(limit = 10) {
    const activities = backend.getRecentActivity(limit);
    return activities.map(activity => ({
      type: activity.type,
      message: getActivityMessage(activity),
      timestamp: activity.timestamp,
      data: activity.data
    }));
  }

  // Get activity message
  function getActivityMessage(activity) {
    switch (activity.type) {
      case 'user_login':
        return `${activity.data.name || 'User'} logged in`;
      case 'user_created':
        return `New user registered: ${activity.data.userData?.name || 'Unknown'}`;
      case 'progress_updated':
        return `${activity.data.userName || 'User'} updated progress`;
      case 'certificate_issued':
        return `Certificate issued to ${activity.data.userName || 'User'}`;
      case 'support_ticket_created':
        return `New support ticket from ${activity.data.userName || 'User'}`;
      default:
        return `Activity: ${activity.type.replace(/_/g, ' ')}`;
    }
  }

  // Get activity configuration
  function getActivityConfig(type) {
    const configs = {
      'user_login': { icon: '🔐', bgColor: 'bg-green-500' },
      'user_created': { icon: '👤', bgColor: 'bg-blue-500' },
      'progress_updated': { icon: '📈', bgColor: 'bg-purple-500' },
      'certificate_issued': { icon: '🎓', bgColor: 'bg-yellow-500' },
      'support_ticket_created': { icon: '🎫', bgColor: 'bg-red-500' }
    };
    return configs[type] || { icon: '📊', bgColor: 'bg-gray-500' };
  }

  // Get time ago
  function getTimeAgo(timestamp) {
    const now = new Date();
    const time = new Date(timestamp);
    const diffMs = now - time;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    return `${diffDays}d ago`;
  }

  // Update charts
  function updateCharts() {
    // In a real implementation, you would use a charting library like Chart.js
    // For now, we'll create simple visualizations
    updateActivityTimeline();
    updateActivityDistribution();
  }

  // Update activity timeline
  function updateActivityTimeline() {
    const chartContainer = document.getElementById('activity-timeline-chart');
    if (!chartContainer) return;

    // Simple timeline visualization
    const activities = getRecentActivities(20);
    const timelineData = activities.reduce((acc, activity) => {
      const hour = new Date(activity.timestamp).getHours();
      acc[hour] = (acc[hour] || 0) + 1;
      return acc;
    }, {});

    const timelineHTML = `
      <div class="flex items-end justify-between h-full gap-1">
        ${Array.from({ length: 24 }, (_, i) => {
          const count = timelineData[i] || 0;
          const height = count > 0 ? Math.max(10, (count / Math.max(...Object.values(timelineData))) * 100) : 0;
          return `
            <div class="flex-1 flex flex-col items-center">
              <div class="w-full bg-[#7C3AED] rounded-t transition-all duration-300" style="height: ${height}%"></div>
              <span class="text-xs text-gray-500 mt-1">${i}</span>
            </div>
          `;
        }).join('')}
      </div>
    `;

    chartContainer.innerHTML = timelineHTML;
  }

  // Update activity distribution
  function updateActivityDistribution() {
    const chartContainer = document.getElementById('activity-distribution-chart');
    if (!chartContainer) return;

    const activities = getRecentActivities(50);
    const distribution = activities.reduce((acc, activity) => {
      acc[activity.type] = (acc[activity.type] || 0) + 1;
      return acc;
    }, {});

    const total = Object.values(distribution).reduce((sum, count) => sum + count, 0);
    
    const distributionHTML = `
      <div class="space-y-3">
        ${Object.entries(distribution).map(([type, count]) => {
          const percentage = total > 0 ? (count / total) * 100 : 0;
          const config = getActivityConfig(type);
          return `
            <div class="flex items-center gap-3">
              <div class="w-4 h-4 ${config.bgColor} rounded"></div>
              <div class="flex-1">
                <div class="flex justify-between text-sm">
                  <span class="font-medium">${type.replace(/_/g, ' ')}</span>
                  <span class="text-gray-500">${count}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                  <div class="bg-[#7C3AED] h-2 rounded-full transition-all duration-300" style="width: ${percentage}%"></div>
                </div>
              </div>
            </div>
          `;
        }).join('')}
      </div>
    `;

    chartContainer.innerHTML = distributionHTML;
  }

  // Start activity polling
  function startActivityPolling() {
    // Update every 5 seconds
    setInterval(() => {
      updateActivityFeed();
      updateOnlineUsers();
      updateCharts();
    }, 5000);

    // Initial update
    updateActivityFeed();
    updateOnlineUsers();
    updateCharts();
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initActivityDashboard);
  } else {
    initActivityDashboard();
  }

  // Export functions for external use
  window.ActivityDashboard = {
    updateActivityFeed,
    updateOnlineUsers,
    updateCharts
  };

})(); 