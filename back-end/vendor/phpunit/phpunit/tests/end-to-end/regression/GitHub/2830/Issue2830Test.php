<?php
class Issue2830Test extends PHPUnit\Framework\TestCase
{
    public function testMethodUsesDataProvider(): void
    {
        $this->assertTrue(true);
    }
    public function simpleDataProvider()
    {
        return [
            ['foo'],
        ];
    }
}
