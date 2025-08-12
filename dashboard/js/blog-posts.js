/**
 * Blog Posts Management
 * Handles fetching, displaying, and managing blog posts
 */

// Global variable to store blog posts
let blogPosts = [];

// API base URL
const API_BASE_URL = '/hearts-after-god-ministry-site/backend/api';

// Default pagination settings
const DEFAULT_PAGE_SIZE = 10;

/**
 * Shows a loading state
 * @param {string} selector - CSS selector for the loading element
 */
function showLoading(selector = '#loading-indicator') {
    const loadingElement = document.querySelector(selector);
    if (loadingElement) {
        loadingElement.style.display = 'block';
    }
}

/**
 * Hides the loading state
 * @param {string} selector - CSS selector for the loading element
 */
function hideLoading(selector = '#loading-indicator') {
    const loadingElement = document.querySelector(selector);
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }
}

/**
 * Shows a notification to the user
 * @param {string} message - The message to display
 * @param {string} type - The type of notification (success, error, info, warning)
 */
function showNotification(message, type = 'info') {
    // Check if notification function exists in parent scope
    if (typeof window.showNotification === 'function') {
        window.showNotification(message, type);
    } else {
        // Fallback notification
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg text-white ${
            type === 'error' ? 'bg-red-500' : 
            type === 'success' ? 'bg-green-500' :
            type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
        }`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

/**
 * Fetches blog posts from the API with pagination and filtering
 * @param {Object} options - Options for fetching posts
 * @param {number} options.page - Page number (1-based)
 * @param {number} options.perPage - Number of posts per page
 * @param {string} options.status - Filter by status (draft, published, archived)
 * @param {string} options.search - Search term for title/content
 * @param {number} options.category - Filter by category ID
 * @returns {Promise<Object>} - Object containing posts and pagination info
 */
async function fetchBlogPosts({
    page = 1,
    perPage = DEFAULT_PAGE_SIZE,
    status = '',
    search = '',
    category = ''
} = {}) {
    try {
        showLoading();
        
        // Build query parameters
        const params = new URLSearchParams({
            page: Math.max(1, parseInt(page)),
            per_page: Math.max(1, Math.min(100, parseInt(perPage)))
        });
        
        if (status) params.append('status', status);
        if (search) params.append('search', search);
        if (category) params.append('category', category);
        
        const url = `${API_BASE_URL}/blog/posts.php?${params.toString()}`;
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            },
            credentials: 'same-origin' // Include cookies for auth
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            // Map the API response to our expected format
            blogPosts = result.data.map(post => ({
                id: post.id,
                title: post.title,
                slug: post.slug,
                excerpt: post.excerpt || (post.content ? post.content.substring(0, 150) + '...' : ''),
                content: post.content,
                status: post.status || 'draft',
                author: post.author_name || post.author_username || 'Admin',
                author_id: post.author_id || 1,
                created_at: post.created_at || new Date().toISOString(),
                updated_at: post.updated_at || null,
                published_at: post.published_at || null,
                featured_image: post.featured_image ? 
                    (post.featured_image.startsWith('http') ? 
                        post.featured_image : 
                        `/hearts-after-god-ministry-site/${post.featured_image.replace(/^\//, '')}`) : 
                    null,
                categories: post.categories || [],
                meta_title: post.meta_title || post.title,
                meta_description: post.meta_description || '',
                meta_keywords: post.meta_keywords || '',
                views: post.views || 0
            }));
            
            // Update the UI
            renderBlogPosts();
            updateBlogStats(result.pagination || {});
            
            return {
                posts: blogPosts,
                pagination: result.pagination || {
                    total: blogPosts.length,
                    per_page: perPage,
                    current_page: 1,
                    total_pages: 1
                }
            };
        } else {
            throw new Error(result.message || 'Failed to load blog posts');
        }
    } catch (error) {
        console.error('Error fetching blog posts:', error);
        showNotification(`Error loading blog posts: ${error.message}`, 'error');
        return { posts: [], pagination: null };
    } finally {
        hideLoading();
    }
}

/**
 * Renders blog posts in the table
 * @param {Array} posts - Optional array of posts to render (defaults to global blogPosts)
 */
function renderBlogPosts(posts = blogPosts) {
    const tbody = document.querySelector('#blog-posts-table tbody');
    const paginationEl = document.querySelector('#pagination');
    const searchInput = document.querySelector('#search-posts');
    const statusFilter = document.querySelector('#status-filter');
    const categoryFilter = document.querySelector('#category-filter');
    
    if (!tbody) return;
    
    // Show loading state
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="py-8 text-center text-gray-500">
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                </div>
                <p class="mt-2">Loading posts...</p>
            </td>
        </tr>
    `;
    
    // If no posts provided, fetch them
    if ((!posts || posts.length === 0) && blogPosts.length === 0) {
        fetchBlogPosts({
            search: searchInput?.value || '',
            status: statusFilter?.value || '',
            category: categoryFilter?.value || ''
        }).then(data => {
            renderBlogPosts(data.posts);
            renderPagination(data.pagination);
        });
        return;
    }
    
    // Handle empty state
    if (!posts || posts.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No posts found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        ${searchInput?.value || statusFilter?.value || categoryFilter?.value ? 
                            'Try adjusting your search or filter criteria.' : 
                            'Get started by creating a new post.'}
                    </p>
                    <div class="mt-6">
                        <button type="button" onclick="showCreateBlogModal()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            New Post
                        </button>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    // Sort posts by date (newest first)
    const sortedPosts = [...posts].sort((a, b) => 
        new Date(b.created_at || 0) - new Date(a.created_at || 0)
    );
    
    // Render posts
    tbody.innerHTML = sortedPosts.map(post => `
        <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors duration-150" data-post-id="${post.id}">
            <td class="px-6 py-4">
                <div class="flex items-center">
                    ${post.featured_image ? `
                        <div class="flex-shrink-0 h-10 w-10 overflow-hidden rounded-md bg-gray-100">
                            <img class="h-full w-full object-cover" src="${post.featured_image}" alt="${post.title}" onerror="this.src='/hearts-after-god-ministry-site/assets/images/placeholder.png';">
                        </div>
                        <div class="ml-4">
                    ` : '<div class="ml-2">'}
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-900 truncate max-w-xs">
                                ${escapeHtml(post.title || 'Untitled Post')}
                            </span>
                            ${post.status === 'draft' ? 
                                '<span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Draft</span>' : 
                                post.status === 'archived' ?
                                '<span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Archived</span>' :
                                ''}
                        </div>
                        ${post.excerpt ? `
                            <div class="mt-1 text-sm text-gray-500 line-clamp-2">
                                ${escapeHtml(post.excerpt)}
                            </div>
                        ` : ''}
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${escapeHtml(post.author || 'Admin')}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full ${
                    post.status === 'published' ? 'bg-green-100 text-green-800' : 
                    post.status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 
                    'bg-gray-100 text-gray-800'
                }">
                    ${post.status ? (post.status.charAt(0).toUpperCase() + post.status.slice(1)) : 'Draft'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${post.created_at ? formatDate(post.created_at) : 'N/A'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${post.views ? post.views.toLocaleString() : '0'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-3">
                    <a href="/hearts-after-god-ministry-site/blog/${post.slug || post.id}" target="_blank" 
                       class="text-blue-600 hover:text-blue-900" title="View">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </a>
                    <button onclick="showEditBlogModal(${post.id})" 
                            class="text-indigo-600 hover:text-indigo-900" 
                            title="Edit">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button onclick="confirmDeletePost(${post.id})" 
                            class="text-red-600 hover:text-red-900" 
                            title="Delete">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    // Update counts
    updateBlogStats({
        total: posts.length,
        published: posts.filter(post => post.status === 'published').length,
        drafts: posts.filter(post => post.status === 'draft').length
    });
}

/**
 * Renders pagination controls
 * @param {Object} pagination - Pagination data
 */
function renderPagination(pagination) {
    const container = document.querySelector('#pagination');
    if (!container || !pagination) return;
    
    const { current_page, total_pages } = pagination;
    
    if (total_pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<nav class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0">';
    
    // Previous button
    html += `
        <div class="-mt-px w-0 flex-1 flex">
            <button onclick="loadPage(${current_page - 1})" 
                    class="border-t-2 border-transparent pt-4 pr-1 inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 ${current_page <= 1 ? 'opacity-50 cursor-not-allowed' : ''}"
                    ${current_page <= 1 ? 'disabled' : ''}>
                <svg class="mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Previous
            </button>
        </div>
    `;
    
    // Page numbers
    html += '<div class="hidden md:-mt-px md:flex">';
    
    const maxPagesToShow = 5;
    let startPage = Math.max(1, current_page - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(total_pages, startPage + maxPagesToShow - 1);
    
    if (endPage - startPage + 1 < maxPagesToShow) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }
    
    if (startPage > 1) {
        html += `
            <button onclick="loadPage(1)" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium">
                1
            </button>
        `;
        if (startPage > 2) {
            html += '<span class="border-transparent text-gray-500 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium">...</span>';
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <button onclick="loadPage(${i})" 
                    class="${i === current_page ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'} border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium">
                ${i}
            </button>
        `;
    }
    
    if (endPage < total_pages) {
        if (endPage < total_pages - 1) {
            html += '<span class="border-transparent text-gray-500 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium">...</span>';
        }
        html += `
            <button onclick="loadPage(${total_pages})" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium">
                ${total_pages}
            </button>
        `;
    }
    
    html += '</div>';
    
    // Next button
    html += `
        <div class="-mt-px w-0 flex-1 flex justify-end">
            <button onclick="loadPage(${current_page + 1})" 
                    class="border-t-2 border-transparent pt-4 pl-1 inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 ${current_page >= total_pages ? 'opacity-50 cursor-not-allowed' : ''}"
                    ${current_page >= total_pages ? 'disabled' : ''}>
                Next
                <svg class="ml-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    `;
    
    html += '</nav>';
    container.innerHTML = html;
}

/**
 * Loads a specific page of blog posts
 * @param {number} page - Page number to load
 */
function loadPage(page) {
    const searchInput = document.querySelector('#search-posts');
    const statusFilter = document.querySelector('#status-filter');
    const categoryFilter = document.querySelector('#category-filter');
    
    fetchBlogPosts({
        page: page,
        search: searchInput?.value || '',
        status: statusFilter?.value || '',
        category: categoryFilter?.value || ''
    }).then(data => {
        renderBlogPosts(data.posts);
        renderPagination(data.pagination);
        
        // Scroll to top of the table
        const table = document.querySelector('#blog-posts-table');
        if (table) {
            table.scrollIntoView({ behavior: 'smooth' });
        }
    });
}

/**
 * Escapes HTML special characters to prevent XSS
 * @param {string} text - The text to escape
 * @returns {string} The escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Fetches blog posts from the API with optional filtering and pagination
 * @param {Object} options - Options for fetching posts
 * @param {number} [options.page=1] - Page number
 * @param {string} [options.search=''] - Search term
 * @param {string} [options.status=''] - Filter by status (published, draft, archived)
 * @param {string|number} [options.category=''] - Filter by category ID or slug
 * @param {number} [options.perPage=10] - Number of posts per page
 * @returns {Promise<Object>} - Object containing posts and pagination info
 */
async function fetchBlogPosts({
    page = 1,
    search = '',
    status = '',
    category = '',
    perPage = 10
} = {}) {
    try {
        // Build query parameters
        const params = new URLSearchParams({
            page: page.toString(),
            per_page: perPage.toString()
        });
        
        if (search) params.append('search', search);
        if (status) params.append('status', status);
        if (category) params.append('category', category);
        
        // Show loading state
        const tbody = document.querySelector('#blog-posts-table tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-500">
                        <div class="flex justify-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                        </div>
                        <p class="mt-2">Loading posts...</p>
                    </td>
                </tr>
            `;
        }
        
        const response = await fetch(`/hearts-after-god-ministry-site/backend/api/blog/posts?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            const error = await response.json().catch(() => ({}));
            throw new Error(error.message || 'Failed to fetch blog posts');
        }
        
        const data = await response.json();
        
        // Update global blogPosts array
        blogPosts = data.data || [];
        
        // Return formatted response
        return {
            posts: blogPosts,
            pagination: {
                current_page: data.meta?.current_page || 1,
                total_pages: data.meta?.last_page || 1,
                total: data.meta?.total || 0,
                per_page: data.meta?.per_page || perPage
            }
        };
    } catch (error) {
        console.error('Error fetching blog posts:', error);
        
        // Show error state
        const tbody = document.querySelector('#blog-posts-table tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-8 text-center text-red-500">
                        <div class="flex flex-col items-center">
                            <svg class="h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-red-700">Error loading posts</h3>
                            <p class="mt-1 text-sm text-red-600">${escapeHtml(error.message || 'An unknown error occurred')}</p>
                            <button type="button" onclick="loadPage(1)" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                                Try Again
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
        
        return {
            posts: [],
            pagination: {
                current_page: 1,
                total_pages: 1,
                total: 0,
                per_page: perPage
            }
        };
    }
}

/**
 * Updates the blog stats in the UI
 * @param {Object} stats - Object containing blog statistics
 * @param {number} stats.total - Total number of posts
 * @param {number} stats.published - Number of published posts
 * @param {number} stats.drafts - Number of draft posts
 */
function updateBlogStats({ total = 0, published = 0, drafts = 0 } = {}) {
    // Update counters
    const totalEl = document.querySelector('#total-posts');
    const publishedEl = document.querySelector('#published-posts');
    const draftsEl = document.querySelector('#draft-posts');
    
    if (totalEl) {
        totalEl.textContent = total.toLocaleString();
        totalEl.setAttribute('title', `${total} total posts`);
    }
    
    if (publishedEl) {
        publishedEl.textContent = published.toLocaleString();
        publishedEl.setAttribute('title', `${published} published posts`);
    }
    
    if (draftsEl) {
        draftsEl.textContent = drafts.toLocaleString();
        draftsEl.setAttribute('title', `${drafts} draft posts`);
    }
    
    // Update progress bars if they exist
    const totalPosts = Math.max(1, total); // Avoid division by zero
    const publishedPercent = Math.round((published / totalPosts) * 100);
    const draftsPercent = Math.round((drafts / totalPosts) * 100);
    
    const publishedBar = document.querySelector('#published-progress');
    const draftsBar = document.querySelector('#drafts-progress');
    
    if (publishedBar) {
        publishedBar.style.width = `${publishedPercent}%`;
        publishedBar.setAttribute('aria-valuenow', publishedPercent);
        publishedBar.setAttribute('title', `${publishedPercent}% of all posts are published`);
    }
    
    if (draftsBar) {
        draftsBar.style.width = `${draftsPercent}%`;
        draftsBar.setAttribute('aria-valuenow', draftsPercent);
        draftsBar.setAttribute('title', `${draftsPercent}% of all posts are drafts`);
    }
    
    // Update last updated time
    const lastUpdatedEl = document.querySelector('#last-updated');
    if (lastUpdatedEl) {
        lastUpdatedEl.textContent = new Date().toLocaleTimeString();
        lastUpdatedEl.setAttribute('datetime', new Date().toISOString());
        lastUpdatedEl.setAttribute('title', `Last updated: ${new Date().toLocaleString()}`);
    }
}

/**
 * Initializes the blog posts functionality
 */
function initBlogPosts() {
    // Add event listener to the blog navigation link
    const blogNavLink = document.querySelector('a[data-section="blog"]');
    if (blogNavLink) {
        blogNavLink.addEventListener('click', function(e) {
            // Only fetch if we don't have posts yet
            if (blogPosts.length === 0) {
                fetchBlogPosts();
            }
        });
    }
    
    // Initial load if we're on the blog page
    if (window.location.hash === '#blog') {
        fetchBlogPosts();
    }
}

/**
 * Formats a date string into a readable format
 * @param {string} dateString - The date string to format
 * @returns {string} Formatted date string
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    
    try {
        return new Date(dateString).toLocaleDateString('en-US', options);
    } catch (e) {
        console.error('Error formatting date:', e);
        return 'Invalid date';
    }
}

/**
 * Updates the blog statistics in the UI
 * @param {number} total - Total number of posts
 * @param {number} published - Number of published posts
 * @param {number} drafts - Number of draft posts
 */
function updateBlogStats(total, published, drafts) {
    const totalEl = document.getElementById('total-blog-posts');
    const publishedEl = document.getElementById('published-blog-posts');
    const draftsEl = document.getElementById('draft-blog-posts');
    
    if (totalEl) totalEl.textContent = total || 0;
    if (publishedEl) publishedEl.textContent = published || 0;
    if (draftsEl) draftsEl.textContent = drafts || 0;
}

/**
 * Initializes the blog management interface
 */
function initBlogManagement() {
    // Initialize any UI components
    initUIComponents();
    
    // Load initial data
    loadInitialData();
    
    // Setup event listeners
    setupEventListeners();
}

/**
 * Initializes UI components
 */
function initUIComponents() {
    // Initialize any UI components here (tooltips, modals, etc.)
    if (typeof tippy === 'function') {
        tippy('[data-tippy-content]');
    }
    
    // Initialize any other UI components
    initRichTextEditors();
    initImageUploaders();
    initTagSelectors();
}

/**
 * Loads initial data for the blog management interface
 */
function loadInitialData() {
    // Show loading state
    const tbody = document.querySelector('#blog-posts-table tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="py-12 text-center">
                    <div class="flex justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    </div>
                    <p class="mt-2 text-gray-600">Loading blog posts...</p>
                </td>
            </tr>
        `;
    }
    
    // Load initial posts
    fetchBlogPosts()
        .then(data => {
            renderBlogPosts(data.posts);
            renderPagination(data.pagination);
            updateBlogStats({
                total: data.pagination?.total || 0,
                published: data.posts.filter(p => p.status === 'published').length,
                drafts: data.posts.filter(p => p.status === 'draft').length
            });
            
            // Load categories if needed
            if (document.querySelector('#category-filter')) {
                loadCategories();
            }
        })
        .catch(error => {
            console.error('Error initializing blog management:', error);
            showNotification('error', 'Failed to load blog posts. Please try again.');
        });
}

/**
 * Loads categories for the category filter
 */
function loadCategories() {
    fetch('/hearts-after-god-ministry-site/backend/api/blog/categories')
        .then(response => response.json())
        .then(data => {
            const categoryFilter = document.querySelector('#category-filter');
            if (categoryFilter && data.data) {
                // Save current value
                const currentValue = categoryFilter.value;
                
                // Clear existing options except the first one
                while (categoryFilter.options.length > 1) {
                    categoryFilter.remove(1);
                }
                
                // Add new options
                data.data.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    categoryFilter.appendChild(option);
                });
                
                // Restore selected value if it still exists
                if (currentValue) {
                    categoryFilter.value = currentValue;
                }
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
        });
}

/**
 * Sets up event listeners for the blog management interface
 */
function setupEventListeners() {
    // Search input debounce
    const searchInput = document.querySelector('#search-posts');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const statusFilter = document.querySelector('#status-filter');
                const categoryFilter = document.querySelector('#category-filter');
                
                fetchBlogPosts({
                    search: e.target.value.trim(),
                    status: statusFilter?.value || '',
                    category: categoryFilter?.value || ''
                }).then(data => {
                    renderBlogPosts(data.posts);
                    renderPagination(data.pagination);
                });
            }, 300);
        });
    }
    
    // Status filter change
    const statusFilter = document.querySelector('#status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', (e) => {
            const searchInput = document.querySelector('#search-posts');
            const categoryFilter = document.querySelector('#category-filter');
            
            fetchBlogPosts({
                status: e.target.value,
                search: searchInput?.value || '',
                category: categoryFilter?.value || ''
            }).then(data => {
                renderBlogPosts(data.posts);
                renderPagination(data.pagination);
            });
        });
    }
    
    // Category filter change
    const categoryFilter = document.querySelector('#category-filter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', (e) => {
            const searchInput = document.querySelector('#search-posts');
            const statusFilter = document.querySelector('#status-filter');
            
            fetchBlogPosts({
                category: e.target.value,
                search: searchInput?.value || '',
                status: statusFilter?.value || ''
            }).then(data => {
                renderBlogPosts(data.posts);
                renderPagination(data.pagination);
            });
        });
    }
    
    // New post button
    const newPostBtn = document.querySelector('#new-post-btn');
    if (newPostBtn) {
        newPostBtn.addEventListener('click', showCreateBlogModal);
    }
    
    // Refresh button
    const refreshBtn = document.querySelector('#refresh-posts');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            loadInitialData();
            showNotification('success', 'Blog posts refreshed successfully');
        });
    }
    
    // Bulk actions form
    const bulkActionsForm = document.querySelector('#bulk-actions-form');
    if (bulkActionsForm) {
        bulkActionsForm.addEventListener('submit', handleBulkActions);
    }
    
    // Select all checkbox
    const selectAllCheckbox = document.querySelector('#select-all-posts');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', (e) => {
            const checkboxes = document.querySelectorAll('.post-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = e.target.checked;
            });
        });
    }
}

/**
 * Handles bulk actions for selected posts
 * @param {Event} e - Form submit event
 */
function handleBulkActions(e) {
    e.preventDefault();
    
    const form = e.target;
    const action = form.action.value;
    const checkboxes = document.querySelectorAll('.post-checkbox:checked');
    
    if (checkboxes.length === 0) {
        showNotification('warning', 'Please select at least one post to perform this action');
        return;
    }
    
    const postIds = Array.from(checkboxes).map(checkbox => checkbox.value);
    
    if (action === 'delete') {
        if (confirm(`Are you sure you want to delete ${postIds.length} selected post(s)? This action cannot be undone.`)) {
            // Implement bulk delete
            showNotification('info', `Deleting ${postIds.length} post(s)...`);
            // Add your bulk delete API call here
        }
    } else if (action === 'publish' || action === 'draft' || action === 'archive') {
        const statusText = action.charAt(0).toUpperCase() + action.slice(1);
        if (confirm(`Are you sure you want to mark ${postIds.length} selected post(s) as ${statusText}?`)) {
            // Implement bulk status update
            showNotification('info', `Updating ${postIds.length} post(s) to ${statusText} status...`);
            // Add your bulk status update API call here
        }
    }
}

/**
 * Shows a notification to the user
 * @param {string} type - Notification type (success, error, warning, info)
 * @param {string} message - Notification message
 * @param {number} [duration=5000] - Duration in milliseconds to show the notification
 */
function showNotification(type, message, duration = 5000) {
    const container = document.querySelector('#notification-container') || document.body;
    const notification = document.createElement('div');
    
    // Create notification container if it doesn't exist
    if (!document.querySelector('#notification-container')) {
        const notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        notificationContainer.className = 'fixed top-4 right-4 z-50 space-y-2 w-80';
        document.body.appendChild(notificationContainer);
    }
    
    notification.className = `notification ${type} animate-fade-in`;
    notification.innerHTML = `
        <div class="notification-content">
            <div class="notification-icon">
                ${getNotificationIcon(type)}
            </div>
            <div class="notification-message">
                <p class="font-medium">${message}</p>
            </div>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    `;
    
    container.appendChild(notification);
    
    // Auto-remove notification after duration
    setTimeout(() => {
        notification.classList.remove('animate-fade-in');
        notification.classList.add('animate-fade-out');
        
        // Remove from DOM after animation completes
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);
}

/**
 * Gets the appropriate icon for a notification type
 * @param {string} type - Notification type
 * @returns {string} - SVG icon HTML
 */
function getNotificationIcon(type) {
    const icons = {
        success: `<svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>`,
        error: `<svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>`,
        warning: `<svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>`,
        info: `<svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>`
    };
    
    return icons[type] || icons.info;
}

// Initialize rich text editors if needed
function initRichTextEditors() {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '.rich-text-editor',
            plugins: 'link lists table code image media',
            toolbar: 'undo redo | formatselect | bold italic backcolor | \
                     alignleft aligncenter alignright alignjustify | \
                     bullist numlist outdent indent | removeformat | help',
            menubar: false,
            statusbar: false,
            height: 300
        });
    }
}

// Initialize image uploaders if needed
function initImageUploaders() {
    // Initialize any image uploaders here
    const fileInputs = document.querySelectorAll('.image-upload-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = input.closest('.image-upload-container').querySelector('.image-preview');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });
}

// Initialize tag selectors if needed
function initTagSelectors() {
    // Initialize any tag selectors here
    const tagInputs = document.querySelectorAll('.tag-selector');
    tagInputs.forEach(input => {
        // Initialize tag selector library or custom implementation
    });
}

// Initialize when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize blog posts functionality
    initBlogPosts();
    
    // Initialize blog management if we're on the blog management page
    if (document.querySelector('.blog-management-container')) {
        initBlogManagement();
    }
    
    // Initial fetch of blog posts if we're on the blog page
    if (window.location.hash === '#blog' || document.querySelector('.blog-posts-container')) {
        fetchBlogPosts().then(data => {
            console.log('Initial blog posts loaded:', data.posts.length);
        }).catch(error => {
            console.error('Error loading initial blog posts:', error);
            showNotification('error', 'Failed to load blog posts. Please try again.');
        });
    }
});
