<?php
namespace Symfony\Component\Routing\Loader\Configurator\Traits;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
trait RouteTrait
{
    private $route;
    final public function defaults(array $defaults)
    {
        $this->route->addDefaults($defaults);
        return $this;
    }
    final public function requirements(array $requirements)
    {
        $this->route->addRequirements($requirements);
        return $this;
    }
    final public function options(array $options)
    {
        $this->route->addOptions($options);
        return $this;
    }
    final public function condition(string $condition)
    {
        $this->route->setCondition($condition);
        return $this;
    }
    final public function host(string $pattern)
    {
        $this->route->setHost($pattern);
        return $this;
    }
    final public function schemes(array $schemes)
    {
        $this->route->setSchemes($schemes);
        return $this;
    }
    final public function methods(array $methods)
    {
        $this->route->setMethods($methods);
        return $this;
    }
    final public function controller($controller)
    {
        $this->route->addDefaults(['_controller' => $controller]);
        return $this;
    }
}
