<div class="space-y-6" x-data="sermonsManagement()" x-init="init()">
  <!-- Header -->
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-bold text-gray-800">Sermons Management</h2>
    <button @click="showSermonModal = true" class="btn-primary">
      <i data-lucide="plus" class="w-4 h-4"></i> Add Sermon
    </button>
  </div>

  <!-- Search and Filter -->
  <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
    <div class="flex flex-col md:flex-row gap-4">
      <div class="relative flex-1">
        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
        <input type="text" x-model="searchQuery" @input="filterSermons()" 
               class="pl-10 w-full" placeholder="Search sermons...">
      </div>
      <div class="flex gap-2">
        <select x-model="filterSeries" @change="filterSermons()" class="text-sm">
          <option value="">All Series</option>
          <template x-for="series in seriesList" :key="series.id">
            <option :value="series.id" x-text="series.name"></option>
          </template>
        </select>
        <select x-model="filterSpeaker" @change="filterSermons()" class="text-sm">
          <option value="">All Speakers</option>
          <template x-for="speaker in speakersList" :key="speaker.id">
            <option :value="speaker.id" x-text="speaker.name"></option>
          </template>
        </select>
      </div>
    </div>
  </div>

  <!-- Sermons List -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sermon</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Speaker</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <template x-for="sermon in paginatedSermons" :key="sermon.id">
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="font-medium text-gray-900" x-text="sermon.title"></div>
                <div class="text-sm text-gray-500" x-text="sermon.scripture_reference"></div>
              </td>
              <td class="px-6 py-4">
                <div x-text="getSpeakerName(sermon.speaker_id)" class="text-sm"></div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div x-text="formatDate(sermon.date_preached)" class="text-sm"></div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="playSermon(sermon)" class="text-blue-600 hover:text-blue-900 mr-3">
                  <i data-lucide="play" class="w-4 h-4"></i>
                </button>
                <button @click="editSermon(sermon)" class="text-blue-600 hover:text-blue-900 mr-3">
                  <i data-lucide="edit" class="w-4 h-4"></i>
                </button>
                <button @click="deleteSermon(sermon.id)" class="text-red-600 hover:text-red-900">
                  <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
              </td>
            </tr>
          </template>
          <tr x-show="filteredSermons.length === 0">
            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
              No sermons found. Add your first sermon to get started.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-3 flex items-center justify-between border-t border-gray-200">
      <div class="text-sm text-gray-700">
        Showing <span x-text="(currentPage - 1) * itemsPerPage + 1"></span> to 
        <span x-text="Math.min(currentPage * itemsPerPage, filteredSermons.length)"></span> of 
        <span x-text="filteredSermons.length"></span> results
      </div>
      <div class="flex space-x-2">
        <button @click="currentPage--" :disabled="currentPage === 1" 
                class="btn-pagination" :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }">
          <i data-lucide="chevron-left" class="w-4 h-4"></i>
        </button>
        <button @click="currentPage++" :disabled="currentPage >= totalPages"
                class="btn-pagination" :class="{ 'opacity-50 cursor-not-allowed': currentPage >= totalPages }">
          <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('sermonsManagement', () => ({
    sermons: [],
    filteredSermons: [],
    seriesList: [],
    speakersList: [],
    searchQuery: '',
    filterSeries: '',
    filterSpeaker: '',
    currentPage: 1,
    itemsPerPage: 10,
    showSermonModal: false,
    editingSermon: null,
    
    sermonForm: {
      id: null,
      title: '',
      description: '',
      date_preached: new Date().toISOString().split('T')[0],
      scripture_reference: '',
      speaker_id: '',
      series_id: '',
      audio_url: '',
      video_url: ''
    },

    async init() {
      await Promise.all([this.fetchSermons(), this.fetchSeries(), this.fetchSpeakers()]);
    },

    async fetchSermons() {
      try {
        const response = await fetch('/api/sermons');
        this.sermons = await response.json();
        this.filterSermons();
      } catch (error) {
        console.error('Error fetching sermons:', error);
      }
    },

    async fetchSeries() {
      try {
        const response = await fetch('/api/series');
        this.seriesList = await response.json();
      } catch (error) {
        console.error('Error fetching series:', error);
      }
    },

    async fetchSpeakers() {
      try {
        const response = await fetch('/api/speakers');
        this.speakersList = await response.json();
      } catch (error) {
        console.error('Error fetching speakers:', error);
      }
    },

    filterSermons() {
      this.currentPage = 1;
      this.filteredSermons = this.sermons.filter(sermon => {
        const matchesSearch = !this.searchQuery || 
          sermon.title.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          sermon.scripture_reference?.toLowerCase().includes(this.searchQuery.toLowerCase());
        
        const matchesSeries = !this.filterSeries || sermon.series_id === this.filterSeries;
        const matchesSpeaker = !this.filterSpeaker || sermon.speaker_id === this.filterSpeaker;
        
        return matchesSearch && matchesSeries && matchesSpeaker;
      });
    },

    get paginatedSermons() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredSermons.slice(start, start + this.itemsPerPage);
    },

    get totalPages() {
      return Math.ceil(this.filteredSermons.length / this.itemsPerPage);
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },

    getSpeakerName(speakerId) {
      const speaker = this.speakersList.find(s => s.id === speakerId);
      return speaker ? speaker.name : 'Unknown';
    },

    getSeriesName(seriesId) {
      const series = this.seriesList.find(s => s.id === seriesId);
      return series ? series.name : '';
    },

    playSermon(sermon) {
      // Implement audio player functionality
      console.log('Playing sermon:', sermon);
    },

    editSermon(sermon) {
      this.editingSermon = sermon;
      this.sermonForm = { ...sermon };
      this.showSermonModal = true;
    },

    async saveSermon() {
      try {
        const url = this.editingSermon 
          ? `/api/sermons/${this.sermonForm.id}`
          : '/api/sermons';
        
        const method = this.editingSermon ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
          method,
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.sermonForm)
        });

        if (!response.ok) throw new Error('Failed to save sermon');

        this.showSermonModal = false;
        await this.fetchSermons();
      } catch (error) {
        console.error('Error saving sermon:', error);
        alert('Failed to save sermon. Please try again.');
      }
    },

    async deleteSermon(sermonId) {
      if (!confirm('Are you sure you want to delete this sermon?')) return;
      
      try {
        const response = await fetch(`/api/sermons/${sermonId}`, { 
          method: 'DELETE' 
        });
        
        if (!response.ok) throw new Error('Failed to delete sermon');
        
        await this.fetchSermons();
      } catch (error) {
        console.error('Error deleting sermon:', error);
        alert('Failed to delete sermon. Please try again.');
      }
    }
  }));
});
</script>
