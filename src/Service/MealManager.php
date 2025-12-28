<?php

namespace App\Service;

use App\Model\MealModel;
use Exception;

/**
 * MealManager - Service pour gérer la logique métier des repas
 * Responsabilités :
 * - Gestion des repas (CRUD)
 * - Ajout d'aliments aux repas
 * - Calculs liés aux repas
 * - Interactions avec MealModel.
 */
class MealManager
{
    public function __construct(
        private MealModel $mealModel,
        private \App\Service\CacheService $cache
    ) {
    }

    /**
     * Ajouter un aliment à un repas.
     */
    public function addFoodToMeal(int $foodId, int $quantity, string $mealType): array
    {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id']))
        {
            return ['success' => false, 'message' => 'Utilisateur non connecté'];
        }

        $userId = $_SESSION['user']['id'];

        try
        {
            // Créer un nouveau repas ou utiliser un repas existant du jour
            $mealId = $this->getOrCreateTodayMeal($userId, $mealType);

            if (!$mealId)
            {
                return ['success' => false, 'message' => 'Erreur lors de la création du repas'];
            }

            // Ajouter l'aliment au repas
            $result = $this->mealModel->addFoodToMeal($mealId, $foodId, $quantity);

            if ($result)
            {
                // Invalider le cache du dashboard pour les statistiques quotidiennes
                $this->cache->clearNamespace('dashboard');

                return [
                    'success' => true,
                    'message' => 'Aliment ajouté au repas avec succès',
                    'meal_id' => $mealId,
                ];
            } else
            {
                return ['success' => false, 'message' => 'Erreur lors de l\'ajout de l\'aliment'];
            }
        } catch (Exception $e)
        {
            error_log('Erreur ajout aliment au repas: ' . $e->getMessage());

            return ['success' => false, 'message' => 'Erreur interne du serveur'];
        }
    }

    /**
     * Récupérer ou créer un repas pour aujourd'hui selon le type.
     */
    public function getOrCreateMealForToday(int $userId, string $mealType): int
    {
        return $this->getOrCreateTodayMeal($userId, $mealType);
    }

    /**
     * Récupérer ou créer un repas pour aujourd'hui selon le type (privé).
     */
    private function getOrCreateTodayMeal(int $userId, string $mealType): int
    {
        // Normaliser le type de repas pour usage interne (sans accents, minuscules)
        $mealTypeNormalized = strtolower($mealType);
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

        $today = date('Y-m-d');

        // Essayer de trouver un repas existant du même type pour aujourd'hui
        $existingMeal = $this->mealModel->getMealByDateAndType($userId, $today, $mealTypeNormalized);

        if ($existingMeal)
        {
            return $existingMeal['id'];
        }

        // Créer un nouveau repas si aucun n'existe
        $newMealId = $this->mealModel->createMeal($userId, $mealTypeNormalized);

        if (!$newMealId)
        {
            throw new Exception('Erreur lors de la création du repas');
        }

        return $newMealId;
    }

    /**
     * Récupérer les repas d'aujourd'hui groupés par type.
     */
    public function getMealsByDate(string $date): array
    {
        if (!isset($_SESSION['user']['id']))
        {
            return [];
        }

        $userId = $_SESSION['user']['id'];
        $namespace = 'meals';
        $key = 'meals_' . $userId . '_' . $date;

        $cached = $this->cache->get($namespace, $key);
        if ($cached !== null)
        {
            return $cached;
        }

        $meals = $this->mealModel->getMealsByDate($userId, $date);

        // Grouper par type de repas
        $groupedMeals = [
            'petit-dejeuner' => [],
            'dejeuner' => [],
            'gouter' => [],
            'diner' => [],
            'en-cas' => [],
        ];

        foreach ($meals as $meal)
        {
            $type = $meal['type_repas'] ?? 'repas';

            // Enrichir le repas avec ses aliments
            $meal['aliments'] = $this->getAlimentsForRepas($meal['id']);

            // Mapper les types
            switch ($type)
            {
                case 'petit_dejeuner':
                case 'petit-dejeuner':
                case 'breakfast':
                    $groupedMeals['petit-dejeuner'][] = $meal;

                    break;
                case 'dejeuner':
                case 'lunch':
                    $groupedMeals['dejeuner'][] = $meal;

                    break;
                case 'gouter':
                case 'collation':
                case 'snack':
                    $groupedMeals['gouter'][] = $meal;

                    break;
                case 'diner':
                case 'dîner':
                case 'dinner':
                    $groupedMeals['diner'][] = $meal;

                    break;
                case 'en-cas':
                case 'snacks':
                    $groupedMeals['en-cas'][] = $meal;

                    break;
                default:
                    $groupedMeals['en-cas'][] = $meal; // Par défaut en en-cas
            }
        }

        $this->cache->set($namespace, $key, $groupedMeals, \App\Service\CacheService::TTL_SHORT);

        return $groupedMeals;
    }

    /**
     * Récupérer les détails d'un repas.
     */
    public function getMealDetails(int $mealId): ?array
    {
        if (!isset($_SESSION['user']['id']))
        {
            return null;
        }

        return $this->mealModel->getMealDetails($mealId);
    }

    /**
     * Récupérer les aliments d'un repas avec leurs détails.
     */
    public function getAlimentsForRepas(int $repasId): array
    {
        $namespace = 'meals';
        $key = 'aliments_' . $repasId;

        $cached = $this->cache->get($namespace, $key);
        if ($cached !== null)
        {
            return $cached;
        }

        $value = $this->mealModel->getAlimentsForRepas($repasId);
        $this->cache->set($namespace, $key, $value, \App\Service\CacheService::TTL_SHORT);

        return $value;
    }

    /**
     * Supprimer un aliment d'un repas.
     */
    public function removeFoodFromMeal(int $repasId, int $alimentId): bool
    {
        $result = $this->mealModel->removeFoodFromMeal($repasId, $alimentId);

        if ($result)
        {
            // Invalider le cache des repas et du dashboard
            $this->cache->clearNamespace('meals');
            $this->cache->clearNamespace('dashboard');
        }

        return $result;
    }

    /**
     * Supprimer un repas.
     */
    public function deleteMeal(int $repasId): bool
    {
        $result = $this->mealModel->deleteMeal($repasId);

        if ($result)
        {
            // Invalider le cache du dashboard
            $this->cache->clearNamespace('dashboard');
        }

        return $result;
    }

    /**
     * Ajouter un aliment depuis le catalogue à un repas.
     */
    public function addFoodFromCatalog(array $postData): array
    {
        try
        {
            // Validation des données
            if (empty($postData['food_id']))
            {
                return ['error' => 'ID de l\'aliment requis'];
            }

            $foodId = (int)$postData['food_id'];
            $mealType = $postData['meal_type'] ?? 'repas';
            $quantity = (float)($postData['quantite_g'] ?? 100);

            // Normaliser le type de repas pour usage interne (sans accents, minuscules)
            $mealTypeNormalized = strtolower($mealType);
            $mealTypeNormalized = strtr($mealTypeNormalized, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'ç' => 'c']);
            $mealTypeNormalized = trim($mealTypeNormalized);

            // Mapper quelques variantes courantes (usage interne)
            $map = [
                'breakfast' => 'petit_dejeuner',
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
                // Invalider le cache du dashboard
                $this->cache->clearNamespace('dashboard');

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
     * Ajouter un aliment depuis la recherche à un repas.
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

            $mealType = $postData['meal_type'] ?? 'repas';
            $quantity = (float)($postData['quantite_g'] ?? 100);

            // Normaliser le type de repas pour usage interne (sans accents, minuscules)
            $mealTypeNormalized = strtolower($mealType);
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
            $saveResult = $this->saveToDatabase($postData);
            if (!$saveResult['success'])
            {
                return $saveResult; // Retourner l'erreur de sauvegarde
            }

            $foodId = $saveResult['food_id'];

            // Créer ou récupérer le repas du jour
            $mealId = $this->getOrCreateMealForToday($userId, $mealTypeNormalized);

            // Ajouter l'aliment au repas
            $result = $this->mealModel->addFoodToMeal($mealId, $foodId, $quantity);

            if ($result)
            {
                // Invalider le cache du dashboard
                $this->cache->clearNamespace('dashboard');

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
     * Ajouter un aliment depuis la recherche à un repas.
     */
    private function saveToDatabase(array $postData): array
    {
        try
        {
            // Préparer les données pour MealModel
            $foodData = [
                'nom' => $postData['food_name'],
                'marque' => $postData['food_brand'] ?? '',
                'categorie' => '',
                'code_barre' => $postData['barcode'] ?? null,
                'image_url' => $postData['image'] ?? '',
                'calories_100g' => (float)($postData['energy-kcal_100g'] ?? 0),
                'proteines_100g' => (float)($postData['proteins_100g'] ?? 0),
                'glucides_100g' => (float)($postData['carbohydrates_100g'] ?? 0),
                'sucres_100g' => (float)($postData['sugars_100g'] ?? 0),
                'lipides_100g' => (float)($postData['fat_100g'] ?? 0),
                'acides_gras_satures_100g' => (float)($postData['saturated-fat_100g'] ?? 0),
                'fibres_100g' => (float)($postData['fiber_100g'] ?? 0),
                'sodium_100g' => (float)($postData['salt_100g'] ?? 0),
            ];

            $foodId = $this->mealModel->addFoodManually($foodData);

            if ($foodId === false)
            {
                return ['error' => 'Erreur lors de l\'ajout de l\'aliment à la base de données'];
            }

            return [
                'success' => true,
                'food_id' => $foodId,
                'message' => 'Aliment sauvegardé avec succès',
            ];
        } catch (Exception $e)
        {
            return ['error' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()];
        }
    }
}
