// User Quiz Interface
// Real-time quiz taking for users

(function() {
  let backend = null;
  let currentUser = null;
  let quizState = {
    userQuizzes: [],
    currentQuiz: null,
    quizTimer: null,
    lastUpdate: null
  };

  // Initialize user quiz
  function initUserQuiz() {
    console.log('Initializing user quiz interface...');
    
    if (!window.DiscipleshipBackend) {
      console.log('Waiting for backend to load...');
      setTimeout(initUserQuiz, 100);
      return;
    }
    
    try {
      backend = window.DiscipleshipBackend;
      console.log('Backend loaded, initializing user quiz...');
      
      setupQuizEventListeners();
      loadUserQuizzes();
      startQuizRealTimeUpdates();
      
      console.log('User quiz interface initialized successfully');
    } catch (error) {
      console.error('Error initializing user quiz interface:', error);
    }
  }

  // Setup quiz event listeners
  function setupQuizEventListeners() {
    try {
      console.log('Setting up user quiz event listeners...');
      
      // Quiz search
      const quizSearch = document.getElementById('user-quiz-search');
      if (quizSearch) {
        quizSearch.addEventListener('input', handleQuizSearch);
      }

      // Quiz filter
      const quizFilter = document.getElementById('user-quiz-filter');
      if (quizFilter) {
        quizFilter.addEventListener('change', handleQuizFilter);
      }

      console.log('User quiz event listeners setup completed');
    } catch (error) {
      console.error('Error setting up user quiz event listeners:', error);
    }
  }

  // Load user quizzes
  function loadUserQuizzes() {
    if (!currentUser || !backend) return;

    try {
      const quizzes = backend.getUserQuizzes(currentUser.id);
      quizState.userQuizzes = quizzes;
      renderUserQuizzes(quizzes);
      updateQuizStats(quizzes);
      
      console.log('User quizzes loaded:', quizzes.length);
    } catch (error) {
      console.error('Error loading user quizzes:', error);
    }
  }

  // Render user quizzes
  function renderUserQuizzes(quizzes) {
    const quizzesContainer = document.getElementById('user-quiz-list');
    if (!quizzesContainer) return;

    if (quizzes.length === 0) {
      quizzesContainer.innerHTML = `
        <div class="text-center py-8">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="text-gray-500 text-lg">No quizzes available yet</p>
          <p class="text-gray-400">Check back later for new quizzes from your admin</p>
        </div>
      `;
      return;
    }

    quizzesContainer.innerHTML = quizzes.map(quiz => {
      const userAttempts = getUserQuizAttempts(quiz.id);
      const bestScore = userAttempts.length > 0 ? Math.max(...userAttempts.map(a => a.score)) : 0;
      const lastAttempt = userAttempts.length > 0 ? userAttempts[0] : null;
      
      return `
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-lg font-bold text-[#2046B3]">${quiz.title}</h3>
                <span class="px-2 py-1 ${getDifficultyColor(quiz.difficulty)} rounded-full text-xs font-medium">${quiz.difficulty}</span>
              </div>
              <p class="text-gray-600 text-sm mb-3">${quiz.description}</p>
              <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full font-bold">${quiz.day || 'No Day'}</span>
                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">${quiz.category}</span>
                <span>⏱️ ${quiz.timeLimit || 'No limit'}</span>
                <span>📊 ${quiz.passingScore}% to pass</span>
                <span>📝 ${quiz.questions?.length || 0} questions</span>
              </div>
            </div>
            <div class="flex items-center gap-2">
              ${lastAttempt ? `
                <div class="text-right">
                  <div class="text-sm text-gray-500">Best Score</div>
                  <div class="text-lg font-bold ${bestScore >= quiz.passingScore ? 'text-green-600' : 'text-red-600'}">${bestScore}%</div>
                </div>
              ` : ''}
              <button onclick="startQuiz('${quiz.id}')" class="px-4 py-2 bg-gradient-to-r from-[#7C3AED] to-[#2046B3] text-white rounded-lg hover:shadow-lg transition-all font-semibold">
                ${lastAttempt ? 'Retake' : 'Start Quiz'}
              </button>
            </div>
          </div>
          <div class="flex items-center justify-between text-sm text-gray-500">
            <div class="flex items-center gap-4">
              <span>👥 ${quiz.attempts || 0} total attempts</span>
              <span>📈 ${quiz.averageScore || 0}% avg</span>
              <span>✅ ${quiz.completions || 0} completions</span>
            </div>
            <span class="text-[#7C3AED] font-medium">${quiz.uploadedBy?.name || 'Admin'}</span>
          </div>
        </div>
      `;
    }).join('');
  }

  // Update quiz statistics
  function updateQuizStats(quizzes) {
    const totalQuizzes = quizzes.length;
    const completedQuizzes = quizzes.filter(quiz => {
      const attempts = getUserQuizAttempts(quiz.id);
      return attempts.some(attempt => attempt.passed);
    }).length;

    // Update stats elements
    const statsElements = {
      'user-total-quizzes': totalQuizzes,
      'user-completed-quizzes': completedQuizzes
    };

    Object.entries(statsElements).forEach(([id, value]) => {
      const element = document.getElementById(id);
      if (element) {
        element.textContent = value;
      }
    });
  }

  // Handle quiz search
  function handleQuizSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const filteredQuizzes = quizState.userQuizzes.filter(quiz =>
      quiz.title.toLowerCase().includes(searchTerm) ||
      quiz.description.toLowerCase().includes(searchTerm) ||
      quiz.category.toLowerCase().includes(searchTerm)
    );
    renderUserQuizzes(filteredQuizzes);
  }

  // Handle quiz filter
  function handleQuizFilter(e) {
    const filter = e.target.value;
    let filteredQuizzes = quizState.userQuizzes;

    if (filter === 'recent') {
      filteredQuizzes = filteredQuizzes.filter(quiz => {
        const quizDate = new Date(quiz.uploadedDate);
        const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
        return quizDate > weekAgo;
      });
    } else if (filter === 'difficulty-hard') {
      filteredQuizzes = filteredQuizzes.filter(quiz => quiz.difficulty === 'hard');
    } else if (filter === 'category-bible-study') {
      filteredQuizzes = filteredQuizzes.filter(quiz => quiz.category === 'bible-study');
    } else if (filter === 'not-attempted') {
      filteredQuizzes = filteredQuizzes.filter(quiz => {
        const attempts = getUserQuizAttempts(quiz.id);
        return attempts.length === 0;
      });
    } else if (filter.startsWith('day-')) {
      filteredQuizzes = filteredQuizzes.filter(quiz => quiz.day === filter);
    }

    renderUserQuizzes(filteredQuizzes);
  }

  // Start quiz
  function startQuiz(quizId) {
    const quiz = quizState.userQuizzes.find(q => q.id === quizId);
    if (!quiz) return;

    quizState.currentQuiz = quiz;
    showQuizModal(quiz);
  }

  // Show quiz modal
  function showQuizModal(quiz) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
      <div class="bg-white rounded-2xl p-8 max-w-4xl mx-4 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-bold text-[#2046B3]">${quiz.title}</h3>
          <div class="flex items-center gap-4">
            ${quiz.timeLimit ? `
              <div class="text-right">
                <div class="text-sm text-gray-500">Time Remaining</div>
                <div class="text-lg font-bold text-[#7C3AED]" id="quiz-timer">${quiz.timeLimit}:00</div>
              </div>
            ` : ''}
            <button onclick="closeQuizModal()" class="text-gray-500 hover:text-gray-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
        
        <div class="space-y-4 mb-6">
          <div class="flex items-center gap-4 text-sm text-gray-600">
            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">${quiz.category}</span>
            <span class="px-2 py-1 ${getDifficultyColor(quiz.difficulty)} rounded-full">${quiz.difficulty}</span>
            <span>📊 ${quiz.passingScore}% to pass</span>
            <span>📝 ${quiz.questions?.length || 0} questions</span>
          </div>
          
          <p class="text-gray-700">${quiz.description}</p>
        </div>
        
        <form id="quiz-form" class="space-y-6">
          ${quiz.questions?.map((question, index) => `
            <div class="bg-gray-50 rounded-lg p-6">
              <h4 class="font-semibold text-gray-900 mb-4">Question ${index + 1}: ${question.text}</h4>
              
              ${question.type === 'multiple-choice' ? `
                <div class="space-y-3">
                  ${question.options?.map(option => `
                    <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 hover:border-[#7C3AED] cursor-pointer transition-colors">
                      <input type="radio" name="q${question.id}" value="${option.id}" class="text-[#7C3AED] focus:ring-[#7C3AED]">
                      <span class="flex-1">${option.text}</span>
                    </label>
                  `).join('') || ''}
                </div>
              ` : question.type === 'true-false' ? `
                <div class="space-y-3">
                  <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 hover:border-[#7C3AED] cursor-pointer transition-colors">
                    <input type="radio" name="q${question.id}" value="true" class="text-[#7C3AED] focus:ring-[#7C3AED]">
                    <span class="flex-1">True</span>
                  </label>
                  <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 hover:border-[#7C3AED] cursor-pointer transition-colors">
                    <input type="radio" name="q${question.id}" value="false" class="text-[#7C3AED] focus:ring-[#7C3AED]">
                    <span class="flex-1">False</span>
                  </label>
                </div>
              ` : `
                <div>
                  <input type="text" name="q${question.id}" placeholder="Enter your answer..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] outline-none">
                </div>
              `}
            </div>
          `).join('') || ''}
          
          <div class="flex gap-4">
            <button type="submit" class="flex-1 bg-gradient-to-r from-[#7C3AED] to-[#2046B3] text-white py-3 rounded-lg font-bold hover:shadow-lg transition-all">
              Submit Quiz
            </button>
            <button type="button" onclick="closeQuizModal()" class="flex-1 border border-gray-300 text-gray-700 py-3 rounded-lg font-bold hover:bg-gray-50 transition-all">
              Cancel
            </button>
          </div>
        </form>
      </div>
    `;
    
    document.body.appendChild(modal);

    // Start timer if time limit exists
    if (quiz.timeLimit) {
      startQuizTimer(quiz.timeLimit);
    }

    // Handle form submission
    const form = modal.querySelector('#quiz-form');
    form.addEventListener('submit', handleQuizSubmission);
  }

  // Start quiz timer
  function startQuizTimer(minutes) {
    let timeLeft = minutes * 60; // Convert to seconds
    
    quizState.quizTimer = setInterval(() => {
      timeLeft--;
      const minutesLeft = Math.floor(timeLeft / 60);
      const secondsLeft = timeLeft % 60;
      
      const timerElement = document.getElementById('quiz-timer');
      if (timerElement) {
        timerElement.textContent = `${minutesLeft}:${secondsLeft.toString().padStart(2, '0')}`;
      }
      
      if (timeLeft <= 0) {
        clearInterval(quizState.quizTimer);
        handleQuizSubmission(new Event('submit')); // Auto-submit
      }
    }, 1000);
  }

  // Handle quiz submission
  function handleQuizSubmission(e) {
    e.preventDefault();
    
    if (quizState.quizTimer) {
      clearInterval(quizState.quizTimer);
    }

    const form = e.target;
    const answers = {};
    
    // Collect answers
    quizState.currentQuiz.questions.forEach(question => {
      const answer = form.elements[`q${question.id}`];
      if (answer) {
        if (answer.type === 'radio') {
          answers[question.id] = answer.checked ? answer.value : null;
        } else {
          answers[question.id] = answer.value;
        }
      }
    });

    // Submit to backend
    if (backend && backend.submitQuizAttempt) {
      const result = backend.submitQuizAttempt(quizState.currentQuiz.id, currentUser.id, answers);
      
      if (result.success) {
        showQuizResults(result);
      } else {
        showNotification('Error submitting quiz: ' + result.message, 'error');
      }
    } else {
      showNotification('Backend not available', 'error');
    }
  }

  // Show quiz results
  function showQuizResults(result) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
      <div class="bg-white rounded-2xl p-8 max-w-2xl mx-4 shadow-2xl">
        <div class="text-center">
          <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center ${result.passed ? 'bg-green-100' : 'bg-red-100'}">
            <svg class="w-10 h-10 ${result.passed ? 'text-green-600' : 'text-red-600'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${result.passed ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"/>
            </svg>
          </div>
          
          <h3 class="text-2xl font-bold text-gray-900 mb-2">
            ${result.passed ? 'Congratulations!' : 'Keep Studying!'}
          </h3>
          
          <p class="text-gray-600 mb-6">${result.message}</p>
          
          <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <div class="grid grid-cols-2 gap-4 text-center">
              <div>
                <div class="text-2xl font-bold text-[#7C3AED]">${result.score}%</div>
                <div class="text-sm text-gray-500">Your Score</div>
              </div>
              <div>
                <div class="text-2xl font-bold text-gray-900">${result.correctAnswers}/${result.totalQuestions}</div>
                <div class="text-sm text-gray-500">Correct Answers</div>
              </div>
            </div>
          </div>
          
          <div class="flex gap-4">
            <button onclick="closeQuizModal()" class="flex-1 bg-gradient-to-r from-[#7C3AED] to-[#2046B3] text-white py-3 rounded-lg font-bold hover:shadow-lg transition-all">
              Close
            </button>
          </div>
        </div>
      </div>
    `;
    
    document.body.appendChild(modal);
    
    // Reload quizzes to update stats
    loadUserQuizzes();
  }

  // Close quiz modal
  function closeQuizModal() {
    if (quizState.quizTimer) {
      clearInterval(quizState.quizTimer);
    }
    
    const modal = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
    if (modal) {
      modal.remove();
    }
    
    quizState.currentQuiz = null;
  }

  // Get user quiz attempts
  function getUserQuizAttempts(quizId) {
    if (!currentUser || !backend) return [];
    
    try {
      return backend.getUserQuizAttempts(currentUser.id, quizId);
    } catch (error) {
      console.error('Error getting user quiz attempts:', error);
      return [];
    }
  }

  // Start real-time updates
  function startQuizRealTimeUpdates() {
    // Update every 10 seconds
    setInterval(() => {
      if (currentUser && backend) {
        loadUserQuizzes();
      }
    }, 10000);

    // Listen for new quiz notifications
    if (backend) {
      backend.broadcastToUsers({
        type: 'quiz_update',
        data: { action: 'check_for_updates' }
      });
    }
  }

  // Get difficulty color
  function getDifficultyColor(difficulty) {
    switch (difficulty) {
      case 'easy': return 'bg-green-100 text-green-800';
      case 'medium': return 'bg-yellow-100 text-yellow-800';
      case 'hard': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  }

  // Show notification
  function showNotification(message, type = 'info') {
    if (window.UserSimple && window.UserSimple.showNotification) {
      window.UserSimple.showNotification(message, type);
    } else {
      console.log(`${type.toUpperCase()}: ${message}`);
    }
  }

  // Set current user
  function setCurrentUser(user) {
    currentUser = user;
    loadUserQuizzes();
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initUserQuiz);
  } else {
    initUserQuiz();
  }

  // Export functions for global use
  window.UserQuiz = {
    setCurrentUser,
    loadUserQuizzes,
    startQuiz,
    closeQuizModal
  };

  // Global functions for HTML onclick handlers
  window.startQuiz = function(quizId) {
    if (window.UserQuiz) {
      window.UserQuiz.startQuiz(quizId);
    }
  };

  window.closeQuizModal = function() {
    if (window.UserQuiz) {
      window.UserQuiz.closeQuizModal();
    }
  };

})(); 