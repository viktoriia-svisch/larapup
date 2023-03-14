<?php
use PHPUnit\Framework\TestCase;
class IsolationTest extends TestCase
{
    public function testIsInIsolationReturnsFalse(): void
    {
        $this->assertFalse($this->isInIsolation());
    }
    public function testIsInIsolationReturnsTrue(): void
    {
        $this->assertTrue($this->isInIsolation());
    }
}
