<?php
namespace test\Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class MockingVariadicArgumentsTest extends MockeryTestCase
{
    public function shouldAllowMockingVariadicArguments()
    {
        $mock = mock("test\Mockery\TestWithVariadicArguments");
        $mock->shouldReceive("foo")->andReturn("notbar");
        $this->assertEquals("notbar", $mock->foo());
    }
}
abstract class TestWithVariadicArguments
{
    public function foo(...$bar)
    {
        return $bar;
    }
}
