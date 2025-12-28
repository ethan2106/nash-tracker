<?php

namespace App\Service;

use App\Model\MealModel;
use Exception;

/**
 * FoodManager - Service pour gérer la logique métier des aliments
 * Responsabilités :
 * - Ajouter des aliments aux repas
 * - Sauvegarder des aliments depuis OpenFoodFacts
 * - Ajouter des aliments manuellement
 * - Gérer les opérations sur les aliments.
 */
class FoodManager
{
    public function __construct(
        private MealModel $mealModel,
        private OpenFoodFactsService $openFoodFactsService,
        private UploadService $uploadService,
        private \App\Service\CacheService $cache
    ) {
    }

    /**
     * Ajouter un aliment du catalogue à un repas.
     */
    public function addFoodFromCatalog(array $postData): array
    {
        try
        {
            // Validation des données
            $foodId = $postData['food_id'] ?? $postData['aliment_id'] ?? null;
            if (!$foodId)
            {
                return ['error' => 'ID de l\'aliment requis'];
            }

            $foodId = (int)$foodId;

            // Supporter plusieurs clés envoyées (AJAX ou form classique)
            $rawMealType = $postData['meal_type'] ?? $postData['repas_type'] ?? 'repas';
            $rawQuantity = $postData['quantity'] ?? $postData['quantite_g'] ?? 100;

            // Normaliser la quantité
            $quantity = (float)$rawQuantity;
            if ($quantity <= 0)
            {
                $quantity = 100.0;
            }

            // Normaliser le type de repas pour usage interne (sans accents, minuscules)
            $mealTypeNormalized = strtolower($rawMealType);
            $mealTypeNormalized = strtr($mealTypeNormalized, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'ç' => 'c']);
            $mealTypeNormalized = trim($mealTypeNormalized);

            // Mapper quelques variantes courantes (usage interne)
            $map = [
                'breakfast' => 'petit-dejeuner',
                'petit-dejeuner' => 'petit_dejeuner',
                'dejeuner' => 'dejeuner',
                'lunch' => 'dejeuner',
                'gouter' => 'collation',
                'snack' => 'collation',
                'diner' => 'diner',
                'dinner' => 'diner',
                'en-cas' => 'collation',
                'snacks' => 'collation',
                'collation' => 'collation',
            ];
            if (isset($map[$mealTypeNormalized]))
            {
                $mealTypeNormalized = $map[$mealTypeNormalized];
            }

            // Vérifier que l'utilisateur est connecté
            if (!isset($_SESSION['user']['id']))
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $userId = $_SESSION['user']['id'];

            // Vérifier si un repas existe déjà pour cette date et ce type
            $dateOnly = date('Y-m-d');
            $existingMeal = $this->mealModel->getMealByDateAndType($userId, $dateOnly, $mealTypeNormalized);

            if ($existingMeal)
            {
                // Repas existe, ajouter simplement l'aliment
                $mealId = $existingMeal['id'];
                $result = $this->mealModel->addFoodToMeal($mealId, $foodId, $quantity);
            } else
            {
                // Repas n'existe pas, créer avec l'aliment en transaction atomique
                $mealId = $this->mealModel->createMealWithFood($userId, $mealTypeNormalized, $foodId, $quantity);
                $result = ($mealId !== false);
            }

            if ($result)
            {
                // Invalider le cache des repas pour cette date
                $this->cache->clearNamespace('meals');

                return [
                    'success' => true,
                    'message' => 'Aliment ajouté au repas avec succès',
                    'meal_id' => $mealId,
                ];
            } else
            {
                return ['error' => 'Erreur lors de l\'ajout de l\'aliment au repas'];
            }
        } catch (Exception $e)
        {
            return ['error' => 'Erreur lors de l\'ajout de l\'aliment: ' . $e->getMessage()];
        }
    }

    /**
     * Ajouter un aliment depuis la recherche OpenFoodFacts à un repas.
     */
    public function addFoodFromSearch(array $postData): array
    {
        try
        {
            // Validation des données
            if (empty($postData['food_name']))
            {
                return ['error' => 'Nom de l\'aliment requis'];
            }

            $rawMealType = $postData['meal_type'] ?? 'repas';
            $quantity = (float)($postData['quantity'] ?? 100);

            // Normaliser le type de repas pour usage interne (sans accents, minuscules)
            $mealTypeNormalized = strtolower($rawMealType);
            $mealTypeNormalized = strtr($mealTypeNormalized, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'ç' => 'c']);
            $mealTypeNormalized = trim($mealTypeNormalized);

            // Mapper quelques variantes courantes (usage interne)
            $map = [
                'breakfast' => 'petit-dejeuner',
                'petit-dejeuner' => 'petit_dejeuner',
                'dejeuner' => 'dejeuner',
                'lunch' => 'dejeuner',
                'gouter' => 'gouter',
                'snack' => 'gouter',
                'diner' => 'diner',
                'dinner' => 'diner',
                'en-cas' => 'en-cas',
                'snacks' => 'en-cas',
            ];
            if (isset($map[$mealTypeNormalized]))
            {
                $mealTypeNormalized = $map[$mealTypeNormalized];
            }

            // Vérifier que l'utilisateur est connecté
            if (!isset($_SESSION['user']['id']))
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $userId = $_SESSION['user']['id'];

            // D'abord, sauvegarder l'aliment en base de données
            $saveResult = $this->saveFoodFromAPI($postData);
            if (!$saveResult['success'])
            {
                return $saveResult;
            }

            $foodId = $saveResult['food_id'];

            // Vérifier si un repas existe déjà pour cette date et ce type
            $dateOnly = date('Y-m-d');
            $existingMeal = $this->mealModel->getMealByDateAndType($userId, $dateOnly, $mealTypeNormalized);

            if ($existingMeal)
            {
                // Repas existe, ajouter simplement l'aliment
                $mealId = $existingMeal['id'];
                $result = $this->mealModel->addFoodToMeal($mealId, $foodId, $quantity);
            } else
            {
                // Repas n'existe pas, créer avec l'aliment en transaction atomique
                $mealId = $this->mealModel->createMealWithFood($userId, $mealTypeNormalized, $foodId, $quantity);
                $result = ($mealId !== false);
            }

            if ($result)
            {
                return [
                    'success' => true,
                    'message' => 'Aliment ajouté au repas avec succès',
                    'meal_id' => $mealId,
                ];
            } else
            {
                return ['error' => 'Erreur lors de l\'ajout de l\'aliment au repas'];
            }
        } catch (Exception $e)
        {
            return ['error' => 'Erreur lors de l\'ajout de l\'aliment: ' . $e->getMessage()];
        }
    }

    /**
     * Ajouter un aliment manuellement à la base de données.
     */
    public function addFoodManually(array $postData, array $filesData = []): array
    {
        try
        {
            // Validation des données requises
            if (empty($postData['manual_name']))
            {
                return ['error' => 'Le nom de l\'aliment est requis'];
            }

            if (empty($postData['manual_calories']))
            {
                return ['error' => 'Les calories sont requises'];
            }

            // Gestion de l'upload d'image
            $imageUrl = '';
            if (isset($filesData['manual_image']) && $filesData['manual_image']['error'] === UPLOAD_ERR_OK)
            {
                $uploadResult = $this->uploadService->handleImageUpload($filesData['manual_image']);
                if (isset($uploadResult['error']))
                {
                    return ['error' => $uploadResult['error']];
                }
                $imageUrl = $uploadResult['url'];
            }

            // Préparer les données de l'aliment
            $foodData = [
                'nom' => $postData['manual_name'],
                'marque' => $postData['manual_brand'] ?? '',
                'categorie' => '',
                'code_barre' => null,
                'image_url' => $imageUrl,
                'calories_100g' => (float)$postData['manual_calories'],
                'proteines_100g' => (float)($postData['manual_proteins'] ?? 0),
                'glucides_100g' => (float)($postData['manual_carbs'] ?? 0),
                'sucres_100g' => 0,
                'lipides_100g' => (float)($postData['manual_fat'] ?? 0),
                'acides_gras_satures_100g' => 0,
                'fibres_100g' => (float)($postData['manual_fiber'] ?? 0),
                'sodium_100g' => 0,
            ];

            $foodId = $this->mealModel->addFoodManually($foodData);

            if ($foodId === false)
            {
                return ['error' => 'Erreur lors de l\'ajout de l\'aliment à la base de données'];
            }

            return [
                'success' => true,
                'food_id' => $foodId,
                'message' => 'Aliment ajouté avec succès à la base de données',
            ];
        } catch (Exception $e)
        {
            return ['error' => 'Erreur lors de l\'ajout de l\'aliment: ' . $e->getMessage()];
        }
    }

    /**
     * Sauvegarder un aliment depuis OpenFoodFacts en base de données.
     */
    public function saveFoodFromAPI(array $postData): array
    {
        try
        {
            // Vérifier si les données viennent du formulaire JavaScript (food_data) ou des champs séparés
            if (isset($postData['food_data']))
            {
                // Données envoyées par JavaScript (objet complet du produit)
                $productData = json_decode($postData['food_data'], true);

                $foodData = [
                    'product_name' => $productData['name'] ?? $productData['product_name'] ?? '',
                    'brands' => $productData['brands'] ?? '',
                    'image_url' => $productData['image'] ?? '',
                    'code' => $productData['barcode'] ?? $productData['code'] ?? '',
                    'nutriments' => [
                        'energy-kcal_100g' => $productData['nutriments']['energy-kcal_100g'] ?? $productData['nutriments']['energy-kcal'] ?? 0,
                        'proteins_100g' => $productData['nutriments']['proteins_100g'] ?? $productData['nutriments']['proteins'] ?? 0,
                        'carbohydrates_100g' => $productData['nutriments']['carbohydrates_100g'] ?? $productData['nutriments']['carbohydrates'] ?? 0,
                        'sugars_100g' => $productData['nutriments']['sugars_100g'] ?? $productData['nutriments']['sugars'] ?? 0,
                        'fat_100g' => $productData['nutriments']['fat_100g'] ?? $productData['nutriments']['fat'] ?? 0,
                        'saturated-fat_100g' => $productData['nutriments']['saturated-fat_100g'] ?? $productData['nutriments']['saturated-fat'] ?? 0,
                        'fiber_100g' => $productData['nutriments']['fiber_100g'] ?? $productData['nutriments']['fiber'] ?? 0,
                        'sodium_100g' => $productData['nutriments']['sodium_100g'] ?? $productData['nutriments']['salt_100g'] ?? 0,
                    ],
                ];
            } else
            {
                // Données envoyées par les champs séparés (ancienne méthode)
                $nutriments = json_decode($postData['food_nutriments'] ?? '{}', true);

                $foodData = [
                    'product_name' => $postData['food_name'] ?? '',
                    'brands' => $postData['food_brands'] ?? '',
                    'image_url' => $postData['food_image'] ?? '',
                    'code' => $postData['food_barcode'] ?? '',
                    'nutriments' => [
                        'energy-kcal_100g' => $nutriments['energy-kcal_100g'] ?? $nutriments['energy-kcal'] ?? 0,
                        'proteins_100g' => $nutriments['proteins_100g'] ?? $nutriments['proteins'] ?? 0,
                        'carbohydrates_100g' => $nutriments['carbohydrates_100g'] ?? $nutriments['carbohydrates'] ?? 0,
                        'sugars_100g' => $nutriments['sugars_100g'] ?? $nutriments['sugars'] ?? 0,
                        'fat_100g' => $nutriments['fat_100g'] ?? $nutriments['fat'] ?? 0,
                        'saturated-fat_100g' => $nutriments['saturated-fat_100g'] ?? $nutriments['saturated-fat'] ?? 0,
                        'fiber_100g' => $nutriments['fiber_100g'] ?? $nutriments['fiber'] ?? 0,
                        'sodium_100g' => $nutriments['sodium_100g'] ?? $nutriments['salt_100g'] ?? 0,
                    ],
                ];
            }

            $foodId = $this->mealModel->addFoodFromOpenFoodFacts($foodData);

            if ($foodId === false)
            {
                return ['success' => false, 'error' => 'Erreur lors de la sauvegarde de l\'aliment'];
            }

            return [
                'success' => true,
                'food_id' => $foodId,
                'message' => 'Aliment sauvegardé avec succès',
            ];
        } catch (Exception $e)
        {
            error_log("Erreur lors de la sauvegarde d'aliment: " . $e->getMessage());

            return ['success' => false, 'error' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()];
        }
    }

    /**
     * Ajouter un aliment depuis OpenFoodFacts à la base de données.
     */
    public function addFoodFromAPI(string $barcode): array
    {
        try
        {
            // Récupérer les données du produit depuis l'API
            $productData = $this->openFoodFactsService->getProduct($barcode);

            if (isset($productData['error']))
            {
                return $productData;
            }

            // Préparer les données pour MealModel
            $foodData = [
                'product_name' => $productData['name'],
                'brands' => $productData['brands'],
                'categories' => $productData['categories'],
                'code' => $barcode,
                'image_url' => $productData['image'],
                'nutriments' => [
                    'energy-kcal_100g' => $productData['nutriments']['energy-kcal_100g'] ?? 0,
                    'proteins_100g' => $productData['nutriments']['proteins_100g'] ?? 0,
                    'carbohydrates_100g' => $productData['nutriments']['carbohydrates_100g'] ?? 0,
                    'fat_100g' => $productData['nutriments']['fat_100g'] ?? 0,
                    'fiber_100g' => $productData['nutriments']['fiber_100g'] ?? 0,
                    'sodium_100g' => $productData['nutriments']['salt_100g'] ?? 0,
                ],
            ];

            // Ajouter à la base de données
            $foodId = $this->mealModel->addFoodFromOpenFoodFacts($foodData);

            if ($foodId === false)
            {
                return ['error' => 'Erreur lors de l\'ajout de l\'aliment à la base de données'];
            }

            return [
                'success' => true,
                'food_id' => $foodId,
                'message' => 'Aliment ajouté avec succès à la base de données',
            ];
        } catch (Exception $e)
        {
            return ['error' => 'Erreur lors de l\'ajout de l\'aliment: ' . $e->getMessage()];
        }
    }

    /**
     * Supprimer un aliment.
     */
    public function deleteFood(int $foodId): array
    {
        try
        {
            $result = $this->mealModel->deleteFood($foodId);

            if (is_array($result))
            {
                return $result;
            }

            return [
                'success' => $result,
                'message' => $result ? 'Aliment supprimé' : 'Erreur suppression',
            ];
        } catch (Exception $e)
        {
            return ['error' => 'Erreur lors de la suppression: ' . $e->getMessage()];
        }
    }

    /**
     * Créer ou récupérer un repas existant.
     */
    private function getOrCreateMeal(int $userId, string $mealType, ?string $date = null): int
    {
        $dateOnly = $date ?? date('Y-m-d');
        $dateTime = $date ?? date('Y-m-d H:i:s');

        $existingMeal = $this->mealModel->getMealByDateAndType($userId, $dateOnly, $mealType);

        if ($existingMeal)
        {
            return $existingMeal['id'];
        }

        $mealId = $this->mealModel->createMeal($userId, $mealType, $dateTime);

        if (!$mealId)
        {
            throw new Exception('Erreur lors de la création du repas');
        }

        return $mealId;
    }

    /**
     * Rechercher des aliments dans la base de données.
     */
    public function searchFoodsInDatabase(string $query): array
    {
        try
        {
            $foods = $this->mealModel->searchFoods($query);

            return [
                'success' => true,
                'foods' => $foods,
            ];
        } catch (Exception $e)
        {
            return ['error' => 'Erreur lors de la recherche d\'aliments: ' . $e->getMessage()];
        }
    }

    /**
     * Récupérer tous les aliments sauvegardés avec pagination.
     */
    public function getSavedFoods(?int $limit = null, int $offset = 0): array
    {
        try
        {
            return $this->mealModel->getSavedFoods($limit, $offset);
        } catch (Exception $e)
        {
            error_log('Erreur lors de la récupération des aliments sauvegardés: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Compter le nombre total d'aliments sauvegardés.
     */
    public function countSavedFoods(): int
    {
        try
        {
            return $this->mealModel->countSavedFoods();
        } catch (Exception $e)
        {
            error_log('Erreur lors du comptage des aliments sauvegardés: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Rechercher dans les aliments sauvegardés.
     */
    public function searchSavedFoods(string $query): array
    {
        try
        {
            return $this->mealModel->searchSavedFoods($query);
        } catch (Exception $e)
        {
            error_log("Erreur lors de la recherche d'aliments sauvegardés: " . $e->getMessage());

            return [];
        }
    }
}
