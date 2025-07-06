// assets/js/admin-realtime.js
// Real-time admin integration using localStorage polling

(function() {
  const backend = window.DiscipleshipBackend;

  // Update user table
  function updateUserTable() {
    const users = backend.getAllUsersWithProgress();
    const userTableBody = document.getElementById('user-table-body');
    if (!userTableBody) return;
    userTableBody.innerHTML = users.map(user => backend.renderUserRow(user)).join('');
  }

  // Update stats
  function updateStats() {
    const stats = backend.getSystemStats();
    document.querySelectorAll('[data-stat]').forEach(el => {
      const stat = el.getAttribute('data-stat');
      if (stats[stat] !== undefined) {
        el.textContent = stats[stat];
      }
    });
  }

  // Update recent activity
  function updateActivityLog() {
    const activityLog = backend.getRecentActivity(20);
    const activityContainer = document.getElementById('admin-activity-log');
    if (!activityContainer) return;
    activityContainer.innerHTML = activityLog.map(log => `
      <div class="py-1 px-2 border-b text-xs text-gray-600">
        <span class="font-semibold">${log.type.replace(/_/g, ' ').toUpperCase()}</span>:
        <span>${log.data?.userId || ''}</span>
        <span class="text-gray-400">${new Date(log.timestamp).toLocaleString()}</span>
      </div>
    `).join('');
  }

  // Update certificates table
  function updateCertificateTable() {
    const certificates = backend.getAllCertificates();
    const certTableBody = document.getElementById('certificate-table-body');
    if (!certTableBody) return;
    certTableBody.innerHTML = certificates.map(cert => backend.renderCertificateRow(cert)).join('');
  }

  // Update support tickets table
  function updateSupportTable() {
    const tickets = backend.getAllSupportTickets();
    const supportTableBody = document.getElementById('support-table-body');
    if (!supportTableBody) return;
    supportTableBody.innerHTML = tickets.map(ticket => {
      return `<tr><td>${ticket.id}</td><td>${ticket.user?.name || ''}</td><td>${ticket.issue || ''}</td><td>${ticket.category || ''}</td><td>${ticket.priority || ''}</td><td>${ticket.status || ''}</td><td>${new Date(ticket.createdDate).toLocaleString()}</td><td><!-- Actions --></td></tr>`;
    }).join('');
  }

  // Polling function
  function poll() {
    updateUserTable();
    updateStats();
    updateActivityLog();
    updateCertificateTable();
    updateSupportTable();
  }

  // Initial load
  poll();
  // Poll every 3 seconds
  setInterval(poll, 3000);
})(); 