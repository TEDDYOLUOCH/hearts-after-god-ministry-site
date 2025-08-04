<?php
// Session is already started in admin-dashboard.php

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    exit;
}

require_once __DIR__ . '/../../backend/config/db.php';

// Set the page title for the header
$pageTitle = 'Manage Team Members';
$userName = $_SESSION['user_name'] ?? 'Admin';

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                case 'edit':
                    $name = trim($_POST['name']);
                    $role = trim($_POST['role']);
                    $bio = trim($_POST['bio']);
                    $display_order = (int)$_POST['display_order'];
                    $is_active = isset($_POST['is_active']) ? 1 : 0;
                    
                    // Handle file upload
                    $image_url = $_POST['existing_image'] ?? '';
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = __DIR__ . '/../../assets/images/team/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $file_name = 'member_' . time() . '_' . uniqid() . '.' . $file_extension;
                        $target_path = $upload_dir . $file_name;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            $image_url = 'assets/images/team/' . $file_name;
                            
                            // Delete old image if it exists and is not the default
                            if (!empty($_POST['existing_image']) && $_POST['existing_image'] !== 'assets/images/team/placeholder.jpg') {
                                @unlink(__DIR__ . '/../../' . $_POST['existing_image']);
                            }
                        }
                    }
                    
                    $data = [
                        'name' => $name,
                        'role' => $role,
                        'bio' => $bio,
                        'image_url' => $image_url,
                        'facebook_url' => $_POST['facebook_url'] ?? '',
                        'twitter_url' => $_POST['twitter_url'] ?? '',
                        'instagram_url' => $_POST['instagram_url'] ?? '',
                        'linkedin_url' => $_POST['linkedin_url'] ?? '',
                        'display_order' => $display_order,
                        'is_active' => $is_active
                    ];
                    
                    if ($_POST['action'] === 'add') {
                        $sql = "INSERT INTO team_members (" . implode(', ', array_keys($data)) . ") 
                                VALUES (:" . implode(', :', array_keys($data)) . ")";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($data);
                        $message = 'Team member added successfully!';
                    } else {
                        $data['id'] = $_POST['id'];
                        $sql = "UPDATE team_members SET ";
                        $updates = [];
                        foreach ($data as $key => $value) {
                            if ($key !== 'id') {
                                $updates[] = "$key = :$key";
                            }
                        }
                        $sql .= implode(', ', $updates) . " WHERE id = :id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($data);
                        $message = 'Team member updated successfully!';
                    }
                    break;
                    
                case 'delete':
                    // First, get the image path to delete the file
                    $stmt = $pdo->prepare("SELECT image_url FROM team_members WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $member = $stmt->fetch();
                    
                    // Delete the team member
                    $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    
                    // Delete the associated image file if it exists and is not the default
                    if ($member && !empty($member['image_url']) && $member['image_url'] !== 'assets/images/team/placeholder.jpg') {
                        @unlink(__DIR__ . '/../../' . $member['image_url']);
                    }
                    
                    $message = 'Team member deleted successfully!';
                    break;
            }
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Fetch all team members
$stmt = $pdo->query("SELECT * FROM team_members ORDER BY display_order, name");
$teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Team - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Success/Error Messages -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Add/Edit Form -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Add New Team Member
                </h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <form id="memberForm" action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="memberId">
                    <input type="hidden" name="existing_image" id="existingImage">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Profile Image</label>
                            <div class="mt-1 flex items-center">
                                <span class="inline-block h-24 w-24 rounded-full overflow-hidden bg-gray-100 mr-4">
                                    <img id="imagePreview" src="/assets/images/team/placeholder.jpg" alt="Preview" class="h-full w-full object-cover">
                                </span>
                                <input type="file" id="image" name="image" accept="image/*" class="text-sm text-gray-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                            <input type="text" id="name" name="name" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        </div>
                        
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">Role/Position *</label>
                            <input type="text" id="role" name="role" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                            <textarea id="bio" name="bio" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"></textarea>
                        </div>
                        
                        <div>
                            <label for="facebook_url" class="block text-sm font-medium text-gray-700">Facebook URL</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    <i class="fab fa-facebook-f"></i>
                                </span>
                                <input type="url" id="facebook_url" name="facebook_url"
                                       class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="twitter_url" class="block text-sm font-medium text-gray-700">Twitter URL</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    <i class="fab fa-twitter"></i>
                                </span>
                                <input type="url" id="twitter_url" name="twitter_url"
                                       class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="instagram_url" class="block text-sm font-medium text-gray-700">Instagram URL</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    <i class="fab fa-instagram"></i>
                                </span>
                                <input type="url" id="instagram_url" name="instagram_url"
                                       class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="linkedin_url" class="block text-sm font-medium text-gray-700">LinkedIn URL</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    <i class="fab fa-linkedin-in"></i>
                                </span>
                                <input type="url" id="linkedin_url" name="linkedin_url"
                                       class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="display_order" class="block text-sm font-medium text-gray-700">Display Order</label>
                            <input type="number" id="display_order" name="display_order" value="0"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        </div>
                        
                        <div class="flex items-center">
                            <input id="is_active" name="is_active" type="checkbox" checked
                                   class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Active
                            </label>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Cancel
                        </button>
                        <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Team Members List -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Team Members</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($teamMembers as $member): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="h-10 w-10 rounded-full overflow-hidden">
                                        <img src="/<?= htmlspecialchars($member['image_url'] ?? 'assets/images/team/placeholder.jpg') ?>" 
                                             alt="<?= htmlspecialchars($member['name']) ?>" 
                                             class="h-full w-full object-cover">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($member['name']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($member['role']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $member['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $member['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $member['display_order'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="editMember(<?= htmlspecialchars(json_encode($member)) ?>)" 
                                            class="text-indigo-600 hover:text-indigo-900 mr-4">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button onclick="confirmDelete(<?= $member['id'] ?>, '<?= addslashes($member['name']) ?>')" 
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($teamMembers)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No team members found. Click "Add Team Member" to get started.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Confirm Deletion</h3>
                <button onclick="closeDeleteModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-gray-600 mb-6">Are you sure you want to delete <span id="deleteMemberName" class="font-semibold"></span>? This action cannot be undone.</p>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" class="inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteMemberId">
                    <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Edit member function
        function editMember(member) {
            // Set form action to edit
            document.getElementById('formAction').value = 'edit';
            
            // Set member ID
            document.getElementById('memberId').value = member.id;
            
            // Fill in form fields
            document.querySelector('input[name="name"]').value = member.name || '';
            document.querySelector('input[name="role"]').value = member.role || '';
            document.querySelector('textarea[name="bio"]').value = member.bio || '';
            document.querySelector('input[name="facebook_url"]').value = member.facebook_url || '';
            document.querySelector('input[name="twitter_url"]').value = member.twitter_url || '';
            document.querySelector('input[name="linkedin_url"]').value = member.linkedin_url || '';
            document.querySelector('input[name="display_order"]').value = member.display_order || '0';
            
            // Set image preview if exists
            const imagePreview = document.getElementById('imagePreview');
            if (member.image_url) {
                imagePreview.src = '/' + member.image_url;
                imagePreview.style.display = 'block';
                document.getElementById('existingImage').value = member.image_url;
            } else {
                imagePreview.src = '';
                imagePreview.style.display = 'none';
                document.getElementById('existingImage').value = '';
            }
            
            // Update form title
            document.querySelector('h3').textContent = 'Edit Team Member';
            
            // Scroll to form
            document.getElementById('memberForm').scrollIntoView({ behavior: 'smooth' });
            
            // Reset file input
            document.querySelector('input[type="file"]').value = '';
        }
        
        // Reset form for new member
        function resetForm() {
            document.getElementById('memberForm').reset();
            document.getElementById('formAction').value = 'add';
            document.getElementById('memberId').value = '';
            document.getElementById('existingImage').value = '';
            document.getElementById('imagePreview').src = '';
            document.getElementById('imagePreview').style.display = 'none';
            document.querySelector('h3').textContent = 'Add New Team Member';
        }
        
        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Delete modal functions
        function confirmDelete(id, name) {
            document.getElementById('deleteMemberId').value = id;
            document.getElementById('deleteMemberName').textContent = name;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }
        
        // Reset form when clicking "Add New" button
        document.querySelector('button[onclick="resetForm()"]')?.addEventListener('click', resetForm);
        
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
