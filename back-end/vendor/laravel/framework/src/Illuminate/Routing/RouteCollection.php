<?php
namespace Illuminate\Routing;
use Countable;
use ArrayIterator;
use IteratorAggregate;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
class RouteCollection implements Countable, IteratorAggregate
{
    protected $routes = [];
    protected $allRoutes = [];
    protected $nameList = [];
    protected $actionList = [];
    public function add(Route $route)
    {
        $this->addToCollections($route);
        $this->addLookups($route);
        return $route;
    }
    protected function addToCollections($route)
    {
        $domainAndUri = $route->getDomain().$route->uri();
        foreach ($route->methods() as $method) {
            $this->routes[$method][$domainAndUri] = $route;
        }
        $this->allRoutes[$method.$domainAndUri] = $route;
    }
    protected function addLookups($route)
    {
        if ($name = $route->getName()) {
            $this->nameList[$name] = $route;
        }
        $action = $route->getAction();
        if (isset($action['controller'])) {
            $this->addToActionList($action, $route);
        }
    }
    protected function addToActionList($action, $route)
    {
        $this->actionList[trim($action['controller'], '\\')] = $route;
    }
    public function refreshNameLookups()
    {
        $this->nameList = [];
        foreach ($this->allRoutes as $route) {
            if ($route->getName()) {
                $this->nameList[$route->getName()] = $route;
            }
        }
    }
    public function refreshActionLookups()
    {
        $this->actionList = [];
        foreach ($this->allRoutes as $route) {
            if (isset($route->getAction()['controller'])) {
                $this->addToActionList($route->getAction(), $route);
            }
        }
    }
    public function match(Request $request)
    {
        $routes = $this->get($request->getMethod());
        $route = $this->matchAgainstRoutes($routes, $request);
        if (! is_null($route)) {
            return $route->bind($request);
        }
        $others = $this->checkForAlternateVerbs($request);
        if (count($others) > 0) {
            return $this->getRouteForMethods($request, $others);
        }
        throw new NotFoundHttpException;
    }
    protected function matchAgainstRoutes(array $routes, $request, $includingMethod = true)
    {
        [$fallbacks, $routes] = collect($routes)->partition(function ($route) {
            return $route->isFallback;
        });
        return $routes->merge($fallbacks)->first(function ($value) use ($request, $includingMethod) {
            return $value->matches($request, $includingMethod);
        });
    }
    protected function checkForAlternateVerbs($request)
    {
        $methods = array_diff(Router::$verbs, [$request->getMethod()]);
        $others = [];
        foreach ($methods as $method) {
            if (! is_null($this->matchAgainstRoutes($this->get($method), $request, false))) {
                $others[] = $method;
            }
        }
        return $others;
    }
    protected function getRouteForMethods($request, array $methods)
    {
        if ($request->method() === 'OPTIONS') {
            return (new Route('OPTIONS', $request->path(), function () use ($methods) {
                return new Response('', 200, ['Allow' => implode(',', $methods)]);
            }))->bind($request);
        }
        $this->methodNotAllowed($methods);
    }
    protected function methodNotAllowed(array $others)
    {
        throw new MethodNotAllowedHttpException($others);
    }
    public function get($method = null)
    {
        return is_null($method) ? $this->getRoutes() : Arr::get($this->routes, $method, []);
    }
    public function hasNamedRoute($name)
    {
        return ! is_null($this->getByName($name));
    }
    public function getByName($name)
    {
        return $this->nameList[$name] ?? null;
    }
    public function getByAction($action)
    {
        return $this->actionList[$action] ?? null;
    }
    public function getRoutes()
    {
        return array_values($this->allRoutes);
    }
    public function getRoutesByMethod()
    {
        return $this->routes;
    }
    public function getRoutesByName()
    {
        return $this->nameList;
    }
    public function getIterator()
    {
        return new ArrayIterator($this->getRoutes());
    }
    public function count()
    {
        return count($this->getRoutes());
    }
}
