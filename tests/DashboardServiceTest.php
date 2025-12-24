<?php

use App\Model\ObjectifsModel;
use App\Service\DashboardService;
use PHPUnit\Framework\TestCase;

class DashboardServiceTest extends TestCase
{
    private $pdoMock;

    private $stmtMock;

    private $objectifsModelMock;

    private $medicamentControllerMock;

    private $dashboardService;

    protected function setUp(): void
    {
        // Créer les mocks
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
        $this->objectifsModelMock = $this->createMock(ObjectifsModel::class);

        // Mock CacheService
        $cacheMock = $this->createMock(\App\Service\CacheService::class);
        $cacheMock->method('get')->willReturn(null); // Cache miss par défaut

        // Mock les classes statiques
        $this->objectifsModelMock->expects($this->any())
            ->method('getByUser')
            ->willReturn([
                'imc' => 22.5,
                'calories_perte' => 1800,
                'proteines_max' => 86.4,
                'fibres_max' => 30,
            ]);

        // Créer DashboardService avec mocks et CacheService
        $this->dashboardService = new DashboardService(
            $this->pdoMock,
            $cacheMock
        );
    }

    public function testGetDashboardDataReturnsExpectedStructure()
    {
        $user = ['id' => 1, 'name' => 'Test User'];

        // Mock CacheService
        $cacheMock = $this->createMock(\App\Service\CacheService::class);
        $cacheMock->method('get')->willReturn(null);

        // Créer un mock partiel qui mock les méthodes qui utilisent des modèles statiques
        $service = $this->getMockBuilder(DashboardService::class)
            ->setConstructorArgs([null, $cacheMock])
            ->onlyMethods([
                'getDashboardStats',
                'getDailyGoals',
                'computeHealthScore',
            ])
            ->getMock();

        $service->expects($this->once())
            ->method('getDashboardStats')
            ->with($user)
            ->willReturn([
                'imc' => 22.5,
                'calories_target' => 1800,
                'objectifs_completion' => 83.0,
                'activity_minutes_today' => 20,
            ]);

        $service->expects($this->once())
            ->method('getDailyGoals')
            ->willReturn(['calories' => 1800]);

        $service->expects($this->once())
            ->method('computeHealthScore')
            ->willReturn([
                'global' => 72,
                'components' => [
                    'IMC' => 25,
                    'Calories' => 20,
                    'Activité' => 12,
                ],
            ]);

        $result = $service->getDashboardData($user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('objectifs', $result);
        $this->assertArrayHasKey('dailyGoals', $result);
        $this->assertArrayHasKey('stats', $result);
        $this->assertArrayHasKey('scores', $result);
        $this->assertEquals($user, $result['user']);
    }

    public function testComputeHealthScoreReturnsConsistentStructure()
    {
        $service = new DashboardService();
        $stats = [
            'objectifs_completion' => 150, // should clamp to 100
            'activity_minutes_today' => 45,
        ];
        $userConfig = [
            'activite_objectif_minutes' => 30,
        ];
        $objectifs = ['imc' => 30.0];

        $scores = $service->computeHealthScore($stats, $userConfig, $objectifs);
        $this->assertIsArray($scores);
        $this->assertArrayHasKey('global', $scores);
        $this->assertArrayHasKey('components', $scores);
        $this->assertIsInt($scores['global']);
        $this->assertGreaterThan(0, $scores['global']);
        $this->assertArrayHasKey('IMC', $scores['components']);
        $this->assertArrayHasKey('Âge', $scores['components']);
        $this->assertArrayHasKey('Activité', $scores['components']);
        $this->assertArrayHasKey('Nutrition', $scores['components']);
    }

    public function testComputeHealthScoreNormalValues()
    {
        $service = new DashboardService();
        $stats = [
            'objectifs_completion' => 90, // Excellent nutrition
            'activity_minutes_today' => 30, // Objectif atteint
        ];
        $userConfig = [
            'activite_objectif_minutes' => 30,
        ];
        $objectifs = [
            'imc' => 22.0, // Normal
            'annee' => 1995, // Age ~30
        ];

        $scores = $service->computeHealthScore($stats, $userConfig, $objectifs);

        // IMC normal (22) = 100 points * 0.4 = 40
        $this->assertEquals(40, $scores['components']['IMC']);
        // Age ~30 = 100 points * 0.15 = 15
        $this->assertEquals(15, $scores['components']['Âge']);
        // Activité 100% = 100 points * 0.25 = 25
        $this->assertEquals(25, $scores['components']['Activité']);
        // Nutrition 90% = 100 points * 0.2 = 20
        $this->assertEquals(20, $scores['components']['Nutrition']);
        // Total: 40 + 15 + 25 + 20 = 100
        $this->assertEquals(100, $scores['global']);
    }

    public function testComputeHealthScoreHighRiskValues()
    {
        $service = new DashboardService();
        $stats = [
            'objectifs_completion' => 10, // Mauvaise nutrition
            'activity_minutes_today' => 5, // Très faible activité
        ];
        $userConfig = [
            'activite_objectif_minutes' => 30,
        ];
        $objectifs = [
            'imc' => 35.0, // Obésité sévère
            'annee' => 1960, // Age ~65
        ];

        $scores = $service->computeHealthScore($stats, $userConfig, $objectifs);

        // IMC 35 (obésité classe I) = 40 points * 0.4 = 16
        $this->assertEquals(16, $scores['components']['IMC']);
        // Age ~65 = 30 points * 0.15 = 4.5 -> 5
        $this->assertEquals(5, $scores['components']['Âge']);
        // Activité 5/30 = ~17% = 20 points * 0.25 = 5
        $this->assertEquals(5, $scores['components']['Activité']);
        // Nutrition 10% = 10 points * 0.2 = 2
        $this->assertEquals(2, $scores['components']['Nutrition']);
        // Total devrait être faible
        $this->assertLessThan(30, $scores['global']);
    }

    public function testComputeHealthScorePartialActivity()
    {
        $service = new DashboardService();
        $stats = [
            'objectifs_completion' => 75,
            'activity_minutes_today' => 22, // 75% de l'objectif
        ];
        $userConfig = [
            'activite_objectif_minutes' => 30,
        ];
        $objectifs = [
            'imc' => 27.0, // Surpoids
            'annee' => 1980, // Age ~45
        ];

        $scores = $service->computeHealthScore($stats, $userConfig, $objectifs);

        // IMC 27 = 70 points * 0.4 = 28
        $this->assertEquals(28, $scores['components']['IMC']);
        // Age ~45 = 85 points * 0.15 = 12.75 -> 13
        $this->assertEquals(13, $scores['components']['Âge']);
        // Activité 22/30 ≈73% = 60 points * 0.25 = 15
        $this->assertEquals(15, $scores['components']['Activité']);
        // Nutrition 75% = 85 points * 0.2 = 17
        $this->assertEquals(17, $scores['components']['Nutrition']);
    }

    public function testGetDashboardStatsReturnsExpectedStructure()
    {
        $user = ['id' => 1];

        // Mock CacheService
        $cacheMock = $this->createMock(\App\Service\CacheService::class);
        $cacheMock->method('get')->willReturn(null);

        // Créer un mock partiel qui mock les méthodes qui utilisent des modèles statiques
        $service = $this->getMockBuilder(DashboardService::class)
            ->setConstructorArgs([null, $cacheMock])
            ->onlyMethods([
                'calculateObjectifsCompletion',
                'getCaloriesConsumedToday',
                'getProteinesConsumedToday',
                'getGlucidesConsumedToday',
                'getLipidesConsumedToday',
                'getActivityMinutesToday',
            ])
            ->getMock();

        $service->expects($this->once())
            ->method('calculateObjectifsCompletion')
            ->willReturn(83.0);

        $service->expects($this->once())
            ->method('getCaloriesConsumedToday')
            ->willReturn(1200.0);

        $service->expects($this->once())
            ->method('getProteinesConsumedToday')
            ->willReturn(70.0);

        $service->expects($this->once())
            ->method('getGlucidesConsumedToday')
            ->willReturn(150.0);

        $service->expects($this->once())
            ->method('getLipidesConsumedToday')
            ->willReturn(45.0);

        $service->expects($this->once())
            ->method('getActivityMinutesToday')
            ->with($user['id'])
            ->willReturn(20);

        $result = $service->getDashboardStats($user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('imc', $result);
        $this->assertArrayHasKey('calories_target', $result);
        $this->assertArrayHasKey('objectifs_completion', $result);
        $this->assertArrayHasKey('calories_consumed', $result);
        $this->assertArrayHasKey('proteines_consumed', $result);
        $this->assertArrayHasKey('glucides_consumed', $result);
        $this->assertArrayHasKey('lipides_consumed', $result);
        $this->assertArrayHasKey('activity_minutes_today', $result);
        $this->assertIsNumeric($result['activity_minutes_today']);
        $this->assertIsNumeric($result['objectifs_completion']);
    }

    public function testCalculateObjectifsCompletionWithValidData()
    {
        $userId = 1;
        $objectifs = [
            'calories_perte' => 1800,
            'proteines_max' => 86.4,
            'fibres_max' => 30,
        ];

        // Créer un mock de DashboardService qui mock les méthodes internes
        $dashboardServiceMock = $this->createPartialMock(DashboardService::class, [
            'getCaloriesConsumedToday',
            'getProteinesConsumedToday',
            'getFibresConsumedToday',
        ]);

        $dashboardServiceMock->expects($this->once())
            ->method('getCaloriesConsumedToday')
            ->with($userId)
            ->willReturn(1500); // 1500 calories consommées

        $dashboardServiceMock->expects($this->once())
            ->method('getProteinesConsumedToday')
            ->with($userId)
            ->willReturn(70); // 70g protéines consommées

        $dashboardServiceMock->expects($this->once())
            ->method('getFibresConsumedToday')
            ->with($userId)
            ->willReturn(25); // 25g fibres consommées

        $result = $dashboardServiceMock->calculateObjectifsCompletion($userId, $objectifs);

        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThanOrEqual(100, $result);
        // Calcul: ((1500/1800)*100 + (70/86.4)*100 + (25/30)*100) / 3 = (83.33 + 81.02 + 83.33) / 3 = 82.56 -> 83
        $this->assertEquals(83, $result);
    }

    public function testCalculateObjectifsCompletionWithNullObjectifs()
    {
        $userId = 1;
        $objectifs = null;

        // Créer un mock partiel qui mock les méthodes DB
        $service = $this->createPartialMock(DashboardService::class, [
            'getCaloriesConsumedToday',
            'getProteinesConsumedToday',
            'getFibresConsumedToday',
        ]);

        $result = $service->calculateObjectifsCompletion($userId, $objectifs);

        $this->assertEquals(0, $result);
    }

    public function testGetDailyGoalsReturnsExpectedStructure()
    {
        $objectifs = [
            'calories_perte' => 1800,
            'proteines_min' => 69.1,
            'proteines_max' => 86.4,
            'fibres_min' => 25,
            'fibres_max' => 30,
        ];
        $userId = 1;

        // Mock CacheService
        $cacheMock = $this->createMock(\App\Service\CacheService::class);
        $cacheMock->method('get')->willReturn(null);

        // Créer un mock partiel qui mock les méthodes DB
        $service = $this->getMockBuilder(DashboardService::class)
            ->setConstructorArgs([null, $cacheMock])
            ->onlyMethods([
                'getProteinesConsumedToday',
                'getFibresConsumedToday',
                'getGlucidesConsumedToday',
                'getActivityMinutesToday',
            ])
            ->getMock();

        $service->expects($this->once())
            ->method('getProteinesConsumedToday')
            ->with($userId)
            ->willReturn(56);

        $service->expects($this->once())
            ->method('getFibresConsumedToday')
            ->with($userId)
            ->willReturn(24);

        $service->expects($this->once())
            ->method('getGlucidesConsumedToday')
            ->with($userId)
            ->willReturn(150);

        $service->expects($this->once())
            ->method('getActivityMinutesToday')
            ->with($userId)
            ->willReturn(20);

        $result = $service->getDailyGoals($objectifs, $userId);

        $this->assertIsArray($result);
        $this->assertCount(4, $result); // Activité, Glucides, Protéines, Fibres

        foreach ($result as $goal)
        {
            $this->assertArrayHasKey('icon', $goal);
            $this->assertArrayHasKey('color', $goal);
            $this->assertArrayHasKey('label', $goal);
            $this->assertArrayHasKey('target', $goal);
            $this->assertArrayHasKey('current', $goal);
            $this->assertArrayHasKey('total', $goal);
            $this->assertArrayHasKey('progress', $goal);
        }
    }
}
