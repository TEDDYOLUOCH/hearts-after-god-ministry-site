document.addEventListener('DOMContentLoaded', function() {
  // Initialize Calendar
  const calendarEl = document.getElementById('calendar');
  if (calendarEl) {
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      events: '/api/events',
      editable: true,
      selectable: true,
      select: function(info) {
        // Handle date selection
      },
      eventClick: function(info) {
        // Handle event click
      }
    });
    calendar.render();
  }

  // Initialize charts
  initializeCharts();
  
  // Initialize other dashboard features
  initializeDashboard();
});

function initializeCharts() {
  const ctx = document.getElementById('statsChart');
  if (ctx) {
      new Chart(ctx, {
          type: 'line',
          data: {
              labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
              datasets: [{
                  label: 'Website Views',
                  data: [12, 19, 3, 5, 2, 3],
                  borderColor: 'rgb(59, 130, 246)',
                  tension: 0.1
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                  legend: {
                      position: 'bottom'
                  }
              }
          }
      });
  }
}

function initializeDashboard() {
  // Add other dashboard initialization code here
  console.log('Dashboard initialized');
}

// Initialize Notifications
initializeNotifications();

function initializeNotifications() {
  const bell = document.querySelector('[data-lucide="bell"]');
  if (bell) {
    bell.addEventListener('click', async () => {
      const notifications = await fetchNotifications();
      // Show notifications popup
    });
  }
}

// Add more dashboard functionality here