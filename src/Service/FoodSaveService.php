<?php

namespace App\Service;

use App\Repository\FoodRepositoryInterface;
use App\Repository\MealRepositoryInterface;

/**
 * FoodSaveService - Service de sauvegarde et validation des aliments
 * Responsabilités :
 * - Validation des données d'aliments
 * - Normalisation des données nutritionnelles
 * - Sauvegarde des aliments depuis l'API
 * - Ajout d'aliments aux repas
 * - Suppression d'aliments
 * - Gestion des aliments manuels.
 */
class FoodSaveService
{
    private FoodRepositoryInterface $foodRepository;

    private MealRepositoryInterface $mealRepository;

    private CacheService $cache;

    public function __construct(
        FoodRepositoryInterface $foodRepository,
        MealRepositoryInterface $mealRepository,
        CacheService $cache
    ) {
        $this->foodRepository = $foodRepository;
        $this->mealRepository = $mealRepository;
        $this->cache = $cache;
    }

    /**
     * Sauvegarde un aliment depuis l'API OpenFoodFacts.
     */
    public function saveFoodFromAPI(array $data): array
    {
        try
        {
            // Validation des données
            $validationErrors = $this->validateApiFoodData($data);
            if (!empty($validationErrors))
            {
                return [
                    'success' => false,
                    'error' => 'Données invalides: ' . implode(', ', $validationErrors),
                ];
            }

            // Normalisation des données
            $normalizedData = $this->normalizeApiFoodData($data);

            // Sauvegarde
            $success = $this->foodRepository->saveFoodFromAPI($normalizedData);

            if ($success)
            {
                $this->invalidateCaches();

                return [
                    'success' => true,
                    'message' => 'Aliment sauvegardé avec succès',
                ];
            }

            return [
                'success' => false,
                'error' => 'Erreur lors de la sauvegarde',
            ];
        } catch (\Exception $e)
        {
            error_log('Erreur sauvegarde aliment API: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Erreur interne lors de la sauvegarde',
            ];
        }
    }

    /**
     * Ajoute un aliment du catalogue à un repas.
     */
    public function addFoodFromCatalog(array $data): array
    {
        try
        {
            $foodId = $data['food_id'] ?? $data['aliment_id'] ?? null;
            $mealType = $data['meal_type'] ?? 'repas';
            $quantity = (float)($data['quantity'] ?? 100);

            if (!$foodId || $quantity <= 0)
            {
                return [
                    'success' => false,
                    'error' => 'Données invalides pour l\'ajout au repas',
                ];
            }

            // Normalisation du type de repas
            $mealType = $this->normalizeMealType($mealType);

            // Récupération ou création du repas
            $userId = $_SESSION['user']['id'] ?? null;
            if (!$userId)
            {
                return [
                    'success' => false,
                    'error' => 'Utilisateur non connecté',
                ];
            }

            $dateOnly = date('Y-m-d');
            $existingMeal = $this->mealRepository->getMealByDateAndType($userId, $dateOnly, $mealType);

            if ($existingMeal)
            {
                $mealId = $existingMeal['id'];
                $result = $this->mealRepository->addFoodToMeal($mealId, (int)$foodId, $quantity);
                if (!$result)
                {
                    // Vérifier si l'aliment existe
                    $foodExists = $this->checkFoodExists($foodId);
                    if (!$foodExists)
                    {
                        return [
                            'success' => false,
                            'error' => 'Cet aliment n\'existe plus dans le catalogue.',
                        ];
                    }

                    return [
                        'success' => false,
                        'error' => 'Erreur lors de l\'ajout au repas.',
                    ];
                }
            } else
            {
                $mealId = $this->mealRepository->createMealWithFood($userId, $mealType, (int)$foodId, $quantity);
                $result = ($mealId !== false);
                if (!$result)
                {
                    // Vérifier si l'aliment existe
                    $foodExists = $this->checkFoodExists($foodId);
                    if (!$foodExists)
                    {
                        return [
                            'success' => false,
                            'error' => 'Cet aliment n\'existe plus dans le catalogue.',
                        ];
                    }

                    return [
                        'success' => false,
                        'error' => 'Erreur lors de la création du repas.',
                    ];
                }
            }

            if ($result)
            {
                $this->invalidateCaches();

                return [
                    'success' => true,
                    'message' => 'Aliment ajouté au repas avec succès',
                    'meal_id' => $mealId,
                ];
            }

            return [
                'success' => false,
                'error' => 'Erreur lors de l\'ajout au repas',
            ];
        } catch (\Exception $e)
        {
            error_log('Erreur ajout aliment catalogue: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Erreur interne lors de l\'ajout',
            ];
        }
    }

    /**
     * Vérifie si un aliment existe dans la base de données.
     */
    private function checkFoodExists(int $foodId): bool
    {
        try
        {
            // Utiliser le repository pour vérifier l'existence
            $food = $this->foodRepository->findById($foodId);

            return $food !== null;
        } catch (\Exception $e)
        {
            return false;
        }
    }

    /**
     * Ajoute un aliment de la recherche à un repas.
     */
    public function addFoodFromSearch(array $data): array
    {
        // D'abord sauvegarder l'aliment, puis l'ajouter au repas
        $saveResult = $this->saveFoodFromAPI($data);

        if (!$saveResult['success'])
        {
            return $saveResult;
        }

        // Récupérer l'ID de l'aliment sauvegardé (nécessite une modification du repository)
        // Pour l'instant, on utilise la logique existante
        return $this->addFoodFromCatalog($data);
    }

    /**
     * Supprime un aliment.
     */
    public function deleteFood(int $foodId): array
    {
        try
        {
            $success = $this->foodRepository->deleteFood($foodId);

            if ($success)
            {
                $this->invalidateCaches();

                return [
                    'success' => true,
                    'message' => 'Aliment supprimé avec succès',
                ];
            }

            return [
                'success' => false,
                'message' => 'Cet aliment ne peut pas être supprimé car il est utilisé dans des repas.',
            ];
        } catch (\Exception $e)
        {
            error_log('Erreur suppression aliment: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression',
            ];
        }
    }

    /**
     * Ajoute un aliment manuel.
     */
    public function addFoodManually(array $data, array $files = []): array
    {
        try
        {
            // Validation et traitement des données manuelles
            // Cette méthode nécessiterait plus de logique pour traiter les uploads d'images
            // Pour l'instant, on retourne une structure similaire

            return [
                'success' => true,
                'message' => 'Aliment manuel ajouté (implémentation à compléter)',
            ];
        } catch (\Exception $e)
        {
            error_log('Erreur ajout aliment manuel: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Erreur lors de l\'ajout manuel',
            ];
        }
    }

    /**
     * Valide les données d'aliment depuis l'API.
     */
    private function validateApiFoodData(array $data): array
    {
        $errors = [];

        // Vérifier le nom du produit dans différents formats
        $productName = $data['product_name'] ?? $data['name'] ?? $data['food_name'] ?? '';
        if (empty($productName))
        {
            $errors[] = 'Nom du produit requis';
        }

        return $errors;
    }

    /**
     * Normalise les données d'aliment depuis l'API.
     */
    private function normalizeApiFoodData(array $data): array
    {
        // Gestion des données depuis JavaScript (food_data) ou champs séparés
        if (isset($data['food_data']))
        {
            $productData = json_decode($data['food_data'], true);

            return [
                'product_name' => $productData['name'] ?? $productData['product_name'] ?? '',
                'brands' => $productData['brands'] ?? '',
                'image_url' => $productData['image'] ?? '',
                'code' => $productData['barcode'] ?? $productData['code'] ?? '',
                'nutriments' => $productData['nutriments'] ?? [],
            ];
        }

        // Données depuis champs séparés
        $nutriments = json_decode($data['food_nutriments'] ?? '{}', true);

        return [
            'product_name' => $data['food_name'] ?? '',
            'brands' => $data['food_brands'] ?? '',
            'image_url' => $data['food_image'] ?? '',
            'code' => $data['food_barcode'] ?? '',
            'nutriments' => $nutriments,
        ];
    }

    /**
     * Normalise le type de repas.
     */
    private function normalizeMealType(string $mealType): string
    {
        // Normalisation de base : minuscules et suppression des espaces
        $mealType = mb_strtolower(trim($mealType));

        $map = [
            'petit-déjeuner' => 'petit_dejeuner',
            'déjeuner' => 'dejeuner',
            'dîner' => 'diner',
            'goûter' => 'collation',
            'en-cas' => 'collation',
            'petit-dejeuner' => 'petit_dejeuner',
            'dejeuner' => 'dejeuner',
            'diner' => 'diner',
            'gouter' => 'collation',
            'collation' => 'collation',
        ];

        return $map[$mealType] ?? 'dejeuner'; // Valeur par défaut
    }

    /**
     * Invalide les caches appropriés.
     */
    private function invalidateCaches(): void
    {
        $this->cache->clearNamespace('foods');
        $this->cache->clearNamespace('meals');
    }
}
