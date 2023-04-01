<?php
class Mockable
{
    public $constructorArgs;
    public $cloned;
    public function __construct($arg1 = null, $arg2 = null)
    {
        $this->constructorArgs = [$arg1, $arg2];
    }
    public function __clone()
    {
        $this->cloned = true;
    }
    public function mockableMethod()
    {
        return true;
    }
    public function anotherMockableMethod()
    {
        return true;
    }
}
