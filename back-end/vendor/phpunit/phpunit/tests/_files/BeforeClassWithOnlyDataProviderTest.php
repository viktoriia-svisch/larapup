<?php
class BeforeClassWithOnlyDataProviderTest extends \PHPUnit\Framework\TestCase
{
    public static $setUpBeforeClassWasCalled;
    public static $beforeClassWasCalled;
    public static function resetProperties(): void
    {
        self::$setUpBeforeClassWasCalled = false;
        self::$beforeClassWasCalled      = false;
    }
    public static function someAnnotatedSetupMethod(): void
    {
        self::$beforeClassWasCalled = true;
    }
    public static function setUpBeforeClass(): void
    {
        self::$setUpBeforeClassWasCalled = true;
    }
    public function dummyProvider()
    {
        return [[1]];
    }
    public function testDummy(): void
    {
        $this->assertFalse(false);
    }
}
