<?php
use PHPUnit\Framework\TestCase;
class ThrowExceptionTestCase extends TestCase
{
    public function test(): void
    {
        throw new RuntimeException('A runtime error occurred');
    }
}
