/**
 * Blog Posts Manager
 * Handles the blog posts list and interactions
 */

class BlogPostsManager {
    constructor() {
        this.postsContainer = document.getElementById('blog-posts-container');
        this.paginationContainer = document.getElementById('pagination-container');
        this.searchInput = document.querySelector('input[name="search"]');
        this.statusFilter = document.querySelector('select[name="status"]');
        this.categoryFilter = document.querySelector('select[name="category"]');
        this.bulkActionsForm = document.getElementById('bulk-actions-form');
        this.selectAllCheckbox = document.getElementById('select-all-posts');
        
        this.currentPage = 1;
        this.perPage = 10;
        this.totalPosts = 0;
        this.isLoading = false;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadPosts();
    }
    
    setupEventListeners() {
        // Search with debounce
        if (this.searchInput) {
            let searchTimeout;
            this.searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.currentPage = 1;
                    this.loadPosts();
                }, 500);
            });
        }
        
        // Filters
        if (this.statusFilter) {
            this.statusFilter.addEventListener('change', () => {
                this.currentPage = 1;
                this.loadPosts();
            });
        }
        
        if (this.categoryFilter) {
            this.categoryFilter.addEventListener('change', () => {
                this.currentPage = 1;
                this.loadPosts();
            });
        }
        
        // Bulk actions
        if (this.bulkActionsForm) {
            this.bulkActionsForm.addEventListener('submit', (e) => this.handleBulkAction(e));
        }
        
        // Select all checkbox
        if (this.selectAllCheckbox) {
            this.selectAllCheckbox.addEventListener('change', (e) => {
                const checkboxes = document.querySelectorAll('.post-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
            });
        }
        
        // Listen for custom events
        document.addEventListener('blog:refresh', () => this.loadPosts());
    }
    
    async loadPosts() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading();
        
        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                search: this.searchInput ? this.searchInput.value : '',
                status: this.statusFilter ? this.statusFilter.value : '',
                category: this.categoryFilter ? this.categoryFilter.value : ''
            });
            
            const response = await fetch(`/hearts-after-god-ministry-site/backend/api/blog/posts?${params}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            this.totalPosts = data.pagination.total;
            this.renderPosts(data.data);
            this.renderPagination(data.pagination);
            this.updateStats(data.stats || {});
            
        } catch (error) {
            console.error('Error loading posts:', error);
            this.showError('Failed to load blog posts. Please try again.');
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }
    
    renderPosts(posts) {
        if (!this.postsContainer) return;
        
        if (!posts || posts.length === 0) {
            this.postsContainer.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No blog posts found.
                    </td>
                </tr>
            `;
            return;
        }
        
        this.postsContainer.innerHTML = posts.map(post => `
            <tr class="border-b border-gray-200 hover:bg-gray-50" data-post-id="${post.id}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" name="post_ids[]" value="${post.id}" class="post-checkbox h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        ${post.featured_image ? `
                            <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-md object-cover" src="/${post.featured_image}" alt="">
                            </div>
                        ` : ''}
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${this.escapeHtml(post.title)}</div>
                            <div class="text-sm text-gray-500">${this.formatDate(post.created_at)}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        ${post.status === 'published' ? 'bg-green-100 text-green-800' : 
                          post.status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 
                          'bg-gray-100 text-gray-800'}">
                        ${post.status.charAt(0).toUpperCase() + post.status.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${post.categories ? post.categories.map(cat => 
                        `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                            ${this.escapeHtml(cat.name)}
                        </span>`
                    ).join('') : ''}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="?edit=${post.id}" class="text-primary-600 hover:text-primary-900 mr-3">Edit</a>
                    <a href="#" class="text-red-600 hover:text-red-900 delete-post" data-id="${post.id}">Delete</a>
                </td>
            </tr>
        `).join('');
        
        // Add event listeners to delete buttons
        document.querySelectorAll('.delete-post').forEach(button => {
            button.addEventListener('click', (e) => this.handleDeletePost(e));
        });
    }
    
    renderPagination(pagination) {
        if (!this.paginationContainer) return;
        
        const { current_page, last_page } = pagination;
        
        if (last_page <= 1) {
            this.paginationContainer.innerHTML = '';
            return;
        }
        
        let paginationHtml = `
            <nav class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0">
                <div class="-mt-px w-0 flex-1 flex">
                    ${current_page > 1 ? `
                        <button onclick="window.blogPostsManager.goToPage(${current_page - 1})" class="border-t-2 border-transparent pt-4 pr-1 inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <svg class="mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Previous
                        </button>
                    ` : ''}
                </div>
                <div class="hidden md:-mt-px md:flex">
        `;
        
        // Page numbers
        for (let i = 1; i <= last_page; i++) {
            if (i === 1 || i === last_page || (i >= current_page - 2 && i <= current_page + 2)) {
                paginationHtml += `
                    <button onclick="window.blogPostsManager.goToPage(${i})" class="${i === current_page ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'} border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium">
                        ${i}
                    </button>
                `;
            } else if (i === current_page - 3 || i === current_page + 3) {
                paginationHtml += `
                    <span class="border-transparent text-gray-500 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium">
                        ...
                    </span>
                `;
            }
        }
        
        paginationHtml += `
                </div>
                <div class="-mt-px w-0 flex-1 flex justify-end">
                    ${current_page < last_page ? `
                        <button onclick="window.blogPostsManager.goToPage(${current_page + 1})" class="border-t-2 border-transparent pt-4 pl-1 inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Next
                            <svg class="ml-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    ` : ''}
                </div>
            </nav>
        `;
        
        this.paginationContainer.innerHTML = paginationHtml;
    }
    
    updateStats(stats) {
        // Update stats counters
        const totalPostsEl = document.getElementById('total-posts');
        const publishedPostsEl = document.getElementById('published-posts');
        const draftPostsEl = document.getElementById('draft-posts');
        
        if (totalPostsEl) totalPostsEl.textContent = stats.total || 0;
        if (publishedPostsEl) publishedPostsEl.textContent = stats.published || 0;
        if (draftPostsEl) draftPostsEl.textContent = stats.draft || 0;
        
        // Update progress bars
        const total = stats.total || 1;
        const publishedPercentage = Math.round(((stats.published || 0) / total) * 100);
        const draftPercentage = Math.round(((stats.draft || 0) / total) * 100);
        
        const publishedProgress = document.querySelector('.published-progress');
        const draftProgress = document.querySelector('.draft-progress');
        
        if (publishedProgress) {
            publishedProgress.style.width = `${publishedPercentage}%`;
            publishedProgress.setAttribute('aria-valuenow', publishedPercentage);
        }
        
        if (draftProgress) {
            draftProgress.style.width = `${draftPercentage}%`;
            draftProgress.setAttribute('aria-valuenow', draftPercentage);
        }
    }
    
    goToPage(page) {
        if (page < 1 || page > Math.ceil(this.totalPosts / this.perPage)) return;
        this.currentPage = page;
        this.loadPosts();
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    async handleDeletePost(e) {
        e.preventDefault();
        
        const postId = e.currentTarget.getAttribute('data-id');
        if (!postId) return;
        
        if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
            return;
        }
        
        try {
            const response = await fetch(`/hearts-after-god-ministry-site/backend/api/blog/posts/${postId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Post deleted successfully', 'success');
                
                // Trigger custom event
                document.dispatchEvent(new CustomEvent('blog:postDeleted'));
                
                // Reload posts
                this.loadPosts();
            } else {
                throw new Error(result.message || 'Failed to delete post');
            }
            
        } catch (error) {
            console.error('Error deleting post:', error);
            this.showNotification(`Error: ${error.message}`, 'error');
        }
    }
    
    async handleBulkAction(e) {
        e.preventDefault();
        
        const formData = new FormData(this.bulkActionsForm);
        const action = formData.get('bulk_action');
        const postIds = Array.from(document.querySelectorAll('.post-checkbox:checked')).map(cb => cb.value);
        
        if (postIds.length === 0) {
            this.showNotification('Please select at least one post', 'warning');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} the selected ${postIds.length} post(s)?`)) {
            return;
        }
        
        try {
            const response = await fetch('/hearts-after-god-ministry-site/backend/api/blog/posts/bulk', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: action,
                    post_ids: postIds
                }),
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification(`Successfully ${action}ed ${postIds.length} post(s)`, 'success');
                
                // Trigger custom event
                document.dispatchEvent(new CustomEvent('blog:postsBulkUpdated'));
                
                // Reload posts
                this.loadPosts();
                
                // Uncheck select all
                if (this.selectAllCheckbox) {
                    this.selectAllCheckbox.checked = false;
                }
            } else {
                throw new Error(result.message || `Failed to ${action} posts`);
            }
            
        } catch (error) {
            console.error(`Error performing bulk ${action}:`, error);
            this.showNotification(`Error: ${error.message}`, 'error');
        }
    }
    
    showLoading() {
        // Show loading indicator
        if (this.postsContainer) {
            this.postsContainer.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center">
                        <div class="flex justify-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Loading posts...</p>
                    </td>
                </tr>
            `;
        }
    }
    
    hideLoading() {
        // Hide loading indicator if needed
    }
    
    showError(message) {
        if (this.postsContainer) {
            this.postsContainer.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-red-600">
                        <div class="flex items-center justify-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            ${this.escapeHtml(message)}
                        </div>
                        <button onclick="window.blogPostsManager.loadPosts()" class="mt-2 px-4 py-2 text-sm text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Retry
                        </button>
                    </td>
                </tr>
            `;
        }
    }
    
    showNotification(message, type = 'info', options = {}) {
        // Use existing notification system if available
        if (typeof showNotification === 'function') {
            showNotification(message, type, options);
            return;
        }
        
        // Fallback notification
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg ${this.getNotificationClass(type)}`;
        notification.textContent = message;
        
        if (options.action) {
            const button = document.createElement('button');
            button.className = 'ml-2 text-sm font-medium text-white underline';
            button.textContent = options.action.text;
            button.onclick = () => {
                options.action.callback();
                notification.remove();
            };
            notification.appendChild(button);
        }
        
        document.body.appendChild(notification);
        
        // Auto-remove notification
        if (options.autoClose !== false) {
            setTimeout(() => {
                notification.remove();
            }, options.autoClose || 5000);
        }
    }
    
    getNotificationClass(type) {
        const classes = {
            success: 'bg-green-600 text-white',
            error: 'bg-red-600 text-white',
            warning: 'bg-yellow-500 text-white',
            info: 'bg-blue-600 text-white'
        };
        
        return classes[type] || classes.info;
    }
    
    escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
    formatDate(dateString) {
        if (!dateString) return '';
        
        const options = { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        
        return new Date(dateString).toLocaleDateString(undefined, options);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize on blog management page
    if (document.getElementById('blog-posts-container')) {
        window.blogPostsManager = new BlogPostsManager();
    }
});
