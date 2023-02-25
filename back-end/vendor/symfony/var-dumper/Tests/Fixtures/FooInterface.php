<?php
namespace Symfony\Component\VarDumper\Tests\Fixtures;
interface FooInterface
{
    public function foo(?\stdClass $a, \stdClass $b = null);
}
