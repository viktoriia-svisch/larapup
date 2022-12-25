<?php
namespace test\Mockery\Fixtures;
class MethodWithIterableTypeHints
{
    public function foo(iterable $bar): iterable
    {
    }
}
