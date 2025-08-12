// Real-time Data Synchronization System
// Keeps all admin sections in sync with consistent data

(function() {
  const backend = window.DiscipleshipBackend;
  
  // Sync state
  let syncState = {
    lastSync: null,
    isSyncing: false,
    syncErrors: [],
    dataVersion: 0
  };

  // Initialize sync system
  function initSyncSystem() {
    createSyncStatusIndicator();
    startSyncPolling();
    setupDataConsistencyChecks();
    setupRealTimeExport();
  }

  // Create sync status indicator
  function createSyncStatusIndicator() {
    const header = document.querySelector('header');
    if (!header) return;

    const syncIndicator = document.createElement('div');
    syncIndicator.id = 'sync-indicator';
    syncIndicator.className = 'flex items-center gap-2 text-white text-sm';
    syncIndicator.innerHTML = `
      <div class="flex items-center gap-2">
        <div id="sync-status-dot" class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
        <span id="sync-status-text">Synced</span>
        <span id="sync-time" class="text-xs text-gray-300"></span>
      </div>
    `;

    // Insert after notification bell
    const notificationBell = document.getElementById('notification-bell');
    if (notificationBell) {
      notificationBell.parentNode.insertBefore(syncIndicator, notificationBell.nextSibling);
    }

    updateSyncStatus();
  }

  // Update sync status
  function updateSyncStatus() {
    const statusDot = document.getElementById('sync-status-dot');
    const statusText = document.getElementById('sync-status-text');
    const syncTime = document.getElementById('sync-time');

    if (!statusDot || !statusText || !syncTime) return;

    if (syncState.isSyncing) {
      statusDot.className = 'w-2 h-2 bg-yellow-500 rounded-full animate-spin';
      statusText.textContent = 'Syncing...';
      syncTime.textContent = '';
    } else if (syncState.syncErrors.length > 0) {
      statusDot.className = 'w-2 h-2 bg-red-500 rounded-full';
      statusText.textContent = 'Sync Error';
      syncTime.textContent = `${syncState.syncErrors.length} errors`;
    } else {
      statusDot.className = 'w-2 h-2 bg-green-500 rounded-full animate-pulse';
      statusText.textContent = 'Synced';
      if (syncState.lastSync) {
        syncTime.textContent = getTimeAgo(syncState.lastSync);
      }
    }
  }

  // Start sync polling
  function startSyncPolling() {
    // Sync every 10 seconds
    setInterval(() => {
      performDataSync();
    }, 10000);

    // Initial sync
    performDataSync();
  }

  // Perform data synchronization
  function performDataSync() {
    if (syncState.isSyncing) return;

    syncState.isSyncing = true;
    updateSyncStatus();

    try {
      // Sync users
      syncUsers();
      
      // Sync courses and progress
      syncCourses();
      
      // Sync certificates
      syncCertificates();
      
      // Sync support tickets
      syncSupportTickets();
      
      // Sync announcements
      syncAnnouncements();
      
      // Update data version
      syncState.dataVersion++;
      syncState.lastSync = new Date();
      syncState.syncErrors = [];
      
      // Trigger UI updates
      triggerUIUpdates();
      
    } catch (error) {
      console.error('Sync error:', error);
      syncState.syncErrors.push({
        timestamp: new Date(),
        error: error.message,
        type: 'sync_error'
      });
    } finally {
      syncState.isSyncing = false;
      updateSyncStatus();
    }
  }

  // Sync users data
  function syncUsers() {
    const users = backend.getAllUsers();
    const progress = backend.getAllProgress();
    
    // Merge user data with progress
    const usersWithProgress = users.map(user => {
      const userProgress = progress.find(p => p.userId === user.id);
      return {
        ...user,
        progress: userProgress || {},
        lastActivity: userProgress?.lastActivity || user.joinedDate,
        isOnline: isUserOnline(user.id)
      };
    });

    // Update localStorage with synced data
    localStorage.setItem('synced_users', JSON.stringify(usersWithProgress));
  }

  // Sync courses data
  function syncCourses() {
    const courses = backend.getAllCourses();
    const courseProgress = backend.getCourseProgress();
    
    // Merge course data with progress
    const coursesWithProgress = courses.map(course => {
      const progress = courseProgress.filter(p => p.courseId === course.id);
      return {
        ...course,
        enrolledUsers: progress.length,
        averageProgress: progress.length > 0 
          ? progress.reduce((sum, p) => sum + p.progress, 0) / progress.length 
          : 0
      };
    });

    localStorage.setItem('synced_courses', JSON.stringify(coursesWithProgress));
  }

  // Sync certificates data
  function syncCertificates() {
    const certificates = backend.getAllCertificates();
    const users = backend.getAllUsers();
    
    // Merge certificate data with user info
    const certificatesWithUsers = certificates.map(cert => {
      const user = users.find(u => u.id === cert.userId);
      return {
        ...cert,
        user: user || { name: 'Unknown User', email: 'unknown@example.com' }
      };
    });

    localStorage.setItem('synced_certificates', JSON.stringify(certificatesWithUsers));
  }

  // Sync support tickets data
  function syncSupportTickets() {
    const tickets = backend.getAllSupportTickets();
    const users = backend.getAllUsers();
    
    // Merge ticket data with user info
    const ticketsWithUsers = tickets.map(ticket => {
      const user = users.find(u => u.id === ticket.userId);
      return {
        ...ticket,
        user: user || { name: 'Unknown User', email: 'unknown@example.com' }
      };
    });

    localStorage.setItem('synced_tickets', JSON.stringify(ticketsWithUsers));
  }

  // Sync announcements data
  function syncAnnouncements() {
    const announcements = backend.getAllAnnouncements();
    localStorage.setItem('synced_announcements', JSON.stringify(announcements));
  }

  // Check if user is online
  function isUserOnline(userId) {
    const lastActivity = localStorage.getItem(`user_activity_${userId}`);
    if (!lastActivity) return false;
    
    const lastActivityTime = new Date(parseInt(lastActivity));
    const now = new Date();
    const diffMinutes = (now - lastActivityTime) / (1000 * 60);
    
    return diffMinutes < 5; // Online if activity in last 5 minutes
  }

  // Setup data consistency checks
  function setupDataConsistencyChecks() {
    // Check for orphaned data
    checkOrphanedData();
    
    // Check for data integrity
    checkDataIntegrity();
    
    // Fix inconsistencies
    fixDataInconsistencies();
  }

  // Check for orphaned data
  function checkOrphanedData() {
    const users = backend.getAllUsers();
    const progress = backend.getAllProgress();
    const certificates = backend.getAllCertificates();
    const tickets = backend.getAllSupportTickets();

    // Find orphaned progress records
    const orphanedProgress = progress.filter(p => 
      !users.find(u => u.id === p.userId)
    );

    // Find orphaned certificates
    const orphanedCertificates = certificates.filter(c => 
      !users.find(u => u.id === c.userId)
    );

    // Find orphaned tickets
    const orphanedTickets = tickets.filter(t => 
      !users.find(u => u.id === t.userId)
    );

    if (orphanedProgress.length > 0 || orphanedCertificates.length > 0 || orphanedTickets.length > 0) {
      console.warn('Found orphaned data:', {
        progress: orphanedProgress.length,
        certificates: orphanedCertificates.length,
        tickets: orphanedTickets.length
      });
    }
  }

  // Check data integrity
  function checkDataIntegrity() {
    const users = backend.getAllUsers();
    const progress = backend.getAllProgress();

    // Check for invalid progress values
    const invalidProgress = progress.filter(p => 
      p.progress < 0 || p.progress > 100 || isNaN(p.progress)
    );

    if (invalidProgress.length > 0) {
      console.warn('Found invalid progress values:', invalidProgress.length);
    }

    // Check for duplicate user IDs
    const userIds = users.map(u => u.id);
    const duplicateIds = userIds.filter((id, index) => userIds.indexOf(id) !== index);

    if (duplicateIds.length > 0) {
      console.error('Found duplicate user IDs:', duplicateIds);
    }
  }

  // Fix data inconsistencies
  function fixDataInconsistencies() {
    const progress = backend.getAllProgress();

    // Fix invalid progress values
    progress.forEach(p => {
      if (p.progress < 0) p.progress = 0;
      if (p.progress > 100) p.progress = 100;
      if (isNaN(p.progress)) p.progress = 0;
    });

    // Save fixed data
    backend.saveProgress(progress);
  }

  // Setup real-time export
  function setupRealTimeExport() {
    // Create export button in header
    const header = document.querySelector('header');
    if (!header) return;

    const exportButton = document.createElement('button');
    exportButton.id = 'export-data-btn';
    exportButton.className = 'px-4 py-2 bg-gradient-to-r from-[#FDBA17] to-[#E0A615] text-[#2046B3] rounded-lg hover:shadow-lg transition-all font-semibold text-sm flex items-center gap-2';
    exportButton.innerHTML = `
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      Export Data
    `;

    exportButton.addEventListener('click', exportAllData);

    // Insert before logout button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
      logoutBtn.parentNode.insertBefore(exportButton, logoutBtn);
    }
  }

  // Export all data
  function exportAllData() {
    const exportData = {
      timestamp: new Date().toISOString(),
      version: syncState.dataVersion,
      users: JSON.parse(localStorage.getItem('synced_users') || '[]'),
      courses: JSON.parse(localStorage.getItem('synced_courses') || '[]'),
      certificates: JSON.parse(localStorage.getItem('synced_certificates') || '[]'),
      tickets: JSON.parse(localStorage.getItem('synced_tickets') || '[]'),
      announcements: JSON.parse(localStorage.getItem('synced_announcements') || '[]'),
      syncState: syncState
    };

    const dataStr = JSON.stringify(exportData, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(dataBlob);
    link.download = `admin-data-export-${new Date().toISOString().split('T')[0]}.json`;
    link.click();

    // Show success notification
    if (window.AdminNotifications) {
      window.AdminNotifications.addNotification('system_alert', {
        message: 'Data export completed successfully'
      });
    }
  }

  // Trigger UI updates
  function triggerUIUpdates() {
    // Update all admin sections
    if (window.ActivityDashboard) {
      window.ActivityDashboard.updateActivityFeed();
      window.ActivityDashboard.updateOnlineUsers();
      window.ActivityDashboard.updateCharts();
    }

    // Update real-time components
    if (window.AdminNotifications) {
      window.AdminNotifications.loadNotificationHistory();
    }

    // Update tables
    updateAllTables();
  }

  // Update all tables
  function updateAllTables() {
    // Update user table
    const userTableBody = document.getElementById('user-table-body');
    if (userTableBody) {
      const users = JSON.parse(localStorage.getItem('synced_users') || '[]');
      userTableBody.innerHTML = users.map(user => renderUserRow(user)).join('');
    }

    // Update certificate table
    const certTableBody = document.getElementById('certificate-table-body');
    if (certTableBody) {
      const certificates = JSON.parse(localStorage.getItem('synced_certificates') || '[]');
      certTableBody.innerHTML = certificates.map(cert => renderCertificateRow(cert)).join('');
    }

    // Update support table
    const supportTableBody = document.getElementById('support-table-body');
    if (supportTableBody) {
      const tickets = JSON.parse(localStorage.getItem('synced_tickets') || '[]');
      supportTableBody.innerHTML = tickets.map(ticket => renderTicketRow(ticket)).join('');
    }
  }

  // Render user row
  function renderUserRow(user) {
    const statusClass = user.isOnline ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
    const statusText = user.isOnline ? 'Online' : 'Offline';
    
    return `
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="flex items-center">
            <div class="w-8 h-8 bg-gradient-to-br from-[#7C3AED] to-[#2046B3] rounded-full flex items-center justify-center text-white text-sm font-semibold">
              ${user.profile?.avatar?.initials || user.name.substring(0, 2).toUpperCase()}
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">${user.name}</div>
              <div class="text-sm text-gray-500">${user.email}</div>
            </div>
          </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
            ${statusText}
          </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.progress?.completedLessons || 0}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${Math.round(user.progress?.progress || 0)}%</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(user.lastActivity).toLocaleDateString()}</td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
          <button class="text-[#7C3AED] hover:text-[#6D28D9] mr-3">Edit</button>
          <button class="text-red-600 hover:text-red-900">Delete</button>
        </td>
      </tr>
    `;
  }

  // Render certificate row
  function renderCertificateRow(cert) {
    return `
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${cert.id}</td>
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="text-sm font-medium text-gray-900">${cert.user?.name || 'Unknown'}</div>
          <div class="text-sm text-gray-500">${cert.user?.email || 'No email'}</div>
        </td>
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
    `;
  }

  // Render ticket row
  function renderTicketRow(ticket) {
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
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${ticket.subject}</td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${priorityClass}">
            ${ticket.priority}
          </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${new Date(ticket.createdDate).toLocaleDateString()}</td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
          <button class="text-[#7C3AED] hover:text-[#6D28D9] mr-3">View</button>
          <button class="text-[#FDBA17] hover:text-[#E0A615]">Reply</button>
        </td>
      </tr>
    `;
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

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSyncSystem);
  } else {
    initSyncSystem();
  }

  // Export functions for external use
  window.AdminSync = {
    performDataSync,
    exportAllData,
    updateSyncStatus
  };

})(); 