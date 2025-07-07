// Simplified Admin Dashboard
// Core functionality with real-time updates and role management

(function() {
  let backend = null;
  
  // Admin state
  let adminState = {
    currentUser: null,
    userRole: 'admin',
    isAuthenticated: false,
    lastUpdate: null
  };

  // Initialize admin dashboard
  function initAdminDashboard() {
    console.log('initAdminDashboard called');
    console.log('window.DiscipleshipBackend:', window.DiscipleshipBackend);
    
    // Wait for backend to be available
    if (!window.DiscipleshipBackend) {
      console.log('Waiting for backend to load...');
      setTimeout(initAdminDashboard, 100);
      return;
    }
    
    try {
      backend = window.DiscipleshipBackend;
      console.log('Backend loaded, initializing admin dashboard...');
      console.log('Backend methods:', Object.getOwnPropertyNames(Object.getPrototypeOf(backend)));
      
      checkAuthentication();
      setupEventListeners();
      startRealTimeUpdates();
      loadInitialData();
      
      console.log('Admin dashboard initialized successfully');
    } catch (error) {
      console.error('Error initializing admin dashboard:', error);
    }
  }

  // Check authentication
  function checkAuthentication() {
    const loggedInUser = localStorage.getItem('discipleship_logged_in_user');
    if (loggedInUser) {
      const user = JSON.parse(loggedInUser);
      if (user.role === 'admin') {
        adminState.currentUser = user;
        adminState.isAuthenticated = true;
        adminState.userRole = user.role;
      } else {
        // User is not admin, redirect to user dashboard
        window.location.href = 'discipleship-user.html';
      }
    } else {
      // For demo purposes, allow admin access without login
      // In production, this should redirect to login
      console.log('No user logged in, using demo admin mode');
      adminState.currentUser = {
        name: 'Demo Admin',
        email: 'admin@demo.com',
        role: 'admin'
      };
      adminState.isAuthenticated = true;
      adminState.userRole = 'admin';
      
      // Update the UI to show demo admin
      const adminNameElement = document.querySelector('.text-sm.font-medium');
      if (adminNameElement) {
        adminNameElement.textContent = 'Demo Admin';
      }
    }
  }

  // Setup event listeners
  function setupEventListeners() {
    try {
      console.log('Setting up event listeners...');
      
      // Sidebar navigation
      const sidebarLinks = document.querySelectorAll('.sidebar-link');
      console.log('Found sidebar links:', sidebarLinks.length);
      sidebarLinks.forEach(link => {
        link.addEventListener('click', handleNavigation);
      });

      // User management
      const addUserBtn = document.getElementById('add-user-btn');
      console.log('Add user button found:', !!addUserBtn);
      addUserBtn?.addEventListener('click', openUserModal);
      
      const userSearch = document.getElementById('user-search');
      console.log('User search found:', !!userSearch);
      userSearch?.addEventListener('input', handleUserSearch);
      
      const userFilter = document.getElementById('user-filter');
      console.log('User filter found:', !!userFilter);
      userFilter?.addEventListener('change', handleUserFilter);

      // Support management
      const ticketFilter = document.getElementById('ticket-filter');
      console.log('Ticket filter found:', !!ticketFilter);
      ticketFilter?.addEventListener('change', handleTicketFilter);

      // Notifications
      const notificationBell = document.getElementById('notification-bell');
      console.log('Notification bell found:', !!notificationBell);
      notificationBell?.addEventListener('click', toggleNotifications);
      
      const closeNotifications = document.getElementById('close-notifications');
      console.log('Close notifications found:', !!closeNotifications);
      closeNotifications?.addEventListener('click', closeNotifications);

      // Modals
      const userModal = document.getElementById('user-modal');
      console.log('User modal found:', !!userModal);
      userModal?.addEventListener('click', handleModalClick);
      
      const addUserForm = document.getElementById('add-user-form');
      console.log('Add user form found:', !!addUserForm);
      addUserForm?.addEventListener('submit', handleAddUser);

      // Logout
      const logoutBtn = document.getElementById('logout-btn');
      console.log('Logout button found:', !!logoutBtn);
      logoutBtn?.addEventListener('click', handleLogout);

      // Export
      const exportDataBtn = document.getElementById('export-data-btn');
      console.log('Export data button found:', !!exportDataBtn);
      exportDataBtn?.addEventListener('click', exportData);
      
      // Event Files section
      setupEventFilesSection();
      
      console.log('Event listeners setup completed');
    } catch (error) {
      console.error('Error setting up event listeners:', error);
    }
  }

  // Handle navigation
  function handleNavigation(e) {
    e.preventDefault();
    
    // Update active link
    document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
    this.classList.add('active');
    
    // Show/hide sections
    const targetId = this.getAttribute('href').substring(1);
    showSection(targetId);
  }

  // Show specific section
  function showSection(sectionId) {
    document.querySelectorAll('.dashboard-section').forEach(section => {
      section.classList.add('hidden');
    });
    
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
      targetSection.classList.remove('hidden');
      loadSectionData(sectionId);
    }
  }

  // Load section data
  function loadSectionData(sectionId) {
    switch (sectionId) {
      case 'dashboard':
        updateDashboardStats();
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
      case 'notes':
        if (window.AdminNotes) {
          window.AdminNotes.loadNotes();
        }
        break;
      case 'quizzes':
        if (window.AdminQuiz) {
          window.AdminQuiz.loadQuizzes();
        }
        break;
      case 'analytics':
        loadAnalytics();
        break;
      case 'settings':
        loadSettings();
        break;
    }
  }

  // Start real-time updates
  function startRealTimeUpdates() {
    // Update every 5 seconds
    setInterval(() => {
      updateDashboardStats();
      updateActivityFeed();
      updateOnlineUsers();
      updateSyncStatus();
    }, 5000);

    // Initial update
    updateDashboardStats();
    updateActivityFeed();
    updateOnlineUsers();
  }

  // Update dashboard stats
  function updateDashboardStats() {
    const users = backend.getAllUsers();
    const courses = backend.getAllCourses();
    const certificates = backend.getAllCertificates();
    const tickets = backend.getAllSupportTickets();

    // Update stats
    document.getElementById('total-users').textContent = users.length;
    document.getElementById('active-courses').textContent = courses.length;
    document.getElementById('certificates-issued').textContent = certificates.length;
    document.getElementById('support-tickets').textContent = tickets.filter(t => t.status === 'open').length;
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
    const users = backend.getAllUsers();
    const onlineUsers = users.filter(user => isUserOnline(user.id));
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
      <div class="flex items-center gap-3 p-2 bg-green-50 rounded-lg border border-green-200">
        <div class="w-6 h-6 bg-gradient-to-br from-[#7C3AED] to-[#2046B3] rounded-full flex items-center justify-center text-white text-xs font-semibold">
          ${user.name.substring(0, 2).toUpperCase()}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 truncate">${user.name}</p>
          <p class="text-xs text-gray-500">${user.role || 'Student'}</p>
        </div>
        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
      </div>
    `).join('');
  }

  // Update sync status
  function updateSyncStatus() {
    const statusDot = document.getElementById('sync-status-dot');
    const statusText = document.getElementById('sync-status-text');
    
    if (statusDot && statusText) {
      statusDot.className = 'w-2 h-2 bg-green-500 rounded-full animate-pulse';
      statusText.textContent = 'Synced';
      adminState.lastUpdate = new Date();
    }
  }

  // Load users
  function loadUsers() {
    const users = backend.getAllUsers();
    const tableBody = document.getElementById('user-table-body');
    
    if (!tableBody) return;

    tableBody.innerHTML = users.map(user => `
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="flex items-center">
            <div class="w-8 h-8 bg-gradient-to-br from-[#7C3AED] to-[#2046B3] rounded-full flex items-center justify-center text-white text-sm font-semibold">
              ${user.name.substring(0, 2).toUpperCase()}
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">${user.name}</div>
              <div class="text-sm text-gray-500">${user.email}</div>
            </div>
          </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${isUserOnline(user.id) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
            ${isUserOnline(user.id) ? 'Online' : 'Offline'}
          </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
          ${user.progress?.completedLessons || 0} lessons
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
          ${getTimeAgo(user.lastActivity || user.joinedDate)}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
          <button onclick="editUser('${user.id}')" class="text-[#7C3AED] hover:text-[#6D28D9] mr-3">Edit</button>
          <button onclick="deleteUser('${user.id}')" class="text-red-600 hover:text-red-900">Delete</button>
        </td>
      </tr>
    `).join('');
  }

  // Load courses
  function loadCourses() {
    const courses = backend.getAllCourses();
    const coursesGrid = document.getElementById('courses-grid');
    
    if (!coursesGrid) return;

    coursesGrid.innerHTML = courses.map(course => `
      <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200 hover:shadow-xl transition-shadow">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-lg font-bold text-[#2046B3]">${course.title}</h4>
          <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
        </div>
        <p class="text-gray-600 text-sm mb-4">${course.description}</p>
        <div class="flex items-center justify-between text-sm text-gray-500">
          <span>${course.lessons?.length || 0} lessons</span>
          <span>${course.enrolledUsers || 0} students</span>
        </div>
        <div class="mt-4 flex gap-2">
          <button class="flex-1 px-3 py-2 bg-[#7C3AED] text-white rounded-lg text-sm hover:bg-[#6D28D9] transition-colors">
            Edit
          </button>
          <button class="flex-1 px-3 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition-colors">
            View
          </button>
        </div>
      </div>
    `).join('');
  }

  // Load certificates
  function loadCertificates() {
    const certificates = backend.getAllCertificates();
    const tableBody = document.getElementById('certificate-table-body');
    
    if (!tableBody) return;

    tableBody.innerHTML = certificates.map(cert => `
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${cert.id}</td>
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="text-sm font-medium text-gray-900">${cert.user?.name || 'Unknown'}</div>
          <div class="text-sm text-gray-500">${cert.user?.email || 'No email'}</div>
        </div>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${cert.courseName}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${new Date(cert.issuedDate).toLocaleDateString()}</td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
            Issued
          </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
          <button class="text-[#7C3AED] hover:text-[#6D28D9] mr-3">View</button>
          <button class="text-[#FDBA17] hover:text-[#E0A615]">Download</button>
        </td>
      </tr>
    `).join('');
  }

  // Load support tickets
  function loadSupportTickets() {
    const tickets = backend.getAllSupportTickets();
    const tableBody = document.getElementById('support-table-body');
    
    if (!tableBody) return;

    tableBody.innerHTML = tickets.map(ticket => {
      const priorityClass = {
        'high': 'bg-red-100 text-red-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'low': 'bg-green-100 text-green-800'
      }[ticket.priority] || 'bg-gray-100 text-gray-800';

      return `
        <tr class="hover:bg-gray-50">
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#${ticket.id}</td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">${ticket.user?.name || 'Unknown'}</div>
            <div class="text-sm text-gray-500">${ticket.user?.email || 'No email'}</div>
          </div>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${ticket.subject}</td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${priorityClass}">
              ${ticket.priority}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${ticket.status === 'open' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
              ${ticket.status}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <button class="text-[#7C3AED] hover:text-[#6D28D9] mr-3">View</button>
            <button class="text-[#FDBA17] hover:text-[#E0A615]">Reply</button>
          </td>
        </tr>
      `;
    }).join('');
  }

  // Load analytics
  function loadAnalytics() {
    // Simple analytics visualization
    updateActivityChart();
    updateCompletionChart();
  }

  // Load settings
  function loadSettings() {
    // Settings are already in the HTML
  }

  // Get recent activities
  function getRecentActivities(limit = 8) {
    const activities = [
      { type: 'user_login', message: 'John Doe logged in', timestamp: new Date(Date.now() - 1000 * 60 * 5), icon: '👤' },
      { type: 'progress_update', message: 'Sarah completed Lesson 3', timestamp: new Date(Date.now() - 1000 * 60 * 15), icon: '📈' },
      { type: 'certificate_issued', message: 'Certificate issued to Mike', timestamp: new Date(Date.now() - 1000 * 60 * 30), icon: '🎓' },
      { type: 'support_ticket', message: 'New support ticket from Lisa', timestamp: new Date(Date.now() - 1000 * 60 * 45), icon: '🎫' }
    ];
    
    return activities.slice(0, limit);
  }

  // Check if user is online
  function isUserOnline(userId) {
    const lastActivity = localStorage.getItem(`user_activity_${userId}`);
    if (!lastActivity) return false;
    
    const lastActivityTime = new Date(parseInt(lastActivity));
    const now = new Date();
    const diffMinutes = (now - lastActivityTime) / (1000 * 60);
    
    return diffMinutes < 5;
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

  // User modal functions
  function openUserModal() {
    document.getElementById('user-modal').classList.remove('hidden');
  }

  function closeUserModal() {
    document.getElementById('user-modal').classList.add('hidden');
  }

  function handleModalClick(e) {
    if (e.target === e.currentTarget) {
      closeUserModal();
    }
  }

  function handleAddUser(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const userData = {
      name: formData.get('name'),
      email: formData.get('email'),
      role: formData.get('role'),
      password: 'defaultPassword123', // In production, this should be generated or required
      joinedDate: new Date().toISOString()
    };
    
    if (backend) {
      backend.createUser(userData);
    }
    
    closeUserModal();
    e.target.reset();
    loadUsers();
  }

  // Search and filter functions
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
  }

  function handleTicketFilter(e) {
    const filter = e.target.value;
    // Implement ticket filtering logic
  }

  // Notification functions
  function toggleNotifications() {
    const panel = document.getElementById('notification-panel');
    panel.classList.toggle('hidden');
  }

  function closeNotifications() {
    document.getElementById('notification-panel').classList.add('hidden');
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
    const data = {
      users: backend.getAllUsers(),
      courses: backend.getAllCourses(),
      certificates: backend.getAllCertificates(),
      tickets: backend.getAllSupportTickets(),
      exportDate: new Date().toISOString()
    };

    const dataStr = JSON.stringify(data, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(dataBlob);
    link.download = `admin-export-${new Date().toISOString().split('T')[0]}.json`;
    link.click();
  }

  // Chart functions
  function updateActivityChart() {
    const chart = document.getElementById('activity-chart');
    if (!chart) return;

    // Simple activity visualization
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

  // Load initial data
  function loadInitialData() {
    updateDashboardStats();
    updateActivityFeed();
    updateOnlineUsers();
    updateSyncStatus();
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminDashboard);
  } else {
    initAdminDashboard();
  }

  // Export functions for global use
  window.AdminSimple = {
    openUserModal,
    closeUserModal,
    editUser: (userId) => console.log('Edit user:', userId),
    deleteUser: (userId) => {
      if (confirm('Are you sure you want to delete this user?')) {
        console.log('Delete user:', userId);
      }
    }
  };

  // Don't auto-initialize - let the main script handle it
  // window.DiscipleshipBackend = new DiscipleshipBackend();

  // --- Event Files Section Logic ---
  function setupEventFilesSection() {
    const sectionId = 'event-files';
    const addBtn = document.getElementById('add-event-file-btn');
    const uploadForm = document.getElementById('event-file-upload-form');
    const cancelBtn = document.getElementById('cancel-event-file-upload');
    const eventSelect = document.getElementById('event-file-event');
    const fileInput = document.getElementById('event-file-input');
    const descInput = document.getElementById('event-file-description');
    const tableBody = document.getElementById('event-files-table-body');
    const createEventBtn = document.getElementById('create-event-btn');
    const createEventModal = document.getElementById('create-event-modal');
    const closeCreateEventModal = document.getElementById('close-create-event-modal');
    const createEventForm = document.getElementById('create-event-form');
    const newEventTitle = document.getElementById('new-event-title');
    const newEventDate = document.getElementById('new-event-date');
    const newEventLocation = document.getElementById('new-event-location');
    const newEventTags = document.getElementById('new-event-tags');
    const newEventDescription = document.getElementById('new-event-description');

    // Show upload form
    addBtn?.addEventListener('click', () => {
      uploadForm.classList.remove('hidden');
      addBtn.classList.add('hidden');
      populateEventDropdown();
    });
    // Hide upload form
    cancelBtn?.addEventListener('click', () => {
      uploadForm.classList.add('hidden');
      addBtn.classList.remove('hidden');
      uploadForm.reset();
    });
    // Populate event dropdown
    function populateEventDropdown() {
      const events = backend.getAllEvents();
      eventSelect.innerHTML = events.length
        ? events.map(e => `<option value="${e.id}">${e.title} (${e.date})</option>`).join('')
        : '<option value="">No events found</option>';
    }
    // Handle file upload
    uploadForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const eventId = eventSelect.value;
      const file = fileInput.files[0];
      if (!eventId || !file) return alert('Please select an event and file.');
      // Read file as base64
      const reader = new FileReader();
      reader.onload = function(evt) {
        const fileMeta = {
          name: file.name,
          type: file.type,
          size: file.size,
          description: descInput.value,
          uploaded: new Date().toISOString(),
          dataUrl: evt.target.result
        };
        backend.addEventFile(eventId, fileMeta);
        renderEventFilesTable();
        uploadForm.classList.add('hidden');
        addBtn.classList.remove('hidden');
        uploadForm.reset();
      };
      reader.readAsDataURL(file);
    });
    // Render files table
    function renderEventFilesTable() {
      const events = backend.getAllEvents();
      let rows = '';
      events.forEach(event => {
        (event.files||[]).forEach(file => {
          rows += `<tr>
            <td class="px-6 py-4 text-sm text-gray-900">${event.title}</td>
            <td class="px-6 py-4 text-sm text-blue-700 underline"><a href="${file.dataUrl}" download="${file.name}" target="_blank">${file.name}</a></td>
            <td class="px-6 py-4 text-sm">${file.type}</td>
            <td class="px-6 py-4 text-sm">${file.description||''}</td>
            <td class="px-6 py-4 text-xs text-gray-500">${new Date(file.uploaded).toLocaleString()}</td>
            <td class="px-6 py-4">
              <button class="text-red-600 hover:text-red-900 font-bold" data-event="${event.id}" data-file="${file.name}">Delete</button>
            </td>
          </tr>`;
        });
      });
      tableBody.innerHTML = rows || '<tr><td colspan="6" class="text-center text-gray-400 py-8">No event files uploaded yet.</td></tr>';
    }
    // Handle file deletion
    tableBody?.addEventListener('click', (e) => {
      if (e.target.tagName === 'BUTTON' && e.target.textContent === 'Delete') {
        const eventId = e.target.getAttribute('data-event');
        const fileName = e.target.getAttribute('data-file');
        if (confirm('Delete this file?')) {
          backend.removeEventFile(eventId, fileName);
          renderEventFilesTable();
        }
      }
    });
    // Initial render
    renderEventFilesTable();

    // Open modal
    createEventBtn?.addEventListener('click', () => {
      createEventModal.classList.remove('hidden');
    });
    // Close modal
    closeCreateEventModal?.addEventListener('click', () => {
      createEventModal.classList.add('hidden');
      createEventForm.reset();
    });
    // Handle event creation
    createEventForm?.addEventListener('submit', (e) => {
      e.preventDefault();
      const title = newEventTitle.value.trim();
      const date = newEventDate.value;
      const location = newEventLocation.value.trim();
      const tags = newEventTags.value.split(',').map(t => t.trim()).filter(Boolean);
      const description = newEventDescription.value.trim();
      if (!title || !date || !location) return alert('Please fill in all required fields.');
      backend.createEvent({ title, date, location, tags, description });
      createEventModal.classList.add('hidden');
      createEventForm.reset();
      populateEventDropdown();
      renderEventFilesTable();
      showMiniToast && showMiniToast('Event created!');
    });
  }

  // Call setup for Event Files section after dashboard init
  document.addEventListener('DOMContentLoaded', function() {
    setupEventFilesSection();
  });

})(); 