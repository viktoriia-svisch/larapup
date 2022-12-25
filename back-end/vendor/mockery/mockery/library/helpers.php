<?php
use Mockery\Matcher\AndAnyOtherArgs;
use Mockery\Matcher\AnyArgs;
if (!function_exists("mock")) {
    function mock(...$args)
    {
        return Mockery::mock(...$args);
    }
}
if (!function_exists("spy")) {
    function spy(...$args)
    {
        return Mockery::spy(...$args);
    }
}
if (!function_exists("namedMock")) {
    function namedMock(...$args)
    {
        return Mockery::namedMock(...$args);
    }
}
if (!function_exists("anyArgs")) {
    function anyArgs()
    {
        return new AnyArgs();
    }
}
if (!function_exists("andAnyOtherArgs")) {
    function andAnyOtherArgs()
    {
        return new AndAnyOtherArgs();
    }
}
if (!function_exists("andAnyOthers")) {
    function andAnyOthers()
    {
        return new AndAnyOtherArgs();
    }
}
