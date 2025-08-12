<?php
<div class="space-y-6 p-6">
  <div class="flex justify-between items-center">
    <div>
      <h2 class="text-2xl font-bold text-gray-800">Events Management</h2>
      <p class="text-gray-600 mt-1">Schedule and manage ministry events</p>
    </div>
    <button id="openCreateEventModal" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
      <i data-lucide="calendar-plus" class="w-5 h-5"></i>
      Create Event
    </button>
  </div>

  <!-- Calendar View -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div id="calendar"></div>
  </div>

  <!-- Upcoming Events -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Events</h3>
    <div class="space-y-4">
      <?php foreach ($upcomingEvents as $event): ?>
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center space-x-4">
          <div class="flex-shrink-0 w-16 text-center">
            <span class="text-lg font-bold text-gray-900"><?= date('d', strtotime($event['start_date'])) ?></span>
            <span class="text-sm text-gray-500"><?= date('M', strtotime($event['start_date'])) ?></span>
          </div>
          <div>
            <h4 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($event['title']) ?></h4>
            <p class="text-sm text-gray-500"><?= htmlspecialchars($event['location']) ?></p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <button class="text-blue-600 hover:text-blue-800" onclick="editEvent(<?= $event['id'] ?>)">
            <i data-lucide="edit" class="w-5 h-5"></i>
          </button>
          <button class="text-red-600 hover:text-red-800" onclick="deleteEvent(<?= $event['id'] ?>)">
            <i data-lucide="trash-2" class="w-5 h-5"></i>
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>