<?php
use PHPUnit\Framework\TestCase;
class Issue433Test extends TestCase
{
    public function testOutputWithExpectationBefore(): void
    {
        $this->expectOutputString('test');
        print 'test';
    }
    public function testOutputWithExpectationAfter(): void
    {
        print 'test';
        $this->expectOutputString('test');
    }
    public function testNotMatchingOutput(): void
    {
        print 'bar';
        $this->expectOutputString('foo');
    }
}
