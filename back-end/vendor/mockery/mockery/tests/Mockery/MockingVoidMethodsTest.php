<?php
namespace test\Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class MockingVoidMethodsTest extends MockeryTestCase
{
    protected function mockeryTestSetUp()
    {
        require_once __DIR__."/Fixtures/MethodWithVoidReturnType.php";
    }
    public function itShouldSuccessfullyBuildTheMock()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithVoidReturnType");
        $this->assertInstanceOf(\test\Mockery\Fixtures\MethodWithVoidReturnType::class, $mock);
    }
    public function it_can_stub_and_mock_void_methods()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithVoidReturnType");
        $mock->shouldReceive("foo");
        $mock->foo();
    }
}
