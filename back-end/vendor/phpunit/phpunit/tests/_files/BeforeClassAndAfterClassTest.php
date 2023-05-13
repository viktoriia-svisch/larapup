<?php
use PHPUnit\Framework\TestCase;
class BeforeClassAndAfterClassTest extends TestCase
{
    public static $beforeClassWasRun = 0;
    public static $afterClassWasRun  = 0;
    public static function resetProperties(): void
    {
        self::$beforeClassWasRun = 0;
        self::$afterClassWasRun  = 0;
    }
    public static function initialClassSetup(): void
    {
        self::$beforeClassWasRun++;
    }
    public static function finalClassTeardown(): void
    {
        self::$afterClassWasRun++;
    }
    public function test1(): void
    {
    }
    public function test2(): void
    {
    }
}
