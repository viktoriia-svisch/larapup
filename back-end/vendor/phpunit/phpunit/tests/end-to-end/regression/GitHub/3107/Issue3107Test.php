<?php
namespace Issue3107;
use PHPUnit\Framework\TestCase;
class Issue3107Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        does_not_exist();
    }
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
