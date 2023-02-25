<?php
namespace Symfony\Component\Routing\Loader\DependencyInjection;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\Loader\ObjectRouteLoader;
class ServiceRouterLoader extends ObjectRouteLoader
{
    private $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    protected function getServiceObject($id)
    {
        return $this->container->get($id);
    }
}
