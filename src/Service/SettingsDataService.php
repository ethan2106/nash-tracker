<?php

namespace App\Service;

use App\Model\HistoriqueMesuresModel;
use App\Model\ObjectifsModel;
use App\Model\UserConfigModel;
use App\Model\UserModel;

/**
 * SettingsDataService - Service de récupération des données pour les paramètres utilisateur.
 *
 * Responsabilités :
 * - Récupération et mise en cache des données utilisateur
 * - Gestion de la pagination pour l'historique des mesures
 * - Agrégation des données pour la vue des paramètres
 */
class SettingsDataService
{
    public function __construct(
        private HistoriqueMesuresModel $historiqueMesuresModel,
        private UserConfigModel $userConfigModel,
        private UserModel $userModel,
        private CacheService $cache,
        private ObjectifsModel $objectifsModel
    ) {
    }

    /**
     * Récupère toutes les données nécessaires pour la page des paramètres.
     *
     * @param int $userId ID de l'utilisateur
     * @param int $pageMesures Page actuelle pour la pagination des mesures
     * @return array Données pour la vue
     */
    public function getSettingsData(int $userId, int $pageMesures = 1): array
    {
        $perPage = 10;
        $offset = ($pageMesures - 1) * $perPage;
        $namespace = 'settings';

        // Cache user info
        $user = $this->getCachedUser($userId, $namespace);

        // Cache user stats
        $stats = $this->getCachedUserStats($userId, $namespace);

        // Cache historique objectifs
        $historiqueObjectifs = $this->getCachedHistoriqueObjectifs($userId, $namespace);

        // Récupérer historique mesures avec pagination (pas de cache pour pagination)
        $historiqueMesures = $this->historiqueMesuresModel->getHistorique($userId, $perPage, $offset);
        $totalMesures = $this->historiqueMesuresModel->getTotalMesures($userId);
        $totalPages = ceil($totalMesures / $perPage);

        // Cache toutes les mesures pour le graphique
        $allHistoriqueMesures = $this->getCachedAllHistoriqueMesures($userId, $namespace);

        // Cache configuration utilisateur
        $userConfig = $this->getCachedUserConfig($userId, $namespace);

        return [
            'user' => $user,
            'stats' => $stats,
            'historiqueObjectifs' => $historiqueObjectifs,
            'historiqueMesures' => $historiqueMesures,
            'allHistoriqueMesures' => $allHistoriqueMesures,
            'userConfig' => $userConfig,
            'mesuresPagination' => [
                'currentPage' => $pageMesures,
                'totalPages' => $totalPages,
                'perPage' => $perPage,
                'total' => $totalMesures,
            ],
        ];
    }

    /**
     * Récupère les données utilisateur avec cache.
     */
    private function getCachedUser(int $userId, string $namespace): array
    {
        $userKey = 'user_' . $userId;
        $cachedUser = $this->cache->get($namespace, $userKey);
        if ($cachedUser !== null)
        {
            return $cachedUser;
        }

        $user = $this->userModel->getById($userId);
        $this->cache->set($namespace, $userKey, $user, CacheService::TTL_MEDIUM);

        return $user;
    }

    /**
     * Récupère les statistiques utilisateur avec cache.
     */
    private function getCachedUserStats(int $userId, string $namespace): array
    {
        $statsKey = 'stats_' . $userId;
        $cachedStats = $this->cache->get($namespace, $statsKey);
        if ($cachedStats !== null)
        {
            return $cachedStats;
        }

        $stats = $this->userModel->getUserStats($userId);
        $this->cache->set($namespace, $statsKey, $stats, CacheService::TTL_SHORT);

        return $stats;
    }

    /**
     * Récupère l'historique des objectifs avec cache.
     */
    private function getCachedHistoriqueObjectifs(int $userId, string $namespace): array
    {
        $objectifsKey = 'historique_objectifs_' . $userId;
        $cachedObjectifs = $this->cache->get($namespace, $objectifsKey);
        if ($cachedObjectifs !== null)
        {
            return $cachedObjectifs;
        }

        $historiqueObjectifs = $this->objectifsModel->getHistoriqueByUser($userId);
        $this->cache->set($namespace, $objectifsKey, $historiqueObjectifs, CacheService::TTL_MEDIUM);

        return $historiqueObjectifs;
    }

    /**
     * Récupère toutes les mesures pour le graphique avec cache.
     */
    private function getCachedAllHistoriqueMesures(int $userId, string $namespace): array
    {
        $allMesuresKey = 'all_historique_mesures_' . $userId;
        $cachedAllMesures = $this->cache->get($namespace, $allMesuresKey);
        if ($cachedAllMesures !== null)
        {
            return $cachedAllMesures;
        }

        $allHistoriqueMesures = $this->historiqueMesuresModel->getHistorique($userId, 1000);
        $this->cache->set($namespace, $allMesuresKey, $allHistoriqueMesures, CacheService::TTL_MEDIUM);

        return $allHistoriqueMesures;
    }

    /**
     * Récupère la configuration utilisateur avec cache.
     */
    private function getCachedUserConfig(int $userId, string $namespace): array
    {
        $configKey = 'user_config_' . $userId;
        $cachedConfig = $this->cache->get($namespace, $configKey);
        if ($cachedConfig !== null)
        {
            return $cachedConfig;
        }

        $userConfig = $this->userConfigModel->getAll($userId);
        $this->cache->set($namespace, $configKey, $userConfig, CacheService::TTL_MEDIUM);

        return $userConfig;
    }
}
