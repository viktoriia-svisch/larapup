<?php
namespace test\Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class MockClassWithFinalWakeupTest extends MockeryTestCase
{
    protected function mockeryTestSetUp()
    {
        $this->container = new \Mockery\Container;
    }
    protected function mockeryTestTearDown()
    {
        $this->container->mockery_close();
    }
    public function testCreateMockForClassWithFinalWakeup()
    {
        $mock = $this->container->mock("test\Mockery\TestWithFinalWakeup");
        $this->assertInstanceOf("test\Mockery\TestWithFinalWakeup", $mock);
        $this->assertEquals('test\Mockery\TestWithFinalWakeup::__wakeup', $mock->__wakeup());
        $mock = $this->container->mock('test\Mockery\SubclassWithFinalWakeup');
        $this->assertInstanceOf('test\Mockery\SubclassWithFinalWakeup', $mock);
        $this->assertEquals('test\Mockery\TestWithFinalWakeup::__wakeup', $mock->__wakeup());
    }
    public function testCreateMockForClassWithNonFinalWakeup()
    {
        $mock = $this->container->mock('test\Mockery\TestWithNonFinalWakeup');
        $this->assertInstanceOf('test\Mockery\TestWithNonFinalWakeup', $mock);
        $this->assertNull($mock->__wakeup());
    }
}
class TestWithFinalWakeup
{
    public function foo()
    {
        return 'foo';
    }
    public function bar()
    {
        return 'bar';
    }
    final public function __wakeup()
    {
        return __METHOD__;
    }
}
class SubclassWithFinalWakeup extends TestWithFinalWakeup
{
}
class TestWithNonFinalWakeup
{
    public function __wakeup()
    {
        return __METHOD__;
    }
}
