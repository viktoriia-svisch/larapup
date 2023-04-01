<?php
use PHPUnit\Framework\TestCase;
class Issue765Test extends TestCase
{
    public function testDependee(): void
    {
        $this->assertTrue(true);
    }
    public function testDependent($a): void
    {
        $this->assertTrue(true);
    }
    public function dependentProvider(): void
    {
        throw new Exception;
    }
}
