<?php
namespace Symfony\Component\HttpKernel\Config;
use Symfony\Component\Config\FileLocator as BaseFileLocator;
use Symfony\Component\HttpKernel\KernelInterface;
class FileLocator extends BaseFileLocator
{
    private $kernel;
    private $path;
    public function __construct(KernelInterface $kernel, string $path = null, array $paths = [])
    {
        $this->kernel = $kernel;
        if (null !== $path) {
            $this->path = $path;
            $paths[] = $path;
        }
        parent::__construct($paths);
    }
    public function locate($file, $currentPath = null, $first = true)
    {
        if (isset($file[0]) && '@' === $file[0]) {
            return $this->kernel->locateResource($file, $this->path, $first);
        }
        return parent::locate($file, $currentPath, $first);
    }
}
