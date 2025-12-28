<?php

use PHPUnit\Framework\TestCase;

/**
 * Test d'intégration pour valider que le refactoring fonctionne.
 */
class IntegrationTest extends TestCase
{
    public function testAllServicesCanBeInstantiated()
    {
        $pdoMock = $this->createMock(PDO::class);

        // Test que toutes les classes peuvent être instanciées sans erreurs
        $mealModel = new \App\Model\MealModel($pdoMock);
        $cacheService = new \App\Service\CacheService();
        $offService = new \App\Service\OpenFoodFactsService($cacheService);
        $uploadService = new \App\Service\UploadService();
        $foodQualityService = new \App\Service\FoodQualityService($cacheService, $mealModel);
        $foodRepository = new \App\Repository\FoodRepository($pdoMock);
        $mealRepository = new \App\Repository\MealRepository($pdoMock);

        $foodDataService = new \App\Service\FoodDataService($foodRepository, $cacheService);
        $this->assertInstanceOf(\App\Service\FoodDataService::class, $foodDataService);

        $foodApiService = new \App\Service\FoodApiService($foodQualityService);
        $this->assertInstanceOf(\App\Service\FoodApiService::class, $foodApiService);

        $foodSaveService = new \App\Service\FoodSaveService($foodRepository, $mealRepository, $cacheService);
        $this->assertInstanceOf(\App\Service\FoodSaveService::class, $foodSaveService);

        $foodController = new \App\Controller\FoodController(
            $offService,
            $foodDataService,
            $foodApiService,
            $foodSaveService
        );
        $this->assertInstanceOf(\App\Controller\FoodController::class, $foodController);

        // Test que les services ont les bonnes méthodes
        $this->assertTrue(method_exists($foodDataService, 'getSavedFoods'));
        $this->assertTrue(method_exists($foodDataService, 'searchSavedFoods'));
        $this->assertTrue(method_exists($foodSaveService, 'addFoodFromCatalog'));
        $this->assertTrue(method_exists($foodSaveService, 'addFoodManually'));

        $this->assertTrue(method_exists($foodController, 'search'));
        $this->assertTrue(method_exists($foodController, 'getProduct'));
        $this->assertTrue(method_exists($foodController, 'handleFoodPage'));
    }

    public function testResponseHelperStaticMethods()
    {
        // Démarrer la session pour les tests
        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }

        // Sauvegarder l'état initial de la session
        $originalFlash = $_SESSION['flash'] ?? null;

        try
        {
            // Test des méthodes statiques de ResponseHelper
            \App\Helper\ResponseHelper::addSuccessMessage('Test message');
            $this->assertEquals('success', $_SESSION['flash']['type']);
            $this->assertEquals('Test message', $_SESSION['flash']['msg']);

            \App\Helper\ResponseHelper::addErrorMessage('Test error');
            $this->assertEquals('error', $_SESSION['flash']['type']);
            $this->assertEquals('Test error', $_SESSION['flash']['msg']);
        } finally
        {
            // Nettoyer et restaurer l'état initial
            if ($originalFlash === null)
            {
                unset($_SESSION['flash']);
            } else
            {
                $_SESSION['flash'] = $originalFlash;
            }
        }
    }

    public function testRefactoringMaintainsApiCompatibility()
    {
        $pdoMock = $this->createMock(PDO::class);
        $mealModel = new \App\Model\MealModel($pdoMock);
        $cacheService = new \App\Service\CacheService();
        $offService = new \App\Service\OpenFoodFactsService($cacheService);
        $uploadService = new \App\Service\UploadService();
        $foodRepository = new \App\Repository\FoodRepository($pdoMock);
        $mealRepository = new \App\Repository\MealRepository($pdoMock);
        $foodQualityService = new \App\Service\FoodQualityService($cacheService, $mealModel);

        $foodDataService = new \App\Service\FoodDataService($foodRepository, $cacheService);
        $foodApiService = new \App\Service\FoodApiService($foodQualityService);
        $foodSaveService = new \App\Service\FoodSaveService($foodRepository, $mealRepository, $cacheService);

        $controller = new \App\Controller\FoodController(
            $offService,
            $foodDataService,
            $foodApiService,
            $foodSaveService
        );

        // Ces méthodes doivent exister et retourner des arrays
        $searchResult = $controller->search('test');
        $this->assertIsArray($searchResult);

        $productResult = $controller->getProduct('1234567890123');
        $this->assertIsArray($productResult);

        // Ces méthodes doivent exister
        $this->assertTrue(method_exists($controller, 'handleFoodPage'));
        $this->assertTrue(method_exists($controller, 'handleCatalogPage'));
    }
}
