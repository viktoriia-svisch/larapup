<?php
use PHPUnit\Framework\TestCase;
class ClonedDependencyTest extends TestCase
{
    private static $dependency;
    public static function setUpBeforeClass(): void
    {
        self::$dependency = new stdClass;
    }
    public function testOne()
    {
        $this->assertTrue(true);
        return self::$dependency;
    }
    public function testTwo($dependency): void
    {
        $this->assertSame(self::$dependency, $dependency);
    }
    public function testThree($dependency): void
    {
        $this->assertSame(self::$dependency, $dependency);
    }
    public function testFour($dependency): void
    {
        $this->assertNotSame(self::$dependency, $dependency);
    }
    public function testFive($dependency): void
    {
        $this->assertSame(self::$dependency, $dependency);
    }
    public function testSix($dependency): void
    {
        $this->assertNotSame(self::$dependency, $dependency);
    }
}
