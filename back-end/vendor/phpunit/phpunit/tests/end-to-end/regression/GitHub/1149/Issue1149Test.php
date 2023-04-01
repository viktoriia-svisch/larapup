<?php
use PHPUnit\Framework\TestCase;
class Issue1149Test extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
        print '1';
    }
    public function testTwo(): void
    {
        $this->assertTrue(true);
        print '2';
    }
}
