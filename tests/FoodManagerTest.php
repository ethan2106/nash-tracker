<?php

use App\Model\MealModel;
use App\Service\CacheService;
use App\Service\FoodManager;
use App\Service\OpenFoodFactsService;
use App\Service\UploadService;
use PHPUnit\Framework\TestCase;

class FoodManagerTest extends TestCase
{
    private $mealModelMock;

    private $openFoodFactsMock;

    private $uploadServiceMock;

    private $cacheServiceMock;

    private $foodManager;

    protected function setUp(): void
    {
        // Ensure session array exists and is clean for each test
        if (session_status() === PHP_SESSION_NONE)
        {
            @session_start();
            session_write_close();
        }
        $_SESSION = [];

        // Créer les mocks
        $this->mealModelMock = $this->createMock(MealModel::class);
        $this->openFoodFactsMock = $this->createMock(OpenFoodFactsService::class);
        $this->uploadServiceMock = $this->createMock(UploadService::class);
        $this->cacheServiceMock = $this->createMock(CacheService::class);

        // Configuration par défaut pour les mocks
        $this->mealModelMock->method('addFoodToMeal')->willReturn(true);

        // Injecter les mocks dans FoodManager
        $this->foodManager = new FoodManager(
            $this->mealModelMock,
            $this->openFoodFactsMock,
            $this->uploadServiceMock,
            $this->cacheServiceMock
        );
    }

    protected function tearDown(): void
    {
        // Clean up session after each test
        $_SESSION = [];
    }

    public function testAddFoodManuallyValidationError()
    {
        // Vérifier qu'aucune méthode DB n'est appelée quand la validation échoue
        $this->mealModelMock
            ->expects($this->never())
            ->method('addFoodManually');

        $this->uploadServiceMock
            ->expects($this->never())
            ->method('handleImageUpload');

        // Test avec données manquantes - nom requis
        $result = $this->foodManager->addFoodManually([], []);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Le nom de l\'aliment est requis', $result['error']);

        // Test avec nom vide
        $result = $this->foodManager->addFoodManually(['manual_name' => ''], []);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Le nom de l\'aliment est requis', $result['error']);

        // Test avec nom mais pas de calories
        $result = $this->foodManager->addFoodManually(['manual_name' => 'Test'], []);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Les calories sont requises', $result['error']);
    }

    public function testAddFoodManuallyCaloriesRequired()
    {
        $this->mealModelMock
            ->expects($this->never())
            ->method('addFoodManually');

        // Test avec nom mais calories vides
        $postData = [
            'manual_name' => 'Thon en boîte',
            'manual_calories' => '',
        ];
        $result = $this->foodManager->addFoodManually($postData, []);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Les calories sont requises', $result['error']);
    }

    public function testAddFoodManuallyWithImageUpload()
    {
        // Configurer les mocks
        $this->mealModelMock
            ->method('addFoodManually')
            ->willReturn(123);

        $this->uploadServiceMock
            ->expects($this->once())
            ->method('handleImageUpload')
            ->with($this->isArray(), $this->equalTo('foods'))
            ->willReturn(['url' => '/images/foods/test.jpg']);

        $postData = [
            'manual_name' => 'Thon en boîte',
            'manual_calories' => '150',
        ];

        $filesData = [
            'manual_image' => [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/test.jpg',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024,
            ],
        ];

        $result = $this->foodManager->addFoodManually($postData, $filesData);

        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['food_id']);
    }

    public function testAddFoodManuallyDatabaseError()
    {
        // Simuler une erreur de base de données (retour false)
        $this->mealModelMock
            ->expects($this->once())
            ->method('addFoodManually')
            ->willReturn(false);

        $postData = [
            'manual_name' => 'Thon en boîte',
            'manual_calories' => '150',
        ];

        $result = $this->foodManager->addFoodManually($postData, []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayNotHasKey('success', $result);
        $this->assertArrayNotHasKey('food_id', $result);
        $this->assertEquals('Erreur lors de l\'ajout de l\'aliment à la base de données', $result['error']);
    }

    public function testAddFoodManuallyExceptionHandling()
    {
        // Simuler une exception lancée par le modèle
        $this->mealModelMock
            ->expects($this->once())
            ->method('addFoodManually')
            ->willThrowException(new \Exception('Database connection failed'));

        $postData = [
            'manual_name' => 'Thon en boîte',
            'manual_calories' => '150',
        ];

        $result = $this->foodManager->addFoodManually($postData, []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayNotHasKey('success', $result);
        $this->assertArrayNotHasKey('food_id', $result);
        $this->assertStringContainsString('Erreur lors de l\'ajout de l\'aliment', $result['error']);
        $this->assertStringContainsString('Database connection failed', $result['error']);
    }

    public function testAddFoodFromCatalogSuccess()
    {
        // Simuler une session utilisateur
        $_SESSION['user']['id'] = 1;

        // Vérifier que getMealByDateAndType est appelée en premier pour trouver le repas existant
        $this->mealModelMock
            ->expects($this->once())
            ->method('getMealByDateAndType')
            ->with(1, date('Y-m-d'), 'dejeuner')
            ->willReturn(['id' => 10]);

        // Vérifier que addFoodToMeal est appelée avec les bons paramètres
        $this->mealModelMock
            ->expects($this->once())
            ->method('addFoodToMeal')
            ->with(10, 5, 200.0)
            ->willReturn(true);

        // Vérifier que createMealWithFood n'est PAS appelée (repas existe déjà)
        $this->mealModelMock
            ->expects($this->never())
            ->method('createMealWithFood');

        $postData = [
            'food_id' => '5',
            'meal_type' => 'déjeuner',
            'quantity' => '200',
        ];

        $result = $this->foodManager->addFoodFromCatalog($postData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('meal_id', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Aliment ajouté au repas avec succès', $result['message']);
        $this->assertEquals(10, $result['meal_id']);
    }

    public function testAddFoodFromCatalogValidationError()
    {
        // Simuler une session utilisateur pour passer la première validation
        $_SESSION['user']['id'] = 1;

        // Vérifier qu'aucune méthode DB n'est appelée quand la validation échoue
        $this->mealModelMock
            ->expects($this->never())
            ->method('getMealByDateAndType');

        $this->mealModelMock
            ->expects($this->never())
            ->method('addFoodToMeal');

        $this->mealModelMock
            ->expects($this->never())
            ->method('createMealWithFood');

        // Test avec données manquantes - food_id requis
        $result = $this->foodManager->addFoodFromCatalog([]);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('ID de l\'aliment requis', $result['error']);

        // Test avec food_id vide
        $result = $this->foodManager->addFoodFromCatalog(['food_id' => '']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('ID de l\'aliment requis', $result['error']);

        // Test avec food_id mais pas de meal_type - mais en fait, il y a une valeur par défaut, donc pas d'erreur
        // Ce test est invalide, supprimé car la validation passe avec valeur par défaut

        // Test avec food_id et meal_type mais pas de quantity - mais quantity a valeur par défaut 100
        // Donc pas d'erreur non plus
    }

    public function testAddFoodFromCatalogCreatesNewMeal()
    {
        // Simuler une session utilisateur
        $_SESSION['user']['id'] = 1;

        // Simuler qu'aucun repas n'existe (null) donc un nouveau doit être créé
        $this->mealModelMock
            ->expects($this->once())
            ->method('getMealByDateAndType')
            ->with(1, date('Y-m-d'), 'dejeuner')
            ->willReturn(null);

        // Le test doit maintenant s'attendre à createMealWithFood au lieu de createMeal + addFoodToMeal
        $this->mealModelMock
            ->expects($this->once())
            ->method('createMealWithFood')
            ->with(
                1,
                'dejeuner',
                5,
                200.0,
                $this->callback(function ($datetime)
                {
                    // Vérifier que c'est un datetime complet (Y-m-d H:i:s) ou null
                    return $datetime === null || preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $datetime) === 1;
                })
            )
            ->willReturn(15);

        $postData = [
            'food_id' => '5',
            'meal_type' => 'déjeuner',
            'quantity' => '200',
        ];

        $result = $this->foodManager->addFoodFromCatalog($postData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals(15, $result['meal_id']);
    }

    public function testAddFoodFromSearchSuccess()
    {
        // Simuler une session utilisateur
        $_SESSION['user']['id'] = 1;

        // Vérifier que addFoodFromOpenFoodFacts est appelée en premier pour sauvegarder l'aliment
        $this->mealModelMock
            ->expects($this->once())
            ->method('addFoodFromOpenFoodFacts')
            ->willReturn(99);

        // Vérifier que getMealByDateAndType est appelée pour trouver le repas
        $this->mealModelMock
            ->expects($this->once())
            ->method('getMealByDateAndType')
            ->with(1, date('Y-m-d'), 'petit_dejeuner')
            ->willReturn(['id' => 15]);

        // Vérifier que addFoodToMeal est appelée avec l'aliment sauvegardé
        $this->mealModelMock
            ->expects($this->once())
            ->method('addFoodToMeal')
            ->with(15, 99, 150.0)
            ->willReturn(true);

        $postData = [
            'food_name' => 'Yaourt nature',
            'food_nutriments' => json_encode(['energy-kcal_100g' => 50]),
            'meal_type' => 'petit-déjeuner',
            'quantity' => '150',
        ];

        $result = $this->foodManager->addFoodFromSearch($postData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Aliment ajouté au repas avec succès', $result['message']);
        $this->assertEquals(15, $result['meal_id']);
    }

    public function testSearchFoodsInDatabase()
    {
        $expectedFoods = [
            ['id' => 1, 'nom' => 'Thon'],
            ['id' => 2, 'nom' => 'Saumon'],
        ];

        // Vérifier que searchFoods est appelée avec la requête et la limite par défaut
        $this->mealModelMock
            ->expects($this->once())
            ->method('searchFoods')
            ->with('thon', 20)
            ->willReturn($expectedFoods);

        $result = $this->foodManager->searchFoodsInDatabase('thon');

        // Vérifier la structure complète de la réponse
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('foods', $result);
        $this->assertArrayNotHasKey('error', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals($expectedFoods, $result['foods']);
        $this->assertCount(2, $result['foods']);
    }

    public function testAddFoodFromSearchAddsToExistingMeal()
    {
        // Simuler une session utilisateur
        $_SESSION['user']['id'] = 1;

        // Vérifier que addFoodFromOpenFoodFacts est appelée en premier pour sauvegarder l'aliment
        $this->mealModelMock
            ->expects($this->once())
            ->method('addFoodFromOpenFoodFacts')
            ->willReturn(99);

        // Simuler qu'un repas existe déjà pour cette date et ce type
        $this->mealModelMock
            ->expects($this->once())
            ->method('getMealByDateAndType')
            ->with(1, date('Y-m-d'), 'petit_dejeuner')
            ->willReturn(['id' => 15]);

        // Vérifier que addFoodToMeal est appelée avec le repas existant et l'aliment sauvegardé
        $this->mealModelMock
            ->expects($this->once())
            ->method('addFoodToMeal')
            ->with(15, 99, 150.0)
            ->willReturn(true);

        // Vérifier que createMealWithFood n'est jamais appelée
        $this->mealModelMock
            ->expects($this->never())
            ->method('createMealWithFood');

        $postData = [
            'food_name' => 'Yaourt nature',
            'food_nutriments' => json_encode(['energy-kcal_100g' => 50]),
            'meal_type' => 'petit-déjeuner',
            'quantity' => '150',
        ];

        $result = $this->foodManager->addFoodFromSearch($postData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Aliment ajouté au repas avec succès', $result['message']);
        $this->assertEquals(15, $result['meal_id']);
    }
}
