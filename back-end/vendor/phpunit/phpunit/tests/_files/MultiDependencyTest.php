<?php
use PHPUnit\Framework\TestCase;
class MultiDependencyTest extends TestCase
{
    public function testOne()
    {
        $this->assertTrue(true);
        return 'foo';
    }
    public function testTwo()
    {
        $this->assertTrue(true);
        return 'bar';
    }
    public function testThree($a, $b): void
    {
        $this->assertEquals('foo', $a);
        $this->assertEquals('bar', $b);
    }
    public function testFour()
    {
        $this->assertTrue(true);
    }
    public function testFive()
    {
        $this->assertTrue(true);
    }
}
