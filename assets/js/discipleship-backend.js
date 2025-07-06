// Backend Simulation for Discipleship Portal
// In a real application, this would be server-side code

class DiscipleshipBackend {
  constructor() {
    this.users = this.loadUsers();
    this.progress = this.loadProgress();
    this.courses = this.loadCourses();
    this.certificates = this.loadCertificates();
    this.supportTickets = this.loadSupportTickets();
    this.announcements = this.loadAnnouncements();
    this.realtimeConnections = new Map();
    this.activityLog = this.loadActivityLog();
    
    // Ensure admin user exists
    this.ensureAdminUser();
    
    // Initialize real-time features
    this.initializeRealtimeFeatures();
    this.startPeriodicUpdates();
  }

  // Real-time initialization
  initializeRealtimeFeatures() {
    // Simulate WebSocket connections
    this.setupRealtimeSimulation();
    
    // Start activity monitoring
    this.monitorUserActivity();
    
    // Initialize admin dashboard updates
    this.initializeAdminUpdates();
  }

  setupRealtimeSimulation() {
    // Simulate real-time connections for demo purposes
    setInterval(() => {
      this.broadcastUserActivity();
      this.updateAdminDashboard();
      this.checkForNewActivities();
    }, 5000); // Update every 5 seconds
  }

  startPeriodicUpdates() {
    // Update various system components periodically
    setInterval(() => {
      this.updateSystemStats();
      this.processPendingActions();
      this.cleanupOldData();
    }, 30000); // Every 30 seconds
  }

  // Real-time broadcasting
  broadcastUserActivity() {
    const activeUsers = this.getActiveUsers();
    const recentActivity = this.getRecentActivity();
    
    // Simulate broadcasting to connected clients
    this.realtimeConnections.forEach((connection, userId) => {
      if (connection.type === 'admin') {
        this.sendToAdmin(userId, {
          type: 'user_activity_update',
          data: {
            activeUsers,
            recentActivity,
            timestamp: new Date().toISOString()
          }
        });
      } else if (connection.type === 'user') {
        this.sendToUser(userId, {
          type: 'progress_update',
          data: this.getUserProgress(userId)
        });
      }
    });
  }

  // Admin dashboard real-time updates
  initializeAdminUpdates() {
    setInterval(() => {
      this.updateAdminStats();
      this.updateUserTable();
      this.updateCertificateTable();
      this.updateSupportTable();
      this.updateAnnouncementTable();
    }, 3000); // Update every 3 seconds
  }

  updateAdminStats() {
    const stats = this.getSystemStats();
    this.broadcastToAdmins({
      type: 'stats_update',
      data: stats
    });
  }

  updateUserTable() {
    const users = this.getAllUsersWithProgress();
    this.broadcastToAdmins({
      type: 'users_update',
      data: users
    });
  }

  updateCertificateTable() {
    const certificates = this.getAllCertificates();
    this.broadcastToAdmins({
      type: 'certificates_update',
      data: certificates
    });
  }

  updateSupportTable() {
    const tickets = this.getAllSupportTickets();
    this.broadcastToAdmins({
      type: 'support_update',
      data: tickets
    });
  }

  updateAnnouncementTable() {
    const announcements = this.getAllAnnouncements();
    this.broadcastToAdmins({
      type: 'announcements_update',
      data: announcements
    });
  }

  // User activity monitoring
  monitorUserActivity() {
    setInterval(() => {
      this.trackUserSessions();
      this.updateOnlineStatus();
      this.logSystemActivity();
    }, 10000); // Every 10 seconds
  }

  trackUserSessions() {
    const now = new Date().toISOString();
    Object.keys(this.users).forEach(userId => {
      const user = this.users[userId];
      const lastActivity = new Date(user.lastActivity || user.lastLogin);
      const timeDiff = (new Date() - lastActivity) / 1000; // seconds
      
      // Mark as online if activity within last 5 minutes
      user.isOnline = timeDiff < 300;
      user.lastSeen = now;
    });
    this.saveUsers();
  }

  updateOnlineStatus() {
    const onlineUsers = Object.values(this.users).filter(user => user.isOnline);
    this.broadcastToAdmins({
      type: 'online_status_update',
      data: {
        onlineCount: onlineUsers.length,
        totalUsers: Object.keys(this.users).length,
        onlineUsers: onlineUsers.map(u => ({ id: u.id, name: u.name, lastActivity: u.lastActivity }))
      }
    });
  }

  // Enhanced user management with real-time features
  createUser(userData) {
    const userId = Date.now().toString();
    const user = {
      id: userId,
      name: userData.name,
      email: userData.email,
      password: this.hashPassword(userData.password),
      joinedDate: new Date().toISOString(),
      lastLogin: new Date().toISOString(),
      lastActivity: new Date().toISOString(),
      isOnline: false,
      status: 'active',
      role: 'student',
      profile: {
        phone: userData.phone || '',
        church: userData.church || '',
        location: userData.location || '',
        avatar: this.generateAvatar(userData.name)
      },
      preferences: {
        notifications: true,
        emailUpdates: true,
        smsUpdates: false
      }
    };

    this.users[userId] = user;
    this.saveUsers();
    this.initializeUserProgress(userId);
    this.logActivity('user_created', { userId, userData });
    
    // Notify admins of new user
    this.broadcastToAdmins({
      type: 'new_user',
      data: user
    });

    return user;
  }

  authenticateUser(email, password) {
    console.log('Attempting to authenticate:', email);
    console.log('Available users:', Object.values(this.users).map(u => ({ email: u.email, role: u.role })));
    
    const user = Object.values(this.users).find(u => u.email === email);
    
    if (user) {
      console.log('User found:', user.email, 'Role:', user.role);
      const passwordValid = this.verifyPassword(password, user.password);
      console.log('Password valid:', passwordValid);
      
      if (passwordValid) {
        user.lastLogin = new Date().toISOString();
        user.lastActivity = new Date().toISOString();
        user.isOnline = true;
        user.loginCount = (user.loginCount || 0) + 1;
        this.saveUsers();
        
        this.logActivity('user_login', { userId: user.id, email });
        
        // Notify admins of user login
        this.broadcastToAdmins({
          type: 'user_login',
          data: { userId: user.id, name: user.name, timestamp: new Date().toISOString() }
        });
        
        return user;
      }
    }
    
    console.log('Authentication failed for:', email);
    return null;
  }

  // Function to manually create admin user
  createAdminUser() {
    const adminUser = {
      id: 'admin_001',
      name: 'Admin User',
      email: 'admin@heartsaftergod.org',
      password: this.hashPassword('admin123'),
      joinedDate: new Date().toISOString(),
      lastLogin: new Date().toISOString(),
      lastActivity: new Date().toISOString(),
      isOnline: false,
      status: 'active',
      role: 'admin',
      profile: {
        phone: '+254 700 000 000',
        church: 'Hearts After God Ministry',
        location: 'Nairobi, Kenya',
        avatar: this.generateAvatar('Admin User')
      },
      preferences: {
        notifications: true,
        emailUpdates: true,
        smsUpdates: false
      }
    };

    this.users['admin_001'] = adminUser;
    this.saveUsers();
    console.log('Admin user created:', adminUser.email);
    return adminUser;
  }

  // Function to ensure admin user exists
  ensureAdminUser() {
    const adminExists = Object.values(this.users).find(u => u.email === 'admin@heartsaftergod.org');
    if (!adminExists) {
      console.log('Admin user not found, creating...');
      this.createAdminUser();
    } else {
      console.log('Admin user already exists:', adminExists.email);
    }
  }

  // Enhanced progress tracking with real-time updates
  updateUserProgress(userId, progressData) {
    const user = this.users[userId];
    if (!user) return;

    user.lastActivity = new Date().toISOString();
    this.progress[userId] = { ...this.progress[userId], ...progressData };
    this.saveProgress();
    this.saveUsers();

    // Log activity
    this.logActivity('progress_updated', { userId, progressData });

    // Notify admins
    this.broadcastToAdmins({
      type: 'progress_updated',
      data: {
        userId,
        userName: user.name,
        progress: this.progress[userId],
        timestamp: new Date().toISOString()
      }
    });

    // Check for achievements
    this.checkForAchievements(userId);
  }

  // Enhanced certificate management
  issueCertificate(certificateData) {
    const certificateId = `cert_${Date.now()}`;
    const certificate = {
      id: certificateId,
      ...certificateData,
      issuedDate: new Date().toISOString(),
      status: 'issued',
      certificateNumber: `HAGM-${certificateData.type.toUpperCase()}-${Date.now()}`,
      issuedBy: certificateData.issuedBy || 'System',
      verificationUrl: `https://heartsaftergod.org/verify/${certificateId}`
    };

    this.certificates[certificateId] = certificate;
    this.saveCertificates();

    // Update user's certificates
    const userProgress = this.getUserProgress(certificateData.userId);
    if (!userProgress.certificates) userProgress.certificates = [];
    userProgress.certificates.push(certificateId);
    this.updateUserProgress(certificateData.userId, userProgress);

    // Log activity
    this.logActivity('certificate_issued', { certificateId, certificateData });

    // Notify admins
    this.broadcastToAdmins({
      type: 'certificate_issued',
      data: certificate
    });

    // Send notification to user
    this.sendUserNotification(certificateData.userId, {
      type: 'certificate_issued',
      title: 'Certificate Issued!',
      message: `Congratulations! Your ${certificateData.courseName} certificate has been issued.`,
      data: certificate
    });

    return certificate;
  }

  // Enhanced support ticket management
  createSupportTicket(ticketData) {
    const ticketId = `ticket_${Date.now()}`;
    const ticket = {
      id: ticketId,
      ...ticketData,
      createdDate: new Date().toISOString(),
      status: 'open',
      priority: ticketData.priority || 'medium',
      assignedTo: null,
      responses: [],
      lastUpdated: new Date().toISOString()
    };

    this.supportTickets[ticketId] = ticket;
    this.saveSupportTickets();

    // Log activity
    this.logActivity('support_ticket_created', { ticketId, ticketData });

    // Notify admins
    this.broadcastToAdmins({
      type: 'new_support_ticket',
      data: ticket
    });

    return ticket;
  }

  updateSupportTicket(ticketId, updates) {
    const ticket = this.supportTickets[ticketId];
    if (!ticket) return null;

    Object.assign(ticket, updates, {
      lastUpdated: new Date().toISOString()
    });

    this.saveSupportTickets();

    // Log activity
    this.logActivity('support_ticket_updated', { ticketId, updates });

    // Notify admins
    this.broadcastToAdmins({
      type: 'support_ticket_updated',
      data: ticket
    });

    return ticket;
  }

  // Enhanced announcement system
  createAnnouncement(announcementData) {
    const announcementId = `announcement_${Date.now()}`;
    const announcement = {
      id: announcementId,
      ...announcementData,
      createdDate: new Date().toISOString(),
      status: 'active',
      views: 0,
      responses: 0,
      createdBy: announcementData.createdBy || 'Admin'
    };

    this.announcements[announcementId] = announcement;
    this.saveAnnouncements();

    // Log activity
    this.logActivity('announcement_created', { announcementId, announcementData });

    // Notify all users
    this.broadcastToUsers({
      type: 'new_announcement',
      data: announcement
    });

    return announcement;
  }

  // Real-time communication methods
  sendToAdmin(adminId, message) {
    const connection = this.realtimeConnections.get(adminId);
    if (connection && connection.type === 'admin') {
      // In real implementation, this would send via WebSocket
      this.handleAdminMessage(adminId, message);
    }
  }

  sendToUser(userId, message) {
    const connection = this.realtimeConnections.get(userId);
    if (connection && connection.type === 'user') {
      // In real implementation, this would send via WebSocket
      this.handleUserMessage(userId, message);
    }
  }

  broadcastToAdmins(message) {
    this.realtimeConnections.forEach((connection, id) => {
      if (connection.type === 'admin') {
        this.sendToAdmin(id, message);
      }
    });
  }

  broadcastToUsers(message) {
    this.realtimeConnections.forEach((connection, id) => {
      if (connection.type === 'user') {
        this.sendToUser(id, message);
      }
    });
  }

  // Activity logging
  logActivity(type, data) {
    const logEntry = {
      id: `log_${Date.now()}`,
      type,
      data,
      timestamp: new Date().toISOString(),
      userId: data.userId || 'system'
    };

    this.activityLog.push(logEntry);
    
    // Keep only last 1000 activities
    if (this.activityLog.length > 1000) {
      this.activityLog = this.activityLog.slice(-1000);
    }

    this.saveActivityLog();
  }

  getRecentActivity(limit = 50) {
    return this.activityLog.slice(-limit).reverse();
  }

  // Enhanced analytics
  getSystemStats() {
    const totalUsers = Object.keys(this.users).length;
    const activeUsers = Object.values(this.users).filter(u => u.isOnline).length;
    const totalCertificates = Object.keys(this.certificates).length;
    const openTickets = Object.values(this.supportTickets).filter(t => t.status === 'open').length;
    const activeAnnouncements = Object.values(this.announcements).filter(a => a.status === 'active').length;

    return {
      totalUsers,
      activeUsers,
      totalCertificates,
      openTickets,
      activeAnnouncements,
      systemUptime: this.getSystemUptime(),
      lastUpdated: new Date().toISOString()
    };
  }

  // Utility methods
  generateAvatar(name) {
    const initials = name.split(' ').map(n => n[0]).join('').toUpperCase();
    const colors = ['#7C3AED', '#FDBA17', '#2046B3', '#10B981', '#F59E0B'];
    const color = colors[Math.floor(Math.random() * colors.length)];
    return { initials, color };
  }

  getSystemUptime() {
    // Simulate system uptime
    return Math.floor(Math.random() * 100) + 90; // 90-99% uptime
  }

  // Data persistence methods
  loadActivityLog() {
    const stored = localStorage.getItem('discipleship_activity_log');
    return stored ? JSON.parse(stored) : [];
  }

  saveActivityLog() {
    localStorage.setItem('discipleship_activity_log', JSON.stringify(this.activityLog));
  }

  loadCourses() {
    const stored = localStorage.getItem('discipleship_courses');
    return stored ? JSON.parse(stored) : this.getDefaultCourses();
  }

  getDefaultCourses() {
    return {
      'foundations': {
        id: 'foundations',
        name: 'Foundations of Faith',
        description: 'Core Discipleship Course',
        duration: '12 weeks',
        level: 'beginner',
        credits: 3,
        lessons: 14
      },
      'prayer': {
        id: 'prayer',
        name: 'Prayer & Devotion',
        description: 'Spiritual Growth Course',
        duration: '8 weeks',
        level: 'intermediate',
        credits: 2,
        lessons: 10
      },
      'leadership': {
        id: 'leadership',
        name: 'Christian Leadership',
        description: 'Leadership Development',
        duration: '16 weeks',
        level: 'advanced',
        credits: 4,
        lessons: 20
      }
    };
  }

  // Enhanced data retrieval methods
  getAllUsersWithProgress() {
    return Object.values(this.users).map(user => ({
      ...user,
      progress: this.getUserProgress(user.id),
      stats: this.getUserStats(user.id)
    }));
  }

  getAllCertificates() {
    return Array.isArray(this.certificates) ? this.certificates : [];
  }

  getAllSupportTickets() {
    return Array.isArray(this.supportTickets) ? this.supportTickets : [];
  }

  getAllAnnouncements() {
    return Array.isArray(this.announcements) ? this.announcements : [];
  }

  // User notification system
  sendUserNotification(userId, notification) {
    const user = this.users[userId];
    if (!user) return;

    if (!user.notifications) user.notifications = [];
    user.notifications.push({
      id: `notif_${Date.now()}`,
      ...notification,
      timestamp: new Date().toISOString(),
      read: false
    });

    this.saveUsers();

    // Send real-time notification
    this.sendToUser(userId, {
      type: 'notification',
      data: notification
    });
  }

  // Message handlers (for real-time communication)
  handleAdminMessage(adminId, message) {
    // Handle admin-specific messages
    console.log(`Admin ${adminId} received:`, message);
    
    // Update admin dashboard in real-time
    if (message.type === 'stats_update') {
      this.updateAdminDashboardStats(message.data);
    } else if (message.type === 'users_update') {
      this.updateAdminUserTable(message.data);
    } else if (message.type === 'certificates_update') {
      this.updateAdminCertificateTable(message.data);
    }
  }

  handleUserMessage(userId, message) {
    // Handle user-specific messages
    console.log(`User ${userId} received:`, message);
    
    // Update user dashboard in real-time
    if (message.type === 'progress_update') {
      this.updateUserDashboard(message.data);
    } else if (message.type === 'notification') {
      this.showUserNotification(message.data);
    }
  }

  // Dashboard update methods
  updateAdminDashboardStats(stats) {
    // Update admin dashboard statistics in real-time
    const statsElements = document.querySelectorAll('[data-stat]');
    statsElements.forEach(element => {
      const statType = element.getAttribute('data-stat');
      if (stats[statType] !== undefined) {
        element.textContent = stats[statType];
      }
    });
  }

  updateAdminUserTable(users) {
    // Update admin user table in real-time
    const userTableBody = document.getElementById('user-table-body');
    if (userTableBody) {
      userTableBody.innerHTML = users.map(user => this.renderUserRow(user)).join('');
    }
  }

  updateAdminCertificateTable(certificates) {
    // Update admin certificate table in real-time
    const certTableBody = document.getElementById('certificate-table-body');
    if (certTableBody) {
      certTableBody.innerHTML = certificates.map(cert => this.renderCertificateRow(cert)).join('');
    }
  }

  updateUserDashboard(progress) {
    // Update user dashboard in real-time
    const progressElements = document.querySelectorAll('[data-progress]');
    progressElements.forEach(element => {
      const progressType = element.getAttribute('data-progress');
      if (progress[progressType] !== undefined) {
        element.textContent = progress[progressType];
      }
    });
  }

  showUserNotification(notification) {
    // Show user notification in real-time
    const notificationContainer = document.getElementById('notification-container');
    if (notificationContainer) {
      const notificationElement = document.createElement('div');
      notificationElement.className = 'notification-item';
      notificationElement.innerHTML = `
        <div class="notification-content">
          <h4>${notification.title}</h4>
          <p>${notification.message}</p>
          <small>${new Date(notification.timestamp).toLocaleString()}</small>
        </div>
      `;
      notificationContainer.appendChild(notificationElement);
      
      // Auto-remove after 5 seconds
      setTimeout(() => {
        notificationElement.remove();
      }, 5000);
    }
  }

  // Render methods for real-time updates
  renderUserRow(user) {
    return `
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-6 py-4">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-gradient-to-br from-[#7C3AED] to-[#2046B3] rounded-full flex items-center justify-center text-white text-sm font-semibold">
              ${user.profile?.avatar?.initials || user.name.substring(0, 2).toUpperCase()}
            </div>
            <div>
              <p class="font-semibold text-gray-900">${user.name}</p>
              <p class="text-sm text-gray-500">${user.email}</p>
            </div>
          </div>
        </td>
        <td class="px-6 py-4">
          <span class="px-3 py-1 text-xs font-semibold rounded-full ${user.isOnline ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
            ${user.isOnline ? 'Online' : 'Offline'}
          </span>
        </td>
        <td class="px-6 py-4">
          <div class="text-sm">
            <p class="text-gray-900">${user.progress?.completedLessons?.length || 0} lessons</p>
            <p class="text-gray-500">${Math.round((user.progress?.completedLessons?.length || 0) / 14 * 100)}% complete</p>
          </div>
        </td>
        <td class="px-6 py-4">
          <div class="text-sm">
            <p class="text-gray-900">${new Date(user.lastActivity).toLocaleDateString()}</p>
            <p class="text-gray-500">${new Date(user.lastActivity).toLocaleTimeString()}</p>
          </div>
        </td>
        <td class="px-6 py-4">
          <div class="flex items-center gap-2">
            <button class="p-2 text-[#7C3AED] hover:bg-[#7C3AED]/10 rounded-lg transition-colors" title="View Profile">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
            <button class="p-2 text-[#FDBA17] hover:bg-[#FDBA17]/10 rounded-lg transition-colors" title="Edit User" onclick="openEditUserModal('${user.id}')">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </button>
            <button class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Reset Password" onclick="openResetPasswordModal('${user.id}')">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
              </svg>
            </button>
          </div>
        </td>
      </tr>
    `;
  }

  renderCertificateRow(certificate) {
    return `
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-6 py-4">
          <span class="font-mono text-sm text-[#7C3AED] font-semibold">${certificate.certificateNumber}</span>
        </td>
        <td class="px-6 py-4">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-gradient-to-br from-[#7C3AED] to-[#2046B3] rounded-full flex items-center justify-center text-white text-sm font-semibold">
              ${certificate.user?.profile?.avatar?.initials || certificate.user?.name?.substring(0, 2).toUpperCase()}
            </div>
            <div>
              <p class="font-semibold text-gray-900">${certificate.user?.name}</p>
              <p class="text-sm text-gray-500">${certificate.user?.email}</p>
            </div>
          </div>
        </td>
        <td class="px-6 py-4">
          <div class="max-w-xs">
            <p class="font-medium text-gray-900">${certificate.courseName}</p>
            <p class="text-sm text-gray-500">${certificate.type}</p>
          </div>
        </td>
        <td class="px-6 py-4">
          <div class="text-sm">
            <p class="text-gray-900">${new Date(certificate.issuedDate).toLocaleDateString()}</p>
            <p class="text-gray-500">${new Date(certificate.issuedDate).toLocaleTimeString()}</p>
          </div>
        </td>
        <td class="px-6 py-4">
          <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">${certificate.status}</span>
        </td>
        <td class="px-6 py-4">
          <div class="flex items-center gap-2">
            <button class="p-2 text-[#7C3AED] hover:bg-[#7C3AED]/10 rounded-lg transition-colors" title="View Certificate">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
            <button class="p-2 text-[#FDBA17] hover:bg-[#FDBA17]/10 rounded-lg transition-colors" title="Download">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </button>
          </div>
        </td>
      </tr>
    `;
  }

  // Initialize real-time connections
  connectUser(userId) {
    this.realtimeConnections.set(userId, {
      type: 'user',
      connectedAt: new Date().toISOString(),
      lastPing: new Date().toISOString()
    });
  }

  connectAdmin(adminId) {
    this.realtimeConnections.set(adminId, {
      type: 'admin',
      connectedAt: new Date().toISOString(),
      lastPing: new Date().toISOString()
    });
  }

  disconnectUser(userId) {
    this.realtimeConnections.delete(userId);
    const user = this.users[userId];
    if (user) {
      user.isOnline = false;
      user.lastSeen = new Date().toISOString();
      this.saveUsers();
    }
  }

  // Check for new activities
  checkForNewActivities() {
    // Check for new users
    const newUsers = Object.values(this.users).filter(user => 
      new Date(user.joinedDate) > new Date(Date.now() - 24 * 60 * 60 * 1000)
    );

    // Check for new certificates
    const newCertificates = Object.values(this.certificates).filter(cert =>
      new Date(cert.issuedDate) > new Date(Date.now() - 24 * 60 * 60 * 1000)
    );

    // Check for new support tickets
    const newTickets = Object.values(this.supportTickets).filter(ticket =>
      new Date(ticket.createdDate) > new Date(Date.now() - 24 * 60 * 60 * 1000)
    );

    if (newUsers.length > 0 || newCertificates.length > 0 || newTickets.length > 0) {
      this.broadcastToAdmins({
        type: 'new_activities',
        data: {
          newUsers: newUsers.length,
          newCertificates: newCertificates.length,
          newTickets: newTickets.length
        }
      });
    }
  }

  // Process pending actions
  processPendingActions() {
    // Process scheduled announcements
    const now = new Date();
    Object.values(this.announcements).forEach(announcement => {
      if (announcement.scheduledDate && new Date(announcement.scheduledDate) <= now && announcement.status === 'scheduled') {
        announcement.status = 'active';
        this.broadcastToUsers({
          type: 'new_announcement',
          data: announcement
        });
      }
    });

    // Process certificate approvals
    Object.values(this.certificates).forEach(certificate => {
      if (certificate.status === 'pending' && certificate.autoApprove) {
        certificate.status = 'issued';
        this.sendUserNotification(certificate.userId, {
          type: 'certificate_approved',
          title: 'Certificate Approved!',
          message: `Your ${certificate.courseName} certificate has been approved and issued.`
        });
      }
    });
  }

  // Cleanup old data
  cleanupOldData() {
    const thirtyDaysAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000);
    
    // Clean up old activity logs
    this.activityLog = this.activityLog.filter(log => 
      new Date(log.timestamp) > thirtyDaysAgo
    );
    this.saveActivityLog();

    // Clean up old notifications
    Object.values(this.users).forEach(user => {
      if (user.notifications) {
        user.notifications = user.notifications.filter(notif =>
          new Date(notif.timestamp) > thirtyDaysAgo
        );
      }
    });
    this.saveUsers();
  }

  // Enhanced data loading methods
  loadUsers() {
    const stored = localStorage.getItem('discipleship_users');
    let users = stored ? JSON.parse(stored) : {};
    
    // Create default admin user if no users exist
    if (Object.keys(users).length === 0) {
      users = this.createDefaultUsers();
    }
    
    return users;
  }

  // Create default users including admin
  createDefaultUsers() {
    const defaultUsers = {
      'admin_001': {
        id: 'admin_001',
        name: 'Admin User',
        email: 'admin@heartsaftergod.org',
        password: this.hashPassword('admin123'),
        joinedDate: new Date().toISOString(),
        lastLogin: new Date().toISOString(),
        lastActivity: new Date().toISOString(),
        isOnline: false,
        status: 'active',
        role: 'admin',
        profile: {
          phone: '+254 700 000 000',
          church: 'Hearts After God Ministry',
          location: 'Nairobi, Kenya',
          avatar: this.generateAvatar('Admin User')
        },
        preferences: {
          notifications: true,
          emailUpdates: true,
          smsUpdates: false
        }
      },
      'demo_student': {
        id: 'demo_student',
        name: 'Demo Student',
        email: 'student@heartsaftergod.org',
        password: this.hashPassword('student123'),
        joinedDate: new Date().toISOString(),
        lastLogin: new Date().toISOString(),
        lastActivity: new Date().toISOString(),
        isOnline: false,
        status: 'active',
        role: 'student',
        profile: {
          phone: '+254 700 000 001',
          church: 'Hearts After God Ministry',
          location: 'Nairobi, Kenya',
          avatar: this.generateAvatar('Demo Student')
        },
        preferences: {
          notifications: true,
          emailUpdates: true,
          smsUpdates: false
        }
      }
    };
    
    // Save the default users
    localStorage.setItem('discipleship_users', JSON.stringify(defaultUsers));
    return defaultUsers;
  }

  loadProgress() {
    const stored = localStorage.getItem('discipleship_progress');
    return stored ? JSON.parse(stored) : {};
  }

  loadCertificates() {
    const stored = localStorage.getItem('discipleship_certificates');
    if (!stored) return [];
    
    try {
      const parsed = JSON.parse(stored);
      // Migrate from object format to array format if needed
      if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
        console.log('Migrating certificates from object to array format');
        const arrayFormat = Object.values(parsed);
        localStorage.setItem('discipleship_certificates', JSON.stringify(arrayFormat));
        return arrayFormat;
      }
      return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
      console.error('Error loading certificates:', error);
      return [];
    }
  }

  loadSupportTickets() {
    const stored = localStorage.getItem('discipleship_support_tickets');
    if (!stored) return [];
    
    try {
      const parsed = JSON.parse(stored);
      // Migrate from object format to array format if needed
      if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
        console.log('Migrating support tickets from object to array format');
        const arrayFormat = Object.values(parsed);
        localStorage.setItem('discipleship_support_tickets', JSON.stringify(arrayFormat));
        return arrayFormat;
      }
      return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
      console.error('Error loading support tickets:', error);
      return [];
    }
  }

  loadAnnouncements() {
    const stored = localStorage.getItem('discipleship_announcements');
    if (!stored) return [];
    
    try {
      const parsed = JSON.parse(stored);
      // Migrate from object format to array format if needed
      if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
        console.log('Migrating announcements from object to array format');
        const arrayFormat = Object.values(parsed);
        localStorage.setItem('discipleship_announcements', JSON.stringify(arrayFormat));
        return arrayFormat;
      }
      return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
      console.error('Error loading announcements:', error);
      return [];
    }
  }

  // Enhanced data saving methods
  saveUsers() {
    localStorage.setItem('discipleship_users', JSON.stringify(this.users));
  }

  saveProgress() {
    localStorage.setItem('discipleship_progress', JSON.stringify(this.progress));
  }

  saveCertificates() {
    localStorage.setItem('discipleship_certificates', JSON.stringify(this.certificates));
  }

  saveSupportTickets() {
    localStorage.setItem('discipleship_support_tickets', JSON.stringify(this.supportTickets));
  }

  saveAnnouncements() {
    localStorage.setItem('discipleship_announcements', JSON.stringify(this.announcements));
  }

  saveCourses() {
    localStorage.setItem('discipleship_courses', JSON.stringify(this.courses));
  }

  // Password hashing (simple implementation for demo)
  hashPassword(password) {
    return btoa(password); // Base64 encoding for demo
  }

  verifyPassword(password, hashedPassword) {
    return btoa(password) === hashedPassword;
  }

  // Initialize user progress
  initializeUserProgress(userId) {
    const initialProgress = {
      completedLessons: [],
      currentLesson: 1,
      certificates: [],
      startDate: new Date().toISOString(),
      lastActivity: new Date().toISOString(),
      quizScores: {},
      timeSpent: {},
      notes: {},
      achievements: []
    };

    this.progress[userId] = initialProgress;
    this.saveProgress();
    return initialProgress;
  }

  // Get user progress
  getUserProgress(userId) {
    return this.progress[userId] || this.initializeUserProgress(userId);
  }

  // Get user stats
  getUserStats(userId) {
    const progress = this.getUserProgress(userId);
    const totalLessons = 14;
    
    return {
      totalLessons,
      completedLessons: progress.completedLessons.length,
      progressPercentage: Math.round((progress.completedLessons.length / totalLessons) * 100),
      certificatesEarned: progress.certificates.length,
      daysSinceStart: Math.floor((new Date() - new Date(progress.startDate)) / (1000 * 60 * 60 * 24)),
      averageTimePerLesson: this.calculateAverageTime(progress.timeSpent),
      currentStreak: this.calculateCurrentStreak(progress.lastActivity)
    };
  }

  // Calculate average time
  calculateAverageTime(timeSpent) {
    const times = Object.values(timeSpent);
    if (times.length === 0) return 0;
    
    const total = times.reduce((sum, time) => sum + time, 0);
    return Math.round(total / times.length);
  }

  // Calculate current streak
  calculateCurrentStreak(lastActivity) {
    const lastActivityDate = new Date(lastActivity);
    const today = new Date();
    const diffTime = Math.abs(today - lastActivityDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    return diffDays <= 1 ? 1 : 0;
  }

  // Check for achievements
  checkForAchievements(userId) {
    const progress = this.getUserProgress(userId);
    const user = this.users[userId];
    
    if (!progress.achievements) progress.achievements = [];

    // First lesson achievement
    if (progress.completedLessons.length >= 1 && !progress.achievements.includes('first_lesson')) {
      progress.achievements.push('first_lesson');
      this.sendUserNotification(userId, {
        type: 'achievement',
        title: 'First Lesson Complete!',
        message: 'Congratulations on completing your first lesson!'
      });
    }

    // Halfway achievement
    if (progress.completedLessons.length >= 7 && !progress.achievements.includes('halfway')) {
      progress.achievements.push('halfway');
      this.sendUserNotification(userId, {
        type: 'achievement',
        title: 'Halfway There!',
        message: 'You\'ve completed half of the course! Keep going!'
      });
    }

    // Completion achievement
    if (progress.completedLessons.length >= 14 && !progress.achievements.includes('completion')) {
      progress.achievements.push('completion');
      this.sendUserNotification(userId, {
        type: 'achievement',
        title: 'Course Complete!',
        message: 'Congratulations! You\'ve completed the entire course!'
      });
    }

    this.updateUserProgress(userId, progress);
  }

  // Get active users
  getActiveUsers() {
    return Object.values(this.users).filter(user => user.isOnline);
  }

  // Get all users
  getAllUsers() {
    return Object.values(this.users);
  }

  getAllCourses() {
    return Array.isArray(this.courses) ? this.courses : Object.values(this.courses);
  }

  updateUser(userId, updates) {
    if (!this.users[userId]) return null;
    const user = this.users[userId];
    if (updates.name !== undefined) user.name = updates.name;
    if (updates.email !== undefined) user.email = updates.email;
    if (updates.role !== undefined) user.role = updates.role;
    this.saveUsers();
    this.logActivity('user_updated', { userId, updates });
    this.broadcastToAdmins({ type: 'user_updated', data: user });
    return user;
  }

  resetUserPassword(userId, newPassword, options = {}) {
    if (!this.users[userId]) {
      return false;
    }

    const user = this.users[userId];
    const hashedPassword = this.hashPassword(newPassword);
    
    // Update user password
    this.users[userId].password = hashedPassword;
    this.users[userId].passwordResetDate = new Date().toISOString();
    this.users[userId].forcePasswordChange = options.forcePasswordChange || false;
    this.users[userId].lastPasswordReset = {
      resetBy: options.resetBy || 'Admin',
      resetDate: options.resetDate || new Date().toISOString(),
      reason: 'Admin Reset'
    };

    this.saveUsers();
    
    // Log the password reset activity
    this.logActivity('password_reset', {
      userId,
      userEmail: user.email,
      resetBy: options.resetBy || 'Admin',
      notifyUser: options.notifyUser || false
    });

    // Send notification to user if requested
    if (options.notifyUser) {
      this.sendUserNotification(userId, {
        type: 'password_reset',
        title: 'Password Reset',
        message: 'Your password has been reset by an administrator. Please log in with your new password.',
        priority: 'high',
        timestamp: new Date().toISOString()
      });
    }

    // Broadcast to admins
    this.broadcastToAdmins({
      type: 'password_reset',
      data: {
        userId,
        userEmail: user.email,
        resetBy: options.resetBy || 'Admin',
        timestamp: new Date().toISOString()
      }
    });

    return true;
  }

  updateCourse(courseId, updates) {
    const course = this.courses.find(c => c.id === courseId);
    if (!course) return null;
    if (updates.title !== undefined) course.title = updates.title;
    if (updates.description !== undefined) course.description = updates.description;
    if (updates.duration !== undefined) course.duration = updates.duration;
    this.saveCourses();
    this.logActivity('course_updated', { courseId, updates });
    this.broadcastToAdmins({ type: 'course_updated', data: course });
    return course;
  }

  // Create a new course
  createCourse(courseData) {
    const courseId = Date.now().toString();
    const course = {
      id: courseId,
      title: courseData.title,
      description: courseData.description || '',
      duration: courseData.duration || '',
      createdDate: courseData.createdDate || new Date().toISOString(),
      lessons: [],
      enrolledUsers: 0,
      status: 'active'
    };

    this.courses.push(course);
    this.saveCourses();
    this.logActivity('course_created', { courseId, courseData });
    this.broadcastToAdmins({ type: 'course_created', data: course });
    return course;
  }

  // Create a new certificate
  createCertificate(certificateData) {
    const certificateId = 'cert_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    const certificate = {
      id: certificateId,
      studentId: certificateData.studentId,
      studentName: certificateData.studentName,
      courseId: certificateData.courseId,
      courseName: certificateData.courseName,
      issuedDate: certificateData.issuedDate || new Date().toISOString(),
      completionDate: certificateData.completionDate,
      status: 'valid',
      certificateNumber: 'CERT-' + certificateId.substring(0, 8).toUpperCase()
    };

    this.certificates.push(certificate);
    this.saveCertificates();
    this.logActivity('certificate_issued', { certificateId, certificateData });
    this.broadcastToAdmins({ type: 'certificate_issued', data: certificate });
    return certificate;
  }
}

// Initialize the backend
const backend = new DiscipleshipBackend();

// Export for use in other files
window.DiscipleshipBackend = backend;

// =====================
// Certificate Management Logic
// =====================
(function() {
  // Utility: Generate unique ID
  function generateId() {
    return 'cert_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
  }

  // Load certificates from localStorage
  function loadCertificates() {
    return JSON.parse(localStorage.getItem('certificates') || '[]');
  }

  // Save certificates to localStorage
  function saveCertificates(certs) {
    localStorage.setItem('certificates', JSON.stringify(certs));
  }

  // Render certificate table
  window.renderCertificateTable = function() {
    const certs = loadCertificates();
    const tableBody = document.getElementById('certificate-table-body');
    if (!tableBody) return;
    tableBody.innerHTML = '';
    certs.forEach(cert => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${cert.title}</td>
        <td>${cert.course}</td>
        <td>${cert.recipient}</td>
        <td>${cert.dateIssued}</td>
        <td>
          <button class="btn-preview" data-id="${cert.id}">Preview</button>
          <button class="btn-edit" data-id="${cert.id}">Edit</button>
          <button class="btn-delete" data-id="${cert.id}">Delete</button>
        </td>
      `;
      tableBody.appendChild(row);
    });
  };

  // Add certificate
  window.addCertificate = function(cert) {
    const certs = loadCertificates();
    certs.push(cert);
    saveCertificates(certs);
    window.renderCertificateTable();
  };

  // Edit certificate
  window.editCertificate = function(id, updatedCert) {
    let certs = loadCertificates();
    certs = certs.map(cert => cert.id === id ? { ...cert, ...updatedCert } : cert);
    saveCertificates(certs);
    window.renderCertificateTable();
  };

  // Delete certificate
  window.deleteCertificate = function(id) {
    if (!confirm('Are you sure you want to delete this certificate?')) return;
    let certs = loadCertificates();
    certs = certs.filter(cert => cert.id !== id);
    saveCertificates(certs);
    window.renderCertificateTable();
  };

  // Search certificates
  window.searchCertificates = function(query) {
    const certs = loadCertificates();
    return certs.filter(cert =>
      cert.title.toLowerCase().includes(query.toLowerCase()) ||
      cert.course.toLowerCase().includes(query.toLowerCase()) ||
      cert.recipient.toLowerCase().includes(query.toLowerCase())
    );
  };

  // Preview certificate (assumes modal and fields exist)
  window.previewCertificate = function(id) {
    const certs = loadCertificates();
    const cert = certs.find(c => c.id === id);
    if (!cert) return;
    document.getElementById('cert-preview-title').innerText = cert.title;
    document.getElementById('cert-preview-recipient').innerText = cert.recipient;
    document.getElementById('cert-preview-date').innerText = cert.dateIssued;
    document.getElementById('cert-preview-id').innerText = cert.certificateId;
    // Generate QR code (assumes QRCode.js is loaded)
    const qrContainer = document.getElementById('cert-preview-qr');
    if (qrContainer) {
      qrContainer.innerHTML = '';
      if (window.QRCode) {
        new QRCode(qrContainer, {
          text: cert.certificateId,
          width: 80,
          height: 80
        });
      }
    }
    // Show modal
    document.getElementById('certificate-preview-modal').style.display = 'block';
  };

  // Download certificate as PNG or PDF (requires html2canvas and jsPDF)
  window.downloadCertificate = function(format = 'png') {
    const certElement = document.getElementById('certificate-preview-content');
    if (!certElement) return;
    html2canvas(certElement).then(canvas => {
      if (format === 'png') {
        const link = document.createElement('a');
        link.download = 'certificate.png';
        link.href = canvas.toDataURL();
        link.click();
      } else if (format === 'pdf') {
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF();
        pdf.addImage(imgData, 'PNG', 10, 10, 180, 120);
        pdf.save('certificate.pdf');
      }
    });
  };

  // Issue certificate to a user
  window.issueCertificateToUser = function(userId, courseId) {
    const cert = {
      id: generateId(),
      title: 'Course Completion',
      course: courseId,
      recipient: userId,
      dateIssued: new Date().toLocaleDateString(),
      certificateId: generateId(),
      status: 'active'
    };
    window.addCertificate(cert);
  };

  // Issue certificate to all users who completed a course
  window.issueCertificateToAll = function(courseId) {
    if (typeof window.getUsersWhoCompletedCourse !== 'function') {
      alert('getUsersWhoCompletedCourse function not implemented!');
      return;
    }
    const users = window.getUsersWhoCompletedCourse(courseId);
    users.forEach(user => {
      window.issueCertificateToUser(user.id, courseId);
    });
  };

  // Event delegation for table actions
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-preview')) {
      window.previewCertificate(e.target.dataset.id);
    } else if (e.target.classList.contains('btn-edit')) {
      if (typeof window.editCertificateModal === 'function') {
        window.editCertificateModal(e.target.dataset.id);
      }
    } else if (e.target.classList.contains('btn-delete')) {
      window.deleteCertificate(e.target.dataset.id);
    }
  });

  // Initial render
  if (document.getElementById('certificate-table-body')) {
    window.renderCertificateTable();
  }
})();
// =====================
// End Certificate Management Logic
// =====================

// =====================
// Advanced Announcements System
// =====================
(function() {
  // Load announcements from localStorage
  function loadAnnouncements() {
    return JSON.parse(localStorage.getItem('announcements') || '[]');
  }

  // Save announcements to localStorage
  function saveAnnouncements(announcements) {
    localStorage.setItem('announcements', JSON.stringify(announcements));
  }

  // Get priority color
  function getPriorityColor(priority) {
    switch(priority) {
      case 'urgent': return 'bg-red-100 text-red-800 border-red-200';
      case 'high': return 'bg-orange-100 text-orange-800 border-orange-200';
      default: return 'bg-blue-100 text-blue-800 border-blue-200';
    }
  }

  // Update announcement statistics
  function updateAnnouncementStats() {
    const announcements = loadAnnouncements();
    const now = new Date();
    
    const total = announcements.length;
    const active = announcements.filter(a => {
      if (a.status === 'scheduled') return false;
      if (a.schedule && new Date(a.schedule) > now) return false;
      return true;
    }).length;
    const scheduled = announcements.filter(a => a.status === 'scheduled' || (a.schedule && new Date(a.schedule) > now)).length;
    const pinned = announcements.filter(a => a.pinned).length;
    
    // Update stats display
    const totalEl = document.getElementById('total-announcements');
    const activeEl = document.getElementById('active-announcements');
    const scheduledEl = document.getElementById('scheduled-announcements');
    const pinnedEl = document.getElementById('pinned-announcements');
    
    if (totalEl) totalEl.textContent = total;
    if (activeEl) activeEl.textContent = active;
    if (scheduledEl) scheduledEl.textContent = scheduled;
    if (pinnedEl) pinnedEl.textContent = pinned;
  }

  // Render announcements list
  function renderAnnouncements() {
    const announcements = loadAnnouncements();
    const container = document.getElementById('announcements-list');
    
    if (!container) return;
    
    // Update statistics
    updateAnnouncementStats();
    
    if (announcements.length === 0) {
      container.innerHTML = '<p class="text-gray-500 text-center py-4">No announcements yet. Post the first one!</p>';
      return;
    }
    
    // Sort: pinned first, then by timestamp
    const sortedAnnouncements = announcements
      .slice()
      .sort((a, b) => (b.pinned - a.pinned) || (b.timestamp - a.timestamp));
    
    container.innerHTML = sortedAnnouncements.map((announcement, index) => {
      const originalIndex = announcements.indexOf(announcement);
      const priorityClass = getPriorityColor(announcement.priority || 'normal');
      const isScheduled = announcement.schedule && new Date(announcement.schedule) > new Date();
      
      return `
        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 ${announcement.pinned ? 'bg-yellow-50 border-yellow-300' : ''}">
          <div class="flex justify-between items-start mb-2">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <h4 class="font-semibold text-lg">${announcement.title}</h4>
                ${announcement.pinned ? '<span class="px-2 py-1 bg-yellow-200 text-yellow-800 rounded text-xs">📌 Pinned</span>' : ''}
                <span class="px-2 py-1 ${priorityClass} rounded text-xs border">${announcement.priority || 'normal'}</span>
                ${isScheduled ? '<span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">⏰ Scheduled</span>' : ''}
              </div>
              <p class="text-gray-700 mb-2 whitespace-pre-wrap">${announcement.message}</p>
              ${announcement.attachment ? `
                <div class="mb-2">
                  <a href="${announcement.attachment.data}" download="${announcement.attachment.name}" 
                     class="text-blue-600 hover:text-blue-800 text-sm">
                    📎 ${announcement.attachment.name}
                  </a>
                </div>
              ` : ''}
              <div class="text-xs text-gray-500">
                Posted: ${new Date(announcement.timestamp).toLocaleString()}
                ${announcement.schedule ? ` | Scheduled: ${new Date(announcement.schedule).toLocaleString()}` : ''}
              </div>
            </div>
            <div class="flex gap-2 ml-4">
              <button onclick="editAnnouncement(${originalIndex})" class="text-blue-500 hover:text-blue-700 text-sm">
                <i class="fa fa-edit"></i> Edit
              </button>
              <button onclick="deleteAnnouncement(${originalIndex})" class="text-red-500 hover:text-red-700 text-sm">
                <i class="fa fa-trash"></i> Delete
              </button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  // Delete announcement
  window.deleteAnnouncement = function(index) {
    if (confirm('Are you sure you want to delete this announcement?')) {
      const announcements = loadAnnouncements();
      announcements.splice(index, 1);
      saveAnnouncements(announcements);
      renderAnnouncements();
    }
  };

  // Edit announcement
  window.editAnnouncement = function(index) {
    const announcements = loadAnnouncements();
    const announcement = announcements[index];
    
    document.getElementById('edit-announcement-id').value = index;
    document.getElementById('edit-announcement-title').value = announcement.title;
    document.getElementById('edit-announcement-message').value = announcement.message;
    document.getElementById('edit-announcement-priority').value = announcement.priority || 'normal';
    document.getElementById('edit-announcement-pin').checked = announcement.pinned || false;
    document.getElementById('edit-announcement-schedule').value = announcement.schedule ? announcement.schedule.substring(0, 16) : '';
    
    // Show attachment preview
    const preview = document.getElementById('edit-attachment-preview');
    if (announcement.attachment) {
      if (announcement.attachment.type.startsWith('image/')) {
        preview.innerHTML = `<img src="${announcement.attachment.data}" alt="${announcement.attachment.name}" class="max-h-32 rounded" />`;
      } else {
        preview.innerHTML = `<a href="${announcement.attachment.data}" download="${announcement.attachment.name}" class="text-blue-600 underline">📎 ${announcement.attachment.name}</a>`;
      }
    } else {
      preview.innerHTML = '';
    }
    
    document.getElementById('edit-announcement-modal').classList.remove('hidden');
  };

  // Initialize when DOM is loaded
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Announcements system initializing...');
    
    // Set up post button
    const postBtn = document.getElementById('post-announcement-btn');
    console.log('Post button found:', postBtn);
    if (postBtn) {
      postBtn.addEventListener('click', function(e) {
        console.log('Post button clicked!');
        e.preventDefault();
        createAnnouncement(false); // Post now
      });
    }

    // Set up schedule button
    const scheduleBtn = document.getElementById('schedule-announcement-btn');
    console.log('Schedule button found:', scheduleBtn);
    if (scheduleBtn) {
      scheduleBtn.addEventListener('click', function(e) {
        console.log('Schedule button clicked!');
        e.preventDefault();
        createAnnouncement(true); // Schedule
      });
    }

    // Set up edit modal
    const editForm = document.getElementById('edit-announcement-form');
    if (editForm) {
      editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        updateAnnouncement();
      });
    }

    // Close edit modal
    const closeEditBtn = document.getElementById('close-edit-modal');
    const cancelEditBtn = document.getElementById('cancel-edit');
    [closeEditBtn, cancelEditBtn].forEach(btn => {
      if (btn) {
        btn.addEventListener('click', function() {
          document.getElementById('edit-announcement-modal').classList.add('hidden');
        });
      }
    });

    // Initial render
    console.log('Rendering initial announcements...');
    renderAnnouncements();
    
    // Check for scheduled announcements every minute
    setInterval(checkScheduledAnnouncements, 60000);
    console.log('Announcements system initialized successfully!');
  });

  // Create announcement
  function createAnnouncement(isScheduled = false) {
    console.log('Creating announcement, scheduled:', isScheduled);
    
    const titleInput = document.getElementById('announcement-title');
    const messageInput = document.getElementById('announcement-message');
    const prioritySelect = document.getElementById('announcement-priority');
    const scheduleInput = document.getElementById('announcement-schedule');
    const pinCheckbox = document.getElementById('announcement-pin');
    const attachmentInput = document.getElementById('announcement-attachment');
    
    console.log('Form elements found:', {
      titleInput: !!titleInput,
      messageInput: !!messageInput,
      prioritySelect: !!prioritySelect,
      scheduleInput: !!scheduleInput,
      pinCheckbox: !!pinCheckbox,
      attachmentInput: !!attachmentInput
    });
    
    const title = titleInput.value.trim();
    const message = messageInput.value.trim();
    const priority = prioritySelect.value;
    const schedule = scheduleInput.value;
    const pinned = pinCheckbox.checked;
    
    console.log('Form values:', { title, message, priority, schedule, pinned });
    
    if (!title || !message) {
      alert('Please enter both title and message');
      return;
    }

    if (isScheduled && !schedule) {
      alert('Please select a schedule time');
      return;
    }
    
    const processAttachment = (attachmentObj) => {
      const newAnnouncement = {
        id: 'ann_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
        title: title,
        message: message,
        priority: priority,
        timestamp: Date.now(),
        pinned: pinned,
        schedule: schedule || null,
        attachment: attachmentObj,
        status: isScheduled ? 'scheduled' : 'active'
      };
      
      // Add to storage
      const announcements = loadAnnouncements();
      announcements.unshift(newAnnouncement);
      saveAnnouncements(announcements);
      
      // Clear form
      titleInput.value = '';
      messageInput.value = '';
      prioritySelect.value = 'normal';
      scheduleInput.value = '';
      pinCheckbox.checked = false;
      attachmentInput.value = '';
      
      // Re-render
      renderAnnouncements();
      
      // Show success message
      const action = isScheduled ? 'scheduled' : 'posted';
      alert(`Announcement ${action} successfully!`);
    };

    if (attachmentInput && attachmentInput.files.length > 0) {
      const file = attachmentInput.files[0];
      const reader = new FileReader();
      reader.onload = function(evt) {
        processAttachment({
          name: file.name,
          type: file.type,
          data: evt.target.result
        });
      };
      reader.readAsDataURL(file);
    } else {
      processAttachment(null);
    }
  }

  // Update announcement
  function updateAnnouncement() {
    const index = parseInt(document.getElementById('edit-announcement-id').value);
    const announcements = loadAnnouncements();
    
    announcements[index].title = document.getElementById('edit-announcement-title').value.trim();
    announcements[index].message = document.getElementById('edit-announcement-message').value.trim();
    announcements[index].priority = document.getElementById('edit-announcement-priority').value;
    announcements[index].pinned = document.getElementById('edit-announcement-pin').checked;
    announcements[index].schedule = document.getElementById('edit-announcement-schedule').value || null;
    
    saveAnnouncements(announcements);
    renderAnnouncements();
    document.getElementById('edit-announcement-modal').classList.add('hidden');
    alert('Announcement updated successfully!');
  }

  // Check for scheduled announcements
  function checkScheduledAnnouncements() {
    const announcements = loadAnnouncements();
    const now = new Date();
    let updated = false;
    
    announcements.forEach(announcement => {
      if (announcement.schedule && announcement.status === 'scheduled') {
        const scheduleTime = new Date(announcement.schedule);
        if (scheduleTime <= now) {
          announcement.status = 'active';
          updated = true;
        }
      }
    });
    
    if (updated) {
      saveAnnouncements(announcements);
      renderAnnouncements();
      
      // Trigger storage event for real-time sync
      window.dispatchEvent(new StorageEvent('storage', {
        key: 'announcements',
        newValue: JSON.stringify(announcements)
      }));
    }
  }

  // Listen for storage changes (real-time sync)
  window.addEventListener('storage', function(e) {
    if (e.key === 'announcements') {
      renderAnnouncements();
    }
  });

  // Test function to verify buttons work
  window.testAnnouncementButtons = function() {
    console.log('Testing announcement buttons...');
    const postBtn = document.getElementById('post-announcement-btn');
    const scheduleBtn = document.getElementById('schedule-announcement-btn');
    
    if (postBtn) {
      console.log('Post button exists and is clickable');
      postBtn.style.border = '2px solid green';
    } else {
      console.log('Post button not found!');
    }
    
    if (scheduleBtn) {
      console.log('Schedule button exists and is clickable');
      scheduleBtn.style.border = '2px solid blue';
    } else {
      console.log('Schedule button not found!');
    }
  };

  // Test function to create a sample announcement
  window.createTestAnnouncement = function() {
    console.log('Creating test announcement...');
    
    // Fill in the form
    const titleInput = document.getElementById('announcement-title');
    const messageInput = document.getElementById('announcement-message');
    
    if (titleInput && messageInput) {
      titleInput.value = 'Test Announcement';
      messageInput.value = 'This is a test announcement to verify the system is working.';
      
      // Trigger the post button
      const postBtn = document.getElementById('post-announcement-btn');
      if (postBtn) {
        postBtn.click();
      }
    }
  };
})();
// =====================
// End Advanced Announcements System
// =====================

// =====================
// Enhanced Support Tickets Logic
// =====================
(function() {
  function loadSupportTickets() {
    return JSON.parse(localStorage.getItem('support_tickets') || '[]');
  }
  
  function saveSupportTickets(list) {
    localStorage.setItem('support_tickets', JSON.stringify(list));
  }

  function updateSupportStats() {
    const tickets = loadSupportTickets();
    const openTickets = tickets.filter(t => t.status === 'Open').length;
    const resolvedTickets = tickets.filter(t => t.status === 'Resolved').length;
    const totalTickets = tickets.length;
    
    // Calculate average response time
    let totalResponseTime = 0;
    let responseCount = 0;
    tickets.forEach(ticket => {
      if (ticket.replies && ticket.replies.length > 0) {
        const firstReply = ticket.replies[0];
        const responseTime = firstReply.timestamp - ticket.timestamp;
        totalResponseTime += responseTime;
        responseCount++;
      }
    });
    const avgResponseHours = responseCount > 0 ? Math.round(totalResponseTime / (1000 * 60 * 60 * responseCount)) : 0;
    
    // Update stats display
    const openEl = document.getElementById('open-tickets');
    const resolvedEl = document.getElementById('resolved-tickets');
    const totalEl = document.getElementById('total-tickets');
    const avgEl = document.getElementById('avg-response');
    
    if (openEl) openEl.textContent = openTickets;
    if (resolvedEl) resolvedEl.textContent = resolvedTickets;
    if (totalEl) totalEl.textContent = totalTickets;
    if (avgEl) avgEl.textContent = `${avgResponseHours}h`;
  }

  function getPriorityColor(priority) {
    switch(priority) {
      case 'Urgent': return 'bg-red-100 text-red-800 border-red-200';
      case 'High': return 'bg-orange-100 text-orange-800 border-orange-200';
      case 'Medium': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
      default: return 'bg-green-100 text-green-800 border-green-200';
    }
  }

  function getStatusColor(status) {
    switch(status) {
      case 'Open': return 'bg-red-100 text-red-800 border-red-200';
      case 'In Progress': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
      case 'Resolved': return 'bg-green-100 text-green-800 border-green-200';
      default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
  }

  function renderSupportTickets() {
    const tickets = loadSupportTickets();
    const tbody = document.getElementById('support-table-body');
    if (!tbody) return;
    
    // Update statistics
    updateSupportStats();
    
    // Get filters
    const searchTerm = document.getElementById('support-search')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('status-filter')?.value || '';
    
    // Filter tickets
    let filteredTickets = tickets.filter(ticket => {
      const matchesSearch = ticket.subject?.toLowerCase().includes(searchTerm) || 
                           ticket.description?.toLowerCase().includes(searchTerm) ||
                           ticket.user?.toLowerCase().includes(searchTerm);
      const matchesStatus = !statusFilter || ticket.status === statusFilter;
      return matchesSearch && matchesStatus;
    });
    
    // Sort by priority and timestamp
    filteredTickets.sort((a, b) => {
      const priorityOrder = { 'Urgent': 4, 'High': 3, 'Medium': 2, 'Low': 1 };
      const aPriority = priorityOrder[a.priority] || 0;
      const bPriority = priorityOrder[b.priority] || 0;
      if (aPriority !== bPriority) return bPriority - aPriority;
      return b.timestamp - a.timestamp;
    });
    
    tbody.innerHTML = '';
    
    if (filteredTickets.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="6" class="px-4 py-8 text-center text-gray-500">
            <i class="fa fa-inbox text-4xl mb-2 opacity-50"></i>
            <p>No support tickets found</p>
          </td>
        </tr>
      `;
      return;
    }
    
    filteredTickets.forEach((ticket, idx) => {
      const originalIndex = tickets.indexOf(ticket);
      const priorityClass = getPriorityColor(ticket.priority || 'Low');
      const statusClass = getStatusColor(ticket.status || 'Open');
      
      tbody.innerHTML += `
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3">
            <div class="font-semibold text-blue-900">${ticket.user}</div>
            <div class="text-xs text-gray-500">${ticket.email || ''}</div>
          </td>
          <td class="px-4 py-3">
            <div class="font-medium text-gray-900">${ticket.subject || 'No subject'}</div>
            <div class="text-sm text-gray-600 mt-1">${(ticket.description || '').substring(0, 100)}${(ticket.description || '').length > 100 ? '...' : ''}</div>
          </td>
          <td class="px-4 py-3">
            <span class="px-2 py-1 rounded text-xs border ${priorityClass}">
              ${ticket.priority || 'Low'}
            </span>
          </td>
          <td class="px-4 py-3">
            <span class="px-2 py-1 rounded text-xs border ${statusClass}">
              ${ticket.status || 'Open'}
            </span>
          </td>
          <td class="px-4 py-3 text-sm text-gray-600">
            ${new Date(ticket.timestamp).toLocaleDateString()}
            <div class="text-xs text-gray-400">
              ${new Date(ticket.timestamp).toLocaleTimeString()}
            </div>
          </td>
          <td class="px-4 py-3">
            <div class="flex gap-2">
              <button class="bg-[#7C3AED] text-white px-3 py-1 rounded text-xs hover:bg-[#6D28D9]" onclick="openSupportReplyModal(${originalIndex})">
                <i class="fa fa-reply mr-1"></i>Reply
              </button>
              <button class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600" onclick="markSupportResolved(${originalIndex})">
                <i class="fa fa-check mr-1"></i>Resolve
              </button>
              <button class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600" onclick="deleteSupportTicket(${originalIndex})">
                <i class="fa fa-trash mr-1"></i>Delete
              </button>
            </div>
          </td>
        </tr>
      `;
    });
  }

  window.openSupportReplyModal = function(idx) {
    const tickets = loadSupportTickets();
    const ticket = tickets[idx];
    
    // Populate modal with ticket details
    document.getElementById('support-reply-user').innerText = ticket.user;
    document.getElementById('support-reply-priority').innerText = ticket.priority || 'Low';
    document.getElementById('support-reply-status').innerText = ticket.status || 'Open';
    document.getElementById('support-reply-date').innerText = new Date(ticket.timestamp).toLocaleString();
    document.getElementById('support-reply-issue').innerText = ticket.description || ticket.subject || 'No description';
    
    // Show reply history
    const historyContainer = document.getElementById('support-reply-history');
    if (ticket.replies && ticket.replies.length > 0) {
      historyContainer.innerHTML = `
        <h4 class="font-semibold text-blue-900 mb-2">Reply History</h4>
        ${ticket.replies.map(reply => `
          <div class="bg-blue-50 p-3 rounded mb-2">
            <div class="text-sm text-gray-600 mb-1">
              <strong>Admin Reply</strong> - ${new Date(reply.timestamp).toLocaleString()}
            </div>
            <div class="text-gray-800">${reply.message}</div>
          </div>
        `).join('')}
      `;
    } else {
      historyContainer.innerHTML = '<p class="text-gray-500 text-sm">No replies yet</p>';
    }
    
    // Reset form
    document.getElementById('support-reply-message').value = '';
    document.getElementById('support-reply-status-update').value = '';
    document.getElementById('support-reply-notify').checked = true;
    document.getElementById('support-reply-form').dataset.idx = idx;
    
    // Show modal
    document.getElementById('support-reply-modal').classList.remove('hidden');
  };

  document.addEventListener('DOMContentLoaded', function() {
    // Close modal buttons
    const closeModalBtn = document.getElementById('close-reply-modal');
    const cancelBtn = document.getElementById('support-reply-cancel');
    [closeModalBtn, cancelBtn].forEach(btn => {
      if (btn) {
        btn.onclick = function() {
          document.getElementById('support-reply-modal').classList.add('hidden');
        };
      }
    });

    // Reply form submission
    const replyForm = document.getElementById('support-reply-form');
    if (replyForm) {
      replyForm.onsubmit = function(e) {
        e.preventDefault();
        const idx = parseInt(this.dataset.idx, 10);
        const message = document.getElementById('support-reply-message').value.trim();
        const statusUpdate = document.getElementById('support-reply-status-update').value;
        const notifyUser = document.getElementById('support-reply-notify').checked;
        
        if (!message) return;
        
        const tickets = loadSupportTickets();
        const ticket = tickets[idx];
        
        // Add reply to ticket
        if (!ticket.replies) ticket.replies = [];
        ticket.replies.push({
          message: message,
          timestamp: Date.now(),
          admin: 'Admin'
        });
        
        // Update status if specified
        if (statusUpdate) {
          ticket.status = statusUpdate;
        }
        
        // Mark as in progress if it was open
        if (ticket.status === 'Open') {
          ticket.status = 'In Progress';
        }
        
        saveSupportTickets(tickets);
        document.getElementById('support-reply-modal').classList.add('hidden');
        renderSupportTickets();
        
        // Show success message
        alert('Reply sent successfully!');
      };
    }

    // Search and filter functionality
    const searchInput = document.getElementById('support-search');
    const statusFilter = document.getElementById('status-filter');
    const refreshBtn = document.getElementById('refresh-support-btn');
    
    [searchInput, statusFilter].forEach(element => {
      if (element) {
        element.addEventListener('input', renderSupportTickets);
        element.addEventListener('change', renderSupportTickets);
      }
    });
    
    if (refreshBtn) {
      refreshBtn.onclick = renderSupportTickets;
    }
  });

  window.markSupportResolved = function(idx) {
    const list = loadSupportTickets();
    list[idx].status = 'Resolved';
    saveSupportTickets(list);
    renderSupportTickets();
  };

  window.deleteSupportTicket = function(idx) {
    if (!confirm('Delete this support ticket?')) return;
    const list = loadSupportTickets();
    list.splice(idx, 1);
    saveSupportTickets(list);
    renderSupportTickets();
  };

  // Demo: Add sample tickets if none exist
  document.addEventListener('DOMContentLoaded', function() {
    if (!localStorage.getItem('support_tickets')) {
      saveSupportTickets([
        { 
          id: 'ticket_1',
          user: 'John Doe', 
          email: 'john@example.com',
          subject: 'Cannot access lesson 2', 
          description: 'I\'m trying to access lesson 2 but it shows as locked. I completed lesson 1 yesterday.',
          priority: 'High',
          status: 'Open', 
          timestamp: Date.now() - 86400000,
          replies: []
        },
        { 
          id: 'ticket_2',
          user: 'Jane Smith', 
          email: 'jane@example.com',
          subject: 'Progress not updating', 
          description: 'My progress bar is stuck at 60% even though I completed the quiz.',
          priority: 'Medium',
          status: 'Resolved', 
          timestamp: Date.now() - 43200000, 
          replies: [
            {
              message: 'Please refresh your browser and try again. If the issue persists, try clearing your browser cache.',
              timestamp: Date.now() - 36000000,
              admin: 'Admin'
            }
          ]
        },
        {
          id: 'ticket_3',
          user: 'Mike Johnson',
          email: 'mike@example.com',
          subject: 'Certificate download issue',
          description: 'I completed the course but cannot download my certificate. The download button is not working.',
          priority: 'Urgent',
          status: 'In Progress',
          timestamp: Date.now() - 21600000,
          replies: [
            {
              message: 'We are investigating this issue. Please try using a different browser or check your internet connection.',
              timestamp: Date.now() - 18000000,
              admin: 'Admin'
            }
          ]
        }
      ]);
    }
    renderSupportTickets();
  });
})();
// =====================
// End Support Tickets Logic
// =====================

// Initialize the global backend instance
window.DiscipleshipBackend = new DiscipleshipBackend();
console.log('DiscipleshipBackend initialized globally'); 