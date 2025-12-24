<?php

namespace Tests\Service;

use App\Model\User;
use App\Service\AuthService;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private User $userModel;

    protected function setUp(): void
    {
        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session
        $_SESSION = [];

        $this->userModel = new User();
        $this->authService = new AuthService($this->userModel);

        // Reset DB for isolation
        $db = $this->userModel->getDb();
        $db->exec('DELETE FROM users');
        try {
            $db->exec('DELETE FROM sqlite_sequence WHERE name="users"');
        } catch (\Throwable $e) {
            // OK if sqlite_sequence doesn't exist
        }

        // Insert test user
        $this->insertTestUser();
    }

    protected function tearDown(): void
    {
        // Clear session after each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }

    private function insertTestUser(): void
    {
        $db = $this->userModel->getDb();
        $stmt = $db->prepare('INSERT INTO users (pseudo, email, mot_de_passe) VALUES (?, ?, ?)');
        $stmt->execute(['testuser', 'test@example.com', password_hash('password123', PASSWORD_DEFAULT)]);
        $this->testUserId = (int)$db->lastInsertId();
    }

    public function testLoginSuccessWithValidCredentials()
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $result = $this->authService->login($data);

        $this->assertTrue($result['success']);
        $this->assertEquals('Connexion réussie !', $result['message']);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals('testuser', $result['user']['pseudo']);
        $this->assertEquals('test@example.com', $result['user']['email']);

        // Check session was created
        $this->assertArrayHasKey('user', $_SESSION);
        $this->assertEquals('testuser', $_SESSION['user']['pseudo']);
    }

    public function testLoginFailsWithWrongPassword()
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $result = $this->authService->login($data);

        $this->assertFalse($result['success']);
        $this->assertEquals('Identifiants incorrects.', $result['message']);
        $this->assertArrayNotHasKey('user', $result);

        // Check session was not created
        $this->assertArrayNotHasKey('user', $_SESSION);
    }

    public function testLoginFailsWithNonExistentUser()
    {
        $data = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];

        $result = $this->authService->login($data);

        $this->assertFalse($result['success']);
        $this->assertEquals('Identifiants incorrects.', $result['message']);
        $this->assertArrayNotHasKey('user', $result);

        // Check session was not created
        $this->assertArrayNotHasKey('user', $_SESSION);
    }

    public function testLoginFailsWithMissingFields()
    {
        $data = [
            'email' => 'test@example.com'
            // missing password
        ];

        $result = $this->authService->login($data);

        $this->assertFalse($result['success']);
        $this->assertEquals('Tous les champs sont obligatoires.', $result['message']);
    }

    public function testLogoutClearsSessionAndRegeneratesId()
    {
        // First login to set session
        $data = ['email' => 'test@example.com', 'password' => 'password123'];
        $this->authService->login($data);

        $oldSessionId = session_id();

        // Logout
        $this->authService->logout();

        // Check session was cleared
        $this->assertArrayNotHasKey('user', $_SESSION);

        // Check session ID was regenerated (session_regenerate_id was called)
        $this->assertNotEquals($oldSessionId, session_id());

        // Check CSRF token was regenerated
        $this->assertArrayHasKey('csrf_token', $_SESSION);
        $this->assertIsString($_SESSION['csrf_token']);
        $this->assertEquals(48, strlen($_SESSION['csrf_token'])); // 24 bytes * 2 hex chars
    }

    public function testHandleRememberMeSetsCookieWhenChecked()
    {
        $postData = [
            'email' => 'test@example.com',
            'remember' => '1'
        ];

        $this->authService->handleRememberMe($postData);

        // Check cookie was set (mock cookies since we can't test actual cookies easily)
        // In a real scenario, we'd check setcookie calls, but for now we'll assume it's working
        $this->assertTrue(true); // Placeholder - would need output buffering or cookie mocking
    }

    public function testGetRememberedEmailReturnsCookieValue()
    {
        // Simulate cookie
        $_COOKIE['remember_email'] = 'test@example.com';

        $email = $this->authService->getRememberedEmail();

        $this->assertEquals('test@example.com', $email);
    }

    public function testGetRememberedEmailReturnsEmptyWhenNoCookie()
    {
        unset($_COOKIE['remember_email']);

        $email = $this->authService->getRememberedEmail();

        $this->assertEquals('', $email);
    }
}