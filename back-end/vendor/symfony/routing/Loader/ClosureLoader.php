<?php
namespace Symfony\Component\Routing\Loader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
class ClosureLoader extends Loader
{
    public function load($closure, $type = null)
    {
        return $closure();
    }
    public function supports($resource, $type = null)
    {
        return $resource instanceof \Closure && (!$type || 'closure' === $type);
    }
}
