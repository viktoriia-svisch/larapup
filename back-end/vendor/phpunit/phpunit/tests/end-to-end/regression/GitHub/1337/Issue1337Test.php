<?php
use PHPUnit\Framework\TestCase;
class Issue1337Test extends TestCase
{
    public function testProvider($a): void
    {
        $this->assertTrue($a);
    }
    public function dataProvider()
    {
        return [
            'c:\\'=> [true],
            0.9   => [true],
        ];
    }
}
