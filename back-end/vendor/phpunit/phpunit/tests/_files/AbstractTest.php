<?php
use PHPUnit\Framework\TestCase;
abstract class AbstractTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
