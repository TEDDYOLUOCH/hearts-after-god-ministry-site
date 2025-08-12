<?php
if (!isset($images) || !is_array($images)) $images = [];
?>
<div class="space-y-6">
  <h2 class="text-2xl font-bold text-gray-800">Gallery Management</h2>
  <a href="/hearts-after-god-ministry-site/backend/gallery/upload_image.php" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
    <i data-lucide="plus" class="w-4 h-4"></i>
    Upload Image
  </a>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <?php foreach ($images as $img): ?>
      <div class="bg-white rounded shadow p-2 flex flex-col items-center">
        <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="<?= htmlspecialchars($img['title']) ?>" class="w-full h-40 object-cover rounded mb-2">
        <div class="text-sm text-gray-700"><?= htmlspecialchars($img['title']) ?></div>
        <div class="mt-2 flex gap-2">
          <a href="/hearts-after-god-ministry-site/backend/gallery/edit_image.php?id=<?= $img['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
          <a href="/hearts-after-god-ministry-site/backend/gallery/delete_image.php?id=<?= $img['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Delete this image?')">Delete</a>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($images)): ?>
      <div class="col-span-4 text-center text-gray-400">No images in the gallery yet.</div>
    <?php endif; ?>
  </div>
</div>
