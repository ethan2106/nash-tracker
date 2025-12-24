<?php

namespace App\Service;

use App\Model\MealModel;

/**
 * MealService - Service d'intégration pour les repas
 * Responsabilités :
 * - Coordination entre services pour les opérations complexes sur les repas
 * - Logique métier spécifique aux repas.
 */
class MealService
{
    private MealModel $mealModel;

    public function __construct(MealModel $mealModel)
    {
        $this->mealModel = $mealModel;
    }

    /**
     * Valider les données d'un repas.
     */
    public function validateMealData(array $data): array
    {
        $errors = [];

        if (empty($data['type_repas']))
        {
            $errors[] = 'Le type de repas est requis';
        }

        if (!isset($data['date']) || empty($data['date']))
        {
            $errors[] = 'La date est requise';
        }

        return $errors;
    }

    /**
     * Calculer les statistiques nutritionnelles d'un repas.
     */
    public function calculateMealNutrition(int $mealId): array
    {
        $mealDetails = $this->mealModel->getMealDetails($mealId);

        if (!$mealDetails)
        {
            return [];
        }

        $totalNutrition = [
            'calories' => 0,
            'proteines' => 0,
            'glucides' => 0,
            'lipides' => 0,
            'fibres' => 0,
        ];

        foreach ($mealDetails['aliments'] as $aliment)
        {
            $quantity = $aliment['quantite_g'];
            $nutrition = $aliment['nutrition'];

            $totalNutrition['calories'] += ($nutrition['calories_100g'] ?? 0) * $quantity / 100;
            $totalNutrition['proteines'] += ($nutrition['proteines_100g'] ?? 0) * $quantity / 100;
            $totalNutrition['glucides'] += ($nutrition['glucides_100g'] ?? 0) * $quantity / 100;
            $totalNutrition['lipides'] += ($nutrition['lipides_100g'] ?? 0) * $quantity / 100;
            $totalNutrition['fibres'] += ($nutrition['fibres_100g'] ?? 0) * $quantity / 100;
        }

        return $totalNutrition;
    }
}
