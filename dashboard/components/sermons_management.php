<?php
if (!isset($sermons) || !is_array($sermons)) $sermons = [];
// $sermons should be provided by the section handler
?>
<div class="space-y-6">
  <h2 class="text-2xl font-bold text-gray-800">Sermons Management</h2>
  <a href="/hearts-after-god-ministry-site/backend/sermons/create_sermon.php" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
    <i data-lucide="plus" class="w-4 h-4"></i>
    New Sermon
  </a>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="min-w-full">
      <thead>
        <tr>
          <th class="px-4 py-2">Title</th>
          <th class="px-4 py-2">Speaker</th>
          <th class="px-4 py-2">Date</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sermons as $sermon): ?>
        <tr>
          <td class="px-4 py-2"><?= htmlspecialchars($sermon['title']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($sermon['speaker']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($sermon['sermon_date']) ?></td>
          <td class="px-4 py-2">
            <a href="/hearts-after-god-ministry-site/backend/sermons/edit_sermon.php?id=<?= $sermon['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
            |
            <a href="/hearts-after-god-ministry-site/backend/sermons/delete_sermon.php?id=<?= $sermon['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Delete this sermon?')">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($sermons)): ?>
        <tr>
          <td colspan="4" class="text-center text-gray-400 py-4">No sermons found.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
