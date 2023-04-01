<?php
namespace test\Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class MockingStaticMethodsCalledObjectStyleTest extends MockeryTestCase
{
    public function testStaticMethodCalledObjectStyleMock()
    {
        $mock = mock('test\Mockery\ClassWithStaticMethods');
        $mock->shouldReceive('foo')->andReturn(true);
        $this->assertEquals(true, $mock->foo());
    }
    public function testStaticMethodCalledObjectStyleMockWithNotAllowingMockingOfNonExistentMethods()
    {
        \Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        $mock = mock('test\Mockery\ClassWithStaticMethods');
        $mock->shouldReceive('foo')->andReturn(true);
        $this->assertEquals(true, $mock->foo());
        \Mockery::getConfiguration()->allowMockingNonExistentMethods(true);
    }
    public function testProtectedStaticMethodCalledObjectStyleMockWithNotAllowingMockingOfNonExistentMethods()
    {
        \Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        $mock = mock('test\Mockery\ClassWithStaticMethods');
        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('protectedBar')->andReturn(true);
        $this->assertEquals(true, $mock->protectedBar());
        \Mockery::getConfiguration()->allowMockingNonExistentMethods(true);
    }
}
class ClassWithStaticMethods
{
    public static function foo()
    {
        return 'foo';
    }
    protected static function protectedBar()
    {
        return 'bar';
    }
}
