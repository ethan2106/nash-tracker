<?php

use App\Service\ActivityService;
use PHPUnit\Framework\TestCase;

class ActivityServiceTest extends TestCase
{
    private $pdoMock;

    private $stmtMock;

    private $activityService;

    protected function setUp(): void
    {
        // Créer les mocks
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        // Injecter le mock PDO dans ActivityService
        $this->activityService = new ActivityService($this->pdoMock);
    }

    public function testGetRecentActivitiesCountReturnsInt()
    {
        $userId = 1;

        // Nouveau comportement: une seule requête UNION pour le comptage
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        // execute() est appelé sans paramètres (bindValue utilisé)
        $this->stmtMock->expects($this->once())
            ->method('execute');

        $this->stmtMock->expects($this->once())
            ->method('fetch')
            ->with($this->anything())
            ->willReturn(['total' => 45]);

        $result = $this->activityService->getRecentActivitiesCount($userId);

        $this->assertIsInt($result);
        $this->assertEquals(45, $result); // 3 requêtes * 15 = 45
    }

    public function testGetRecentActivitiesReturnsArray()
    {
        $userId = 1;
        $limit = 5;
        $offset = 0;

        // Nouveau comportement: une seule requête UNION triée par date
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute');

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                ['type' => 'repas', 'id' => 1, 'date' => '2025-11-29 12:00:00', 'description' => 'Repas: Déjeuner', 'valeur' => 500, 'unite' => 'kcal'],
            ]);

        $result = $this->activityService->getRecentActivities($userId, $limit, $offset);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('type', $result[0]);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('date', $result[0]);
        $this->assertEquals('repas', $result[0]['type']);
    }

    public function testGetRecentActivitiesWithCustomLimit()
    {
        $userId = 1;
        $limit = 10;
        $offset = 5;

        // Nouveau comportement: une seule requête
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute');

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]); // Résultat vide

        $result = $this->activityService->getRecentActivities($userId, $limit, $offset);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetRecentActivitiesCountWithZeroActivities()
    {
        $userId = 1;

        // Nouveau comportement: une seule requête de comptage
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute');

        $this->stmtMock->expects($this->once())
            ->method('fetch')
            ->with($this->anything())
            ->willReturn(['total' => 0]);

        $result = $this->activityService->getRecentActivitiesCount($userId);

        $this->assertEquals(0, $result);
    }
}
