<?php
class ClassWithVariadicArgumentMethod
{
    public function foo(...$args)
    {
        return $args;
    }
}
