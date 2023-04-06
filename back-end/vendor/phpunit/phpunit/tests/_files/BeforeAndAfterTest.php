<?php
use PHPUnit\Framework\TestCase;
class BeforeAndAfterTest extends TestCase
{
    public static $beforeWasRun;
    public static $afterWasRun;
    public static function resetProperties(): void
    {
        self::$beforeWasRun = 0;
        self::$afterWasRun  = 0;
    }
    public function initialSetup(): void
    {
        self::$beforeWasRun++;
    }
    public function finalTeardown(): void
    {
        self::$afterWasRun++;
    }
    public function test1(): void
    {
    }
    public function test2(): void
    {
    }
}
