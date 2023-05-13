<?php
namespace test\Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class MockingMethodsWithIterableTypeHintsTest extends MockeryTestCase
{
    public function itShouldSuccessfullyBuildTheMock()
    {
        require __DIR__."/Fixtures/MethodWithIterableTypeHints.php";
        $mock = mock("test\Mockery\Fixtures\MethodWithIterableTypeHints");
        $this->assertInstanceOf(\test\Mockery\Fixtures\MethodWithIterableTypeHints::class, $mock);
    }
}
