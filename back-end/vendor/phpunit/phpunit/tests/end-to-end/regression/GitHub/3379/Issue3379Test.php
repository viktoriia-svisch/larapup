<?php declare(strict_types=1);
namespace Test;
use PHPUnit\Framework\TestCase;
class Issue3379Test extends TestCase
{
    public function testOne(): void
    {
        $this->markTestSkipped();
    }
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
