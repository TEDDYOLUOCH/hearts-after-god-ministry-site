<!-- Upload Modal Component -->
<div 
  x-show="showUploadModal" 
  x-transition:enter="ease-out duration-300" 
  x-transition:enter-start="opacity-0" 
  x-transition:enter-end="opacity-100" 
  x-transition:leave="ease-in duration-200" 
  x-transition:leave-start="opacity-100" 
  x-transition:leave-end="opacity-0" 
  class="fixed inset-0 z-50 overflow-y-auto" 
  aria-labelledby="upload-modal-title" 
  role="dialog" 
  aria-modal="true"
  style="display: none;"
  x-cloak
  @keydown.escape.window="showUploadModal = false"
  @open-upload.window="showUploadModal = true"
>
  <!-- Overlay -->
  <div 
    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
    aria-hidden="true"
    @click="showUploadModal = false"
  ></div>

  <!-- Modal Content -->
  <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    
    <div 
      class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h3 class="text-lg leading-6 font-medium text-gray-900" id="upload-modal-title">
          Upload Media
        </h3>
        <button 
          @click="showUploadModal = false"
          type="button" 
          class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <span class="sr-only">Close</span>
          <i data-lucide="x" class="h-6 w-6"></i>
        </button>
      </div>
      
      <!-- Upload Area -->
      <div 
        class="mt-5 border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors"
        @dragover.prevent="isDragging = true"
        @dragleave="isDragging = false"
        @drop.prevent="handleDrop($event)"
        :class="{ 'border-blue-500 bg-blue-50': isDragging }"
      >
        <div class="space-y-1 text-center">
          <i data-lucide="upload" class="mx-auto h-12 w-12 text-gray-400"></i>
          <div class="flex text-sm text-gray-600 justify-center">
            <form id="upload-form" data-upload-form>
              <label 
                for="file-upload" 
                class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"
                @click.prevent
              >
                <span>Upload a file</span>
                <input 
                  id="file-upload" 
                  name="file-upload" 
                  type="file" 
                  class="sr-only" 
                  multiple
                  @change="handleFileSelect($event)"
                >
              </label>
              <p class="pl-1">or drag and drop</p>
            </form>
          </div>
          <p class="text-xs text-gray-500">
            PNG, JPG, GIF, MP4 up to 10MB
          </p>
        </div>
      </div>
      
      <!-- Upload Progress -->
      <div x-show="isUploading" class="mt-6 space-y-4">
        <h4 class="text-sm font-medium text-gray-700">Uploading files ({{ uploadProgress }}%)</h4>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
          <div 
            class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" 
            :style="`width: ${uploadProgress}%`"
          ></div>
        </div>
      </div>
      
      <!-- Selected Files -->
      <div x-show="selectedFiles.length > 0" class="mt-6">
        <div class="flex items-center justify-between mb-2">
          <h4 class="text-sm font-medium text-gray-700">
            <span x-text="selectedFiles.length"></span> file(s) selected
            <span x-show="isUploading" class="text-xs text-gray-500">
              (Uploading... <span x-text="uploadProgress + '%'"></span>)
            </span>
          </h4>
          <button 
            x-show="!isUploading"
            @click="selectedFiles = []" 
            class="text-xs text-red-600 hover:text-red-800"
          >
            Clear all
          </button>
        </div>
        
        <div class="space-y-2 max-h-48 overflow-y-auto pr-2">
          <template x-for="(file, index) in selectedFiles" :key="file.name + index">
            <div 
              class="flex items-center justify-between p-3 bg-white border rounded-lg hover:bg-gray-50 transition-colors"
              :class="{
                'border-blue-200': file.status === 'pending',
                'border-green-200': file.status === 'completed',
                'border-red-200': file.status === 'error'
              }"
            >
              <div class="flex items-center space-x-3 flex-1 min-w-0">
                <div class="flex-shrink-0">
                  <template x-if="file.type.startsWith('image/')">
                    <img 
                      :src="URL.createObjectURL(file.file)" 
                      class="h-10 w-10 object-cover rounded"
                  >
                </template>
                <template x-if="!file.type.startsWith('image/')">
                  <div class="h-10 w-10 flex items-center justify-center bg-gray-200 rounded-md">
                    <i data-lucide="file" class="h-5 w-5 text-gray-500"></i>
                  </div>
                </template>
                <div class="min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate" x-text="file.name"></p>
                  <p class="text-xs text-gray-500" x-text="$root.formatFileSize(file.size)"></p>
                </div>
              </div>
              <button 
                @click="$root.removeFile(index)" 
                type="button" 
                class="text-gray-400 hover:text-red-500"
              >
                <i data-lucide="x" class="h-5 w-5"></i>
              </button>
            </div>
          </template>
        </div>
      </div>
      
      <!-- Upload Options -->
      <div x-show="$root.selectedFiles.length > 0" class="mt-6 space-y-4">
        <div>
          <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
          <select 
            id="category" 
            x-model="$root.uploadCategory"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
          >
            <option value="general">General</option>
            <option value="events">Events</option>
            <option value="sermons">Sermons</option>
            <option value="ministry">Ministry</option>
            <option value="other">Other</option>
          </select>
        </div>
        
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
          <div class="mt-1">
            <textarea 
              id="description" 
              x-model="$root.uploadDescription"
              rows="3" 
              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-gray-300 rounded-md"
              placeholder="Add a description for these files..."
            ></textarea>
          </div>
        </div>
        
        <div class="flex items-center">
          <input 
            id="is-public" 
            type="checkbox" 
            x-model="$root.isPublic"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          >
          <label for="is-public" class="ml-2 block text-sm text-gray-700">
            Make these files publicly accessible
          </label>
        </div>
      </div>
        
        <!-- Footer -->
      <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
        <button
          type="button"
          class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm"
          :class="{ 'opacity-50 cursor-not-allowed': $root.isUploading || $root.selectedFiles.length === 0 }"
          :disabled="$root.isUploading || $root.selectedFiles.length === 0"
          @click="$root.uploadFiles()"
        >
          <span x-show="!$root.isUploading">Upload Files</span>
          <span x-show="$root.isUploading" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Uploading...
          </span>
        </button>
        <button
          type="button"
          @click="showUploadModal = false"
          class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// All functionality is handled by the parent gallery management component
</script>
