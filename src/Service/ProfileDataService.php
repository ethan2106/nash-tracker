<?php

namespace App\Service;

use App\Model\ObjectifsModel;
use PDO;

/**
 * ProfileDataService - Service pour la récupération et l'agrégation des données de profil.
 *
 * Responsabilités :
 * - Exécution de requêtes SQL optimisées pour les données de profil
 * - Transformation des résultats SQL en structures de données
 * - Calculs liés aux objectifs (completion, etc.)
 * - Agrégation des données depuis les services existants
 */
class ProfileDataService
{
    private readonly PDO $db;

    private readonly NutritionService $nutritionService;

    private readonly ActivityService $activityService;

    private readonly DashboardService $dashboardService;

    public function __construct(PDO $db, NutritionService $nutritionService, ActivityService $activityService, DashboardService $dashboardService)
    {
        $this->db = $db;
        $this->nutritionService = $nutritionService;
        $this->activityService = $activityService;
        $this->dashboardService = $dashboardService;
    }

    /**
     * Version optimisée qui récupère toutes les données du profil en une seule requête.
     */
    public function getOptimizedProfileData(int $userId): array
    {
        // Pour optimisation SQL : on prépare les bornes du jour
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';

        $stmt = $this->db->prepare('
            SELECT
                -- Objectifs utilisateur
                o.id AS objectifs_id,
                o.user_id,
                o.imc,
                o.poids,
                o.taille,
                o.annee AS age,
                o.sexe,
                o.activite AS niveau_activite,
                o.calories_perte,
                o.proteines_min,
                o.proteines_max,
                o.fibres_min,
                o.fibres_max,
                o.graisses_sat_max,
                o.date_debut AS objectifs_created_at,
                
                -- Nutrition du jour
                COALESCE(DAY_NUTRITION.total_calories, 0) AS current_calories,
                COALESCE(DAY_NUTRITION.total_proteines, 0) AS current_proteines,
                COALESCE(DAY_NUTRITION.total_fibres, 0) AS current_fibres,
                COALESCE(DAY_NUTRITION.total_graisses_sat, 0) AS current_graisses_sat,
                
                -- Activité du jour
                COALESCE(ACTIVITY_TODAY.total_minutes, 0) AS activity_today
                
            FROM objectifs_nutrition o
            
            -- Nutrition du jour
            LEFT JOIN (
                SELECT
                    r.user_id,
                    SUM(ra.quantite_g * a.calories_100g / 100) AS total_calories,
                    SUM(ra.quantite_g * a.proteines_100g / 100) AS total_proteines,
                    SUM(ra.quantite_g * a.fibres_100g / 100) AS total_fibres,
                    SUM(ra.quantite_g * a.acides_gras_satures_100g / 100) AS total_graisses_sat
                FROM repas r
                JOIN repas_aliments ra ON r.id = ra.repas_id
                JOIN aliments a ON ra.aliment_id = a.id
                WHERE r.date_heure BETWEEN ? AND ?
                GROUP BY r.user_id
            ) DAY_NUTRITION ON o.user_id = DAY_NUTRITION.user_id
            
            -- Activité du jour
            LEFT JOIN (
                SELECT user_id, SUM(duree_minutes) AS total_minutes
                FROM activites_physiques
                WHERE date_heure BETWEEN ? AND ?
                GROUP BY user_id
            ) ACTIVITY_TODAY ON o.user_id = ACTIVITY_TODAY.user_id
            
            WHERE o.user_id = ? AND o.actif = 1
        ');

        $stmt->execute([$todayStart, $todayEnd, $todayStart, $todayEnd, $todayStart, $todayEnd, $userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result)
        {
            return [];
        }

        // Calculer objectifs_completion
        $objectifs = [
            'calories_perte' => (float)$result['calories_perte'],
            'proteines_max' => (float)$result['proteines_max'],
            'fibres_max' => (float)$result['fibres_max'],
            'graisses_sat_max' => (float)$result['graisses_sat_max'],
        ];

        $currentNutritionForCompletion = [
            'calories' => (float)$result['current_calories'],
            'proteines' => (float)$result['current_proteines'],
            'fibres' => (float)$result['current_fibres'],
            'graisses_sat' => (float)$result['current_graisses_sat'],
        ];

        $objectifsCompletion = $this->calculateObjectifsCompletion($currentNutritionForCompletion, $objectifs);

        return [
            'objectifs' => [
                'id' => $result['objectifs_id'],
                'user_id' => $result['user_id'],
                'imc' => (float)$result['imc'],
                'poids' => (float)$result['poids'],
                'taille' => (float)$result['taille'],
                'age' => (int)$result['age'],
                'sexe' => $result['sexe'],
                'niveau_activite' => $result['niveau_activite'],
                'activite' => $result['niveau_activite'], // Alias pour compatibilité
                'calories_perte' => (float)$result['calories_perte'],
                'proteines_min' => (float)$result['proteines_min'],
                'proteines_max' => (float)$result['proteines_max'],
                'fibres_min' => (float)$result['fibres_min'],
                'fibres_max' => (float)$result['fibres_max'],
                'graisses_sat_max' => (float)$result['graisses_sat_max'],
                'created_at' => $result['objectifs_created_at'],
            ],
            'stats' => [
                'imc' => (float)$result['imc'],
                'calories_target' => (float)$result['calories_perte'],
                'objectifs_completion' => $objectifsCompletion,
                'calories_consumed' => (float)$result['current_calories'],
                'proteines_consumed' => (float)$result['current_proteines'],
                'glucides_consumed' => 0, // À calculer si nécessaire
                'lipides_consumed' => (float)$result['current_graisses_sat'],
                'activity_minutes_today' => (int)$result['activity_today'],
            ],
            'currentNutrition' => [
                'calories' => (float)$result['current_calories'],
                'proteines' => (float)$result['current_proteines'],
                'fibres' => (float)$result['current_fibres'],
                'graisses_sat' => (float)$result['current_graisses_sat'],
            ],
            'weeklyNutrition' => $this->nutritionService->getWeeklyNutrition($userId), // Garder séparé car complexe
            'recentActivities' => $this->activityService->getRecentActivities($userId, 5), // Garder séparé pour la pagination
            'recentPage' => 1, // Page actuelle pour la pagination
            'recentTotal' => $this->activityService->getRecentActivitiesCount($userId), // Nombre total d'activités
            'realScore' => $this->calculateRealScore($result, $objectifsCompletion), // Score de santé pour la gamification
        ];
    }

    /**
     * Calcule le score réel de santé pour la gamification.
     */
    private function calculateRealScore(array $result, int $objectifsCompletion): float
    {
        // Préparer les données pour le calcul du score
        $stats = [
            'imc' => (float)$result['imc'],
            'calories_target' => (float)$result['calories_perte'],
            'objectifs_completion' => $objectifsCompletion,
            'calories_consumed' => (float)$result['current_calories'],
            'proteines_consumed' => (float)$result['current_proteines'],
            'glucides_consumed' => 0, // Non calculé dans cette requête
            'lipides_consumed' => (float)$result['current_graisses_sat'],
            'activity_minutes_today' => (int)$result['activity_today'],
        ];

        $userConfig = [
            'activite_objectif_minutes' => 30, // Valeur par défaut
        ];

        $objectifs = [
            'annee' => (int)$result['age'],
            'imc' => (float)$result['imc'],
        ];

        try
        {
            $scores = $this->dashboardService->computeHealthScore($stats, $userConfig, $objectifs);

            return (float)$scores['global'];
        } catch (\Throwable $e)
        {
            return 0.0; // Score par défaut en cas d'erreur
        }
    }

    /**
     * Calcule le pourcentage d'objectifs atteints.
     */
    public function calculateObjectifsCompletion(array $currentNutrition, array $objectifs): int
    {
        if (!$objectifs)
        {
            return 0;
        }

        $caloriesConsumed = $currentNutrition['calories'] ?? 0;
        $caloriesTarget = $objectifs['calories_perte'] ?? 0;

        $proteinesConsumed = $currentNutrition['proteines'] ?? 0;
        $proteinesTarget = $objectifs['proteines_max'] ?? 0;

        $fibresConsumed = $currentNutrition['fibres'] ?? 0;
        $fibresTarget = $objectifs['fibres_max'] ?? 0;

        // Calculer le score moyen
        $scores = [];
        if ($caloriesTarget > 0)
        {
            $scores[] = min(100, ($caloriesConsumed / $caloriesTarget) * 100);
        }
        if ($proteinesTarget > 0)
        {
            $scores[] = min(100, ($proteinesConsumed / $proteinesTarget) * 100);
        }
        if ($fibresTarget > 0)
        {
            $scores[] = min(100, ($fibresConsumed / $fibresTarget) * 100);
        }

        return $scores ? round(array_sum($scores) / count($scores)) : 0;
    }

    /**
     * Récupère les données de profil de base (version non optimisée).
     */
    public function getProfileData(?array $user): ?array
    {
        if (empty($user))
        {
            return null;
        }

        $data = [
            'user' => $user,
            'objectifs' => null,
            'stats' => null,
            'currentNutrition' => null,
            'weeklyNutrition' => null,
            'recentActivities' => [],
        ];

        try
        {
            $data['objectifs'] = ObjectifsModel::getByUser($user['id']);

            $dashboardData = $this->dashboardService->getDashboardData($user);
            $data['stats'] = $dashboardData['stats'] ?? null;

            $data['currentNutrition'] = $this->nutritionService->getCurrentNutrition($user['id']);
            $data['weeklyNutrition'] = $this->nutritionService->getWeeklyNutrition($user['id']);
            $data['recentActivities'] = $this->activityService->getRecentActivities($user['id']);
        } catch (\Exception $e)
        {
            error_log('Erreur récupération données profil: ' . $e->getMessage());
        }

        return $data;
    }
}
