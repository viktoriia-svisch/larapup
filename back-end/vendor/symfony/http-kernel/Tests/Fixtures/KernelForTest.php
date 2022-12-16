<?php
namespace Symfony\Component\HttpKernel\Tests\Fixtures;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
class KernelForTest extends Kernel
{
    public function getBundleMap()
    {
        return $this->bundleMap;
    }
    public function registerBundles()
    {
        return [];
    }
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
    public function isBooted()
    {
        return $this->booted;
    }
    public function getCacheDir()
    {
        return $this->getProjectDir().'/Tests/Fixtures/cache.'.$this->environment;
    }
    public function getLogDir()
    {
        return $this->getProjectDir().'/Tests/Fixtures/logs';
    }
}
