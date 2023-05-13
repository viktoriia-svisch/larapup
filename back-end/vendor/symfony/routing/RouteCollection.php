<?php
namespace Symfony\Component\Routing;
use Symfony\Component\Config\Resource\ResourceInterface;
class RouteCollection implements \IteratorAggregate, \Countable
{
    private $routes = [];
    private $resources = [];
    public function __clone()
    {
        foreach ($this->routes as $name => $route) {
            $this->routes[$name] = clone $route;
        }
    }
    public function getIterator()
    {
        return new \ArrayIterator($this->routes);
    }
    public function count()
    {
        return \count($this->routes);
    }
    public function add($name, Route $route)
    {
        unset($this->routes[$name]);
        $this->routes[$name] = $route;
    }
    public function all()
    {
        return $this->routes;
    }
    public function get($name)
    {
        return isset($this->routes[$name]) ? $this->routes[$name] : null;
    }
    public function remove($name)
    {
        foreach ((array) $name as $n) {
            unset($this->routes[$n]);
        }
    }
    public function addCollection(self $collection)
    {
        foreach ($collection->all() as $name => $route) {
            unset($this->routes[$name]);
            $this->routes[$name] = $route;
        }
        foreach ($collection->getResources() as $resource) {
            $this->addResource($resource);
        }
    }
    public function addPrefix($prefix, array $defaults = [], array $requirements = [])
    {
        $prefix = trim(trim($prefix), '/');
        if ('' === $prefix) {
            return;
        }
        foreach ($this->routes as $route) {
            $route->setPath('/'.$prefix.$route->getPath());
            $route->addDefaults($defaults);
            $route->addRequirements($requirements);
        }
    }
    public function addNamePrefix(string $prefix)
    {
        $prefixedRoutes = [];
        foreach ($this->routes as $name => $route) {
            $prefixedRoutes[$prefix.$name] = $route;
            if (null !== $name = $route->getDefault('_canonical_route')) {
                $route->setDefault('_canonical_route', $prefix.$name);
            }
        }
        $this->routes = $prefixedRoutes;
    }
    public function setHost($pattern, array $defaults = [], array $requirements = [])
    {
        foreach ($this->routes as $route) {
            $route->setHost($pattern);
            $route->addDefaults($defaults);
            $route->addRequirements($requirements);
        }
    }
    public function setCondition($condition)
    {
        foreach ($this->routes as $route) {
            $route->setCondition($condition);
        }
    }
    public function addDefaults(array $defaults)
    {
        if ($defaults) {
            foreach ($this->routes as $route) {
                $route->addDefaults($defaults);
            }
        }
    }
    public function addRequirements(array $requirements)
    {
        if ($requirements) {
            foreach ($this->routes as $route) {
                $route->addRequirements($requirements);
            }
        }
    }
    public function addOptions(array $options)
    {
        if ($options) {
            foreach ($this->routes as $route) {
                $route->addOptions($options);
            }
        }
    }
    public function setSchemes($schemes)
    {
        foreach ($this->routes as $route) {
            $route->setSchemes($schemes);
        }
    }
    public function setMethods($methods)
    {
        foreach ($this->routes as $route) {
            $route->setMethods($methods);
        }
    }
    public function getResources()
    {
        return array_values($this->resources);
    }
    public function addResource(ResourceInterface $resource)
    {
        $key = (string) $resource;
        if (!isset($this->resources[$key])) {
            $this->resources[$key] = $resource;
        }
    }
}
