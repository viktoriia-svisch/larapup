<?php
class Issue2137Test extends PHPUnit\Framework\TestCase
{
    public function testBrandService($provided, $expected): void
    {
        $this->assertSame($provided, $expected);
    }
    public function provideBrandService()
    {
        return [
            new stdClass, 
        ];
    }
    public function testSomethingElseInvalid($provided, $expected): void
    {
        $this->assertSame($provided, $expected);
    }
}
