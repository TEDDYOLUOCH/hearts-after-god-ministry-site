// Backend Simulation for Discipleship Portal
// In a real application, this would be server-side code

class DiscipleshipBackend {
  constructor() {
    this.users = this.loadUsers();
    this.progress = this.loadProgress();
  }

  loadUsers() {
    const stored = localStorage.getItem('discipleship_users');
    return stored ? JSON.parse(stored) : {};
  }

  loadProgress() {
    const stored = localStorage.getItem('discipleship_progress');
    return stored ? JSON.parse(stored) : {};
  }

  saveUsers() {
    localStorage.setItem('discipleship_users', JSON.stringify(this.users));
  }

  saveProgress() {
    localStorage.setItem('discipleship_progress', JSON.stringify(this.progress));
  }

  // User Management
  createUser(userData) {
    const userId = Date.now().toString();
    const user = {
      id: userId,
      name: userData.name,
      email: userData.email,
      password: this.hashPassword(userData.password), // In real app, use proper hashing
      joinedDate: new Date().toISOString(),
      lastLogin: new Date().toISOString()
    };

    this.users[userId] = user;
    this.saveUsers();

    // Initialize progress for new user
    this.initializeUserProgress(userId);

    return user;
  }

  authenticateUser(email, password) {
    const user = Object.values(this.users).find(u => u.email === email);
    
    if (user && this.verifyPassword(password, user.password)) {
      // Update last login
      user.lastLogin = new Date().toISOString();
      this.saveUsers();
      
      return user;
    }
    
    return null;
  }

  getUserProgress(userId) {
    return this.progress[userId] || this.initializeUserProgress(userId);
  }

  updateUserProgress(userId, progressData) {
    this.progress[userId] = { ...this.progress[userId], ...progressData };
    this.saveProgress();
  }

  initializeUserProgress(userId) {
    const initialProgress = {
      completedLessons: [],
      currentLesson: 1,
      certificates: [],
      startDate: new Date().toISOString(),
      lastActivity: new Date().toISOString(),
      quizScores: {},
      timeSpent: {},
      notes: {}
    };

    this.progress[userId] = initialProgress;
    this.saveProgress();
    return initialProgress;
  }

  // Lesson Management
  completeLesson(userId, lessonId) {
    const userProgress = this.getUserProgress(userId);
    
    if (!userProgress.completedLessons.includes(lessonId)) {
      userProgress.completedLessons.push(lessonId);
      userProgress.lastActivity = new Date().toISOString();
      
      // Check for certificates
      this.checkForCertificates(userId, userProgress);
      
      this.updateUserProgress(userId, userProgress);
    }
  }

  checkForCertificates(userId, progress) {
    const completedCount = progress.completedLessons.length;
    
    // Halfway certificate (7 lessons)
    if (completedCount >= 7 && !progress.certificates.includes('halfway')) {
      progress.certificates.push({
        type: 'halfway',
        earnedDate: new Date().toISOString(),
        name: 'Halfway Achievement Certificate'
      });
    }
    
    // Completion certificate (14 lessons)
    if (completedCount >= 14 && !progress.certificates.includes('complete')) {
      progress.certificates.push({
        type: 'complete',
        earnedDate: new Date().toISOString(),
        name: 'Course Completion Certificate'
      });
    }
  }

  // Certificate Generation
  generateCertificate(userId, certificateType) {
    const user = this.users[userId];
    const progress = this.getUserProgress(userId);
    
    if (!user) return null;

    const certificate = {
      id: `cert_${Date.now()}`,
      userId: userId,
      type: certificateType,
      userName: user.name,
      issuedDate: new Date().toISOString(),
      ministryName: 'Hearts After God Ministry',
      courseName: certificateType === 'complete' ? '14-Day Discipleship Course' : 'First Half Achievement',
      signature: 'Pastor John Doe',
      certificateNumber: `HAGM-${certificateType.toUpperCase()}-${Date.now()}`
    };

    return certificate;
  }

  // Analytics and Reporting
  getUserStats(userId) {
    const progress = this.getUserProgress(userId);
    const totalLessons = 14; // Total lessons in the course
    
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

  calculateAverageTime(timeSpent) {
    const times = Object.values(timeSpent);
    if (times.length === 0) return 0;
    
    const total = times.reduce((sum, time) => sum + time, 0);
    return Math.round(total / times.length);
  }

  calculateCurrentStreak(lastActivity) {
    const lastActivityDate = new Date(lastActivity);
    const today = new Date();
    const diffTime = Math.abs(today - lastActivityDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    return diffDays <= 1 ? 1 : 0; // Simple streak calculation
  }

  // Quiz and Assessment
  saveQuizScore(userId, lessonId, score) {
    const progress = this.getUserProgress(userId);
    progress.quizScores[lessonId] = {
      score: score,
      date: new Date().toISOString(),
      passed: score >= 70 // 70% passing threshold
    };
    
    this.updateUserProgress(userId, progress);
  }

  // Notes and Journal
  saveUserNote(userId, lessonId, note) {
    const progress = this.getUserProgress(userId);
    
    if (!progress.notes[lessonId]) {
      progress.notes[lessonId] = [];
    }
    
    progress.notes[lessonId].push({
      content: note,
      date: new Date().toISOString()
    });
    
    this.updateUserProgress(userId, progress);
  }

  getUserNotes(userId, lessonId) {
    const progress = this.getUserProgress(userId);
    return progress.notes[lessonId] || [];
  }

  // Time Tracking
  recordLessonTime(userId, lessonId, timeSpent) {
    const progress = this.getUserProgress(userId);
    progress.timeSpent[lessonId] = timeSpent;
    this.updateUserProgress(userId, progress);
  }

  // Password Hashing (Simple simulation - use proper hashing in production)
  hashPassword(password) {
    // This is a simple simulation - in production, use bcrypt or similar
    return btoa(password + 'salt');
  }

  verifyPassword(password, hashedPassword) {
    return this.hashPassword(password) === hashedPassword;
  }

  // Data Export
  exportUserData(userId) {
    const user = this.users[userId];
    const progress = this.getUserProgress(userId);
    const stats = this.getUserStats(userId);
    
    return {
      user: {
        name: user.name,
        email: user.email,
        joinedDate: user.joinedDate
      },
      progress: progress,
      stats: stats,
      exportDate: new Date().toISOString()
    };
  }

  // Admin Functions
  getAllUsers() {
    return Object.values(this.users).map(user => ({
      id: user.id,
      name: user.name,
      email: user.email,
      joinedDate: user.joinedDate,
      lastLogin: user.lastLogin
    }));
  }

  getAllProgress() {
    return this.progress;
  }

  getSystemStats() {
    const users = Object.values(this.users);
    const totalUsers = users.length;
    const activeUsers = users.filter(user => {
      const lastLogin = new Date(user.lastLogin);
      const thirtyDaysAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000);
      return lastLogin > thirtyDaysAgo;
    }).length;

    const totalCompletions = Object.values(this.progress).filter(p => 
      p.completedLessons.length >= 14
    ).length;

    return {
      totalUsers,
      activeUsers,
      totalCompletions,
      completionRate: totalUsers > 0 ? Math.round((totalCompletions / totalUsers) * 100) : 0
    };
  }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = DiscipleshipBackend;
} else {
  window.DiscipleshipBackend = DiscipleshipBackend;
}

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