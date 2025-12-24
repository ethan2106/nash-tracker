<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Helper/view_helpers.php';

class ViewHelpersTest extends TestCase
{
    public function testGetScoreTailwindColorThresholds()
    {
        // < 40 => red
        $this->assertSame('red', getScoreTailwindColor(0));
        $this->assertSame('red', getScoreTailwindColor(39));

        // >= 40 and < 60 => orange
        $this->assertSame('orange', getScoreTailwindColor(40));
        $this->assertSame('orange', getScoreTailwindColor(59));

        // >= 60 and < 80 => blue
        $this->assertSame('blue', getScoreTailwindColor(60));
        $this->assertSame('blue', getScoreTailwindColor(79));

        // >= 80 => green
        $this->assertSame('green', getScoreTailwindColor(80));
        $this->assertSame('green', getScoreTailwindColor(100));
    }
}
