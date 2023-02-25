<?php
use PHPUnit\Framework\TestCase;
class TestWithTest extends TestCase
{
    public static function providerMethod()
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
            [1, 1, 3],
            [1, 0, 1],
        ];
    }
    public function testAdd($a, $b, $c): void
    {
        $this->assertEquals($c, $a + $b);
    }
}
