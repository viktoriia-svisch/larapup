<?php
namespace Illuminate\Routing;
use Illuminate\Container\Container;
use Illuminate\Routing\Contracts\ControllerDispatcher as ControllerDispatcherContract;
class ControllerDispatcher implements ControllerDispatcherContract
{
    use RouteDependencyResolverTrait;
    protected $container;
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    public function dispatch(Route $route, $controller, $method)
    {
        $parameters = $this->resolveClassMethodDependencies(
            $route->parametersWithoutNulls(), $controller, $method
        );
        if (method_exists($controller, 'callAction')) {
            return $controller->callAction($method, $parameters);
        }
        return $controller->{$method}(...array_values($parameters));
    }
    public function getMiddleware($controller, $method)
    {
        if (! method_exists($controller, 'getMiddleware')) {
            return [];
        }
        return collect($controller->getMiddleware())->reject(function ($data) use ($method) {
            return static::methodExcludedByOptions($method, $data['options']);
        })->pluck('middleware')->all();
    }
    protected static function methodExcludedByOptions($method, array $options)
    {
        return (isset($options['only']) && ! in_array($method, (array) $options['only'])) ||
            (! empty($options['except']) && in_array($method, (array) $options['except']));
    }
}
