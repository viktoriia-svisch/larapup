<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class Issue3364SetupBeforeClassTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        throw new \RuntimeException('throw exception in setUpBeforeClass');
    }
    public function testOneWithClassSetupException(): void
    {
        $this->fail();
    }
    public function testTwoWithClassSetupException(): void
    {
        $this->fail();
    }
}
