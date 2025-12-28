<?php

namespace App\Controller;

use App\Model\Database;
use App\Model\UserConfigModel;
use App\Service\ActivityService;
use App\Service\CacheService;
use App\Service\DashboardService;
use App\Service\GamificationService;
use App\Service\NAFLDAdviceService;
use App\Service\NutritionService;
use App\Service\ProfileApiService;
use App\Service\ProfileDataService;
use App\Service\ValidationService;

class ProfileController extends BaseApiController
{
    public function __construct(
        private readonly \PDO $db,
        private readonly NutritionService $nutritionService,
        private readonly ActivityService $activityService,
        private readonly NAFLDAdviceService $adviceService,
        private readonly UserConfigModel $userConfigModel,
        private readonly CacheService $cacheService,
        private readonly ValidationService $validationService,
        private readonly ProfileDataService $profileDataService,
        private readonly ProfileApiService $profileApiService,
        private readonly GamificationService $gamificationService
    ) {
    }

    // ===============================
    // Profils et données utilisateur
    // ===============================

    /**
     * Affiche la page de profil.
     */
    public function showProfile(): void
    {
        $userId = $this->requireAuth();
        $user = $this->getUser();

        $data = $this->getOptimizedProfileData($userId);
        $data['user'] = $user;

        // Calculer les données de gamification
        $realScore = $data['realScore'] ?? 0;
        $data['levelData'] = $this->gamificationService->computeLevel((int)($realScore * 10));

        // Inclure la vue avec les données
        extract($data);
        include __DIR__ . '/../View/profile.php';
    }

    public function getRecentActivitiesCount(int $userId): int
    {
        return $this->activityService->getRecentActivitiesCount($userId);
    }

    public function getRecentActivities(int $userId, int $limit = 5, int $offset = 0): array
    {
        return $this->activityService->getRecentActivities($userId, $limit, $offset);
    }

    public function calculateHealthScore(array $currentNutrition, array $objectifs): float
    {
        return $this->nutritionService->calculateHealthScore($currentNutrition, $objectifs);
    }

    public function getProfileData(?array $user): ?array
    {
        return $this->profileDataService->getProfileData($user);
    }

    /**
     * Version optimisée qui récupère toutes les données du profil en une seule requête.
     */
    public function getOptimizedProfileData(int $userId): array
    {
        return $this->profileDataService->getOptimizedProfileData($userId);
    }

    // ===============================
    // Endpoints API JSON
    // ===============================
    public function handleApiRecentActivities(): void
    {
        try
        {
            $userId = (int)($_SESSION['user']['id'] ?? $_GET['user_id'] ?? 0);
            if ($userId <= 0)
            {
                $this->jsonError(['error' => 'Utilisateur invalide'], 400);
            }

            $pageNum = max(1, intval($_GET['recent_page'] ?? 1));
            $limit = max(1, intval($_GET['limit'] ?? 5));

            $data = $this->profileApiService->getRecentActivitiesData($userId, $pageNum, $limit);

            $this->jsonSuccess($data);
        } catch (\Exception $e)
        {
            $this->jsonError(['error' => 'Erreur récupération activités: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Invalide le cache pour un utilisateur (lors de modifications de données).
     */
    public function invalidateUserCache(int $userId): void
    {
        // Invalider le cache de nutrition pour cet utilisateur
        $this->cacheService->clearNamespace('nutrition');

        // Invalider le cache du profil pour cet utilisateur
        $this->cacheService->clearNamespace('profile');

        // Invalider le cache des conseils pour cet utilisateur
        $this->cacheService->clearNamespace('advice');
    }
}
