<?php
namespace Illuminate\Routing;
use Symfony\Component\Routing\Route as SymfonyRoute;
class RouteCompiler
{
    protected $route;
    public function __construct($route)
    {
        $this->route = $route;
    }
    public function compile()
    {
        $optionals = $this->getOptionalParameters();
        $uri = preg_replace('/\{(\w+?)\?\}/', '{$1}', $this->route->uri());
        return (
            new SymfonyRoute($uri, $optionals, $this->route->wheres, ['utf8' => true], $this->route->getDomain() ?: '')
        )->compile();
    }
    protected function getOptionalParameters()
    {
        preg_match_all('/\{(\w+?)\?\}/', $this->route->uri(), $matches);
        return isset($matches[1]) ? array_fill_keys($matches[1], null) : [];
    }
}
