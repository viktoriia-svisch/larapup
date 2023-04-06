<?php
use PHPUnit\Framework\TestCase;
class DataProviderIncompleteTest extends TestCase
{
    public static function providerMethod()
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
        ];
    }
    public function testIncomplete($a, $b, $c): void
    {
        $this->assertTrue(true);
    }
    public function testAdd($a, $b, $c): void
    {
        $this->assertEquals($c, $a + $b);
    }
    public function incompleteTestProviderMethod()
    {
        $this->markTestIncomplete('incomplete');
        return [
            [0, 0, 0],
            [0, 1, 1],
        ];
    }
}
