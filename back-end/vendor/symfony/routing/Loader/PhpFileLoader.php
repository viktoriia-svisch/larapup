<?php
namespace Symfony\Component\Routing\Loader;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollection;
class PhpFileLoader extends FileLoader
{
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);
        $this->setCurrentDir(\dirname($path));
        $loader = $this;
        $load = \Closure::bind(function ($file) use ($loader) {
            return include $file;
        }, null, ProtectedPhpFileLoader::class);
        $result = $load($path);
        if (\is_object($result) && \is_callable($result)) {
            $collection = new RouteCollection();
            $result(new RoutingConfigurator($collection, $this, $path, $file), $this);
        } else {
            $collection = $result;
        }
        $collection->addResource(new FileResource($path));
        return $collection;
    }
    public function supports($resource, $type = null)
    {
        return \is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'php' === $type);
    }
}
final class ProtectedPhpFileLoader extends PhpFileLoader
{
}
