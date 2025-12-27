<?php

namespace Tests;

use App\Controller\ProfileController;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * TestableProfileController - Subclass for testing without exit.
 */
class TestableProfileController extends ProfileController
{
    protected function jsonSuccess(array $data = [], int $statusCode = 200): void
    {
        echo json_encode(array_merge(['success' => true], $data));
    }

    protected function jsonError(array $message, int $statusCode = 400): void
    {
        echo json_encode(['success' => false, 'error' => $message]);
    }
}

/**
 * ProfileControllerTest - Tests unitaires pour ProfileController.
 */
class ProfileControllerTest extends TestCase
{
    private PDO $db;

    /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Service\NutritionService */
    private $nutritionService;

    /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Service\ActivityService */
    private $activityService;

    /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Service\ProfileDataService */
    private $profileDataService;

    /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Service\ProfileApiService */
    private $profileApiService;

    /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Service\GamificationService */
    private $gamificationService;

    private ProfileController $controller;

    protected function setUp(): void
    {
        // Mock des services pour éviter les appels DB
        $this->db = $this->createMock(PDO::class);
        $this->nutritionService = $this->createMock(\App\Service\NutritionService::class);
        $this->activityService = $this->createMock(\App\Service\ActivityService::class);
        $this->profileDataService = $this->createMock(\App\Service\ProfileDataService::class);
        $this->profileApiService = $this->createMock(\App\Service\ProfileApiService::class);
        $this->gamificationService = $this->createMock(\App\Service\GamificationService::class);

        // Injection des dépendances mockées
        $this->controller = new TestableProfileController($this->nutritionService, $this->activityService, null, null, null, null, $this->profileDataService, $this->profileApiService, null, $this->gamificationService, $this->db);
    }

    public function testGetProfileDataReturnsNullForInvalidUser(): void
    {
        $this->profileDataService->expects($this->exactly(2))
            ->method('getProfileData')
            ->willReturn(null);

        $result = $this->controller->getProfileData([]);
        $this->assertNull($result);

        $result = $this->controller->getProfileData(null);
        $this->assertNull($result);
    }

    public function testGetProfileDataCallsServices(): void
    {
        $user = ['id' => 1, 'name' => 'Test User'];

        $expected = [
            'user' => $user,
            'objectifs' => null,
            'stats' => ['imc' => 25.0],
            'currentNutrition' => ['calories' => 2000],
            'weeklyNutrition' => [['day' => 'Lun', 'calories' => 2000]],
            'recentActivities' => [['type' => 'repas', 'description' => 'Test']],
        ];

        $this->profileDataService->method('getProfileData')->with($user)->willReturn($expected);

        $result = $this->controller->getProfileData($user);

        $this->assertIsArray($result);
        $this->assertEquals($user, $result['user']);
        $this->assertEquals(['calories' => 2000], $result['currentNutrition']);
        $this->assertEquals([['day' => 'Lun', 'calories' => 2000]], $result['weeklyNutrition']);
        $this->assertEquals([['type' => 'repas', 'description' => 'Test']], $result['recentActivities']);
        $this->assertEquals(['imc' => 25.0], $result['stats']);
    }

    public function testGetRecentActivitiesDelegatesToService(): void
    {
        $this->activityService->expects($this->once())
            ->method('getRecentActivities')
            ->with(1, 10, 5)
            ->willReturn([['type' => 'repas']]);

        $result = $this->controller->getRecentActivities(1, 10, 5);

        $this->assertEquals([['type' => 'repas']], $result);
    }

    public function testGetRecentActivitiesCountDelegatesToService(): void
    {
        $this->activityService->expects($this->once())
            ->method('getRecentActivitiesCount')
            ->with(1)
            ->willReturn(42);

        $result = $this->controller->getRecentActivitiesCount(1);

        $this->assertEquals(42, $result);
    }

    public function testHandleApiRecentActivitiesReturnsJson(): void
    {
        $_SESSION = [];
        $this->profileApiService->method('getRecentActivitiesData')->with(1, 1, 5)->willReturn([
            'activities' => [['type' => 'repas']],
            'total' => 42,
            'page' => 1,
            'limit' => 5,
        ]);

        $_SESSION['user']['id'] = 1;
        $_GET['recent_page'] = 1;
        $_GET['limit'] = 5;

        ob_start();
        $this->controller->handleApiRecentActivities();
        $output = ob_get_clean();

        $data = json_decode($output, true);
        $this->assertTrue($data['success']);
        $this->assertCount(1, $data['activities']);
        $this->assertEquals(42, $data['total']);
        $this->assertEquals(1, $data['page']);
        $this->assertEquals(5, $data['limit']);
    }

    public function testHandleApiRecentActivitiesInvalidUser(): void
    {
        $_SESSION = [];
        $_SESSION['user'] = [];
        $_GET['user_id'] = 0;

        ob_start();
        $this->controller->handleApiRecentActivities();
        $output = ob_get_clean();

        $this->assertStringStartsWith('{"success":false', $output);
    }

    public function testHandleApiRecentActivitiesWithPagination(): void
    {
        $_SESSION = [];
        $this->profileApiService->method('getRecentActivitiesData')->with(1, 2, 2)->willReturn([
            'activities' => [['type' => 'repas2'], ['type' => 'repas3']],
            'total' => 5,
            'page' => 2,
            'limit' => 2,
        ]);

        $_SESSION['user']['id'] = 1;
        $_GET['recent_page'] = 2;
        $_GET['limit'] = 2;

        ob_start();
        $this->controller->handleApiRecentActivities();
        $output = ob_get_clean();

        $data = json_decode($output, true);
        $this->assertTrue($data['success']);
        $this->assertCount(2, $data['activities']);
        $this->assertEquals(5, $data['total']);
        $this->assertEquals(2, $data['page']);
        $this->assertEquals(2, $data['limit']);
    }

    public function testHandleApiRecentActivitiesServiceThrowsException(): void
    {
        $_SESSION = [];
        $this->profileApiService->method('getRecentActivitiesData')->with(1, 1, 5)->willThrowException(new \Exception('Service error'));

        $_SESSION['user']['id'] = 1;
        $_GET['recent_page'] = 1;
        $_GET['limit'] = 5;

        ob_start();
        $this->controller->handleApiRecentActivities();
        $output = ob_get_clean();

        $this->assertStringStartsWith('{"success":false', $output);
        $this->assertStringContainsString('Service error', $output);
    }
}
