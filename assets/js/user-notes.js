// User Notes Interface
// Real-time notes viewing for users

(function() {
  let backend = null;
  let currentUser = null;
  let notesState = {
    userNotes: [],
    lastUpdate: null
  };

  // Initialize user notes
  function initUserNotes() {
    console.log('Initializing user notes interface...');
    
    if (!window.DiscipleshipBackend) {
      console.log('Waiting for backend to load...');
      setTimeout(initUserNotes, 100);
      return;
    }
    
    try {
      backend = window.DiscipleshipBackend;
      console.log('Backend loaded, initializing user notes...');
      
      setupNotesEventListeners();
      loadUserNotes();
      startNotesRealTimeUpdates();
      
      console.log('User notes interface initialized successfully');
    } catch (error) {
      console.error('Error initializing user notes interface:', error);
    }
  }

  // Setup notes event listeners
  function setupNotesEventListeners() {
    try {
      console.log('Setting up user notes event listeners...');
      
      // Notes search
      const notesSearch = document.getElementById('user-notes-search');
      if (notesSearch) {
        notesSearch.addEventListener('input', handleNotesSearch);
      }

      // Notes filter
      const notesFilter = document.getElementById('user-notes-filter');
      if (notesFilter) {
        notesFilter.addEventListener('change', handleNotesFilter);
      }

      console.log('User notes event listeners setup completed');
    } catch (error) {
      console.error('Error setting up user notes event listeners:', error);
    }
  }

  // Load user notes
  function loadUserNotes() {
    if (!currentUser || !backend) return;

    try {
      const notes = backend.getUserNotes(currentUser.id);
      notesState.userNotes = notes;
      renderUserNotes(notes);
      updateNotesStats(notes);
      
      console.log('User notes loaded:', notes.length);
    } catch (error) {
      console.error('Error loading user notes:', error);
    }
  }

  // Render user notes
  function renderUserNotes(notes) {
    const notesContainer = document.getElementById('user-notes-list');
    if (!notesContainer) return;

    if (notes.length === 0) {
      notesContainer.innerHTML = `
        <div class="text-center py-8">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="text-gray-500 text-lg">No notes available yet</p>
          <p class="text-gray-400">Check back later for new notes from your admin</p>
        </div>
      `;
      return;
    }

    notesContainer.innerHTML = notes.map(note => `
      <div class="bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <h3 class="text-lg font-bold text-[#2046B3]">${note.title}</h3>
              <span class="px-2 py-1 ${getPriorityColor(note.priority)} rounded-full text-xs font-medium">${note.priority}</span>
            </div>
            <p class="text-gray-600 text-sm mb-3">${note.description}</p>
            <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
              <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full font-bold">${note.day || 'No Day'}</span>
              <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">${note.category}</span>
              <span>📅 ${new Date(note.uploadedDate).toLocaleDateString()}</span>
              <span>👤 ${note.uploadedBy?.name || 'Admin'}</span>
            </div>
          </div>
          <div class="flex items-center gap-2">
            ${note.fileName ? `
              <button onclick="downloadUserNotes('${note.id}')" class="p-2 text-[#7C3AED] hover:bg-[#7C3AED]/10 rounded-lg transition-colors" title="Download">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </button>
            ` : ''}
            <button onclick="viewUserNotes('${note.id}')" class="p-2 text-[#FDBA17] hover:bg-[#FDBA17]/10 rounded-lg transition-colors" title="View">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
        </div>
        <div class="flex items-center justify-between text-sm text-gray-500">
          <div class="flex items-center gap-4">
            <span>👁️ ${note.views || 0} views</span>
            <span>⬇️ ${note.downloads || 0} downloads</span>
          </div>
          <span class="text-[#7C3AED] font-medium">${getTimeAgo(note.uploadedDate)}</span>
        </div>
      </div>
    `).join('');
  }

  // Update notes statistics
  function updateNotesStats(notes) {
    const totalNotes = notes.length;
    const recentNotes = notes.filter(note => {
      const noteDate = new Date(note.uploadedDate);
      const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
      return noteDate > weekAgo;
    }).length;

    // Update stats elements
    const statsElements = {
      'user-total-notes': totalNotes,
      'user-recent-notes': recentNotes
    };

    Object.entries(statsElements).forEach(([id, value]) => {
      const element = document.getElementById(id);
      if (element) {
        element.textContent = value;
      }
    });
  }

  // Handle notes search
  function handleNotesSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const filteredNotes = notesState.userNotes.filter(note =>
      note.title.toLowerCase().includes(searchTerm) ||
      note.description.toLowerCase().includes(searchTerm) ||
      note.category.toLowerCase().includes(searchTerm)
    );
    renderUserNotes(filteredNotes);
  }

  // Handle notes filter
  function handleNotesFilter(e) {
    const filter = e.target.value;
    let filteredNotes = notesState.userNotes;

    if (filter === 'recent') {
      filteredNotes = filteredNotes.filter(note => {
        const noteDate = new Date(note.uploadedDate);
        const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
        return noteDate > weekAgo;
      });
    } else if (filter === 'priority-high') {
      filteredNotes = filteredNotes.filter(note => note.priority === 'high');
    } else if (filter === 'category-bible-study') {
      filteredNotes = filteredNotes.filter(note => note.category === 'bible-study');
    } else if (filter.startsWith('day-')) {
      filteredNotes = filteredNotes.filter(note => note.day === filter);
    }

    renderUserNotes(filteredNotes);
  }

  // Download user notes
  function downloadUserNotes(notesId) {
    const note = notesState.userNotes.find(n => n.id === notesId);
    if (!note) return;

    // Track download
    if (backend && backend.trackNotesDownload) {
      backend.trackNotesDownload(notesId, currentUser.id);
    }

    if (note.fileBase64) {
      // Create download link for file
      const link = document.createElement('a');
      link.href = `data:${note.fileType};base64,${note.fileBase64}`;
      link.download = note.fileName;
      link.click();
    } else {
      // Create text file from description
      const blob = new Blob([note.description], { type: 'text/plain' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `${note.title}.txt`;
      link.click();
      URL.revokeObjectURL(url);
    }

    showNotification('Notes downloaded successfully!', 'success');
    
    // Update download count in UI
    note.downloads = (note.downloads || 0) + 1;
    renderUserNotes(notesState.userNotes);
  }

  // View user notes
  function viewUserNotes(notesId) {
    const note = notesState.userNotes.find(n => n.id === notesId);
    if (!note) return;

    // Track view
    if (backend && backend.trackNotesView) {
      backend.trackNotesView(notesId, currentUser.id);
    }

    // Create modal to view notes
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
      <div class="bg-white rounded-2xl p-8 max-w-4xl mx-4 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-bold text-[#2046B3]">${note.title}</h3>
          <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        
        <div class="space-y-4">
          <div class="flex items-center gap-4 text-sm text-gray-600">
            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">${note.category}</span>
            <span class="px-2 py-1 ${getPriorityColor(note.priority)} rounded-full">${note.priority}</span>
            <span>📅 ${new Date(note.uploadedDate).toLocaleDateString()}</span>
            <span>👤 ${note.uploadedBy?.name || 'Admin'}</span>
          </div>
          
          <div class="prose max-w-none">
            <p class="text-gray-700 leading-relaxed">${note.description}</p>
          </div>
          
          ${note.fileName ? `
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-[#7C3AED]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div>
                  <p class="font-medium text-gray-900">${note.fileName}</p>
                  <p class="text-sm text-gray-500">${(note.fileSize / 1024).toFixed(2)} KB</p>
                </div>
                <button onclick="downloadUserNotes('${note.id}')" class="ml-auto bg-[#7C3AED] text-white px-4 py-2 rounded-lg hover:bg-[#6D28D9] transition-colors">
                  Download
                </button>
              </div>
            </div>
          ` : ''}
        </div>
        
        <div class="flex gap-4 mt-8">
          <button onclick="downloadUserNotes('${note.id}')" class="flex-1 bg-gradient-to-r from-[#7C3AED] to-[#2046B3] text-white py-3 rounded-lg font-bold hover:shadow-lg transition-all">
            Download Notes
          </button>
          <button onclick="this.closest('.fixed').remove()" class="flex-1 border border-gray-300 text-gray-700 py-3 rounded-lg font-bold hover:bg-gray-50 transition-all">
            Close
          </button>
        </div>
      </div>
    `;
    
    document.body.appendChild(modal);
    
    // Update view count in UI
    note.views = (note.views || 0) + 1;
    renderUserNotes(notesState.userNotes);
  }

  // Start real-time updates
  function startNotesRealTimeUpdates() {
    // Update every 10 seconds
    setInterval(() => {
      if (currentUser && backend) {
        loadUserNotes();
      }
    }, 10000);

    // Listen for new notes notifications
    if (backend) {
      backend.broadcastToUsers({
        type: 'notes_update',
        data: { action: 'check_for_updates' }
      });
    }
  }

  // Get priority color
  function getPriorityColor(priority) {
    switch (priority) {
      case 'high': return 'bg-red-100 text-red-800';
      case 'medium': return 'bg-yellow-100 text-yellow-800';
      case 'low': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
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
    loadUserNotes();
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initUserNotes);
  } else {
    initUserNotes();
  }

  // Export functions for global use
  window.UserNotes = {
    setCurrentUser,
    loadUserNotes,
    downloadUserNotes,
    viewUserNotes
  };

  // Global functions for HTML onclick handlers
  window.downloadUserNotes = function(notesId) {
    if (window.UserNotes) {
      window.UserNotes.downloadUserNotes(notesId);
    }
  };

  window.viewUserNotes = function(notesId) {
    if (window.UserNotes) {
      window.UserNotes.viewUserNotes(notesId);
    }
  };

})(); 