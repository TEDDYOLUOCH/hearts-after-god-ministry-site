<?php
/**
 * Stat Card Component
 * 
 * @param array $params - Array of parameters
 * @return string HTML content
 */
function statCard(array $params): string {
    $defaults = [
        'title' => '',
        'value' => '0',
        'icon' => 'circle',
        'color' => 'default',
        'subtitle' => null,
        'trend' => null,
        'trendText' => null,
        'action' => null
    ];
    
    $params = array_merge($defaults, $params);
    
    $colorClasses = [
        'default' => ['bg' => 'bg-white dark:bg-gray-800', 'icon' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-600 dark:text-gray-300', 'value' => 'text-gray-900 dark:text-white'],
        'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-900/20', 'icon' => 'bg-blue-100 dark:bg-blue-900/40', 'text' => 'text-blue-600 dark:text-blue-400', 'value' => 'text-blue-900 dark:text-white'],
        'green' => ['bg' => 'bg-green-50 dark:bg-green-900/20', 'icon' => 'bg-green-100 dark:bg-green-900/40', 'text' => 'text-green-600 dark:text-green-400', 'value' => 'text-green-900 dark:text-white'],
        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-900/20', 'icon' => 'bg-purple-100 dark:bg-purple-900/40', 'text' => 'text-purple-600 dark:text-purple-400', 'value' => 'text-purple-900 dark:text-white'],
        'red' => ['bg' => 'bg-red-50 dark:bg-red-900/20', 'icon' => 'bg-red-100 dark:bg-red-900/40', 'text' => 'text-red-600 dark:text-red-400', 'value' => 'text-red-900 dark:text-white'],
        'yellow' => ['bg' => 'bg-yellow-50 dark:bg-yellow-900/20', 'icon' => 'bg-yellow-100 dark:bg-yellow-900/40', 'text' => 'text-yellow-600 dark:text-yellow-400', 'value' => 'text-yellow-900 dark:text-white'],
        'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/20', 'icon' => 'bg-indigo-100 dark:bg-indigo-900/40', 'text' => 'text-indigo-600 dark:text-indigo-400', 'value' => 'text-indigo-900 dark:text-white'],
        'pink' => ['bg' => 'bg-pink-50 dark:bg-pink-900/20', 'icon' => 'bg-pink-100 dark:bg-pink-900/40', 'text' => 'text-pink-600 dark:text-pink-400', 'value' => 'text-pink-900 dark:text-white'],
    ];
    
    $color = $params['color'];
    $colorScheme = $colorClasses[$color] ?? $colorClasses['default'];
    
    ob_start();
    ?>

    <div 
        x-data="{ 
            isLoading: true,
            loaded: false,
            init() {
                // Simulate loading delay for demo
                setTimeout(() => {
                    this.isLoading = false;
                    this.loaded = true;
                }, Math.random() * 300 + 100);
            }
        }"
        class="relative h-full rounded-xl overflow-hidden transition-all duration-300 hover:shadow-md dark:border dark:border-gray-700"
        :class="{
            'animate-pulse': isLoading,
            '<?= $colorScheme['bg'] ?>': loaded
        }"
    >
    <!-- Loading Skeleton -->
    <div x-show="isLoading" class="absolute inset-0 bg-gray-100 dark:bg-gray-800 rounded-xl">
        <div class="h-1/2 p-6">
            <div class="h-4 w-3/4 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
            <div class="h-6 w-1/2 bg-gray-200 dark:bg-gray-700 rounded mt-2"></div>
        </div>
        <div class="h-1/2 p-6 pt-0">
            <div class="h-3 w-full bg-gray-200 dark:bg-gray-700 rounded mb-1"></div>
            <div class="h-3 w-2/3 bg-gray-200 dark:bg-gray-700 rounded"></div>
        </div>
    </div>
    
    <!-- Content -->
    <div x-show="loaded" class="h-full p-6 transition-opacity duration-300" :class="{'opacity-0': isLoading, 'opacity-100': !isLoading}">
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-lg <?= $colorScheme['icon'] ?> <?= $colorScheme['text'] ?>">
                        <i data-lucide="<?= $params['icon'] ?>" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium <?= $colorScheme['text'] ?>">
                            <?= htmlspecialchars($params['title']) ?>
                        </h3>
                        <p class="text-2xl font-bold mt-1 <?= $colorScheme['value'] ?>">
                            <?= htmlspecialchars($params['value']) ?>
                        </p>
                    </div>
                </div>
                
                <?php if (!empty($params['subtitle']) || !empty($params['trend'])): ?>
                <div class="mt-4 flex items-center text-sm <?= $colorScheme['text'] ?>">
                    <?php if (!empty($params['trend'])): ?>
                    <span class="inline-flex items-center <?= $params['trend'] === 'up' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
                        <i data-lucide="<?= $params['trend'] === 'up' ? 'trending-up' : 'trending-down' ?>" class="w-4 h-4 mr-1"></i>
                        <?= htmlspecialchars($params['trendText'] ?? '') ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($params['subtitle'])): ?>
                    <span class="ml-2">
                        <?= htmlspecialchars($params['subtitle']) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($params['action'])): ?>
            <button @click="<?= htmlspecialchars($params['action']) ?>" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                <i data-lucide="more-vertical" class="w-5 h-5"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
    return ob_get_clean();
}
