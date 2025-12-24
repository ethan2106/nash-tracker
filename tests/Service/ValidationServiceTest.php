<?php

namespace Tests\Service;

use App\Service\ValidationService;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator as v;

/**
 * Tests pour ValidationService
 * Couvre toutes les méthodes de validation avec cas valides et invalides
 */
class ValidationServiceTest extends TestCase
{
    private ValidationService $validationService;

    protected function setUp(): void
    {
        $this->validationService = new ValidationService();
    }

    protected function tearDown(): void
    {
        // Nettoyage si nécessaire
    }

    // Tests pour validateUserRegistration

    public function testValidateUserRegistrationValid(): void
    {
        $data = [
            'email' => 'test@example.com',
            'pseudo' => 'testuser123',
            'password' => 'password123',
            'password_confirm' => 'password123'
        ];

        $errors = $this->validationService->validateUserRegistration($data);

        $this->assertEmpty($errors);
    }

    public function testValidateUserRegistrationInvalidEmail(): void
    {
        $data = [
            'email' => 'invalid-email',
            'pseudo' => 'testuser123',
            'password' => 'password123',
            'password_confirm' => 'password123'
        ];

        $errors = $this->validationService->validateUserRegistration($data);

        $this->assertArrayHasKey('email', $errors);
        $this->assertIsString($errors['email']);
        $this->assertNotSame('', trim($errors['email']));
    }

    public function testValidateUserRegistrationInvalidPseudo(): void
    {
        $data = [
            'email' => 'test@example.com',
            'pseudo' => 't', // trop court
            'password' => 'password123',
            'password_confirm' => 'password123'
        ];

        $errors = $this->validationService->validateUserRegistration($data);

        $this->assertArrayHasKey('pseudo', $errors);
        $this->assertIsString($errors['pseudo']);
        $this->assertNotSame('', trim($errors['pseudo']));
    }

    public function testValidateUserRegistrationInvalidPassword(): void
    {
        $data = [
            'email' => 'test@example.com',
            'pseudo' => 'testuser123',
            'password' => '123', // trop court
            'password_confirm' => '123'
        ];

        $errors = $this->validationService->validateUserRegistration($data);

        $this->assertArrayHasKey('password', $errors);
        $this->assertIsString($errors['password']);
        $this->assertNotSame('', trim($errors['password']));
    }

    public function testValidateUserRegistrationPasswordMismatch(): void
    {
        $data = [
            'email' => 'test@example.com',
            'pseudo' => 'testuser123',
            'password' => 'password123',
            'password_confirm' => 'different123'
        ];

        $errors = $this->validationService->validateUserRegistration($data);

        $this->assertArrayHasKey('password_confirm', $errors);
        $this->assertIsString($errors['password_confirm']);
        $this->assertNotSame('', trim($errors['password_confirm']));
    }

    // Tests pour validateUserLogin

    public function testValidateUserLoginValid(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $errors = $this->validationService->validateUserLogin($data);

        $this->assertEmpty($errors);
    }

    public function testValidateUserLoginInvalidEmail(): void
    {
        $data = [
            'email' => 'invalid-email',
            'password' => 'password123'
        ];

        $errors = $this->validationService->validateUserLogin($data);

        $this->assertArrayHasKey('email', $errors);
    }

    public function testValidateUserLoginEmptyPassword(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => ''
        ];

        $errors = $this->validationService->validateUserLogin($data);

        $this->assertArrayHasKey('password', $errors);
    }

    public function testValidateUserLoginMissingEmail(): void
    {
        $errors = $this->validationService->validateUserLogin([
            'password' => 'password123'
        ]);

        $this->assertArrayHasKey('email', $errors);
    }

    // Tests pour validateEmail

    public function testValidateEmailValid(): void
    {
        $errors = $this->validationService->validateEmail(['email' => 'test@example.com']);
        $this->assertEmpty($errors);
    }

    public function testValidateEmailInvalid(): void
    {
        $errors = $this->validationService->validateEmail(['email' => 'invalid']);
        $this->assertArrayHasKey('email', $errors);
    }

    // Tests pour validatePseudo

    public function testValidatePseudoValid(): void
    {
        $errors = $this->validationService->validatePseudo(['pseudo' => 'testuser_123']);
        $this->assertEmpty($errors);
    }

    public function testValidatePseudoTooShort(): void
    {
        $errors = $this->validationService->validatePseudo(['pseudo' => 't']);
        $this->assertArrayHasKey('pseudo', $errors);
    }

    public function testValidatePseudoTooLong(): void
    {
        $errors = $this->validationService->validatePseudo(['pseudo' => str_repeat('a', 51)]);
        $this->assertArrayHasKey('pseudo', $errors);
    }

    public function testValidatePseudoInvalidChars(): void
    {
        $errors = $this->validationService->validatePseudo(['pseudo' => 'test@#$%']);
        $this->assertArrayHasKey('pseudo', $errors);
    }

    // Tests pour validatePasswordChange

    public function testValidatePasswordChangeValid(): void
    {
        $data = [
            'current_password' => 'oldpass123',
            'new_password' => 'newpass123',
            'new_password_confirm' => 'newpass123'
        ];

        $errors = $this->validationService->validatePasswordChange($data);

        $this->assertEmpty($errors);
    }

    public function testValidatePasswordChangeEmptyCurrent(): void
    {
        $data = [
            'current_password' => '',
            'new_password' => 'newpass123',
            'new_password_confirm' => 'newpass123'
        ];

        $errors = $this->validationService->validatePasswordChange($data);

        $this->assertArrayHasKey('current_password', $errors);
    }

    public function testValidatePasswordChangeNewTooShort(): void
    {
        $data = [
            'current_password' => 'oldpass123',
            'new_password' => '123',
            'new_password_confirm' => '123'
        ];

        $errors = $this->validationService->validatePasswordChange($data);

        $this->assertArrayHasKey('new_password', $errors);
    }

    public function testValidatePasswordChangeConfirmMismatch(): void
    {
        $data = [
            'current_password' => 'oldpass123',
            'new_password' => 'newpass123',
            'new_password_confirm' => 'different123'
        ];

        $errors = $this->validationService->validatePasswordChange($data);

        $this->assertArrayHasKey('new_password_confirm', $errors);
    }

    // Tests pour validateDeleteAccount

    public function testValidateDeleteAccountValid(): void
    {
        $data = [
            'password' => 'password123',
            'confirmation' => 'SUPPRIMER'
        ];

        $errors = $this->validationService->validateDeleteAccount($data);

        $this->assertEmpty($errors);
    }

    public function testValidateDeleteAccountEmptyPassword(): void
    {
        $data = [
            'password' => '',
            'confirmation' => 'SUPPRIMER'
        ];

        $errors = $this->validationService->validateDeleteAccount($data);

        $this->assertArrayHasKey('password', $errors);
    }

    public function testValidateDeleteAccountWrongConfirmation(): void
    {
        $data = [
            'password' => 'password123',
            'confirmation' => 'delete'
        ];

        $errors = $this->validationService->validateDeleteAccount($data);

        $this->assertArrayHasKey('confirmation', $errors);
    }

    // Tests pour validateFoodAddition

    public function testValidateFoodAdditionValid(): void
    {
        $data = [
            'aliment_id' => 123,
            'quantite_g' => 100.5,
            'meal_type' => 'dejeuner'
        ];

        $errors = $this->validationService->validateFoodAddition($data);

        $this->assertEmpty($errors);
    }

    public function testValidateFoodAdditionInvalidId(): void
    {
        $data = [
            'aliment_id' => -1,
            'quantite_g' => 100.5,
            'meal_type' => 'dejeuner'
        ];

        $errors = $this->validationService->validateFoodAddition($data);

        $this->assertArrayHasKey('aliment_id', $errors);
    }

    public function testValidateFoodAdditionInvalidQuantity(): void
    {
        $data = [
            'aliment_id' => 123,
            'quantite_g' => 6000,
            'meal_type' => 'dejeuner'
        ];

        $errors = $this->validationService->validateFoodAddition($data);

        $this->assertArrayHasKey('quantite_g', $errors);
    }

    public function testValidateFoodAdditionInvalidMealType(): void
    {
        $data = [
            'aliment_id' => 123,
            'quantite_g' => 100.5,
            'meal_type' => 'invalid'
        ];

        $errors = $this->validationService->validateFoodAddition($data);

        $this->assertArrayHasKey('meal_type', $errors);
    }

    public function testValidateFoodAdditionZeroQuantity(): void
    {
        $errors = $this->validationService->validateFoodAddition([
            'aliment_id' => 123,
            'quantite_g' => 0,
            'meal_type' => 'dejeuner'
        ]);

        $this->assertArrayHasKey('quantite_g', $errors);
    }

    // Tests pour validateNutritionGoals

    public function testValidateNutritionGoalsValid(): void
    {
        $currentYear = (int) date('Y');

        $data = [
            'annee' => $currentYear,
            'sexe' => 'H',
            'age' => 30,
            'taille_cm' => 175,
            'poids_kg' => 70.5,
            'activite' => 'modere'
        ];

        $errors = $this->validationService->validateNutritionGoals($data);

        $this->assertEmpty($errors);
    }

    public function testValidateNutritionGoalsInvalidAnnee(): void
    {
        $data = [
            'annee' => 2019,
            'sexe' => 'H',
            'age' => 30,
            'taille_cm' => 175,
            'poids_kg' => 70.5,
            'activite' => 'modere'
        ];

        $errors = $this->validationService->validateNutritionGoals($data);

        $this->assertArrayHasKey('annee', $errors);
    }

    public function testValidateNutritionGoalsInvalidSexe(): void
    {
        $currentYear = (int) date('Y');

        $data = [
            'annee' => $currentYear,
            'sexe' => 'X',
            'age' => 30,
            'taille_cm' => 175,
            'poids_kg' => 70.5,
            'activite' => 'modere'
        ];

        $errors = $this->validationService->validateNutritionGoals($data);

        $this->assertArrayHasKey('sexe', $errors);
    }

    public function testValidateNutritionGoalsInvalidAge(): void
    {
        $currentYear = (int) date('Y');

        $data = [
            'annee' => $currentYear,
            'sexe' => 'H',
            'age' => 150,
            'taille_cm' => 175,
            'poids_kg' => 70.5,
            'activite' => 'modere'
        ];

        $errors = $this->validationService->validateNutritionGoals($data);

        $this->assertArrayHasKey('age', $errors);
    }

    public function testValidateNutritionGoalsInvalidTaille(): void
    {
        $currentYear = (int) date('Y');

        $data = [
            'annee' => $currentYear,
            'sexe' => 'H',
            'age' => 30,
            'taille_cm' => 300,
            'poids_kg' => 70.5,
            'activite' => 'modere'
        ];

        $errors = $this->validationService->validateNutritionGoals($data);

        $this->assertArrayHasKey('taille_cm', $errors);
    }

    public function testValidateNutritionGoalsInvalidPoids(): void
    {
        $currentYear = (int) date('Y');

        $data = [
            'annee' => $currentYear,
            'sexe' => 'H',
            'age' => 30,
            'taille_cm' => 175,
            'poids_kg' => 350,
            'activite' => 'modere'
        ];

        $errors = $this->validationService->validateNutritionGoals($data);

        $this->assertArrayHasKey('poids_kg', $errors);
    }

    public function testValidateNutritionGoalsInvalidActivite(): void
    {
        $currentYear = (int) date('Y');

        $data = [
            'annee' => $currentYear,
            'sexe' => 'H',
            'age' => 30,
            'taille_cm' => 175,
            'poids_kg' => 70.5,
            'activite' => 'invalid'
        ];

        $errors = $this->validationService->validateNutritionGoals($data);

        $this->assertArrayHasKey('activite', $errors);
    }

    // Tests pour validateField

    public function testValidateFieldValid(): void
    {
        if (!class_exists(v::class)) {
            $this->markTestSkipped('Respect\Validation not installed');
        }

    // Skip this test if PHP 8.5+ due to deprecation in respect/validation library
    if (PHP_VERSION_ID >= 80500 && class_exists(\Respect\Validation\Validator::class)) {
        $this->markTestSkipped(
            'Skipped due to respect/validation ReflectionProperty::setAccessible() deprecation on PHP 8.5+'
        );
    }

        $rule = fn() => v::stringType()->notEmpty();

        $result = $this->validationService->validateField('test', $rule);

        $this->assertNull($result);
    }

    public function testValidateFieldInvalid(): void
    {
        if (!class_exists(v::class)) {
            $this->markTestSkipped('Respect\Validation not installed');
        }

        // Skip this test if PHP 8.5+ due to deprecation in respect/validation library
        if (PHP_VERSION_ID >= 80500) {
            $this->markTestSkipped('Skipped due to respect/validation deprecation in PHP 8.5');
        }

        $rule = fn() => v::stringType()->notEmpty();

        $result = $this->validationService->validateField('', $rule);

        $this->assertNotNull($result);
        $this->assertIsString($result);
    }
}