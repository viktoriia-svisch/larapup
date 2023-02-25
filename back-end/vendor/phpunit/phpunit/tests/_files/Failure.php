<?php
use PHPUnit\Framework\TestCase;
class Failure extends TestCase
{
    protected function runTest(): void
    {
        $this->fail();
    }
}
