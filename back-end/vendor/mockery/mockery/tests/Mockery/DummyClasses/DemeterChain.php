<?php
namespace DemeterChain;
class C
{
    public function baz(): \stdClass
    {
        return new \stdClass();
    }
}
class B
{
    public function bar(): C
    {
        return new C();
    }
}
class A
{
    public function foo(): B
    {
        return new B();
    }
}
class Main
{
    public function callDemeter(A $a)
    {
        return $a->foo()->bar()->baz();
    }
}
