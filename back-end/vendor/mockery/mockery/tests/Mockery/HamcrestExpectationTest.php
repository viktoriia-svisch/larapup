<?php
use Mockery\Adapter\Phpunit\MockeryTestCase;
class HamcrestExpectationTest extends MockeryTestCase
{
    public function mockeryTestSetUp()
    {
        parent::mockeryTestSetUp();
        $this->mock = mock('foo');
    }
    public function mockeryTestTearDown()
    {
        \Mockery::getConfiguration()->allowMockingNonExistentMethods(true);
        parent::mockeryTestTearDown();
    }
    public function testAnythingConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(anything())->once();
        $this->mock->foo(2);
    }
    public function testGreaterThanConstraintMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(greaterThan(1))->once();
        $this->mock->foo(2);
    }
    public function testGreaterThanConstraintNotMatchesArgument()
    {
        $this->mock->shouldReceive('foo')->with(greaterThan(1));
        $this->expectException(\Mockery\Exception::class);
        $this->mock->foo(1);
        Mockery::close();
    }
}
