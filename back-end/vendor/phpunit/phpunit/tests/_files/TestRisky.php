<?php
use PHPUnit\Framework\TestCase;
class TestRisky extends TestCase
{
    protected function runTest(): void
    {
        $this->markAsRisky();
    }
}
