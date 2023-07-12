<?php
use PHPUnit\Framework\TestCase;
class Issue1468Test extends TestCase
{
    public function testFailure(): void
    {
        $this->markTestIncomplete();
    }
}
