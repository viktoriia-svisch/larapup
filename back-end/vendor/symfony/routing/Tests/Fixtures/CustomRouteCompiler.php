<?php
namespace Symfony\Component\Routing\Tests\Fixtures;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCompiler;
class CustomRouteCompiler extends RouteCompiler
{
    public static function compile(Route $route)
    {
        return new CustomCompiledRoute('', '', [], []);
    }
}
