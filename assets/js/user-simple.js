// Simplified User Dashboard
// Easy-to-understand user interface with admin integration

(function() {
  let backend = null;
  let currentUser = null;
  
  // User state
  let userState = {
    isAuthenticated: false,
    currentProgress: null,
    lastUpdate: null,
    notifications: []
  };

  let lastRealtimeUpdate = Date.now();
  let lastLessonTitle = '';

  // Initialize user dashboard
  function initUserDashboard() {
    console.log('Initializing user dashboard...');
    
    // Wait for backend to be available
    if (!window.DiscipleshipBackend) {
      console.log('Waiting for backend to load...');
      setTimeout(initUserDashboard, 100);
      return;
    }
    
    try {
      backend = window.DiscipleshipBackend;
      console.log('Backend loaded, initializing user dashboard...');
      
      checkAuthentication();
      setupEventListeners();
      loadUserData();
      connectToBackend();
      startRealTimeUpdates();
      
      // Initialize notes system
      if (window.UserNotes && currentUser) {
        window.UserNotes.setCurrentUser(currentUser);
      }
      
      // Initialize quiz system
      if (window.UserQuiz && currentUser) {
        window.UserQuiz.setCurrentUser(currentUser);
      }
      
      console.log('User dashboard initialized successfully');
    } catch (error) {
      console.error('Error initializing user dashboard:', error);
    }
  }

  // Check authentication
  function checkAuthentication() {
    // Check if this is an admin viewing a user dashboard
    const urlParams = new URLSearchParams(window.location.search);
    const isAdminView = urlParams.get('admin_view') === 'true';
    const adminViewUserId = urlParams.get('user_id');
    
    if (isAdminView && adminViewUserId) {
      // Admin is viewing a user dashboard
      const adminViewingUser = localStorage.getItem('admin_viewing_user');
      if (adminViewingUser) {
        const user = JSON.parse(adminViewingUser);
        if (user.id === adminViewUserId) {
          currentUser = user;
          userState.isAuthenticated = true;
          userState.isAdminView = true;
          updateUserInterface();
          showAdminViewMode();
          return;
        }
      }
    }
    
    const loggedInUser = localStorage.getItem('discipleship_logged_in_user');
    if (loggedInUser) {
      const user = JSON.parse(loggedInUser);
      if (user.role === 'student' || user.role === 'user') {
        currentUser = user;
        userState.isAuthenticated = true;
        updateUserInterface();
      } else if (user.role === 'admin') {
        // Admin should be redirected to admin dashboard
        window.location.href = 'discipleship-admin.html';
      }
    } else {
      // Redirect to login if not authenticated
      window.location.href = 'discipleship-login.html';
    }
  }

  // Update user interface with current user data
  function updateUserInterface() {
    if (!currentUser) return;

    // Update user name and email
    const userNameElements = document.querySelectorAll('#user-name, #nav-user-name');
    const userEmailElements = document.querySelectorAll('#user-email, #nav-user-email');
    const userAvatarElements = document.querySelectorAll('#user-avatar, #nav-user-avatar');

    userNameElements.forEach(el => {
      if (el) el.textContent = currentUser.name;
    });

    userEmailElements.forEach(el => {
      if (el) el.textContent = currentUser.email;
    });

    userAvatarElements.forEach(el => {
      if (el) {
        el.textContent = currentUser.name.substring(0, 2).toUpperCase();
      }
    });
  }

  // Real-time user progress update
  function updateUserProgress() {
    if (!currentUser || !backend) return;

    const progress = backend.getUserProgress(currentUser.id);
    if (!progress) return;

    // Update progress indicators with animation
    const progressElement = document.getElementById('summary-progress');
    if (progressElement) {
      const totalLessons = 10;
      const percentage = Math.round(((progress.completedLessons || 0) / totalLessons) * 100);
      const currentPercentage = parseInt(progressElement.textContent) || 0;
      
      if (currentPercentage !== percentage) {
        progressElement.textContent = `${percentage}%`;
        progressElement.classList.add('animate-pulse');
        setTimeout(() => progressElement.classList.remove('animate-pulse'), 1000);
        
        // Show real-time update indicator
        showRealtimeUpdate('Progress updated');
      }
    }

    // Update lesson count with animation
    const lessonsElement = document.getElementById('summary-lessons');
    if (lessonsElement) {
      const newCount = progress.completedLessons || 0;
      const currentCount = parseInt(lessonsElement.textContent) || 0;
      
      if (currentCount !== newCount) {
        lessonsElement.textContent = newCount;
        lessonsElement.classList.add('animate-bounce');
        setTimeout(() => lessonsElement.classList.remove('animate-bounce'), 1000);
        
        // Show real-time update indicator
        showRealtimeUpdate('Lessons updated');
      }
    }

    // Update badges count
    const badgesElement = document.getElementById('summary-badges');
    if (badgesElement) {
      const newBadges = progress.badges?.length || 0;
      const currentBadges = parseInt(badgesElement.textContent) || 0;
      
      if (currentBadges !== newBadges) {
        badgesElement.textContent = newBadges;
        badgesElement.classList.add('animate-pulse');
        setTimeout(() => badgesElement.classList.remove('animate-pulse'), 1000);
        
        // Show real-time update indicator
        showRealtimeUpdate('Achievement unlocked!');
      }
    }
  }

  // Show real-time update indicator
  function showRealtimeUpdate(message) {
    let updateIndicator = document.getElementById('realtime-update');
    if (!updateIndicator) {
      updateIndicator = document.createElement('div');
      updateIndicator.id = 'realtime-update';
      updateIndicator.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 z-50 bg-gradient-to-r from-[#7C3AED] to-[#2046B3] text-white px-4 py-2 rounded-lg shadow-lg opacity-0 transition-opacity duration-300';
      document.body.appendChild(updateIndicator);
    }
    
    updateIndicator.textContent = `🔄 ${message}`;
    updateIndicator.classList.remove('opacity-0');
    updateIndicator.classList.add('opacity-100');
    
    setTimeout(() => {
      updateIndicator.classList.remove('opacity-100');
      updateIndicator.classList.add('opacity-0');
    }, 2000);
  }

  // Real-time activity feed update
  function updateActivityFeed() {
    const activityList = document.getElementById('recent-activity');
    if (!activityList) return;

    const activities = getRecentActivities();
    const currentHTML = activityList.innerHTML;
    const newHTML = activities.map(activity => `
      <li class="flex items-center gap-3 text-gray-700">
        <div class="w-6 h-6 ${activity.iconBg} rounded-full flex items-center justify-center">
          <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${activity.iconPath}"/>
          </svg>
        </div>
        <span class="text-sm">${activity.text}</span>
      </li>
    `).join('');

    if (currentHTML !== newHTML) {
      activityList.innerHTML = newHTML;
      // Add subtle animation for new activities
      const firstActivity = activityList.querySelector('li');
      if (firstActivity) {
        firstActivity.classList.add('animate-fade-in');
        setTimeout(() => firstActivity.classList.remove('animate-fade-in'), 2000);
      }
    }
  }

  // Update online status
  function updateOnlineStatus() {
    if (!currentUser || !backend) return;

    const users = backend.getAllUsers();
    const user = users.find(u => u.id === currentUser.id);
    
    // Create or update real-time status indicator
    let statusIndicator = document.getElementById('realtime-status');
    if (!statusIndicator) {
      statusIndicator = document.createElement('div');
      statusIndicator.id = 'realtime-status';
      statusIndicator.className = 'fixed bottom-4 right-4 z-50 flex items-center gap-2 bg-white rounded-lg shadow-lg px-3 py-2 border border-gray-200';
      
      const statusDot = document.createElement('div');
      statusDot.className = 'w-2 h-2 bg-green-500 rounded-full animate-pulse';
      
      const statusText = document.createElement('span');
      statusText.className = 'text-xs text-gray-600 font-medium';
      statusText.textContent = 'Live';
      
      statusIndicator.appendChild(statusDot);
      statusIndicator.appendChild(statusText);
      document.body.appendChild(statusIndicator);
    }
    
    if (user && user.isOnline) {
      const statusDot = statusIndicator.querySelector('.w-2');
      const statusText = statusIndicator.querySelector('span');
      
      if (statusDot) {
        statusDot.className = 'w-2 h-2 bg-green-500 rounded-full animate-pulse';
      }
      if (statusText) {
        statusText.textContent = 'Live';
        statusText.className = 'text-xs text-green-600 font-medium';
      }
    } else {
      const statusDot = statusIndicator.querySelector('.w-2');
      const statusText = statusIndicator.querySelector('span');
      
      if (statusDot) {
        statusDot.className = 'w-2 h-2 bg-gray-400 rounded-full';
      }
      if (statusText) {
        statusText.textContent = 'Offline';
        statusText.className = 'text-xs text-gray-500 font-medium';
      }
    }
  }

  // Check for new notifications
  function checkForNewNotifications() {
    if (!currentUser || !backend) return;

    // Check for new achievements
    const progress = backend.getUserProgress(currentUser.id);
    if (progress && progress.badges && progress.badges.length > 0) {
      const lastBadge = progress.badges[progress.badges.length - 1];
      const lastBadgeTime = new Date(lastBadge.earnedDate || Date.now());
      const timeDiff = Date.now() - lastBadgeTime.getTime();
      
      // Show notification for achievements earned in the last 30 seconds
      if (timeDiff < 30000 && !lastBadge.notified) {
        showNotification(`🎉 Achievement unlocked: ${lastBadge.name}!`, 'success');
        lastBadge.notified = true;
      }
    }

    // Check for new announcements
    const announcements = backend.getAllAnnouncements();
    const recentAnnouncements = announcements.filter(ann => {
      const annTime = new Date(ann.createdDate || Date.now());
      return Date.now() - annTime.getTime() < 60000; // Last minute
    });

    if (recentAnnouncements.length > 0) {
      const latestAnnouncement = recentAnnouncements[0];
      showNotification(`📢 New announcement: ${latestAnnouncement.title}`, 'info');
    }
  }

  // Update motivation quote
  function updateMotivationQuote() {
    const motivationText = document.getElementById('motivation-text');
    if (!motivationText) return;

    const quotes = [
      '"But you will receive power when the Holy Spirit comes on you; and you will be my witnesses in Jerusalem, and in all Judea and Samaria, and to the ends of the earth."',
      '"For I know the plans I have for you," declares the LORD, "plans to prosper you and not to harm you, plans to give you hope and a future."',
      '"I can do all this through him who gives me strength."',
      '"Trust in the LORD with all your heart and lean not on your own understanding; in all your ways submit to him, and he will make your paths straight."',
      '"Be strong and courageous. Do not be afraid; do not be discouraged, for the LORD your God will be with you wherever you go."'
    ];

    const references = [
      '— Acts 1:8',
      '— Jeremiah 29:11',
      '— Philippians 4:13',
      '— Proverbs 3:5-6',
      '— Joshua 1:9'
    ];

    // Change quote every 5 minutes
    const quoteIndex = Math.floor(Date.now() / (5 * 60 * 1000)) % quotes.length;
    const newQuote = quotes[quoteIndex];
    const newReference = references[quoteIndex];

    if (motivationText.textContent !== newQuote) {
      motivationText.textContent = newQuote;
      const referenceElement = motivationText.nextElementSibling;
      if (referenceElement) {
        referenceElement.textContent = newReference;
      }
      
      // Add subtle animation
      motivationText.classList.add('animate-fade-in');
      setTimeout(() => motivationText.classList.remove('animate-fade-in'), 2000);
    }
  }

  // Setup event listeners
  function setupEventListeners() {
    try {
      console.log('Setting up user dashboard event listeners...');
      
      // Navigation
      const sidebarLinks = document.querySelectorAll('.sidebar-link');
      sidebarLinks.forEach(link => {
        link.addEventListener('click', handleNavigation);
      });

      // Continue learning button
      const continueBtn = document.getElementById('continue-btn');
      if (continueBtn) {
        continueBtn.addEventListener('click', continueLearning);
      }

      // Next lesson button
      const nextLessonBtn = document.getElementById('next-lesson-btn');
      if (nextLessonBtn) {
        nextLessonBtn.addEventListener('click', startNextLesson);
      }

      // Logout
      const logoutBtn = document.getElementById('logout-btn');
      if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
      }

      // Search functionality
      const searchInput = document.querySelector('input[placeholder*="Search"]');
      if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
      }

      // Real-time connection management
      document.addEventListener('visibilitychange', handleVisibilityChange);
      window.addEventListener('beforeunload', handleBeforeUnload);
      window.addEventListener('focus', handleWindowFocus);
      window.addEventListener('blur', handleWindowBlur);

      console.log('User dashboard event listeners setup completed');
    } catch (error) {
      console.error('Error setting up event listeners:', error);
    }
  }

  // Handle page visibility changes
  function handleVisibilityChange() {
    if (document.hidden) {
      // Page is hidden, update last activity
      if (currentUser && backend) {
        backend.updateUser(currentUser.id, {
          lastActivity: new Date().toISOString()
        });
      }
    } else {
      // Page is visible again, reconnect
      connectToBackend();
    }
  }

  // Handle before unload
  function handleBeforeUnload() {
    disconnectFromBackend();
  }

  // Handle window focus
  function handleWindowFocus() {
    connectToBackend();
  }

  // Handle window blur
  function handleWindowBlur() {
    // Update last activity when user switches tabs
    if (currentUser && backend) {
      backend.updateUser(currentUser.id, {
        lastActivity: new Date().toISOString()
      });
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
    document.querySelectorAll('section').forEach(section => {
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
        updateRecentActivity();
        updateNextLesson();
        break;
      case 'lessons':
        loadUserLessons();
        break;
      case 'achievements':
        loadUserAchievements();
        break;
      case 'community':
        loadCommunityData();
        break;
      case 'support':
        loadUserSupport();
        break;
    }
  }

  // Load user data
  function loadUserData() {
    if (!currentUser || !backend) return;

    // Get user progress
    const progress = backend.getUserProgress(currentUser.id);
    userState.currentProgress = progress;

    // Update dashboard
    updateDashboardStats();
    updateRecentActivity();
    updateNextLesson();
    updateUserInterface();
    
    // Update last activity timestamp
    userState.lastUpdate = new Date().toISOString();
  }

  // Connect to real-time backend
  function connectToBackend() {
    if (!currentUser || !backend) return;

    try {
      // Connect user to backend for real-time updates
      backend.connectUser(currentUser.id);
      
      // Update user's last activity
      backend.updateUser(currentUser.id, {
        lastActivity: new Date().toISOString(),
        isOnline: true
      });

      console.log('User connected to real-time backend');
    } catch (error) {
      console.error('Error connecting to backend:', error);
    }
  }

  // Disconnect from backend
  function disconnectFromBackend() {
    if (!currentUser || !backend) return;

    try {
      backend.disconnectUser(currentUser.id);
      console.log('User disconnected from real-time backend');
    } catch (error) {
      console.error('Error disconnecting from backend:', error);
    }
  }

  // Update dashboard statistics
  function updateDashboardStats() {
    if (!currentUser || !backend) return;

    const progress = backend.getUserProgress(currentUser.id);
    const stats = backend.getUserStats(currentUser.id);

    // Update lesson count
    const lessonsCompleted = document.getElementById('summary-lessons');
    if (lessonsCompleted) {
      lessonsCompleted.textContent = progress.completedLessons || 0;
    }

    // Update progress percentage
    const progressElement = document.getElementById('summary-progress');
    if (progressElement) {
      const totalLessons = 10; // Assuming 10 total lessons
      const percentage = Math.round(((progress.completedLessons || 0) / totalLessons) * 100);
      progressElement.textContent = `${percentage}%`;
    }

    // Update badges count
    const badgesElement = document.getElementById('summary-badges');
    if (badgesElement) {
      badgesElement.textContent = progress.badges?.length || 0;
    }
  }

  // Update recent activity
  function updateRecentActivity() {
    const activityList = document.getElementById('recent-activity');
    if (!activityList) return;

    const activities = getRecentActivities();
    
    activityList.innerHTML = activities.map(activity => `
      <li class="flex items-center gap-3 text-gray-700">
        <div class="w-6 h-6 ${activity.iconBg} rounded-full flex items-center justify-center">
          <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${activity.iconPath}"/>
          </svg>
        </div>
        <span class="text-sm">${activity.text}</span>
      </li>
    `).join('');
  }

  // Get recent activities for the user
  function getRecentActivities() {
    if (!currentUser || !backend) return [];

    const progress = backend.getUserProgress(currentUser.id);
    const activities = [];

    // Add completed lessons
    if (progress.completedLessons > 0) {
      activities.push({
        text: `Completed ${progress.completedLessons} lesson${progress.completedLessons > 1 ? 's' : ''}`,
        iconBg: 'bg-green-500',
        iconPath: 'M5 13l4 4L19 7'
      });
    }

    // Add badges earned
    if (progress.badges && progress.badges.length > 0) {
      activities.push({
        text: `Earned ${progress.badges.length} badge${progress.badges.length > 1 ? 's' : ''}`,
        iconBg: 'bg-yellow-500',
        iconPath: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'
      });
    }

    // Add current streak
    if (progress.currentStreak > 0) {
      activities.push({
        text: `${progress.currentStreak} day${progress.currentStreak > 1 ? 's' : ''} learning streak`,
        iconBg: 'bg-blue-500',
        iconPath: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
      });
    }

    return activities.slice(0, 3); // Show only 3 most recent
  }

  // Update next lesson information
  function updateNextLesson() {
    const nextLessonTitle = document.getElementById('next-lesson-title');
    const nextLessonBtn = document.getElementById('next-lesson-btn');
    
    if (!currentUser || !backend) return;

    const progress = backend.getUserProgress(currentUser.id);
    const nextLessonNumber = (progress.completedLessons || 0) + 1;
    
    const lessonTitles = [
      'Day 1: Foundations of Faith',
      'Day 2: Prayer & Devotion',
      'Day 3: Bible Study Methods',
      'Day 4: Worship & Praise',
      'Day 5: Fellowship & Community',
      'Day 6: Service & Ministry',
      'Day 7: Evangelism & Witness',
      'Day 8: Spiritual Gifts',
      'Day 9: Discipleship & Mentoring',
      'Day 10: Living for Christ'
    ];

    if (nextLessonTitle && nextLessonNumber <= lessonTitles.length) {
      nextLessonTitle.textContent = lessonTitles[nextLessonNumber - 1];
    }

    if (nextLessonBtn) {
      nextLessonBtn.addEventListener('click', () => {
        startLesson(nextLessonNumber);
      });
    }
  }

  // Continue learning function
  function continueLearning() {
    if (!currentUser || !backend) return;

    const progress = backend.getUserProgress(currentUser.id);
    const nextLesson = (progress.completedLessons || 0) + 1;
    
    startLesson(nextLesson);
  }

  // Start next lesson
  function startNextLesson() {
    if (!currentUser || !backend) return;

    const progress = backend.getUserProgress(currentUser.id);
    const nextLesson = (progress.completedLessons || 0) + 1;
    
    startLesson(nextLesson);
  }

  // Start a specific lesson
  function startLesson(lessonNumber) {
    if (!currentUser || !backend) return;

    // Show lesson modal or navigate to lesson page
    showLessonModal(lessonNumber);
  }

  // Show lesson modal
  function showLessonModal(lessonNumber) {
    const lessonTitles = [
      'Day 1: Foundations of Faith',
      'Day 2: Prayer & Devotion',
      'Day 3: Bible Study Methods',
      'Day 4: Worship & Praise',
      'Day 5: Fellowship & Community',
      'Day 6: Service & Ministry',
      'Day 7: Evangelism & Witness',
      'Day 8: Spiritual Gifts',
      'Day 9: Discipleship & Mentoring',
      'Day 10: Living for Christ'
    ];

    const lessonTitle = lessonTitles[lessonNumber - 1] || `Lesson ${lessonNumber}`;
    
    // Create simple modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
      <div class="bg-white rounded-2xl p-8 max-w-2xl mx-4 shadow-2xl">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-bold text-[#2046B3]">${lessonTitle}</h3>
          <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="prose max-w-none">
          <p class="text-gray-600 mb-4">Welcome to ${lessonTitle}! This lesson will help you grow in your faith journey.</p>
          <div class="bg-gradient-to-r from-[#7C3AED] to-[#2046B3] text-white p-6 rounded-xl mb-6">
            <h4 class="font-bold mb-2">Lesson Overview</h4>
            <p>In this lesson, you'll learn about the fundamental principles of Christian discipleship and how to apply them in your daily life.</p>
          </div>
          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-[#7C3AED] rounded-full flex items-center justify-center text-white font-bold">1</div>
              <span>Read the lesson content</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-[#7C3AED] rounded-full flex items-center justify-center text-white font-bold">2</div>
              <span>Complete the reflection questions</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-[#7C3AED] rounded-full flex items-center justify-center text-white font-bold">3</div>
              <span>Apply what you've learned</span>
            </div>
          </div>
        </div>
        <div class="flex gap-4 mt-8">
          <button onclick="completeLesson(${lessonNumber})" class="flex-1 bg-gradient-to-r from-[#7C3AED] to-[#2046B3] text-white py-3 rounded-xl font-bold hover:shadow-lg transition-all">
            Complete Lesson
          </button>
          <button onclick="this.closest('.fixed').remove()" class="flex-1 border border-gray-300 text-gray-700 py-3 rounded-xl font-bold hover:bg-gray-50 transition-all">
            Close
          </button>
        </div>
      </div>
    `;
    
    document.body.appendChild(modal);
  }

  // Complete lesson function
  function completeLesson(lessonNumber) {
    if (!currentUser || !backend) return;

    // Update progress
    const progress = backend.getUserProgress(currentUser.id);
    progress.completedLessons = lessonNumber;
    progress.lastCompletedLesson = new Date().toISOString();
    
    // Update backend
    backend.updateUserProgress(currentUser.id, progress);
    
    // Show completion message
    showNotification('Lesson completed successfully! 🎉', 'success');
    
    // Close modal and refresh dashboard
    const modal = document.querySelector('.fixed');
    if (modal) modal.remove();
    
    // Refresh dashboard data
    loadUserData();
  }

  // Load user lessons
  function loadUserLessons() {
    if (!currentUser || !backend) return;

    const progress = backend.getUserProgress(currentUser.id);
    const completedLessons = progress.completedLessons || 0;
    
    // Update lesson cards based on progress
    const lessonCards = document.querySelectorAll('[onclick*="showLessonModal"]');
    lessonCards.forEach((card, index) => {
      const lessonNumber = index + 1;
      if (lessonNumber <= completedLessons) {
        card.classList.add('bg-gradient-to-br', 'from-green-50', 'to-green-100', 'border-green-500');
        card.classList.remove('bg-gradient-to-br', 'from-blue-50', 'to-blue-100', 'border-blue-500');
      }
    });
  }

  // Load user achievements
  function loadUserAchievements() {
    if (!currentUser || !backend) return;

    const progress = backend.getUserProgress(currentUser.id);
    const achievements = progress.badges || [];
    
    // Update achievements display
    const achievementsSection = document.getElementById('achievements');
    if (achievementsSection) {
      // Implementation for achievements display
    }
  }

  // Load community data
  function loadCommunityData() {
    if (!currentUser || !backend) return;

    // Get community information
    const users = backend.getAllUsers();
    const onlineUsers = users.filter(user => user.isOnline);
    
    // Update community display
    const communitySection = document.getElementById('community');
    if (communitySection) {
      // Implementation for community display
    }
  }

  // Load user support
  function loadUserSupport() {
    if (!currentUser || !backend) return;

    // Get user's support tickets
    const tickets = backend.getAllSupportTickets();
    const userTickets = tickets.filter(ticket => ticket.userId === currentUser.id);
    
    // Update support display
    const supportSection = document.getElementById('support');
    if (supportSection) {
      // Implementation for support display
    }
  }

  // Handle search
  function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    // Implement search functionality
  }

  // Show admin view mode
  function showAdminViewMode() {
    // Add admin view indicator
    const header = document.querySelector('header');
    if (header) {
      const adminIndicator = document.createElement('div');
      adminIndicator.className = 'fixed top-4 left-4 z-50 bg-gradient-to-r from-[#7C3AED] to-[#2046B3] text-white px-4 py-2 rounded-lg shadow-lg';
      adminIndicator.innerHTML = `
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
          <span class="font-semibold">Admin View: ${currentUser.name}</span>
          <button onclick="closeAdminView()" class="ml-2 hover:bg-white/20 rounded px-2 py-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      `;
      document.body.appendChild(adminIndicator);
    }

    // Update logout button to return to admin dashboard
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
      logoutBtn.textContent = 'Return to Admin';
      logoutBtn.onclick = closeAdminView;
    }
  }

  // Close admin view
  function closeAdminView() {
    localStorage.removeItem('admin_viewing_user');
    window.close();
  }

  // Handle logout
  function handleLogout() {
    if (userState.isAdminView) {
      closeAdminView();
      return;
    }
    
    if (confirm('Are you sure you want to logout?')) {
      localStorage.removeItem('discipleship_logged_in_user');
      window.location.href = 'discipleship-login.html';
    }
  }

  // Show notification
  function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-xl shadow-lg z-50 ${
      type === 'success' ? 'bg-green-500 text-white' : 
      type === 'error' ? 'bg-red-500 text-white' : 
      'bg-[#7C3AED] text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.remove();
    }, 3000);
  }

  // Show mini toast at top-right
  function showMiniToast(message) {
    let toast = document.createElement('div');
    toast.className = 'fixed top-6 right-6 bg-[#7C3AED] text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-fade-in';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
      toast.classList.add('animate-fade-out');
      setTimeout(() => toast.remove(), 500);
    }, 1800);
  }

  // Animate progress bar at top
  function animateProgressBar() {
    let bar = document.getElementById('realtime-progress-bar');
    if (!bar) {
      bar = document.createElement('div');
      bar.id = 'realtime-progress-bar';
      bar.className = 'fixed top-0 left-0 w-0 h-1 bg-gradient-to-r from-[#7C3AED] to-[#FDBA17] z-50 transition-all duration-500';
      document.body.appendChild(bar);
    }
    bar.style.width = '0';
    setTimeout(() => {
      bar.style.width = '100vw';
      setTimeout(() => {
        bar.style.width = '0';
      }, 600);
    }, 10);
  }

  // Add or update real-time indicator with tooltip
  function updateRealtimeIndicator(status = 'live') {
    let indicator = document.getElementById('realtime-indicator');
    if (!indicator) {
      indicator = document.createElement('div');
      indicator.id = 'realtime-indicator';
      indicator.className = 'fixed bottom-6 right-6 z-50 flex items-center gap-2 cursor-pointer group';
      indicator.innerHTML = `
        <span id="realtime-dot" class="w-3 h-3 rounded-full animate-pulse"></span>
        <span id="realtime-text" class="text-xs font-bold"></span>
        <div id="realtime-tooltip" class="absolute bottom-10 right-0 bg-gray-900 text-white text-xs rounded px-3 py-2 shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity z-50"></div>
      `;
      document.body.appendChild(indicator);
    }
    const dot = indicator.querySelector('#realtime-dot');
    const text = indicator.querySelector('#realtime-text');
    const tooltip = indicator.querySelector('#realtime-tooltip');
    const secondsAgo = Math.floor((Date.now() - lastRealtimeUpdate) / 1000);
    if (status === 'live') {
      dot.className = 'w-3 h-3 rounded-full bg-green-500 animate-pulse';
      text.textContent = 'Live';
      text.className = 'text-xs font-bold text-green-600';
      tooltip.textContent = `Last update: ${secondsAgo}s ago. Status: Live.`;
    } else {
      dot.className = 'w-3 h-3 rounded-full bg-gray-400';
      text.textContent = 'Offline';
      text.className = 'text-xs font-bold text-gray-400';
      tooltip.textContent = `Last update: ${secondsAgo}s ago. Status: Offline.`;
    }
  }

  // Add or update last updated text
  function updateLastUpdatedText() {
    let el = document.getElementById('last-updated-text');
    if (!el) {
      el = document.createElement('div');
      el.id = 'last-updated-text';
      el.className = 'text-xs text-gray-400 mt-2 text-right';
      const heroSection = document.querySelector('#dashboard .grid');
      if (heroSection) heroSection.parentNode.insertBefore(el, heroSection.nextSibling);
    }
    const secondsAgo = Math.floor((Date.now() - lastRealtimeUpdate) / 1000);
    el.textContent = `Last updated ${secondsAgo}s ago`;
  }

  function updateHeroAndContinueLearning() {
    if (!currentUser || !backend) return;

    // Get latest stats
    const progress = backend.getUserProgress(currentUser.id);
    const stats = backend.getUserStats(currentUser.id);

    // Lessons Completed
    const lessonsEl = document.getElementById('summary-lessons');
    if (lessonsEl) {
      const newVal = stats.completedLessons;
      if (lessonsEl.textContent != newVal) {
        lessonsEl.textContent = newVal;
        lessonsEl.classList.add('animate-pulse');
        setTimeout(() => lessonsEl.classList.remove('animate-pulse'), 800);
      }
    }

    // Overall Progress
    const progressEl = document.getElementById('summary-progress');
    if (progressEl) {
      const newVal = stats.progressPercentage + '%';
      if (progressEl.textContent != newVal) {
        progressEl.textContent = newVal;
        progressEl.classList.add('animate-pulse');
        setTimeout(() => progressEl.classList.remove('animate-pulse'), 800);
      }
    }

    // Badges Earned
    const badgesEl = document.getElementById('summary-badges');
    if (badgesEl) {
      const newVal = stats.certificatesEarned;
      if (badgesEl.textContent != newVal) {
        badgesEl.textContent = newVal;
        badgesEl.classList.add('animate-pulse');
        setTimeout(() => badgesEl.classList.remove('animate-pulse'), 800);
      }
    }

    // Continue Learning (next lesson)
    const nextLessonTitle = document.getElementById('next-lesson-title');
    const nextLessonBtn = document.getElementById('continue-btn') || document.getElementById('next-lesson-btn');
    const lessonTitles = [
      'Day 1: Foundations of Faith',
      'Day 2: Prayer & Devotion',
      'Day 3: Bible Study Methods',
      'Day 4: Worship & Praise',
      'Day 5: Fellowship & Community',
      'Day 6: Service & Ministry',
      'Day 7: Evangelism & Witness',
      'Day 8: Spiritual Gifts',
      'Day 9: Discipleship & Mentoring',
      'Day 10: Living for Christ'
    ];
    const nextLessonNumber = (progress.completedLessons || 0) + 1;
    let heroGrid = document.querySelector('#dashboard .grid');
    if (nextLessonTitle && nextLessonNumber <= lessonTitles.length) {
      const newTitle = lessonTitles[nextLessonNumber - 1];
      if (nextLessonTitle.textContent !== newTitle) {
        nextLessonTitle.textContent = newTitle;
        nextLessonTitle.classList.add('animate-fade-in');
        setTimeout(() => nextLessonTitle.classList.remove('animate-fade-in'), 800);
        // Animate Continue Learning button
        if (nextLessonBtn) {
          nextLessonBtn.classList.add('animate-pulse', 'ring-4', 'ring-[#FDBA17]');
          setTimeout(() => nextLessonBtn.classList.remove('animate-pulse', 'ring-4', 'ring-[#FDBA17]'), 800);
        }
        // Glow Heros grid
        if (heroGrid) {
          heroGrid.classList.add('ring-4', 'ring-[#7C3AED]', 'shadow-xl');
          setTimeout(() => heroGrid.classList.remove('ring-4', 'ring-[#7C3AED]', 'shadow-xl'), 800);
        }
      }
      lastLessonTitle = newTitle;
    }
    if (nextLessonBtn) {
      nextLessonBtn.onclick = () => startLesson(nextLessonNumber);
    }

    // Real-time indicator, last updated, toast, and progress bar
    lastRealtimeUpdate = Date.now();
    updateRealtimeIndicator('live');
    updateLastUpdatedText();
    showMiniToast('Data updated!');
    animateProgressBar();
  }

  // Monitor for offline (no updates)
  setInterval(() => {
    if (Date.now() - lastRealtimeUpdate > 15000) {
      updateRealtimeIndicator('offline');
    }
    updateLastUpdatedText();
  }, 3000);

  // Update startRealTimeUpdates to use the new function
  function startRealTimeUpdates() {
    console.log('Starting real-time updates for user dashboard...');
    setInterval(() => {
      if (currentUser && backend) {
        updateHeroAndContinueLearning();
        updateUserProgress();
        updateActivityFeed();
        updateOnlineStatus();
        checkForNewNotifications();
        updateMotivationQuote();
      }
    }, 5000);
    setInterval(() => {
      if (currentUser && backend) {
        loadUserData();
        updateDashboardStats();
        updateNextLesson();
      }
    }, 30000);
    // Initial update
    if (currentUser && backend) {
      updateHeroAndContinueLearning();
      updateUserProgress();
      updateActivityFeed();
      updateOnlineStatus();
    }
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initUserDashboard);
  } else {
    initUserDashboard();
  }

  // Export functions for global use
  window.UserSimple = {
    showLessonModal,
    completeLesson,
    showNotification,
    handleLogout,
    closeAdminView
  };

  // Global functions for HTML onclick handlers
  window.closeAdminView = function() {
    if (window.UserSimple) {
      window.UserSimple.closeAdminView();
    }
  };

})(); 