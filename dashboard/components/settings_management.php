<?php
if (!isset($settings) || !is_array($settings)) $settings = [];
?>
<div class="space-y-6">
  <h2 class="text-2xl font-bold text-gray-800">Settings Management</h2>
  <form action="/hearts-after-god-ministry-site/backend/settings/update_settings.php" method="post" class="space-y-4 bg-white rounded-xl shadow p-6 max-w-lg">
    <div>
      <label class="block text-gray-700 font-medium mb-2">Site Name</label>
      <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" class="w-full border rounded px-3 py-2" required>
    </div>
    <div>
      <label class="block text-gray-700 font-medium mb-2">Contact Email</label>
      <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" class="w-full border rounded px-3 py-2" required>
    </div>
    <!-- Add more settings fields as needed -->
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Settings</button>
  </form>
</div>
<script>
document.querySelector('form[action*="update_settings.php"]').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    const response = await fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const result = await response.json();

    if (result.success) {
        // Reload the settings section via AJAX (assuming Alpine.js or your JS framework)
        if (window.$store && $store.app && $store.app.loadSection) {
            $store.app.loadSection('settings');
        } else {
            location.reload(); // fallback
        }
    } else {
        alert(result.message || 'Failed to update settings.');
    }
});
</script>