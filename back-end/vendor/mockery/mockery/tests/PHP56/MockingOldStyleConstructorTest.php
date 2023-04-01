<?php
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
class MockingOldStyleConstructorTest extends MockeryTestCase
{
    public function testCanMockClassWithOldStyleConstructorAndArguments()
    {
        $this->assertInstanceOf(MockInterface::class, mock('MockeryTest_OldStyleConstructor'));
    }
}
class MockeryTest_OldStyleConstructor
{
    public function MockeryTest_OldStyleConstructor($arg)
    {
    }
}
