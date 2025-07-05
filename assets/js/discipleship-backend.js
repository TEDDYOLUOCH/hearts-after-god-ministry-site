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