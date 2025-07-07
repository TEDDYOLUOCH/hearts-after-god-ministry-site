// Admin Quiz Management System
// Real-time quiz upload and user assessment

(function() {
  let backend = null;
  let quizState = {
    currentQuizzes: [],
    isUploading: false,
    lastUpdate: null
  };

  // Initialize quiz system
  function initQuizSystem() {
    console.log('Initializing admin quiz system...');
    
    if (!window.DiscipleshipBackend) {
      console.log('Waiting for backend to load...');
      setTimeout(initQuizSystem, 100);
      return;
    }
    
    try {
      backend = window.DiscipleshipBackend;
      console.log('Backend loaded, initializing quiz system...');
      
      setupQuizEventListeners();
      loadQuizzes();
      startQuizRealTimeUpdates();
      
      console.log('Quiz system initialized successfully');
    } catch (error) {
      console.error('Error initializing quiz system:', error);
    }
  }

  // Setup quiz event listeners
  function setupQuizEventListeners() {
    try {
      console.log('Setting up quiz event listeners...');
      
      // Quiz upload form
      const quizForm = document.getElementById('quiz-upload-form');
      if (quizForm) {
        quizForm.addEventListener('submit', handleQuizUpload);
      }

      // Quiz search
      const quizSearch = document.getElementById('quiz-search');
      if (quizSearch) {
        quizSearch.addEventListener('input', handleQuizSearch);
      }

      // Quiz filter
      const quizFilter = document.getElementById('quiz-filter');
      if (quizFilter) {
        quizFilter.addEventListener('change', handleQuizFilter);
      }

      // Quiz actions
      const addQuizBtn = document.getElementById('add-quiz-btn');
      if (addQuizBtn) {
        addQuizBtn.addEventListener('click', openQuizModal);
      }

      const closeQuizBtn = document.getElementById('close-quiz-modal');
      if (closeQuizBtn) {
        closeQuizBtn.addEventListener('click', closeQuizModal);
      }

      // Question management
      const addQuestionBtn = document.getElementById('add-question-btn');
      if (addQuestionBtn) {
        addQuestionBtn.addEventListener('click', addQuestionField);
      }

      console.log('Quiz event listeners setup completed');
    } catch (error) {
      console.error('Error setting up quiz event listeners:', error);
    }
  }

  // Handle quiz upload
  function handleQuizUpload(e) {
    e.preventDefault();
    
    if (quizState.isUploading) {
      showNotification('Upload already in progress...', 'warning');
      return;
    }

    const formData = new FormData(e.target);
    const title = formData.get('quiz-title');
    const description = formData.get('quiz-description');
    const day = formData.get('quiz-day');
    const category = formData.get('quiz-category');
    const difficulty = formData.get('quiz-difficulty');
    const timeLimit = formData.get('quiz-time-limit');
    const targetUsers = formData.get('quiz-target-users');
    const passingScore = formData.get('quiz-passing-score');

    if (!title || !description || !day) {
      showNotification('Please fill in all required fields including Day', 'error');
      return;
    }

    // Collect questions from the form
    const questions = collectQuestionsFromForm();
    if (questions.length === 0) {
      showNotification('Please add at least one question', 'error');
      return;
    }

    quizState.isUploading = true;
    updateUploadStatus('Uploading quiz...');

    // Create quiz object
    const quizData = {
      id: 'quiz_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
      title: title,
      description: description,
      day: day,
      category: category,
      difficulty: difficulty,
      timeLimit: parseInt(timeLimit) || 0,
      targetUsers: targetUsers === 'all' ? 'all' : targetUsers.split(',').map(u => u.trim()),
      passingScore: parseInt(passingScore) || 70,
      questions: questions,
      uploadedBy: getCurrentAdminUser(),
      uploadedDate: new Date().toISOString(),
      status: 'active',
      attempts: 0,
      averageScore: 0,
      completions: 0
    };

    saveQuiz(quizData);
  }

  // Collect questions from form
  function collectQuestionsFromForm() {
    const questions = [];
    const questionContainers = document.querySelectorAll('.question-container');
    
    questionContainers.forEach((container, index) => {
      const questionText = container.querySelector('.question-text').value;
      const questionType = container.querySelector('.question-type').value;
      
      if (!questionText.trim()) return;

      const question = {
        id: `q${index + 1}`,
        text: questionText,
        type: questionType,
        points: 1
      };

      if (questionType === 'multiple-choice') {
        const options = [];
        const optionInputs = container.querySelectorAll('.option-input');
        optionInputs.forEach((input, optIndex) => {
          if (input.value.trim()) {
            options.push({
              id: `opt${optIndex + 1}`,
              text: input.value.trim(),
              isCorrect: container.querySelector(`input[name="correct-${index}"]:checked`)?.value === `opt${optIndex + 1}`
            });
          }
        });
        question.options = options;
      } else if (questionType === 'true-false') {
        const correctAnswer = container.querySelector(`input[name="correct-${index}"]:checked`)?.value;
        question.correctAnswer = correctAnswer === 'true';
      } else if (questionType === 'short-answer') {
        const correctAnswer = container.querySelector('.correct-answer').value;
        question.correctAnswer = correctAnswer.trim();
      }

      questions.push(question);
    });

    return questions;
  }

  // Save quiz to backend
  function saveQuiz(quizData) {
    try {
      if (backend && backend.createQuiz) {
        backend.createQuiz(quizData);
      } else {
        // Fallback to localStorage
        const quizzes = JSON.parse(localStorage.getItem('admin_quizzes') || '[]');
        quizzes.unshift(quizData);
        localStorage.setItem('admin_quizzes', JSON.stringify(quizzes));
      }

      // Broadcast to users in real-time
      broadcastQuizToUsers(quizData);

      quizState.isUploading = false;
      updateUploadStatus('Quiz uploaded successfully!');
      showNotification('Quiz uploaded successfully!', 'success');

      // Reset form
      const form = document.getElementById('quiz-upload-form');
      if (form) {
        form.reset();
        clearQuestionFields();
      }

      // Reload quiz list
      loadQuizzes();

    } catch (error) {
      console.error('Error saving quiz:', error);
      quizState.isUploading = false;
      updateUploadStatus('Upload failed');
      showNotification('Error uploading quiz', 'error');
    }
  }

  // Broadcast quiz to users
  function broadcastQuizToUsers(quizData) {
    try {
      if (backend && backend.broadcastToUsers) {
        backend.broadcastToUsers({
          type: 'new_quiz',
          data: quizData
        });
      }

      // Also broadcast to specific users if targeted
      if (quizData.targetUsers && quizData.targetUsers !== 'all') {
        quizData.targetUsers.forEach(userId => {
          if (backend && backend.sendUserNotification) {
            backend.sendUserNotification(userId, {
              type: 'new_quiz',
              title: 'New Quiz Available',
              message: `New quiz uploaded: ${quizData.title}`,
              data: quizData
            });
          }
        });
      }
    } catch (error) {
      console.error('Error broadcasting quiz:', error);
    }
  }

  // Load quizzes
  function loadQuizzes() {
    try {
      let quizzes = [];
      
      if (backend && backend.getAllQuizzes) {
        quizzes = backend.getAllQuizzes();
      } else {
        // Fallback to localStorage
        quizzes = JSON.parse(localStorage.getItem('admin_quizzes') || '[]');
      }

      quizState.currentQuizzes = quizzes;
      renderQuizzes(quizzes);
      updateQuizStats(quizzes);
      
    } catch (error) {
      console.error('Error loading quizzes:', error);
    }
  }

  // Render quizzes
  function renderQuizzes(quizzes) {
    const quizzesContainer = document.getElementById('quiz-list');
    if (!quizzesContainer) return;

    if (quizzes.length === 0) {
      quizzesContainer.innerHTML = `
        <div class="text-center py-8">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="text-gray-500 text-lg">No quizzes uploaded yet</p>
          <p class="text-gray-400">Upload your first quiz to get started</p>
        </div>
      `;
      return;
    }

    quizzesContainer.innerHTML = quizzes.map(quiz => `
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
            <button onclick="previewQuiz('${quiz.id}')" class="p-2 text-[#7C3AED] hover:bg-[#7C3AED]/10 rounded-lg transition-colors" title="Preview">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
            <button onclick="editQuiz('${quiz.id}')" class="p-2 text-[#FDBA17] hover:bg-[#FDBA17]/10 rounded-lg transition-colors" title="Edit">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </button>
            <button onclick="deleteQuiz('${quiz.id}')" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Delete">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>
        <div class="flex items-center justify-between text-sm text-gray-500">
          <div class="flex items-center gap-4">
            <span>👥 ${quiz.attempts || 0} attempts</span>
            <span>📈 ${quiz.averageScore || 0}% avg</span>
            <span>✅ ${quiz.completions || 0} completions</span>
            <span>👤 ${quiz.targetUsers === 'all' ? 'All Users' : quiz.targetUsers.length + ' users'}</span>
          </div>
          <span class="text-[#7C3AED] font-medium">${quiz.uploadedBy?.name || 'Admin'}</span>
        </div>
      </div>
    `).join('');
  }

  // Update quiz statistics
  function updateQuizStats(quizzes) {
    const totalQuizzes = quizzes.length;
    const totalAttempts = quizzes.reduce((sum, quiz) => sum + (quiz.attempts || 0), 0);
    const totalCompletions = quizzes.reduce((sum, quiz) => sum + (quiz.completions || 0), 0);
    const averageScore = quizzes.length > 0 ? 
      Math.round(quizzes.reduce((sum, quiz) => sum + (quiz.averageScore || 0), 0) / quizzes.length) : 0;

    // Update stats elements
    const statsElements = {
      'total-quizzes': totalQuizzes,
      'total-attempts': totalAttempts,
      'total-completions': totalCompletions,
      'average-score': averageScore
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
    const filteredQuizzes = quizState.currentQuizzes.filter(quiz =>
      quiz.title.toLowerCase().includes(searchTerm) ||
      quiz.description.toLowerCase().includes(searchTerm) ||
      quiz.category.toLowerCase().includes(searchTerm)
    );
    renderQuizzes(filteredQuizzes);
  }

  // Handle quiz filter
  function handleQuizFilter(e) {
    const filter = e.target.value;
    let filteredQuizzes = quizState.currentQuizzes;

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
    }

    renderQuizzes(filteredQuizzes);
  }

  // Open quiz modal
  function openQuizModal() {
    const modal = document.getElementById('quiz-modal');
    if (modal) {
      modal.classList.remove('hidden');
      addQuestionField(); // Add first question field
    }
  }

  // Close quiz modal
  function closeQuizModal() {
    const modal = document.getElementById('quiz-modal');
    if (modal) {
      modal.classList.add('hidden');
      clearQuestionFields();
    }
  }

  // Add question field
  function addQuestionField() {
    const questionsContainer = document.getElementById('questions-container');
    if (!questionsContainer) return;

    const questionIndex = questionsContainer.children.length;
    const questionHtml = `
      <div class="question-container bg-gray-50 rounded-lg p-4 mb-4">
        <div class="flex items-center justify-between mb-3">
          <h4 class="font-semibold text-gray-700">Question ${questionIndex + 1}</h4>
          <button type="button" onclick="removeQuestion(this)" class="text-red-600 hover:text-red-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Question Text *</label>
            <textarea class="question-text w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] outline-none" rows="2" required></textarea>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Question Type</label>
            <select class="question-type w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] outline-none" onchange="toggleQuestionOptions(this)">
              <option value="multiple-choice">Multiple Choice</option>
              <option value="true-false">True/False</option>
              <option value="short-answer">Short Answer</option>
            </select>
          </div>
          
          <div class="question-options">
            <!-- Options will be populated based on question type -->
          </div>
        </div>
      </div>
    `;

    questionsContainer.insertAdjacentHTML('beforeend', questionHtml);
    toggleQuestionOptions(questionsContainer.lastElementChild.querySelector('.question-type'));
  }

  // Remove question field
  function removeQuestion(button) {
    const questionContainer = button.closest('.question-container');
    if (questionContainer) {
      questionContainer.remove();
      updateQuestionNumbers();
    }
  }

  // Update question numbers
  function updateQuestionNumbers() {
    const questions = document.querySelectorAll('.question-container');
    questions.forEach((question, index) => {
      const title = question.querySelector('h4');
      if (title) {
        title.textContent = `Question ${index + 1}`;
      }
    });
  }

  // Toggle question options based on type
  function toggleQuestionOptions(selectElement) {
    const questionContainer = selectElement.closest('.question-container');
    const optionsContainer = questionContainer.querySelector('.question-options');
    const questionType = selectElement.value;

    if (questionType === 'multiple-choice') {
      optionsContainer.innerHTML = `
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Options</label>
          <div class="space-y-2">
            <div class="flex items-center gap-2">
              <input type="radio" name="correct-${questionContainer.dataset.index || 0}" value="opt1" class="text-[#7C3AED]">
              <input type="text" class="option-input flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] outline-none" placeholder="Option 1" required>
            </div>
            <div class="flex items-center gap-2">
              <input type="radio" name="correct-${questionContainer.dataset.index || 0}" value="opt2" class="text-[#7C3AED]">
              <input type="text" class="option-input flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] outline-none" placeholder="Option 2" required>
            </div>
            <div class="flex items-center gap-2">
              <input type="radio" name="correct-${questionContainer.dataset.index || 0}" value="opt3" class="text-[#7C3AED]">
              <input type="text" class="option-input flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] outline-none" placeholder="Option 3" required>
            </div>
            <div class="flex items-center gap-2">
              <input type="radio" name="correct-${questionContainer.dataset.index || 0}" value="opt4" class="text-[#7C3AED]">
              <input type="text" class="option-input flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] outline-none" placeholder="Option 4" required>
            </div>
          </div>
        </div>
      `;
    } else if (questionType === 'true-false') {
      optionsContainer.innerHTML = `
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Correct Answer</label>
          <div class="space-y-2">
            <label class="flex items-center gap-2">
              <input type="radio" name="correct-${questionContainer.dataset.index || 0}" value="true" class="text-[#7C3AED]">
              <span>True</span>
            </label>
            <label class="flex items-center gap-2">
              <input type="radio" name="correct-${questionContainer.dataset.index || 0}" value="false" class="text-[#7C3AED]">
              <span>False</span>
            </label>
          </div>
        </div>
      `;
    } else if (questionType === 'short-answer') {
      optionsContainer.innerHTML = `
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Correct Answer</label>
          <input type="text" class="correct-answer w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] outline-none" placeholder="Enter correct answer" required>
        </div>
      `;
    }
  }

  // Clear question fields
  function clearQuestionFields() {
    const questionsContainer = document.getElementById('questions-container');
    if (questionsContainer) {
      questionsContainer.innerHTML = '';
    }
  }

  // Preview quiz
  function previewQuiz(quizId) {
    const quiz = quizState.currentQuizzes.find(q => q.id === quizId);
    if (!quiz) return;

    // Create modal to preview quiz
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
      <div class="bg-white rounded-2xl p-8 max-w-4xl mx-4 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-bold text-[#2046B3]">${quiz.title}</h3>
          <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        
        <div class="space-y-4">
          <div class="flex items-center gap-4 text-sm text-gray-600">
            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">${quiz.category}</span>
            <span class="px-2 py-1 ${getDifficultyColor(quiz.difficulty)} rounded-full">${quiz.difficulty}</span>
            <span>⏱️ ${quiz.timeLimit || 'No limit'}</span>
            <span>📊 ${quiz.passingScore}% to pass</span>
          </div>
          
          <p class="text-gray-700">${quiz.description}</p>
          
          <div class="space-y-4">
            <h4 class="font-semibold text-gray-900">Questions (${quiz.questions?.length || 0})</h4>
            ${quiz.questions?.map((question, index) => `
              <div class="bg-gray-50 rounded-lg p-4">
                <p class="font-medium mb-2">${index + 1}. ${question.text}</p>
                ${question.type === 'multiple-choice' ? `
                  <div class="space-y-1 ml-4">
                    ${question.options?.map(option => `
                      <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full border-2 border-gray-300 ${option.isCorrect ? 'bg-green-500 border-green-500' : ''}"></span>
                        <span class="${option.isCorrect ? 'font-medium text-green-700' : ''}">${option.text}</span>
                      </div>
                    `).join('') || ''}
                  </div>
                ` : question.type === 'true-false' ? `
                  <div class="ml-4">
                    <span class="font-medium text-green-700">Correct Answer: ${question.correctAnswer ? 'True' : 'False'}</span>
                  </div>
                ` : `
                  <div class="ml-4">
                    <span class="font-medium text-green-700">Correct Answer: ${question.correctAnswer}</span>
                  </div>
                `}
              </div>
            `).join('') || ''}
          </div>
        </div>
        
        <div class="flex gap-4 mt-8">
          <button onclick="this.closest('.fixed').remove()" class="flex-1 border border-gray-300 text-gray-700 py-3 rounded-lg font-bold hover:bg-gray-50 transition-all">
            Close
          </button>
        </div>
      </div>
    `;
    
    document.body.appendChild(modal);
  }

  // Edit quiz
  function editQuiz(quizId) {
    const quiz = quizState.currentQuizzes.find(q => q.id === quizId);
    if (!quiz) return;

    // Populate edit form
    const form = document.getElementById('quiz-upload-form');
    if (form) {
      form.elements['quiz-title'].value = quiz.title;
      form.elements['quiz-description'].value = quiz.description;
      form.elements['quiz-category'].value = quiz.category;
      form.elements['quiz-difficulty'].value = quiz.difficulty;
      form.elements['quiz-time-limit'].value = quiz.timeLimit || '';
      form.elements['quiz-target-users'].value = quiz.targetUsers === 'all' ? 'all' : quiz.targetUsers.join(', ');
      form.elements['quiz-passing-score'].value = quiz.passingScore || 70;
      
      // Add edit mode indicator
      form.dataset.editMode = 'true';
      form.dataset.editId = quizId;
    }

    openQuizModal();
  }

  // Delete quiz
  function deleteQuiz(quizId) {
    if (!confirm('Are you sure you want to delete this quiz? This action cannot be undone.')) {
      return;
    }

    quizState.currentQuizzes = quizState.currentQuizzes.filter(q => q.id !== quizId);
    saveQuizzesToStorage();
    renderQuizzes(quizState.currentQuizzes);
    showNotification('Quiz deleted successfully', 'success');
  }

  // Save quizzes to storage
  function saveQuizzesToStorage() {
    if (backend && backend.saveQuizzes) {
      backend.saveQuizzes(quizState.currentQuizzes);
    } else {
      localStorage.setItem('admin_quizzes', JSON.stringify(quizState.currentQuizzes));
    }
  }

  // Update upload status
  function updateUploadStatus(message) {
    const statusElement = document.getElementById('upload-status');
    if (statusElement) {
      statusElement.textContent = message;
      statusElement.className = message.includes('success') ? 'text-green-600' : 'text-blue-600';
    }
  }

  // Get current admin user
  function getCurrentAdminUser() {
    const loggedInUser = localStorage.getItem('discipleship_logged_in_user');
    if (loggedInUser) {
      return JSON.parse(loggedInUser);
    }
    return { name: 'Admin', email: 'admin@demo.com' };
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

  // Start real-time updates
  function startQuizRealTimeUpdates() {
    setInterval(() => {
      loadQuizzes();
    }, 10000); // Update every 10 seconds
  }

  // Show notification
  function showNotification(message, type = 'info') {
    if (window.AdminComplete && window.AdminComplete.showNotification) {
      window.AdminComplete.showNotification(message, type);
    } else {
      console.log(`${type.toUpperCase()}: ${message}`);
    }
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQuizSystem);
  } else {
    initQuizSystem();
  }

  // Export functions for global use
  window.AdminQuiz = {
    openQuizModal,
    closeQuizModal,
    previewQuiz,
    editQuiz,
    deleteQuiz,
    addQuestionField,
    removeQuestion,
    toggleQuestionOptions,
    loadQuizzes
  };

  // Global functions for HTML onclick handlers
  window.openQuizModal = function() {
    if (window.AdminQuiz) {
      window.AdminQuiz.openQuizModal();
    }
  };

  window.closeQuizModal = function() {
    if (window.AdminQuiz) {
      window.AdminQuiz.closeQuizModal();
    }
  };

  window.previewQuiz = function(quizId) {
    if (window.AdminQuiz) {
      window.AdminQuiz.previewQuiz(quizId);
    }
  };

  window.editQuiz = function(quizId) {
    if (window.AdminQuiz) {
      window.AdminQuiz.editQuiz(quizId);
    }
  };

  window.deleteQuiz = function(quizId) {
    if (window.AdminQuiz) {
      window.AdminQuiz.deleteQuiz(quizId);
    }
  };

  window.addQuestionField = function() {
    if (window.AdminQuiz) {
      window.AdminQuiz.addQuestionField();
    }
  };

  window.removeQuestion = function(button) {
    if (window.AdminQuiz) {
      window.AdminQuiz.removeQuestion(button);
    }
  };

  window.toggleQuestionOptions = function(selectElement) {
    if (window.AdminQuiz) {
      window.AdminQuiz.toggleQuestionOptions(selectElement);
    }
  };

})(); 