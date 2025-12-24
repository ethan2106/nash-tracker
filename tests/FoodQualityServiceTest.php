<?php

namespace Tests;

use App\Service\FoodQualityService;
use PHPUnit\Framework\TestCase;

class FoodQualityServiceTest extends TestCase
{
    private FoodQualityService $service;

    protected function setUp(): void
    {
        $this->service = new FoodQualityService();
    }

    public function testCalculateGradeFromNutrimentsWithExcellentFood()
    {
        $nutriments = [
            'proteins_100g' => 25,      // Excellent (>15g)
            'saturated-fat_100g' => 2,  // Excellent (<3g)
            'fiber_100g' => 4,          // Excellent (>3g)
            'sugars_100g' => 3,         // Bon (<5g)
            'energy-kcal_100g' => 150,   // Bon (<400kcal)
        ];

        $result = $this->service->calculateGradeFromNutriments($nutriments);

        $this->assertEquals('A', $result['grade']);
        $this->assertGreaterThanOrEqual(80, $result['percentage']);
        $this->assertEquals('Excellent', $result['label']);
    }

    public function testCalculateGradeFromNutrimentsWithPoorFood()
    {
        $nutriments = [
            'proteins_100g' => 5,       // Mauvais (<7g)
            'saturated-fat_100g' => 15, // Mauvais (>7g)
            'fiber_100g' => 0.5,        // Mauvais (<1.5g)
            'sugars_100g' => 25,        // Mauvais (>5g)
            'energy-kcal_100g' => 450,   // Mauvais (>400kcal)
        ];

        $result = $this->service->calculateGradeFromNutriments($nutriments);

        $this->assertEquals('E', $result['grade']);
        $this->assertLessThan(15, $result['percentage']);
        $this->assertEquals('À éviter', $result['label']);
    }

    public function testCalculateGradeFromNutrimentsWithApiFormat()
    {
        // Test avec le format de l'API OpenFoodFacts
        $nutriments = [
            'proteins' => 20,           // Format API
            'saturated-fat' => 1.5,     // Format API
            'fiber' => 3.5,             // Format API
            'sugars' => 2,              // Format API
            'energy-kcal' => 120,        // Format API
        ];

        $result = $this->service->calculateGradeFromNutriments($nutriments);

        $this->assertEquals('A', $result['grade']);
        $this->assertGreaterThanOrEqual(80, $result['percentage']);
    }

    public function testCalculateGradeFromNutrimentsWithMissingData()
    {
        $nutriments = [
            'proteins_100g' => 10,
            // Données manquantes = 0
        ];

        $result = $this->service->calculateGradeFromNutriments($nutriments);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('grade', $result);
        $this->assertArrayHasKey('percentage', $result);
    }
}
