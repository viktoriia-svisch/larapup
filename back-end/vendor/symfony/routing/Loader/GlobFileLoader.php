<?php
namespace Symfony\Component\Routing\Loader;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Routing\RouteCollection;
class GlobFileLoader extends FileLoader
{
    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();
        foreach ($this->glob($resource, false, $globResource) as $path => $info) {
            $collection->addCollection($this->import($path));
        }
        $collection->addResource($globResource);
        return $collection;
    }
    public function supports($resource, $type = null)
    {
        return 'glob' === $type;
    }
}
