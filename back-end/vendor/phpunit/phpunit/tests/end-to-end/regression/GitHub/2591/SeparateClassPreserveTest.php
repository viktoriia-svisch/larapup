<?php
use PHPUnit\Framework\TestCase;
class Issue2591_SeparateClassPreserveTest extends TestCase
{
    public function testOriginalGlobalString(): void
    {
        $this->assertEquals('Hello', $GLOBALS['globalString']);
    }
    public function testChangedGlobalString(): void
    {
        $value = 'Hello! I am changed from inside!';
        $GLOBALS['globalString'] = $value;
        $this->assertEquals($value, $GLOBALS['globalString']);
    }
    public function testGlobalString(): void
    {
        $this->assertEquals('Hello', $GLOBALS['globalString']);
    }
}
