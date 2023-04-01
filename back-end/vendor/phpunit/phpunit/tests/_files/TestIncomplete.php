<?php
use PHPUnit\Framework\TestCase;
class TestIncomplete extends TestCase
{
    protected function runTest(): void
    {
        $this->markTestIncomplete('Incomplete test');
    }
}
