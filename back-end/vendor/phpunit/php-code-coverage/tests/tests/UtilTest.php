<?php
namespace SebastianBergmann\CodeCoverage;
use PHPUnit\Framework\TestCase;
class UtilTest extends TestCase
{
    public function testPercent()
    {
        $this->assertEquals(100, Util::percent(100, 0));
        $this->assertEquals(100, Util::percent(100, 100));
        $this->assertEquals(
            '100.00%',
            Util::percent(100, 100, true)
        );
    }
}
