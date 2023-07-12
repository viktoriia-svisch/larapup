<?php
namespace Illuminate\Routing;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class RouteBinding
{
    public static function forCallback($container, $binder)
    {
        if (is_string($binder)) {
            return static::createClassBinding($container, $binder);
        }
        return $binder;
    }
    protected static function createClassBinding($container, $binding)
    {
        return function ($value, $route) use ($container, $binding) {
            [$class, $method] = Str::parseCallback($binding, 'bind');
            $callable = [$container->make($class), $method];
            return call_user_func($callable, $value, $route);
        };
    }
    public static function forModel($container, $class, $callback = null)
    {
        return function ($value) use ($container, $class, $callback) {
            if (is_null($value)) {
                return;
            }
            $instance = $container->make($class);
            if ($model = $instance->resolveRouteBinding($value)) {
                return $model;
            }
            if ($callback instanceof Closure) {
                return call_user_func($callback, $value);
            }
            throw (new ModelNotFoundException)->setModel($class);
        };
    }
}
