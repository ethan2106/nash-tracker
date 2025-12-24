<?php

namespace App\Service;

/**
 * PerformanceMetrics - Service pour mesurer les performances de l'application
 * Responsabilités :
 * - Mesurer le temps de chargement des pages
 * - Compter les requêtes DB
 * - Suivre l'état du cache
 * - Générer des rapports de performance.
 */
class PerformanceMetrics
{
    private static $instance = null;

    private $startTime;

    private $dbQueries = 0;

    private $cacheHits = 0;

    private $cacheMisses = 0;

    private $cacheStats = [];

    private function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Singleton pattern pour mesurer globalement.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Démarre le chronomètre.
     */
    public function startTimer(): void
    {
        $this->startTime = microtime(true);
    }

    /**
     * Arrête le chronomètre et retourne le temps écoulé.
     */
    public function getElapsedTime(): float
    {
        return microtime(true) - $this->startTime;
    }

    /**
     * Incrémente le compteur de requêtes DB.
     */
    public function incrementDbQueries(): void
    {
        $this->dbQueries++;
    }

    /**
     * Incrémente le compteur de hits cache.
     */
    public function incrementCacheHit(): void
    {
        $this->cacheHits++;
    }

    /**
     * Incrémente le compteur de misses cache.
     */
    public function incrementCacheMiss(): void
    {
        $this->cacheMisses++;
    }

    /**
     * Met à jour les statistiques du cache.
     */
    public function updateCacheStats(array $stats): void
    {
        $this->cacheStats = $stats;
    }

    /**
     * Génère un rapport de performance.
     */
    public function getReport(): array
    {
        $elapsed = $this->getElapsedTime();
        $totalCacheRequests = $this->cacheHits + $this->cacheMisses;
        $cacheHitRate = $totalCacheRequests > 0 ? round(($this->cacheHits / $totalCacheRequests) * 100, 1) : 0;

        return [
            'load_time' => round($elapsed * 1000, 2), // en millisecondes
            'db_queries' => $this->dbQueries,
            'cache_hits' => $this->cacheHits,
            'cache_misses' => $this->cacheMisses,
            'cache_hit_rate' => $cacheHitRate,
            'cache_entries' => $this->cacheStats['total_entries'] ?? 0,
            'cache_size_mb' => $this->cacheStats['total_size_mb'] ?? 0,
            'memory_usage' => $this->getMemoryUsage(),
        ];
    }

    /**
     * Retourne l'utilisation mémoire.
     */
    private function getMemoryUsage(): array
    {
        $peak = memory_get_peak_usage(true);
        $current = memory_get_usage(true);

        return [
            'current_mb' => round($current / 1024 / 1024, 2),
            'peak_mb' => round($peak / 1024 / 1024, 2),
        ];
    }

    /**
     * Réinitialise les métriques.
     */
    public function reset(): void
    {
        $this->startTime = microtime(true);
        $this->dbQueries = 0;
        $this->cacheHits = 0;
        $this->cacheMisses = 0;
        $this->cacheStats = [];
    }
}
