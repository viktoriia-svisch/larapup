<?php
use PHPUnit\Framework\TestCase;
class WasRun extends TestCase
{
    public $wasRun = false;
    protected function runTest(): void
    {
        $this->wasRun = true;
    }
}
