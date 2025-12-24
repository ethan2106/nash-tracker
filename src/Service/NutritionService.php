<?php

namespace App\Service;

use Exception;
use PDO;

/**
 * NutritionService - Gère tous les calculs nutritionnels.
 * Responsabilités :
 * - Calcul de la nutrition quotidienne
 * - Calcul de la nutrition hebdomadaire
 * - Calcul des scores santé.
 */
class NutritionService
{
    private PDO $db;

    private CacheService $cache;

    // Configuration centralisée des nutriments (déplacée depuis ProfileController)
    public const NUTRITION_CONFIG = [
        'calories' => [
            'label' => 'Calories',
            'unit' => 'kcal',
            'operator' => '>=',
            'weight' => 2, // Pondération plus élevée
        ],
        'proteines' => [
            'label' => 'Protéines',
            'unit' => 'g',
            'operator' => '>=',
            'weight' => 1,
        ],
        'fibres' => [
            'label' => 'Fibres',
            'unit' => 'g',
            'operator' => '>=',
            'weight' => 1,
        ],
        'graisses_sat' => [
            'label' => 'Graisses saturées',
            'unit' => 'g',
            'operator' => '<=',
            'weight' => 2, // Pondération plus élevée
        ],
    ];

    public function __construct(PDO $db, ?CacheService $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? new CacheService();
    }

    /**
     * Calcule la nutrition consommée aujourd'hui.
     */
    public function getCurrentNutrition(int $userId): array
    {
        $cacheKey = 'current_nutrition_' . $userId . '_' . date('Y-m-d');

        return $this->cache->remember('nutrition', $cacheKey, function () use ($userId)
        {
            try
            {
                $today = date('Y-m-d');
                $stmt = $this->db->prepare('
                    SELECT
                        COALESCE(SUM(ra.quantite_g * a.calories_100g / 100), 0) AS total_calories,
                        COALESCE(SUM(ra.quantite_g * a.proteines_100g / 100), 0) AS total_proteines,
                        COALESCE(SUM(ra.quantite_g * a.fibres_100g / 100), 0) AS total_fibres,
                        COALESCE(SUM(ra.quantite_g * a.acides_gras_satures_100g / 100), 0) AS total_graisses_sat
                    FROM repas r
                    JOIN repas_aliments ra ON r.id = ra.repas_id
                    JOIN aliments a ON ra.aliment_id = a.id
                    WHERE r.user_id = ? AND date(r.date_heure) = ?
                ');
                $stmt->execute([$userId, $today]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                return [
                    'calories' => (float)$result['total_calories'],
                    'proteines' => (float)$result['total_proteines'],
                    'fibres' => (float)$result['total_fibres'],
                    'graisses_sat' => (float)$result['total_graisses_sat'],
                ];
            } catch (Exception $e)
            {
                error_log('Erreur calcul nutrition actuelle: ' . $e->getMessage());

                return [
                    'calories' => 0.0,
                    'proteines' => 0.0,
                    'fibres' => 0.0,
                    'graisses_sat' => 0.0,
                ];
            }
        }, CacheService::TTL_SHORT); // Cache pendant 5 minutes pour données du jour
    }

    /**
     * Récupère les données nutritionnelles des 7 derniers jours.
     */
    public function getWeeklyNutrition(int $userId): array
    {
        $cacheKey = 'weekly_nutrition_' . $userId . '_' . date('Y-m-d');

        return $this->cache->remember('nutrition', $cacheKey, function () use ($userId)
        {
            try
            {
                $stmt = $this->db->prepare("
                    SELECT
                        DATE(r.date_heure) AS date,
                        COALESCE(SUM(ra.quantite_g * a.calories_100g / 100), 0) AS calories,
                        COALESCE(SUM(ra.quantite_g * a.proteines_100g / 100), 0) AS proteines,
                        COALESCE(SUM(ra.quantite_g * a.fibres_100g / 100), 0) AS fibres,
                        COALESCE(SUM(ra.quantite_g * a.acides_gras_satures_100g / 100), 0) AS graisses_sat
                    FROM repas r
                    LEFT JOIN repas_aliments ra ON r.id = ra.repas_id
                    LEFT JOIN aliments a ON ra.aliment_id = a.id
                    WHERE r.user_id = ? AND r.date_heure >= date('now', '-6 days')
                    GROUP BY DATE(r.date_heure)
                    ORDER BY DATE(r.date_heure) ASC
                ");
                $stmt->execute([$userId]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Organiser par jour de la semaine (Lun à Dim)
                $days = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
                $weeklyData = [];

                for ($i = 6; $i >= 0; $i--)
                {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $found = false;
                    foreach ($results as $result)
                    {
                        if ($result['date'] === $date)
                        {
                            $weeklyData[] = [
                                'date' => $date,
                                'day' => $days[6 - $i],
                                'calories' => (float)$result['calories'],
                                'proteines' => (float)$result['proteines'],
                                'fibres' => (float)$result['fibres'],
                                'graisses_sat' => (float)$result['graisses_sat'],
                            ];
                            $found = true;

                            break;
                        }
                    }
                    if (!$found)
                    {
                        $weeklyData[] = [
                            'date' => $date,
                            'day' => $days[6 - $i],
                            'calories' => 0.0,
                            'proteines' => 0.0,
                            'fibres' => 0.0,
                            'graisses_sat' => 0.0,
                        ];
                    }
                }

                return $weeklyData;
            } catch (Exception $e)
            {
                error_log('Erreur récupération nutrition hebdomadaire: ' . $e->getMessage());
                // Retourner des données vides pour les 7 jours
                $days = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

                return array_map(function ($day)
                {
                    return [
                        'date' => '',
                        'day' => $day,
                        'calories' => 0.0,
                        'proteines' => 0.0,
                        'fibres' => 0.0,
                        'graisses_sat' => 0.0,
                    ];
                }, $days);
            }
        }, CacheService::TTL_MEDIUM); // Cache pendant 30 minutes pour données hebdomadaires
    }

    /**
     * Calcule le score santé basé sur les objectifs atteints.
     */
    public function calculateHealthScore(?array $currentNutrition, ?array $objectifs): int
    {
        if (!$objectifs || !$currentNutrition)
        {
            return 0;
        }

        // Fonction interne pour vérifier un critère
        $checkCriterion = fn ($current, $threshold, $op) => ($op === '>=') ? $current >= $threshold : $current <= $threshold;

        // Définition des critères d'évaluation avec pondération
        $criteria = [
            [
                'key' => 'calories',
                'target' => $objectifs['calories_perte'],
                'threshold' => $objectifs['calories_perte'] * 0.8,
                'weight' => self::NUTRITION_CONFIG['calories']['weight'],
            ],
            [
                'key' => 'proteines',
                'target' => $objectifs['proteines_min'],
                'threshold' => $objectifs['proteines_min'],
                'weight' => self::NUTRITION_CONFIG['proteines']['weight'],
            ],
            [
                'key' => 'fibres',
                'target' => $objectifs['fibres_min'],
                'threshold' => $objectifs['fibres_min'],
                'weight' => self::NUTRITION_CONFIG['fibres']['weight'],
            ],
            [
                'key' => 'graisses_sat',
                'target' => $objectifs['graisses_sat_max'],
                'threshold' => $objectifs['graisses_sat_max'],
                'weight' => self::NUTRITION_CONFIG['graisses_sat']['weight'],
            ],
        ];

        $weightedScore = 0;
        $totalWeight = 0;

        foreach ($criteria as $criterion)
        {
            if ($criterion['target'] > 0)
            {
                $totalWeight += $criterion['weight'];
                $currentValue = $currentNutrition[$criterion['key']];
                $operator = self::NUTRITION_CONFIG[$criterion['key']]['operator'];

                if ($checkCriterion($currentValue, $criterion['threshold'], $operator))
                {
                    $weightedScore += $criterion['weight'];
                }
            }
        }

        return $totalWeight > 0 ? round(($weightedScore / $totalWeight) * 100) : 0;
    }
}
