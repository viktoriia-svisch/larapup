<?php
namespace Symfony\Component\Routing;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\ResourceInterface;
class RouteCollectionBuilder
{
    private $routes = [];
    private $loader;
    private $defaults = [];
    private $prefix;
    private $host;
    private $condition;
    private $requirements = [];
    private $options = [];
    private $schemes;
    private $methods;
    private $resources = [];
    public function __construct(LoaderInterface $loader = null)
    {
        $this->loader = $loader;
    }
    public function import($resource, $prefix = '/', $type = null)
    {
        $collections = $this->load($resource, $type);
        $builder = $this->createBuilder();
        foreach ($collections as $collection) {
            if (null === $collection) {
                continue;
            }
            foreach ($collection->all() as $name => $route) {
                $builder->addRoute($route, $name);
            }
            foreach ($collection->getResources() as $resource) {
                $builder->addResource($resource);
            }
        }
        $this->mount($prefix, $builder);
        return $builder;
    }
    public function add($path, $controller, $name = null)
    {
        $route = new Route($path);
        $route->setDefault('_controller', $controller);
        $this->addRoute($route, $name);
        return $route;
    }
    public function createBuilder()
    {
        return new self($this->loader);
    }
    public function mount($prefix, self $builder)
    {
        $builder->prefix = trim(trim($prefix), '/');
        $this->routes[] = $builder;
    }
    public function addRoute(Route $route, $name = null)
    {
        if (null === $name) {
            $name = '_unnamed_route_'.spl_object_hash($route);
        }
        $this->routes[$name] = $route;
        return $this;
    }
    public function setHost($pattern)
    {
        $this->host = $pattern;
        return $this;
    }
    public function setCondition($condition)
    {
        $this->condition = $condition;
        return $this;
    }
    public function setDefault($key, $value)
    {
        $this->defaults[$key] = $value;
        return $this;
    }
    public function setRequirement($key, $regex)
    {
        $this->requirements[$key] = $regex;
        return $this;
    }
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }
    public function setSchemes($schemes)
    {
        $this->schemes = $schemes;
        return $this;
    }
    public function setMethods($methods)
    {
        $this->methods = $methods;
        return $this;
    }
    private function addResource(ResourceInterface $resource): self
    {
        $this->resources[] = $resource;
        return $this;
    }
    public function build()
    {
        $routeCollection = new RouteCollection();
        foreach ($this->routes as $name => $route) {
            if ($route instanceof Route) {
                $route->setDefaults(array_merge($this->defaults, $route->getDefaults()));
                $route->setOptions(array_merge($this->options, $route->getOptions()));
                foreach ($this->requirements as $key => $val) {
                    if (!$route->hasRequirement($key)) {
                        $route->setRequirement($key, $val);
                    }
                }
                if (null !== $this->prefix) {
                    $route->setPath('/'.$this->prefix.$route->getPath());
                }
                if (!$route->getHost()) {
                    $route->setHost($this->host);
                }
                if (!$route->getCondition()) {
                    $route->setCondition($this->condition);
                }
                if (!$route->getSchemes()) {
                    $route->setSchemes($this->schemes);
                }
                if (!$route->getMethods()) {
                    $route->setMethods($this->methods);
                }
                if ('_unnamed_route_' === substr($name, 0, 15)) {
                    $name = $this->generateRouteName($route);
                }
                $routeCollection->add($name, $route);
            } else {
                $subCollection = $route->build();
                $subCollection->addPrefix($this->prefix);
                $routeCollection->addCollection($subCollection);
            }
        }
        foreach ($this->resources as $resource) {
            $routeCollection->addResource($resource);
        }
        return $routeCollection;
    }
    private function generateRouteName(Route $route): string
    {
        $methods = implode('_', $route->getMethods()).'_';
        $routeName = $methods.$route->getPath();
        $routeName = str_replace(['/', ':', '|', '-'], '_', $routeName);
        $routeName = preg_replace('/[^a-z0-9A-Z_.]+/', '', $routeName);
        $routeName = preg_replace('/_+/', '_', $routeName);
        return $routeName;
    }
    private function load($resource, string $type = null): array
    {
        if (null === $this->loader) {
            throw new \BadMethodCallException('Cannot import other routing resources: you must pass a LoaderInterface when constructing RouteCollectionBuilder.');
        }
        if ($this->loader->supports($resource, $type)) {
            $collections = $this->loader->load($resource, $type);
            return \is_array($collections) ? $collections : [$collections];
        }
        if (null === $resolver = $this->loader->getResolver()) {
            throw new LoaderLoadException($resource, null, null, null, $type);
        }
        if (false === $loader = $resolver->resolve($resource, $type)) {
            throw new LoaderLoadException($resource, null, null, null, $type);
        }
        $collections = $loader->load($resource, $type);
        return \is_array($collections) ? $collections : [$collections];
    }
}
