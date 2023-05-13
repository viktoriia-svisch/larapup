<?php
use PHPUnit\Framework\TestCase;
class Issue1471Test extends TestCase
{
    public function testFailure(): void
    {
        $this->expectOutputString('*');
        print '*';
        $this->assertTrue(false);
    }
}
