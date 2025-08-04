<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    exit;
}

// Include database configuration
require_once __DIR__ . '/../../config/db.php';

try {
    // Get database connection
    $pdo = getDbConnection();
    
    // Initialize variables
    $feedback = '';
    $feedback_type = '';
    $upload_dir = __DIR__ . '/../../uploads/sermons/';

// Create uploads directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete_id'])) {
        try {
            // Get sermon details before deleting to remove associated files
            $stmt = $pdo->prepare("SELECT audio_url, video_url, thumbnail_url FROM sermons WHERE id = ?");
            $stmt->execute([$_POST['delete_id']]);
            $sermon = $stmt->fetch();
            
            // Delete associated files
            if ($sermon) {
                if ($sermon['audio_url'] && file_exists('../../' . $sermon['audio_url'])) {
                    unlink('../../' . $sermon['audio_url']);
                }
                if ($sermon['video_url'] && file_exists('../../' . $sermon['video_url'])) {
                    unlink('../../' . $sermon['video_url']);
                }
                if ($sermon['thumbnail_url'] && file_exists('../../' . $sermon['thumbnail_url'])) {
                    unlink('../../' . $sermon['thumbnail_url']);
                }
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM sermons WHERE id = ?");
            $stmt->execute([$_POST['delete_id']]);
            $feedback = "Sermon deleted successfully.";
            $feedback_type = "success";
        } catch (Exception $e) {
            $feedback = "Failed to delete sermon: " . $e->getMessage();
            $feedback_type = "error";
        }
    } elseif (!empty($_POST['title'])) {
        try {
            // Handle file uploads
            $audio_url = '';
            $video_url = '';
            $thumbnail_url = '';
            
            // Upload audio file
            if (!empty($_FILES['audio_file']['name'])) {
                $audio_ext = strtolower(pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION));
                $audio_filename = 'sermon_audio_' . time() . '.' . $audio_ext;
                $audio_target = $upload_dir . $audio_filename;
                if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $audio_target)) {
                    $audio_url = 'uploads/sermons/' . $audio_filename;
                }
            }
            
            // Upload video file
            if (!empty($_FILES['video_file']['name'])) {
                $video_ext = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
                $video_filename = 'sermon_video_' . time() . '.' . $video_ext;
                $video_target = $upload_dir . $video_filename;
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $video_target)) {
                    $video_url = 'uploads/sermons/' . $video_filename;
                }
            }
            
            // Upload thumbnail image
            if (!empty($_FILES['thumbnail']['name'])) {
                $thumbnail_ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
                $thumbnail_filename = 'sermon_thumb_' . time() . '.' . $thumbnail_ext;
                $thumbnail_target = $upload_dir . $thumbnail_filename;
                if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnail_target)) {
                    $thumbnail_url = 'uploads/sermons/' . $thumbnail_filename;
                }
            }
            
            // Prepare base query and parameters
            $columns = [];
            $placeholders = [];
            $params = [];
            
            // Add required fields
            $columns[] = 'title';
            $params[] = $_POST['title'];
            $placeholders[] = '?';
            
            // Add optional fields if they exist in the form
            if (isset($_POST['preacher'])) {
                $columns[] = 'preacher';
                $params[] = $_POST['preacher'];
                $placeholders[] = '?';
            }
            
            if (isset($_POST['bible_reference'])) {
                $columns[] = 'bible_reference';
                $params[] = $_POST['bible_reference'];
                $placeholders[] = '?';
            }
            
            if (isset($_POST['sermon_date'])) {
                $columns[] = 'sermon_date';
                $params[] = $_POST['sermon_date'];
                $placeholders[] = '?';
            }
            
            if (isset($_POST['description'])) {
                $columns[] = 'description';
                $params[] = $_POST['description'];
                $placeholders[] = '?';
            }
            
            // Add file URLs if they exist
            if ($audio_url) {
                $columns[] = 'audio_url';
                $params[] = $audio_url;
                $placeholders[] = '?';
            }
            
            if ($video_url) {
                $columns[] = 'video_url';
                $params[] = $video_url;
                $placeholders[] = '?';
            }
            
            if ($thumbnail_url) {
                $columns[] = 'thumbnail_url';
                $params[] = $thumbnail_url;
                $placeholders[] = '?';
            }
            
            // Build and execute the query
            $columnsStr = implode(', ', $columns);
            $placeholdersStr = implode(', ', $placeholders);
            $sql = "INSERT INTO sermons ($columnsStr) VALUES ($placeholdersStr)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $feedback = "Sermon added successfully.";
            $feedback_type = "success";
        } catch (Exception $e) {
            $feedback = "Failed to add sermon: " . $e->getMessage();
            $feedback_type = "error";
        }
    }
}

    // Function to check and update database schema if needed
if (!function_exists('ensureSermonsTableSchema')) {
function ensureSermonsTableSchema($pdo) {
    try {
        // Check if table exists
        $tableExists = $pdo->query("SHOW TABLES LIKE 'sermons'")->rowCount() > 0;
        
        if (!$tableExists) {
            // Create table with all required columns
            $pdo->exec("
                CREATE TABLE sermons (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    preacher VARCHAR(255) NOT NULL,
                    bible_reference VARCHAR(100),
                    sermon_date DATE NOT NULL,
                    audio_url VARCHAR(255),
                    video_url VARCHAR(255),
                    description TEXT,
                    thumbnail_url VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            return true;
        }
        
        // Check and add missing columns
        $columns = [
            'preacher' => "ADD COLUMN IF NOT EXISTS preacher VARCHAR(255) NOT NULL AFTER title",
            'bible_reference' => "ADD COLUMN IF NOT EXISTS bible_reference VARCHAR(100) AFTER preacher",
            'sermon_date' => "ADD COLUMN IF NOT EXISTS sermon_date DATE NOT NULL AFTER bible_reference",
            'audio_url' => "ADD COLUMN IF NOT EXISTS audio_url VARCHAR(255) AFTER sermon_date",
            'video_url' => "ADD COLUMN IF NOT EXISTS video_url VARCHAR(255) AFTER audio_url",
            'description' => "ADD COLUMN IF NOT EXISTS description TEXT AFTER video_url",
            'thumbnail_url' => "ADD COLUMN IF NOT EXISTS thumbnail_url VARCHAR(255) AFTER description"
        ];
        
        foreach ($columns as $column => $alterSql) {
            $columnExists = $pdo->query("SHOW COLUMNS FROM sermons LIKE '$column'")->rowCount() === 0;
            if ($columnExists) {
                $pdo->exec("ALTER TABLE sermons $alterSql");
            }
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error ensuring sermons table schema: " . $e->getMessage());
        return false;
    }
}
} // Close the function_exists block

// Ensure database schema is up to date
ensureSermonsTableSchema($pdo);

// Fetch all sermons
try {
    // Check if sermon_date column exists for sorting
    $dateColumnExists = $pdo->query("SHOW COLUMNS FROM sermons LIKE 'sermon_date'")->rowCount() > 0;
    $orderBy = $dateColumnExists ? 'sermon_date' : 'created_at';
    $sermons = $pdo->query("SELECT * FROM sermons ORDER BY $orderBy DESC")->fetchAll();
} catch (Exception $e) {
    $feedback = "Error fetching sermons: " . $e->getMessage();
    $feedback_type = "error";
    $sermons = [];
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Sermons</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <script>
    function openModal(id) {
      document.getElementById('modal-' + id).classList.remove('hidden');
    }
    function closeModal(id) {
      document.getElementById('modal-' + id).classList.add('hidden');
    }
    
    function previewThumbnail(input) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('thumbnail-preview').src = e.target.result;
          document.getElementById('thumbnail-preview').classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
      }
    }
  </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
  <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 py-6">
    <!-- Page Header -->
    <div class="mb-6">
      <div class="flex flex-col sm:flex-row justify-between items-start gap-3">
        <div class="space-y-0.5">
          <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Sermon Management</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Manage your sermons and media files</p>
        </div>
        <a href="?page=sermons&new=1" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 text-sm rounded-md transition-colors text-center font-medium inline-flex items-center justify-center space-x-1.5">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          <span>New Sermon</span>
        </a>
      </div>
      
      <?php if (!empty($feedback)): ?>
        <div class="mt-4 p-3 rounded <?= $feedback_type === 'success' ? 'bg-green-50 text-green-800 border border-green-100' : 'bg-red-50 text-red-800 border border-red-100' ?> flex items-start">
          <div class="flex-shrink-0">
            <?php if ($feedback_type === 'success'): ?>
              <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            <?php else: ?>
              <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            <?php endif; ?>
          </div>
          <div class="ml-3">
            <p class="text-sm"><?= htmlspecialchars($feedback) ?></p>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Add sermon form -->
    <form method="POST" action="/hearts-after-god-ministry-site/backend/users/admin_manage_sermon.php" enctype="multipart/form-data" class="mb-8 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white"><?= isset($_GET['edit']) ? 'Edit' : 'Add New' ?> Sermon</h2>
        <a href="?page=sermons" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Sermons
        </a>
      </div>
      
      <div class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" placeholder="Sermon title" value="<?= htmlspecialchars($sermon['title'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" required>
          </div>
          
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preacher <span class="text-red-500">*</span></label>
            <input type="text" name="preacher" placeholder="Preacher's name" value="<?= htmlspecialchars($sermon['preacher'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" required>
          </div>
          
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bible Reference</label>
            <input type="text" name="bible_reference" placeholder="e.g. John 3:16" value="<?= htmlspecialchars($sermon['bible_reference'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
          </div>
          
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date <span class="text-red-500">*</span></label>
            <input type="date" name="sermon_date" value="<?= isset($sermon['sermon_date']) ? date('Y-m-d', strtotime($sermon['sermon_date'])) : '' ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" required>
          </div>
        </div>
        
        <div class="space-y-1">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
          <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Sermon description or notes"><?= htmlspecialchars($sermon['description'] ?? '') ?></textarea>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Audio File</label>
            <input type="file" name="audio_file" accept="audio/*" class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-300 dark:hover:file:bg-blue-900/50">
            <?php if (!empty($sermon['audio_url'])): ?>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Current: <?= basename($sermon['audio_url']) ?></p>
            <?php endif; ?>
          </div>
          
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Video File</label>
            <input type="file" name="video_file" accept="video/*" class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-300 dark:hover:file:bg-blue-900/50">
            <?php if (!empty($sermon['video_url'])): ?>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Current: <?= basename($sermon['video_url']) ?></p>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="space-y-1">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thumbnail Image</label>
          <div class="flex items-center gap-4">
            <div class="flex-1">
              <input type="file" name="thumbnail" accept="image/*" onchange="previewThumbnail(this)" class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-300 dark:hover:file:bg-blue-900/50">
            </div>
            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-md overflow-hidden border border-gray-200 dark:border-gray-600 flex items-center justify-center">
              <?php if (!empty($sermon['thumbnail_url'])): ?>
                <img id="thumbnail-preview" src="/hearts-after-god-ministry-site/<?= htmlspecialchars($sermon['thumbnail_url']) ?>" class="w-full h-full object-cover">
              <?php else: ?>
                <img id="thumbnail-preview" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%239CA3AF' viewBox='0 0 24 24'%3E%3Cpath d='M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z'/%3E%3Cpath d='M14.14 11.86l-3 3.87L9 13.14 6 18h12l-3.86-5.14z'/%3E%3C/svg%3E" class="w-10 h-10 opacity-50">
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <div class="pt-2">
          <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white py-2.5 px-6 rounded-md transition-colors text-sm font-medium flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <?= isset($_GET['edit']) ? 'Update' : 'Add' ?> Sermon
          </button>
        </div>
      </div>
    </form>

    <!-- Sermons table -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Sermons</h3>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Thumbnail</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Preacher</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (empty($sermons)): ?>
              <tr>
                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                  <div class="py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No sermons</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by adding a new sermon.</p>
                    <div class="mt-6">
                      <a href="?page=sermons&new=1" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        New Sermon
                      </a>
                    </div>
                  </div>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($sermons as $sermon): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <?php if (!empty($sermon['thumbnail_url'])): ?>
                      <img src="/hearts-after-god-ministry-site/<?= htmlspecialchars($sermon['thumbnail_url']) ?>" alt="Sermon thumbnail" class="w-12 h-12 rounded-md object-cover">
                    <?php else: ?>
                      <div class="w-12 h-12 bg-gray-100 dark:bg-gray-600 rounded-md flex items-center justify-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($sermon['title']) ?></div>
                    <?php if (!empty($sermon['bible_reference'])): ?>
                      <div class="text-xs text-blue-600 dark:text-blue-400"><?= htmlspecialchars($sermon['bible_reference']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                    <?= htmlspecialchars($sermon['preacher']) ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                    <?= date('M j, Y', strtotime($sermon['sermon_date'])) ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                    <?php if (!empty($sermon['audio_url'])): ?>
                      <a href="/hearts-after-god-ministry-site/<?= htmlspecialchars($sermon['audio_url']) ?>" class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Download Audio" download>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                        <span class="sr-only">Audio</span>
                      </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($sermon['video_url'])): ?>
                      <a href="/hearts-after-god-ministry-site/<?= htmlspecialchars($sermon['video_url']) ?>" class="inline-flex items-center text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Watch Video" download>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span class="sr-only">Video</span>
                      </a>
                    <?php endif; ?>
                    
                    <a href="?page=sermons&edit=<?= $sermon['id'] ?>" class="inline-flex items-center text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Edit">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      <span class="sr-only">Edit</span>
                    </a>
                    
                    <form action="" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this sermon? This action cannot be undone.');">
                      <input type="hidden" name="delete_id" value="<?= $sermon['id'] ?>">
                      <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span class="sr-only">Delete</span>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
                    <?php endif; ?>
                    <button onclick="openModal(<?= $sermon['id'] ?>)" class="text-red-600 hover:text-red-800" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>

                  <!-- Delete Confirmation Modal -->
                  <div id="modal-<?= $sermon['id'] ?>" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                    <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
                      <h2 class="text-lg font-semibold mb-2 text-red-600">Confirm Deletion</h2>
                      <p class="mb-4 text-sm">Are you sure you want to delete <strong><?= htmlspecialchars($sermon['title']) ?></strong>? This action cannot be undone.</p>
                      <form method="POST" action="admin_manage_sermon.php" class="flex gap-2">
                        <input type="hidden" name="delete_id" value="<?= $sermon['id'] ?>">
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition flex items-center gap-2">
                          <i class="fas fa-trash"></i> Delete
                        </button>
                        <button type="button" onclick="closeModal(<?= $sermon['id'] ?>)" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400 transition">
                          Cancel
                        </button>
                      </form>
                    </div>
                  </div>
                  <!-- End Modal -->
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

     <a href="../../dashboard/admin-dashboard.php" class="text-blue-700 hover:underline">← Back to Dashboard</a>
        </div>
    </div>
</div>

<?php
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
