<?php

use App\Controller\FoodController;
use App\Service\FoodDataService;
use App\Service\FoodApiService;
use App\Service\FoodSaveService;
use App\Service\OpenFoodFactsService;
use PHPUnit\Framework\TestCase;

class FoodControllerTest extends TestCase
{
    private $foodController;

    private $openFoodFactsMock;

    private $foodDataServiceMock;

    private $foodApiServiceMock;

    private $foodSaveServiceMock;

    protected function setUp(): void
    {
        // Créer les mocks
        $this->openFoodFactsMock = $this->createMock(OpenFoodFactsService::class);
        $this->foodDataServiceMock = $this->createMock(FoodDataService::class);
        $this->foodApiServiceMock = $this->createMock(FoodApiService::class);
        $this->foodSaveServiceMock = $this->createMock(FoodSaveService::class);

        $this->foodController = new FoodController(
            $this->openFoodFactsMock,
            $this->foodDataServiceMock,
            $this->foodApiServiceMock,
            $this->foodSaveServiceMock
        );
    }

    public function testFoodControllerCanBeInstantiated()
    {
        $this->assertInstanceOf(FoodController::class, $this->foodController);
    }

    public function testSearchDelegatesToService()
    {
        $this->openFoodFactsMock->expects($this->once())
            ->method('search')
            ->with('test query')
            ->willReturn(['products' => []]);

        $result = $this->foodController->search('test query');
        $this->assertEquals(['products' => []], $result);
    }

    public function testGetProductDelegatesToService()
    {
        $this->openFoodFactsMock->expects($this->once())
            ->method('getProduct')
            ->with('1234567890123')
            ->willReturn(['product' => null]);

        $result = $this->foodController->getProduct('1234567890123');
        $this->assertEquals(['product' => null], $result);
    }

    public function testHandleFoodPageExists()
    {
        // Vérifier que la méthode handleFoodPage existe
        $this->assertTrue(method_exists($this->foodController, 'handleFoodPage'));
    }

    public function testHandleCatalogPageExists()
    {
        // Vérifier que la méthode handleCatalogPage existe
        $this->assertTrue(method_exists($this->foodController, 'handleCatalogPage'));
    }

    public function testAdaptPostDataForFoodService()
    {
        // Tester l'adaptation des données de recherche
        // Utilisation de la réflexion pour accéder à la méthode protected
        $reflection = new \ReflectionClass($this->foodController);
        $method = $reflection->getMethod('adaptPostDataForFoodService');

        // Cas 1: données avec search_query mais pas food_name
        $input1 = ['search_query' => 'banane', 'search_type' => 'text'];
        $result1 = $method->invoke($this->foodController, $input1);
        $this->assertEquals('banane', $result1['food_name']);

        // Cas 2: données avec déjà food_name
        $input2 = ['food_name' => 'pomme', 'search_query' => 'banane'];
        $result2 = $method->invoke($this->foodController, $input2);
        $this->assertEquals('pomme', $result2['food_name']);

        // Cas 3: données avec product_name mais pas food_name (cas OpenFoodFacts)
        $input3 = ['product_name' => 'orange', 'brands' => 'marque'];
        $result3 = $method->invoke($this->foodController, $input3);
        $this->assertEquals('orange', $result3['food_name']);

        // Cas 4: données avec name mais pas food_name
        $input4 = ['name' => 'fraise', 'category' => 'fruit'];
        $result4 = $method->invoke($this->foodController, $input4);
        $this->assertEquals('fraise', $result4['food_name']);

        // Cas 5: données sans aucun champ de nom
        $input5 = ['search_type' => 'text'];
        $result5 = $method->invoke($this->foodController, $input5);
        $this->assertArrayNotHasKey('food_name', $result5);

        // Cas 6: priorité search_query > product_name > name
        $input6 = ['search_query' => 'banane', 'product_name' => 'orange', 'name' => 'fraise'];
        $result6 = $method->invoke($this->foodController, $input6);
        $this->assertEquals('banane', $result6['food_name']); // search_query prioritaire

        // Cas 7: données avec food_data JSON (cas OpenFoodFacts depuis frontend)
        $foodDataJson = json_encode([
            'name' => 'Salade Poulet',
            'brands' => 'Sodebo',
            'barcode' => '123456789',
            'image' => 'http://example.com/image.jpg',
            'nutriments' => ['proteins' => 10, 'carbs' => 20]
        ]);
        $input7 = ['food_data' => $foodDataJson, 'save_to_db' => '1'];
        $result7 = $method->invoke($this->foodController, $input7);

        $this->assertEquals('Salade Poulet', $result7['food_name']);
        $this->assertEquals('Sodebo', $result7['food_brands']);
        $this->assertEquals('123456789', $result7['food_barcode']);
        $this->assertEquals('http://example.com/image.jpg', $result7['food_image']);
        $this->assertEquals('{"proteins":10,"carbs":20}', $result7['food_nutriments']);
        $this->assertEquals('1', $result7['save_to_db']); // Autres champs préservés
        $this->assertArrayNotHasKey('food_data', $result7); // food_data nettoyé

        // Cas 8: food_data avec code au lieu de barcode
        $foodDataJson8 = json_encode([
            'name' => 'Produit Test',
            'code' => '987654321'
        ]);
        $input8 = ['food_data' => $foodDataJson8];
        $result8 = $method->invoke($this->foodController, $input8);
        $this->assertEquals('987654321', $result8['food_barcode']);

        // Cas 9: food_data JSON invalide - pas de transformation
        $input9 = ['food_data' => 'invalid json', 'fallback' => 'data'];
        $result9 = $method->invoke($this->foodController, $input9);
        $this->assertEquals('data', $result9['fallback']);
        $this->assertArrayNotHasKey('food_name', $result9); // Pas de transformation

        // Cas 10: données existantes ne sont pas écrasées par food_data
        $foodDataJson10 = json_encode([
            'name' => 'Nom depuis food_data',
            'brands' => 'Marque depuis food_data'
        ]);
        $input10 = [
            'food_data' => $foodDataJson10,
            'food_name' => 'Nom existant',
            'other_field' => 'préservé'
        ];
        $result10 = $method->invoke($this->foodController, $input10);
        $this->assertEquals('Nom existant', $result10['food_name']); // Pas écrasé
        $this->assertEquals('Marque depuis food_data', $result10['food_brands']); // Ajouté car vide
        $this->assertEquals('préservé', $result10['other_field']);
    }
}
