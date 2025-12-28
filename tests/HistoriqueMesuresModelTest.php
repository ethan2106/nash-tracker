<?php

use App\Model\HistoriqueMesuresModel;
use PHPUnit\Framework\TestCase;

class HistoriqueMesuresModelTest extends TestCase
{
    private $model;

    private $pdoMock;

    private $stmtMock;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->model = new HistoriqueMesuresModel($this->pdoMock);
    }

    public function testSaveMesure()
    {
        $userId = 1;
        $poids = 75.5;
        $imc = 24.2;
        $taille = 175.0;
        $date = '2025-12-01';

        // Mock execute pour retourner true
        $this->stmtMock->expects($this->atLeastOnce())
            ->method('execute')
            ->willReturn(true);

        $this->pdoMock->expects($this->atLeastOnce())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $result = $this->model->saveMesure($userId, $poids, $imc, $taille, $date);
        $this->assertTrue($result);
    }

    public function testGetHistorique()
    {
        $userId = 1;

        // Mock fetchAll pour retourner un array
        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                ['poids' => 75.5, 'imc' => 24.2, 'date' => '2025-12-01'],
            ]);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $historique = $this->model->getHistorique($userId);
        $this->assertIsArray($historique);
    }

    public function testMesureExists()
    {
        $userId = 1;
        $date = '2025-12-01';

        // Mock fetchColumn pour retourner 1 (mesure existe)
        $this->stmtMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([$userId, $date])
            ->willReturn(true);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $exists = $this->model->mesureExists($userId, $date);
        $this->assertTrue($exists);
    }

    public function testDeleteMesure()
    {
        $userId = 1;
        $date = '2025-12-01';

        // Mock execute pour retourner true
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([$userId, $date])
            ->willReturn(true);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $result = $this->model->deleteMesure($userId, $date);
        $this->assertTrue($result);
    }
}
