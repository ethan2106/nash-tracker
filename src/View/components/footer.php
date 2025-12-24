<footer class="bg-white/60 backdrop-blur-md border-t border-blue-100 shadow-md py-6 mt-auto rounded-t-3xl relative">
    <div class="container mx-auto flex flex-col md:flex-row items-center justify-between px-6 gap-4">
        <div class="flex items-center gap-2 text-gray-600">
            <i class="fa-solid fa-leaf text-green-400" aria-hidden="true"></i>
            <span class="font-semibold">Suivi Nash &copy; <?= date('Y'); ?></span>
        </div>
        <div class="flex gap-4 text-gray-500">
            <a href="https://openfoodfacts.org/" target="_blank" rel="noopener noreferrer" class="hover:text-green-500 transition focus:outline-none focus:underline">
                <i class="fa-solid fa-utensils" aria-hidden="true"></i>
                <span class="sr-only sm:not-sr-only">OpenFoodFacts</span>
            </a>
            <a href="mailto:contact@suivinash.local" class="hover:text-blue-500 transition focus:outline-none focus:underline">
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                <span class="sr-only sm:not-sr-only">Contact</span>
            </a>
            <a href="https://github.com/" target="_blank" rel="noopener noreferrer" class="hover:text-gray-700 transition focus:outline-none focus:underline">
                <i class="fa-brands fa-github" aria-hidden="true"></i>
                <span class="sr-only sm:not-sr-only">Github</span>
            </a>
        </div>
    </div>

    <!-- Métriques de performance (visible en développement) -->
    <?php if (isset($_GET['debug']) || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false)
    { ?>
        <?php
        if (!isset($performanceMetrics))
        {
            echo '<div class="bg-red-900 text-white p-2">PerformanceMetrics not defined</div>';
        } else
        {
            $metrics = $performanceMetrics->getReport();
            $cacheService = new \App\Service\CacheService();
            $cacheStats = $cacheService->getStats();
            $performanceMetrics->updateCacheStats($cacheStats);
            $metrics = $performanceMetrics->getReport(); // Re-générer avec les stats cache
            ?>
        <div class="bg-gray-900 text-green-400 text-xs font-mono p-2 border-t border-gray-700" title="Métriques de performance - Ajoutez ?debug=1 à l'URL pour les voir en production">
            <div class="container mx-auto flex flex-wrap gap-4 justify-center">
                <span><i class="fa-solid fa-clock"></i> <?= number_format($metrics['load_time'], 0, ',', ' '); ?>ms</span>
                <span><i class="fa-solid fa-database"></i> <?= $metrics['db_queries']; ?> req</span>
                <span><i class="fa-solid fa-bolt"></i> Cache: <?= $metrics['cache_hit_rate']; ?>% hit</span>
                <span><i class="fa-solid fa-memory"></i> RAM: <?= number_format($metrics['memory_usage']['current_mb'], 1, ',', ' '); ?>MB</span>
                <span><i class="fa-solid fa-hdd"></i> Cache: <?= $cacheStats['total_entries']; ?> files (<?= number_format($cacheStats['total_size_mb'], 1, ',', ' '); ?>MB)</span>
            </div>
        </div>
        <?php } ?>
    <?php } ?>

    <!-- Back to top button -->
    <button onclick="window.scrollTo({top:0,behavior:'smooth'})"
        class="fixed bottom-8 right-8 bg-blue-100 hover:bg-blue-300 text-blue-700 rounded-full shadow-lg p-3 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400 z-50"
        aria-label="Retour en haut">
        <i class="fa-solid fa-arrow-up"></i>
    </button>
</footer>
