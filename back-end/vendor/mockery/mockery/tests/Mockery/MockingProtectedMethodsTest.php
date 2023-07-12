<?php
namespace test\Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class MockingProtectedMethodsTest extends MockeryTestCase
{
    public function shouldAutomaticallyDeferCallsToProtectedMethodsForPartials()
    {
        $mock = mock("test\Mockery\TestWithProtectedMethods[foo]");
        $this->assertEquals("bar", $mock->bar());
    }
    public function shouldAutomaticallyDeferCallsToProtectedMethodsForRuntimePartials()
    {
        $mock = mock("test\Mockery\TestWithProtectedMethods")->makePartial();
        $this->assertEquals("bar", $mock->bar());
    }
    public function shouldAutomaticallyIgnoreAbstractProtectedMethods()
    {
        $mock = mock("test\Mockery\TestWithProtectedMethods")->makePartial();
        $this->assertNull($mock->foo());
    }
    public function shouldAllowMockingProtectedMethods()
    {
        $mock = mock("test\Mockery\TestWithProtectedMethods")
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive("protectedBar")->andReturn("notbar");
        $this->assertEquals("notbar", $mock->bar());
    }
    public function shouldAllowMockingProtectedMethodOnDefinitionTimePartial()
    {
        $mock = mock("test\Mockery\TestWithProtectedMethods[protectedBar]")
            ->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive("protectedBar")->andReturn("notbar");
        $this->assertEquals("notbar", $mock->bar());
    }
    public function shouldAllowMockingAbstractProtectedMethods()
    {
        $mock = mock("test\Mockery\TestWithProtectedMethods")
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive("abstractProtected")->andReturn("abstractProtected");
        $this->assertEquals("abstractProtected", $mock->foo());
    }
    public function shouldAllowMockingIncreasedVisabilityMethods()
    {
        $mock = mock("test\Mockery\TestIncreasedVisibilityChild");
        $mock->shouldReceive('foobar')->andReturn("foobar");
        $this->assertEquals('foobar', $mock->foobar());
    }
}
abstract class TestWithProtectedMethods
{
    public function foo()
    {
        return $this->abstractProtected();
    }
    abstract protected function abstractProtected();
    public function bar()
    {
        return $this->protectedBar();
    }
    protected function protectedBar()
    {
        return 'bar';
    }
}
class TestIncreasedVisibilityParent
{
    protected function foobar()
    {
    }
}
class TestIncreasedVisibilityChild extends TestIncreasedVisibilityParent
{
    public function foobar()
    {
    }
}
