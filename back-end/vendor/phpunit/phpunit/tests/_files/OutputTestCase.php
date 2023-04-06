<?php
use PHPUnit\Framework\TestCase;
class OutputTestCase extends TestCase
{
    public function testExpectOutputStringFooActualFoo(): void
    {
        $this->expectOutputString('foo');
        print 'foo';
    }
    public function testExpectOutputStringFooActualBar(): void
    {
        $this->expectOutputString('foo');
        print 'bar';
    }
    public function testExpectOutputRegexFooActualFoo(): void
    {
        $this->expectOutputRegex('/foo/');
        print 'foo';
    }
    public function testExpectOutputRegexFooActualBar(): void
    {
        $this->expectOutputRegex('/foo/');
        print 'bar';
    }
}
