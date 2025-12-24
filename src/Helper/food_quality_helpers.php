<?php

/**
 * Helpers pour les composants de notation alimentaire
 * Fonctions utilitaires pour intégrer facilement les notations.
 */

/**
 * Génère le HTML pour un badge de notation simple.
 */
function renderFoodQualityBadge(int $foodId, string $size = 'md', bool $showTooltip = true): string
{
    static $qualityService = null;
    if ($qualityService === null)
    {
        $qualityService = new \App\Service\FoodQualityService();
    }

    $grade = $qualityService->getFoodGrade($foodId);

    ob_start();
    include __DIR__ . '/../View/components/food-quality-badge.php';

    return ob_get_clean();
}

/**
 * Génère le HTML pour les détails complets de notation.
 */
function renderFoodQualityDetails(int $foodId): string
{
    static $qualityService = null;
    static $mealModel = null;

    if ($qualityService === null)
    {
        $qualityService = new \App\Service\FoodQualityService();
    }
    if ($mealModel === null)
    {
        $mealModel = new \App\Model\MealModel();
    }

    $grade = $qualityService->getFoodGrade($foodId);
    $macros = $mealModel->getFoodById($foodId);

    ob_start();
    include __DIR__ . '/../View/components/food-quality-details.php';

    return ob_get_clean();
}

/**
 * Génère le HTML pour un badge de notation simple depuis les données nutritionnelles.
 */
function renderFoodQualityBadgeFromData(array $nutriments, string $size = 'md', bool $showTooltip = true): string
{
    static $qualityService = null;
    if ($qualityService === null)
    {
        $qualityService = new \App\Service\FoodQualityService();
    }

    $grade = $qualityService->calculateGradeFromNutriments($nutriments);

    ob_start();
    include __DIR__ . '/../View/components/food-quality-badge.php';

    return ob_get_clean();
}

/**
 * Génère le HTML pour les détails complets de notation depuis les données nutritionnelles.
 */
function renderFoodQualityDetailsFromData(array $nutriments): string
{
    static $qualityService = null;
    if ($qualityService === null)
    {
        $qualityService = new \App\Service\FoodQualityService();
    }

    $grade = $qualityService->calculateGradeFromNutriments($nutriments);
    $macros = $nutriments; // Les données nutritionnelles brutes

    ob_start();
    include __DIR__ . '/../View/components/food-quality-details.php';

    return ob_get_clean();
}

/**
 * Génère les données de qualité depuis les données nutritionnelles.
 */
function getFoodQualityData(array $nutriments): array
{
    static $qualityService = null;
    if ($qualityService === null)
    {
        $qualityService = new \App\Service\FoodQualityService();
    }

    $grade = $qualityService->calculateGradeFromNutriments($nutriments);

    return [
        'grade' => $grade,
        'nutriments' => $nutriments,
    ];
}

/**
 * Génère les données pour plusieurs aliments.
 */
function getFoodsQualityData(array $foodIds): array
{
    static $qualityService = null;
    if ($qualityService === null)
    {
        $qualityService = new \App\Service\FoodQualityService();
    }

    return $qualityService->getFoodsGrades($foodIds);
}

/**
 * Helper pour les classes CSS selon la notation.
 */
function getQualityColorClasses(string $grade): array
{
    $colors = [
        'A' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-200'],
        'B' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-200'],
        'C' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'border' => 'border-yellow-200'],
        'D' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200'],
    ];

    return $colors[$grade] ?? $colors['D'];
}
