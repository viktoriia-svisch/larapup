<?php
namespace Illuminate\Routing;
use Illuminate\Support\Arr;
class RouteParameterBinder
{
    protected $route;
    public function __construct($route)
    {
        $this->route = $route;
    }
    public function parameters($request)
    {
        $parameters = $this->bindPathParameters($request);
        if (! is_null($this->route->compiled->getHostRegex())) {
            $parameters = $this->bindHostParameters(
                $request, $parameters
            );
        }
        return $this->replaceDefaults($parameters);
    }
    protected function bindPathParameters($request)
    {
        $path = '/'.ltrim($request->decodedPath(), '/');
        preg_match($this->route->compiled->getRegex(), $path, $matches);
        return $this->matchToKeys(array_slice($matches, 1));
    }
    protected function bindHostParameters($request, $parameters)
    {
        preg_match($this->route->compiled->getHostRegex(), $request->getHost(), $matches);
        return array_merge($this->matchToKeys(array_slice($matches, 1)), $parameters);
    }
    protected function matchToKeys(array $matches)
    {
        if (empty($parameterNames = $this->route->parameterNames())) {
            return [];
        }
        $parameters = array_intersect_key($matches, array_flip($parameterNames));
        return array_filter($parameters, function ($value) {
            return is_string($value) && strlen($value) > 0;
        });
    }
    protected function replaceDefaults(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $value ?? Arr::get($this->route->defaults, $key);
        }
        foreach ($this->route->defaults as $key => $value) {
            if (! isset($parameters[$key])) {
                $parameters[$key] = $value;
            }
        }
        return $parameters;
    }
}
