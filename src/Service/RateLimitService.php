<?php

namespace App\Service;

/**
 * RateLimitService - Gère la limitation de taux des requêtes
 * Implémentation simple basée sur fichiers (best-effort)
 * Responsabilités :
 * - Vérification du taux de requêtes par IP
 * - Stockage des timestamps dans des fichiers cache
 * - Gestion des fenêtres temporelles
 */
class RateLimitService
{
    private string $cacheDir;
    private int $windowSeconds;
    private int $maxRequests;

    public function __construct(string $cacheDir = null, int $windowSeconds = 60, int $maxRequests = 15)
    {
        $this->cacheDir = $cacheDir ?? dirname(__DIR__, 2) . '/storage/cache';
        $this->windowSeconds = $windowSeconds;
        $this->maxRequests = $maxRequests;

        $this->ensureCacheDirExists();
    }

    /**
     * Vérifie si la requête courante dépasse la limite de taux
     * @throws \RuntimeException Si rate limited
     */
    public function checkRateLimit(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $safeIp = $this->normalizeIpForFilename($ip);

        $cacheFile = $this->cacheDir . '/api_check_unique_' . $safeIp . '.json';
        $now = time();

        // Charger les timestamps existants
        $timestamps = $this->loadTimestamps($cacheFile, $now);

        // Ajouter la requête courante
        $timestamps[] = $now;

        // Sauvegarder (best-effort)
        $this->saveTimestamps($cacheFile, $timestamps);

        // Vérifier la limite
        if (count($timestamps) > $this->maxRequests) {
            throw new \RuntimeException('rate_limited');
        }
    }

    /**
     * Retourne le nombre de secondes avant de pouvoir réessayer
     */
    public function getRetryAfter(): int
    {
        return $this->windowSeconds;
    }

    /**
     * Normalise une IP pour la sécurité des noms de fichiers
     */
    private function normalizeIpForFilename(string $ip): string
    {
        return preg_replace('/[^a-z0-9_.-]/i', '_', $ip);
    }

    /**
     * S'assure que le répertoire cache existe
     */
    private function ensureCacheDirExists(): void
    {
        if (!is_dir($this->cacheDir)) {
            if (!mkdir($this->cacheDir, 0755, true) && !is_dir($this->cacheDir)) {
                error_log('Failed to create cache directory: ' . $this->cacheDir);
            }
        }
    }

    /**
     * Charge les timestamps depuis le fichier cache
     */
    private function loadTimestamps(string $cacheFile, int $now): array
    {
        $timestamps = [];

        if (is_readable($cacheFile)) {
            $raw = file_get_contents($cacheFile);
            if ($raw !== false) {
                $arr = json_decode($raw, true);
                if (is_array($arr)) {
                    // Filtrer les timestamps dans la fenêtre
                    $timestamps = array_filter($arr, function ($t) use ($now) {
                        return $t >= ($now - $this->windowSeconds);
                    });
                }
            }
        }

        return $timestamps;
    }

    /**
     * Sauvegarde les timestamps dans le fichier cache
     */
    private function saveTimestamps(string $cacheFile, array $timestamps): void
    {
        $written = file_put_contents($cacheFile, json_encode(array_values($timestamps)));
        if ($written === false) {
            error_log('Failed to write rate limit cache: ' . $cacheFile);
        }
    }
}