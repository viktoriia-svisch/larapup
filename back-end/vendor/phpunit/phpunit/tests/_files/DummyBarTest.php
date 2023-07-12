<?php
use PHPUnit\Framework\TestCase;
class DummyBarTest extends TestCase
{
    public function testBarEqualsBar(): void
    {
        $this->assertEquals('Bar', 'Bar');
    }
}
