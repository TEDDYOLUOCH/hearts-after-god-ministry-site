/**
 * Real-time Blog Management
 * Handles real-time updates for the blog management interface
 */

class BlogRealtime {
    constructor() {
        this.eventSource = null;
        this.lastUpdate = 0;
        this.retryCount = 0;
        this.maxRetries = 5;
        this.retryDelay = 5000; // 5 seconds
        this.isConnected = false;
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }
    
    init() {
        this.connect();
        this.setupEventHandlers();
    }
    
    connect() {
        if (this.eventSource) {
            this.eventSource.close();
        }
        
        try {
            // Create new EventSource connection
            this.eventSource = new EventSource('/hearts-after-god-ministry-site/backend/api/blog/realtime.php');
            
            this.eventSource.onopen = () => {
                this.isConnected = true;
                this.retryCount = 0;
                console.log('SSE connection established');
                this.showNotification('Connected to real-time updates', 'success');
            };
            
            this.eventSource.onerror = (error) => {
                console.error('SSE error:', error);
                this.isConnected = false;
                this.handleConnectionError();
            };
            
            // Handle init event
            this.eventSource.addEventListener('init', (event) => {
                const data = JSON.parse(event.data);
                this.lastUpdate = data.timestamp;
                console.log('Initialized real-time updates', data);
            });
            
            // Handle update event
            this.eventSource.addEventListener('update', (event) => {
                const data = JSON.parse(event.data);
                this.lastUpdate = data.timestamp;
                this.handleUpdate(data.data);
            });
            
            // Handle error event
            this.eventSource.addEventListener('error', (event) => {
                const error = JSON.parse(event.data);
                console.error('SSE server error:', error);
                this.showNotification('Real-time update error: ' + error.message, 'error');
            });
            
        } catch (error) {
            console.error('Failed to initialize SSE:', error);
            this.handleConnectionError();
        }
    }
    
    handleUpdate(data) {
        console.log('Received update:', data);
        
        // Show notification for new/updated posts
        if (data.new_posts > 0 || data.updated_posts > 0) {
            let message = '';
            if (data.new_posts > 0 && data.updated_posts > 0) {
                message = `${data.new_posts} new post(s) and ${data.updated_posts} updated post(s) available`;
            } else if (data.new_posts > 0) {
                message = `${data.new_posts} new post(s) available`;
            } else {
                message = `${data.updated_posts} post(s) updated`;
            }
            
            this.showNotification(message, 'info', {
                autoClose: 5000,
                action: {
                    text: 'Refresh',
                    callback: () => this.refreshBlogPosts()
                }
            });
        }
    }
    
    handleConnectionError() {
        this.retryCount++;
        
        if (this.retryCount <= this.maxRetries) {
            const delay = this.retryDelay * Math.pow(2, this.retryCount - 1);
            console.log(`Reconnecting in ${delay/1000} seconds... (Attempt ${this.retryCount}/${this.maxRetries})`);
            
            setTimeout(() => {
                this.connect();
            }, delay);
        } else {
            console.error('Max retry attempts reached');
            this.showNotification('Disconnected from real-time updates', 'error');
        }
    }
    
    refreshBlogPosts() {
        // Trigger a refresh of the blog posts list
        if (typeof window.blogPostsApp !== 'undefined' && typeof window.blogPostsApp.fetchBlogPosts === 'function') {
            window.blogPostsApp.fetchBlogPosts();
        } else if (window.location.search.includes('edit')) {
            // If on edit page, just reload
            window.location.reload();
        } else {
            // Fallback to full page reload
            window.location.reload();
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
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        if (options.action) {
            const button = document.createElement('button');
            button.className = 'btn btn-sm ml-2';
            button.textContent = options.action.text;
            button.onclick = () => {
                options.action.callback();
                notification.remove();
            };
            notification.appendChild(button);
        }
        
        document.body.appendChild(notification);
        
        if (options.autoClose !== false) {
            setTimeout(() => {
                notification.remove();
            }, options.autoClose || 5000);
        }
    }
    
    setupEventHandlers() {
        // Add event listeners for page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                // Refresh data when tab becomes visible
                this.refreshBlogPosts();
            }
        });
        
        // Listen for custom events from other scripts
        document.addEventListener('blog:postCreated', () => this.refreshBlogPosts());
        document.addEventListener('blog:postUpdated', () => this.refreshBlogPosts());
        document.addEventListener('blog:postDeleted', () => this.refreshBlogPosts());
    }
}

// Initialize real-time updates
if (typeof EventSource !== 'undefined') {
    window.blogRealtime = new BlogRealtime();
} else {
    console.warn('Server-Sent Events not supported in this browser');
}
