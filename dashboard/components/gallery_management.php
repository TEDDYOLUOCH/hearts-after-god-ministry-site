<?php
// Gallery Management Component
?>
<div class="space-y-6" x-data="galleryManagement()" x-init="fetchGallery()">
  <!-- Header with Upload Button -->
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-bold text-gray-800">Media Gallery</h2>
    <button 
      @click="showUploadModal = true"
      class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
    >
      <i data-lucide="upload" class="w-4 h-4"></i>
      Upload Media
    </button>
  </div>

  <!-- Loading State -->
  <div x-show="isLoading" class="text-center py-12">
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
    <p class="mt-4 text-gray-600">Loading gallery...</p>
  </div>

  <!-- Error State -->
  <div x-show="errorMessage" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
    <p x-text="errorMessage"></p>
  </div>

  <!-- Gallery Content -->
  <div x-show="!isLoading && !errorMessage" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Gallery Grid -->
    <div x-show="media.length > 0" class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
      <template x-for="item in media" :key="getItemKey(item)">
        <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
          <div class="h-40 bg-gray-100 flex items-center justify-center overflow-hidden relative">
            <template x-if="item.image_url">
              <div class="relative w-full h-full">
                <!-- Main Image -->
                <img 
                  :src="getImageUrl(item.image_url)" 
                  :alt="item.title || 'Gallery image'" 
                  class="h-full w-full object-cover transition-opacity duration-300"
                  :class="{ 'opacity-0': !item.loaded }"
                  @error="handleImageError($event, item)"
                  @load="handleImageLoad($event, item)"
                  loading="lazy"
                  :key="getItemKey(item)">
                
                <!-- Loading Spinner -->
                <div x-show="!item.loaded" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
                
                <!-- Error State -->
                <div x-show="item.error" class="absolute inset-0 flex flex-col items-center justify-center bg-red-50 p-4 text-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  <span class="text-xs text-red-600">Error loading image</span>
                </div>
              </div>
            </template>
            <template x-if="!item.image_url">
              <div class="text-center p-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-xs text-gray-500 mt-2">No image available</p>
              </div>
            </template>
          </div>
          <div class="p-3">
            <h3 class="font-medium text-gray-900 text-sm truncate" x-text="item.title || 'Untitled'"></h3>
            <p class="text-xs text-gray-500 mt-1" x-text="formatDate(item.created_at)"></p>
            <div class="mt-2 flex justify-between items-center">
              <span class="text-xs text-gray-500" x-text="formatFileSize(item.size || 0)"></span>
              <div class="flex space-x-2">
                <button @click="deleteImage(item.id)" class="text-red-600 hover:text-red-800" title="Delete">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Empty State -->
    <div x-show="media.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No images</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by uploading a new image.</p>
      <div class="mt-6">
        <button type="button" @click="showUploadModal = true" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          New Image
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  // Image handling functions
  function handleImageError(event, item = {}) {
    const errorDetails = {
      error: 'Image load error',
      imageUrl: item?.image_url || 'unknown',
      itemId: item?.id || 'unknown',
      timestamp: new Date().toISOString(),
      errorEvent: event ? {
        type: event.type,
        target: event.target ? {
          currentSrc: event.target.currentSrc,
          complete: event.target.complete,
          naturalWidth: event.target.naturalWidth,
          naturalHeight: event.target.naturalHeight,
          readyState: event.target.readyState,
          error: event.target.error
        } : 'No target element'
      } : 'No event data'
    };
    
    console.error('Error loading image:', errorDetails);
    
    // Set a placeholder or error state
    if (event?.target) {
      // Try to recover from broken images by appending a cache buster
      if (event.target.src && !event.target.src.includes('?')) {
        event.target.src = `${event.target.src}?error_${Date.now()}`;
        return; // Let the browser retry with the new URL
      }
      
      // If we get here, the image is still broken, show error placeholder
      event.target.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2Q0ZDVkNzkiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBjbGFzcz0ibHVjaWRlIGx1Y2lkZS1pbWFnZS1vZmYiPjxwYXRoIGQ9Ik0yIDhoMTh2MThhMiAyIDAgMCAxLTIgMkg0YTIgMiAwIDAgMS0yLTJWMTAiLz48cGF0aCBkPSJNMTAuNTkgNC41OUEyIDIgMCAxIDEgMTIgM2EyIDIgMCAwIDEgLjc5IDMuODUiLz48bGluZSB4MT0iMiIgeDI9IjIyIiB5MT0iMiIgeTI9IjIyIi8+PC9zdz4=';
      event.target.alt = 'Failed to load image';
      event.target.classList.add('opacity-100');
    }
    
    // Mark as loaded to remove loading state
    if (item) {
      item.loaded = true;
      
      // Try to refetch the image after a delay if it's the first error
      if (item.id && !item.retryCount) {
        item.retryCount = 1;
        console.log(`Will retry loading image ${item.id} in 2 seconds...`);
        
        setTimeout(() => {
          if (!item.image_url) return;
          
          console.log(`Retrying to load image ${item.id}...`);
          const img = new Image();
          img.onload = () => {
            console.log(`Successfully loaded image ${item.id} on retry`);
            if (event?.target) {
              event.target.src = img.src;
              event.target.classList.add('opacity-100');
              if (item) item.loaded = true;
            }
          };
          img.onerror = () => console.error(`Failed to load image on retry: ${item.image_url}`);
          
          const separator = item.image_url.includes('?') ? '&' : '?';
          img.src = `${item.image_url}${separator}retry=${Date.now()}`;
        }, 2000);
      }  
    }
  }
  
  function handleImageLoad(event, item) {
    if (!item) {
      console.warn('handleImageLoad called without item');
      return;
    }
    
    try {
      console.log(`Image loaded successfully: ${item.id}`, {
        imageUrl: item.image_url,
        naturalWidth: event.target?.naturalWidth,
        naturalHeight: event.target?.naturalHeight,
        src: event.target?.src
      });
      
      // Mark as loaded when image loads successfully
      item.loaded = true;
      
      // If there was a previous error, clear it
      if (item.error) {
        console.log(`Clearing previous error for image ${item.id}`);
        delete item.error;
      }
      
      // Force Alpine to re-render
      setTimeout(() => {
        item.loaded = true;
      }, 0);
      
    } catch (error) {
      console.error('Error in handleImageLoad:', {
        error: error.message,
        itemId: item.id,
        imageUrl: item.image_url
      });
      
      // If there's an error in the load handler, mark as error
      item.error = 'Error displaying image';
      item.loaded = true; // Still mark as loaded to remove spinner
    }
  }

  Alpine.data('galleryManagement', () => ({
    media: [],
    isLoading: true,
    errorMessage: null,
    showUploadModal: false,
    isUploading: false,
    uploadProgress: 0,
    selectedFiles: [],
    uploadCategory: 'general',
    uploadDescription: '',
    isPublic: true,
    isDragging: false,

    async fetchGallery() {
      this.isLoading = true;
      this.errorMessage = null;
      
      try {
        const apiUrl = '/hearts-after-god-ministry-site/api/gallery.php';
        console.log('Fetching gallery from:', apiUrl);
        
        const response = await fetch(apiUrl);
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('API Response:', data);
        
        if (data.success && Array.isArray(data.data)) {
          const uniqueIds = new Set();
          this.media = []; // Clear existing media
          
          // Process each item with better error handling
          for (const item of data.data) {
            try {
              if (!item.id || !item.image_url) {
                console.warn('Skipping item with missing ID or image_url:', item);
                continue;
              }
              
              if (uniqueIds.has(item.id)) {
                console.warn('Duplicate image ID found:', item.id);
                continue;
              }
              
              uniqueIds.add(item.id);
              
              const url = this.getImageUrl(item.image_url);
              console.log('Processing image:', {
                id: item.id,
                original: item.image_url,
                processed: url,
                fullUrl: window.location.origin + url
              });
              
              // Create a test image to verify the URL is valid
              const img = new Image();
              await new Promise((resolve, reject) => {
                img.onload = resolve;
                img.onerror = () => reject(new Error(`Failed to load image: ${url}`));
                img.src = url;
              });
              
              this.media.push({
                ...item,
                image_url: url,
                loaded: false
              });
              
            } catch (error) {
              console.error('Error processing gallery item:', error);
              // Continue with next item even if one fails
              continue;
            }
          }
          
          console.log('Successfully processed media items:', this.media);
          
          if (this.media.length === 0) {
            console.warn('No valid media items found in the response');
            this.showNotification('No images found in the gallery', 'info');
          }
          
        } else {
          throw new Error(data.error || 'Invalid response format from server');
        }
      } catch (error) {
        console.error('Error fetching gallery:', error);
        this.errorMessage = 'Failed to load gallery. Please try again.';
        this.showNotification(this.errorMessage, 'error');
      } finally {
        this.isLoading = false;
      }
    },

    async deleteImage(id) {
      if (!confirm('Are you sure you want to delete this image?')) return;
      
      try {
        const response = await fetch(`/hearts-after-god-ministry-site/api/delete_image.php?id=${id}`, {
          method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.media = this.media.filter(item => item.id !== id);
        } else {
          alert(data.error || 'Failed to delete image');
        }
      } catch (error) {
        console.error('Error deleting image:', error);
        alert('Failed to delete image. Please try again.');
      }
    },

    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString();
    },
    
    // Get image URL with proper base path and cache busting
    getImageUrl(path) {
      if (!path) return '';
      
      // If it's already a full URL or data URL, just add cache buster
      if (path.startsWith('http') || path.startsWith('//') || path.startsWith('data:')) {
        const separator = path.includes('?') ? '&' : '?';
        return `${path}${separator}t=${Date.now()}`;
      }
      
      // Normalize path - remove any leading/trailing slashes
      let cleanPath = path.replace(/^[\\/]+|[\\/]+$/g, '');
      
      // Ensure the path starts with 'uploads/gallery/'
      if (!cleanPath.startsWith('uploads/')) {
        cleanPath = cleanPath.startsWith('gallery/') 
          ? 'uploads/' + cleanPath 
          : 'uploads/gallery/' + cleanPath;
      }
      
      // Remove any double slashes that might have been created
      cleanPath = cleanPath.replace(/([^:]\/)\/+/g, '$1');
      
      // Add cache busting timestamp
      const separator = cleanPath.includes('?') ? '&' : '?';
      const fullPath = `/${cleanPath}${separator}t=${Date.now()}`;
      
      return fullPath;
    },
    
    // Generate a unique key for each gallery item
    getItemKey(item) {
      return `gallery-item-${item.id}-${item._ts || ''}`;
    },
    
    // Handle file selection
    handleFileSelect(event) {
      const files = Array.from(event.target.files);
      this.addFiles(files);
    },
    
    // Handle file drop
    handleDrop(event) {
      event.preventDefault();
      this.isDragging = false;
      const files = Array.from(event.dataTransfer.files);
      this.addFiles(files);
    },
    
    // Add files to the upload queue
    addFiles(files) {
      const validFiles = files.filter(file => {
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
          alert(`File ${file.name} is not a valid image type. Only JPG, PNG, GIF, and WebP are allowed.`);
          return false;
        }
        return true;
      });
      
      this.selectedFiles = [
        ...this.selectedFiles,
        ...validFiles.map(file => ({
          file,
          name: file.name,
          size: file.size,
          type: file.type,
          status: 'pending'
        }))
      ];
    },
    
    // Remove file from upload queue
    removeFile(index) {
      this.selectedFiles.splice(index, 1);
    },
    
    // Upload files to server with proper gallery refresh
    async uploadFiles() {
      if (this.selectedFiles.length === 0) return;
      
      // Prevent multiple submissions
      if (this.isUploading) return;
      
      this.isUploading = true;
      this.uploadProgress = 0;
      
      // Create a new form data object
      const formData = new FormData();
      
      // Add CSRF token if available
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
      if (csrfToken) {
        formData.append('_token', csrfToken);
      }
      
      // Add files to form data
      this.selectedFiles.forEach((fileObj) => {
        formData.append('files[]', fileObj.file);
      });
      
      try {
        console.log('Starting file upload...', {
          fileCount: this.selectedFiles.length,
          files: this.selectedFiles.map(f => f.file.name)
        });
        
        // Show upload progress
        const xhr = new XMLHttpRequest();
        
        // Track upload progress
        xhr.upload.addEventListener('progress', (e) => {
          if (e.lengthComputable) {
            const percentComplete = Math.round((e.loaded / e.total) * 100);
            this.uploadProgress = percentComplete;
            console.log(`Upload progress: ${percentComplete}%`);
          }
        });
        
        // Create a promise to handle the XHR request
        const uploadPromise = new Promise((resolve, reject) => {
          const uploadUrl = '/hearts-after-god-ministry-site/api/gallery/upload.php';
          console.log('Sending upload request to:', uploadUrl);
          
          xhr.open('POST', uploadUrl);
          xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          xhr.responseType = 'json';
          
          xhr.onload = () => {
            console.log('Upload response received:', {
              status: xhr.status,
              response: xhr.response
            });
            
            if (xhr.status >= 200 && xhr.status < 300) {
              resolve(xhr.response);
            } else {
              const error = new Error(xhr.response?.message || `HTTP error! status: ${xhr.status}`);
              error.response = xhr.response;
              reject(error);
            }
          };
          
          xhr.onerror = () => {
            const error = new Error('Network error during file upload');
            console.error('Upload error:', error);
            reject(error);
          };
          
          xhr.ontimeout = () => {
            const error = new Error('Upload timed out');
            console.error('Upload timeout:', error);
            reject(error);
          };
          
          xhr.send(formData);
          console.log('Upload request sent');
        });
        
        const result = await uploadPromise;
        console.log('Upload result:', result);
        
        if (result && result.success) {
          // Show success message
          const fileCount = result.data?.length || 0;
          const message = fileCount === 1 ? 'File uploaded successfully!' : `${fileCount} files uploaded successfully!`;
          this.showNotification(message, 'success');
          
          // Reset the form
          this.resetUploadForm();
          
          // Clear existing media and force a fresh fetch
          this.media = [];
          this.isLoading = true;
          
          // Close the upload modal
          this.showUploadModal = false;
          
          // Force a hard refresh of the gallery with cache busting
          const timestamp = Date.now();
          await this.fetchGallery();
          
          // Force refresh any cached images in the background
          if (result.data && Array.isArray(result.data)) {
            result.data.forEach(fileInfo => {
              if (fileInfo.url) {
                const img = new Image();
                img.src = `${fileInfo.url}${fileInfo.url.includes('?') ? '&' : '?'}t=${timestamp}`;
              }
            });
          }
          
          // Update browser history to prevent form resubmission
          if (window.history.replaceState) {
            window.history.replaceState(null, '', window.location.href.split('?')[0]);
          }
      } catch (error) {
        console.error('Upload error:', error);
        this.showNotification(error.message || 'An error occurred during upload', 'error');
      } finally {
        this.isUploading = false;
        this.uploadProgress = 0;
      }
    },
    
    // Reset upload form
    resetUploadForm() {
      this.selectedFiles = [];
      this.uploadProgress = 0;
      
      // Clear any existing file input
      const fileInput = document.getElementById('file-upload');
      if (fileInput) {
        fileInput.value = '';
      }
      
      // Reset any form fields if needed
      const form = document.querySelector('form[data-upload-form]');
      if (form) {
        form.reset();
      }
    }
  }));
});
</script>
