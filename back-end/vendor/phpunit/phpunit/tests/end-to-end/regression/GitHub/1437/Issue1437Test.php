<?php
use PHPUnit\Framework\TestCase;
class Issue1437Test extends TestCase
{
    public function testFailure(): void
    {
        \ob_start();
        $this->assertTrue(false);
    }
}
