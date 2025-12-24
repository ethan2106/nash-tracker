<?php

namespace App\Service;

/**
 * FoodQualityService - Service de notation qualité nutritionnelle des aliments
 * Calcule un score A/B/C/D/E basé sur les macros nutritionnelles (style Nutri-Score).
 * Utilise un système de points pondérés selon des seuils définis.
 * Intègre un système de cache pour optimiser les performances.
 */
class FoodQualityService
{
    private CacheService $cache;

    private ?\App\Model\MealModel $mealModel = null;

    // Seuils configurables pour la notation
    private const THRESHOLDS = [
        'protein_good' => 15,    // g/100g - Excellent
        'protein_ok' => 7,       // g/100g - Bon
        'sat_fat_bad' => 7,      // g/100g - Mauvais
        'sat_fat_ok' => 3,       // g/100g - Acceptable
        'fiber_good' => 3,       // g/100g - Excellent
        'fiber_ok' => 1.5,       // g/100g - Bon
        'sugar_bad' => 5,        // g/100g - Mauvais
        'calories_low' => 50,    // kcal/100g - Peu calorique
        'calories_high' => 400,  // kcal/100g - Très calorique
    ];

    public function __construct()
    {
        // Initialiser le cache toujours
        $this->cache = new CacheService();

        // Initialiser le MealModel seulement si on en a besoin (lazy loading)
        // $this->mealModel sera initialisé dans getFoodGrade() si nécessaire
    }

    /**
     * Calcule la notation d'un aliment.
     */
    public function getFoodGrade(int $foodId): array
    {
        // Initialiser MealModel si pas encore fait
        if ($this->mealModel === null)
        {
            $this->mealModel = new \App\Model\MealModel();
        }

        $cacheKey = "food_grade_$foodId";

        return $this->cache->remember('food_quality', $cacheKey, function () use ($foodId)
        {
            $food = $this->mealModel->getFoodById($foodId);

            if (!$food)
            {
                return $this->getDefaultGrade();
            }

            return $this->calculateGrade($food);
        }, CacheService::TTL_MEDIUM);
    }

    /**
     * Calcule la notation pour plusieurs aliments.
     */
    public function getFoodsGrades(array $foodIds): array
    {
        $grades = [];
        foreach ($foodIds as $foodId)
        {
            $grades[$foodId] = $this->getFoodGrade($foodId);
        }

        return $grades;
    }

    /**
     * Calcule la notation basée sur les macros.
     */
    private function calculateGrade(array $food): array
    {
        $score = 0;
        $maxScore = 8; // 8 critères possibles

        // 1. Protéines (0-2 points)
        $proteins = (float)($food['proteines_100g'] ?? 0);
        if ($proteins >= self::THRESHOLDS['protein_good'])
        {
            $score += 2; // Excellent
        } elseif ($proteins >= self::THRESHOLDS['protein_ok'])
        {
            $score += 1; // Bon
        }

        // 2. Graisses saturées (0-2 points, inversé)
        $satFat = (float)($food['acides_gras_satures_100g'] ?? 0);
        if ($satFat <= self::THRESHOLDS['sat_fat_ok'])
        {
            $score += 2; // Excellent
        } elseif ($satFat <= self::THRESHOLDS['sat_fat_bad'])
        {
            $score += 1; // Acceptable
        }

        // 3. Fibres (0-2 points)
        $fiber = (float)($food['fibres_100g'] ?? 0);
        if ($fiber >= self::THRESHOLDS['fiber_good'])
        {
            $score += 2; // Excellent
        } elseif ($fiber >= self::THRESHOLDS['fiber_ok'])
        {
            $score += 1; // Bon
        }

        // 4. Sucres (0-1 point, inversé)
        $sugar = (float)($food['sucres_100g'] ?? 0);
        if ($sugar <= self::THRESHOLDS['sugar_bad'])
        {
            $score += 1; // Bon
        }

        // 5. Densité calorique (0-1 point)
        $calories = (float)($food['calories_100g'] ?? 0);
        if ($calories <= self::THRESHOLDS['calories_low'])
        {
            $score += 1; // Peu calorique = bon
        }

        // Calcul du pourcentage et notation
        $percentage = ($score / $maxScore) * 100;

        return $this->percentageToGrade($percentage);
    }

    /**
     * Convertit un pourcentage en notation A/B/C/D/E.
     * Système aligné sur le Nutri-Score pour une meilleure compréhension.
     */
    private function percentageToGrade(float $percentage): array
    {
        if ($percentage >= 75)
        {
            return [
                'grade' => 'A',
                'label' => 'Excellent',
                'color' => 'green',
                'bg_color' => 'bg-green-100',
                'text_color' => 'text-green-800',
                'percentage' => round($percentage),
                'description' => 'Aliment très équilibré nutritionnellement',
            ];
        } elseif ($percentage >= 55)
        {
            return [
                'grade' => 'B',
                'label' => 'Très bien',
                'color' => 'blue',
                'bg_color' => 'bg-blue-100',
                'text_color' => 'text-blue-800',
                'percentage' => round($percentage),
                'description' => 'Bon équilibre nutritionnel',
            ];
        } elseif ($percentage >= 35)
        {
            return [
                'grade' => 'C',
                'label' => 'Correct',
                'color' => 'yellow',
                'bg_color' => 'bg-yellow-100',
                'text_color' => 'text-yellow-800',
                'percentage' => round($percentage),
                'description' => 'Équilibre nutritionnel moyen',
            ];
        } elseif ($percentage >= 15)
        {
            return [
                'grade' => 'D',
                'label' => 'À limiter',
                'color' => 'orange',
                'bg_color' => 'bg-orange-100',
                'text_color' => 'text-orange-800',
                'percentage' => round($percentage),
                'description' => 'Aliment à consommer avec modération',
            ];
        } else
        {
            return [
                'grade' => 'E',
                'label' => 'À éviter',
                'color' => 'red',
                'bg_color' => 'bg-red-100',
                'text_color' => 'text-red-800',
                'percentage' => round($percentage),
                'description' => 'Aliment ultra-transformé ou très défavorable pour la santé',
            ];
        }
    }

    /**
     * Grade par défaut pour les aliments non trouvés.
     */
    private function getDefaultGrade(): array
    {
        return [
            'grade' => '?',
            'label' => 'Non évalué',
            'color' => 'gray',
            'bg_color' => 'bg-gray-100',
            'text_color' => 'text-gray-800',
            'percentage' => 0,
            'description' => 'Notation non disponible',
        ];
    }

    /**
     * Calcule la notation d'un aliment directement depuis ses données nutritionnelles
     * Utile pour les aliments venant d'APIs externes ou non sauvegardés.
     */
    public function calculateGradeFromNutriments(array $nutriments): array
    {
        // Normaliser les données nutritionnelles du format API vers le format interne
        $normalized = [
            'proteines_100g' => (float)($nutriments['proteins_100g'] ?? $nutriments['proteins'] ?? 0),
            'acides_gras_satures_100g' => (float)($nutriments['saturated-fat_100g'] ?? $nutriments['saturated-fat'] ?? 0),
            'fibres_100g' => (float)($nutriments['fiber_100g'] ?? $nutriments['fiber'] ?? 0),
            'sucres_100g' => (float)($nutriments['sugars_100g'] ?? $nutriments['sugars'] ?? 0),
            'calories_100g' => (float)($nutriments['energy-kcal_100g'] ?? $nutriments['energy-kcal'] ?? 0),
        ];

        return $this->calculateGrade($normalized);
    }

    /**
     * Invalide le cache d'une notation d'aliment.
     */
    public function invalidateFoodGrade(int $foodId): void
    {
        $this->cache->delete('food_quality', "food_grade_$foodId");
    }

    /**
     * Nettoie tout le cache des notations.
     */
    public function clearAllGrades(): int
    {
        return $this->cache->clearNamespace('food_quality');
    }
}
