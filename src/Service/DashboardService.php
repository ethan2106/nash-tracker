<?php

namespace App\Service;

use App\Model\UserConfigModel;
use App\Model\ObjectifsModel;
use Exception;
use PDO;

/**
 * DashboardService - Service métier pour la logique du dashboard.
 *
 * Responsabilités :
 * - Calculs des statistiques dashboard
 * - Récupération des objectifs quotidiens
 * - Agrégation des données nutritionnelles
 * - Gestion des médicaments du jour
 */
class DashboardService
{
    private const TOAST_COOLDOWN_MINUTES = 60; // anti-spam entre 2 alertes identiques

    public function __construct(
        private \PDO $db,
        private CacheService $cache,
        private ObjectifsModel $objectifsModel,
        private UserConfigModel $userConfigModel
    ) {
    }

    /**
     * Récupère toutes les données du dashboard pour un utilisateur.
     */
    public function getDashboardData($user)
    {
        $data = [
            'user' => $user,
            'objectifs' => null,
            'dailyGoals' => [],
            'stats' => $this->getDashboardStats($user),
            'recentActivity' => [], // Activités récentes
            'toasts' => [],
            'scores' => [],
        ];

        if ($user)
        {
            // Cache objectifs
            $namespace = 'dashboard';
            $key = 'objectifs_' . $user['id'];
            $cachedObjectifs = $this->cache->get($namespace, $key);
            if ($cachedObjectifs !== null)
            {
                $data['objectifs'] = $cachedObjectifs;
            } else
            {
                $data['objectifs'] = $this->objectifsModel->getByUser($user['id']);
                $this->cache->set($namespace, $key, $data['objectifs'], \App\Service\CacheService::TTL_MEDIUM); // 30 min
            }

            $data['dailyGoals'] = $this->getDailyGoals($data['objectifs'], $user['id']);
            $data['recentActivity'] = $this->getRecentActivity($user['id']);

            // Récupération configuration utilisateur (utilisée pour scores et toasts)
            $userConfig = [];
            $key = 'user_config_' . $user['id'];
            $cachedConfig = $this->cache->get($namespace, $key);
            if ($cachedConfig !== null)
            {
                $userConfig = $cachedConfig;
            } else
            {
                try
                {
                    $userConfig = $this->userConfigModel->getAll($user['id']);
                } catch (\Throwable $e)
                {
                    $userConfig = [];
                }
                $this->cache->set($namespace, $key, $userConfig, \App\Service\CacheService::TTL_MEDIUM); // 30 min
            }

            // Calcul des scores de santé (global + composants)
            try
            {
                $data['scores'] = $this->computeHealthScore($data['stats'], $userConfig, $data['objectifs']);
            } catch (\Throwable $e)
            {
                $data['scores'] = ['global' => 0, 'components' => []];
            }

            // Générer les toasts contextuels selon préférences + période silencieuse
            try
            {
                $data['toasts'] = $this->generateToasts($user['id'], $userConfig, $data['stats']);
            } catch (\Throwable $e)
            {
                // pas critique pour le dashboard
                error_log('Toast generation error: ' . $e->getMessage());
            }
        }

        return $data;
    }

    /**
     * Calcule le score santé (global 0-100) et ses composants (IMC, Calories, Activité), chacun sur 25 points.
     * @return array{global:int,components:array<string,float|int>}
     */
    public function computeHealthScore(array $stats, array $userConfig, $objectifs): array
    {
        $components = [];

        // Calcul âge si disponible
        $age = 0;
        if (isset($objectifs['annee']) && is_numeric($objectifs['annee']))
        {
            $age = (int)date('Y') - (int)$objectifs['annee'];
        }

        // IMC (40% du score total) - Risque NAFLD élevé si IMC > 25
        $imc = isset($objectifs['imc']) ? (float)$objectifs['imc'] : 0.0;
        if ($imc <= 18.5)
        {
            $imcScore = 60; // Sous-poids, risque modéré
        } elseif ($imc <= 25.0)
        {
            $imcScore = 100; // Poids normal, risque faible
        } elseif ($imc <= 30.0)
        {
            $imcScore = 70; // Surpoids, risque modéré
        } elseif ($imc <= 35.0)
        {
            $imcScore = 40; // Obésité classe I, risque élevé
        } else
        {
            $imcScore = 20; // Obésité classe II+, risque très élevé
        }
        $components['IMC'] = (int)round($imcScore * 0.4); // 40% poids

        // Âge (15% du score total) - Risque NAFLD augmente avec l'âge
        if ($age <= 30)
        {
            $ageScore = 100; // Jeune, risque faible
        } elseif ($age <= 45)
        {
            $ageScore = 85; // Adulte moyen
        } elseif ($age <= 60)
        {
            $ageScore = 60; // Senior, risque modéré
        } else
        {
            $ageScore = 30; // Âgé, risque élevé
        }
        $components['Âge'] = (int)round($ageScore * 0.15); // 15% poids

        // Activité physique (25% du score total) - Protecteur contre NAFLD
        $activityTarget = (int)($userConfig['activite_objectif_minutes'] ?? 30);
        $activityToday = max(0, (int)($stats['activity_minutes_today'] ?? 0));
        $activityCompletion = $activityTarget > 0 ? min(100, ($activityToday / $activityTarget) * 100) : 0;

        if ($activityCompletion >= 100)
        {
            $activityScore = 100; // Objectif atteint, excellent
        } elseif ($activityCompletion >= 75)
        {
            $activityScore = 80; // Très bien
        } elseif ($activityCompletion >= 50)
        {
            $activityScore = 60; // Bien
        } elseif ($activityCompletion >= 25)
        {
            $activityScore = 40; // Faible
        } else
        {
            $activityScore = 20; // Très faible, risque élevé
        }
        $components['Activité'] = (int)round($activityScore * 0.25); // 25% poids

        // Nutrition/Objectifs (20% du score total) - Alimentation équilibrée
        $completion = (int)($stats['objectifs_completion'] ?? 0);
        $completion = max(0, min(100, $completion));

        if ($completion >= 90)
        {
            $nutritionScore = 100; // Excellente alimentation
        } elseif ($completion >= 75)
        {
            $nutritionScore = 85; // Très bonne
        } elseif ($completion >= 60)
        {
            $nutritionScore = 70; // Bonne
        } elseif ($completion >= 40)
        {
            $nutritionScore = 50; // Moyenne
        } elseif ($completion >= 20)
        {
            $nutritionScore = 30; // Faible
        } else
        {
            $nutritionScore = 10; // Très faible, risque élevé
        }
        $components['Nutrition'] = (int)round($nutritionScore * 0.2); // 20% poids

        // Score global pondéré (somme des composants)
        $global = (int)round(array_sum($components));

        return [
            'global' => $global,
            'components' => $components,
        ];
    }

    /**
     * Détermine la liste des toasts à afficher selon les préférences, la période silencieuse et un cooldown anti-spam.
     * @return array<int, array{type:string,message:string}>
     */
    public function generateToasts(int $userId, array $userConfig, array $stats): array
    {
        $toasts = [];

        // Période silencieuse
        $start = (int)($userConfig['notify_quiet_start_hour'] ?? 22);
        $end = (int)($userConfig['notify_quiet_end_hour'] ?? 7);
        $nowHour = (int)date('G'); // 0..23
        if ($this->isInQuietHours($start, $end, $nowHour))
        {
            return $toasts; // aucune alerte pendant la période silencieuse
        }

        // Préférences
        $notifyActivity = (int)($userConfig['notify_activity_enabled'] ?? 1) === 1;
        $notifyGoals = (int)($userConfig['notify_goals_enabled'] ?? 1) === 1;

        // Objectifs
        $activityTarget = (int)($userConfig['activite_objectif_minutes'] ?? 30);

        // Stats du jour
        $activityToday = (int)($stats['activity_minutes_today'] ?? 0);
        $completion = (int)($stats['objectifs_completion'] ?? 0);

        // Anti-spam via session (par utilisateur et catégorie)
        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }
        if (!isset($_SESSION['toast_last']))
        {
            $_SESSION['toast_last'] = [];
        }
        if (!isset($_SESSION['toast_last'][$userId]))
        {
            $_SESSION['toast_last'][$userId] = [];
        }

        // Définition des catégories en une seule structure
        $categories = [
            'activity_reminder' => function () use ($notifyActivity, $activityTarget, $activityToday)
            {
                if (!($notifyActivity && $activityTarget > 0 && $activityToday < $activityTarget))
                {
                    return null;
                }
                $reste = max(0, $activityTarget - $activityToday);

                return [
                    'type' => 'warning',
                    'message' => sprintf("Activité: encore %d min pour l'objectif 🏃", $reste),
                ];
            },
            'goals_success' => function () use ($notifyGoals, $completion)
            {
                if (!($notifyGoals && $completion >= 100))
                {
                    return null;
                }

                return [
                    'type' => 'success',
                    'message' => 'Bravo ! Tous vos objectifs du jour sont atteints 🎉',
                ];
            },
        ];

        foreach ($categories as $key => $builder)
        {
            $toast = $builder();
            if ($toast && $this->allowToast($userId, (string)$key))
            {
                $toasts[] = $toast;
                $this->markToast($userId, (string)$key);
            }
        }

        return $toasts;
    }

    private function isInQuietHours(int $start, int $end, int $hourNow): bool
    {
        if ($start === $end)
        {
            return false; // 0h de silence si égalité (l'UI empêche normalement)
        }
        if ($start < $end)
        {
            return $hourNow >= $start && $hourNow < $end;
        }

        // Plage qui traverse minuit (ex: 22 -> 7)
        return $hourNow >= $start || $hourNow < $end;
    }

    private function allowToast(int $userId, string $key): bool
    {
        $last = $_SESSION['toast_last'][$userId][$key] ?? 0;
        $cooldown = self::TOAST_COOLDOWN_MINUTES * 60;

        return (time() - (int)$last) >= $cooldown;
    }

    private function markToast(int $userId, string $key): void
    {
        $_SESSION['toast_last'][$userId][$key] = time();
    }

    /**
     * Génère les statistiques du dashboard.
     */
    public function getDashboardStats($user)
    {
        if (!$user)
        {
            return [];
        }

        $namespace = 'dashboard';
        $key = 'stats_' . $user['id'] . '_' . date('Y-m-d');

        $cached = $this->cache->get($namespace, $key);
        if ($cached !== null)
        {
            return $cached;
        }

        // Récupérer les objectifs de l'utilisateur
        $objectifs = $this->objectifsModel->getByUser($user['id']);

        // Calculer les vraies statistiques
        $stats = [
            'imc' => $objectifs['imc'] ?? 0,
            'calories_target' => $objectifs['calories_perte'] ?? 0,
            'objectifs_completion' => $this->calculateObjectifsCompletion($user['id'], $objectifs),
            // Macronutriments du jour
            'calories_consumed' => $this->getCaloriesConsumedToday($user['id']),
            'proteines_consumed' => $this->getProteinesConsumedToday($user['id']),
            'glucides_consumed' => $this->getGlucidesConsumedToday($user['id']),
            'lipides_consumed' => $this->getLipidesConsumedToday($user['id']),
            // Activité
            'activity_minutes_today' => $this->getActivityMinutesToday($user['id']),
        ];

        $this->cache->set($namespace, $key, $stats, \App\Service\CacheService::TTL_SHORT);

        return $stats;
    }

    /**
     * Calcule le pourcentage d'objectifs atteints.
     */
    public function calculateObjectifsCompletion($userId, $objectifs)
    {
        if (!$objectifs)
        {
            return 0;
        }

        // Calculer les calories consommées aujourd'hui
        $caloriesConsumed = $this->getCaloriesConsumedToday($userId);
        $caloriesTarget = $objectifs['calories_perte'] ?? 0;

        // Calculer les protéines consommées aujourd'hui
        $proteinesConsumed = $this->getProteinesConsumedToday($userId);
        $proteinesTarget = $objectifs['proteines_max'] ?? 0;

        // Calculer les fibres consommées aujourd'hui
        $fibresConsumed = $this->getFibresConsumedToday($userId);
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
     * Récupère les calories consommées aujourd'hui.
     */
    public function getCaloriesConsumedToday($userId)
    {
        $namespace = 'dashboard';
        $key = 'calories_' . $userId . '_' . date('Y-m-d');

        $cached = $this->cache->get($namespace, $key);
        if ($cached !== null)
        {
            return $cached;
        }

        try
        {
            $stmt = $this->db->prepare("
                SELECT SUM(ra.quantite_g * a.calories_100g / 100) as total_calories
                FROM repas r
                JOIN repas_aliments ra ON r.id = ra.repas_id
                JOIN aliments a ON ra.aliment_id = a.id
                WHERE r.user_id = ? AND DATE(r.date_heure) = date('now')
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $value = (float)($result['total_calories'] ?? 0);
        } catch (Exception $e)
        {
            error_log('Erreur calcul calories: ' . $e->getMessage());
            $value = 0;
        }

        $this->cache->set($namespace, $key, $value, \App\Service\CacheService::TTL_SHORT);

        return $value;
    }

    /**
     * Récupère les protéines consommées aujourd'hui.
     */
    public function getProteinesConsumedToday($userId)
    {
        $namespace = 'dashboard';
        $key = 'proteines_' . $userId . '_' . date('Y-m-d');

        $cached = $this->cache->get($namespace, $key);
        if ($cached !== null)
        {
            return $cached;
        }

        try
        {
            $stmt = $this->db->prepare("
                SELECT SUM(ra.quantite_g * a.proteines_100g / 100) as total_proteines
                FROM repas r
                JOIN repas_aliments ra ON r.id = ra.repas_id
                JOIN aliments a ON ra.aliment_id = a.id
                WHERE r.user_id = ? AND DATE(r.date_heure) = date('now')
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $value = (float)($result['total_proteines'] ?? 0);
        } catch (Exception $e)
        {
            error_log('Erreur calcul protéines: ' . $e->getMessage());
            $value = 0;
        }

        $this->cache->set($namespace, $key, $value, \App\Service\CacheService::TTL_SHORT);

        return $value;
    }

    /**
     * Récupère les fibres consommées aujourd'hui.
     */
    public function getFibresConsumedToday($userId)
    {
        $namespace = 'dashboard';
        $key = 'fibres_' . $userId . '_' . date('Y-m-d');

        $cached = $this->cache->get($namespace, $key);
        if ($cached !== null)
        {
            return $cached;
        }

        try
        {
            $stmt = $this->db->prepare("
                SELECT SUM(ra.quantite_g * a.fibres_100g / 100) as total_fibres
                FROM repas r
                JOIN repas_aliments ra ON r.id = ra.repas_id
                JOIN aliments a ON ra.aliment_id = a.id
                WHERE r.user_id = ? AND DATE(r.date_heure) = date('now')
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $value = (float)($result['total_fibres'] ?? 0);
        } catch (Exception $e)
        {
            error_log('Erreur calcul fibres: ' . $e->getMessage());
            $value = 0;
        }

        $this->cache->set($namespace, $key, $value, \App\Service\CacheService::TTL_SHORT);

        return $value;
    }

    /**
     * Récupère les glucides consommés aujourd'hui.
     */
    public function getGlucidesConsumedToday($userId)
    {
        $namespace = 'dashboard';
        $key = 'glucides_' . $userId . '_' . date('Y-m-d');

        $cached = $this->cache->get($namespace, $key);
        if ($cached !== null)
        {
            return $cached;
        }

        if (!$this->db)
        {
            return 0;
        }

        try
        {
            $stmt = $this->db->prepare("
                SELECT SUM(ra.quantite_g * a.glucides_100g / 100) as total_glucides
                FROM repas r
                JOIN repas_aliments ra ON r.id = ra.repas_id
                JOIN aliments a ON ra.aliment_id = a.id
                WHERE r.user_id = ? AND DATE(r.date_heure) = date('now')
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $value = (float)($result['total_glucides'] ?? 0);
        } catch (Exception $e)
        {
            error_log('Erreur calcul glucides: ' . $e->getMessage());
            $value = 0;
        }

        $this->cache->set($namespace, $key, $value, \App\Service\CacheService::TTL_SHORT);

        return $value;
    }

    /**
     * Récupère les lipides consommés aujourd'hui.
     */
    public function getLipidesConsumedToday($userId)
    {
        $namespace = 'dashboard';
        $key = 'lipides_' . $userId . '_' . date('Y-m-d');

        $cached = $this->cache->get($namespace, $key);
        if ($cached !== null)
        {
            return $cached;
        }

        if (!$this->db)
        {
            return 0;
        }

        try
        {
            $stmt = $this->db->prepare("
                SELECT SUM(ra.quantite_g * a.lipides_100g / 100) as total_lipides
                FROM repas r
                JOIN repas_aliments ra ON r.id = ra.repas_id
                JOIN aliments a ON ra.aliment_id = a.id
                WHERE r.user_id = ? AND DATE(r.date_heure) = date('now')
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $value = (float)($result['total_lipides'] ?? 0);
        } catch (Exception $e)
        {
            error_log('Erreur calcul lipides: ' . $e->getMessage());
            $value = 0;
        }

        $this->cache->set($namespace, $key, $value, \App\Service\CacheService::TTL_SHORT);

        return $value;
    }

    /**
     * Génère les objectifs quotidiens.
     */
    public function getDailyGoals(?array $objectifs, ?int $userId = null): array
    {
        // Valeurs par défaut si objectifs null
        $caloriesTarget = $objectifs['calories_perte'] ?? 1800;
        $proteinesMin = $objectifs['proteines_min'] ?? 69.1;
        $proteinesMax = $objectifs['proteines_max'] ?? 86.4;
        $fibresMin = $objectifs['fibres_min'] ?? 25;
        $fibresMax = $objectifs['fibres_max'] ?? 30;

        // Cibles dérivées pour glucides (approximation sur base calories)
        $glucidesTarget = $caloriesTarget > 0 ? round(($caloriesTarget * 0.50) / 4) : 250; // ~50% kcal / 4 kcal/g

        // Objectif activité via configuration utilisateur si disponible
        try
        {
            $activityTarget = $userId ? (int)$this->userConfigModel->get($userId, 'activite_objectif_minutes') : 30;
        } catch (\Throwable $e)
        {
            $activityTarget = 30;
        }

        // Formatage des targets pour éviter duplication
        $proteinesTargetFormatted = number_format($proteinesMin, 1, ',', ' ') . '-' . number_format($proteinesMax, 1, ',', ' ') . ' g';
        $fibresTargetFormatted = $fibresMin . '-' . $fibresMax . ' g';
        $glucidesTargetFormatted = number_format($glucidesTarget, 0, ',', ' ') . ' g';
        $activityTargetFormatted = number_format($activityTarget, 0, ',', ' ') . ' min';

        // Récupérer les vraies valeurs actuelles depuis la BDD
        $currentProteines = $userId ? $this->getProteinesConsumedToday($userId) : 56;
        $currentFibres = $userId ? $this->getFibresConsumedToday($userId) : 24;
        $currentGlucides = $userId ? $this->getGlucidesConsumedToday($userId) : 0;
        $currentActivityMin = $userId ? $this->getActivityMinutesToday($userId) : 0;

        return [
            $this->createGoal('fa-person-running', 'purple', 'Activité', $activityTargetFormatted, $currentActivityMin, $activityTarget, null, 'min'),
            $this->createGoal('fa-bread-slice', 'orange', 'Glucides', $glucidesTargetFormatted, $currentGlucides, $glucidesTarget, null, 'g'),
            $this->createGoal('fa-dumbbell', 'purple', 'Protéines', $proteinesTargetFormatted, $currentProteines, $proteinesMax, null, 'g'),
            $this->createGoal('fa-seedling', 'green', 'Fibres', $fibresTargetFormatted, $currentFibres, $fibresMax, null, 'g'),
        ];
    }

    /**
     * Récupère le total des minutes d'activité physique aujourd'hui.
     */
    public function getActivityMinutesToday($userId)
    {
        $namespace = 'dashboard';
        $key = 'activity_' . $userId . '_' . date('Y-m-d');

        $cached = $this->cache->get($namespace, $key);
        if ($cached !== null)
        {
            return $cached;
        }

        if (!$this->db)
        {
            return 0;
        }

        try
        {
            $today = date('Y-m-d');
            $stmt = $this->db->prepare('
                SELECT SUM(duree_minutes) as total_minutes
                FROM activites_physiques
                WHERE user_id = ? AND date_heure BETWEEN ? AND ?
            ');
            $stmt->execute([$userId, $today . ' 00:00:00', $today . ' 23:59:59']);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $value = (int)($result['total_minutes'] ?? 0);
        } catch (Exception $e)
        {
            error_log('Erreur calcul activité minutes: ' . $e->getMessage());
            $value = 0;
        }

        $this->cache->set($namespace, $key, $value, \App\Service\CacheService::TTL_SHORT);

        return $value;
    }

    /**
     * Factory method pour créer un objectif quotidien.
     */
    private function createGoal(string $icon, string $color, string $label, string $target, float|int $current, float|int $total, ?float $progress = null, string $unit = ''): array
    {
        if ($progress === null)
        {
            // Calcul automatique du progrès si pas fourni
            $progress = $total > 0 ? min(100, ($current / $total) * 100) : 0;
        }

        return [
            'icon' => $icon,
            'color' => $color,
            'label' => $label,
            'target' => $target,
            'current' => $current,
            'total' => $total,
            'unit' => $unit,
            'progress' => round($progress, 1),
        ];
    }

    /**
     * Récupère les 5 dernières activités de l'utilisateur.
     * Combine : repas et activités physiques.
     */
    public function getRecentActivity($userId)
    {
        $namespace = 'dashboard';
        $key = 'recent_activity_' . $userId;

        $cached = $this->cache->get($namespace, $key);
        if ($cached !== null)
        {
            return $cached;
        }

        if (!$this->db)
        {
            return [];
        }

        try
        {
            $activities = [];

            // 1. Repas récents (3 derniers)
            $stmt = $this->db->prepare('
                SELECT 
                    r.id,
                    r.type_repas as meal_type,
                    r.date_heure,
                    SUM(ra.quantite_g * a.calories_100g / 100) as total_calories
                FROM repas r
                LEFT JOIN repas_aliments ra ON r.id = ra.repas_id
                LEFT JOIN aliments a ON ra.aliment_id = a.id
                WHERE r.user_id = ?
                GROUP BY r.id, r.type_repas, r.date_heure
                ORDER BY r.date_heure DESC
                LIMIT 3
            ');
            $stmt->execute([$userId]);
            $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($meals as $meal)
            {
                $calories = $meal['total_calories'] !== null ? round((float)$meal['total_calories']) : 0;
                $activities[] = [
                    'type' => 'meal',
                    'icon' => 'fa-utensils',
                    'color' => 'green',
                    'title' => 'Repas ajouté',
                    'description' => ucfirst($meal['meal_type']) . ' - ' . $calories . ' kcal',
                    'datetime' => $meal['date_heure'],
                    'timestamp' => strtotime($meal['date_heure'] ?? 'now'),
                ];
            }

            // 3. Activités physiques récentes (2 dernières)
            $stmt = $this->db->prepare('
                SELECT 
                    type_activite,
                    duree_minutes,
                    date_heure
                FROM activites_physiques
                WHERE user_id = ?
                ORDER BY date_heure DESC
                LIMIT 2
            ');
            $stmt->execute([$userId]);
            $physicalActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($physicalActivities as $activity)
            {
                $activities[] = [
                    'type' => 'activity',
                    'icon' => 'fa-person-running',
                    'color' => 'purple',
                    'title' => 'Activité physique',
                    'description' => ucfirst($activity['type_activite']) . ' - ' . $activity['duree_minutes'] . ' min',
                    'datetime' => $activity['date_heure'],
                    'timestamp' => strtotime($activity['date_heure'] ?? 'now'),
                ];
            }

            // Trier par timestamp décroissant
            usort($activities, function ($a, $b)
            {
                return $b['timestamp'] - $a['timestamp'];
            });

            // Limiter à 5 items
            $value = array_slice($activities, 0, 5);
        } catch (Exception $e)
        {
            error_log('Erreur récupération activités récentes: ' . $e->getMessage());
            $value = [];
        }

        $this->cache->set($namespace, $key, $value, \App\Service\CacheService::TTL_SHORT);

        return $value;
    }
}
