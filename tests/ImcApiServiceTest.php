<?php

use App\Service\ImcApiService;
use PHPUnit\Framework\TestCase;

class ImcApiServiceTest extends TestCase
{
    private $imcApiService;

    protected function setUp(): void
    {
        $this->imcApiService = new ImcApiService();
    }

    /**
     * Empêche la régression : structure du graphique incorrecte (clés, types, valeurs).
     */
    public function testGetChartDataReturnsCorrectStructureAndValues()
    {
        $imcData = [
            'imc' => 25.5,
            'bmr' => 1800,
            'tdee' => 2200,
            'calories_perte' => 1700,
            'calories_maintien' => 2000,
            'calories_masse' => 2500,
        ];

        $result = $this->imcApiService->getChartData($imcData);

        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('backgroundColor', $result);

        $this->assertEquals(['IMC', 'BMR', 'TDEE', 'Perte', 'Maintien', 'Masse'], $result['labels']);
        $this->assertCount(6, $result['data']);
        $this->assertCount(6, $result['backgroundColor']);

        // Vérifier types float et valeurs arrondies
        $this->assertSame(25.5, $result['data'][0]); // IMC float
        $this->assertSame(1800.0, $result['data'][1]); // BMR float
        $this->assertSame(2200.0, $result['data'][2]);
        $this->assertSame(1700.0, $result['data'][3]);
        $this->assertSame(2000.0, $result['data'][4]);
        $this->assertSame(2500.0, $result['data'][5]);
    }

    /**
     * Empêche la régression : graphique ne reflète pas les changements de données source.
     */
    public function testChartDataChangesWhenSourceDataChanges()
    {
        $imcData1 = ['imc' => 20, 'bmr' => 1500, 'tdee' => 1800, 'calories_perte' => 1300, 'calories_maintien' => 1600, 'calories_masse' => 2000];
        $imcData2 = ['imc' => 30, 'bmr' => 2000, 'tdee' => 2400, 'calories_perte' => 1900, 'calories_maintien' => 2200, 'calories_masse' => 2800];

        $chart1 = $this->imcApiService->getChartData($imcData1);
        $chart2 = $this->imcApiService->getChartData($imcData2);

        $this->assertNotEquals($chart1['data'], $chart2['data']);
        $this->assertEquals(20.0, $chart1['data'][0]);
        $this->assertEquals(30.0, $chart2['data'][0]);
    }
}
