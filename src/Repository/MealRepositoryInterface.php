<?php

namespace App\Repository;

/**
 * MealRepositoryInterface - Interface pour les opérations sur les repas.
 */
interface MealRepositoryInterface
{
    /**
     * Récupère un repas par date et type pour un utilisateur.
     */
    public function getMealByDateAndType(int $userId, string $date, string $mealType): ?array;

    /**
     * Crée un repas avec un aliment en transaction atomique.
     */
    public function createMealWithFood(int $userId, string $mealType, int $foodId, float $quantity): int|false;

    /**
     * Ajoute un aliment à un repas existant.
     */
    public function addFoodToMeal(int $mealId, int $foodId, float $quantity): bool;

    /**
     * Récupère les repas d'un utilisateur pour une date donnée.
     */
    public function getMealsByDate(int $userId, string $date): array;

    /**
     * Supprime un aliment d'un repas.
     */
    public function removeFoodFromMeal(int $mealId, int $foodId): bool;
}
