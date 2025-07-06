// Admin Notification System
// Real-time notifications for the admin portal

(function() {
  const backend = window.DiscipleshipBackend;
  
  // Notification types and their configurations
  const notificationTypes = {
    'new_user': {
      icon: '👤',
      color: 'blue',
      title: 'New User Registration',
      sound: 'notification.mp3'
    },
    'new_support_ticket': {
      icon: '🎫',
      color: 'red',
      title: 'New Support Ticket',
      sound: 'alert.mp3'
    },
    'certificate_request': {
      icon: '🎓',
      color: 'purple',
      title: 'Certificate Request',
      sound: 'notification.mp3'
    },
    'system_alert': {
      icon: '⚠️',
      color: 'orange',
      title: 'System Alert',
      sound: 'warning.mp3'
    },
    'user_login': {
      icon: '🔐',
      color: 'green',
      title: 'User Login',
      sound: 'notification.mp3'
    },
    'progress_update': {
      icon: '📈',
      color: 'blue',
      title: 'Progress Update',
      sound: 'notification.mp3'
    }
  };

  // Notification queue
  let notificationQueue = [];
  let isShowingNotification = false;

  // Initialize notification system
  function initNotificationSystem() {
    createNotificationBell();
    createNotificationPanel();
    loadNotificationHistory();
    startNotificationPolling();
  }

  // Create notification bell in header
  function createNotificationBell() {
    const header = document.querySelector('header');
    if (!header) return;

    const notificationBell = document.createElement('div');
    notificationBell.className = 'relative';
    notificationBell.innerHTML = `
      <button id="notification-bell" class="relative p-2 text-white hover:text-[#FDBA17] transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19h6a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <span id="notification-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
      </button>
    `;

    // Insert before logout button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
      logoutBtn.parentNode.insertBefore(notificationBell, logoutBtn);
    }

    // Add click event
    document.getElementById('notification-bell').addEventListener('click', toggleNotificationPanel);
  }

  // Create notification panel
  function createNotificationPanel() {
    const notificationPanel = document.createElement('div');
    notificationPanel.id = 'notification-panel';
    notificationPanel.className = 'fixed top-20 right-4 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 z-50 hidden max-h-96 overflow-hidden';
    notificationPanel.innerHTML = `
      <div class="bg-gradient-to-r from-[#7C3AED] to-[#2046B3] text-white p-4 rounded-t-xl">
        <div class="flex items-center justify-between">
          <h3 class="font-bold">Notifications</h3>
          <button id="close-notifications" class="text-white hover:text-[#FDBA17] transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="flex items-center gap-2 mt-2">
          <span id="notification-count" class="text-sm">0 notifications</span>
          <button id="mark-all-read" class="text-xs underline hover:text-[#FDBA17]">Mark all read</button>
        </div>
      </div>
      <div id="notification-list" class="max-h-64 overflow-y-auto">
        <!-- Notifications will be populated here -->
      </div>
      <div class="p-3 border-t border-gray-200 bg-gray-50">
        <button id="view-all-notifications" class="w-full text-center text-[#7C3AED] hover:text-[#6D28D9] text-sm font-medium">
          View All Notifications
        </button>
      </div>
    `;

    document.body.appendChild(notificationPanel);

    // Add event listeners
    document.getElementById('close-notifications').addEventListener('click', toggleNotificationPanel);
    document.getElementById('mark-all-read').addEventListener('click', markAllAsRead);
    document.getElementById('view-all-notifications').addEventListener('click', viewAllNotifications);
  }

  // Toggle notification panel
  function toggleNotificationPanel() {
    const panel = document.getElementById('notification-panel');
    if (panel) {
      panel.classList.toggle('hidden');
      if (!panel.classList.contains('hidden')) {
        loadNotificationHistory();
      }
    }
  }

  // Add notification to queue
  function addNotification(type, data) {
    const notification = {
      id: 'notif_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
      type: type,
      data: data,
      timestamp: new Date().toISOString(),
      read: false
    };

    notificationQueue.push(notification);
    saveNotificationHistory();
    updateNotificationBadge();
    showNotificationToast(notification);
  }

  // Show notification toast
  function showNotificationToast(notification) {
    const config = notificationTypes[notification.type] || notificationTypes.system_alert;
    
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 bg-white border-l-4 border-${config.color}-500 shadow-lg rounded-lg p-4 z-50 transform translate-x-full transition-transform duration-300 max-w-sm`;
    toast.innerHTML = `
      <div class="flex items-start gap-3">
        <div class="text-2xl">${config.icon}</div>
        <div class="flex-1">
          <h4 class="font-semibold text-gray-900">${config.title}</h4>
          <p class="text-sm text-gray-600 mt-1">${getNotificationMessage(notification)}</p>
          <p class="text-xs text-gray-400 mt-2">${new Date(notification.timestamp).toLocaleTimeString()}</p>
        </div>
        <button class="text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.remove()">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    `;

    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => {
      toast.classList.remove('translate-x-full');
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
      toast.classList.add('translate-x-full');
      setTimeout(() => {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 300);
    }, 5000);

    // Play sound if available
    playNotificationSound(config.sound);
  }

  // Get notification message
  function getNotificationMessage(notification) {
    switch (notification.type) {
      case 'new_user':
        return `New user registered: ${notification.data.name || 'Unknown'}`;
      case 'new_support_ticket':
        return `New support ticket from ${notification.data.user || 'Unknown'}`;
      case 'certificate_request':
        return `Certificate request for ${notification.data.courseName || 'Unknown course'}`;
      case 'user_login':
        return `${notification.data.name || 'User'} logged in`;
      case 'progress_update':
        return `${notification.data.userName || 'User'} completed a lesson`;
      default:
        return notification.data.message || 'New notification';
    }
  }

  // Play notification sound
  function playNotificationSound(soundFile) {
    // In a real implementation, you would play actual sound files
    // For now, we'll just log the sound that would be played
    console.log(`Playing notification sound: ${soundFile}`);
  }

  // Update notification badge
  function updateNotificationBadge() {
    const badge = document.getElementById('notification-badge');
    const unreadCount = notificationQueue.filter(n => !n.read).length;
    
    if (badge) {
      if (unreadCount > 0) {
        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    }
  }

  // Load notification history
  function loadNotificationHistory() {
    const stored = localStorage.getItem('admin_notifications');
    if (stored) {
      notificationQueue = JSON.parse(stored);
    }
    renderNotificationList();
    updateNotificationBadge();
  }

  // Save notification history
  function saveNotificationHistory() {
    localStorage.setItem('admin_notifications', JSON.stringify(notificationQueue));
  }

  // Render notification list
  function renderNotificationList() {
    const list = document.getElementById('notification-list');
    const count = document.getElementById('notification-count');
    
    if (!list) return;

    const unreadCount = notificationQueue.filter(n => !n.read).length;
    if (count) {
      count.textContent = `${unreadCount} notification${unreadCount !== 1 ? 's' : ''}`;
    }

    if (notificationQueue.length === 0) {
      list.innerHTML = `
        <div class="p-6 text-center text-gray-500">
          <div class="text-4xl mb-2">🔔</div>
          <p>No notifications yet</p>
        </div>
      `;
      return;
    }

    list.innerHTML = notificationQueue
      .slice(-10) // Show last 10 notifications
      .reverse()
      .map(notification => {
        const config = notificationTypes[notification.type] || notificationTypes.system_alert;
        return `
          <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors ${!notification.read ? 'bg-blue-50' : ''}" data-notification-id="${notification.id}">
            <div class="flex items-start gap-3">
              <div class="text-xl">${config.icon}</div>
              <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-gray-900 text-sm">${config.title}</h4>
                <p class="text-sm text-gray-600 mt-1">${getNotificationMessage(notification)}</p>
                <p class="text-xs text-gray-400 mt-2">${new Date(notification.timestamp).toLocaleString()}</p>
              </div>
              ${!notification.read ? '<div class="w-2 h-2 bg-blue-500 rounded-full"></div>' : ''}
            </div>
          </div>
        `;
      })
      .join('');
  }

  // Mark all notifications as read
  function markAllAsRead() {
    notificationQueue.forEach(notification => {
      notification.read = true;
    });
    saveNotificationHistory();
    renderNotificationList();
    updateNotificationBadge();
  }

  // View all notifications (could open a modal or navigate to notifications page)
  function viewAllNotifications() {
    // For now, just close the panel
    toggleNotificationPanel();
    // In a real implementation, you might open a full notifications page
    console.log('View all notifications clicked');
  }

  // Start notification polling
  function startNotificationPolling() {
    setInterval(() => {
      checkForNewNotifications();
    }, 5000); // Check every 5 seconds
  }

  // Check for new notifications
  function checkForNewNotifications() {
    // Check for new users
    const users = backend.getAllUsers();
    const lastCheck = localStorage.getItem('last_notification_check') || '0';
    const newUsers = users.filter(user => 
      new Date(user.joinedDate) > new Date(parseInt(lastCheck))
    );

    newUsers.forEach(user => {
      addNotification('new_user', { name: user.name, email: user.email });
    });

    // Check for new support tickets
    const tickets = backend.getAllSupportTickets();
    const newTickets = tickets.filter(ticket =>
      new Date(ticket.createdDate) > new Date(parseInt(lastCheck))
    );

    newTickets.forEach(ticket => {
      addNotification('new_support_ticket', { 
        user: ticket.user?.name || 'Unknown',
        issue: ticket.subject || 'No subject'
      });
    });

    // Check for new certificates
    const certificates = backend.getAllCertificates();
    const newCertificates = certificates.filter(cert =>
      new Date(cert.issuedDate) > new Date(parseInt(lastCheck))
    );

    newCertificates.forEach(cert => {
      addNotification('certificate_request', {
        courseName: cert.courseName,
        userName: cert.user?.name || 'Unknown'
      });
    });

    // Update last check time
    localStorage.setItem('last_notification_check', Date.now().toString());
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNotificationSystem);
  } else {
    initNotificationSystem();
  }

  // Export functions for external use
  window.AdminNotifications = {
    addNotification,
    markAllAsRead,
    loadNotificationHistory
  };

})(); 