<?php
use PHPUnit\Framework\TestCase;
class DataProviderSkippedTest extends TestCase
{
    public static function providerMethod()
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
        ];
    }
    public function testSkipped($a, $b, $c): void
    {
        $this->assertTrue(true);
    }
    public function testAdd($a, $b, $c): void
    {
        $this->assertEquals($c, $a + $b);
    }
    public function skippedTestProviderMethod()
    {
        $this->markTestSkipped('skipped');
        return [
            [0, 0, 0],
            [0, 1, 1],
        ];
    }
}
