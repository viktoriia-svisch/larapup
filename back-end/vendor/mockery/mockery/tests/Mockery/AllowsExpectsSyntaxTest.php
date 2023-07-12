<?php
namespace test\Mockery;
use Mockery as m;
use Mockery\Spy;
use Mockery\Exception\InvalidCountException;
use PHPUnit\Framework\TestCase;
class AllowsExpectsSyntaxTest extends TestCase
{
    public function allowsSetsUpMethodStub()
    {
        $stub = m::mock();
        $stub->allows()->foo(123)->andReturns(456);
        $this->assertEquals(456, $stub->foo(123));
    }
    public function allowsCanTakeAnArrayOfCalls()
    {
        $stub = m::mock();
        $stub->allows([
            "foo" => "bar",
            "bar" => "baz",
        ]);
        $this->assertEquals("bar", $stub->foo());
        $this->assertEquals("baz", $stub->bar());
    }
    public function allowsCanTakeAString()
    {
        $stub = m::mock();
        $stub->allows("foo")->andReturns("bar");
        $this->assertEquals("bar", $stub->foo());
    }
    public function expects_can_optionally_match_on_any_arguments()
    {
        $mock = m::mock();
        $mock->allows()->foo()->withAnyArgs()->andReturns(123);
        $this->assertEquals(123, $mock->foo(456, 789));
    }
    public function expects_can_take_a_string()
    {
        $mock = m::mock();
        $mock->expects("foo")->andReturns(123);
        $this->assertEquals(123, $mock->foo(456, 789));
    }
    public function expectsSetsUpExpectationOfOneCall()
    {
        $mock = m::mock();
        $mock->expects()->foo(123);
        $this->expectException("Mockery\Exception\InvalidCountException");
        m::close();
    }
    public function callVerificationCountCanBeOverridenAfterExpectsThrowsExceptionWhenIncorrectNumberOfCalls()
    {
        $mock = m::mock();
        $mock->expects()->foo(123)->twice();
        $mock->foo(123);
        $this->expectException("Mockery\Exception\InvalidCountException");
        m::close();
    }
    public function callVerificationCountCanBeOverridenAfterExpects()
    {
        $mock = m::mock();
        $mock->expects()->foo(123)->twice();
        $mock->foo(123);
        $mock->foo(123);
        m::close();
    }
}
