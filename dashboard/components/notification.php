<?php
/**
 * Notification Component
 * Displays toast notifications
 */
if (!function_exists('add_notification')) {
    function add_notification($message, $type = 'info') {
        if (!isset($_SESSION['notifications'])) {
            $_SESSION['notifications'] = [];
        }
        
        $_SESSION['notifications'][] = [
            'message' => $message,
            'type' => $type,
            'time' => time()
        ];
    }
}
?>

<!-- Notification Container -->
<div x-data="{
    notifications: [],
    remove(notification) {
        this.notifications = this.notifications.filter(n => n !== notification);
    },
    init() {
        // Load notifications from PHP session
        this.notifications = <?= json_encode($_SESSION['notifications'] ?? []) ?>;
        
        // Clear notifications from session after displaying
        <?php unset($_SESSION['notifications']); ?>
        
        // Listen for new notifications from other components
        window.addEventListener('notify', (e) => {
            this.notifications.push({
                message: e.detail.message,
                type: e.detail.type || 'info',
                time: Date.now()
            });
            
            // Auto-remove notification after delay
            setTimeout(() => {
                this.remove(this.notifications[this.notifications.length - 1]);
            }, 5000);
        });
    }
}" class="fixed top-4 right-4 z-50 space-y-2 w-80">
    <template x-for="(notification, index) in notifications" :key="index">
        <div x-show="notification" 
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="rounded-lg shadow-lg overflow-hidden bg-white border border-gray-200">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <template x-if="notification.type === 'success'">
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                        <template x-if="notification.type === 'error'">
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </template>
                        <template x-if="notification.type === 'warning'">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                        <template x-if="notification.type === 'info'">
                            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p x-text="notification.message" class="text-sm font-medium text-gray-900"></p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="remove(notification)" class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
