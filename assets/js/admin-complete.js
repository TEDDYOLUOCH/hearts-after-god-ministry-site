// Complete Admin Dashboard Functionality
// Handles all admin roles and features

(function() {
  let adminState = {
    currentUser: null,
    isAuthenticated: false,
    lastUpdate: null,
    realTimeEnabled: true
  };

  // Initialize admin dashboard
  function initAdminDashboard() {
    console.log('Initializing complete admin dashboard...');
    
    // Check if backend is available
    if (window.DiscipleshipBackend) {
      console.log('Backend is available');
      console.log('Backend methods:', Object.keys(window.DiscipleshipBackend));
    } else {
      console.error('Backend is not available!');
    }
    
    // Add a small delay to ensure DOM is fully loaded
    setTimeout(() => {
      console.log('DOM should be fully loaded now');
      
      // Load initial data
      loadDashboardStats();
      loadUsers();
      loadCourses();
      loadCertificates();
      loadSupportTickets();
      loadAnalytics();
      
      // Setup event listeners
      setupEventListeners();
      
      // Start real-time updates
      if (adminState.realTimeEnabled) {
        startRealTimeUpdates();
      }
      
      console.log('Admin dashboard fully initialized');
    }, 100);
  }

  // Setup all event listeners
  function setupEventListeners() {
    console.log('Setting up event listeners...');
    
    // Sidebar navigation
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    console.log('Found sidebar links:', sidebarLinks.length);
    
    sidebarLinks.forEach((link, index) => {
      console.log(`Adding click listener to sidebar link ${index}:`, link.textContent.trim());
      link.addEventListener('click', handleNavigation);
    });

    // User management
    const addUserBtn = document.getElementById('add-user-btn');
    if (addUserBtn) {
      console.log('Found add user button');
      addUserBtn.addEventListener('click', openUserModal);
    } else {
      console.log('Add user button not found');
    }
    
    const userSearch = document.getElementById('user-search');
    if (userSearch) userSearch.addEventListener('input', handleUserSearch);
    
    const userFilter = document.getElementById('user-filter');
    if (userFilter) userFilter.addEventListener('change', handleUserFilter);

    // Course management
    const addCourseBtn = document.querySelector('#courses button');
    if (addCourseBtn) {
      console.log('Found add course button');
      addCourseBtn.addEventListener('click', openCourseModal);
    } else {
      console.log('Add course button not found');
    }

    // Certificate management
    const issueCertBtn = document.querySelector('#certificates button');
    if (issueCertBtn) {
      console.log('Found issue certificate button');
      issueCertBtn.addEventListener('click', openCertificateModal);
    } else {
      console.log('Issue certificate button not found');
    }

    // Support management
    const ticketFilter = document.getElementById('ticket-filter');
    if (ticketFilter) ticketFilter.addEventListener('change', handleTicketFilter);

    // Settings
    const exportBtn = document.getElementById('export-data-btn');
    if (exportBtn) exportBtn.addEventListener('click', exportData);
    
    const backupBtn = document.querySelector('#settings button:last-child');
    if (backupBtn) backupBtn.addEventListener('click', backupDatabase);

    // Notifications
    const notificationBell = document.getElementById('notification-bell');
    if (notificationBell) notificationBell.addEventListener('click', toggleNotifications);
    
    const closeNotifications = document.getElementById('close-notifications');
    if (closeNotifications) closeNotifications.addEventListener('click', closeNotifications);

    // Modals
    const userModal = document.getElementById('user-modal');
    if (userModal) {
      userModal.addEventListener('click', function(e) {
        if (e.target === this) closeUserModal();
      });
    }

    const courseModal = document.getElementById('course-modal');
    if (courseModal) {
      courseModal.addEventListener('click', function(e) {
        if (e.target === this) closeCourseModal();
      });
    }

    const certificateModal = document.getElementById('certificate-modal');
    if (certificateModal) {
      certificateModal.addEventListener('click', function(e) {
        if (e.target === this) closeCertificateModal();
      });
    }

    // Forms
    const addUserForm = document.getElementById('add-user-form');
    if (addUserForm) addUserForm.addEventListener('submit', handleAddUser);

    const addCourseForm = document.getElementById('add-course-form');
    if (addCourseForm) addCourseForm.addEventListener('submit', handleAddCourse);

    const issueCertificateForm = document.getElementById('issue-certificate-form');
    if (issueCertificateForm) issueCertificateForm.addEventListener('submit', handleIssueCertificate);

    // Edit User Form
    const editUserForm = document.getElementById('edit-user-form');
    if (editUserForm) editUserForm.addEventListener('submit', handleEditUser);

    // Edit Course Form
    const editCourseForm = document.getElementById('edit-course-form');
    if (editCourseForm) editCourseForm.addEventListener('submit', handleEditCourse);

    // Logout
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) logoutBtn.addEventListener('click', handleLogout);
    
    // Test sidebar button
    const testSidebarBtn = document.getElementById('test-sidebar-btn');
    if (testSidebarBtn) {
      console.log('Found test sidebar button');
      testSidebarBtn.addEventListener('click', function() {
        if (window.AdminComplete && window.AdminComplete.testSidebar) {
          window.AdminComplete.testSidebar();
        }
      });
    } else {
      console.log('Test sidebar button not found');
    }
    
    // Reply Ticket Form
    const replyTicketForm = document.getElementById('reply-ticket-form');
    if (replyTicketForm) replyTicketForm.addEventListener('submit', handleReplyTicket);
    
    // Reset Password Modal
    const resetPasswordModal = document.getElementById('reset-password-modal');
    if (resetPasswordModal) {
      resetPasswordModal.addEventListener('click', function(e) {
        if (e.target === this) closeResetPasswordModal();
      });
    }
    
    // Reset Password Form
    const resetPasswordForm = document.getElementById('reset-password-form');
    if (resetPasswordForm) resetPasswordForm.addEventListener('submit', handleResetPassword);
    
    // Reset Password Method Toggle
    const resetMethodRadios = document.querySelectorAll('input[name="resetMethod"]');
    resetMethodRadios.forEach(radio => {
      radio.addEventListener('change', handleResetMethodChange);
    });
    
    console.log('Event listeners setup complete');
  }

  // Handle navigation
  function handleNavigation(e) {
    console.log('Navigation clicked:', e.target.textContent.trim());
    e.preventDefault();
    
    // Update active link
    document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
    this.classList.add('active');
    
    // Show/hide sections
    const targetId = this.getAttribute('href').substring(1);
    console.log('Target section:', targetId);
    showSection(targetId);
  }

  // Show specific section
  function showSection(sectionId) {
    console.log('Showing section:', sectionId);
    
    document.querySelectorAll('.dashboard-section').forEach(section => {
      section.classList.add('hidden');
    });
    
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
      targetSection.classList.remove('hidden');
      console.log('Section shown:', sectionId);
      loadSectionData(sectionId);
    } else {
      console.log('Section not found:', sectionId);
    }
  }

  // Load section data
  function loadSectionData(sectionId) {
    switch (sectionId) {
      case 'dashboard':
        loadDashboardStats();
        updateActivityFeed();
        updateOnlineUsers();
        break;
      case 'users':
        loadUsers();
        break;
      case 'courses':
        loadCourses();
        break;
      case 'certificates':
        loadCertificates();
        break;
      case 'support':
        loadSupportTickets();
        break;
      case 'analytics':
        loadAnalytics();
        break;
      case 'settings':
        loadSettings();
        break;
    }
  }

  // Dashboard Stats
  function loadDashboardStats() {
    if (!window.DiscipleshipBackend) return;
    
    const users = window.DiscipleshipBackend.getAllUsers();
    const courses = window.DiscipleshipBackend.getAllCourses();
    const certificates = window.DiscipleshipBackend.getAllCertificates();
    const tickets = window.DiscipleshipBackend.getAllSupportTickets();

    // Update stats
    const totalUsers = document.getElementById('total-users');
    if (totalUsers) totalUsers.textContent = users.length;
    
    const activeCourses = document.getElementById('active-courses');
    if (activeCourses) activeCourses.textContent = courses.length;
    
    const certificatesIssued = document.getElementById('certificates-issued');
    if (certificatesIssued) certificatesIssued.textContent = certificates.length;
    
    const supportTickets = document.getElementById('support-tickets');
    if (supportTickets) supportTickets.textContent = tickets.filter(t => t.status === 'Open').length;
  }

  // Update activity feed
  function updateActivityFeed() {
    const feed = document.getElementById('live-activity-feed');
    if (!feed) return;

    const activities = getRecentActivities(8);
    
    if (activities.length === 0) {
      feed.innerHTML = `
        <div class="text-center text-gray-500 py-8">
          <i class="fas fa-info-circle text-2xl mb-2"></i>
          <p>No recent activity</p>
        </div>
      `;
      return;
    }

    feed.innerHTML = activities.map(activity => `
      <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
        <div class="w-8 h-8 bg-gradient-to-br from-[#7C3AED] to-[#2046B3] rounded-full flex items-center justify-center text-white text-sm">
          ${activity.icon}
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-900">${activity.message}</p>
          <p class="text-xs text-gray-500">${getTimeAgo(activity.timestamp)}</p>
        </div>
      </div>
    `).join('');
  }

  // Update online users
  function updateOnlineUsers() {
    if (!window.DiscipleshipBackend) return;
    
    const users = window.DiscipleshipBackend.getAllUsers();
    const onlineUsers = users.filter(user => user.isOnline);
    const onlineList = document.getElementById('online-users-list');
    const onlineCount = document.getElementById('online-count');
    
    if (onlineCount) {
      onlineCount.textContent = `(${onlineUsers.length} online)`;
    }

    if (!onlineList) return;

    if (onlineUsers.length === 0) {
      onlineList.innerHTML = `
        <div class="text-center text-gray-500 py-8">
          <i class="fas fa-users text-2xl mb-2"></i>
          <p>No users online</p>
        </div>
      `;
      return;
    }

    onlineList.innerHTML = onlineUsers.map(user => `
      <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
        <div class="w-8 h-8 bg-gradient-to-br from-[#7C3AED] to-[#2046B3] rounded-full flex items-center justify-center text-white text-sm font-bold">
          ${user.name.charAt(0).toUpperCase()}
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-900">${user.name}</p>
          <p class="text-xs text-gray-500">${user.role}</p>
        </div>
        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
      </div>
    `).join('');
  }

  // Load users
  function loadUsers() {
    if (!window.DiscipleshipBackend) return;
    
    const users = window.DiscipleshipBackend.getAllUsers();
    const tbody = document.getElementById('user-table-body');
    if (!tbody) return;

    tbody.innerHTML = users.map(user => `
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="flex items-center">
            <div class="w-8 h-8 bg-gradient-to-br from-[#7C3AED] to-[#2046B3] rounded-full flex items-center justify-center text-white text-sm font-bold">
              ${user.name.charAt(0).toUpperCase()}
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">${user.name}</div>
              <div class="text-sm text-gray-500">${user.email}</div>
            </div>
          </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${user.isOnline ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
            ${user.isOnline ? 'Online' : 'Offline'}
          </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
          ${user.role}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
          ${getTimeAgo(user.lastActivity || user.joinedDate)}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
          <div class="flex items-center gap-2">
            <button onclick="openEditUserModal('${user.id}')" class="p-2 text-[#FDBA17] hover:bg-[#FDBA17]/10 rounded-lg transition-colors" title="Edit User">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </button>
            <button onclick="openResetPasswordModal('${user.id}')" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Reset Password">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
              </svg>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  // Load courses
  function loadCourses() {
    if (!window.DiscipleshipBackend) return;
    
    const courses = window.DiscipleshipBackend.getAllCourses();
    const grid = document.getElementById('courses-grid');
    if (!grid) return;

    grid.innerHTML = courses.map(course => `
      <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-bold text-[#2046B3]">${course.title}</h3>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
            Active
          </span>
        </div>
        <p class="text-gray-600 mb-4">${course.description}</p>
        <div class="flex items-center justify-between text-sm text-gray-500">
          <span>${course.lessons?.length || 0} lessons</span>
          <span>${course.duration || 'N/A'}</span>
        </div>
        <div class="mt-4 flex gap-2">
          <button onclick="editCourse('${course.id}')" class="flex-1 px-3 py-2 bg-[#7C3AED] text-white rounded-lg hover:bg-[#6D28D9] text-sm">
            Edit
          </button>
          <button onclick="deleteCourse('${course.id}')" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
            Delete
          </button>
        </div>
      </div>
    `).join('');
  }

  // Load certificates
  function loadCertificates() {
    if (!window.DiscipleshipBackend) return;
    
    const certificates = window.DiscipleshipBackend.getAllCertificates();
    const tbody = document.getElementById('certificate-table-body');
    if (!tbody) return;

    tbody.innerHTML = certificates.map(cert => `
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
          ${cert.id}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
          ${cert.studentName}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
          ${cert.courseName}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
          ${new Date(cert.issuedDate).toLocaleDateString()}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
            Valid
          </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
          <button onclick="downloadCertificate('${cert.id}')" class="text-[#7C3AED] hover:text-[#6D28D9] mr-3">Download</button>
          <button onclick="revokeCertificate('${cert.id}')" class="text-red-600 hover:text-red-900">Revoke</button>
        </td>
      </tr>
    `).join('');
  }

  // Load support tickets
  function loadSupportTickets() {
    if (!window.DiscipleshipBackend) return;
    
    const tickets = window.DiscipleshipBackend.getAllSupportTickets();
    const tbody = document.getElementById('support-table-body');
    if (!tbody) return;

    tbody.innerHTML = tickets.map(ticket => `
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
          ${ticket.id}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
          ${ticket.user}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
          ${ticket.subject}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getPriorityColor(ticket.priority)}">
            ${ticket.priority}
          </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(ticket.status)}">
            ${ticket.status}
          </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
          <button onclick="viewTicketDetails('${ticket.id}')" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
          <button onclick="replyToTicket('${ticket.id}')" class="text-[#7C3AED] hover:text-[#6D28D9] mr-3">Reply</button>
          <button onclick="resolveTicket('${ticket.id}')" class="text-green-600 hover:text-green-900">Resolve</button>
        </td>
      </tr>
    `).join('');
  }

  // Load analytics
  function loadAnalytics() {
    updateActivityChart();
    updateCompletionChart();
  }

  // Load settings
  function loadSettings() {
    // Settings are already loaded in HTML
    console.log('Settings loaded');
  }

  // User management functions
  function openUserModal() {
    const modal = document.getElementById('user-modal');
    if (modal) modal.classList.remove('hidden');
  }

  function closeUserModal() {
    const modal = document.getElementById('user-modal');
    if (modal) modal.classList.add('hidden');
  }

  function handleAddUser(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const userData = {
      name: formData.get('name'),
      email: formData.get('email'),
      role: formData.get('role'),
      joinedDate: new Date().toISOString()
    };
    
    if (window.DiscipleshipBackend) {
      window.DiscipleshipBackend.createUser(userData);
      loadUsers();
      loadDashboardStats();
    }
    
    closeUserModal();
    e.target.reset();
  }

  function handleUserSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#user-table-body tr');
    
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
  }

  function handleUserFilter(e) {
    const filter = e.target.value;
    // Implement user filtering logic
    console.log('Filtering users by:', filter);
  }

  function handleTicketFilter(e) {
    const filter = e.target.value;
    // Implement ticket filtering logic
    console.log('Filtering tickets by:', filter);
  }

  // Notification functions
  function toggleNotifications() {
    const panel = document.getElementById('notification-panel');
    if (panel) panel.classList.toggle('hidden');
  }

  function closeNotifications() {
    const panel = document.getElementById('notification-panel');
    if (panel) panel.classList.add('hidden');
  }

  // Logout function
  function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
      localStorage.removeItem('discipleship_logged_in_user');
      window.location.href = 'discipleship-login.html';
    }
  }

  // Export function
  function exportData() {
    if (!window.DiscipleshipBackend) return;
    
    const data = {
      users: window.DiscipleshipBackend.getAllUsers(),
      courses: window.DiscipleshipBackend.getAllCourses(),
      certificates: window.DiscipleshipBackend.getAllCertificates(),
      tickets: window.DiscipleshipBackend.getAllSupportTickets(),
      exportDate: new Date().toISOString()
    };

    const dataStr = JSON.stringify(data, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(dataBlob);
    link.download = `admin-export-${new Date().toISOString().split('T')[0]}.json`;
    link.click();
  }

  // Backup function
  function backupDatabase() {
    alert('Database backup functionality would be implemented here.');
  }

  // Chart functions
  function updateActivityChart() {
    const chart = document.getElementById('activity-chart');
    if (!chart) return;

    chart.innerHTML = `
      <div class="flex items-end justify-between h-full gap-1">
        ${Array.from({ length: 24 }, (_, i) => {
          const height = Math.random() * 100;
          return `
            <div class="flex-1 flex flex-col items-center">
              <div class="w-full bg-[#7C3AED] rounded-t transition-all duration-300" style="height: ${height}%"></div>
              <span class="text-xs text-gray-500 mt-1">${i}</span>
            </div>
          `;
        }).join('')}
      </div>
    `;
  }

  function updateCompletionChart() {
    const chart = document.getElementById('completion-chart');
    if (!chart) return;

    chart.innerHTML = `
      <div class="space-y-3">
        <div class="flex items-center gap-3">
          <div class="w-4 h-4 bg-[#7C3AED] rounded"></div>
          <div class="flex-1">
            <div class="flex justify-between text-sm">
              <span class="font-medium">Completed</span>
              <span class="text-gray-500">65%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
              <div class="bg-[#7C3AED] h-2 rounded-full transition-all duration-300" style="width: 65%"></div>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <div class="w-4 h-4 bg-[#FDBA17] rounded"></div>
          <div class="flex-1">
            <div class="flex justify-between text-sm">
              <span class="font-medium">In Progress</span>
              <span class="text-gray-500">25%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
              <div class="bg-[#FDBA17] h-2 rounded-full transition-all duration-300" style="width: 25%"></div>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <div class="w-4 h-4 bg-gray-400 rounded"></div>
          <div class="flex-1">
            <div class="flex justify-between text-sm">
              <span class="font-medium">Not Started</span>
              <span class="text-gray-500">10%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
              <div class="bg-gray-400 h-2 rounded-full transition-all duration-300" style="width: 10%"></div>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  // Start real-time updates
  function startRealTimeUpdates() {
    setInterval(() => {
      loadDashboardStats();
      updateActivityFeed();
      updateOnlineUsers();
    }, 5000);
  }

  // Utility functions
  function getRecentActivities(limit = 8) {
    return [
      { message: 'New user registered', icon: '👤', timestamp: Date.now() - 1000 },
      { message: 'Course completed', icon: '📚', timestamp: Date.now() - 5000 },
      { message: 'Certificate issued', icon: '🏆', timestamp: Date.now() - 10000 },
      { message: 'Support ticket resolved', icon: '✅', timestamp: Date.now() - 15000 }
    ].slice(0, limit);
  }

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

  function getPriorityColor(priority) {
    switch (priority?.toLowerCase()) {
      case 'urgent': return 'bg-red-100 text-red-800';
      case 'high': return 'bg-orange-100 text-orange-800';
      case 'medium': return 'bg-yellow-100 text-yellow-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  }

  function getStatusColor(status) {
    switch (status?.toLowerCase()) {
      case 'open': return 'bg-red-100 text-red-800';
      case 'in progress': return 'bg-yellow-100 text-yellow-800';
      case 'resolved': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  }

  // Edit User Modal Functions
  function openEditUserModal(userId) {
    const modal = document.getElementById('edit-user-modal');
    const form = document.getElementById('edit-user-form');
    if (!modal || !form || !window.DiscipleshipBackend) return;
    const user = window.DiscipleshipBackend.getAllUsers().find(u => u.id === userId);
    if (!user) return;
    form.elements['id'].value = user.id;
    form.elements['name'].value = user.name;
    form.elements['email'].value = user.email;
    form.elements['role'].value = user.role;
    modal.classList.remove('hidden');
  }
  function closeEditUserModal() {
    const modal = document.getElementById('edit-user-modal');
    if (modal) modal.classList.add('hidden');
  }
  function handleEditUser(e) {
    e.preventDefault();
    const form = e.target;
    const userId = form.elements['id'].value;
    const name = form.elements['name'].value;
    const email = form.elements['email'].value;
    const role = form.elements['role'].value;
    if (window.DiscipleshipBackend) {
      window.DiscipleshipBackend.updateUser(userId, { name, email, role });
      loadUsers();
      loadDashboardStats();
    }
    closeEditUserModal();
  }

  // Edit Course Modal Functions
  function openEditCourseModal(courseId) {
    const modal = document.getElementById('edit-course-modal');
    const form = document.getElementById('edit-course-form');
    if (!modal || !form || !window.DiscipleshipBackend) return;
    const course = window.DiscipleshipBackend.getAllCourses().find(c => c.id === courseId);
    if (!course) return;
    form.elements['id'].value = course.id;
    form.elements['title'].value = course.title;
    form.elements['description'].value = course.description || '';
    form.elements['duration'].value = course.duration || '';
    modal.classList.remove('hidden');
  }
  function closeEditCourseModal() {
    const modal = document.getElementById('edit-course-modal');
    if (modal) modal.classList.add('hidden');
  }
  function handleEditCourse(e) {
    e.preventDefault();
    const form = e.target;
    const courseId = form.elements['id'].value;
    const title = form.elements['title'].value;
    const description = form.elements['description'].value;
    const duration = form.elements['duration'].value;
    if (window.DiscipleshipBackend) {
      window.DiscipleshipBackend.updateCourse(courseId, { title, description, duration });
      loadCourses();
      loadDashboardStats();
    }
    closeEditCourseModal();
  }

  // Global admin functions
  window.editUser = function(userId) {
    openEditUserModal(userId);
  };

  window.deleteUser = function(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
      console.log('Delete user:', userId);
      // Implement delete user logic
    }
  };

  window.editCourse = function(courseId) {
    openEditCourseModal(courseId);
  };

  window.deleteCourse = function(courseId) {
    if (confirm('Are you sure you want to delete this course?')) {
      console.log('Delete course:', courseId);
      // Implement delete course logic
    }
  };

  window.downloadCertificate = function(certId) {
    console.log('Downloading certificate:', certId);
    
    if (!window.DiscipleshipBackend) {
      alert('Backend not available');
      return;
    }
    
    const certificates = window.DiscipleshipBackend.getAllCertificates();
    const certificate = certificates.find(c => c.id === certId);
    
    if (!certificate) {
      alert('Certificate not found');
      return;
    }
    
    // Generate certificate content
    const certificateContent = generateCertificateHTML(certificate);
    
    // Create and download the certificate
    downloadCertificateAsPDF(certificateContent, certificate);
  };

  window.revokeCertificate = function(certId) {
    if (confirm('Are you sure you want to revoke this certificate?')) {
      console.log('Revoke certificate:', certId);
      // Implement revoke certificate logic
    }
  };

  window.replyToTicket = function(ticketId) {
    openReplyTicketModal(ticketId);
  };

  window.resolveTicket = function(ticketId) {
    if (confirm('Mark this ticket as resolved?')) {
      if (window.DiscipleshipBackend) {
        window.DiscipleshipBackend.updateSupportTicket(ticketId, {
          status: 'resolved',
          resolvedBy: 'Admin',
          resolvedDate: new Date().toISOString()
        });
        loadSupportTickets();
        loadDashboardStats();
      }
    }
  };

  window.viewTicketDetails = function(ticketId) {
    openTicketDetailsModal(ticketId);
  };

  // Course management functions
  function openCourseModal() {
    const modal = document.getElementById('course-modal');
    if (modal) modal.classList.remove('hidden');
  }

  function closeCourseModal() {
    const modal = document.getElementById('course-modal');
    if (modal) modal.classList.add('hidden');
  }

  // Certificate management functions
  function openCertificateModal() {
    const modal = document.getElementById('certificate-modal');
    if (modal) {
      modal.classList.remove('hidden');
      populateCertificateModal();
    }
  }

  function closeCertificateModal() {
    const modal = document.getElementById('certificate-modal');
    if (modal) modal.classList.add('hidden');
  }

  function populateCertificateModal() {
    if (!window.DiscipleshipBackend) return;
    
    const students = window.DiscipleshipBackend.getAllUsers().filter(u => u.role === 'student');
    const courses = window.DiscipleshipBackend.getAllCourses();
    
    const studentSelect = document.querySelector('#certificate-modal select[name="studentId"]');
    const courseSelect = document.querySelector('#certificate-modal select[name="courseId"]');
    
    if (studentSelect) {
      studentSelect.innerHTML = '<option value="">Select Student</option>' + 
        students.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
    }
    
    if (courseSelect) {
      courseSelect.innerHTML = '<option value="">Select Course</option>' + 
        courses.map(c => `<option value="${c.id}">${c.title}</option>`).join('');
    }
  }

  function handleAddCourse(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const courseData = {
      title: formData.get('title'),
      description: formData.get('description'),
      duration: formData.get('duration'),
      createdDate: new Date().toISOString()
    };
    
    if (window.DiscipleshipBackend) {
      window.DiscipleshipBackend.createCourse(courseData);
      loadCourses();
      loadDashboardStats();
    }
    
    closeCourseModal();
    e.target.reset();
  }

  function handleIssueCertificate(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const studentId = formData.get('studentId');
    const courseId = formData.get('courseId');
    const completionDate = formData.get('completionDate');
    
    if (!studentId || !courseId || !completionDate) {
      alert('Please fill in all fields');
      return;
    }
    
    if (window.DiscipleshipBackend) {
      const students = window.DiscipleshipBackend.getAllUsers();
      const courses = window.DiscipleshipBackend.getAllCourses();
      
      const student = students.find(s => s.id === studentId);
      const course = courses.find(c => c.id === courseId);
      
      if (student && course) {
        const certificateData = {
          studentId: studentId,
          studentName: student.name,
          courseId: courseId,
          courseName: course.title,
          issuedDate: new Date().toISOString(),
          completionDate: completionDate
        };
        
        window.DiscipleshipBackend.createCertificate(certificateData);
        loadCertificates();
        loadDashboardStats();
      }
    }
    
    closeCertificateModal();
    e.target.reset();
  }

  // Certificate generation functions
  function getCertificateSignatures(certificate) {
    // Default signatures - can be customized based on course or certificate type
    const signatures = [
      {
        name: "Cyrilla Chikamai",
        title: "Founder & Director",
        organization: "Hearts After God Ministry"
      },
      {
        name: "Pastor John Doe",
        title: "Course Instructor",
        organization: "Discipleship Training Program"
      },
      {
        name: "Dr. Sarah Johnson",
        title: "Academic Director",
        organization: "Ministry Education"
      }
    ];
    
    // You can customize signatures based on course type
    if (certificate.courseName && certificate.courseName.toLowerCase().includes('leadership')) {
      signatures[1] = {
        name: "Pastor Michael Smith",
        title: "Leadership Instructor",
        organization: "Ministry Leadership Program"
      };
    }
    
    return signatures;
  }

  function generateCertificateHTML(certificate) {
    const currentDate = new Date().toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
    
    return `
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="UTF-8">
        <title>Certificate of Completion</title>
        <style>
          body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 40px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
          }
          .certificate {
            background: white;
            border: 20px solid #7C3AED;
            border-radius: 15px;
            padding: 60px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 800px;
            position: relative;
            overflow: hidden;
          }
          .certificate::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23f0f0f0"/><circle cx="75" cy="75" r="1" fill="%23f0f0f0"/><circle cx="50" cy="10" r="0.5" fill="%23f0f0f0"/><circle cx="10" cy="60" r="0.5" fill="%23f0f0f0"/><circle cx="90" cy="40" r="0.5" fill="%23f0f0f0"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
            pointer-events: none;
          }
          .header {
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
          }
          .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
          }
          .ministry-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            border: 3px solid #FDBA17;
            overflow: hidden;
          }
          .ministry-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
          }
          .ministry-text {
            text-align: center;
          }
          .ministry-name {
            font-size: 24px;
            font-weight: bold;
            color: #7C3AED;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 2px;
          }
          .ministry-subtitle {
            font-size: 16px;
            color: #2046B3;
            margin-bottom: 5px;
            font-weight: 500;
          }
          .ministry-tagline {
            font-size: 12px;
            color: #666;
            font-style: italic;
          }
          .certificate-title {
            font-size: 36px;
            font-weight: bold;
            color: #2046B3;
            margin: 40px 0;
            text-transform: uppercase;
            letter-spacing: 3px;
            border-bottom: 3px solid #FDBA17;
            padding-bottom: 20px;
            display: inline-block;
          }
          .certificate-text {
            font-size: 18px;
            line-height: 1.6;
            color: #333;
            margin: 30px 0;
          }
          .student-name {
            font-size: 28px;
            font-weight: bold;
            color: #7C3AED;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
          }
          .course-name {
            font-size: 22px;
            font-weight: bold;
            color: #2046B3;
            margin: 20px 0;
            font-style: italic;
          }
          .completion-date {
            font-size: 16px;
            color: #666;
            margin: 30px 0;
          }
          .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            padding-top: 40px;
            border-top: 2px solid #ddd;
            gap: 20px;
          }
          .signature {
            text-align: center;
            flex: 1;
            min-width: 0;
          }
          .signature-line {
            width: 150px;
            height: 2px;
            background: #333;
            margin: 10px auto;
          }
          .signature-name {
            font-weight: bold;
            color: #2046B3;
            margin-bottom: 5px;
            font-size: 14px;
          }
          .signature-title {
            font-size: 11px;
            color: #666;
            line-height: 1.3;
          }
          .certificate-number {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 12px;
            color: #999;
          }
          .qr-code-section {
            position: absolute;
            bottom: 20px;
            left: 20px;
            text-align: center;
          }
          .qr-code-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
            font-weight: bold;
          }
          .qr-code {
            width: 80px;
            height: 80px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
          }
          .seal {
            position: absolute;
            top: 50%;
            right: 40px;
            transform: translateY(-50%);
            width: 80px;
            height: 80px;
            border: 3px solid #FDBA17;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(45deg, #7C3AED, #2046B3);
            color: white;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            line-height: 1.2;
          }
          .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(124, 58, 237, 0.05);
            font-weight: bold;
            pointer-events: none;
            z-index: -1;
          }
        </style>
      </head>
      <body>
        <div class="certificate">
          <div class="watermark">HAGM</div>
          <div class="seal">HAGM<br>SEAL</div>
          
          <div class="header">
            <div class="logo-container">
              <div class="ministry-logo">
                <img src="assets/images/WhatsApp Image 2025-07-04 at 17.27.34_37ced412.jpg" alt="Ministry Logo">
              </div>
            </div>
            <div class="ministry-text">
              <div class="ministry-name">Hearts After God Ministry</div>
              <div class="ministry-subtitle">Discipleship Training Program</div>
              <div class="ministry-tagline">Empowering believers to grow in faith and knowledge</div>
            </div>
          </div>
          
          <div class="certificate-title">Certificate of Completion</div>
          
          <div class="certificate-text">
            This is to certify that
          </div>
          
          <div class="student-name">${certificate.studentName}</div>
          
          <div class="certificate-text">
            has successfully completed the course
          </div>
          
          <div class="course-name">${certificate.courseName}</div>
          
          <div class="completion-date">
            Completed on: ${new Date(certificate.issuedDate).toLocaleDateString('en-US', {
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            })}
          </div>
          
          <div class="signature-section">
            ${getCertificateSignatures(certificate).map(signature => `
              <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-name">${signature.name}</div>
                <div class="signature-title">${signature.title}</div>
                <div class="signature-title">${signature.organization}</div>
              </div>
            `).join('')}
          </div>
          
          <div class="certificate-number">
            Certificate ID: ${certificate.id}
          </div>
          
          <div class="qr-code-section">
            <div class="qr-code-label">Scan to Verify</div>
            <!-- QR_CODE_PLACEHOLDER -->
          </div>
        </div>
      </body>
      </html>
    `;
  }

  function downloadCertificateAsPDF(certificateHTML, certificate) {
    // Add QR code to the certificate
    const qrCodeData = `https://heartsaftergod.org/verify/${certificate.id}`;
    const qrCodeURL = `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(qrCodeData)}`;
    
    // Replace placeholder with actual QR code
    const certificateWithQR = certificateHTML.replace(
      '<!-- QR_CODE_PLACEHOLDER -->',
      `<img src="${qrCodeURL}" alt="Certificate QR Code" style="width: 80px; height: 80px; border: 1px solid #ddd;">`
    );
    
    // Create a new window with the certificate content
    const printWindow = window.open('', '_blank');
    printWindow.document.write(certificateWithQR);
    printWindow.document.close();
    
    // Wait for content to load, then print
    printWindow.onload = function() {
      printWindow.print();
      
      // Also provide a download option
      const link = document.createElement('a');
      link.href = 'data:text/html;charset=utf-8,' + encodeURIComponent(certificateWithQR);
      link.download = `certificate_${certificate.studentName.replace(/\s+/g, '_')}_${certificate.courseName.replace(/\s+/g, '_')}.html`;
      link.click();
    };
  }

  // Support Ticket Modal Functions
  function openReplyTicketModal(ticketId) {
    const modal = document.getElementById('reply-ticket-modal');
    const form = document.getElementById('reply-ticket-form');
    const detailsDiv = document.getElementById('ticket-details');
    
    if (!modal || !form || !window.DiscipleshipBackend) return;
    
    const tickets = window.DiscipleshipBackend.getAllSupportTickets();
    const ticket = tickets.find(t => t.id === ticketId);
    
    if (!ticket) return;
    
    // Populate ticket details
    detailsDiv.innerHTML = `
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div><strong>Ticket ID:</strong> ${ticket.id}</div>
        <div><strong>User:</strong> ${ticket.user}</div>
        <div><strong>Subject:</strong> ${ticket.subject}</div>
        <div><strong>Priority:</strong> <span class="px-2 py-1 rounded text-xs ${getPriorityColor(ticket.priority)}">${ticket.priority}</span></div>
        <div><strong>Status:</strong> <span class="px-2 py-1 rounded text-xs ${getStatusColor(ticket.status)}">${ticket.status}</span></div>
        <div><strong>Created:</strong> ${new Date(ticket.createdDate).toLocaleDateString()}</div>
      </div>
      <div class="mt-3">
        <strong>Message:</strong>
        <p class="text-gray-700 mt-1">${ticket.message || 'No message provided'}</p>
      </div>
    `;
    
    // Set form values
    form.elements['ticketId'].value = ticketId;
    form.elements['status'].value = ticket.status;
    
    modal.classList.remove('hidden');
  }

  function closeReplyTicketModal() {
    const modal = document.getElementById('reply-ticket-modal');
    if (modal) modal.classList.add('hidden');
  }

  function handleReplyTicket(e) {
    e.preventDefault();
    const form = e.target;
    const ticketId = form.elements['ticketId'].value;
    const response = form.elements['response'].value;
    const status = form.elements['status'].value;
    
    if (window.DiscipleshipBackend) {
      window.DiscipleshipBackend.updateSupportTicket(ticketId, {
        status: status,
        adminResponse: response,
        respondedBy: 'Admin',
        responseDate: new Date().toISOString()
      });
      loadSupportTickets();
      loadDashboardStats();
    }
    
    closeReplyTicketModal();
    form.reset();
  }

  function openTicketDetailsModal(ticketId) {
    const modal = document.getElementById('ticket-details-modal');
    const detailsDiv = document.getElementById('full-ticket-details');
    
    if (!modal || !window.DiscipleshipBackend) return;
    
    const tickets = window.DiscipleshipBackend.getAllSupportTickets();
    const ticket = tickets.find(t => t.id === ticketId);
    
    if (!ticket) return;
    
    // Populate full ticket details
    detailsDiv.innerHTML = `
      <div class="bg-gray-50 p-4 rounded-lg">
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div><strong>Ticket ID:</strong> ${ticket.id}</div>
          <div><strong>User:</strong> ${ticket.user}</div>
          <div><strong>Subject:</strong> ${ticket.subject}</div>
          <div><strong>Priority:</strong> <span class="px-2 py-1 rounded text-xs ${getPriorityColor(ticket.priority)}">${ticket.priority}</span></div>
          <div><strong>Status:</strong> <span class="px-2 py-1 rounded text-xs ${getStatusColor(ticket.status)}">${ticket.status}</span></div>
          <div><strong>Created:</strong> ${new Date(ticket.createdDate).toLocaleDateString()}</div>
        </div>
        
        <div class="mb-4">
          <strong>User Message:</strong>
          <p class="text-gray-700 mt-1 p-3 bg-white rounded border">${ticket.message || 'No message provided'}</p>
        </div>
        
        ${ticket.adminResponse ? `
        <div>
          <strong>Admin Response:</strong>
          <p class="text-gray-700 mt-1 p-3 bg-blue-50 rounded border">${ticket.adminResponse}</p>
          <p class="text-xs text-gray-500 mt-1">Responded by: ${ticket.respondedBy} on ${new Date(ticket.responseDate).toLocaleDateString()}</p>
        </div>
        ` : '<p class="text-gray-500 italic">No response yet</p>'}
      </div>
    `;
    
    // Set up reply button
    const replyBtn = document.getElementById('reply-from-details');
    replyBtn.onclick = () => {
      closeTicketDetailsModal();
      openReplyTicketModal(ticketId);
    };
    
    modal.classList.remove('hidden');
  }

  function closeTicketDetailsModal() {
    const modal = document.getElementById('ticket-details-modal');
    if (modal) modal.classList.add('hidden');
  }

  // Reset Password Functions
  function openResetPasswordModal(userId) {
    const modal = document.getElementById('reset-password-modal');
    const form = document.getElementById('reset-password-form');
    
    if (!modal || !form || !window.DiscipleshipBackend) return;
    
    const users = window.DiscipleshipBackend.getAllUsers();
    const user = users.find(u => u.id === userId);
    
    if (!user) return;
    
    // Populate user info
    document.getElementById('reset-user-id').value = userId;
    document.getElementById('reset-user-name').textContent = user.name;
    document.getElementById('reset-user-email').textContent = user.email;
    
    // Generate initial password
    generateNewPassword();
    
    // Show generated password section by default
    showGeneratedPasswordSection();
    
    modal.classList.remove('hidden');
  }

  function closeResetPasswordModal() {
    const modal = document.getElementById('reset-password-modal');
    const form = document.getElementById('reset-password-form');
    
    if (modal) modal.classList.add('hidden');
    if (form) form.reset();
    
    // Reset sections
    showGeneratedPasswordSection();
  }

  function handleResetMethodChange(e) {
    const method = e.target.value;
    
    if (method === 'generate') {
      showGeneratedPasswordSection();
    } else {
      showCustomPasswordSection();
    }
  }

  function showGeneratedPasswordSection() {
    document.getElementById('generated-password-section').classList.remove('hidden');
    document.getElementById('custom-password-section').classList.add('hidden');
  }

  function showCustomPasswordSection() {
    document.getElementById('generated-password-section').classList.add('hidden');
    document.getElementById('custom-password-section').classList.remove('hidden');
  }

  function generateNewPassword() {
    const length = 12;
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    
    // Ensure at least one character from each category
    password += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)]; // Uppercase
    password += 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random() * 26)]; // Lowercase
    password += '0123456789'[Math.floor(Math.random() * 10)]; // Number
    password += '!@#$%^&*'[Math.floor(Math.random() * 8)]; // Special
    
    // Fill the rest randomly
    for (let i = 4; i < length; i++) {
      password += charset[Math.floor(Math.random() * charset.length)];
    }
    
    // Shuffle the password
    password = password.split('').sort(() => Math.random() - 0.5).join('');
    
    document.getElementById('generated-password').value = password;
  }

  function copyGeneratedPassword() {
    const passwordField = document.getElementById('generated-password');
    passwordField.select();
    passwordField.setSelectionRange(0, 99999); // For mobile devices
    
    try {
      document.execCommand('copy');
      showNotification('Password copied to clipboard!', 'success');
    } catch (err) {
      // Fallback for modern browsers
      navigator.clipboard.writeText(passwordField.value).then(() => {
        showNotification('Password copied to clipboard!', 'success');
      }).catch(() => {
        showNotification('Failed to copy password', 'error');
      });
    }
  }

  function regeneratePassword() {
    generateNewPassword();
    showNotification('New password generated!', 'success');
  }

  function validateCustomPassword(password) {
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    return password.length >= minLength && hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChar;
  }

  function handleResetPassword(e) {
    e.preventDefault();
    const form = e.target;
    const userId = form.elements['userId'].value;
    const resetMethod = form.elements['resetMethod'].value;
    const notifyUser = form.elements['notifyUser'].checked;
    const forcePasswordChange = form.elements['forcePasswordChange'].checked;
    
    let newPassword = '';
    
    if (resetMethod === 'generate') {
      newPassword = document.getElementById('generated-password').value;
    } else {
      const customPassword = form.elements['customPassword'].value;
      const confirmPassword = form.elements['confirmPassword'].value;
      
      if (customPassword !== confirmPassword) {
        showNotification('Passwords do not match!', 'error');
        return;
      }
      
      if (!validateCustomPassword(customPassword)) {
        showNotification('Password does not meet security requirements!', 'error');
        return;
      }
      
      newPassword = customPassword;
    }
    
    if (!newPassword) {
      showNotification('Please generate or enter a password!', 'error');
      return;
    }
    
    // Update user password in backend
    if (window.DiscipleshipBackend) {
      const success = window.DiscipleshipBackend.resetUserPassword(userId, newPassword, {
        notifyUser,
        forcePasswordChange,
        resetBy: 'Admin',
        resetDate: new Date().toISOString()
      });
      
      if (success) {
        showNotification('Password reset successfully!', 'success');
        
        // Log the action
        const user = window.DiscipleshipBackend.getAllUsers().find(u => u.id === userId);
        if (user) {
          console.log(`Admin reset password for user: ${user.name} (${user.email})`);
        }
        
        closeResetPasswordModal();
        loadUsers(); // Refresh user list
      } else {
        showNotification('Failed to reset password!', 'error');
      }
    }
  }

  function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 ${
      type === 'success' ? 'bg-green-500 text-white' :
      type === 'error' ? 'bg-red-500 text-white' :
      'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
      notification.remove();
    }, 3000);
  }

  // Export functions for global use
  window.AdminComplete = {
    openUserModal,
    closeUserModal,
    openCourseModal,
    closeCourseModal,
    openCertificateModal,
    closeCertificateModal,
    openReplyTicketModal,
    closeReplyTicketModal,
    openTicketDetailsModal,
    closeTicketDetailsModal,
    openResetPasswordModal,
    closeResetPasswordModal,
    initAdminDashboard,
    testSidebar: function() {
      console.log('Testing sidebar functionality...');
      const sidebarLinks = document.querySelectorAll('.sidebar-link');
      console.log('Found sidebar links:', sidebarLinks.length);
      
      sidebarLinks.forEach((link, index) => {
        console.log(`Link ${index}:`, {
          text: link.textContent.trim(),
          href: link.getAttribute('href'),
          classes: link.className
        });
      });
      
      // Test clicking the first non-active link
      const nonActiveLinks = Array.from(sidebarLinks).filter(link => !link.classList.contains('active'));
      if (nonActiveLinks.length > 0) {
        console.log('Testing click on:', nonActiveLinks[0].textContent.trim());
        nonActiveLinks[0].click();
      }
    }
  };

  // Make functions globally available for HTML onclick handlers
  window.closeReplyTicketModal = function() {
    if (window.AdminComplete) {
      window.AdminComplete.closeReplyTicketModal();
    }
  };

  window.closeTicketDetailsModal = function() {
    if (window.AdminComplete) {
      window.AdminComplete.closeTicketDetailsModal();
    }
  };

  window.closeResetPasswordModal = function() {
    if (window.AdminComplete) {
      window.AdminComplete.closeResetPasswordModal();
    }
  };

  window.copyGeneratedPassword = function() {
    if (window.AdminComplete) {
      // This function is already defined in the scope
      copyGeneratedPassword();
    }
  };

  window.regeneratePassword = function() {
    if (window.AdminComplete) {
      // This function is already defined in the scope
      regeneratePassword();
    }
  };

  window.openResetPasswordModal = function(userId) {
    if (window.AdminComplete) {
      window.AdminComplete.openResetPasswordModal(userId);
    }
  };

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminDashboard);
  } else {
    initAdminDashboard();
  }

})(); 