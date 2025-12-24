<?php

namespace Tests\Repository;

use PHPUnit\Framework\TestCase;
use App\Repository\MealRepository;

class MealRepositoryTest extends TestCase
{
    private MealRepository $repository;
    private int $userId;
    private array $foodIds;

    protected function setUp(): void
    {
        $this->repository = new MealRepository();

        // Reset tables to ensure isolation
        $db = $this->getDb();
        $db->exec('DELETE FROM repas_aliments');
        $db->exec('DELETE FROM repas');
        $db->exec('DELETE FROM aliments');
        $db->exec('DELETE FROM users');
        try {
            $db->exec('DELETE FROM sqlite_sequence WHERE name IN ("repas_aliments", "repas", "aliments", "users")');
        } catch (\Throwable $e) {
            // OK if sqlite_sequence doesn't exist or no AUTOINCREMENT
        }

        // Insert test data
        $this->insertTestData();
    }

    private function getDb(): \PDO
    {
        return \App\Model\Database::getInstance();
    }

    private function insertTestData(): void
    {
        $db = $this->getDb();

        // Insert test user
        $stmt = $db->prepare('INSERT INTO users (pseudo, email, mot_de_passe) VALUES (?, ?, ?)');
        $stmt->execute(['testuser', 'test@example.com', password_hash('password', PASSWORD_DEFAULT)]);
        $this->userId = (int)$db->lastInsertId();

        // Insert test foods
        $foods = [
            ['nom' => 'Pomme', 'calories_100g' => 52, 'proteines_100g' => 0.2, 'glucides_100g' => 14, 'lipides_100g' => 0.2, 'fibres_100g' => 2.4, 'sucres_100g' => 10, 'acides_gras_satures_100g' => 0, 'sodium_100g' => 1],
            ['nom' => 'Banane', 'calories_100g' => 89, 'proteines_100g' => 1.1, 'glucides_100g' => 23, 'lipides_100g' => 0.3, 'fibres_100g' => 2.6, 'sucres_100g' => 12, 'acides_gras_satures_100g' => 0, 'sodium_100g' => 1],
        ];
        $foodIds = [];
        foreach ($foods as $food) {
            $stmt = $db->prepare('INSERT INTO aliments (nom, calories_100g, proteines_100g, glucides_100g, lipides_100g, fibres_100g, sucres_100g, acides_gras_satures_100g, sodium_100g) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$food['nom'], $food['calories_100g'], $food['proteines_100g'], $food['glucides_100g'], $food['lipides_100g'], $food['fibres_100g'], $food['sucres_100g'], $food['acides_gras_satures_100g'], $food['sodium_100g']]);
            $foodIds[] = (int)$db->lastInsertId();
        }
        $this->foodIds = $foodIds;
    }

    public function testGetMealByDateAndTypeReturnsNullWhenNoMeal()
    {
        $result = $this->repository->getMealByDateAndType($this->userId, '2025-01-01', 'dejeuner');

        $this->assertNull($result);
    }

    public function testGetMealByDateAndTypeFindsExistingMeal()
    {
        // Create a meal first
        $mealId = $this->repository->createMealWithFood($this->userId, 'dejeuner', $this->foodIds[0], 100);

        $result = $this->repository->getMealByDateAndType($this->userId, date('Y-m-d'), 'dejeuner');

        $this->assertIsArray($result);
        $this->assertEquals($mealId, $result['id']);
        $this->assertEquals('dejeuner', $result['type_repas']);
    }

    public function testCreateMealWithFoodCreatesMealAndAddsFood()
    {
        $mealId = $this->repository->createMealWithFood($this->userId, 'dejeuner', $this->foodIds[0], 150);

        $this->assertIsInt($mealId);
        $this->assertGreaterThan(0, $mealId);

        // Verify meal exists
        $meals = $this->repository->getMealsByDate($this->userId, date('Y-m-d'));
        $this->assertCount(1, $meals);
        $this->assertEquals($mealId, $meals[0]['id']);
        $this->assertEquals(1, $meals[0]['aliment_count']);
        $this->assertEquals(78.0, $meals[0]['calories_total'], '', 0.01); // 52 * 1.5
    }

    public function testCreateMealWithFoodReturnsFalseForInvalidFood()
    {
        $result = $this->repository->createMealWithFood($this->userId, 'dejeuner', 99999, 100);

        $this->assertFalse($result);
    }

    public function testCreateMealWithFoodReturnsFalseForInvalidQuantity()
    {
        $this->assertFalse($this->repository->createMealWithFood($this->userId, 'dejeuner', $this->foodIds[0], 0));
        $this->assertFalse($this->repository->createMealWithFood($this->userId, 'dejeuner', $this->foodIds[0], -10));
    }

    public function testAddFoodToMealAddsToExistingMeal()
    {
        // Create meal first
        $mealId = $this->repository->createMealWithFood($this->userId, 'dejeuner', $this->foodIds[0], 100);

        // Add another food
        $result = $this->repository->addFoodToMeal($mealId, $this->foodIds[1], 200);

        $this->assertTrue($result);

        // Verify
        $meals = $this->repository->getMealsByDate($this->userId, date('Y-m-d'));
        $this->assertCount(1, $meals);
        $this->assertEquals(2, $meals[0]['aliment_count']);
        $this->assertEquals(52 + 178, $meals[0]['calories_total'], '', 0.01); // 52*1 + 89*2
    }

    public function testAddFoodToMealReturnsFalseForInvalidMeal()
    {
        $result = $this->repository->addFoodToMeal(99999, $this->foodIds[0], 100);

        $this->assertFalse($result);
    }

    public function testAddFoodToMealReturnsFalseForInvalidFood()
    {
        // Create meal first
        $mealId = $this->repository->createMealWithFood($this->userId, 'dejeuner', $this->foodIds[0], 100);

        $result = $this->repository->addFoodToMeal($mealId, 99999, 100);

        $this->assertFalse($result);
    }

    public function testAddFoodToMealReturnsFalseForInvalidQuantity()
    {
        // Create meal first
        $mealId = $this->repository->createMealWithFood($this->userId, 'dejeuner', $this->foodIds[0], 100);

        $this->assertFalse($this->repository->addFoodToMeal($mealId, $this->foodIds[1], 0));
        $this->assertFalse($this->repository->addFoodToMeal($mealId, $this->foodIds[1], -10));
    }

    public function testGetMealsByDateReturnsMealsWithTotals()
    {
        // Create two meals
        $this->repository->createMealWithFood($this->userId, 'petit_dejeuner', $this->foodIds[0], 100);
        $this->repository->createMealWithFood($this->userId, 'dejeuner', $this->foodIds[1], 200);

        $meals = $this->repository->getMealsByDate($this->userId, date('Y-m-d'));

        $this->assertCount(2, $meals);

        // Check totals
        $breakfast = array_filter($meals, fn($m) => $m['type_repas'] === 'petit_dejeuner');
        $lunch = array_filter($meals, fn($m) => $m['type_repas'] === 'dejeuner');

        $this->assertEquals(52.0, reset($breakfast)['calories_total'], '', 0.01);
        $this->assertEquals(178.0, reset($lunch)['calories_total'], '', 0.01); // 89 * 2
    }

    public function testGetMealsByDateReturnsEmptyForNoMeals()
    {
        $meals = $this->repository->getMealsByDate($this->userId, '2025-01-01');

        $this->assertIsArray($meals);
        $this->assertEmpty($meals);
    }

    public function testRemoveFoodFromMealRemovesFood()
    {
        // Create meal with two foods
        $mealId = $this->repository->createMealWithFood($this->userId, 'dejeuner', $this->foodIds[0], 100);
        $this->repository->addFoodToMeal($mealId, $this->foodIds[1], 100);

        // Remove one food
        $result = $this->repository->removeFoodFromMeal($mealId, $this->foodIds[0]);

        $this->assertTrue($result);

        // Verify
        $meals = $this->repository->getMealsByDate($this->userId, date('Y-m-d'));
        $this->assertCount(1, $meals);
        $this->assertEquals(1, $meals[0]['aliment_count']);
        $this->assertEquals(89.0, $meals[0]['calories_total'], '', 0.01);
    }

    public function testRemoveFoodFromMealReturnsFalseForNonExistent()
    {
        // Create meal
        $mealId = $this->repository->createMealWithFood($this->userId, 'dejeuner', $this->foodIds[0], 100);

        // Try to remove non-existent food
        $result = $this->repository->removeFoodFromMeal($mealId, 99999);

        $this->assertFalse($result);
    }

    public function testRemoveFoodFromMealRemovesLastFoodLeavesMealEmpty()
    {
        // Create meal with one food
        $mealId = $this->repository->createMealWithFood($this->userId, 'dejeuner', $this->foodIds[0], 100);

        // Remove the only food
        $result = $this->repository->removeFoodFromMeal($mealId, $this->foodIds[0]);

        $this->assertTrue($result);

        // Verify meal still exists but is empty
        $meals = $this->repository->getMealsByDate($this->userId, date('Y-m-d'));
        $this->assertCount(1, $meals);
        $this->assertEquals(0, $meals[0]['aliment_count']);
        $this->assertEquals(0.0, $meals[0]['calories_total'], '', 0.01);
    }
}