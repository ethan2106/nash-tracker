<?php

namespace Tests\Repository;

use App\Repository\FoodRepository;
use PHPUnit\Framework\TestCase;

class FoodRepositoryTest extends TestCase
{
    private FoodRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new FoodRepository();

        // Reset table to ensure isolation
        $db = $this->getDb();
        $db->exec('DELETE FROM aliments');

        try
        {
            $db->exec('DELETE FROM sqlite_sequence WHERE name="aliments"');
        } catch (\Throwable $e)
        {
            // OK if sqlite_sequence doesn't exist or no AUTOINCREMENT
        }

        // Insert test data into in-memory DB
        $this->insertTestFoods();
    }

    private function getDb(): \PDO
    {
        return \App\Model\Database::getInstance();
    }

    private function insertTestFoods(): void
    {
        $db = $this->getDb();

        $foods = [
            ['nom' => 'Pomme', 'calories_100g' => 52, 'proteines_100g' => 0.2, 'glucides_100g' => 14, 'lipides_100g' => 0.2, 'fibres_100g' => 2.4],
            ['nom' => 'Banane', 'calories_100g' => 89, 'proteines_100g' => 1.1, 'glucides_100g' => 23, 'lipides_100g' => 0.3, 'fibres_100g' => 2.6],
            ['nom' => 'Pain complet', 'calories_100g' => 247, 'proteines_100g' => 9.4, 'glucides_100g' => 41, 'lipides_100g' => 3.2, 'fibres_100g' => 6.8],
            ['nom' => 'Fromage blanc', 'calories_100g' => 72, 'proteines_100g' => 10.3, 'glucides_100g' => 3.4, 'lipides_100g' => 0.4, 'fibres_100g' => 0],
        ];

        foreach ($foods as $food)
        {
            $stmt = $db->prepare('INSERT INTO aliments (nom, calories_100g, proteines_100g, glucides_100g, lipides_100g, fibres_100g) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$food['nom'], $food['calories_100g'], $food['proteines_100g'], $food['glucides_100g'], $food['lipides_100g'], $food['fibres_100g']]);
        }
    }

    public function testSearchSavedFoodsFindsMatchingFoods()
    {
        $result = $this->repository->searchSavedFoods('pomme');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Pomme', $result[0]['nom']);
    }

    public function testSearchSavedFoodsReturnsEmptyForNoMatch()
    {
        $result = $this->repository->searchSavedFoods('xyz');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testSearchSavedFoodsIsCaseInsensitive()
    {
        $result = $this->repository->searchSavedFoods('POMME');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Pomme', $result[0]['nom']);
    }

    public function testSearchSavedFoodsLimitsTo20Results()
    {
        // Insert more foods to exceed limit
        $db = $this->getDb();
        for ($i = 0; $i < 25; $i++)
        {
            $stmt = $db->prepare('INSERT INTO aliments (nom, calories_100g, proteines_100g, glucides_100g, lipides_100g, fibres_100g) VALUES (?, 100, 1, 20, 1, 1)');
            $stmt->execute(["Test Food {$i}"]);
        }

        $result = $this->repository->searchSavedFoods('Test');

        $this->assertIsArray($result);
        $this->assertCount(20, $result);
    }

    public function testGetSavedFoodsReturnsAllWithoutLimit()
    {
        $result = $this->repository->getSavedFoods();

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(4, count($result)); // at least our test foods
    }

    public function testGetSavedFoodsWithLimitAndOffset()
    {
        $result = $this->repository->getSavedFoods(2, 1);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testCountSavedFoodsReturnsCorrectCount()
    {
        $count = $this->repository->countSavedFoods();

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(4, $count);
    }

    public function testSaveFoodFromAPIInsertsNewFood()
    {
        $data = [
            'product_name' => 'Unique Test Food',
            'brands' => 'Test Brand',
            'image_url' => 'http://example.com/image.jpg',
            'code' => '123456789',
            'nutriments' => [
                'energy-kcal_100g' => 150,
                'proteins_100g' => 5,
                'carbohydrates_100g' => 20,
                'fat_100g' => 2,
                'fiber_100g' => 3,
            ],
        ];

        $result = $this->repository->saveFoodFromAPI($data);

        $this->assertTrue($result);

        // Verify it was inserted
        $saved = $this->repository->searchSavedFoods('Unique Test Food');
        $this->assertCount(1, $saved);
        $this->assertEquals('Unique Test Food', $saved[0]['nom']);
    }

    public function testSaveFoodFromAPIReturnsTrueForExistingFood()
    {
        // First save
        $data = [
            'product_name' => 'Existing Unique Food',
            'code' => '999999999',
            'nutriments' => ['energy-kcal_100g' => 100],
        ];
        $this->repository->saveFoodFromAPI($data);

        // Save again with same code
        $result = $this->repository->saveFoodFromAPI($data);

        $this->assertTrue($result);

        // Should not duplicate
        $saved = $this->repository->searchSavedFoods('Existing Unique Food');
        $this->assertCount(1, $saved);
    }

    public function testSaveFoodFromAPIHandlesMissingData()
    {
        $initialCount = $this->repository->countSavedFoods();

        // Test with missing product_name
        $data = [
            'code' => '111111111',
            'nutriments' => ['energy-kcal_100g' => 50],
        ];

        $result = $this->repository->saveFoodFromAPI($data);

        // Should refuse insertion if product_name is missing
        $this->assertFalse($result);
        $this->assertEquals($initialCount, $this->repository->countSavedFoods());
    }
}
