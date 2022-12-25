<?php
namespace Illuminate\Routing;
use ReflectionMethod;
use ReflectionFunction;
use Illuminate\Support\Str;
class RouteSignatureParameters
{
    public static function fromAction(array $action, $subClass = null)
    {
        $parameters = is_string($action['uses'])
                        ? static::fromClassMethodString($action['uses'])
                        : (new ReflectionFunction($action['uses']))->getParameters();
        return is_null($subClass) ? $parameters : array_filter($parameters, function ($p) use ($subClass) {
            return $p->getClass() && $p->getClass()->isSubclassOf($subClass);
        });
    }
    protected static function fromClassMethodString($uses)
    {
        [$class, $method] = Str::parseCallback($uses);
        if (! method_exists($class, $method) && is_callable($class, $method)) {
            return [];
        }
        return (new ReflectionMethod($class, $method))->getParameters();
    }
}
