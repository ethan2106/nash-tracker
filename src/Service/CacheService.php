<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * CacheService - Service de cache unifié pour l'application
 * Utilise Symfony Cache pour la persistance.
 * Supporte le cache fichier pour différentes durées.
 * Fournit des méthodes pour get, set, delete, clear et remember.
 * Intègre des métriques de performance pour le suivi des hits/misses.
 */
class CacheService
{
    private const CACHE_DIR = __DIR__ . '/../../storage/cache/';

    private const DEFAULT_TTL = 3600; // 1 heure par défaut

    // Durées de cache prédéfinies
    public const TTL_SHORT = 300;    // 5 minutes

    public const TTL_MEDIUM = 1800;  // 30 minutes

    public const TTL_LONG = 3600;    // 1 heure

    public const TTL_DAY = 86400;    // 1 jour

    public const TTL_WEEK = 604800;  // 1 semaine

    // Namespaces prédéfinis
    public const NAMESPACE_OPENFOODFACTS = 'openfoodfacts';

    /**
     * Indique si le cache est activé.
     * Désactivé en environnement de développement (Docker).
     */
    private bool $enabled;

    private FilesystemAdapter $cache;

    public function __construct()
    {
        // Cache désactivé si variable d'env CACHE_DISABLED=1 ou en Docker (détection auto)
        $this->enabled = !$this->isDevEnvironment();

        $this->cache = new FilesystemAdapter('', self::DEFAULT_TTL, self::CACHE_DIR);

        if ($this->enabled)
        {
            $this->ensureCacheDirectory();
        }
    }

    /**
     * Détecte si on est en environnement de développement.
     */
    private function isDevEnvironment(): bool
    {
        // Cache désactivé si APP_ENV=dev
        return getenv('APP_ENV') === 'dev';
    }

    /**
     * Vérifie si le cache est activé.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Active ou désactive le cache manuellement.
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * Assure que le répertoire de cache existe.
     */
    private function ensureCacheDirectory(): void
    {
        if (!is_dir(self::CACHE_DIR))
        {
            mkdir(self::CACHE_DIR, 0755, true);
        }
    }

    /**
     * Génère une clé de cache unique.
     */
    private function generateKey(string $namespace, string $key): string
    {
        return $namespace . '_' . md5($key);
    }

    /**
     * Récupère une valeur du cache.
     */
    public function get(string $namespace, string $key)
    {
        // Cache désactivé = toujours miss
        if (!$this->enabled)
        {
            return null;
        }

        $cacheKey = $this->generateKey($namespace, $key);
        $item = $this->cache->getItem($cacheKey);

        if (!$item->isHit())
        {
            $this->notifyCacheMiss();

            return null;
        }

        $this->notifyCacheHit();

        return $item->get();
    }

    /**
     * Stocke une valeur en cache.
     */
    public function set(string $namespace, string $key, $value, int $ttl = self::DEFAULT_TTL): bool
    {
        // Cache désactivé = on ne stocke rien
        if (!$this->enabled)
        {
            return true;
        }

        $cacheKey = $this->generateKey($namespace, $key);
        $item = $this->cache->getItem($cacheKey);
        $item->set($value)->expiresAfter($ttl);

        return $this->cache->save($item);
    }

    /**
     * Supprime une entrée du cache.
     */
    public function delete(string $namespace, string $key): bool
    {
        $cacheKey = $this->generateKey($namespace, $key);

        return $this->cache->deleteItem($cacheKey);
    }

    /**
     * Vide tout le cache d'un namespace.
     */
    public function clearNamespace(string $namespace): bool
    {
        return $this->cache->clear($namespace);
    }

    /**
     * Vide tout le cache.
     */
    public function clearAll(): bool
    {
        return $this->cache->clear();
    }

    /**
     * Récupère ou définit une valeur (pattern cache miss).
     */
    public function remember(string $namespace, string $key, callable $callback, int $ttl = self::DEFAULT_TTL)
    {
        $cached = $this->get($namespace, $key);

        if ($cached !== null)
        {
            return $cached;
        }

        $value = $callback();
        $this->set($namespace, $key, $value, $ttl);

        return $value;
    }

    /**
     * Notifie un hit cache aux métriques de performance.
     */
    private function notifyCacheHit(): void
    {
        if (class_exists('App\Service\PerformanceMetrics'))
        {
            \App\Service\PerformanceMetrics::getInstance()->incrementCacheHit();
        }
    }

    /**
     * Notifie un miss cache aux métriques de performance.
     */
    private function notifyCacheMiss(): void
    {
        if (class_exists('App\Service\PerformanceMetrics'))
        {
            \App\Service\PerformanceMetrics::getInstance()->incrementCacheMiss();
        }
    }
}
