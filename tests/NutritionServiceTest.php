<?php

use App\Service\CacheService;
use App\Service\NutritionService;
use PHPUnit\Framework\TestCase;

class NutritionServiceTest extends TestCase
{
    private $pdoMock;

    private $stmtMock;

    private $cacheMock;

    private $nutritionService;

    protected function setUp(): void
    {
        // Créer les mocks
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
        $this->cacheMock = $this->createMock(CacheService::class);

        // Mock cache->remember pour exécuter directement le callback
        $this->cacheMock->method('remember')
            ->willReturnCallback(function ($namespace, $key, $callback)
            {
                return $callback();
            });

        // Injecter le mock PDO dans NutritionService
        $this->nutritionService = new NutritionService($this->pdoMock, $this->cacheMock);
    }

    public function testGetCurrentNutritionReturnsExpectedStructure()
    {
        $userId = 1;

        // Mock pour la requête de nutrition actuelle
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([$userId, date('Y-m-d')]);

        $this->stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'total_calories' => 1850.5,
                'total_proteines' => 85.2,
                'total_fibres' => 22.1,
                'total_graisses_sat' => 45.3,
            ]);

        $result = $this->nutritionService->getCurrentNutrition($userId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('calories', $result);
        $this->assertArrayHasKey('proteines', $result);
        $this->assertArrayHasKey('fibres', $result);
        $this->assertArrayHasKey('graisses_sat', $result);
        $this->assertEquals(1850.5, $result['calories']);
        $this->assertEquals(85.2, $result['proteines']);
    }

    public function testGetWeeklyNutritionReturnsExpectedStructure()
    {
        $userId = 1;

        // Mock pour la requête de nutrition hebdomadaire
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([$userId]);

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'date' => '2025-11-29',
                    'calories' => 1850.5,
                    'proteines' => 85.2,
                    'fibres' => 22.1,
                    'graisses_sat' => 45.3,
                ],
            ]);

        $result = $this->nutritionService->getWeeklyNutrition($userId);

        $this->assertIsArray($result);
        $this->assertCount(7, $result); // Devrait retourner 7 jours
        $this->assertArrayHasKey('date', $result[0]);
        $this->assertArrayHasKey('day', $result[0]);
        $this->assertArrayHasKey('calories', $result[0]);
        $this->assertArrayHasKey('proteines', $result[0]);
    }

    public function testCalculateHealthScoreWithValidData()
    {
        $currentNutrition = [
            'calories' => 1800,
            'proteines' => 80,
            'fibres' => 25,
            'graisses_sat' => 40,
        ];

        $objectifs = [
            'calories_perte' => 1800,
            'proteines_min' => 86.4,
            'fibres_min' => 25,
            'graisses_sat_max' => 50,
        ];

        // Créer une instance réelle pour ce test purement calculatoire
        $cacheMock = $this->createMock(CacheService::class);
        $service = new \App\Service\NutritionService($this->pdoMock, $cacheMock);
        $score = $service->calculateHealthScore($currentNutrition, $objectifs);

        $this->assertIsInt($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function testCalculateHealthScoreWithNullData()
    {
        $score = $this->nutritionService->calculateHealthScore(null, null);

        $this->assertEquals(0, $score);
    }

    public function testCalculateHealthScoreWithPartialData()
    {
        $currentNutrition = ['calories' => 1800, 'proteines' => 80, 'fibres' => 25, 'graisses_sat' => 40];
        $objectifs = [
            'calories_perte' => 1800,
            'proteines_min' => 70,
            'fibres_min' => 25,
            'graisses_sat_max' => 50,
        ];

        // Créer une instance réelle pour ce test purement calculatoire
        $cacheMock = $this->createMock(CacheService::class);
        $service = new \App\Service\NutritionService($this->pdoMock, $cacheMock);
        $score = $service->calculateHealthScore($currentNutrition, $objectifs);

        $this->assertIsInt($score);
        $this->assertGreaterThanOrEqual(0, $score);
    }
}
