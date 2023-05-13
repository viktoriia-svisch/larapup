<?php
namespace test\Mockery\Fixtures;
class MethodWithParametersWithDefaultValues
{
    public function foo($bar = null)
    {
    }
    public function bar(string $bar = null)
    {
    }
}
