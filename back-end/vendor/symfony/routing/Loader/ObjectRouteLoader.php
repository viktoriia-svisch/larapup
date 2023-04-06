<?php
namespace Symfony\Component\Routing\Loader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouteCollection;
abstract class ObjectRouteLoader extends Loader
{
    abstract protected function getServiceObject($id);
    public function load($resource, $type = null)
    {
        if (1 === substr_count($resource, ':')) {
            $resource = str_replace(':', '::', $resource);
            @trigger_error(sprintf('Referencing service route loaders with a single colon is deprecated since Symfony 4.1. Use %s instead.', $resource), E_USER_DEPRECATED);
        }
        $parts = explode('::', $resource);
        if (2 != \count($parts)) {
            throw new \InvalidArgumentException(sprintf('Invalid resource "%s" passed to the "service" route loader: use the format "service::method"', $resource));
        }
        $serviceString = $parts[0];
        $method = $parts[1];
        $loaderObject = $this->getServiceObject($serviceString);
        if (!\is_object($loaderObject)) {
            throw new \LogicException(sprintf('%s:getServiceObject() must return an object: %s returned', \get_class($this), \gettype($loaderObject)));
        }
        if (!\is_callable([$loaderObject, $method])) {
            throw new \BadMethodCallException(sprintf('Method "%s" not found on "%s" when importing routing resource "%s"', $method, \get_class($loaderObject), $resource));
        }
        $routeCollection = $loaderObject->$method($this);
        if (!$routeCollection instanceof RouteCollection) {
            $type = \is_object($routeCollection) ? \get_class($routeCollection) : \gettype($routeCollection);
            throw new \LogicException(sprintf('The %s::%s method must return a RouteCollection: %s returned', \get_class($loaderObject), $method, $type));
        }
        $this->addClassResource(new \ReflectionClass($loaderObject), $routeCollection);
        return $routeCollection;
    }
    public function supports($resource, $type = null)
    {
        return 'service' === $type;
    }
    private function addClassResource(\ReflectionClass $class, RouteCollection $collection)
    {
        do {
            if (is_file($class->getFileName())) {
                $collection->addResource(new FileResource($class->getFileName()));
            }
        } while ($class = $class->getParentClass());
    }
}
