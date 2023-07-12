<?php
use PHPUnit\Framework\TestCase;
class CoverageNamespacedFunctionTest extends TestCase
{
    public function testFunc(): void
    {
        foo\func();
    }
}
