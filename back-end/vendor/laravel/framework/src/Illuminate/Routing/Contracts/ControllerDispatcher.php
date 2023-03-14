<?php
namespace Illuminate\Routing\Contracts;
use Illuminate\Routing\Route;
interface ControllerDispatcher
{
    public function dispatch(Route $route, $controller, $method);
    public function getMiddleware($controller, $method);
}
