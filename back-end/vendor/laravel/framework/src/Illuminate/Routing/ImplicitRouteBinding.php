<?php
namespace Illuminate\Routing;
use Illuminate\Support\Str;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class ImplicitRouteBinding
{
    public static function resolveForRoute($container, $route)
    {
        $parameters = $route->parameters();
        foreach ($route->signatureParameters(UrlRoutable::class) as $parameter) {
            if (! $parameterName = static::getParameterName($parameter->name, $parameters)) {
                continue;
            }
            $parameterValue = $parameters[$parameterName];
            if ($parameterValue instanceof UrlRoutable) {
                continue;
            }
            $instance = $container->make($parameter->getClass()->name);
            if (! $model = $instance->resolveRouteBinding($parameterValue)) {
                throw (new ModelNotFoundException)->setModel(get_class($instance));
            }
            $route->setParameter($parameterName, $model);
        }
    }
    protected static function getParameterName($name, $parameters)
    {
        if (array_key_exists($name, $parameters)) {
            return $name;
        }
        if (array_key_exists($snakedName = Str::snake($name), $parameters)) {
            return $snakedName;
        }
    }
}