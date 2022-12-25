<?php
use PHPUnit\Framework\TestCase;
class Issue2591_SeparateFunctionPreserveTest extends TestCase
{
    public function testChangedGlobalString(): void
    {
        $GLOBALS['globalString'] = 'Hello!';
        $this->assertEquals('Hello!', $GLOBALS['globalString']);
    }
    public function testGlobalString(): void
    {
        $this->assertEquals('Hello', $GLOBALS['globalString']);
    }
}
