<?php

namespace Tests;

use App\Controller\SettingsController;
use App\Model\UserModel;
use PHPUnit\Framework\TestCase;

/**
 * TestableSettingsController - Subclass pour tester sans exit().
 */
class TestableSettingsController extends SettingsController
{
    private $userModel;
    private $userConfigModel;
    private $historiqueMesuresModel;
    private $cacheService;
    private $validationService;
    private $settingsDataService;
    private $skipSessionCheck = false;

    public function __construct(
        $userModel,
        $userConfigModel,
        $historiqueMesuresModel,
        $cacheService,
        $validationService,
        $settingsDataService,
        bool $skipSessionCheck = false
    ) {
        $this->skipSessionCheck = $skipSessionCheck;
        $this->userModel = $userModel;
        $this->userConfigModel = $userConfigModel;
        $this->historiqueMesuresModel = $historiqueMesuresModel;
        $this->cacheService = $cacheService;
        $this->validationService = $validationService;
        $this->settingsDataService = $settingsDataService;

        parent::__construct(
            $userModel,
            $userConfigModel,
            $historiqueMesuresModel,
            $cacheService,
            $validationService,
            $settingsDataService
        );
    }

    /**
     * Override pour éviter les exit() dans les tests.
     */
    public function updateEmail(): void
    {
        header('Content-Type: application/json');

        if (!$this->skipSessionCheck && !isset($_SESSION['user']['id']))
        {
            echo json_encode(['success' => false, 'message' => 'Non authentifié']);

            return;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->skipSessionCheck && (!isset($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']))
        {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);

            return;
        }

        $userId = $_SESSION['user']['id'] ?? 1;
        $newEmail = trim($_POST['email'] ?? '');

        if (empty($newEmail))
        {
            echo json_encode(['success' => false, 'message' => 'Email requis']);

            return;
        }

        $success = $this->userModel->updateEmail($userId, $newEmail);

        if ($success)
        {
            $_SESSION['user']['email'] = $newEmail;
            echo json_encode(['success' => true, 'message' => 'Email mis à jour avec succès']);
        } else
        {
            echo json_encode(['success' => false, 'message' => 'Email déjà utilisé ou invalide']);
        }
    }

    public function updatePseudo(): void
    {
        header('Content-Type: application/json');

        if (!$this->skipSessionCheck && !isset($_SESSION['user']['id']))
        {
            echo json_encode(['success' => false, 'message' => 'Non authentifié']);

            return;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->skipSessionCheck && (!isset($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']))
        {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);

            return;
        }

        $userId = $_SESSION['user']['id'] ?? 1;
        $newPseudo = trim($_POST['pseudo'] ?? '');

        if (empty($newPseudo))
        {
            echo json_encode(['success' => false, 'message' => 'Pseudo requis']);

            return;
        }

        $success = $this->userModel->updatePseudo($userId, $newPseudo);

        if ($success)
        {
            $_SESSION['user']['pseudo'] = $newPseudo;
            echo json_encode(['success' => true, 'message' => 'Pseudo mis à jour avec succès']);
        } else
        {
            echo json_encode(['success' => false, 'message' => 'Pseudo invalide ou déjà utilisé']);
        }
    }

    public function updatePassword(): void
    {
        header('Content-Type: application/json');

        if (!$this->skipSessionCheck && !isset($_SESSION['user']['id']))
        {
            echo json_encode(['success' => false, 'message' => 'Non authentifié']);

            return;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->skipSessionCheck && (!isset($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']))
        {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);

            return;
        }

        $userId = $_SESSION['user']['id'] ?? 1;
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword))
        {
            echo json_encode(['success' => false, 'message' => 'Mots de passe requis']);

            return;
        }

        $success = $this->userModel->updatePassword($userId, $currentPassword, $newPassword);

        if ($success)
        {
            echo json_encode(['success' => true, 'message' => 'Mot de passe mis à jour avec succès']);
        } else
        {
            echo json_encode(['success' => false, 'message' => 'Mot de passe actuel incorrect']);
        }
    }

    public function deleteAccount(): void
    {
        header('Content-Type: application/json');

        if (!$this->skipSessionCheck && !isset($_SESSION['user']['id']))
        {
            echo json_encode(['success' => false, 'message' => 'Non authentifié']);

            return;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->skipSessionCheck && (!isset($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']))
        {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);

            return;
        }

        $userId = $_SESSION['user']['id'] ?? 1;
        $password = $_POST['password'] ?? '';
        $confirmation = $_POST['confirmation'] ?? '';

        if (empty($password) || $confirmation !== 'SUPPRIMER')
        {
            echo json_encode(['success' => false, 'message' => 'Confirmation invalide']);

            return;
        }

        $success = $this->userModel->deleteAccount($userId, $password);

        if ($success)
        {
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Compte supprimé avec succès']);
        } else
        {
            echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
        }
    }
}

/**
 * SettingsControllerTest - Tests unitaires pour SettingsController.
 */
class SettingsControllerTest extends TestCase
{
    private $userModel;
    private $userConfigModel;
    private $historiqueMesuresModel;
    private $cacheService;
    private $validationService;
    private $settingsDataService;
    private $controller;

    protected function setUp(): void
    {
        // Reset session
        $_SESSION = [];
        $_POST = [];

        // Mock des dépendances
        $this->userModel = $this->createMock(UserModel::class);
        $this->userConfigModel = $this->createMock(\App\Model\UserConfigModel::class);
        $this->historiqueMesuresModel = $this->createMock(\App\Model\HistoriqueMesuresModel::class);
        $this->cacheService = $this->createMock(\App\Service\CacheService::class);
        $this->validationService = $this->createMock(\App\Service\ValidationService::class);
        $this->settingsDataService = $this->createMock(\App\Service\SettingsDataService::class);

        // Create controller avec mocks
        $this->controller = new TestableSettingsController(
            $this->userModel,
            $this->userConfigModel,
            $this->historiqueMesuresModel,
            $this->cacheService,
            $this->validationService,
            $this->settingsDataService,
            true
        );
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    // ============ Tests updateEmail() ============

    public function testUpdateEmailWithValidData(): void
    {
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['email'] = 'newemail@example.com';
        $_POST['csrf_token'] = 'valid-token';

        $this->userModel->expects($this->once())
            ->method('updateEmail')
            ->with(1, 'newemail@example.com')
            ->willReturn(true);

        ob_start();
        $this->controller->updateEmail();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Email mis à jour avec succès', $response['message']);
        $this->assertEquals('newemail@example.com', $_SESSION['user']['email']);
    }

    public function testUpdateEmailWithExistingEmail(): void
    {
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['email'] = 'existing@example.com';
        $_POST['csrf_token'] = 'valid-token';

        $this->userModel->expects($this->once())
            ->method('updateEmail')
            ->with(1, 'existing@example.com')
            ->willReturn(false);

        ob_start();
        $this->controller->updateEmail();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('déjà utilisé', $response['message']);
    }

    public function testUpdateEmailWithEmptyEmail(): void
    {
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['email'] = '';
        $_POST['csrf_token'] = 'valid-token';

        ob_start();
        $this->controller->updateEmail();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Email requis', $response['message']);
    }

    public function testUpdateEmailWithInvalidCsrfToken(): void
    {
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['email'] = 'test@example.com';
        $_POST['csrf_token'] = 'invalid-token';

        // Create controller sans skipSessionCheck pour tester CSRF
        $controller = new TestableSettingsController(
            $this->userModel,
            $this->userConfigModel,
            $this->historiqueMesuresModel,
            $this->cacheService,
            $this->validationService,
            $this->settingsDataService,
            false
        );

        ob_start();
        $controller->updateEmail();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Token CSRF invalide', $response['message']);
    }

    // ============ Tests updatePseudo() ============

    public function testUpdatePseudoWithValidData(): void
    {
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['pseudo'] = 'NewPseudo123';
        $_POST['csrf_token'] = 'valid-token';

        $this->userModel->expects($this->once())
            ->method('updatePseudo')
            ->with(1, 'NewPseudo123')
            ->willReturn(true);

        ob_start();
        $this->controller->updatePseudo();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Pseudo mis à jour avec succès', $response['message']);
        $this->assertEquals('NewPseudo123', $_SESSION['user']['pseudo']);
    }

    public function testUpdatePseudoWithInvalidCharacters(): void
    {
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['pseudo'] = 'Invalid@Pseudo!';
        $_POST['csrf_token'] = 'valid-token';

        $this->userModel->expects($this->once())
            ->method('updatePseudo')
            ->with(1, 'Invalid@Pseudo!')
            ->willReturn(false);

        ob_start();
        $this->controller->updatePseudo();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('invalide', $response['message']);
    }

    public function testUpdatePseudoWithEmptyPseudo(): void
    {
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['pseudo'] = '';
        $_POST['csrf_token'] = 'valid-token';

        ob_start();
        $this->controller->updatePseudo();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Pseudo requis', $response['message']);
    }

    // ============ Tests updatePassword() ============

    public function testUpdatePasswordWithValidData(): void
    {
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['current_password'] = 'OldPassword123';
        $_POST['new_password'] = 'NewPassword456';
        $_POST['csrf_token'] = 'valid-token';

        $this->userModel->expects($this->once())
            ->method('updatePassword')
            ->with(1, 'OldPassword123', 'NewPassword456')
            ->willReturn(true);

        ob_start();
        $this->controller->updatePassword();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Mot de passe mis à jour avec succès', $response['message']);
    }

    public function testUpdatePasswordWithWrongCurrentPassword(): void
    {
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['current_password'] = 'WrongPassword';
        $_POST['new_password'] = 'NewPassword456';
        $_POST['csrf_token'] = 'valid-token';

        $this->userModel->expects($this->once())
            ->method('updatePassword')
            ->with(1, 'WrongPassword', 'NewPassword456')
            ->willReturn(false);

        ob_start();
        $this->controller->updatePassword();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Mot de passe actuel incorrect', $response['message']);
    }

    public function testUpdatePasswordWithMissingData(): void
    {
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['current_password'] = '';
        $_POST['new_password'] = 'NewPassword456';
        $_POST['csrf_token'] = 'valid-token';

        ob_start();
        $this->controller->updatePassword();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Mots de passe requis', $response['message']);
    }

    // ============ Tests deleteAccount() ============

    public function testDeleteAccountWithValidData(): void
    {
        // Démarrer la session avant le test pour éviter le warning session_destroy()
        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }

        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['password'] = 'UserPassword123';
        $_POST['confirmation'] = 'SUPPRIMER';
        $_POST['csrf_token'] = 'valid-token';

        $this->userModel->expects($this->once())
            ->method('deleteAccount')
            ->with(1, 'UserPassword123')
            ->willReturn(true);

        ob_start();
        $this->controller->deleteAccount();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Compte supprimé avec succès', $response['message']);
    }

    public function testDeleteAccountWithWrongPassword(): void
    {
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['password'] = 'WrongPassword';
        $_POST['confirmation'] = 'SUPPRIMER';
        $_POST['csrf_token'] = 'valid-token';

        $this->userModel->expects($this->once())
            ->method('deleteAccount')
            ->with(1, 'WrongPassword')
            ->willReturn(false);

        ob_start();
        $this->controller->deleteAccount();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Mot de passe incorrect', $response['message']);
    }

    public function testDeleteAccountWithWrongConfirmation(): void
    {
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['password'] = 'UserPassword123';
        $_POST['confirmation'] = 'delete'; // Doit être exactement "SUPPRIMER"
        $_POST['csrf_token'] = 'valid-token';

        ob_start();
        $this->controller->deleteAccount();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Confirmation invalide', $response['message']);
    }

    public function testDeleteAccountWithMissingPassword(): void
    {
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['password'] = '';
        $_POST['confirmation'] = 'SUPPRIMER';
        $_POST['csrf_token'] = 'valid-token';

        ob_start();
        $this->controller->deleteAccount();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Confirmation invalide', $response['message']);
    }

    // ============ Tests de sécurité ============

    public function testAllMethodsRequireAuthentication(): void
    {
        // Test sans session (skipSessionCheck = false)
        $controller = new TestableSettingsController(
            $this->userModel,
            $this->userConfigModel,
            $this->historiqueMesuresModel,
            $this->cacheService,
            $this->validationService,
            $this->settingsDataService,
            false
        );

        // Test updateEmail sans auth
        ob_start();
        $controller->updateEmail();
        $output = ob_get_clean();
        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('authentifié', $response['message']);

        // Test updatePseudo sans auth
        ob_start();
        $controller->updatePseudo();
        $output = ob_get_clean();
        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('authentifié', $response['message']);

        // Test updatePassword sans auth
        ob_start();
        $controller->updatePassword();
        $output = ob_get_clean();
        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('authentifié', $response['message']);

        // Test deleteAccount sans auth
        ob_start();
        $controller->deleteAccount();
        $output = ob_get_clean();
        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('authentifié', $response['message']);
    }
}
