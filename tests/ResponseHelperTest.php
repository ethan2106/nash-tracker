<?php

use App\Helper\ResponseHelper;
use PHPUnit\Framework\TestCase;

class ResponseHelperTest extends TestCase
{
    protected function setUp(): void
    {
        // Démarrer la session pour les tests
        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }
    }

    protected function tearDown(): void
    {
        // Nettoyer la session après chaque test
        if (isset($_SESSION['flash']))
        {
            unset($_SESSION['flash']);
        }
    }

    public function testAddSuccessMessage()
    {
        ResponseHelper::addSuccessMessage('Test success message');

        $this->assertEquals('success', $_SESSION['flash']['type']);
        $this->assertEquals('Test success message', $_SESSION['flash']['msg']);
    }

    public function testAddErrorMessage()
    {
        ResponseHelper::addErrorMessage('Test error message');

        $this->assertEquals('error', $_SESSION['flash']['type']);
        $this->assertEquals('Test error message', $_SESSION['flash']['msg']);
    }

    public function testSetFlashMessage()
    {
        ResponseHelper::setFlashMessage('warning', 'Test warning message');

        $this->assertEquals('warning', $_SESSION['flash']['type']);
        $this->assertEquals('Test warning message', $_SESSION['flash']['msg']);
    }

    public function testValidateCsrfToken()
    {
        // Simuler un token CSRF valide
        $_SESSION['csrf_token'] = 'valid_token_123';

        $this->assertTrue(ResponseHelper::validateCsrfToken('valid_token_123'));
        $this->assertFalse(ResponseHelper::validateCsrfToken('invalid_token'));
        $this->assertFalse(ResponseHelper::validateCsrfToken(''));
    }
}
