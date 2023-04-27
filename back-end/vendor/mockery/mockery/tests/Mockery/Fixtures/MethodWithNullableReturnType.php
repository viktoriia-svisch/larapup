<?php
namespace test\Mockery\Fixtures;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class MethodWithNullableReturnType extends MockeryTestCase
{
    public function nonNullablePrimitive() : string
    {
        return 'test';
    }
    public function nullablePrimitive() : ?string
    {
        return null;
    }
    public function nonNullableSelf() : self
    {
        return $this;
    }
    public function nullableSelf() : ?self
    {
        return null;
    }
    public function nonNullableClass() : MethodWithNullableReturnType
    {
        return $this;
    }
    public function nullableClass() : ?MethodWithNullableReturnType
    {
        return null;
    }
    public function nullableInt() : ?int
    {
        return null;
    }
    public function nullableString() : ?string
    {
        return null;
    }
}
