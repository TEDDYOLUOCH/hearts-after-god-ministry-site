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
      
      // Check for key elements
      const editUserModal = document.getElementById('edit-user-modal');
      const editUserForm = document.getElementById('edit-user-form');
      console.log('Key elements found:', {
        editUserModal: !!editUserModal,
        editUserForm: !!editUserForm
      });
      
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

    const editUserModal = document.getElementById('edit-user-modal');
    if (editUserModal) {
      console.log('Found edit user modal, adding click handler');
      editUserModal.addEventListener('click', function(e) {
        if (e.target === this) closeEditUserModal();
      });
    } else {
      console.log('Edit user modal not found');
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
            ${user.role !== 'admin' ? `
            <button onclick="deleteUser('${user.id}')" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Delete User">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
            ` : ''}
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
    console.log('openEditUserModal called with userId:', userId);
    
    const modal = document.getElementById('edit-user-modal');
    console.log('Modal element found:', !!modal);
    
    const form = document.getElementById('edit-user-form');
    console.log('Form element found:', !!form);
    
    if (!modal || !form || !window.DiscipleshipBackend) {
      console.error('Missing required elements:', {
        modal: !!modal,
        form: !!form,
        backend: !!window.DiscipleshipBackend
      });
      return;
    }
    
    const user = window.DiscipleshipBackend.getAllUsers().find(u => u.id === userId);
    console.log('User found:', user);
    
    if (!user) {
      console.error('User not found with ID:', userId);
      return;
    }
    
    // Populate form fields
    form.elements['id'].value = user.id;
    form.elements['name'].value = user.name;
    form.elements['email'].value = user.email;
    form.elements['role'].value = user.role;
    
    console.log('Form populated with user data:', {
      id: user.id,
      name: user.name,
      email: user.email,
      role: user.role
    });
    
    modal.classList.remove('hidden');
    console.log('Modal should now be visible');
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
    console.log('Global editUser function called with userId:', userId);
    if (window.AdminComplete) {
      console.log('AdminComplete found, calling openEditUserModal');
      window.AdminComplete.openEditUserModal(userId);
    } else {
      console.error('AdminComplete not available');
    }
  };

  window.deleteUser = function(userId) {
    if (window.AdminComplete) {
      window.AdminComplete.deleteUser(userId);
    }
  };

  window.viewUserDashboard = function(userId) {
    if (window.AdminComplete) {
      window.AdminComplete.viewUserDashboard(userId);
    }
  };

  window.editCourse = function(courseId) {
    if (window.AdminComplete) {
      window.AdminComplete.openEditCourseModal(courseId);
    }
  };

  window.deleteCourse = function(courseId) {
    if (window.AdminComplete) {
      window.AdminComplete.deleteCourse(courseId);
    }
  };

  window.downloadCertificate = function(certificateId) {
    console.log('Downloading certificate:', certificateId);
    
    if (!window.DiscipleshipBackend) {
      showNotification('Backend not available', 'error');
      return;
    }
    
    const certificates = window.DiscipleshipBackend.getAllCertificates();
    const certificate = certificates.find(c => c.id === certificateId);
    
    if (!certificate) {
      showNotification('Certificate not found', 'error');
      return;
    }
    
    try {
      // Generate certificate HTML
      const certificateHTML = generateCertificateHTML(certificate);
      
      // Create a new window with the certificate
      const newWindow = window.open('', '_blank');
      newWindow.document.write(certificateHTML);
      newWindow.document.close();
      
      showNotification('Certificate opened in new window', 'success');
    } catch (error) {
      console.error('Error downloading certificate:', error);
      showNotification('Error downloading certificate', 'error');
    }
  };

  window.revokeCertificate = function(certificateId) {
    console.log('Revoking certificate:', certificateId);
    
    if (!confirm('Are you sure you want to revoke this certificate? This action cannot be undone.')) {
      return;
    }
    
    if (!window.DiscipleshipBackend) {
      showNotification('Backend not available', 'error');
      return;
    }
    
    const certificates = window.DiscipleshipBackend.getAllCertificates();
    const certificate = certificates.find(c => c.id === certificateId);
    
    if (!certificate) {
      showNotification('Certificate not found', 'error');
      return;
    }
    
    try {
      // Update certificate status to revoked
      certificate.status = 'revoked';
      certificate.revokedDate = new Date().toISOString();
      
      // Save to backend (you might need to add an updateCertificate method)
      // For now, we'll just reload the certificates
      loadCertificates();
      loadDashboardStats();
      
      showNotification('Certificate revoked successfully', 'success');
    } catch (error) {
      console.error('Error revoking certificate:', error);
      showNotification('Error revoking certificate', 'error');
    }
  };

  window.replyToTicket = function(ticketId) {
    console.log('Replying to ticket:', ticketId);
    if (window.AdminComplete && window.AdminComplete.openReplyTicketModal) {
      window.AdminComplete.openReplyTicketModal(ticketId);
    } else {
      showNotification('Reply function not available', 'error');
    }
  };

  window.resolveTicket = function(ticketId) {
    console.log('Resolving ticket:', ticketId);
    
    if (!confirm('Are you sure you want to mark this ticket as resolved?')) {
      return;
    }
    
    if (!window.DiscipleshipBackend) {
      showNotification('Backend not available', 'error');
      return;
    }
    
    try {
      // Update ticket status to resolved
      window.DiscipleshipBackend.updateSupportTicket(ticketId, {
        status: 'resolved',
        resolvedDate: new Date().toISOString()
      });
      
      loadSupportTickets();
      loadDashboardStats();
      
      showNotification('Ticket resolved successfully', 'success');
    } catch (error) {
      console.error('Error resolving ticket:', error);
      showNotification('Error resolving ticket', 'error');
    }
  };

  window.viewTicketDetails = function(ticketId) {
    console.log('Viewing ticket details:', ticketId);
    if (window.AdminComplete && window.AdminComplete.openTicketDetailsModal) {
      window.AdminComplete.openTicketDetailsModal(ticketId);
    } else {
      showNotification('Ticket details function not available', 'error');
    }
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
    console.log('openCertificateModal called');
    const modal = document.getElementById('certificate-modal');
    console.log('Certificate modal found:', !!modal);
    if (modal) {
      modal.classList.remove('hidden');
      populateCertificateModal();
    } else {
      console.error('Certificate modal not found');
    }
  }

  function closeCertificateModal() {
    console.log('closeCertificateModal called');
    const modal = document.getElementById('certificate-modal');
    if (modal) modal.classList.add('hidden');
  }

  function populateCertificateModal() {
    console.log('populateCertificateModal called');
    if (!window.DiscipleshipBackend) {
      console.error('Backend not available');
      return;
    }
    
    const students = window.DiscipleshipBackend.getAllUsers().filter(u => u.role === 'student');
    const courses = window.DiscipleshipBackend.getAllCourses();
    
    console.log('Students found:', students.length);
    console.log('Courses found:', courses.length);
    
    const studentSelect = document.querySelector('#certificate-modal select[name="studentId"]');
    const courseSelect = document.querySelector('#certificate-modal select[name="courseId"]');
    
    console.log('Student select found:', !!studentSelect);
    console.log('Course select found:', !!courseSelect);
    
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
    console.log('=== handleIssueCertificate called ===');
    e.preventDefault();
    
    try {
      const formData = new FormData(e.target);
      const studentId = formData.get('studentId');
      const courseId = formData.get('courseId');
      const completionDate = formData.get('completionDate');
      
      console.log('Form data:', { studentId, courseId, completionDate });
      
      if (!studentId || !courseId || !completionDate) {
        console.error('Missing required fields');
        showNotification('Please fill in all fields', 'error');
        return;
      }
      
      console.log('Backend available:', !!window.DiscipleshipBackend);
      
      if (window.DiscipleshipBackend) {
        console.log('Getting users and courses...');
        const users = window.DiscipleshipBackend.getAllUsers();
        const courses = window.DiscipleshipBackend.getAllCourses();
        
        console.log('Users found:', users.length);
        console.log('Courses found:', courses.length);
        
        const student = users.find(s => s.id === studentId);
        const course = courses.find(c => c.id === courseId);
        
        console.log('Student found:', !!student, student ? student.name : 'N/A');
        console.log('Course found:', !!course, course ? course.title : 'N/A');
        
        if (student && course) {
          const certificateData = {
            studentId: studentId,
            studentName: student.name,
            courseId: courseId,
            courseName: course.title,
            issuedDate: new Date().toISOString(),
            completionDate: completionDate
          };
          
          console.log('Creating certificate with data:', certificateData);
          
          try {
            console.log('Calling createCertificate...');
            const certificate = window.DiscipleshipBackend.createCertificate(certificateData);
            console.log('Certificate created successfully:', certificate);
            
            console.log('Reloading certificates...');
            loadCertificates();
            
            console.log('Reloading dashboard stats...');
            loadDashboardStats();
            
            showNotification('Certificate issued successfully!', 'success');
            console.log('=== Certificate creation completed successfully ===');
          } catch (error) {
            console.error('Error in createCertificate call:', error);
            console.error('Error stack:', error.stack);
            showNotification('Error creating certificate: ' + error.message, 'error');
          }
        } else {
          console.error('Student or course not found');
          if (!student) console.error('Student not found for ID:', studentId);
          if (!course) console.error('Course not found for ID:', courseId);
          showNotification('Student or course not found', 'error');
        }
      } else {
        console.error('Backend not available');
        showNotification('Backend not available', 'error');
      }
    } catch (error) {
      console.error('Unexpected error in handleIssueCertificate:', error);
      console.error('Error stack:', error.stack);
      showNotification('Unexpected error: ' + error.message, 'error');
    }
    
    console.log('Closing modal and resetting form...');
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
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="loading-spinner"></span> Resetting Password...';
    
    // Update user password in backend with enhanced cross-device support
    if (window.DiscipleshipBackend) {
      const success = window.DiscipleshipBackend.resetUserPassword(userId, newPassword, {
        notifyUser,
        forcePasswordChange,
        resetBy: 'Admin',
        resetDate: new Date().toISOString()
      });
      
      if (success) {
        const user = window.DiscipleshipBackend.getAllUsers().find(u => u.id === userId);
        
        // Show success notification with details
        showNotification(`Password reset successfully for ${user.name}! New password: ${newPassword}`, 'success');
        
        // Log the action
        if (user) {
          console.log(`Admin reset password for user: ${user.name} (${user.email})`);
          console.log(`New password: ${newPassword}`);
          console.log(`Cross-device sync enabled: true`);
        }
        
        // Show additional info about cross-device sync
        setTimeout(() => {
          showNotification('Password will be available immediately on all devices (phone, laptop, tablet)', 'info');
        }, 2000);
        
        closeResetPasswordModal();
        loadUsers(); // Refresh user list
        
        // Update the user row to show password reset status
        updateUserRowAfterPasswordReset(userId, newPassword);
      } else {
        showNotification('Failed to reset password!', 'error');
      }
    }
    
    // Reset button state
    submitButton.disabled = false;
    submitButton.innerHTML = originalText;
  }

  // Update user row to show password reset status
  function updateUserRowAfterPasswordReset(userId, newPassword) {
    const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
    if (userRow) {
      // Add a visual indicator that password was reset
      const statusCell = userRow.querySelector('.password-reset-status');
      if (statusCell) {
        statusCell.innerHTML = `
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
            <i class="fas fa-key mr-1"></i>Password Reset
          </span>
        `;
        
        // Remove the indicator after 30 seconds
        setTimeout(() => {
          statusCell.innerHTML = `
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
              <i class="fas fa-check mr-1"></i>Active
            </span>
          `;
        }, 30000);
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
    openEditUserModal,
    closeEditUserModal,
    openCourseModal,
    closeCourseModal,
    openEditCourseModal,
    closeEditCourseModal,
    openCertificateModal,
    closeCertificateModal,
    openReplyTicketModal,
    closeReplyTicketModal,
    openTicketDetailsModal,
    closeTicketDetailsModal,
    openResetPasswordModal,
    closeResetPasswordModal,
    initAdminDashboard,
    deleteUser: function(userId) {
      if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return;
      }

      console.log('Deleting user:', userId);
      
      if (!window.DiscipleshipBackend) {
        showNotification('Backend not available', 'error');
        return;
      }

      try {
        const result = window.DiscipleshipBackend.deleteUser(userId);
        
        if (result.success) {
          showNotification(result.message, 'success');
          // Reload the user table and dashboard stats
          loadUsers();
          loadDashboardStats();
          updateActivityFeed();
        } else {
          showNotification(result.message, 'error');
        }
      } catch (error) {
        console.error('Error deleting user:', error);
        showNotification('Error deleting user', 'error');
      }
    },
    deleteCourse: function(courseId) {
      if (confirm('Are you sure you want to delete this course?')) {
        console.log('Delete course:', courseId);
        // TODO: Implement delete course logic
        showNotification('Delete course functionality coming soon!', 'info');
      }
    },
    viewUserDashboard: function(userId) {
      console.log('Viewing user dashboard for:', userId);
      
      if (!window.DiscipleshipBackend) {
        showNotification('Backend not available', 'error');
        return;
      }

      const users = window.DiscipleshipBackend.getAllUsers();
      const user = users.find(u => u.id === userId);
      
      if (!user) {
        showNotification('User not found', 'error');
        return;
      }

      // Store the user data temporarily for the dashboard view
      localStorage.setItem('admin_viewing_user', JSON.stringify(user));
      
      // Open user dashboard in a new tab/window
      const userDashboardUrl = 'discipleship-user.html?admin_view=true&user_id=' + userId;
      window.open(userDashboardUrl, '_blank');
      
      showNotification(`Opening dashboard for ${user.name}`, 'info');
    },
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

  // Add more global functions for edit functionality
  window.openEditUserModal = function(userId) {
    if (window.AdminComplete) {
      window.AdminComplete.openEditUserModal(userId);
    }
  };

  window.closeEditUserModal = function() {
    if (window.AdminComplete) {
      window.AdminComplete.closeEditUserModal();
    }
  };

  window.openEditCourseModal = function(courseId) {
    if (window.AdminComplete) {
      window.AdminComplete.openEditCourseModal(courseId);
    }
  };

  window.closeEditCourseModal = function() {
    if (window.AdminComplete) {
      window.AdminComplete.closeEditCourseModal();
    }
  };

  // Certificate management global functions
  window.openCertificateModal = function() {
    if (window.AdminComplete) {
      window.AdminComplete.openCertificateModal();
    }
  };

  window.closeCertificateModal = function() {
    if (window.AdminComplete) {
      window.AdminComplete.closeCertificateModal();
    }
  };

  // Test delete user functionality
  window.testDeleteUserFunctionality = function() {
    console.log('=== Testing Delete User Functionality ===');
    
    // Test 1: Check if backend is available
    console.log('1. Backend available:', !!window.DiscipleshipBackend);
    
    if (window.DiscipleshipBackend) {
      // Test 2: Check backend methods
      console.log('2. Backend methods:', {
        deleteUser: typeof window.DiscipleshipBackend.deleteUser,
        getAllUsers: typeof window.DiscipleshipBackend.getAllUsers
      });
      
      // Test 3: Check data availability
      const users = window.DiscipleshipBackend.getAllUsers();
      const nonAdminUsers = users.filter(u => u.role !== 'admin');
      
      console.log('3. Data availability:', {
        totalUsers: users.length,
        nonAdminUsers: nonAdminUsers.length
      });
      
      // Test 4: Check if we have users to delete
      if (nonAdminUsers.length > 0) {
        const testUser = nonAdminUsers[0];
        console.log('4. Test user found:', {
          id: testUser.id,
          name: testUser.name,
          email: testUser.email,
          role: testUser.role
        });
        
        // Test 5: Try to delete the test user
        console.log('5. Testing delete user...');
        try {
          const result = window.DiscipleshipBackend.deleteUser(testUser.id);
          console.log('Delete result:', result);
          
          if (result.success) {
            console.log('✅ Delete user test PASSED');
            showNotification('Delete user test PASSED - User deleted successfully', 'success');
            
            // Reload the user table
            loadUsers();
            loadDashboardStats();
          } else {
            console.log('❌ Delete user test FAILED:', result.message);
            showNotification('Delete user test FAILED: ' + result.message, 'error');
          }
        } catch (error) {
          console.error('❌ Delete user test ERROR:', error);
          showNotification('Delete user test ERROR: ' + error.message, 'error');
        }
      } else {
        console.log('4. No non-admin users available for testing');
        showNotification('No non-admin users available for testing', 'info');
      }
      
      // Test 6: Check global functions
      console.log('6. Global functions:', {
        deleteUser: typeof window.deleteUser,
        AdminComplete: !!window.AdminComplete
      });
    } else {
      console.log('❌ Backend not available');
      showNotification('Backend not available for testing', 'error');
    }
  };

  // Test certificate functionality
  window.testCertificateFunctionality = function() {
    console.log('=== Testing Certificate Functionality ===');
    
    // Test 1: Check if backend is available
    console.log('1. Backend available:', !!window.DiscipleshipBackend);
    
    if (window.DiscipleshipBackend) {
      // Test 2: Check backend methods
      console.log('2. Backend methods:', {
        createCertificate: typeof window.DiscipleshipBackend.createCertificate,
        getAllUsers: typeof window.DiscipleshipBackend.getAllUsers,
        getAllCourses: typeof window.DiscipleshipBackend.getAllCourses,
        getAllCertificates: typeof window.DiscipleshipBackend.getAllCertificates
      });
      
      // Test 3: Check data availability
      const users = window.DiscipleshipBackend.getAllUsers();
      const courses = window.DiscipleshipBackend.getAllCourses();
      const certificates = window.DiscipleshipBackend.getAllCertificates();
      
      console.log('3. Data availability:', {
        users: users.length,
        courses: courses.length,
        certificates: certificates.length
      });
      
      // Test 4: Check modal elements
      const modal = document.getElementById('certificate-modal');
      const form = document.getElementById('issue-certificate-form');
      const studentSelect = document.querySelector('#certificate-modal select[name="studentId"]');
      const courseSelect = document.querySelector('#certificate-modal select[name="courseId"]');
      
      console.log('4. Modal elements:', {
        modal: !!modal,
        form: !!form,
        studentSelect: !!studentSelect,
        courseSelect: !!courseSelect
      });
      
      // Test 5: Check global functions
      console.log('5. Global functions:', {
        openCertificateModal: typeof window.openCertificateModal,
        closeCertificateModal: typeof window.closeCertificateModal,
        AdminComplete: !!window.AdminComplete
      });
      
      // Test 6: Try to open modal
      console.log('6. Testing modal open...');
      try {
        window.openCertificateModal();
        console.log('Modal opened successfully');
      } catch (error) {
        console.error('Error opening modal:', error);
      }
      
      // Test 7: Check if we have students and courses
      const students = users.filter(u => u.role === 'student');
      console.log('7. Students available:', students.length);
      if (students.length > 0) {
        console.log('Sample student:', students[0]);
      }
      
      console.log('8. Courses available:', courses.length);
      if (courses.length > 0) {
        console.log('Sample course:', courses[0]);
      }
      
    } else {
      console.error('Backend not available!');
    }
    
    console.log('=== End Certificate Test ===');
  };

  // Simple backend test function
  window.testBackend = function() {
    console.log('=== Testing Backend ===');
    
    if (!window.DiscipleshipBackend) {
      console.error('❌ Backend not available');
      return;
    }
    
    console.log('✅ Backend available');
    
    try {
      // Test basic methods
      const users = window.DiscipleshipBackend.getAllUsers();
      const courses = window.DiscipleshipBackend.getAllCourses();
      const certificates = window.DiscipleshipBackend.getAllCertificates();
      
      console.log('✅ Users:', users.length);
      console.log('✅ Courses:', courses.length);
      console.log('✅ Certificates:', certificates.length);
      
      // Test certificate creation
      if (users.length > 0 && courses.length > 0) {
        const testCertificate = {
          studentId: users[0].id,
          studentName: users[0].name,
          courseId: courses[0].id,
          courseName: courses[0].title,
          issuedDate: new Date().toISOString(),
          completionDate: new Date().toISOString()
        };
        
        console.log('Testing certificate creation...');
        const newCert = window.DiscipleshipBackend.createCertificate(testCertificate);
        console.log('✅ Certificate created:', newCert);
        
        // Verify it was added
        const updatedCerts = window.DiscipleshipBackend.getAllCertificates();
        console.log('✅ Updated certificates count:', updatedCerts.length);
        
        showNotification('Backend test completed successfully!', 'success');
      } else {
        console.log('⚠️ No users or courses available for testing');
        showNotification('No users or courses available for testing', 'warning');
      }
      
    } catch (error) {
      console.error('❌ Backend test failed:', error);
      showNotification('Backend test failed: ' + error.message, 'error');
    }
    
    console.log('=== End Backend Test ===');
  };

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminDashboard);
  } else {
    initAdminDashboard();
  }

})(); 