<?php

use PHPUnit\Framework\TestCase;

class FormInputTest extends TestCase
{
    protected function setUp(): void
    {
        // Include the component
        require_once __DIR__ . '/../src/View/components/form-input.php';
    }

    public function testFormInputGeneratesCorrectHtml()
    {
        $html = form_input('email', 'Email', 'test@example.com', 'email', true, '<i class="icon"></i>', 'email');

        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('type="email"', $html);
        $this->assertStringContainsString('value="test@example.com"', $html);
        $this->assertStringContainsString('required', $html);
        $this->assertStringContainsString('autocomplete="email"', $html);
        $this->assertStringContainsString('<i class="icon"></i> Email', $html);
        $this->assertStringContainsString('for="email"', $html);
    }

    public function testFormInputWithCustomId()
    {
        $html = form_input('password', 'Mot de passe', '', 'password', true, '', 'current-password', 'mot_de_passe');

        $this->assertStringContainsString('id="mot_de_passe"', $html);
        $this->assertStringContainsString('for="mot_de_passe"', $html);
    }

    public function testFormInputWithCustomClass()
    {
        $customClass = 'custom-class';
        $html = form_input('test', 'Test', '', 'text', false, '', '', '', $customClass);

        $this->assertStringContainsString('class="' . $customClass . '"', $html);
    }

    public function testFormInputDefaultClass()
    {
        $html = form_input('test', 'Test', '', 'text');

        $this->assertStringContainsString('class="w-full p-3 rounded-xl border border-blue-100 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-300 transition shadow-sm"', $html);
    }
}
