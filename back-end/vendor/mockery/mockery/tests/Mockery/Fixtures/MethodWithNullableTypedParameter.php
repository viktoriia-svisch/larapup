<?php
namespace test\Mockery\Fixtures;
class MethodWithNullableTypedParameter
{
    public function foo(?string $bar)
    {
    }
    public function bar(string $bar = null)
    {
    }
    public function baz(?string $bar = null)
    {
    }
}
