<?php
namespace test\Mockery;
use Mockery\MockInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class MockClassWithUnknownTypeHintTest extends MockeryTestCase
{
    public function itShouldSuccessfullyBuildTheMock()
    {
        $mock = mock("test\Mockery\HasUnknownClassAsTypeHintOnMethod");
        $this->assertInstanceOf(MockInterface::class, $mock);
    }
}
class HasUnknownClassAsTypeHintOnMethod
{
    public function foo(\UnknownTestClass\Bar $bar)
    {
    }
}
