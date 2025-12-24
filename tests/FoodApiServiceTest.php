<?php

use App\Service\FoodApiService;
use App\Service\FoodQualityService;
use PHPUnit\Framework\TestCase;

class FoodApiServiceTest extends TestCase
{
    private $foodQualityServiceMock;
    private $foodApiService;

    protected function setUp(): void
    {
        $this->foodQualityServiceMock = $this->createMock(FoodQualityService::class);
        $this->foodApiService = new FoodApiService($this->foodQualityServiceMock);
    }

    /**
     * Test formatage des résultats de recherche réussis
     */
    public function testFormatSearchResultsSuccess()
    {
        $results = [
            ['id' => 1, 'nom' => 'Test Food'],
            ['id' => 2, 'nom' => 'Another Food'],
        ];

        $formatted = $this->foodApiService->formatSearchResults($results);

        $this->assertEquals([
            'success' => true,
            'results' => $results,
        ], $formatted);
    }

    /**
     * Test formatage des résultats de recherche avec erreur
     */
    public function testFormatSearchResultsWithError()
    {
        $error = 'API indisponible';

        $formatted = $this->foodApiService->formatSearchResults([], $error);

        $this->assertEquals([
            'success' => false,
            'error' => $error,
        ], $formatted);
    }

    /**
     * Test formatage des données de catalogue
     */
    public function testFormatCatalogData()
    {
        $catalogData = [
            'foods' => [
                [
                    'id' => 1,
                    'nom' => 'Test Food',
                    'proteines_100g' => 10,
                    'acides_gras_satures_100g' => 5,
                    'fibres_100g' => 3,
                    'sucres_100g' => 2,
                    'calories_100g' => 150,
                ],
            ],
            'totalFoods' => 25,
            'totalPages' => 3,
            'currentPage' => 1,
            'perPage' => 10,
        ];

        $this->foodQualityServiceMock->expects($this->once())
            ->method('calculateGradeFromNutriments')
            ->with([
                'proteins_100g' => 10,
                'saturated-fat_100g' => 5,
                'fiber_100g' => 3,
                'sugars_100g' => 2,
                'energy-kcal_100g' => 150,
            ])
            ->willReturn([
                'grade' => 'B',
                'label' => 'Très bien',
                'color' => 'blue',
                'bg_color' => 'bg-blue-100',
                'text_color' => 'text-blue-800',
                'percentage' => 65,
                'description' => 'Aliment bien équilibré',
            ]);

        $formatted = $this->foodApiService->formatCatalogData($catalogData);

        $this->assertEquals([
            'success' => true,
            'foods' => [
                [
                    'id' => 1,
                    'nom' => 'Test Food',
                    'proteines_100g' => 10,
                    'acides_gras_satures_100g' => 5,
                    'fibres_100g' => 3,
                    'sucres_100g' => 2,
                    'calories_100g' => 150,
                    'quality_score' => [
                        'grade' => 'B',
                        'label' => 'Très bien',
                        'color' => 'blue',
                        'bg_color' => 'bg-blue-100',
                        'text_color' => 'text-blue-800',
                        'percentage' => 65,
                        'description' => 'Aliment bien équilibré',
                    ],
                ],
            ],
            'pagination' => [
                'total' => 25,
                'pages' => 3,
                'current' => 1,
                'perPage' => 10,
            ],
        ], $formatted);
    }

    /**
     * Test formatage du résultat de sauvegarde réussi
     */
    public function testFormatSaveResultSuccess()
    {
        $result = [
            'success' => true,
            'message' => 'Aliment sauvegardé',
            'food_id' => 42,
        ];

        $formatted = $this->foodApiService->formatSaveResult($result);

        $this->assertEquals([
            'success' => true,
            'message' => 'Aliment sauvegardé',
            'food_id' => 42,
            'error' => null,
        ], $formatted);
    }

    /**
     * Test formatage du résultat de sauvegarde avec erreur
     */
    public function testFormatSaveResultError()
    {
        $result = [
            'success' => false,
            'error' => 'Erreur de validation',
        ];

        $formatted = $this->foodApiService->formatSaveResult($result);

        $this->assertEquals([
            'success' => false,
            'message' => '',
            'food_id' => null,
            'error' => 'Erreur de validation',
        ], $formatted);
    }

    /**
     * Test formatage du résultat d'ajout au repas
     */
    public function testFormatAddToMealResult()
    {
        $result = [
            'success' => true,
            'message' => 'Ajouté au repas',
            'meal_id' => 15,
        ];

        $formatted = $this->foodApiService->formatAddToMealResult($result);

        $this->assertEquals([
            'success' => true,
            'message' => 'Ajouté au repas',
            'meal_id' => 15,
            'error' => null,
        ], $formatted);
    }

    /**
     * Test formatage du résultat de suppression
     */
    public function testFormatDeleteResult()
    {
        $result = [
            'success' => true,
            'message' => 'Supprimé avec succès',
        ];

        $formatted = $this->foodApiService->formatDeleteResult($result);

        $this->assertEquals([
            'success' => true,
            'message' => 'Supprimé avec succès',
            'error' => null,
        ], $formatted);
    }
}