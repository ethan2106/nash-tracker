<?php

use App\Service\CacheService;
use App\Service\ImcDataService;
use PHPUnit\Framework\TestCase;

class ImcDataServiceTest extends TestCase
{
    private $cacheMock;

    private $imcDataService;

    protected function setUp(): void
    {
        $this->cacheMock = $this->createMock(CacheService::class);
        $this->imcDataService = new ImcDataService($this->cacheMock);
    }

    /**
     * Empêche la régression : objectifs en cache non utilisés si présents.
     */
    public function testGetImcDataWithUserAndCachedObjectives()
    {
        $userId = 1;
        $cachedData = ['taille' => 170, 'poids' => 70, 'objectif' => 'perte'];
        $expected = array_merge($cachedData, \App\Model\ImcModel::calculate($cachedData));

        $this->cacheMock->expects($this->once())
            ->method('get')
            ->with('imc', 'objectifs_' . $userId)
            ->willReturn($cachedData);

        $result = $this->imcDataService->getImcData($userId);

        $this->assertEquals($expected, $result);
    }

    /**
     * Empêche la régression : données POST prioritaires sur données sauvegardées.
     */
    public function testGetImcDataWithPostDataPrioritizesSubmitted()
    {
        $userId = 1;
        $savedData = ['taille' => 170, 'poids' => 70, 'objectif' => 'perte'];
        $postData = ['taille' => 175, 'poids' => 75];
        $expected = array_merge($savedData, \App\Model\ImcModel::calculate($postData));

        $this->cacheMock->expects($this->once())
            ->method('get')
            ->with('imc', 'objectifs_' . $userId)
            ->willReturn($savedData);

        $result = $this->imcDataService->getImcData($userId, $postData);

        $this->assertEquals($expected, $result);
        $this->assertEquals(175, $result['taille']); // POST prioritaire
    }

    /**
     * Empêche la régression : sans utilisateur, calcul avec données vides.
     */
    public function testGetImcDataWithoutUser()
    {
        $expected = \App\Model\ImcModel::calculate([]);

        $this->cacheMock->expects($this->never())
            ->method('get');

        $result = $this->imcDataService->getImcData(null);

        $this->assertEquals($expected, $result);
    }
}
