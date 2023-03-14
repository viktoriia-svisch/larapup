<?php
use PHPUnit\Framework\TestCase;
class DependencyFailureTest extends TestCase
{
    public function testOne(): void
    {
        $this->fail();
    }
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
    public function testThree(): void
    {
        $this->assertTrue(true);
    }
    public function testFour(): void
    {
        $this->assertTrue(true);
    }
    public function testHandlesDependsAnnotationForNonexistentTests(): void
    {
        $this->assertTrue(true);
    }
}
