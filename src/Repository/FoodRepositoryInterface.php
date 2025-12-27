<?php

namespace App\Repository;

/**
 * FoodRepositoryInterface - Interface pour les opérations sur les aliments sauvegardés.
 */
interface FoodRepositoryInterface
{
    /**
     * Recherche des aliments sauvegardés par nom.
     */
    public function searchSavedFoods(string $query): array;

    /**
     * Récupère tous les aliments sauvegardés avec pagination.
     */
    public function getSavedFoods(?int $limit = null, int $offset = 0): array;

    /**
     * Compte le nombre total d'aliments sauvegardés.
     */
    public function countSavedFoods(): int;

    /**
     * Sauvegarde un aliment depuis l'API OpenFoodFacts.
     */
    public function saveFoodFromAPI(array $data): bool;

    /**
     * Supprime un aliment par son ID.
     */
    public function deleteFood(int $foodId): bool;

    /**
     * Vérifie si un aliment existe par son barcode.
     */
    public function foodExistsByBarcode(string $barcode): bool;

    /**
     * Trouve un aliment par son ID.
     */
    public function findById(int $id): ?array;
}
