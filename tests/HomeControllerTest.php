<?php

use App\Controller\HomeController;
use App\Model\UserConfigModel;
use App\Service\DashboardService;
use App\Service\GamificationService;
use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
    private $dashboardServiceMock;

    private $userConfigModelMock;

    private $gamificationServiceMock;

    private $homeController;

    protected function setUp(): void
    {
        $this->dashboardServiceMock = $this->createMock(DashboardService::class);
        $this->userConfigModelMock = $this->createMock(UserConfigModel::class);
        $this->gamificationServiceMock = $this->createMock(GamificationService::class);
        $this->homeController = new HomeController($this->dashboardServiceMock, $this->userConfigModelMock, $this->gamificationServiceMock);
    }

    public function testPrepareHomeViewDataForGuestUser()
    {
        $user = null;

        $result = $this->homeController->prepareHomeViewData($user);

        $this->assertIsArray($result);
        $this->assertFalse($result['isLoggedIn']);
        $this->assertEquals('Prenez le contrôle de votre Santé Hépatique', $result['pageTitle']);
        $this->assertNull($result['user']);
        $this->assertArrayNotHasKey('dashboard', $result);
        $this->assertArrayNotHasKey('userConfig', $result);
    }

    public function testPrepareHomeViewDataForLoggedInUser()
    {
        $user = ['id' => 1, 'pseudo' => 'TestUser'];

        $dashboardData = ['scores' => ['global' => 85], 'toasts' => ['success' => 'Test toast']];
        $this->dashboardServiceMock
            ->expects($this->once())
            ->method('getDashboardData')
            ->with($user)
            ->willReturn($dashboardData);

        $userConfig = ['theme' => 'dark'];
        $this->userConfigModelMock
            ->expects($this->once())
            ->method('getAll')
            ->with(1)
            ->willReturn($userConfig);

        $levelData = ['level' => 5, 'xp' => 850, 'xpToNext' => 150];
        $this->gamificationServiceMock
            ->expects($this->once())
            ->method('computeLevel')
            ->with(850) // 85 * 10
            ->willReturn($levelData);

        $result = $this->homeController->prepareHomeViewData($user);

        $this->assertIsArray($result);
        $this->assertTrue($result['isLoggedIn']);
        $this->assertEquals('Tableau de bord', $result['pageTitle']);
        $this->assertStringContainsString('Bonjour TestUser', $result['pageSubtitle']);
        $this->assertEquals($user, $result['user']);
        $this->assertEquals($dashboardData, $result['dashboard']);
        $this->assertEquals($userConfig, $result['userConfig']);
        $this->assertEquals($levelData, $result['levelData']);
        $this->assertEquals(['success' => 'Test toast'], $result['toasts']);
    }

    public function testGetDashboardDataDelegatesToService()
    {
        $user = ['id' => 1];
        $expectedData = ['data' => 'test'];

        $this->dashboardServiceMock
            ->expects($this->once())
            ->method('getDashboardData')
            ->with($user)
            ->willReturn($expectedData);

        $result = $this->homeController->getDashboardData($user);

        $this->assertEquals($expectedData, $result);
    }
}
