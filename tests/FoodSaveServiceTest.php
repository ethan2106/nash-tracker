<?php

use App\Service\FoodSaveService;
use App\Repository\FoodRepositoryInterface;
use App\Repository\MealRepositoryInterface;
use App\Service\CacheService;
use PHPUnit\Framework\TestCase;

class FoodSaveServiceTest extends TestCase
{
    private $foodRepositoryMock;
    private $mealRepositoryMock;
    private $cacheMock;
    private $foodSaveService;

    protected function setUp(): void
    {
        $this->foodRepositoryMock = $this->createMock(FoodRepositoryInterface::class);
        $this->mealRepositoryMock = $this->createMock(MealRepositoryInterface::class);
        $this->cacheMock = $this->createMock(CacheService::class);
        $this->foodSaveService = new FoodSaveService(
            $this->foodRepositoryMock,
            $this->mealRepositoryMock,
            $this->cacheMock
        );
    }

    /**
     * Test sauvegarde d'aliment depuis API - succès
     */
    public function testSaveFoodFromAPISuccess()
    {
        $data = [
            'product_name' => 'Test Food',
            'brands' => 'Test Brand',
            'code' => '123456789',
            'nutriments' => [
                'energy-kcal_100g' => 150,
                'proteins_100g' => 10,
            ],
        ];

        $this->foodRepositoryMock->expects($this->once())
            ->method('saveFoodFromAPI')
            ->willReturn(true);

        $this->cacheMock->expects($this->exactly(2))
            ->method('clearNamespace')
            ->with($this->callback(function ($arg) {
                return $arg === 'foods' || $arg === 'meals';
            }));

        $result = $this->foodSaveService->saveFoodFromAPI($data);

        $this->assertEquals([
            'success' => true,
            'message' => 'Aliment sauvegardé avec succès',
        ], $result);
    }

    /**
     * Test sauvegarde d'aliment depuis API - données invalides
     */
    public function testSaveFoodFromAPIInvalidData()
    {
        $data = [
            // Données vides = invalides
        ];

        $result = $this->foodSaveService->saveFoodFromAPI($data);

        $this->assertEquals([
            'success' => false,
            'error' => 'Données invalides: Nom du produit requis',
        ], $result);
    }

    /**
     * Test ajout d'aliment du catalogue à un repas existant
     */
    public function testAddFoodFromCatalogExistingMeal()
    {
        // Simuler une session utilisateur
        $_SESSION['user']['id'] = 1;

        $data = [
            'food_id' => 42,
            'meal_type' => 'dejeuner',
            'quantity' => 150,
        ];

        $existingMeal = ['id' => 10];

        $this->mealRepositoryMock->expects($this->once())
            ->method('getMealByDateAndType')
            ->with(1, date('Y-m-d'), 'dejeuner')
            ->willReturn($existingMeal);

        $this->mealRepositoryMock->expects($this->once())
            ->method('addFoodToMeal')
            ->with(10, 42, 150.0)
            ->willReturn(true);

        $this->cacheMock->expects($this->exactly(2))
            ->method('clearNamespace')
            ->with($this->callback(function ($arg) {
                return $arg === 'foods' || $arg === 'meals';
            }));

        $result = $this->foodSaveService->addFoodFromCatalog($data);

        $this->assertEquals([
            'success' => true,
            'message' => 'Aliment ajouté au repas avec succès',
            'meal_id' => 10,
        ], $result);

        // Nettoyer la session
        unset($_SESSION['user']);
    }

    /**
     * Test ajout d'aliment du catalogue - création nouveau repas
     */
    public function testAddFoodFromCatalogNewMeal()
    {
        $_SESSION['user']['id'] = 1;

        $data = [
            'aliment_id' => 42, // Test avec clé alternative
            'meal_type' => 'diner',
            'quantity' => 200,
        ];

        $this->mealRepositoryMock->expects($this->once())
            ->method('getMealByDateAndType')
            ->with(1, date('Y-m-d'), 'diner')
            ->willReturn(null);

        $this->mealRepositoryMock->expects($this->once())
            ->method('createMealWithFood')
            ->with(1, 'diner', 42, 200.0)
            ->willReturn(15);

        $this->cacheMock->expects($this->exactly(2))
            ->method('clearNamespace')
            ->with($this->callback(function ($arg) {
                return $arg === 'foods' || $arg === 'meals';
            }));

        $result = $this->foodSaveService->addFoodFromCatalog($data);

        $this->assertEquals([
            'success' => true,
            'message' => 'Aliment ajouté au repas avec succès',
            'meal_id' => 15,
        ], $result);

        unset($_SESSION['user']);
    }

    /**
     * Test ajout d'aliment du catalogue - données invalides
     */
    public function testAddFoodFromCatalogInvalidData()
    {
        $_SESSION['user']['id'] = 1;

        $data = [
            // Pas de food_id
            'quantity' => 0, // Quantité invalide
        ];

        $result = $this->foodSaveService->addFoodFromCatalog($data);

        $this->assertEquals([
            'success' => false,
            'error' => 'Données invalides pour l\'ajout au repas',
        ], $result);

        unset($_SESSION['user']);
    }

    /**
     * Test ajout d'aliment du catalogue - utilisateur non connecté
     */
    public function testAddFoodFromCatalogNotLoggedIn()
    {
        // Pas de session utilisateur

        $data = ['food_id' => 42];

        $result = $this->foodSaveService->addFoodFromCatalog($data);

        $this->assertEquals([
            'success' => false,
            'error' => 'Utilisateur non connecté',
        ], $result);
    }

    /**
     * Test suppression d'aliment - succès
     */
    public function testDeleteFoodSuccess()
    {
        $foodId = 42;

        $this->foodRepositoryMock->expects($this->once())
            ->method('deleteFood')
            ->with($foodId)
            ->willReturn(true);

        $this->cacheMock->expects($this->exactly(2))
            ->method('clearNamespace')
            ->with($this->callback(function ($arg) {
                return $arg === 'foods' || $arg === 'meals';
            }));

        $result = $this->foodSaveService->deleteFood($foodId);

        $this->assertEquals([
            'success' => true,
            'message' => 'Aliment supprimé avec succès',
        ], $result);
    }

    /**
     * Test suppression d'aliment - échec (aliment utilisé)
     */
    public function testDeleteFoodFailure()
    {
        $foodId = 42;

        $this->foodRepositoryMock->expects($this->once())
            ->method('deleteFood')
            ->with($foodId)
            ->willReturn(false);

        $result = $this->foodSaveService->deleteFood($foodId);

        $this->assertEquals([
            'success' => false,
            'message' => 'Cet aliment ne peut pas être supprimé car il est utilisé dans des repas.',
        ], $result);
    }

    /**
     * Test normalisation du type de repas
     */
    public function testNormalizeMealType()
    {
        // Cette méthode est privée, on teste via les méthodes publiques
        $_SESSION['user']['id'] = 1;

        $data = [
            'food_id' => 42,
            'meal_type' => 'petit-dejeuner', // Sera normalisé
        ];

        $this->mealRepositoryMock->expects($this->once())
            ->method('getMealByDateAndType')
            ->with(1, date('Y-m-d'), 'petit_dejeuner') // Normalisé
            ->willReturn(['id' => 10]);

        $this->mealRepositoryMock->expects($this->once())
            ->method('addFoodToMeal')
            ->willReturn(true);

        $this->cacheMock->expects($this->exactly(2))
            ->method('clearNamespace');

        $result = $this->foodSaveService->addFoodFromCatalog($data);

        $this->assertTrue($result['success']);

        unset($_SESSION['user']);
    }

    /**
     * Test normalisation robuste des types de repas (casse, espaces)
     */
    public function testNormalizeMealTypeRobust()
    {
        $reflection = new \ReflectionClass($this->foodSaveService);
        $method = $reflection->getMethod('normalizeMealType');

        // Test avec différentes variations
        $testCases = [
            'DEJEUNER' => 'dejeuner',
            ' Déjeuner ' => 'dejeuner',
            'dîner' => 'diner',
            'DINER' => 'diner',
            ' Goûter  ' => 'collation',
            'invalid_type' => 'dejeuner', // Valeur par défaut
        ];

        foreach ($testCases as $input => $expected) {
            $result = $method->invoke($this->foodSaveService, $input);
            $this->assertEquals($expected, $result, "Failed for input: '$input'");
        }
    }
}