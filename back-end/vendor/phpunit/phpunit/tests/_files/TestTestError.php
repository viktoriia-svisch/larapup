<?php
use PHPUnit\Framework\TestCase;
class TestError extends TestCase
{
    protected function runTest(): void
    {
        throw new Exception;
    }
}
