<?php
namespace Illuminate\Routing;
use Closure;
use LogicException;
use ReflectionFunction;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Container\Container;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Routing\Matching\UriValidator;
use Illuminate\Routing\Matching\HostValidator;
use Illuminate\Routing\Matching\MethodValidator;
use Illuminate\Routing\Matching\SchemeValidator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Contracts\ControllerDispatcher as ControllerDispatcherContract;
class Route
{
    use Macroable, RouteDependencyResolverTrait;
    public $uri;
    public $methods;
    public $action;
    public $isFallback = false;
    public $controller;
    public $defaults = [];
    public $wheres = [];
    public $parameters;
    public $parameterNames;
    protected $originalParameters;
    public $computedMiddleware;
    public $compiled;
    protected $router;
    protected $container;
    public static $validators;
    public function __construct($methods, $uri, $action)
    {
        $this->uri = $uri;
        $this->methods = (array) $methods;
        $this->action = $this->parseAction($action);
        if (in_array('GET', $this->methods) && ! in_array('HEAD', $this->methods)) {
            $this->methods[] = 'HEAD';
        }
        if (isset($this->action['prefix'])) {
            $this->prefix($this->action['prefix']);
        }
    }
    protected function parseAction($action)
    {
        return RouteAction::parse($this->uri, $action);
    }
    public function run()
    {
        $this->container = $this->container ?: new Container;
        try {
            if ($this->isControllerAction()) {
                return $this->runController();
            }
            return $this->runCallable();
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }
    protected function isControllerAction()
    {
        return is_string($this->action['uses']);
    }
    protected function runCallable()
    {
        $callable = $this->action['uses'];
        return $callable(...array_values($this->resolveMethodDependencies(
            $this->parametersWithoutNulls(), new ReflectionFunction($this->action['uses'])
        )));
    }
    protected function runController()
    {
        return $this->controllerDispatcher()->dispatch(
            $this, $this->getController(), $this->getControllerMethod()
        );
    }
    public function getController()
    {
        if (! $this->controller) {
            $class = $this->parseControllerCallback()[0];
            $this->controller = $this->container->make(ltrim($class, '\\'));
        }
        return $this->controller;
    }
    protected function getControllerMethod()
    {
        return $this->parseControllerCallback()[1];
    }
    protected function parseControllerCallback()
    {
        return Str::parseCallback($this->action['uses']);
    }
    public function matches(Request $request, $includingMethod = true)
    {
        $this->compileRoute();
        foreach ($this->getValidators() as $validator) {
            if (! $includingMethod && $validator instanceof MethodValidator) {
                continue;
            }
            if (! $validator->matches($this, $request)) {
                return false;
            }
        }
        return true;
    }
    protected function compileRoute()
    {
        if (! $this->compiled) {
            $this->compiled = (new RouteCompiler($this))->compile();
        }
        return $this->compiled;
    }
    public function bind(Request $request)
    {
        $this->compileRoute();
        $this->parameters = (new RouteParameterBinder($this))
                        ->parameters($request);
        $this->originalParameters = $this->parameters;
        return $this;
    }
    public function hasParameters()
    {
        return isset($this->parameters);
    }
    public function hasParameter($name)
    {
        if ($this->hasParameters()) {
            return array_key_exists($name, $this->parameters());
        }
        return false;
    }
    public function parameter($name, $default = null)
    {
        return Arr::get($this->parameters(), $name, $default);
    }
    public function originalParameter($name, $default = null)
    {
        return Arr::get($this->originalParameters(), $name, $default);
    }
    public function setParameter($name, $value)
    {
        $this->parameters();
        $this->parameters[$name] = $value;
    }
    public function forgetParameter($name)
    {
        $this->parameters();
        unset($this->parameters[$name]);
    }
    public function parameters()
    {
        if (isset($this->parameters)) {
            return $this->parameters;
        }
        throw new LogicException('Route is not bound.');
    }
    public function originalParameters()
    {
        if (isset($this->originalParameters)) {
            return $this->originalParameters;
        }
        throw new LogicException('Route is not bound.');
    }
    public function parametersWithoutNulls()
    {
        return array_filter($this->parameters(), function ($p) {
            return ! is_null($p);
        });
    }
    public function parameterNames()
    {
        if (isset($this->parameterNames)) {
            return $this->parameterNames;
        }
        return $this->parameterNames = $this->compileParameterNames();
    }
    protected function compileParameterNames()
    {
        preg_match_all('/\{(.*?)\}/', $this->getDomain().$this->uri, $matches);
        return array_map(function ($m) {
            return trim($m, '?');
        }, $matches[1]);
    }
    public function signatureParameters($subClass = null)
    {
        return RouteSignatureParameters::fromAction($this->action, $subClass);
    }
    public function defaults($key, $value)
    {
        $this->defaults[$key] = $value;
        return $this;
    }
    public function where($name, $expression = null)
    {
        foreach ($this->parseWhere($name, $expression) as $name => $expression) {
            $this->wheres[$name] = $expression;
        }
        return $this;
    }
    protected function parseWhere($name, $expression)
    {
        return is_array($name) ? $name : [$name => $expression];
    }
    protected function whereArray(array $wheres)
    {
        foreach ($wheres as $name => $expression) {
            $this->where($name, $expression);
        }
        return $this;
    }
    public function fallback()
    {
        $this->isFallback = true;
        return $this;
    }
    public function methods()
    {
        return $this->methods;
    }
    public function httpOnly()
    {
        return in_array('http', $this->action, true);
    }
    public function httpsOnly()
    {
        return $this->secure();
    }
    public function secure()
    {
        return in_array('https', $this->action, true);
    }
    public function domain($domain = null)
    {
        if (is_null($domain)) {
            return $this->getDomain();
        }
        $this->action['domain'] = $domain;
        return $this;
    }
    public function getDomain()
    {
        return isset($this->action['domain'])
                ? str_replace(['http:
    }
    public function getPrefix()
    {
        return $this->action['prefix'] ?? null;
    }
    public function prefix($prefix)
    {
        $uri = rtrim($prefix, '/').'/'.ltrim($this->uri, '/');
        $this->uri = trim($uri, '/');
        return $this;
    }
    public function uri()
    {
        return $this->uri;
    }
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }
    public function getName()
    {
        return $this->action['as'] ?? null;
    }
    public function name($name)
    {
        $this->action['as'] = isset($this->action['as']) ? $this->action['as'].$name : $name;
        return $this;
    }
    public function named(...$patterns)
    {
        if (is_null($routeName = $this->getName())) {
            return false;
        }
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }
        return false;
    }
    public function uses($action)
    {
        $action = is_string($action) ? $this->addGroupNamespaceToStringUses($action) : $action;
        return $this->setAction(array_merge($this->action, $this->parseAction([
            'uses' => $action,
            'controller' => $action,
        ])));
    }
    protected function addGroupNamespaceToStringUses($action)
    {
        $groupStack = last($this->router->getGroupStack());
        if (isset($groupStack['namespace']) && strpos($action, '\\') !== 0) {
            return $groupStack['namespace'].'\\'.$action;
        }
        return $action;
    }
    public function getActionName()
    {
        return $this->action['controller'] ?? 'Closure';
    }
    public function getActionMethod()
    {
        return Arr::last(explode('@', $this->getActionName()));
    }
    public function getAction($key = null)
    {
        return Arr::get($this->action, $key);
    }
    public function setAction(array $action)
    {
        $this->action = $action;
        return $this;
    }
    public function gatherMiddleware()
    {
        if (! is_null($this->computedMiddleware)) {
            return $this->computedMiddleware;
        }
        $this->computedMiddleware = [];
        return $this->computedMiddleware = array_unique(array_merge(
            $this->middleware(), $this->controllerMiddleware()
        ), SORT_REGULAR);
    }
    public function middleware($middleware = null)
    {
        if (is_null($middleware)) {
            return (array) ($this->action['middleware'] ?? []);
        }
        if (is_string($middleware)) {
            $middleware = func_get_args();
        }
        $this->action['middleware'] = array_merge(
            (array) ($this->action['middleware'] ?? []), $middleware
        );
        return $this;
    }
    public function controllerMiddleware()
    {
        if (! $this->isControllerAction()) {
            return [];
        }
        return $this->controllerDispatcher()->getMiddleware(
            $this->getController(), $this->getControllerMethod()
        );
    }
    public function controllerDispatcher()
    {
        if ($this->container->bound(ControllerDispatcherContract::class)) {
            return $this->container->make(ControllerDispatcherContract::class);
        }
        return new ControllerDispatcher($this->container);
    }
    public static function getValidators()
    {
        if (isset(static::$validators)) {
            return static::$validators;
        }
        return static::$validators = [
            new UriValidator, new MethodValidator,
            new SchemeValidator, new HostValidator,
        ];
    }
    public function getCompiled()
    {
        return $this->compiled;
    }
    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }
    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }
    public function prepareForSerialization()
    {
        if ($this->action['uses'] instanceof Closure) {
            throw new LogicException("Unable to prepare route [{$this->uri}] for serialization. Uses Closure.");
        }
        $this->compileRoute();
        unset($this->router, $this->container);
    }
    public function __get($key)
    {
        return $this->parameter($key);
    }
}
