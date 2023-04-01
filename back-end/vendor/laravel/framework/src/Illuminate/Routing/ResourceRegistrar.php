<?php
namespace Illuminate\Routing;
use Illuminate\Support\Str;
class ResourceRegistrar
{
    protected $router;
    protected $resourceDefaults = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
    protected $parameters;
    protected static $parameterMap = [];
    protected static $singularParameters = true;
    protected static $verbs = [
        'create' => 'create',
        'edit' => 'edit',
    ];
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
    public function register($name, $controller, array $options = [])
    {
        if (isset($options['parameters']) && ! isset($this->parameters)) {
            $this->parameters = $options['parameters'];
        }
        if (Str::contains($name, '/')) {
            $this->prefixedResource($name, $controller, $options);
            return;
        }
        $base = $this->getResourceWildcard(last(explode('.', $name)));
        $defaults = $this->resourceDefaults;
        $collection = new RouteCollection;
        foreach ($this->getResourceMethods($defaults, $options) as $m) {
            $collection->add($this->{'addResource'.ucfirst($m)}(
                $name, $base, $controller, $options
            ));
        }
        return $collection;
    }
    protected function prefixedResource($name, $controller, array $options)
    {
        [$name, $prefix] = $this->getResourcePrefix($name);
        $callback = function ($me) use ($name, $controller, $options) {
            $me->resource($name, $controller, $options);
        };
        return $this->router->group(compact('prefix'), $callback);
    }
    protected function getResourcePrefix($name)
    {
        $segments = explode('/', $name);
        $prefix = implode('/', array_slice($segments, 0, -1));
        return [end($segments), $prefix];
    }
    protected function getResourceMethods($defaults, $options)
    {
        $methods = $defaults;
        if (isset($options['only'])) {
            $methods = array_intersect($methods, (array) $options['only']);
        }
        if (isset($options['except'])) {
            $methods = array_diff($methods, (array) $options['except']);
        }
        return $methods;
    }
    protected function addResourceIndex($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name);
        $action = $this->getResourceAction($name, $controller, 'index', $options);
        return $this->router->get($uri, $action);
    }
    protected function addResourceCreate($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/'.static::$verbs['create'];
        $action = $this->getResourceAction($name, $controller, 'create', $options);
        return $this->router->get($uri, $action);
    }
    protected function addResourceStore($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name);
        $action = $this->getResourceAction($name, $controller, 'store', $options);
        return $this->router->post($uri, $action);
    }
    protected function addResourceShow($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}';
        $action = $this->getResourceAction($name, $controller, 'show', $options);
        return $this->router->get($uri, $action);
    }
    protected function addResourceEdit($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}/'.static::$verbs['edit'];
        $action = $this->getResourceAction($name, $controller, 'edit', $options);
        return $this->router->get($uri, $action);
    }
    protected function addResourceUpdate($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}';
        $action = $this->getResourceAction($name, $controller, 'update', $options);
        return $this->router->match(['PUT', 'PATCH'], $uri, $action);
    }
    protected function addResourceDestroy($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}';
        $action = $this->getResourceAction($name, $controller, 'destroy', $options);
        return $this->router->delete($uri, $action);
    }
    public function getResourceUri($resource)
    {
        if (! Str::contains($resource, '.')) {
            return $resource;
        }
        $segments = explode('.', $resource);
        $uri = $this->getNestedResourceUri($segments);
        return str_replace('/{'.$this->getResourceWildcard(end($segments)).'}', '', $uri);
    }
    protected function getNestedResourceUri(array $segments)
    {
        return implode('/', array_map(function ($s) {
            return $s.'/{'.$this->getResourceWildcard($s).'}';
        }, $segments));
    }
    public function getResourceWildcard($value)
    {
        if (isset($this->parameters[$value])) {
            $value = $this->parameters[$value];
        } elseif (isset(static::$parameterMap[$value])) {
            $value = static::$parameterMap[$value];
        } elseif ($this->parameters === 'singular' || static::$singularParameters) {
            $value = Str::singular($value);
        }
        return str_replace('-', '_', $value);
    }
    protected function getResourceAction($resource, $controller, $method, $options)
    {
        $name = $this->getResourceRouteName($resource, $method, $options);
        $action = ['as' => $name, 'uses' => $controller.'@'.$method];
        if (isset($options['middleware'])) {
            $action['middleware'] = $options['middleware'];
        }
        return $action;
    }
    protected function getResourceRouteName($resource, $method, $options)
    {
        $name = $resource;
        if (isset($options['names'])) {
            if (is_string($options['names'])) {
                $name = $options['names'];
            } elseif (isset($options['names'][$method])) {
                return $options['names'][$method];
            }
        }
        $prefix = isset($options['as']) ? $options['as'].'.' : '';
        return trim(sprintf('%s%s.%s', $prefix, $name, $method), '.');
    }
    public static function singularParameters($singular = true)
    {
        static::$singularParameters = (bool) $singular;
    }
    public static function getParameters()
    {
        return static::$parameterMap;
    }
    public static function setParameters(array $parameters = [])
    {
        static::$parameterMap = $parameters;
    }
    public static function verbs(array $verbs = [])
    {
        if (empty($verbs)) {
            return static::$verbs;
        } else {
            static::$verbs = array_merge(static::$verbs, $verbs);
        }
    }
}
