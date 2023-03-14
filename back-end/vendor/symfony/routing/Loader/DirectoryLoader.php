<?php
namespace Symfony\Component\Routing\Loader;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Routing\RouteCollection;
class DirectoryLoader extends FileLoader
{
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);
        $collection = new RouteCollection();
        $collection->addResource(new DirectoryResource($path));
        foreach (scandir($path) as $dir) {
            if ('.' !== $dir[0]) {
                $this->setCurrentDir($path);
                $subPath = $path.'/'.$dir;
                $subType = null;
                if (is_dir($subPath)) {
                    $subPath .= '/';
                    $subType = 'directory';
                }
                $subCollection = $this->import($subPath, $subType, false, $path);
                $collection->addCollection($subCollection);
            }
        }
        return $collection;
    }
    public function supports($resource, $type = null)
    {
        return 'directory' === $type;
    }
}
