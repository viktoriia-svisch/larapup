<?php
namespace test\Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class MockingMethodsWithNullableParametersTest extends MockeryTestCase
{
    public function it_can_handle_nullable_typed_parameters()
    {
        require __DIR__."/Fixtures/MethodWithNullableTypedParameter.php";
        $mock = mock("test\Mockery\Fixtures\MethodWithNullableTypedParameter");
        $this->assertInstanceOf(\test\Mockery\Fixtures\MethodWithNullableTypedParameter::class, $mock);
    }
    public function it_can_handle_default_parameters()
    {
        require __DIR__."/Fixtures/MethodWithParametersWithDefaultValues.php";
        $mock = mock("test\Mockery\Fixtures\MethodWithParametersWithDefaultValues");
        $this->assertInstanceOf(\test\Mockery\Fixtures\MethodWithParametersWithDefaultValues::class, $mock);
    }
}
