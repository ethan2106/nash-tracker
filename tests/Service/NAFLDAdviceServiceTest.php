<?php

namespace Tests\Service;

use App\Model\UserConfigModel;
use App\Service\NAFLDAdviceService;
use PHPUnit\Framework\TestCase;

class NAFLDAdviceServiceTest extends TestCase
{
    private NAFLDAdviceService $service;

    /** @var \App\Model\UserConfigModel&\PHPUnit\Framework\MockObject\MockObject */
    private UserConfigModel $mockUserConfig;

    protected function setUp(): void
    {
        $this->mockUserConfig = $this->createMock(UserConfigModel::class);
        $this->service = new NAFLDAdviceService($this->mockUserConfig);
    }

    public function testGeneratePersonalizedAdviceWithNoUserIdReturnsEmptyArray()
    {
        $result = $this->service->generatePersonalizedAdvice([], [], [], []);
        $this->assertEquals([], $result);
    }

    public function testGeneratePersonalizedAdviceWithMedicalConditionsIncludesDisclaimer()
    {
        $this->mockUserConfig->method('get')
            ->willReturnCallback(function ($userId, $key)
            {
                if (in_array($key, ['medical_cardiac', 'medical_diabetes', 'medical_other']))
                {
                    return true;
                }

                return 18.5; // default IMC thresholds
            });

        $user = ['id' => 1];
        $objectifs = ['imc' => 25];
        $currentNutrition = [];
        $stats = [];

        $result = $this->service->generatePersonalizedAdvice($user, $objectifs, $currentNutrition, $stats);

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('conditions médicales', $result[0]['text']);
        $this->assertEquals('red', $result[0]['color']);
    }

    public function testGeneratePersonalizedAdviceNormalCaseReturnsAdvice()
    {
        $this->mockUserConfig->method('get')
            ->willReturn(18.5); // normal thresholds

        $user = ['id' => 1, 'date_naissance' => '1990-01-01'];
        $objectifs = ['imc' => 22, 'calories' => 2000, 'proteines' => 150, 'glucides' => 250, 'lipides' => 70];
        $currentNutrition = ['calories' => 1800, 'proteines' => 120, 'glucides' => 200, 'lipides' => 60];
        $stats = ['activity_today' => 30];

        $result = $this->service->generatePersonalizedAdvice($user, $objectifs, $currentNutrition, $stats);

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(5, count($result)); // max 5 advice
        foreach ($result as $advice)
        {
            $this->assertArrayHasKey('text', $advice);
            $this->assertArrayHasKey('icon', $advice);
            $this->assertArrayHasKey('color', $advice);
            $this->assertArrayHasKey('priority', $advice);
        }
    }

    public function testGeneratePersonalizedAdviceWithHighIMC()
    {
        $this->mockUserConfig->method('get')
            ->willReturn(18.5);

        $user = ['id' => 1];
        $objectifs = ['imc' => 30]; // obese
        $currentNutrition = [];
        $stats = [];

        $result = $this->service->generatePersonalizedAdvice($user, $objectifs, $currentNutrition, $stats);

        $this->assertNotEmpty($result);
        // Should contain advice about obesity
        $texts = array_column($result, 'text');
        $this->assertTrue(count(array_filter($texts, fn ($t) => stripos($t, 'obésité') !== false)) > 0);
    }

    public function testGeneratePersonalizedAdviceWithLowActivity()
    {
        $this->mockUserConfig->method('get')
            ->willReturn(18.5);

        $user = ['id' => 1];
        $objectifs = ['imc' => 22];
        $currentNutrition = [];
        $stats = ['last_activity_days' => 3]; // no recent activity

        $result = $this->service->generatePersonalizedAdvice($user, $objectifs, $currentNutrition, $stats);

        $this->assertNotEmpty($result);
        // Should contain advice about activity
        $texts = array_column($result, 'text');
        $this->assertTrue(count(array_filter($texts, fn ($t) => stripos($t, 'activité') !== false || stripos($t, 'marche') !== false)) > 0);
    }
}
