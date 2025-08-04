<div class="space-y-6" x-data="eventsManagement()" x-init="init()">
  <!-- Header -->
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-bold text-gray-800">Events Management</h2>
    <button @click="showEventModal = true" class="btn-primary">
      <i data-lucide="plus" class="w-4 h-4"></i> New Event
    </button>
  </div>

  <!-- Search and Filter -->
  <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
    <div class="flex flex-col md:flex-row gap-4">
      <div class="relative flex-1">
        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
        <input type="text" x-model="searchQuery" @input="filterEvents()" 
               class="pl-10 w-full" placeholder="Search events...">
      </div>
      <div class="flex gap-2">
        <select x-model="filterStatus" @change="filterEvents()" class="text-sm">
          <option value="all">All Events</option>
          <option value="upcoming">Upcoming</option>
          <option value="past">Past</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Events List -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <template x-for="event in paginatedEvents" :key="event.id">
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i :data-lucide="getEventIcon(event.category)" class="w-5 h-5 text-blue-600"></i>
                  </div>
                  <div class="ml-4">
                    <div class="font-medium text-gray-900" x-text="event.title"></div>
                    <div class="text-sm text-gray-500" x-text="event.location"></div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div x-text="formatDate(event.start_date)"></div>
                <div class="text-sm text-gray-500" x-text="formatTime(event.start_date, event.end_date)"></div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(event)" x-text="getStatusText(event)" class="px-2 py-1 rounded-full text-xs"></span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="editEvent(event)" class="text-blue-600 hover:text-blue-900 mr-3">
                  <i data-lucide="edit" class="w-4 h-4"></i>
                </button>
                <button @click="deleteEvent(event.id)" class="text-red-600 hover:text-red-900">
                  <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
              </td>
            </tr>
          </template>
          <tr x-show="filteredEvents.length === 0">
            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
              No events found. Create your first event to get started.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-3 flex items-center justify-between border-t border-gray-200">
      <div class="text-sm text-gray-700">
        Showing <span x-text="(currentPage - 1) * itemsPerPage + 1"></span> to 
        <span x-text="Math.min(currentPage * itemsPerPage, filteredEvents.length)"></span> of 
        <span x-text="filteredEvents.length"></span> results
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

  <!-- Event Modal -->
  <div x-show="showEventModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="fixed inset-0 bg-black/50" @click="showEventModal = false"></div>
    <div class="flex min-h-full items-center justify-center p-4">
      <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-lg p-6" @click.stop>
        <button @click="showEventModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
        
        <h3 class="text-lg font-medium text-gray-900 mb-6" x-text="editingEvent ? 'Edit Event' : 'Create New Event'"></h3>
        
        <form @submit.prevent="saveEvent" class="space-y-6">
          <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <!-- Event Title -->
            <div class="sm:col-span-6">
              <label for="event-title" class="form-label">Event Title</label>
              <input type="text" id="event-title" x-model="eventForm.title" class="form-input" required>
            </div>

            <!-- Date and Time -->
            <div class="sm:col-span-3">
              <label for="event-date" class="form-label">Date</label>
              <input type="date" id="event-date" x-model="eventForm.date" class="form-input" required>
            </div>
            <div class="sm:col-span-3">
              <label for="event-time" class="form-label">Time</label>
              <input type="time" id="event-time" x-model="eventForm.time" class="form-input" required>
            </div>

            <!-- Location -->
            <div class="sm:col-span-6">
              <label for="event-location" class="form-label">Location</label>
              <input type="text" id="event-location" x-model="eventForm.location" class="form-input" required>
            </div>

            <!-- Category -->
            <div class="sm:col-span-3">
              <label for="event-category" class="form-label">Category</label>
              <select id="event-category" x-model="eventForm.category" class="form-select" required>
                <option value="service">Church Service</option>
                <option value="bible-study">Bible Study</option>
                <option value="prayer">Prayer Meeting</option>
                <option value="outreach">Outreach</option>
                <option value="special">Special Event</option>
              </select>
            </div>

            <!-- Status -->
            <div class="sm:col-span-3">
              <label for="event-status" class="form-label">Status</label>
              <select id="event-status" x-model="eventForm.status" class="form-select" required>
                <option value="upcoming">Upcoming</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <!-- Description -->
            <div class="sm:col-span-6">
              <label for="event-description" class="form-label">Description</label>
              <textarea id="event-description" x-model="eventForm.description" rows="3" class="form-textarea"></textarea>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button type="button" @click="showEventModal = false" class="btn-secondary">
              Cancel
            </button>
            <button type="submit" class="btn-primary">
              <span x-text="editingEvent ? 'Update Event' : 'Create Event'"></span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('eventsManagement', () => ({
    events: [],
    filteredEvents: [],
    searchQuery: '',
    filterStatus: 'all',
    showEventModal: false,
    editingEvent: null,
    currentPage: 1,
    itemsPerPage: 10,
    
    eventForm: {
      id: null,
      title: '',
      description: '',
      date: '',
      time: '19:00',
      location: '',
      category: 'service',
      status: 'upcoming'
    },

    init() {
      this.fetchEvents();
      this.updateDateTime();
      setInterval(() => this.updateDateTime(), 60000); // Update time every minute
    },

    async fetchEvents() {
      try {
        // Replace with actual API call
        const response = await fetch('/api/events');
        this.events = await response.json();
        this.filterEvents();
      } catch (error) {
        console.error('Error fetching events:', error);
      }
    },

    filterEvents() {
      this.currentPage = 1; // Reset to first page when filters change
      
      this.filteredEvents = this.events.filter(event => {
        const matchesSearch = !this.searchQuery || 
          event.title.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          event.description.toLowerCase().includes(this.searchQuery.toLowerCase());
        
        const matchesStatus = this.filterStatus === 'all' || 
          (this.filterStatus === 'upcoming' && new Date(event.end_date) > new Date()) ||
          (this.filterStatus === 'past' && new Date(event.end_date) <= new Date());
        
        return matchesSearch && matchesStatus;
      });
    },

    get paginatedEvents() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredEvents.slice(start, start + this.itemsPerPage);
    },

    get totalPages() {
      return Math.ceil(this.filteredEvents.length / this.itemsPerPage);
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });
    },

    formatTime(start, end) {
      const startTime = new Date(start).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
      });
      
      if (!end) return startTime;
      
      const endTime = new Date(end).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
      });
      
      return `${startTime} - ${endTime}`;
    },

    getStatusClass(event) {
      const now = new Date();
      const endDate = new Date(event.end_date);
      const startDate = new Date(event.start_date);
      
      if (event.status === 'cancelled') return 'bg-gray-100 text-gray-800';
      if (now > endDate) return 'bg-green-100 text-green-800';
      if (now >= startDate && now <= endDate) return 'bg-blue-100 text-blue-800';
      return 'bg-yellow-100 text-yellow-800';
    },

    getStatusText(event) {
      if (event.status === 'cancelled') return 'Cancelled';
      
      const now = new Date();
      const endDate = new Date(event.end_date);
      const startDate = new Date(event.start_date);
      
      if (now > endDate) return 'Completed';
      if (now >= startDate && now <= endDate) return 'Ongoing';
      return 'Upcoming';
    },

    getEventIcon(category) {
      const icons = {
        'service': 'church',
        'bible-study': 'book-open',
        'prayer': 'pray',
        'outreach': 'users',
        'special': 'star'
      };
      return icons[category] || 'calendar';
    },

    editEvent(event) {
      this.editingEvent = event;
      this.eventForm = {
        id: event.id,
        title: event.title,
        description: event.description || '',
        date: event.start_date.split('T')[0],
        time: new Date(event.start_date).toTimeString().substring(0, 5),
        location: event.location || '',
        category: event.category || 'service',
        status: event.status || 'upcoming'
      };
      this.showEventModal = true;
    },

    async saveEvent() {
      try {
        const eventData = {
          ...this.eventForm,
          start_date: `${this.eventForm.date}T${this.eventForm.time}:00`,
          end_date: this.calculateEndDate()
        };

        // Replace with actual API call
        const method = this.editingEvent ? 'PUT' : 'POST';
        const url = this.editingEvent 
          ? `/api/events/${this.eventForm.id}`
          : '/api/events';

        const response = await fetch(url, {
          method,
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(eventData)
        });

        if (!response.ok) throw new Error('Failed to save event');

        this.showEventModal = false;
        this.fetchEvents(); // Refresh the events list
      } catch (error) {
        console.error('Error saving event:', error);
        alert('Failed to save event. Please try again.');
      }
    },

    calculateEndDate() {
      // Default to 1 hour after start time
      const start = new Date(`${this.eventForm.date}T${this.eventForm.time}:00`);
      start.setHours(start.getHours() + 1);
      return start.toISOString();
    },

    async deleteEvent(eventId) {
      if (!confirm('Are you sure you want to delete this event?')) return;
      
      try {
        // Replace with actual API call
        const response = await fetch(`/api/events/${eventId}`, { method: 'DELETE' });
        if (!response.ok) throw new Error('Failed to delete event');
        this.fetchEvents(); // Refresh the events list
      } catch (error) {
        console.error('Error deleting event:', error);
        alert('Failed to delete event. Please try again.');
      }
    },

    updateDateTime() {
      const now = new Date();
      this.currentTime = now.toLocaleTimeString();
      this.currentDate = now.toLocaleDateString();
    }
  }));
});
</script>
