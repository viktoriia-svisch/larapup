<?php
use PHPUnit\Framework\TestCase;
class ExceptionInTearDownAfterClassTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        throw new Exception('throw Exception in tearDownAfterClass()');
    }
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
