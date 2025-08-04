<?php
require_once __DIR__ . '/includes/standard_layout.php';
require_once __DIR__ . '/includes/blog_functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /hearts-after-god-ministry-site/backend/users/login.php');
    exit;
}

// Database connection
$db = require __DIR__ . '/../config/db.php';

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    try {
        $action = $_GET['action'] ?? 'list';
        $response = ['success' => false];
        
        switch ($action) {
            case 'list':
                $posts = getBlogPosts($db);
                ob_start();
                include __DIR__ . '/includes/views/blog_list.php';
                $response = [
                    'success' => true,
                    'html' => ob_get_clean()
                ];
                break;
                
            case 'form':
                $post = null;
                $postId = $_GET['id'] ?? null;
                
                if ($postId) {
                    $post = getBlogPost($db, $postId);
                    if (!$post) {
                        throw new Exception('Post not found');
                    }
                }
                
                ob_start();
                include __DIR__ . '/includes/views/blog_form.php';
                $response = [
                    'success' => true,
                    'html' => ob_get_clean(),
                    'title' => $post ? 'Edit Blog Post' : 'Add New Blog Post'
                ];
                break;
                
            case 'save':
                $postData = [
                    'title' => $_POST['title'] ?? '',
                    'slug' => $_POST['slug'] ?? '',
                    'content' => $_POST['content'] ?? '',
                    'excerpt' => $_POST['excerpt'] ?? '',
                    'status' => $_POST['status'] ?? 'draft',
                    'featured_image' => $_FILES['featured_image'] ?? null,
                    'author_id' => $_SESSION['user_id']
                ];
                
                if (isset($_POST['id'])) {
                    $postId = (int)$_POST['id'];
                    $result = updateBlogPost($db, $postId, $postData);
                    $message = 'Post updated successfully';
                } else {
                    $result = createBlogPost($db, $postData);
                    $message = 'Post created successfully';
                }
                
                if ($result) {
                    $response = [
                        'success' => true,
                        'message' => $message,
                        'redirect' => '?section=blog'
                    ];
                } else {
                    throw new Exception('Failed to save post');
                }
                break;
                
            case 'delete':
                $postId = (int)$_POST['id'];
                if (deleteBlogPost($db, $postId)) {
                    $response = [
                        'success' => true,
                        'message' => 'Post deleted successfully'
                    ];
                } else {
                    throw new Exception('Failed to delete post');
                }
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
    
    echo json_encode($response);
    exit;
}

// Render the standard layout with blog content
renderStandardLayout('Blog Management', function() use ($db) {
    // This content will be replaced by JavaScript in the SPA
    ?>
    <div x-data="{
        loading: true,
        posts: [],
        error: null,
        
        async loadPosts() {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await fetch('?section=blog&ajax=1&action=list');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('dynamic-content').innerHTML = data.html;
                } else {
                    throw new Error(data.message || 'Failed to load posts');
                }
            } catch (err) {
                this.error = err.message || 'An error occurred while loading posts';
                console.error('Error loading posts:', err);
            } finally {
                this.loading = false;
            }
        },
        
        async loadPostForm(postId = null) {
            try {
                const url = postId 
                    ? `?section=blog&ajax=1&action=form&id=${postId}`
                    : '?section=blog&ajax=1&action=form';
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success) {
                    // Show modal with form
                    this.showModal(data.html, data.title || 'Blog Post');
                    this.initializeFormValidation();
                } else {
                    throw new Error(data.message || 'Failed to load form');
                }
            } catch (err) {
                this.showError(err.message || 'An error occurred');
                console.error('Error loading form:', err);
            }
        },
        
        async savePost(formData) {
            try {
                const response = await fetch('?section=blog&ajax=1&action=save', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showSuccess(data.message);
                    this.closeModal();
                    this.loadPosts();
                    
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    }
                } else {
                    throw new Error(data.message || 'Failed to save post');
                }
            } catch (err) {
                this.showError(err.message || 'An error occurred while saving');
                console.error('Error saving post:', err);
            }
        },
        
        async deletePost(postId) {
            if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('id', postId);
                
                const response = await fetch('?section=blog&ajax=1&action=delete', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showSuccess(data.message);
                    this.loadPosts();
                } else {
                    throw new Error(data.message || 'Failed to delete post');
                }
            } catch (err) {
                this.showError(err.message || 'An error occurred while deleting');
                console.error('Error deleting post:', err);
            }
        },
        
        showModal(content, title = '') {
            const modal = document.createElement('div');
            modal.id = 'modal';
            modal.className = 'fixed inset-0 z-50 overflow-auto bg-black/50 flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col">
                    <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">${title}</h3>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>
                    <div class="p-6 overflow-y-auto flex-1">
                        ${content}
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            lucide.createIcons();
            
            // Close modal when clicking outside
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal();
                }
            });
            
            // Close with Escape key
            const handleEscape = (e) => {
                if (e.key === 'Escape') {
                    this.closeModal();
                }
            };
            
            document.addEventListener('keydown', handleEscape);
            this.currentModal = { element: modal, handleEscape };
        },
        
        closeModal() {
            if (this.currentModal) {
                document.removeEventListener('keydown', this.currentModal.handleEscape);
                this.currentModal.element.remove();
                this.currentModal = null;
            }
        },
        
        showSuccess(message) {
            this.showAlert(message, 'success');
        },
        
        showError(message) {
            this.showAlert(message, 'error');
        },
        
        showAlert(message, type = 'info') {
            const alert = document.createElement('div');
            alert.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
                type === 'success' ? 'bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500' : 
                type === 'error' ? 'bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500' :
                'bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-500'
            }`;
            
            alert.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i data-lucide="${
                            type === 'success' ? 'check-circle' : 
                            type === 'error' ? 'alert-circle' : 'info'
                        }" class="w-5 h-5 ${
                            type === 'success' ? 'text-green-500' : 
                            type === 'error' ? 'text-red-500' : 'text-blue-500'
                        }"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium ${
                            type === 'success' ? 'text-green-800 dark:text-green-200' : 
                            type === 'error' ? 'text-red-800 dark:text-red-200' : 
                            'text-blue-800 dark:text-blue-200'
                        }">
                            ${message}
                        </p>
                    </div>
                    <div class="ml-4">
                        <button @click="this.parentElement.parentElement.remove()" class="inline-flex text-gray-400 focus:outline-none">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(alert);
            lucide.createIcons();
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (document.body.contains(alert)) {
                    alert.remove();
                }
            }, 5000);
        },
        
        initializeFormValidation() {
            const form = document.querySelector('#post-form');
            if (!form) return;
            
            // Slug generation from title
            const titleInput = form.querySelector('[name="title"]');
            const slugInput = form.querySelector('[name="slug"]');
            
            if (titleInput && slugInput && !slugInput.value) {
                titleInput.addEventListener('blur', () => {
                    if (!slugInput.value) {
                        const slug = titleInput.value
                            .toLowerCase()
                            .replace(/[^\w\s-]/g, '')
                            .replace(/\s+/g, '-')
                            .replace(/--+/g, '-');
                        slugInput.value = slug;
                    }
                });
            }
            
            // Form submission
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                // Basic validation
                if (!formData.get('title') || !formData.get('content')) {
                    this.showError('Title and content are required');
                    return;
                }
                
                this.savePost(formData);
            });
        },
        
        // Initialize when component is mounted
        init() {
            this.loadPosts();
        }
    }" x-init="init()">
        <!-- Page header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Blog Posts</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage your blog posts and content
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                <button @click="loadPostForm()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    New Post
                </button>
            </div>
        </div>
        
        <!-- Error message -->
        <div x-show="error" class="mb-6">
            <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 dark:text-red-300" x-text="error"></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Loading state -->
        <div x-show="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        </div>
        
        <!-- Blog posts will be loaded here -->
        <div id="dynamic-content"></div>
    </div>
    <?php
});
