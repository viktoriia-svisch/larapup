<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;
class TestWarning extends TestCase
{
    protected function runTest(): void
    {
        throw new Warning;
    }
}
