<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: index.php');
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <div class="flex items-center gap-6">
      <a href="dashboard.php" class="font-bold text-[#7C3AED] text-xl">Admin Dashboard</a>
      <a href="analytics.php" class="bg-[#FDBA17] text-[#2046B3] px-4 py-2 rounded font-bold shadow hover:bg-[#7C3AED] hover:text-white transition">Analytics</a>
    </div>
    <a href="logout.php" class="text-red-500 font-bold">Logout</a>
  </nav>
  <main class="max-w-6xl mx-auto mt-8 px-4">
    <!-- Gallery Management Section -->
    <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
      <div class="flex items-center gap-4 mb-6">
        <div class="bg-gradient-to-br from-[#7C3AED] to-[#FDBA17] rounded-full p-3 shadow-lg">
          <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-[#7C3AED] to-[#FDBA17]">Gallery Management</h2>
        <span class="text-[#7C3AED] text-2xl">📸</span>
      </div>
      
      <!-- Upload Area -->
      <div id="upload-area" class="border-2 border-dashed border-[#7C3AED] rounded-xl p-8 text-center hover:border-[#FDBA17] transition-colors cursor-pointer mb-6">
        <svg class="w-16 h-16 text-[#7C3AED] mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
        </svg>
        <h3 class="text-xl font-bold text-[#7C3AED] mb-2">Upload Images</h3>
        <p class="text-gray-600 mb-4">Drag & drop images here or click to browse</p>
        <input type="file" id="image-upload" multiple accept="image/*" class="hidden" />
        <button onclick="document.getElementById('image-upload').click()" class="px-6 py-3 bg-[#7C3AED] text-white font-bold rounded-lg hover:bg-[#FDBA17] hover:text-[#2046B3] transition">
          Choose Files
        </button>
      </div>
      
      <!-- Upload Progress -->
      <div id="upload-progress" class="hidden mb-6">
        <div class="flex items-center gap-4 mb-2">
          <span class="text-sm font-semibold text-[#7C3AED]">Uploading...</span>
          <span id="upload-percentage" class="text-sm text-gray-600">0%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div id="progress-bar" class="bg-[#7C3AED] h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
      </div>
      
      <!-- Image Previews -->
      <div id="image-previews" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6"></div>
      
      <!-- Metadata Form -->
      <div id="metadata-form" class="hidden">
        <h3 class="text-xl font-bold text-[#7C3AED] mb-4">Image Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Album/Category</label>
            <select id="upload-album" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:outline-none">
              <option value="General">General</option>
              <option value="Events">Events</option>
              <option value="Worship">Worship</option>
              <option value="Outreach">Outreach</option>
              <option value="Youth">Youth</option>
              <option value="Leadership">Leadership</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tags (comma separated)</label>
            <input type="text" id="upload-tags" placeholder="worship, ministry, event" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:outline-none" />
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
            <textarea id="upload-description" rows="3" placeholder="Describe the image..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7C3AED] focus:outline-none"></textarea>
          </div>
        </div>
        <div class="flex gap-4 mt-6">
          <button id="upload-submit" class="px-8 py-3 bg-[#7C3AED] text-white font-bold rounded-lg hover:bg-[#FDBA17] hover:text-[#2046B3] transition">
            Upload to Gallery
          </button>
          <button id="upload-cancel" class="px-8 py-3 bg-gray-400 text-white font-bold rounded-lg hover:bg-gray-500 transition">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Other Admin Panels -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <a href="users.php" class="bg-white rounded-xl shadow p-6 flex flex-col items-center hover:bg-[#F3E8FF] transition">
        <span class="text-3xl mb-2">👤</span>
        <span class="font-bold text-lg text-[#7C3AED]">User Management</span>
      </a>
      <a href="mentors.php" class="bg-white rounded-xl shadow p-6 flex flex-col items-center hover:bg-[#F3E8FF] transition">
        <span class="text-3xl mb-2">👨‍🏫</span>
        <span class="font-bold text-lg text-[#7C3AED]">Mentor Management</span>
      </a>
      <a href="modules.php" class="bg-white rounded-xl shadow p-6 flex flex-col items-center hover:bg-[#F3E8FF] transition">
        <span class="text-3xl mb-2">📚</span>
        <span class="font-bold text-lg text-[#7C3AED]">Module Management</span>
      </a>
      <a href="resources.php" class="bg-white rounded-xl shadow p-6 flex flex-col items-center hover:bg-[#F3E8FF] transition">
        <span class="text-3xl mb-2">📖</span>
        <span class="font-bold text-lg text-[#7C3AED]">Resource Management</span>
      </a>
      <a href="pathways.php" class="bg-white rounded-xl shadow p-6 flex flex-col items-center hover:bg-[#F3E8FF] transition">
        <span class="text-3xl mb-2">🛤️</span>
        <span class="font-bold text-lg text-[#7C3AED]">Pathway Management</span>
      </a>
      <a href="assessments.php" class="bg-white rounded-xl shadow p-6 flex flex-col items-center hover:bg-[#F3E8FF] transition">
        <span class="text-3xl mb-2">📝</span>
        <span class="font-bold text-lg text-[#7C3AED]">Assessment Management</span>
      </a>
    </div>
  </main>

  <script>
    // Gallery Upload Functionality
    let selectedFiles = [];
    let uploadInProgress = false;
    
    // Handle file selection
    function handleFileSelect(files) {
      selectedFiles = Array.from(files).filter(file => file.type.startsWith('image/'));
      
      if (selectedFiles.length === 0) {
        alert('Please select valid image files.');
        return;
      }
      
      showImagePreviews();
      showMetadataForm();
    }
    
    // Show image previews
    function showImagePreviews() {
      const previewContainer = document.getElementById('image-previews');
      if (!previewContainer) return;
      
      previewContainer.innerHTML = selectedFiles.map((file, index) => `
        <div class="relative group">
          <img src="${URL.createObjectURL(file)}" alt="Preview" 
               class="w-full h-48 object-cover rounded-lg shadow" />
          <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
            <button onclick="removeImage(${index})" class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold">
              Remove
            </button>
          </div>
          <div class="mt-2 text-sm text-gray-600">
            <div class="font-semibold">${file.name}</div>
            <div>${(file.size / 1024 / 1024).toFixed(2)} MB</div>
          </div>
        </div>
      `).join('');
    }
    
    // Remove image from selection
    window.removeImage = function(index) {
      selectedFiles.splice(index, 1);
      showImagePreviews();
      
      if (selectedFiles.length === 0) {
        hideMetadataForm();
      }
    };
    
    // Show metadata form
    function showMetadataForm() {
      const form = document.getElementById('metadata-form');
      if (form) form.style.display = 'block';
    }
    
    // Hide metadata form
    function hideMetadataForm() {
      const form = document.getElementById('metadata-form');
      if (form) form.style.display = 'none';
    }
    
    // Handle upload submission
    function handleUpload() {
      if (uploadInProgress) return;
      
      const album = document.getElementById('upload-album').value;
      const tags = document.getElementById('upload-tags').value;
      const description = document.getElementById('upload-description').value;
      
      if (selectedFiles.length === 0) {
        alert('Please select images to upload.');
        return;
      }
      
      uploadInProgress = true;
      showUploadProgress();
      
      // Simulate upload process
      let progress = 0;
      const interval = setInterval(() => {
        progress += 10;
        updateProgress(progress);
        
        if (progress >= 100) {
          clearInterval(interval);
          completeUpload();
        }
      }, 200);
    }
    
    // Show upload progress
    function showUploadProgress() {
      const progress = document.getElementById('upload-progress');
      if (progress) progress.style.display = 'block';
    }
    
    // Update progress bar
    function updateProgress(percentage) {
      const progressBar = document.getElementById('progress-bar');
      const percentageText = document.getElementById('upload-percentage');
      
      if (progressBar) progressBar.style.width = percentage + '%';
      if (percentageText) percentageText.textContent = percentage + '%';
    }
    
    // Complete upload
    function completeUpload() {
      uploadInProgress = false;
      
      // Simulate adding images to gallery
      const newImages = selectedFiles.map((file, index) => ({
        id: 'uploaded-' + Date.now() + '-' + index,
        src: URL.createObjectURL(file),
        title: file.name.replace(/\.[^/.]+$/, ''),
        description: document.getElementById('upload-description').value,
        album: document.getElementById('upload-album').value,
        tags: document.getElementById('upload-tags').value.split(',').map(tag => tag.trim()).filter(tag => tag),
        year: new Date().getFullYear().toString(),
        uploadDate: new Date().toISOString(),
        views: 0,
        favorites: 0
      }));
      
      // Add to gallery (in real implementation, this would save to server)
      console.log('Uploaded images:', newImages);
      
      // Reset form
      selectedFiles = [];
      hideMetadataForm();
      hideUploadProgress();
      document.getElementById('image-previews').innerHTML = '';
      document.getElementById('upload-tags').value = '';
      document.getElementById('upload-description').value = '';
      
      // Show success message
      showNotification('Images uploaded successfully!', 'success');
    }
    
    // Hide upload progress
    function hideUploadProgress() {
      const progress = document.getElementById('upload-progress');
      if (progress) progress.style.display = 'none';
    }
    
    // Show notification
    function showNotification(message, type = 'info') {
      const notification = document.createElement('div');
      notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-blue-500 text-white'
      }`;
      notification.textContent = message;
      
      document.body.appendChild(notification);
      
      setTimeout(() => {
        notification.remove();
      }, 3000);
    }
    
    // Initialize upload functionality
    function initUpload() {
      const uploadArea = document.getElementById('upload-area');
      const fileInput = document.getElementById('image-upload');
      const submitBtn = document.getElementById('upload-submit');
      const cancelBtn = document.getElementById('upload-cancel');
      
      if (!uploadArea || !fileInput) return;
      
      // File input change
      fileInput.addEventListener('change', (e) => {
        handleFileSelect(e.target.files);
      });
      
      // Drag and drop
      uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('border-[#FDBA17]');
      });
      
      uploadArea.addEventListener('dragleave', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('border-[#FDBA17]');
      });
      
      uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('border-[#FDBA17]');
        handleFileSelect(e.dataTransfer.files);
      });
      
      // Submit button
      if (submitBtn) {
        submitBtn.addEventListener('click', handleUpload);
      }
      
      // Cancel button
      if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
          selectedFiles = [];
          hideMetadataForm();
          hideUploadProgress();
          document.getElementById('image-previews').innerHTML = '';
          document.getElementById('upload-tags').value = '';
          document.getElementById('upload-description').value = '';
        });
      }
    }
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', initUpload);
  </script>
</body>
</html> 