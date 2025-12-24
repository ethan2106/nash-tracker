<?php

namespace App\Service;

use App\Repository\FoodRepositoryInterface;

/**
 * FoodDataService - Service de récupération des données alimentaires
 * Responsabilités :
 * - Récupération des aliments sauvegardés avec pagination
 * - Recherche d'aliments sauvegardés
 * - Comptage des aliments
 * - Agrégation des données nutritionnelles
 */
class FoodDataService
{
    private FoodRepositoryInterface $foodRepository;
    private CacheService $cache;

    public function __construct(FoodRepositoryInterface $foodRepository, CacheService $cache)
    {
        $this->foodRepository = $foodRepository;
        $this->cache = $cache;
    }

    /**
     * Récupère les aliments sauvegardés avec pagination
     */
    public function getSavedFoods(?int $limit = null, int $offset = 0): array
    {
        $cacheKey = 'foods_saved_' . $limit . '_' . $offset;

        return $this->cache->remember('foods', $cacheKey, function () use ($limit, $offset) {
            return $this->foodRepository->getSavedFoods($limit, $offset);
        });
    }

    /**
     * Recherche des aliments sauvegardés
     */
    public function searchSavedFoods(string $query): array
    {
        $cacheKey = 'foods_search_' . md5($query);

        return $this->cache->remember('foods', $cacheKey, function () use ($query) {
            return $this->foodRepository->searchSavedFoods($query);
        });
    }

    /**
     * Compte le nombre total d'aliments sauvegardés
     */
    public function countSavedFoods(): int
    {
        return $this->cache->remember('foods', 'foods_count', function () {
            return $this->foodRepository->countSavedFoods();
        });
    }

    /**
     * Récupère les données paginées pour le catalogue
     */
    public function getCatalogData(int $page = 1, int $perPage = 12): array
    {
        $totalFoods = $this->countSavedFoods();
        $totalPages = ceil($totalFoods / $perPage);
        $offset = ($page - 1) * $perPage;

        $foods = $this->getSavedFoods($perPage, $offset);

        return [
            'foods' => $foods,
            'totalFoods' => $totalFoods,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'perPage' => $perPage,
        ];
    }

    /**
     * Invalide le cache des aliments
     */
    public function invalidateCache(): void
    {
        $this->cache->clearNamespace('foods');
    }
}