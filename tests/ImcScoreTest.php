<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Helper/view_helpers.php';

class ImcScoreTest extends TestCase
{
    public function testImcScorePointsBoundaries()
    {
        // <= 25 => 25 points
        $this->assertSame(25, getIMCScorePoints(18.0));
        $this->assertSame(25, getIMCScorePoints(25.0));

        // Between 25 and 35: 25 - (imc-25)*1.5, rounded, min 1
        // IMC 30: 25 - 7.5 = 17.5 -> 18
        $this->assertSame(18, getIMCScorePoints(30.0));
        // IMC 35: 25 - 15 = 10
        $this->assertSame(10, getIMCScorePoints(35.0));

        // > 35 => floor to 1
        $this->assertSame(1, getIMCScorePoints(50.0));
    }

    public function testImcAdviceExists()
    {
        $this->assertIsString(getIMCAdvice(18.0));
        $this->assertIsString(getIMCAdvice(28.0));
        $this->assertIsString(getIMCAdvice(36.0));
    }
}
