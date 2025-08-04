<?php
// Event Management Component
?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-bold text-gray-800">Event Management</h2>
    <button @click="showEventModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>
      Create Event
    </button>
  </div>

  <!-- Event Calendar View -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-900">Upcoming Events</h3>
      <div class="flex space-x-2">
        <button @click="view = 'list'" :class="{ 'bg-blue-50 text-blue-600': view === 'list' }" class="px-3 py-1.5 text-sm rounded-md hover:bg-gray-100">
          <i data-lucide="list" class="w-4 h-4 inline mr-1"></i> List
        </button>
        <button @click="view = 'calendar'" :class="{ 'bg-blue-50 text-blue-600': view === 'calendar' }" class="px-3 py-1.5 text-sm rounded-md hover:bg-gray-100">
          <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i> Calendar
        </button>
      </div>
    </div>
    
    <!-- List View -->
    <div x-show="view === 'list'" class="divide-y divide-gray-200">
      <?php
      try {
        // Fetch events from the database
        $stmt = $pdo->query("
          SELECT * FROM events 
          WHERE event_date >= CURDATE() 
          ORDER BY event_date ASC, start_time ASC
          LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($stmt) > 0) {
          foreach ($stmt as $event) {
            $eventDate = new DateTime($event['event_date']);
            $startTime = new DateTime($event['start_time']);
            $endTime = new DateTime($event['end_time']);
            
            echo "
            <div class='p-6 hover:bg-gray-50 transition-colors'>
              <div class='flex flex-col md:flex-row md:items-center'>
                <div class='flex-shrink-0 w-20 text-center border-r border-gray-200 pr-4'>
                  <div class='text-sm font-semibold text-blue-600'>" . $eventDate->format('M') . "</div>
                  <div class='text-2xl font-bold text-gray-900'>" . $eventDate->format('d') . "</div>
                  <div class='text-xs text-gray-500'>" . $eventDate->format('D') . "</div>
                </div>
                <div class='mt-4 md:mt-0 md:ml-6 flex-1'>
                  <div class='flex items-center justify-between'>
                    <h4 class='text-lg font-semibold text-gray-900'>" . htmlspecialchars($event['title']) . "</h4>
                    <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800'>
                      " . $startTime->format('g:i A') . ' - ' . $endTime->format('g:i A') . "
                    </span>
                  </div>
                  <p class='mt-1 text-sm text-gray-500 line-clamp-2'>" . htmlspecialchars($event['description']) . "</p>
                  <div class='mt-2 flex items-center text-sm text-gray-500'>
                    <i data-lucide='map-pin' class='w-4 h-4 mr-1.5'></i>
                    " . htmlspecialchars($event['location']) . "
                  </div>
                </div>
                <div class='mt-4 md:mt-0 md:ml-6 flex items-center space-x-2'>
                  <button @click='editEvent('" . $event['id'] . "')' class='p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-full'>
                    <i data-lucide='edit' class='w-4 h-4'></i>
                  </button>
                  <button @click='deleteEvent('" . $event['id'] . "')' class='p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-full'>
                    <i data-lucide='trash-2' class='w-4 h-4'></i>
                  </button>
                </div>
              </div>
            </div>";
          }
        } else {
          echo "
          <div class='p-8 text-center text-gray-500'>
            <i data-lucide='calendar-x' class='w-12 h-12 mx-auto text-gray-300 mb-2'></i>
            <p>No upcoming events found. Create your first event to get started.</p>
          </div>";
        }
      } catch (PDOException $e) {
        echo "
        <div class='p-8 text-center text-red-500'>
          <i data-lucide='alert-circle' class='w-12 h-12 mx-auto mb-2'></i>
          <p>Error loading events: " . htmlspecialchars($e->getMessage()) . "</p>
        </div>";
      }
      ?>
    </div>
    
    <!-- Calendar View (Placeholder) -->
    <div x-show="view === 'calendar'" class="p-6">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="text-center text-gray-500 mb-4">
          Calendar view will be implemented here
        </div>
      </div>
    </div>
    
    <!-- View All Events -->
    <div class="p-4 border-t border-gray-200 text-center">
      <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-800">View all events</a>
    </div>
  </div>
  
  <!-- Past Events -->
  <div class="mt-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Past Events</h3>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-6">
        <?php
        try {
          // Fetch past events from the database
          $stmt = $pdo->query("
            SELECT * FROM events 
            WHERE event_date < CURDATE() 
            ORDER BY event_date DESC
            LIMIT 5
          ")->fetchAll(PDO::FETCH_ASSOC);
          
          if (count($stmt) > 0) {
            echo "<div class='space-y-4'>";
            foreach ($stmt as $event) {
              $eventDate = new DateTime($event['event_date']);
              echo "
              <div class='flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg'>
                <div class='flex items-center'>
                  <div class='flex-shrink-0 w-12 text-center'>
                    <div class='text-xs text-gray-500'>" . $eventDate->format('M j') . "</div>
                  </div>
                  <div class='ml-4'>
                    <h4 class='text-sm font-medium text-gray-900'>" . htmlspecialchars($event['title']) . "</h4>
                    <div class='flex items-center text-xs text-gray-500'>
                      <i data-lucide='map-pin' class='w-3 h-3 mr-1'></i>
                      " . htmlspecialchars($event['location']) . "
                    </div>
                  </div>
                </div>
                <button class='text-blue-600 hover:text-blue-800 text-sm font-medium'>View Details</button>
              </div>";
            }
            echo "</div>";
          } else {
            echo "<p class='text-sm text-gray-500'>No past events found.</p>";
          }
        } catch (PDOException $e) {
          echo "<p class='text-sm text-red-500'>Error loading past events: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
      </div>
    </div>
  </div>
</div>

<!-- Event Modal -->
<div x-show="showEventModal" 
     x-transition:enter="ease-out duration-300" 
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100" 
     x-transition:leave="ease-in duration-200" 
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true"
     style="display: none;"
     x-cloak>
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div x-show="showEventModal" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
         aria-hidden="true"
         @click="showEventModal = false">
    </div>
    
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    
    <div x-show="showEventModal" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
         class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
      <div>
        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
          <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
            Create New Event
          </h3>
          <div class="mt-5">
            <form id="eventForm" class="space-y-4">
              <div>
                <label for="eventTitle" class="block text-sm font-medium text-gray-700">Event Title</label>
                <input type="text" id="eventTitle" name="title" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="eventDate" class="block text-sm font-medium text-gray-700">Date</label>
                  <input type="date" id="eventDate" name="date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label for="startTime" class="block text-sm font-medium text-gray-700">Start Time</label>
                    <input type="time" id="startTime" name="startTime" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                  </div>
                  <div>
                    <label for="endTime" class="block text-sm font-medium text-gray-700">End Time</label>
                    <input type="time" id="endTime" name="endTime" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                  </div>
                </div>
              </div>
              
              <div>
                <label for="eventLocation" class="block text-sm font-medium text-gray-700">Location</label>
                <input type="text" id="eventLocation" name="location" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
              </div>
              
              <div>
                <label for="eventDescription" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="eventDescription" name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
              </div>
              
              <div>
                <label for="eventImage" class="block text-sm font-medium text-gray-700">Event Image</label>
                <div class="mt-1 flex items-center">
                  <span class="inline-block h-12 w-12 rounded-full overflow-hidden bg-gray-100">
                    <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                  </span>
                  <button type="button" class="ml-5 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Change
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
        <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
          Create Event
        </button>
        <button @click="showEventModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
          Cancel
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('eventManagement', () => ({
    view: 'list',
    showEventModal: false,
    events: [],
    
    init() {
      // This would be replaced with an actual API call
      this.events = [
        {
          id: 1,
          title: 'Sunday Service',
          date: '2023-06-20',
          startTime: '10:00:00',
          endTime: '12:00:00',
          location: 'Main Sanctuary',
          description: 'Weekly Sunday worship service with communion.'
        },
        // Add more sample events as needed
      ];
    },
    
    editEvent(id) {
      const event = this.events.find(e => e.id === id);
      if (event) {
        // Populate form with event data
        document.getElementById('eventTitle').value = event.title;
        document.getElementById('eventDate').value = event.date;
        document.getElementById('startTime').value = event.startTime;
        document.getElementById('endTime').value = event.endTime;
        document.getElementById('eventLocation').value = event.location;
        document.getElementById('eventDescription').value = event.description;
        
        // Update modal title and button text
        document.getElementById('modal-title').textContent = 'Edit Event';
        document.querySelector('[type="submit"]').textContent = 'Update Event';
        
        // Show the modal
        this.showEventModal = true;
      }
    },
    
    deleteEvent(id) {
      if (confirm('Are you sure you want to delete this event?')) {
        // This would be replaced with an actual API call
        this.events = this.events.filter(event => event.id !== id);
      }
    },
    
    formatTime(timeString) {
      if (!timeString) return '';
      const [hours, minutes] = timeString.split(':');
      const hour = parseInt(hours, 10);
      const ampm = hour >= 12 ? 'PM' : 'AM';
      const hour12 = hour % 12 || 12;
      return `${hour12}:${minutes} ${ampm}`;
    }
  }));
});
</script>
