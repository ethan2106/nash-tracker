<?php

namespace App\Controller;

use App\Service\CacheService;
use App\Service\FoodDataService;
use App\Service\FoodApiService;
use App\Service\FoodSaveService;
use App\Service\OpenFoodFactsService;
use App\Helper\ResponseHelper;

/**
 * FoodController - Gère l'ajout d'aliments au catalogue.
 * Responsabilités :
 * - Recherche d'aliments via OpenFoodFacts API
 * - Sauvegarde d'aliments dans le catalogue
 * - Ajout manuel d'aliments personnalisés
 * - Interface d'ajout d'aliments au catalogue.
 */
class FoodController extends BaseApiController
{
    private OpenFoodFactsService $apiService;
    private FoodDataService $foodDataService;
    private FoodApiService $foodApiService;
    private FoodSaveService $foodSaveService;

    public function __construct(
        OpenFoodFactsService $apiService,
        FoodDataService $foodDataService,
        FoodApiService $foodApiService,
        FoodSaveService $foodSaveService
    ) {
        $this->apiService = $apiService;
        $this->foodDataService = $foodDataService;
        $this->foodApiService = $foodApiService;
        $this->foodSaveService = $foodSaveService;
    }

    /**
     * Recherche des produits via OpenFoodFacts.
     */
    public function search(string $query): array
    {
        return $this->apiService->search($query);
    }

    /**
     * Récupère les détails d'un produit spécifique.
     */
    public function getProduct(string $barcode): array
    {
        return $this->apiService->getProduct($barcode);
    }

    /**
     * Adapte les données de recherche en données d'aliment
     */
    protected function adaptPostDataForFoodService(array $data): array
    {
        $adaptedData = $data;

        // Cas OpenFoodFacts : food_data JSON string
        if (!empty($adaptedData['food_data']) && is_string($adaptedData['food_data'])) {
            $foodData = json_decode($adaptedData['food_data'], true);

            if (is_array($foodData)) {
                $adaptedData['food_name']       ??= $foodData['name'] ?? null;
                $adaptedData['food_brands']     ??= $foodData['brands'] ?? null;
                $adaptedData['food_barcode']    ??= $foodData['barcode'] ?? $foodData['code'] ?? null;
                $adaptedData['food_image']      ??= $foodData['image'] ?? null;
                $adaptedData['food_nutriments'] ??= json_encode($foodData['nutriments'] ?? []);
            }

            unset($adaptedData['food_data']);
        }

        // Fallbacks nom produit
        if (empty($adaptedData['food_name'])) {
            if (!empty($adaptedData['search_query'])) {
                $adaptedData['food_name'] = trim($adaptedData['search_query']);
            } elseif (!empty($adaptedData['product_name'])) {
                $adaptedData['food_name'] = trim($adaptedData['product_name']);
            } elseif (!empty($adaptedData['name'])) {
                $adaptedData['food_name'] = trim($adaptedData['name']);
            }
        }

        return $adaptedData;
    }

    /**
     * Gère la réponse d'un service selon le contexte (AJAX ou non)
     */
    private function handleServiceResponse(array $result, ?string $redirectUrl = null, ?bool $forceAjax = null, bool $formatForMeal = false): void
    {
        $isAjax = $forceAjax ?? $this->isAjaxRequest();

        if ($isAjax) {
            $payload = $formatForMeal
                ? $this->foodApiService->formatAddToMealResult($result)
                : $result;

            ResponseHelper::jsonResponse($payload);
            return;
        }

        ResponseHelper::handleOperationResult($result, $redirectUrl);
    }

    /**
     * Gérer la page food : POST et affichage.
     */
    public function handleFoodPage()
    {
        // Vérifier l'authentification
        $this->ensureSession();

        $user = $this->getUser();
        if (!$user)
        {
            header('Location: ?page=login');
            exit;
        }

        // Récupérer et valider les paramètres
        $mealType = $_GET['meal_type'] ?? 'repas';
        $mealTypeLabels = [
            'petit-dejeuner' => 'Petit-déjeuner',
            'dejeuner' => 'Déjeuner',
            'gouter' => 'Goûter',
            'diner' => 'Dîner',
            'en-cas' => 'En-cas',
        ];
        $currentMealLabel = $mealTypeLabels[$mealType] ?? 'Repas';

        // Récupérer les données de recherche depuis POST
        $query = $_POST['search_query'] ?? '';
        $searchType = $_POST['search_type'] ?? 'text';

        // Dispatcher pour les actions POST
        $postActions = [
            'add_manual' => 'handleAddManual',
            'add_to_meal_from_catalog' => 'handleAddToMealFromCatalog',
            'add_to_meal_from_search' => 'handleAddToMealFromSearch',
            'save_to_db' => 'handleSaveToDatabase',
        ];

        foreach ($postActions as $actionKey => $handlerMethod) {
            if (isset($_POST[$actionKey]) && $_POST[$actionKey] == '1') {
                $this->$handlerMethod();
                break;
            }
        }

        // Gestion de la recherche
        $searchResults = [];
        $searchError = '';
        $singleProduct = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST')
        {
            if ($searchType === 'barcode' && isset($_POST['barcode']))
            {
                $barcode = trim($_POST['barcode']);
                if (!empty($barcode))
                {
                    $singleProduct = $this->getProduct($barcode);
                    if (isset($singleProduct['error']))
                    {
                        $searchError = $singleProduct['error'];
                        $singleProduct = null;
                    } else
                    {
                        // Sauvegarder automatiquement le produit scanné
                        $saveResult = $this->foodSaveService->saveFoodFromAPI([
                            'food_name' => $singleProduct['product_name'] ?? '',
                            'food_brands' => $singleProduct['brands'] ?? '',
                            'food_image' => $singleProduct['image'] ?? '',
                            'food_barcode' => $barcode,
                            'food_nutriments' => json_encode($singleProduct['nutriments'] ?? []),
                        ]);

                        if (!$saveResult['success'])
                        {
                            $searchError = $saveResult['error'] ?? 'Erreur lors de la sauvegarde du produit';
                            $singleProduct = null;
                        }
                    }
                }
            } elseif ($searchType === 'text' && isset($_POST['search_query']))
            {
                $query = trim($_POST['search_query']);
                if (!empty($query))
                {
                    $results = $this->search($query);
                    if (isset($results['error']))
                    {
                        $searchError = $results['error'];
                    } else
                    {
                        $searchResults = $results;
                    }
                }
            }
        }

        // Préparer les variables pour la vue
        $viewData = [
            'title' => 'Recherche Aliments - Suivi Nash',
            'user' => $user,
            'mealType' => $mealType,
            'currentMealLabel' => $currentMealLabel,
            'mealTypeLabels' => $mealTypeLabels,
            'query' => $query,
            'searchType' => $searchType,
            'searchResults' => $searchResults,
            'searchError' => $searchError,
            'singleProduct' => $singleProduct,
        ];

        // Inclure la vue avec les données préparées
        extract($viewData);
        require_once __DIR__ . '/../View/food.php';
    }

    /**
     * Gérer la page catalog : AJAX, POST et affichage.
     */
    public function handleCatalogPage()
    {
        $this->ensureSession();

        // Helpers pour les composants de qualité alimentaire
        require_once __DIR__ . '/../Helper/food_quality_helpers.php';

        // Ajout depuis le catalogue vers un repas
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['add_to_meal_from_catalog'])
            && $_POST['add_to_meal_from_catalog'] == '1')
        {
            $result = $this->foodSaveService->addFoodFromCatalog($_POST);

            // Vérifier si c'est une requête AJAX
            $isAjax = $this->isAjaxRequest();

            if ($isAjax)
            {
                // Pour les requêtes AJAX, retourner du JSON
                ResponseHelper::jsonResponse($result);

                return;
            } else
            {
                // Pour les requêtes normales, utiliser la gestion standard
                ResponseHelper::handleOperationResult($result, '?page=meals', '?page=catalog');

                return;
            }
        }
        if (isset($_GET['action']) && $_GET['action'] === 'search' && isset($_GET['q']))
        {
            $query = trim($_GET['q']);
            $results = $this->foodDataService->searchSavedFoods($query);
            ResponseHelper::jsonResponse(['success' => true, 'foods' => $results]);

            return;
        }

        // Gestion de récupération de tous les aliments AJAX
        if (isset($_GET['action']) && $_GET['action'] === 'get_all')
        {
            $results = $this->foodDataService->getSavedFoods();
            ResponseHelper::jsonResponse(['success' => true, 'foods' => $results]);

            return;
        }

        // Gestion de l'ajout manuel d'aliment AJAX
        if (isset($_GET['action']) && $_GET['action'] === 'add_manual' && $_SERVER['REQUEST_METHOD'] === 'POST')
        {
            $result = $this->foodSaveService->addFoodManually($_POST, $_FILES);
            ResponseHelper::jsonResponse($result);

            return;
        }

        // Gestion de la suppression d'aliment
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'supprimer-aliment')
        {
            // Vérification CSRF optionnelle pour la suppression
            $csrf = $_POST['csrf_token'] ?? '';
            if (!empty($csrf) && !$this->validateCsrf($csrf))
            {
                ResponseHelper::addErrorMessage('Session invalide (CSRF).');
                ResponseHelper::redirectToCatalog();
            }

            $alimentId = (int)($_POST['aliment_id'] ?? 0);
            if ($alimentId)
            {
                $result = $this->foodSaveService->deleteFood($alimentId);
                ResponseHelper::handleOperationResult($result, '?page=catalog');
            }
            ResponseHelper::redirectToCatalog();
        }

        // Pagination pour le catalogue
        $page = max(1, intval($_GET['p'] ?? 1));
        $perPage = 12; // 12 aliments par page
        $offset = ($page - 1) * $perPage;

        $savedFoods = $this->foodDataService->getSavedFoods($perPage, $offset);
        $totalFoods = $this->foodDataService->countSavedFoods();
        $totalPages = ceil($totalFoods / $perPage);

        // Ajouter les données de qualité et le HTML du badge à chaque aliment
        $foodQualityService = new \App\Service\FoodQualityService();
        foreach ($savedFoods as &$food)
        {
            // Normaliser les données nutritionnelles pour le calcul de qualité
            $nutriments = [
                'proteins_100g' => $food['proteines_100g'] ?? 0,
                'saturated-fat_100g' => $food['acides_gras_satures_100g'] ?? 0,
                'fiber_100g' => $food['fibres_100g'] ?? 0,
                'sugars_100g' => $food['sucres_100g'] ?? 0,
                'energy-kcal_100g' => $food['calories_100g'] ?? 0,
            ];
            $food['quality'] = $foodQualityService->calculateGradeFromNutriments($nutriments);
            // Générer le badge HTML réutilisable
            $food['quality_html'] = renderFoodQualityBadgeFromData($nutriments, 'sm');
        }

        // Passer les variables à la vue
        extract(compact('savedFoods', 'totalFoods', 'totalPages', 'page', 'perPage'));

        require_once __DIR__ . '/../View/catalog.php';
    }

    /**
     * Gère l'ajout manuel d'aliment
     */
    private function handleAddManual(): void
    {
        $result = $this->foodSaveService->addFoodManually($_POST, $_FILES);
        ResponseHelper::handleOperationResult($result, null, '?page=food');
    }

    /**
     * Gère l'ajout d'aliment du catalogue au repas
     */
    private function handleAddToMealFromCatalog(): void
    {
        $result = $this->foodSaveService->addFoodFromCatalog($_POST);
        $this->handleServiceResponse($result, '?page=meals', null, true);
    }

    /**
     * Gère l'ajout d'aliment de la recherche au repas
     */
    private function handleAddToMealFromSearch(): void
    {
        $data = $this->adaptPostDataForFoodService($_POST);
        $result = $this->foodSaveService->addFoodFromSearch($data);
        $redirectUrl = isset($_POST['redirect_to_meals']) && $_POST['redirect_to_meals'] == '1' ? '?page=meals' : '?page=food';
        ResponseHelper::handleOperationResult($result, $redirectUrl);
    }

    /**
     * Gère la sauvegarde d'aliment en base de données
     */
    private function handleSaveToDatabase(): void
    {
        $data = $this->adaptPostDataForFoodService($_POST);

        $result = $this->foodSaveService->saveFoodFromAPI($data);
        ResponseHelper::handleOperationResult($result, '?page=catalog');
    }
}
