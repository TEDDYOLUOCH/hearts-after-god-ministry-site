<!-- Preview Modal Component -->
<div 
  x-show="showPreviewModal" 
  x-transition:enter="ease-out duration-300" 
  x-transition:enter-start="opacity-0" 
  x-transition:enter-end="opacity-100" 
  x-transition:leave="ease-in duration-200" 
  x-transition:leave-start="opacity-100" 
  x-transition:leave-end="opacity-0" 
  class="fixed inset-0 z-50 overflow-y-auto" 
  aria-labelledby="preview-modal-title" 
  role="dialog" 
  aria-modal="true"
  style="display: none;"
  x-cloak
  @keydown.escape.window="closePreview()"
  x-data="previewModal()"
  @open-preview.window="openPreview($event.detail)"
>
  <!-- Overlay -->
  <div 
    class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" 
    aria-hidden="true"
    @click="closePreview()"
  ></div>
  
  <!-- Close Button -->
  <button 
    @click="closePreview()"
    class="fixed top-4 right-4 text-white hover:text-gray-200 focus:outline-none z-10"
  >
    <i data-lucide="x" class="w-8 h-8"></i>
  </button>
  
  <!-- Navigation Buttons -->
  <button 
    @click="navigate(-1)"
    :disabled="!hasPrevious()"
    :class="{ 'opacity-50 cursor-not-allowed': !hasPrevious() }"
    class="fixed left-4 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 text-white p-3 rounded-full hover:bg-opacity-70 focus:outline-none focus:ring-2 focus:ring-white z-10"
  >
    <i data-lucide="chevron-left" class="w-6 h-6"></i>
  </button>
  
  <button 
    @click="navigate(1)"
    :disabled="!hasNext()"
    :class="{ 'opacity-50 cursor-not-allowed': !hasNext() }"
    class="fixed right-4 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 text-white p-3 rounded-full hover:bg-opacity-70 focus:outline-none focus:ring-2 focus:ring-white z-10"
  >
    <i data-lucide="chevron-right" class="w-6 h-6"></i>
  </button>
  
  <!-- Preview Content -->
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="max-w-6xl w-full">
      <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all">
        <!-- Preview Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h3 class="text-lg font-medium text-gray-900" id="preview-modal-title" x-text="currentItem?.name || ''"></h3>
          <div class="flex space-x-2">
            <a 
              :href="currentItem?.downloadUrl || currentItem?.original" 
              class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              download
            >
              <i data-lucide="download" class="w-4 h-4 mr-1"></i> Download
            </a>
            <button 
              @click="deleteItem()"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
              <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete
            </button>
          </div>
        </div>
        
        <!-- Preview Body -->
        <div class="p-6 flex justify-center">
          <template x-if="currentItem?.type.includes('image')">
            <img 
              :src="currentItem?.original" 
              :alt="currentItem?.name"
              class="max-h-[70vh] w-auto max-w-full"
              @error="currentItem.original = 'https://via.placeholder.com/800x600?text=Image+Not+Found'"
            >
          </template>
          
          <template x-if="currentItem?.type.includes('video')">
            <video 
              :src="currentItem?.original" 
              controls
              class="max-h-[70vh] w-full max-w-full"
              autoplay
            >
              Your browser does not support the video tag.
            </video>
          </template>
          
          <template x-if="currentItem?.type.includes('audio')">
            <div class="w-full max-w-2xl py-12">
              <div class="bg-gray-100 rounded-lg p-6">
                <div class="flex items-center space-x-4">
                  <div class="flex-shrink-0">
                    <i data-lucide="music" class="w-12 h-12 text-gray-400"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate" x-text="currentItem?.name"></p>
                    <p class="text-sm text-gray-500" x-text="formatFileSize(currentItem?.size)"></p>
                  </div>
                </div>
                <audio 
                  :src="currentItem?.original" 
                  controls
                  class="w-full mt-4"
                  autoplay
                >
                  Your browser does not support the audio element.
                </audio>
              </div>
            </div>
          </template>
          
          <template x-if="!['image', 'video', 'audio'].some(type => currentItem?.type.includes(type))">
            <div class="py-12 text-center">
              <i data-lucide="file" class="w-16 h-16 mx-auto text-gray-400"></i>
              <h3 class="mt-2 text-lg font-medium text-gray-900" x-text="currentItem?.name"></h3>
              <p class="mt-1 text-sm text-gray-500" x-text="formatFileSize(currentItem?.size)"></p>
              <div class="mt-6">
                <a 
                  :href="currentItem?.downloadUrl || currentItem?.original" 
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  download
                >
                  <i data-lucide="download" class="w-4 h-4 mr-2"></i> Download File
                </a>
              </div>
            </div>
          </template>
        </div>
        
        <!-- File Info -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
            <div class="sm:col-span-1">
              <dt class="text-sm font-medium text-gray-500">File name</dt>
              <dd class="mt-1 text-sm text-gray-900 truncate" x-text="currentItem?.name"></dd>
            </div>
            <div class="sm:col-span-1">
              <dt class="text-sm font-medium text-gray-500">File type</dt>
              <dd class="mt-1 text-sm text-gray-900" x-text="currentItem?.type"></dd>
            </div>
            <div class="sm:col-span-1">
              <dt class="text-sm font-medium text-gray-500">File size</dt>
              <dd class="mt-1 text-sm text-gray-900" x-text="formatFileSize(currentItem?.size)"></dd>
            </div>
            <div class="sm:col-span-1">
              <dt class="text-sm font-medium text-gray-500">Uploaded</dt>
              <dd class="mt-1 text-sm text-gray-900" x-text="formatDate(currentItem?.uploadedAt)"></dd>
            </div>
            <div class="sm:col-span-2">
              <dt class="text-sm font-medium text-gray-500">URL</dt>
              <div class="mt-1 flex rounded-md shadow-sm">
                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                  URL
                </span>
                <input 
                  type="text" 
                  :value="currentItem?.original || ''" 
                  class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  readonly
                  @click="$event.target.select()"
                >
              </div>
              <p class="mt-2 text-sm text-gray-500">
                Copy this URL to share the file
              </p>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('previewModal', () => ({
    showPreviewModal: false,
    items: [],
    currentIndex: 0,
    
    get currentItem() {
      return this.items[this.currentIndex] || null;
    },
    
    openPreview({ item, items }) {
      this.items = items || [item];
      this.currentIndex = items ? items.findIndex(i => i.id === item.id) : 0;
      this.showPreviewModal = true;
      
      // Prevent body scroll when modal is open
      document.body.style.overflow = 'hidden';
      
      // Add keyboard navigation
      window.addEventListener('keydown', this.handleKeyDown);
    },
    
    closePreview() {
      this.showPreviewModal = false;
      this.items = [];
      this.currentIndex = 0;
      
      // Re-enable body scroll
      document.body.style.overflow = '';
      
      // Clean up event listener
      window.removeEventListener('keydown', this.handleKeyDown);
      
      // Notify parent to refresh the media list if needed
      if (this.shouldRefresh) {
        this.$dispatch('refresh-media');
        this.shouldRefresh = false;
      }
    },
    
    handleKeyDown: null, // Will be defined in init
    
    init() {
      // Define handleKeyDown with access to component context
      this.handleKeyDown = (e) => {
        switch(e.key) {
          case 'ArrowLeft':
            if (this.hasPrevious()) {
              this.navigate(-1);
              e.preventDefault();
            }
            break;
          case 'ArrowRight':
            if (this.hasNext()) {
              this.navigate(1);
              e.preventDefault();
            }
            break;
          case 'Escape':
            this.closePreview();
            break;
        }
      };
    },
    
    navigate(direction) {
      const newIndex = this.currentIndex + direction;
      if (newIndex >= 0 && newIndex < this.items.length) {
        this.currentIndex = newIndex;
      }
    },
    
    hasNext() {
      return this.currentIndex < this.items.length - 1;
    },
    
    hasPrevious() {
      return this.currentIndex > 0;
    },
    
    async deleteItem() {
      if (!this.currentItem) return;
      
      if (!confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
        return;
      }
      
      try {
        // In a real app, you would make an API call to delete the file
        /*
        const response = await fetch(`/api/media/${this.currentItem.id}`, {
          method: 'DELETE'
        });
        
        if (!response.ok) throw new Error('Delete failed');
        */
        
        // Remove the item from the current items array
        this.items.splice(this.currentIndex, 1);
        this.shouldRefresh = true;
        
        // If no items left, close the modal
        if (this.items.length === 0) {
          this.closePreview();
          return;
        }
        
        // Adjust current index if needed
        if (this.currentIndex >= this.items.length) {
          this.currentIndex = this.items.length - 1;
        }
        
        // If only one item remains after deletion, close the preview
        if (this.items.length === 1) {
          this.closePreview();
        }
        
      } catch (error) {
        console.error('Delete error:', error);
        alert('An error occurred while deleting the file. Please try again.');
      }
    },
    
    formatFileSize(bytes) {
      if (bytes === undefined || bytes === null) return '';
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
  }));
});
</script>
