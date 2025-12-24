<?php

namespace App\Service;

/**
 * ProfileApiService - Service pour la logique des endpoints API du profil.
 *
 * Responsabilités :
 * - Gestion de la pagination pour les activités récentes
 * - Validation des paramètres API
 * - Préparation des données pour les réponses JSON
 */
class ProfileApiService
{
    private readonly ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Récupère les données paginées des activités récentes pour l'API.
     */
    public function getRecentActivitiesData(int $userId, int $pageNum, int $limit): array
    {
        $offset = ($pageNum - 1) * $limit;

        $activities = $this->activityService->getRecentActivities($userId, $limit, $offset);
        $total = $this->activityService->getRecentActivitiesCount($userId);

        return [
            'activities' => $activities,
            'total' => $total,
            'page' => $pageNum,
            'limit' => $limit,
        ];
    }
}
