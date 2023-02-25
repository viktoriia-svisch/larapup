<?php
namespace test\Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Generator\Method;
use test\Mockery\Fixtures\MethodWithNullableReturnType;
class MockingNullableMethodsTest extends MockeryTestCase
{
    private $container;
    protected function mockeryTestSetUp()
    {
        parent::mockeryTestSetUp();
        require_once __DIR__."/Fixtures/MethodWithNullableReturnType.php";
    }
    public function itShouldAllowNonNullableTypeToBeSet()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithNullableReturnType");
        $mock->shouldReceive('nonNullablePrimitive')->andReturn('a string');
        $mock->nonNullablePrimitive();
    }
    public function itShouldNotAllowNonNullToBeNull()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithNullableReturnType");
        $mock->shouldReceive('nonNullablePrimitive')->andReturn(null);
        $this->expectException(\TypeError::class);
        $mock->nonNullablePrimitive();
    }
    public function itShouldAllowPrimitiveNullableToBeNull()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithNullableReturnType");
        $mock->shouldReceive('nullablePrimitive')->andReturn(null);
        $mock->nullablePrimitive();
    }
    public function itShouldAllowPrimitiveNullableToBeSet()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithNullableReturnType");
        $mock->shouldReceive('nullablePrimitive')->andReturn('a string');
        $mock->nullablePrimitive();
    }
    public function itShouldAllowSelfToBeSet()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithNullableReturnType");
        $mock->shouldReceive('nonNullableSelf')->andReturn(new MethodWithNullableReturnType());
        $mock->nonNullableSelf();
    }
    public function itShouldNotAllowSelfToBeNull()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithNullableReturnType");
        $mock->shouldReceive('nonNullableSelf')->andReturn(null);
        $this->expectException(\TypeError::class);
        $mock->nonNullableSelf();
    }
    public function itShouldAllowNullableSelfToBeSet()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithNullableReturnType");
        $mock->shouldReceive('nullableSelf')->andReturn(new MethodWithNullableReturnType());
        $mock->nullableSelf();
    }
    public function itShouldAllowNullableSelfToBeNull()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithNullableReturnType");
        $mock->shouldReceive('nullableSelf')->andReturn(null);
        $mock->nullableSelf();
    }
    public function itShouldAllowClassToBeSet()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithNullableReturnType");
        $mock->shouldReceive('nonNullableClass')->andReturn(new MethodWithNullableReturnType());
        $mock->nonNullableClass();
    }
    public function itShouldNotAllowClassToBeNull()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithNullableReturnType");
        $mock->shouldReceive('nonNullableClass')->andReturn(null);
        $this->expectException(\TypeError::class);
        $mock->nonNullableClass();
    }
    public function itShouldAllowNullalbeClassToBeSet()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithNullableReturnType");
        $mock->shouldReceive('nullableClass')->andReturn(new MethodWithNullableReturnType());
        $mock->nullableClass();
    }
    public function itShouldAllowNullableClassToBeNull()
    {
        $mock = mock("test\Mockery\Fixtures\MethodWithNullableReturnType");
        $mock->shouldReceive('nullableClass')->andReturn(null);
        $mock->nullableClass();
    }
    public function it_allows_returning_null_for_nullable_object_return_types()
    {
        $double= \Mockery::mock(MethodWithNullableReturnType::class);
        $double->shouldReceive("nullableClass")->andReturnNull();
        $this->assertNull($double->nullableClass());
    }
    public function it_allows_returning_null_for_nullable_string_return_types()
    {
        $double= \Mockery::mock(MethodWithNullableReturnType::class);
        $double->shouldReceive("nullableString")->andReturnNull();
        $this->assertNull($double->nullableString());
    }
    public function it_allows_returning_null_for_nullable_int_return_types()
    {
        $double= \Mockery::mock(MethodWithNullableReturnType::class);
        $double->shouldReceive("nullableInt")->andReturnNull();
        $this->assertNull($double->nullableInt());
    }
    public function it_returns_null_on_calls_to_ignored_methods_of_spies_if_return_type_is_nullable()
    {
        $double = \Mockery::spy(MethodWithNullableReturnType::class);
        $this->assertNull($double->nullableClass());
    }
}
