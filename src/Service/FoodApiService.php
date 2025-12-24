<?php

namespace App\Service;

use App\Service\FoodQualityService;

/**
 * FoodApiService - Service de formatage des réponses API pour les aliments
 * Responsabilités :
 * - Formatage des réponses JSON pour les recherches
 * - Formatage des réponses AJAX pour le catalogue
 * - Enrichissement des données avec les scores qualité
 * - Gestion des erreurs API
 */
class FoodApiService
{
    private FoodQualityService $foodQualityService;

    public function __construct(FoodQualityService $foodQualityService)
    {
        $this->foodQualityService = $foodQualityService;
    }

    /**
     * Formate les résultats de recherche pour l'API
     */
    public function formatSearchResults(array $results, ?string $error = null): array
    {
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
            ];
        }

        return [
            'success' => true,
            'results' => $results,
        ];
    }

    /**
     * Formate les données du catalogue pour AJAX
     */
    public function formatCatalogData(array $catalogData): array
    {
        $foods = array_map(function ($food) {
            return $this->enrichFoodWithQuality($food);
        }, $catalogData['foods']);

        return [
            'success' => true,
            'foods' => $foods,
            'pagination' => [
                'total' => $catalogData['totalFoods'],
                'pages' => $catalogData['totalPages'],
                'current' => $catalogData['currentPage'],
                'perPage' => $catalogData['perPage'],
            ],
        ];
    }

    /**
     * Formate une réponse de sauvegarde d'aliment
     */
    public function formatSaveResult(array $result): array
    {
        return [
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? '',
            'food_id' => $result['food_id'] ?? null,
            'error' => $result['error'] ?? null,
        ];
    }

    /**
     * Formate une réponse d'ajout au repas
     */
    public function formatAddToMealResult(array $result): array
    {
        return [
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? '',
            'meal_id' => $result['meal_id'] ?? null,
            'error' => $result['error'] ?? null,
        ];
    }

    /**
     * Formate une réponse de suppression
     */
    public function formatDeleteResult(array $result): array
    {
        return [
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? '',
            'error' => $result['error'] ?? null,
        ];
    }

    /**
     * Enrichit un aliment avec les données de qualité
     */
    private function enrichFoodWithQuality(array $food): array
    {
        // Normaliser les données nutritionnelles pour le calcul de qualité
        $nutriments = [
            'proteins_100g' => $food['proteines_100g'] ?? 0,
            'saturated-fat_100g' => $food['acides_gras_satures_100g'] ?? 0,
            'fiber_100g' => $food['fibres_100g'] ?? 0,
            'sugars_100g' => $food['sucres_100g'] ?? 0,
            'energy-kcal_100g' => $food['calories_100g'] ?? 0,
        ];

        $food['quality_score'] = $this->foodQualityService->calculateGradeFromNutriments($nutriments);

        return $food;
    }
}