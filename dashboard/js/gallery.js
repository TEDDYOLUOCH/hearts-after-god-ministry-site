// Gallery Management Alpine.js Component
document.addEventListener('alpine:init', () => {
  Alpine.data('galleryManagement', () => ({
    // State
    viewMode: 'grid',
    searchQuery: '',
    selectedCategory: 'all',
    sortBy: 'newest',
    selectedItems: [],
    media: [],
    filteredMedia: [],
    isLoading: true,
    currentPage: 1,
    itemsPerPage: 12,
    showUploadModal: false,
    isDragging: false,
    uploadProgress: 0,
    isUploading: false,
    selectedFiles: [],
    uploadCategory: 'general',
    uploadDescription: '',
    isPublic: true,
    
    // Computed Properties
    get totalPages() {
      return Math.ceil(this.filteredMedia.length / this.itemsPerPage);
    },
    
    get paginatedMedia() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredMedia.slice(start, end);
    },
    
    // Methods
    init() {
      this.fetchMedia();
    },
    
    async fetchMedia() {
      try {
        this.isLoading = true;
        const response = await fetch('/hearts-after-god-ministry-site/api/gallery/list.php');
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const result = await response.json();
        
        if (result.success && Array.isArray(result.data)) {
          this.media = result.data;
          this.filteredMedia = [...this.media];
          this.sortMedia();
        } else {
          console.error('Invalid response format:', result);
          this.media = [];
          this.filteredMedia = [];
        }
      } catch (error) {
        console.error('Error fetching media:', error);
        this.media = [];
        this.filteredMedia = [];
      } finally {
        this.isLoading = false;
      }
    },
    
    filterMedia() {
      this.filteredMedia = this.media.filter(item => {
        const matchesSearch = !this.searchQuery || 
          (item.filename && item.filename.toLowerCase().includes(this.searchQuery.toLowerCase()));
        const matchesCategory = this.selectedCategory === 'all' || item.category === this.selectedCategory;
        return matchesSearch && matchesCategory;
      });
      this.currentPage = 1;
      this.sortMedia();
    },
    
    sortMedia() {
      this.filteredMedia.sort((a, b) => {
        if (this.sortBy === 'newest') {
          return new Date(b.created_at) - new Date(a.created_at);
        } else if (this.sortBy === 'oldest') {
          return new Date(a.created_at) - new Date(b.created_at);
        } else if (this.sortBy === 'name-asc') {
          return (a.filename || '').localeCompare(b.filename || '');
        } else if (this.sortBy === 'name-desc') {
          return (b.filename || '').localeCompare(a.filename || '');
        }
        return 0;
      });
    },
    
    toggleSelectItem(id) {
      const index = this.selectedItems.indexOf(id);
      if (index === -1) {
        this.selectedItems.push(id);
      } else {
        this.selectedItems.splice(index, 1);
      }
    },
    
    selectAll() {
      if (this.selectedItems.length === this.paginatedMedia.length) {
        this.selectedItems = [];
      } else {
        this.selectedItems = this.paginatedMedia.map(item => item.id);
      }
    },
    
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
    
    getPageRange() {
      const range = [];
      const maxPages = 5; // Show max 5 page numbers
      let startPage = Math.max(1, this.currentPage - Math.floor(maxPages / 2));
      const endPage = Math.min(this.totalPages, startPage + maxPages - 1);
      
      if (endPage - startPage + 1 < maxPages) {
        startPage = Math.max(1, endPage - maxPages + 1);
      }
      
      for (let i = startPage; i <= endPage; i++) {
        range.push(i);
      }
      
      return range;
    },
    
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },
    
    // File handling methods
    handleFileSelect(event) {
      this.selectedFiles = Array.from(event.target.files);
    },
    
    handleDragOver(event) {
      event.preventDefault();
      this.isDragging = true;
    },
    
    handleDragLeave() {
      this.isDragging = false;
    },
    
    handleDrop(event) {
      event.preventDefault();
      this.isDragging = false;
      this.selectedFiles = Array.from(event.dataTransfer.files);
    },
    
    removeFile(index) {
      this.selectedFiles.splice(index, 1);
    },
    
    clearFiles() {
      this.selectedFiles = [];
    },
    
    // Upload methods
    async uploadFiles() {
      if (this.selectedFiles.length === 0) return;
      
      this.isUploading = true;
      this.uploadProgress = 0;
      
      const formData = new FormData();
      this.selectedFiles.forEach(file => {
        formData.append('files[]', file);
      });
      formData.append('category', this.uploadCategory);
      formData.append('description', this.uploadDescription);
      formData.append('is_public', this.isPublic ? '1' : '0');
      
      try {
        const response = await fetch('/hearts-after-god-ministry-site/api/gallery/upload.php', {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
          // Refresh the gallery
          await this.fetchMedia();
          // Reset form
          this.showUploadModal = false;
          this.selectedFiles = [];
          this.uploadCategory = 'general';
          this.uploadDescription = '';
          this.isPublic = true;
          
          // Show success message
          this.$dispatch('notify', {
            type: 'success',
            message: 'Files uploaded successfully!'
          });
        } else {
          throw new Error(result.message || 'Failed to upload files');
        }
      } catch (error) {
        console.error('Error uploading files:', error);
        this.$dispatch('notify', {
          type: 'error',
          message: 'Failed to upload files: ' + (error.message || 'Unknown error')
        });
      } finally {
        this.isUploading = false;
      }
    },
    
    // Delete methods
    async deleteSelected() {
      if (this.selectedItems.length === 0) return;
      
      if (!confirm(`Are you sure you want to delete ${this.selectedItems.length} selected items?`)) {
        return;
      }
      
      try {
        const response = await fetch('/hearts-after-god-ministry-site/api/gallery/delete.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            ids: this.selectedItems
          })
        });
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
          // Refresh the gallery
          await this.fetchMedia();
          this.selectedItems = [];
          
          // Show success message
          this.$dispatch('notify', {
            type: 'success',
            message: 'Selected items deleted successfully!'
          });
        } else {
          throw new Error(result.message || 'Failed to delete items');
        }
      } catch (error) {
        console.error('Error deleting items:', error);
        this.$dispatch('notify', {
          type: 'error',
          message: 'Failed to delete items: ' + (error.message || 'Unknown error')
        });
      }
    },
    
    // Preview methods
    previewItem(item) {
      this.$dispatch('open-preview', { item, items: this.filteredMedia });
    }
  }));
});
