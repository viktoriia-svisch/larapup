<?php
namespace Symfony\Component\HttpKernel\Tests\Fixtures;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
class KernelForOverrideName extends Kernel
{
    protected $name = 'overridden';
    public function registerBundles()
    {
    }
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
}
