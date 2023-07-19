<?php
namespace Symfony\Component\Routing\Loader\Configurator;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\RouteCollection;
class RoutingConfigurator
{
    use Traits\AddTrait;
    private $loader;
    private $path;
    private $file;
    public function __construct(RouteCollection $collection, PhpFileLoader $loader, string $path, string $file)
    {
        $this->collection = $collection;
        $this->loader = $loader;
        $this->path = $path;
        $this->file = $file;
    }
    final public function import($resource, $type = null, $ignoreErrors = false)
    {
        $this->loader->setCurrentDir(\dirname($this->path));
        $imported = $this->loader->import($resource, $type, $ignoreErrors, $this->file);
        if (!\is_array($imported)) {
            return new ImportConfigurator($this->collection, $imported);
        }
        $mergedCollection = new RouteCollection();
        foreach ($imported as $subCollection) {
            $mergedCollection->addCollection($subCollection);
        }
        return new ImportConfigurator($this->collection, $mergedCollection);
    }
    final public function collection($name = '')
    {
        return new CollectionConfigurator($this->collection, $name);
    }
}