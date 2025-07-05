// Advanced Discipleship Portal JavaScript
class AdvancedDiscipleshipPortal {
  constructor() {
    this.currentUser = null;
    this.userProgress = {};
    this.lessons = this.initializeLessons();
    this.quizzes = this.initializeQuizzes();
    this.currentLessonId = null;
    this.currentTab = 'content';
    this.init();
  }

  init() {
    this.setupEventListeners();
    this.checkAuthStatus();
  }

  setupEventListeners() {
    // Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
      loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = document.getElementById('email').value.trim().toLowerCase();
        const password = document.getElementById('password').value;
        const users = JSON.parse(localStorage.getItem('discipleship_users') || '[]');
        // Find user by email (case-insensitive, trimmed) and password
        const user = users.find(u => u.email.trim().toLowerCase() === email && u.password === password);
        if (!user) {
          alert('Invalid email or password.');
          return;
        }
        handleLogin(email);
        const loginSection = document.getElementById('login-section');
        if (loginSection) loginSection.classList.add('hidden');
        const dashboardSection = document.getElementById('dashboard-section');
        if (dashboardSection) dashboardSection.classList.remove('hidden');
        // Set the user's name in the dashboard
        const nameSpan = document.getElementById('user-name');
        if (nameSpan) nameSpan.innerText = user.name;
      });
    }

    // Register form
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
      registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = document.getElementById('reg-email').value.trim().toLowerCase();
        if (isAdmin(email)) {
          alert('Admin account cannot be registered.');
          return false;
        }
        const users = JSON.parse(localStorage.getItem('discipleship_users') || '[]');
        if (users.find(u => u.email.trim().toLowerCase() === email)) {
          alert('User already exists.');
          return false;
        }
        const name = document.getElementById('reg-name').value.trim();
        const password = document.getElementById('reg-password').value;
        // In a real app, hash the password here
        users.push({ name, email, password, role: 'user' });
        localStorage.setItem('discipleship_users', JSON.stringify(users));
        alert('Registration successful! Please log in.');
        // Optionally, redirect to login or auto-login
      });
    }

    // Navigation between login/register
    document.getElementById('show-register').addEventListener('click', () => {
      this.showSection('register-section');
    });

    document.getElementById('show-login').addEventListener('click', () => {
      this.showSection('login-section');
    });

    // Logout
    document.getElementById('logout-btn').addEventListener('click', () => {
      this.handleLogout();
    });

    // Module buttons
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('module-btn')) {
        const moduleId = e.target.dataset.module;
        this.openLesson(moduleId);
      }
    });

    // Lesson modal
    document.getElementById('close-lesson').addEventListener('click', () => {
      this.closeLessonModal();
    });

    document.getElementById('complete-lesson').addEventListener('click', () => {
      this.completeCurrentLesson();
    });

    // Close modal on outside click
    document.getElementById('lesson-modal').addEventListener('click', (e) => {
      if (e.target.id === 'lesson-modal') {
        this.closeLessonModal();
      }
    });

    // Tab navigation
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('lesson-tab')) {
        const tabName = e.target.dataset.tab;
        this.switchTab(tabName);
      }
    });

    // Quiz submission
    document.addEventListener('submit', (e) => {
      if (e.target.id === 'quiz-form') {
        e.preventDefault();
        this.submitQuiz();
      }
    });

    // Notes saving
    document.addEventListener('input', (e) => {
      if (e.target.id === 'lesson-notes-textarea') {
        this.autoSaveNotes();
      }
    });

    // Refined show/hide password toggle: place 👁 icon inside the password input
    (function() {
      const passwordInput = document.getElementById('password');
      if (passwordInput) {
        // Wrap the input in a relative div
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        passwordInput.parentNode.insertBefore(wrapper, passwordInput);
        wrapper.appendChild(passwordInput);
        passwordInput.style.paddingRight = '2.5em';
        // Create the eye icon
        const eye = document.createElement('span');
        eye.textContent = '👁';
        eye.style.position = 'absolute';
        eye.style.right = '0.75em';
        eye.style.top = '50%';
        eye.style.transform = 'translateY(-50%)';
        eye.style.cursor = 'pointer';
        eye.style.fontSize = '1.2em';
        eye.title = 'Show/Hide Password';
        wrapper.appendChild(eye);
        eye.onclick = function() {
          if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eye.textContent = '🙈';
          } else {
            passwordInput.type = 'password';
            eye.textContent = '👁';
          }
        };
      }
    })();
  }

  initializeLessons() {
    return {
      1: {
        title: "Day 1: Foundations of Faith",
        duration: "45 minutes",
        content: `
          <h3>Understanding Salvation and Grace</h3>
          <p>Welcome to your discipleship journey! Today we'll explore the foundational truths of your faith.</p>
          
          <h4>Key Scripture: Ephesians 2:8-10</h4>
          <blockquote class="bg-gray-100 p-4 rounded-lg my-4">
            "For it is by grace you have been saved, through faith—and this is not from yourselves, it is the gift of God—not by works, so that no one can boast. For we are God's handiwork, created in Christ Jesus to do good works, which God prepared in advance for us to do."
          </blockquote>
          
          <h4>Today's Learning Objectives:</h4>
          <ul class="list-disc list-inside space-y-2 my-4">
            <li>Understand what salvation means and how it works</li>
            <li>Recognize the difference between grace and works</li>
            <li>Discover your new identity in Christ</li>
            <li>Learn how to share your testimony</li>
          </ul>
          
          <h4>Reflection Questions:</h4>
          <ol class="list-decimal list-inside space-y-2 my-4">
            <li>What does salvation mean to you personally?</li>
            <li>How has God's grace changed your life?</li>
            <li>What does it mean to have a new identity in Christ?</li>
          </ol>
          
          <h4>Practical Application:</h4>
          <p>Take time today to write out your personal testimony. Include:</p>
          <ul class="list-disc list-inside space-y-1 my-4">
            <li>What your life was like before Christ</li>
            <li>How you came to know Jesus</li>
            <li>How your life has changed since then</li>
          </ul>
          
          <div class="bg-blue-50 p-4 rounded-lg my-6">
            <h4 class="font-semibold text-blue-800">Prayer Focus:</h4>
            <p class="text-blue-700">Thank God for His grace and salvation. Ask Him to help you understand your new identity in Christ and to give you opportunities to share your faith with others.</p>
          </div>
        `,
        resources: [
          { name: "Day 1 Notes", type: "PDF", size: "2.1MB", url: "#" },
          { name: "Salvation Scripture Cards", type: "PDF", size: "1.5MB", url: "#" },
          { name: "Testimony Template", type: "DOCX", size: "0.8MB", url: "#" },
          { name: "Audio Lesson", type: "MP3", size: "15.2MB", url: "#" }
        ]
      },
      2: {
        title: "Day 2: Prayer & Devotion",
        duration: "60 minutes",
        content: `
          <h3>Building a Daily Prayer Life</h3>
          <p>Today we'll explore the power and practice of prayer in your daily walk with God.</p>
          
          <h4>Key Scripture: Matthew 6:6</h4>
          <blockquote class="bg-gray-100 p-4 rounded-lg my-4">
            "But when you pray, go into your room, close the door and pray to your Father, who is unseen. Then your Father, who sees what is done in secret, will reward you."
          </blockquote>
          
          <h4>Today's Learning Objectives:</h4>
          <ul class="list-disc list-inside space-y-2 my-4">
            <li>Understand the importance of daily prayer</li>
            <li>Learn different types of prayer</li>
            <li>Develop a personal prayer routine</li>
            <li>Practice listening to God's voice</li>
          </ul>
          
          <h4>The ACTS Prayer Model:</h4>
          <div class="grid md:grid-cols-2 gap-4 my-6">
            <div class="bg-green-50 p-4 rounded-lg">
              <h5 class="font-semibold text-green-800">Adoration</h5>
              <p class="text-green-700">Praise God for who He is</p>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg">
              <h5 class="font-semibold text-blue-800">Confession</h5>
              <p class="text-blue-700">Confess your sins to God</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
              <h5 class="font-semibold text-yellow-800">Thanksgiving</h5>
              <p class="text-yellow-700">Thank God for His blessings</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
              <h5 class="font-semibold text-purple-800">Supplication</h5>
              <p class="text-purple-700">Ask God for your needs</p>
            </div>
          </div>
          
          <h4>Daily Prayer Challenge:</h4>
          <p>Commit to spending at least 15 minutes in prayer each day this week. Use the ACTS model to structure your prayers.</p>
          
          <div class="bg-gold-50 p-4 rounded-lg my-6">
            <h4 class="font-semibold text-gold-800">Prayer Focus:</h4>
            <p class="text-gold-700">Ask God to help you develop a consistent prayer life. Pray for wisdom, strength, and opportunities to serve Him.</p>
          </div>
        `,
        resources: [
          { name: "Prayer Journal Template", type: "PDF", size: "1.2MB", url: "#" },
          { name: "ACTS Prayer Guide", type: "PDF", size: "0.9MB", url: "#" },
          { name: "Scripture Prayer Cards", type: "PDF", size: "1.8MB", url: "#" },
          { name: "Prayer Audio Guide", type: "MP3", size: "22.1MB", url: "#" }
        ]
      },
      3: {
        title: "Day 3: Community & Fellowship",
        duration: "50 minutes",
        content: `
          <h3>The Power of Christian Community</h3>
          <p>Today we'll explore the importance of being connected to other believers and building meaningful relationships.</p>
          
          <h4>Key Scripture: Hebrews 10:24-25</h4>
          <blockquote class="bg-gray-100 p-4 rounded-lg my-4">
            "And let us consider how we may spur one another on toward love and good deeds, not giving up meeting together, as some are in the habit of doing, but encouraging one another—and all the more as you see the Day approaching."
          </blockquote>
          
          <h4>Today's Learning Objectives:</h4>
          <ul class="list-disc list-inside space-y-2 my-4">
            <li>Understand the importance of Christian community</li>
            <li>Learn how to build authentic relationships</li>
            <li>Discover your role in the body of Christ</li>
            <li>Practice accountability and encouragement</li>
          </ul>
          
          <h4>Benefits of Christian Fellowship:</h4>
          <div class="space-y-3 my-6">
            <div class="flex items-start space-x-3">
              <div class="w-6 h-6 bg-blue rounded-full flex items-center justify-center text-white text-sm font-bold mt-1">1</div>
              <div>
                <h5 class="font-semibold">Spiritual Growth</h5>
                <p class="text-gray-600">Iron sharpens iron - we grow together</p>
              </div>
            </div>
            <div class="flex items-start space-x-3">
              <div class="w-6 h-6 bg-blue rounded-full flex items-center justify-center text-white text-sm font-bold mt-1">2</div>
              <div>
                <h5 class="font-semibold">Accountability</h5>
                <p class="text-gray-600">Support and encouragement in your walk</p>
              </div>
            </div>
            <div class="flex items-start space-x-3">
              <div class="w-6 h-6 bg-blue rounded-full flex items-center justify-center text-white text-sm font-bold mt-1">3</div>
              <div>
                <h5 class="font-semibold">Giftedness</h5>
                <p class="text-gray-600">Discover and use your spiritual gifts</p>
              </div>
            </div>
          </div>
          
          <h4>Building Authentic Relationships:</h4>
          <ul class="list-disc list-inside space-y-2 my-4">
            <li>Be vulnerable and honest about your struggles</li>
            <li>Listen actively and show genuine interest</li>
            <li>Offer encouragement and support</li>
            <li>Pray for and with others</li>
            <li>Serve together in ministry</li>
          </ul>
          
          <div class="bg-purple-50 p-4 rounded-lg my-6">
            <h4 class="font-semibold text-purple-800">Prayer Focus:</h4>
            <p class="text-purple-700">Ask God to help you build meaningful relationships with other believers. Pray for opportunities to encourage and serve others in your community.</p>
          </div>
        `,
        resources: [
          { name: "Community Building Guide", type: "PDF", size: "2.3MB", url: "#" },
          { name: "Spiritual Gifts Assessment", type: "PDF", size: "1.1MB", url: "#" },
          { name: "Accountability Partner Guide", type: "PDF", size: "1.6MB", url: "#" },
          { name: "Fellowship Video", type: "MP4", size: "45.7MB", url: "#" }
        ]
      }
    };
  }

  initializeQuizzes() {
    return {
      1: {
        title: "Day 1 Quiz: Foundations of Faith",
        questions: [
          {
            question: "What is salvation?",
            options: [
              "A religious ritual",
              "Being saved from sin through Jesus Christ",
              "A good deed",
              "Going to church regularly"
            ],
            correct: 1,
            explanation: "Salvation is being saved from sin through Jesus Christ's death and resurrection."
          },
          {
            question: "What does Ephesians 2:8-9 teach us about salvation?",
            options: [
              "We earn salvation through good works",
              "Salvation is a gift from God, not by works",
              "Only certain people can be saved",
              "Salvation requires perfect behavior"
            ],
            correct: 1,
            explanation: "Ephesians 2:8-9 clearly states that salvation is by grace through faith, not by works."
          },
          {
            question: "What is your new identity in Christ?",
            options: [
              "A sinner",
              "A child of God",
              "A religious person",
              "A church member"
            ],
            correct: 1,
            explanation: "When you accept Christ, you become a child of God with a new identity."
          },
          {
            question: "What should be included in your personal testimony?",
            options: [
              "Only your current life",
              "Life before Christ, how you came to know Him, and life after",
              "Only your church activities",
              "Your religious background"
            ],
            correct: 1,
            explanation: "A complete testimony includes your life before Christ, how you came to know Him, and how your life changed."
          }
        ]
      },
      2: {
        title: "Day 2 Quiz: Prayer & Devotion",
        questions: [
          {
            question: "What does the ACTS prayer model stand for?",
            options: [
              "Always, Confess, Thank, Serve",
              "Adoration, Confession, Thanksgiving, Supplication",
              "Ask, Confess, Trust, Submit",
              "Adore, Confess, Thank, Seek"
            ],
            correct: 1,
            explanation: "ACTS stands for Adoration, Confession, Thanksgiving, and Supplication."
          },
          {
            question: "According to Matthew 6:6, where should we pray?",
            options: [
              "Only in church",
              "In public places",
              "In our room, in private",
              "With others only"
            ],
            correct: 2,
            explanation: "Jesus teaches us to pray in private, in our room, not for show."
          },
          {
            question: "What is the purpose of daily prayer?",
            options: [
              "To impress others",
              "To build relationship with God",
              "To fulfill religious requirements",
              "To get what we want"
            ],
            correct: 1,
            explanation: "Daily prayer builds our relationship with God and helps us grow spiritually."
          }
        ]
      }
    };
  }

  checkAuthStatus() {
    const user = localStorage.getItem('discipleship_user');
    if (user) {
      this.currentUser = JSON.parse(user);
      this.loadUserProgress();
      this.showDashboard();
    }
  }

  handleLogin() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    if (email && password) {
      this.currentUser = {
        id: 1,
        name: email.split('@')[0],
        email: email,
        joinedDate: new Date().toISOString()
      };

      localStorage.setItem('discipleship_user', JSON.stringify(this.currentUser));
      this.loadUserProgress();
      this.showDashboard();
    } else {
      this.showMessage('Please enter both email and password', 'error');
    }
  }

  handleRegister() {
    const name = document.getElementById('reg-name').value;
    const email = document.getElementById('reg-email').value;
    const password = document.getElementById('reg-password').value;
    const confirmPassword = document.getElementById('reg-confirm-password').value;

    if (password !== confirmPassword) {
      this.showMessage('Passwords do not match', 'error');
      return;
    }

    if (name && email && password) {
      this.currentUser = {
        id: Date.now(),
        name: name,
        email: email,
        joinedDate: new Date().toISOString()
      };

      localStorage.setItem('discipleship_user', JSON.stringify(this.currentUser));
      this.initializeUserProgress();
      this.showDashboard();
      this.showMessage('Account created successfully!', 'success');
    } else {
      this.showMessage('Please fill in all fields', 'error');
    }
  }

  handleLogout() {
    this.currentUser = null;
    localStorage.removeItem('discipleship_user');
    localStorage.removeItem('user_progress');
    this.showSection('login-section');
  }

  loadUserProgress() {
    const progress = localStorage.getItem('user_progress');
    this.userProgress = progress ? JSON.parse(progress) : this.initializeUserProgress();
  }

  initializeUserProgress() {
    const progress = {
      completedLessons: [],
      currentLesson: 1,
      certificates: [],
      startDate: new Date().toISOString(),
      quizScores: {},
      timeSpent: {},
      notes: {},
      streak: 0,
      lastActivity: new Date().toISOString()
    };
    localStorage.setItem('user_progress', JSON.stringify(progress));
    return progress;
  }

  showDashboard() {
    this.showSection('dashboard-section');
    this.updateDashboard();
  }

  showSection(sectionId) {
    const loginSection = document.getElementById('login-section');
    if (loginSection) loginSection.classList.add('hidden');
    document.getElementById('register-section').classList.add('hidden');
    document.getElementById('dashboard-section').classList.add('hidden');

    document.getElementById(sectionId).classList.remove('hidden');
  }

  updateDashboard() {
    if (!this.currentUser) return;

    // Update user name
    document.getElementById('user-name').textContent = this.currentUser.name;

    // Calculate progress
    const totalLessons = Object.keys(this.lessons).length;
    const completedCount = this.userProgress.completedLessons.length;
    const progressPercentage = Math.round((completedCount / totalLessons) * 100);

    // Update progress display
    document.getElementById('overall-progress').textContent = `${progressPercentage}%`;
    document.getElementById('progress-bar').style.width = `${progressPercentage}%`;
    document.getElementById('lessons-completed').textContent = `${completedCount}/${totalLessons}`;
    document.getElementById('total-lessons').textContent = completedCount;
    document.getElementById('certificates-count').textContent = this.userProgress.certificates.length;

    // Calculate and display advanced stats
    this.updateAdvancedStats();

    // Update module buttons
    this.updateModuleButtons();
  }

  updateAdvancedStats() {
    // Calculate total time spent
    const totalTime = Object.values(this.userProgress.timeSpent).reduce((sum, time) => sum + time, 0);
    const hours = Math.floor(totalTime / 60);
    document.getElementById('total-time').textContent = `${hours}h`;

    // Calculate average quiz score
    const quizScores = Object.values(this.userProgress.quizScores);
    const avgScore = quizScores.length > 0 ? Math.round(quizScores.reduce((sum, score) => sum + score, 0) / quizScores.length) : 0;
    document.getElementById('quiz-score').textContent = `${avgScore}%`;

    // Update streak
    document.getElementById('streak-count').textContent = this.userProgress.streak || 0;
  }

  updateModuleButtons() {
    const moduleButtons = document.querySelectorAll('.module-btn');
    
    moduleButtons.forEach(button => {
      const moduleId = parseInt(button.dataset.module);
      const isCompleted = this.userProgress.completedLessons.includes(moduleId);
      const isUnlocked = moduleId === 1 || this.userProgress.completedLessons.includes(moduleId - 1);

      if (isCompleted) {
        button.textContent = 'Completed';
        button.className = 'module-btn bg-green-600 text-white px-4 py-2 rounded-lg cursor-default';
        button.disabled = true;
      } else if (isUnlocked) {
        button.textContent = 'Start Lesson';
        button.className = 'module-btn bg-blue text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition';
        button.disabled = false;
      } else {
        button.textContent = 'Locked';
        button.className = 'module-btn bg-gray-300 text-gray-600 px-4 py-2 rounded-lg cursor-not-allowed';
        button.disabled = true;
      }
    });
  }

  openLesson(moduleId) {
    const lesson = this.lessons[moduleId];
    if (!lesson) return;

    this.currentLessonId = parseInt(moduleId);
    this.currentTab = 'content';

    document.getElementById('lesson-title').textContent = lesson.title;
    this.loadTabContent('content');
    this.updateTabNavigation();
    
    // Show modal
    document.getElementById('lesson-modal').classList.remove('hidden');
    
    // Update navigation buttons
    this.updateLessonNavigation();
  }

  closeLessonModal() {
    document.getElementById('lesson-modal').classList.add('hidden');
    this.currentLessonId = null;
    this.currentTab = 'content';
  }

  switchTab(tabName) {
    this.currentTab = tabName;
    this.loadTabContent(tabName);
    this.updateTabNavigation();
  }

  updateTabNavigation() {
    const tabs = document.querySelectorAll('.lesson-tab');
    tabs.forEach(tab => {
      tab.classList.remove('active', 'border-b-2', 'border-blue', 'text-blue');
      tab.classList.add('text-gray-600');
    });

    const activeTab = document.querySelector(`[data-tab="${this.currentTab}"]`);
    if (activeTab) {
      activeTab.classList.add('active', 'border-b-2', 'border-blue', 'text-blue');
      activeTab.classList.remove('text-gray-600');
    }
  }

  loadTabContent(tabName) {
    // Hide all tab content
    document.querySelectorAll('.lesson-tab-content').forEach(content => {
      content.classList.add('hidden');
    });

    // Show selected tab content
    const contentElement = document.getElementById(`lesson-${tabName}`);
    if (contentElement) {
      contentElement.classList.remove('hidden');
    }

    // Load content based on tab
    switch(tabName) {
      case 'content':
        this.loadLessonContent();
        break;
      case 'quiz':
        this.loadQuizContent();
        break;
      case 'resources':
        this.loadResourcesContent();
        break;
      case 'notes':
        this.loadNotesContent();
        break;
    }
  }

  loadLessonContent() {
    const lesson = this.lessons[this.currentLessonId];
    if (!lesson) return;

    document.getElementById('lesson-content').innerHTML = lesson.content;
  }

  loadQuizContent() {
    const quiz = this.quizzes[this.currentLessonId];
    if (!quiz) {
      document.getElementById('lesson-quiz').innerHTML = '<p class="text-gray-600 text-center py-8">No quiz available for this lesson.</p>';
      return;
    }

    let quizHTML = `
      <h3 class="text-xl font-bold text-gray-800 mb-6">${quiz.title}</h3>
      <form id="quiz-form" class="space-y-6">
    `;

    quiz.questions.forEach((q, index) => {
      quizHTML += `
        <div class="bg-gray-50 p-6 rounded-lg">
          <h4 class="font-semibold text-gray-800 mb-4">Question ${index + 1}: ${q.question}</h4>
          <div class="space-y-3">
      `;
      
      q.options.forEach((option, optionIndex) => {
        quizHTML += `
          <label class="flex items-center space-x-3 cursor-pointer">
            <input type="radio" name="q${index}" value="${optionIndex}" class="text-blue focus:ring-blue">
            <span class="text-gray-700">${option}</span>
          </label>
        `;
      });
      
      quizHTML += `
          </div>
        </div>
      `;
    });

    quizHTML += `
        <button type="submit" class="w-full bg-blue text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition">
          Submit Quiz
        </button>
      </form>
    `;

    document.getElementById('lesson-quiz').innerHTML = quizHTML;
  }

  loadResourcesContent() {
    const lesson = this.lessons[this.currentLessonId];
    if (!lesson || !lesson.resources) {
      document.getElementById('lesson-resources').innerHTML = '<p class="text-gray-600 text-center py-8">No resources available for this lesson.</p>';
      return;
    }

    let resourcesHTML = `
      <h3 class="text-xl font-bold text-gray-800 mb-6">Lesson Resources</h3>
      <div class="grid md:grid-cols-2 gap-4">
    `;

    lesson.resources.forEach(resource => {
      resourcesHTML += `
        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="font-semibold text-gray-800">${resource.name}</h4>
              <p class="text-sm text-gray-600">${resource.type} • ${resource.size}</p>
            </div>
            <button class="bg-blue text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition" onclick="this.downloadResource('${resource.name}')">
              Download
            </button>
          </div>
        </div>
      `;
    });

    resourcesHTML += '</div>';
    document.getElementById('lesson-resources').innerHTML = resourcesHTML;
  }

  loadNotesContent() {
    const lessonId = this.currentLessonId;
    const savedNotes = this.userProgress.notes[lessonId] || '';
    
    const notesHTML = `
      <h3 class="text-xl font-bold text-gray-800 mb-6">Personal Notes</h3>
      <textarea id="lesson-notes-textarea" class="w-full h-64 p-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue" placeholder="Write your notes here...">${savedNotes}</textarea>
      <div class="mt-4 flex justify-between items-center">
        <span class="text-sm text-gray-600">Notes are automatically saved</span>
        <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition" onclick="this.saveNotes()">
          Save Notes
        </button>
      </div>
    `;
    
    document.getElementById('lesson-notes').innerHTML = notesHTML;
  }

  submitQuiz() {
    const quiz = this.quizzes[this.currentLessonId];
    if (!quiz) return;

    const form = document.getElementById('quiz-form');
    const formData = new FormData(form);
    
    let score = 0;
    let totalQuestions = quiz.questions.length;

    quiz.questions.forEach((question, index) => {
      const answer = formData.get(`q${index}`);
      if (answer && parseInt(answer) === question.correct) {
        score++;
      }
    });

    const percentage = Math.round((score / totalQuestions) * 100);
    
    // Save quiz score
    this.userProgress.quizScores[this.currentLessonId] = percentage;
    localStorage.setItem('user_progress', JSON.stringify(this.userProgress));

    // Show results
    this.showQuizResults(score, totalQuestions, percentage, quiz);
  }

  showQuizResults(score, total, percentage, quiz) {
    let resultsHTML = `
      <h3 class="text-xl font-bold text-gray-800 mb-6">Quiz Results</h3>
      <div class="bg-blue-50 p-6 rounded-lg mb-6">
        <div class="text-center">
          <div class="text-4xl font-bold text-blue mb-2">${percentage}%</div>
          <div class="text-gray-600">You got ${score} out of ${total} questions correct</div>
        </div>
      </div>
    `;

    if (percentage >= 70) {
      resultsHTML += `
        <div class="bg-green-50 p-4 rounded-lg mb-6">
          <div class="flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-green-800 font-semibold">Great job! You passed the quiz!</span>
          </div>
        </div>
      `;
    } else {
      resultsHTML += `
        <div class="bg-yellow-50 p-4 rounded-lg mb-6">
          <div class="flex items-center">
            <svg class="w-6 h-6 text-yellow-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <span class="text-yellow-800 font-semibold">Review the lesson and try again!</span>
          </div>
        </div>
      `;
    }

    // Add review section
    resultsHTML += '<h4 class="font-semibold text-gray-800 mb-4">Review Answers:</h4>';
    quiz.questions.forEach((question, index) => {
      const userAnswer = document.querySelector(`input[name="q${index}"]:checked`);
      const isCorrect = userAnswer && parseInt(userAnswer.value) === question.correct;
      
      resultsHTML += `
        <div class="border border-gray-200 rounded-lg p-4 mb-4 ${isCorrect ? 'bg-green-50' : 'bg-red-50'}">
          <h5 class="font-semibold mb-2">Question ${index + 1}: ${question.question}</h5>
          <p class="text-sm text-gray-600 mb-2">Correct Answer: ${question.options[question.correct]}</p>
          <p class="text-sm text-gray-700">${question.explanation}</p>
        </div>
      `;
    });

    document.getElementById('lesson-quiz').innerHTML = resultsHTML;
  }

  autoSaveNotes() {
    const textarea = document.getElementById('lesson-notes-textarea');
    if (textarea && this.currentLessonId) {
      this.userProgress.notes[this.currentLessonId] = textarea.value;
      localStorage.setItem('user_progress', JSON.stringify(this.userProgress));
    }
  }

  saveNotes() {
    this.autoSaveNotes();
    this.showMessage('Notes saved successfully!', 'success');
  }

  downloadResource(resourceName) {
    // Simulate download
    this.showMessage(`Downloading ${resourceName}...`, 'success');
  }

  completeCurrentLesson() {
    if (!this.currentLessonId) return;

    // Add to completed lessons
    if (!this.userProgress.completedLessons.includes(this.currentLessonId)) {
      this.userProgress.completedLessons.push(this.currentLessonId);
    }

    // Update streak
    const today = new Date().toDateString();
    const lastActivity = new Date(this.userProgress.lastActivity).toDateString();
    if (today !== lastActivity) {
      this.userProgress.streak++;
    }
    this.userProgress.lastActivity = new Date().toISOString();

    // Check for certificates
    this.checkForCertificates();

    // Save progress
    localStorage.setItem('user_progress', JSON.stringify(this.userProgress));

    // Update dashboard
    this.updateDashboard();

    // Close modal
    this.closeLessonModal();

    // Show completion message
    this.showMessage('Lesson completed! Great job!', 'success');
  }

  checkForCertificates() {
    const completedCount = this.userProgress.completedLessons.length;
    
    if (completedCount >= 7 && !this.userProgress.certificates.includes('halfway')) {
      this.userProgress.certificates.push('halfway');
    }
    
    if (completedCount >= 14 && !this.userProgress.certificates.includes('complete')) {
      this.userProgress.certificates.push('complete');
    }
  }

  updateLessonNavigation() {
    const prevBtn = document.getElementById('prev-lesson');
    const nextBtn = document.getElementById('next-lesson');
    
    prevBtn.disabled = this.currentLessonId <= 1;
    nextBtn.disabled = this.currentLessonId >= Object.keys(this.lessons).length;
  }

  showMessage(message, type = 'info') {
    const messageDiv = document.createElement('div');
    messageDiv.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
      type === 'success' ? 'bg-green-600 text-white' :
      type === 'error' ? 'bg-red-600 text-white' :
      'bg-blue-600 text-white'
    }`;
    messageDiv.textContent = message;
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
      messageDiv.remove();
    }, 3000);
  }
}

// Initialize the portal when the page loads
document.addEventListener('DOMContentLoaded', function() {
  new AdvancedDiscipleshipPortal();
});

// --- ADMIN PORTAL SUPPORT ---
const ADMIN_EMAIL = 'admin@heartsaftergod.org';
const ADMIN_PASSWORD = 'SuperSecret123!';

function isAdmin(email) {
  const user = getUserByEmail(email);
  return user && user.role === 'admin';
}

// Add admin dashboard section (hidden by default)
function renderAdminDashboard() {
  const adminSection = document.createElement('section');
  adminSection.id = 'admin-dashboard-section';
  adminSection.className = 'hidden';
  adminSection.innerHTML = `
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-2xl p-8 text-white mb-8 flex flex-col items-center">
      <h1 class="text-3xl font-bold mb-4">Admin Dashboard</h1>
      <div class="flex flex-wrap gap-6 justify-center mb-8">
        <button id="admin-users-btn" class="bg-white/20 px-6 py-3 rounded-lg font-semibold hover:bg-white/30 transition">User Management</button>
        <button id="admin-courses-btn" class="bg-white/20 px-6 py-3 rounded-lg font-semibold hover:bg-white/30 transition">Course Management</button>
        <button id="admin-broadcast-btn" class="bg-white/20 px-6 py-3 rounded-lg font-semibold hover:bg-white/30 transition">Broadcast Announcement</button>
        <button id="admin-analytics-btn" class="bg-white/20 px-6 py-3 rounded-lg font-semibold hover:bg-white/30 transition">Analytics</button>
        <button id="admin-impersonate-btn" class="bg-white/20 px-6 py-3 rounded-lg font-semibold hover:bg-white/30 transition">Impersonate User</button>
        <button id="admin-certificates-btn" class="bg-white/20 px-6 py-3 rounded-lg font-semibold hover:bg-white/30 transition">Certificate Management</button>
      </div>
      <div id="admin-panel-content" class="w-full max-w-4xl bg-white/10 rounded-xl p-6"></div>
      <button id="switch-to-user-dashboard" class="mt-8 bg-gold text-blue-900 font-semibold px-8 py-3 rounded-full shadow hover:bg-yellow-400 transition">Switch to My Discipleship Dashboard</button>
    </div>
  `;
  document.querySelector('main').appendChild(adminSection);
}

// Show/hide admin dashboard based on role
function showAdminDashboard() {
  document.getElementById('dashboard-section').classList.add('hidden');
  document.getElementById('admin-dashboard-section').classList.remove('hidden');
}
function showUserDashboard() {
  document.getElementById('admin-dashboard-section').classList.add('hidden');
  document.getElementById('dashboard-section').classList.remove('hidden');
}

// On login, check for admin
function handleLogin(email) {
  if (isAdmin(email)) {
    renderAdminDashboard();
    showAdminDashboard();
    attachAdminFeatureHandlers();
    document.getElementById('switch-to-user-dashboard').onclick = showUserDashboard;
  } else {
    showUserDashboard();
  }
}

// --- ADMIN FEATURE HANDLERS ---
function adminRenderUserManagement() {
  // Simulate user list from localStorage
  const users = JSON.parse(localStorage.getItem('discipleship_users') || '[]');
  let html = `<h2 class='text-2xl font-bold mb-4'>User Management</h2>`;
  if (users.length === 0) {
    html += `<p class='text-gray-200'>No users registered yet.</p>`;
  } else {
    html += `<table class='w-full text-left mb-4'><thead><tr><th>Name</th><th>Email</th><th>Progress</th><th>Actions</th></tr></thead><tbody>`;
    users.forEach(u => {
      html += `<tr><td>${u.name}</td><td>${u.email}</td><td>${u.progress || 0}%</td><td><button class='reset-pw-btn bg-gold text-blue-900 px-2 py-1 rounded' data-email='${u.email}'>Reset Password</button></td></tr>`;
    });
    html += `</tbody></table>`;
  }
  document.getElementById('admin-panel-content').innerHTML = html;
  // Reset password handler
  document.querySelectorAll('.reset-pw-btn').forEach(btn => {
    btn.onclick = function() {
      alert('Password reset link sent to ' + btn.dataset.email);
    };
  });
}

function adminRenderCourseManagement() {
  // Simulate course modules from localStorage
  const modules = JSON.parse(localStorage.getItem('discipleship_modules') || '[]');
  let html = `<h2 class='text-2xl font-bold mb-4'>Course Management</h2>`;
  html += `<button id='add-module-btn' class='bg-blue-500 text-white px-4 py-2 rounded mb-4'>Add Lesson/Quiz</button>`;
  if (modules.length === 0) {
    html += `<p class='text-gray-200'>No modules found.</p>`;
  } else {
    html += `<ul class='mb-4'>`;
    modules.forEach((m, i) => {
      html += `<li class='mb-2 bg-white/10 p-3 rounded flex justify-between items-center'>
        <span><b>${m.title}</b> (${m.type})</span>
        <span>
          <button class='edit-module-btn bg-gold text-blue-900 px-2 py-1 rounded mr-2' data-idx='${i}'>Edit</button>
          <button class='delete-module-btn bg-red-500 text-white px-2 py-1 rounded' data-idx='${i}'>Delete</button>
        </span>
      </li>`;
    });
    html += `</ul>`;
  }
  document.getElementById('admin-panel-content').innerHTML = html;
  document.getElementById('add-module-btn').onclick = function() {
    const title = prompt('Lesson/Quiz Title:');
    if (!title) return;
    const type = prompt('Type (lesson/quiz):', 'lesson');
    if (!type) return;
    modules.push({ title, type });
    localStorage.setItem('discipleship_modules', JSON.stringify(modules));
    adminRenderCourseManagement();
  };
  document.querySelectorAll('.edit-module-btn').forEach(btn => {
    btn.onclick = function() {
      const idx = btn.dataset.idx;
      const newTitle = prompt('Edit Title:', modules[idx].title);
      if (!newTitle) return;
      modules[idx].title = newTitle;
      localStorage.setItem('discipleship_modules', JSON.stringify(modules));
      adminRenderCourseManagement();
    };
  });
  document.querySelectorAll('.delete-module-btn').forEach(btn => {
    btn.onclick = function() {
      const idx = btn.dataset.idx;
      if (confirm('Delete this module?')) {
        modules.splice(idx, 1);
        localStorage.setItem('discipleship_modules', JSON.stringify(modules));
        adminRenderCourseManagement();
      }
    };
  });
}

function adminRenderBroadcast() {
  let html = `<h2 class='text-2xl font-bold mb-4'>Broadcast Announcement</h2>`;
  html += `<textarea id='broadcast-msg' class='w-full p-2 rounded mb-4' rows='3' placeholder='Type your announcement...'></textarea>`;
  html += `<button id='send-broadcast-btn' class='bg-gold text-blue-900 px-6 py-2 rounded font-semibold'>Send to All Users</button>`;
  document.getElementById('admin-panel-content').innerHTML = html;
  document.getElementById('send-broadcast-btn').onclick = function() {
    const msg = document.getElementById('broadcast-msg').value;
    if (!msg) return alert('Message cannot be empty.');
    alert('Announcement sent to all users!');
  };
}

function adminRenderAnalytics() {
  // Simulate analytics from localStorage
  const users = JSON.parse(localStorage.getItem('discipleship_users') || '[]');
  const total = users.length;
  const completions = users.filter(u => u.progress === 100).length;
  const avgQuiz = users.length ? Math.round(users.reduce((a, u) => a + (u.quizAvg || 0), 0) / users.length) : 0;
  let html = `<h2 class='text-2xl font-bold mb-4'>Analytics</h2>`;
  html += `<div class='grid grid-cols-2 gap-6 mb-6'>
    <div class='bg-white/10 p-4 rounded'><b>Total Users:</b> ${total}</div>
    <div class='bg-white/10 p-4 rounded'><b>Completions:</b> ${completions}</div>
    <div class='bg-white/10 p-4 rounded'><b>Avg Quiz Score:</b> ${avgQuiz}%</div>
    <div class='bg-white/10 p-4 rounded'><b>Active Today:</b> ${users.filter(u => u.activeToday).length}</div>
  </div>`;
  document.getElementById('admin-panel-content').innerHTML = html;
}

function adminRenderImpersonate() {
  const users = JSON.parse(localStorage.getItem('discipleship_users') || '[]');
  let html = `<h2 class='text-2xl font-bold mb-4'>Impersonate User</h2>`;
  if (users.length === 0) {
    html += `<p class='text-gray-200'>No users to impersonate.</p>`;
  } else {
    html += `<select id='impersonate-select' class='p-2 rounded mb-4'>
      <option value=''>Select user...</option>
      ${users.map(u => `<option value='${u.email}'>${u.name} (${u.email})</option>`).join('')}
    </select>
    <button id='impersonate-btn' class='bg-blue-500 text-white px-4 py-2 rounded'>Impersonate</button>`;
  }
  document.getElementById('admin-panel-content').innerHTML = html;
  if (users.length) {
    document.getElementById('impersonate-btn').onclick = function() {
      const email = document.getElementById('impersonate-select').value;
      if (!email) return;
      alert('Now viewing as ' + email);
      // Optionally, switch dashboard view here
    };
  }
}

function adminRenderCertificates() {
  const users = JSON.parse(localStorage.getItem('discipleship_users') || '[]');
  let html = `<h2 class='text-2xl font-bold mb-4'>Certificate Management</h2>`;
  if (users.length === 0) {
    html += `<p class='text-gray-200'>No users found.</p>`;
  } else {
    html += `<table class='w-full text-left mb-4'><thead><tr><th>Name</th><th>Email</th><th>Certificate</th><th>Actions</th></tr></thead><tbody>`;
    users.forEach(u => {
      html += `<tr><td>${u.name}</td><td>${u.email}</td><td>${u.certificate ? '✅' : '❌'}</td><td><button class='issue-cert-btn bg-gold text-blue-900 px-2 py-1 rounded mr-2' data-email='${u.email}'>Issue</button><button class='revoke-cert-btn bg-red-500 text-white px-2 py-1 rounded' data-email='${u.email}'>Revoke</button></td></tr>`;
    });
    html += `</tbody></table>`;
  }
  document.getElementById('admin-panel-content').innerHTML = html;
  document.querySelectorAll('.issue-cert-btn').forEach(btn => {
    btn.onclick = function() {
      const email = btn.dataset.email;
      users.forEach(u => { if (u.email === email) u.certificate = true; });
      localStorage.setItem('discipleship_users', JSON.stringify(users));
      adminRenderCertificates();
    };
  });
  document.querySelectorAll('.revoke-cert-btn').forEach(btn => {
    btn.onclick = function() {
      const email = btn.dataset.email;
      users.forEach(u => { if (u.email === email) u.certificate = false; });
      localStorage.setItem('discipleship_users', JSON.stringify(users));
      adminRenderCertificates();
    };
  });
}

// Attach admin feature handlers after rendering admin dashboard
function attachAdminFeatureHandlers() {
  document.getElementById('admin-users-btn').onclick = adminRenderUserManagement;
  document.getElementById('admin-courses-btn').onclick = adminRenderCourseManagement;
  document.getElementById('admin-broadcast-btn').onclick = adminRenderBroadcast;
  document.getElementById('admin-analytics-btn').onclick = adminRenderAnalytics;
  document.getElementById('admin-impersonate-btn').onclick = adminRenderImpersonate;
  document.getElementById('admin-certificates-btn').onclick = adminRenderCertificates;
}

// Update handleLogin to attach handlers
const originalHandleLogin = handleLogin;
handleLogin = function(email) {
  if (isAdmin(email)) {
    renderAdminDashboard();
    showAdminDashboard();
    attachAdminFeatureHandlers();
    document.getElementById('switch-to-user-dashboard').onclick = showUserDashboard;
  } else {
    showUserDashboard();
  }
};

// Hook into your existing login logic:
// After successful login, call handleLogin(email)
// Example:
// handleLogin(userEmail); 

// --- PROFESSIONAL ADMIN SEEDING & ROLE MANAGEMENT ---
function seedAdminUser() {
  const users = JSON.parse(localStorage.getItem('discipleship_users') || '[]');
  const adminEmail = 'admin@heartsaftergod.org';
  const adminPassword = 'SuperSecret123!';
  // In a real app, hash the password here
  if (!users.find(u => u.email === adminEmail)) {
    users.push({
      name: 'Administrator',
      email: adminEmail,
      password: adminPassword, // In production, store a hash
      role: 'admin'
    });
    localStorage.setItem('discipleship_users', JSON.stringify(users));
  }
}
seedAdminUser();

function getUserByEmail(email) {
  const users = JSON.parse(localStorage.getItem('discipleship_users') || '[]');
  return users.find(u => u.email === email);
}

// Patch login form submit handler
const loginForm = document.getElementById('login-form');
if (loginForm) {
  loginForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('email').value.trim().toLowerCase();
    const password = document.getElementById('password').value;
    const users = JSON.parse(localStorage.getItem('discipleship_users') || '[]');
    // Find user by email (case-insensitive, trimmed) and password
    const user = users.find(u => u.email.trim().toLowerCase() === email && u.password === password);
    if (!user) {
      alert('Invalid email or password.');
      return;
    }
    handleLogin(email);
    const loginSection = document.getElementById('login-section');
    if (loginSection) loginSection.classList.add('hidden');
    const dashboardSection = document.getElementById('dashboard-section');
    if (dashboardSection) dashboardSection.classList.remove('hidden');
    // Set the user's name in the dashboard
    const nameSpan = document.getElementById('user-name');
    if (nameSpan) nameSpan.innerText = user.name;
  });
}

// Prevent admin registration and force role to 'user' for all new registrations
const registerForm = document.getElementById('register-form');
if (registerForm) {
  registerForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('reg-email').value.trim().toLowerCase();
    if (isAdmin(email)) {
      alert('Admin account cannot be registered.');
      return false;
    }
    const users = JSON.parse(localStorage.getItem('discipleship_users') || '[]');
    if (users.find(u => u.email.trim().toLowerCase() === email)) {
      alert('User already exists.');
      return false;
    }
    const name = document.getElementById('reg-name').value.trim();
    const password = document.getElementById('reg-password').value;
    // In a real app, hash the password here
    users.push({ name, email, password, role: 'user' });
    localStorage.setItem('discipleship_users', JSON.stringify(users));
    alert('Registration successful! Please log in.');
    // Optionally, redirect to login or auto-login
  });
} 