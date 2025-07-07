// Admin Notes Management System
// Real-time notes upload and user reflection

(function() {
  let backend = null;
  let notesState = {
    currentNotes: [],
    isUploading: false,
    lastUpdate: null
  };

  // Initialize notes system
  function initNotesSystem() {
    console.log('Initializing admin notes system...');
    
    if (!window.DiscipleshipBackend) {
      console.log('Waiting for backend to load...');
      setTimeout(initNotesSystem, 100);
      return;
    }
    
    try {
      backend = window.DiscipleshipBackend;
      console.log('Backend loaded, initializing notes system...');
      
      setupNotesEventListeners();
      loadNotes();
      startNotesRealTimeUpdates();
      
      console.log('Notes system initialized successfully');
    } catch (error) {
      console.error('Error initializing notes system:', error);
    }
  }

  // Setup notes event listeners
  function setupNotesEventListeners() {
    try {
      console.log('Setting up notes event listeners...');
      
      // Notes upload form
      const notesForm = document.getElementById('notes-upload-form');
      if (notesForm) {
        notesForm.addEventListener('submit', handleNotesUpload);
      }

      // File input
      const fileInput = document.getElementById('notes-file-input');
      if (fileInput) {
        fileInput.addEventListener('change', handleFileSelection);
      }

      // Notes filter
      const notesFilter = document.getElementById('notes-filter');
      if (notesFilter) {
        notesFilter.addEventListener('change', handleNotesFilter);
      }

      // Notes search
      const notesSearch = document.getElementById('notes-search');
      if (notesSearch) {
        notesSearch.addEventListener('input', handleNotesSearch);
      }

      // Notes actions
      const addNotesBtn = document.getElementById('add-notes-btn');
      if (addNotesBtn) {
        addNotesBtn.addEventListener('click', openNotesModal);
      }

      const closeNotesBtn = document.getElementById('close-notes-modal');
      if (closeNotesBtn) {
        closeNotesBtn.addEventListener('click', closeNotesModal);
      }

      console.log('Notes event listeners setup completed');
    } catch (error) {
      console.error('Error setting up notes event listeners:', error);
    }
  }

  // Handle notes upload
  function handleNotesUpload(e) {
    e.preventDefault();
    
    if (notesState.isUploading) {
      showNotification('Upload already in progress...', 'warning');
      return;
    }

    const formData = new FormData(e.target);
    const title = formData.get('notes-title');
    const description = formData.get('notes-description');
    const day = formData.get('notes-day');
    const category = formData.get('notes-category');
    const priority = formData.get('notes-priority');
    const targetUsers = formData.get('notes-target-users');
    const file = formData.get('notes-file');

    if (!title || !description || !day) {
      showNotification('Please fill in all required fields including Day', 'error');
      return;
    }

    notesState.isUploading = true;
    updateUploadStatus('Uploading notes...');

    // Create notes object
    const notesData = {
      id: 'notes_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
      title: title,
      description: description,
      day: day,
      category: category,
      priority: priority,
      targetUsers: targetUsers === 'all' ? 'all' : targetUsers.split(',').map(u => u.trim()),
      fileName: file ? file.name : null,
      fileSize: file ? file.size : null,
      fileType: file ? file.type : null,
      uploadedBy: getCurrentAdminUser(),
      uploadedDate: new Date().toISOString(),
      status: 'active',
      views: 0,
      downloads: 0
    };

    // Handle file upload
    if (file) {
      handleFileUpload(file, notesData);
    } else {
      saveNotes(notesData);
    }
  }

  // Handle file upload
  function handleFileUpload(file, notesData) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
      notesData.fileContent = e.target.result;
      notesData.fileBase64 = btoa(e.target.result);
      saveNotes(notesData);
    };

    reader.onerror = function() {
      notesState.isUploading = false;
      updateUploadStatus('File upload failed');
      showNotification('Error reading file', 'error');
    };

    reader.readAsBinaryString(file);
  }

  // Save notes to backend
  function saveNotes(notesData) {
    try {
      if (backend && backend.createNotes) {
        backend.createNotes(notesData);
      } else {
        // Fallback to localStorage
        const notes = JSON.parse(localStorage.getItem('admin_notes') || '[]');
        notes.unshift(notesData);
        localStorage.setItem('admin_notes', JSON.stringify(notes));
      }

      // Broadcast to users in real-time
      broadcastNotesToUsers(notesData);

      notesState.isUploading = false;
      updateUploadStatus('Notes uploaded successfully!');
      showNotification('Notes uploaded successfully!', 'success');

      // Reset form
      const form = document.getElementById('notes-upload-form');
      if (form) {
        form.reset();
        updateFilePreview(null);
      }

      // Reload notes list
      loadNotes();

    } catch (error) {
      console.error('Error saving notes:', error);
      notesState.isUploading = false;
      updateUploadStatus('Upload failed');
      showNotification('Error uploading notes', 'error');
    }
  }

  // Broadcast notes to users
  function broadcastNotesToUsers(notesData) {
    try {
      if (backend && backend.broadcastToUsers) {
        backend.broadcastToUsers({
          type: 'new_notes',
          data: notesData
        });
      }

      // Also broadcast to specific users if targeted
      if (notesData.targetUsers && notesData.targetUsers !== 'all') {
        notesData.targetUsers.forEach(userId => {
          if (backend && backend.sendUserNotification) {
            backend.sendUserNotification(userId, {
              type: 'new_notes',
              title: 'New Notes Available',
              message: `New notes uploaded: ${notesData.title}`,
              data: notesData
            });
          }
        });
      }
    } catch (error) {
      console.error('Error broadcasting notes:', error);
    }
  }

  // Handle file selection
  function handleFileSelection(e) {
    const file = e.target.files[0];
    updateFilePreview(file);
  }

  // Update file preview
  function updateFilePreview(file) {
    const preview = document.getElementById('file-preview');
    if (!preview) return;

    if (file) {
      const fileSize = (file.size / 1024).toFixed(2);
      preview.innerHTML = `
        <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
          <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <div>
            <p class="font-medium text-blue-900">${file.name}</p>
            <p class="text-sm text-blue-600">${fileSize} KB</p>
          </div>
          <button onclick="removeFile()" class="ml-auto text-red-600 hover:text-red-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      `;
    } else {
      preview.innerHTML = '';
    }
  }

  // Remove file
  function removeFile() {
    const fileInput = document.getElementById('notes-file-input');
    if (fileInput) {
      fileInput.value = '';
    }
    updateFilePreview(null);
  }

  // Load notes
  function loadNotes() {
    try {
      let notes = [];
      
      if (backend && backend.getAllNotes) {
        notes = backend.getAllNotes();
      } else {
        // Fallback to localStorage
        notes = JSON.parse(localStorage.getItem('admin_notes') || '[]');
      }

      notesState.currentNotes = notes;
      renderNotes(notes);
      updateNotesStats(notes);
      
    } catch (error) {
      console.error('Error loading notes:', error);
    }
  }

  // Render notes
  function renderNotes(notes) {
    const notesContainer = document.getElementById('notes-list');
    if (!notesContainer) return;

    if (notes.length === 0) {
      notesContainer.innerHTML = `
        <div class="text-center py-8">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="text-gray-500 text-lg">No notes uploaded yet</p>
          <p class="text-gray-400">Upload your first notes to get started</p>
        </div>
      `;
      return;
    }

    notesContainer.innerHTML = notes.map(note => `
      <div class="flex items-center bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all px-4 py-3 mb-2 gap-4">
        <div class="flex-1 flex flex-wrap items-center gap-2 min-w-0">
          <span class="font-bold text-[#2046B3] truncate max-w-[120px]">${note.title}</span>
          <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs font-bold">${note.day || 'No Day'}</span>
          <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs">${note.category}</span>
          <span class="px-2 py-0.5 ${getPriorityColor(note.priority)} rounded-full text-xs capitalize">${note.priority}</span>
          <span class="flex items-center gap-1 text-xs text-gray-500"><i class="fa-regular fa-calendar"></i> ${new Date(note.uploadedDate).toLocaleDateString()}</span>
          <span class="flex items-center gap-1 text-xs text-gray-500"><i class="fa-regular fa-user"></i> ${note.uploadedBy?.name || 'Admin'}</span>
          <span class="flex items-center gap-1 text-xs text-gray-500"><i class="fa-regular fa-eye"></i> ${note.views || 0}</span>
          <span class="flex items-center gap-1 text-xs text-gray-500"><i class="fa-regular fa-arrow-down"></i> ${note.downloads || 0}</span>
          <span class="flex items-center gap-1 text-xs text-gray-500">${note.targetUsers === 'all' ? 'All Users' : (note.targetUsers.length + ' users')}</span>
        </div>
        <div class="flex items-center gap-2 ml-auto">
          ${note.fileName ? `
            <button onclick="downloadNotes('${note.id}')" class="p-2 text-[#7C3AED] hover:bg-[#7C3AED]/10 rounded transition-colors" title="Download">
              <i class="fa-solid fa-download"></i>
            </button>
          ` : ''}
          <button onclick="editNotes('${note.id}')" class="p-2 text-[#FDBA17] hover:bg-[#FDBA17]/10 rounded transition-colors" title="Edit">
            <i class="fa-solid fa-pen"></i>
          </button>
          <button onclick="deleteNotes('${note.id}')" class="p-2 text-red-600 hover:bg-red-100 rounded transition-colors" title="Delete">
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>
    `).join('');
  }

  // Update notes statistics
  function updateNotesStats(notes) {
    const totalNotes = notes.length;
    const totalViews = notes.reduce((sum, note) => sum + (note.views || 0), 0);
    const totalDownloads = notes.reduce((sum, note) => sum + (note.downloads || 0), 0);
    const recentNotes = notes.filter(note => {
      const noteDate = new Date(note.uploadedDate);
      const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
      return noteDate > weekAgo;
    }).length;

    // Update stats elements
    const statsElements = {
      'total-notes': totalNotes,
      'total-views': totalViews,
      'total-downloads': totalDownloads,
      'recent-notes': recentNotes
    };

    Object.entries(statsElements).forEach(([id, value]) => {
      const element = document.getElementById(id);
      if (element) {
        element.textContent = value;
      }
    });
  }

  // Handle notes filter
  function handleNotesFilter(e) {
    const filter = e.target.value;
    let filteredNotes = notesState.currentNotes;

    if (filter === 'recent') {
      filteredNotes = filteredNotes.filter(note => {
        const noteDate = new Date(note.uploadedDate);
        const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
        return noteDate > weekAgo;
      });
    } else if (filter === 'popular') {
      filteredNotes = filteredNotes.sort((a, b) => (b.views || 0) - (a.views || 0));
    } else if (filter === 'priority-high') {
      filteredNotes = filteredNotes.filter(note => note.priority === 'high');
    }

    renderNotes(filteredNotes);
  }

  // Handle notes search
  function handleNotesSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const filteredNotes = notesState.currentNotes.filter(note =>
      note.title.toLowerCase().includes(searchTerm) ||
      note.description.toLowerCase().includes(searchTerm) ||
      note.category.toLowerCase().includes(searchTerm)
    );
    renderNotes(filteredNotes);
  }

  // Open notes modal
  function openNotesModal() {
    const modal = document.getElementById('notes-modal');
    if (modal) {
      modal.classList.remove('hidden');
    }
  }

  // Close notes modal
  function closeNotesModal() {
    const modal = document.getElementById('notes-modal');
    if (modal) {
      modal.classList.add('hidden');
    }
  }

  // Download notes
  function downloadNotes(notesId) {
    const note = notesState.currentNotes.find(n => n.id === notesId);
    if (!note) return;

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

    // Update download count
    note.downloads = (note.downloads || 0) + 1;
    saveNotesToStorage();
    renderNotes(notesState.currentNotes);
  }

  // Edit notes
  function editNotes(notesId) {
    const note = notesState.currentNotes.find(n => n.id === notesId);
    if (!note) return;

    // Populate edit form
    const form = document.getElementById('notes-upload-form');
    if (form) {
      form.elements['notes-title'].value = note.title;
      form.elements['notes-description'].value = note.description;
      form.elements['notes-category'].value = note.category;
      form.elements['notes-priority'].value = note.priority;
      form.elements['notes-target-users'].value = note.targetUsers === 'all' ? 'all' : note.targetUsers.join(', ');
      
      // Add edit mode indicator
      form.dataset.editMode = 'true';
      form.dataset.editId = notesId;
    }

    openNotesModal();
  }

  // Delete notes
  function deleteNotes(notesId) {
    if (!confirm('Are you sure you want to delete this notes? This action cannot be undone.')) {
      return;
    }

    notesState.currentNotes = notesState.currentNotes.filter(n => n.id !== notesId);
    saveNotesToStorage();
    renderNotes(notesState.currentNotes);
    showNotification('Notes deleted successfully', 'success');
  }

  // Save notes to storage
  function saveNotesToStorage() {
    if (backend && backend.saveNotes) {
      backend.saveNotes(notesState.currentNotes);
    } else {
      localStorage.setItem('admin_notes', JSON.stringify(notesState.currentNotes));
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

  // Get priority color
  function getPriorityColor(priority) {
    switch (priority) {
      case 'high': return 'bg-red-100 text-red-800';
      case 'medium': return 'bg-yellow-100 text-yellow-800';
      case 'low': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  }

  // Start real-time updates
  function startNotesRealTimeUpdates() {
    setInterval(() => {
      loadNotes();
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
    document.addEventListener('DOMContentLoaded', initNotesSystem);
  } else {
    initNotesSystem();
  }

  // Export functions for global use
  window.AdminNotes = {
    openNotesModal,
    closeNotesModal,
    downloadNotes,
    editNotes,
    deleteNotes,
    removeFile,
    loadNotes
  };

  // Global functions for HTML onclick handlers
  window.openNotesModal = function() {
    if (window.AdminNotes) {
      window.AdminNotes.openNotesModal();
    }
  };

  window.closeNotesModal = function() {
    if (window.AdminNotes) {
      window.AdminNotes.closeNotesModal();
    }
  };

  window.downloadNotes = function(notesId) {
    if (window.AdminNotes) {
      window.AdminNotes.downloadNotes(notesId);
    }
  };

  window.editNotes = function(notesId) {
    if (window.AdminNotes) {
      window.AdminNotes.editNotes(notesId);
    }
  };

  window.deleteNotes = function(notesId) {
    if (window.AdminNotes) {
      window.AdminNotes.deleteNotes(notesId);
    }
  };

  window.removeFile = function() {
    if (window.AdminNotes) {
      window.AdminNotes.removeFile();
    }
  };

})(); 