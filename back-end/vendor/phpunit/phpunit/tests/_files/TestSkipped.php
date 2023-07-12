<?php
use PHPUnit\Framework\TestCase;
class TestSkipped extends TestCase
{
    protected function runTest(): void
    {
        $this->markTestSkipped('Skipped test');
    }
}
