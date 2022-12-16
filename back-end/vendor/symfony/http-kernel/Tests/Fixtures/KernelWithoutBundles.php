<?php
namespace Symfony\Component\HttpKernel\Tests\Fixtures;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
class KernelWithoutBundles extends Kernel
{
    public function registerBundles()
    {
        return [];
    }
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
    public function getProjectDir()
    {
        return __DIR__;
    }
    protected function build(ContainerBuilder $container)
    {
        $container->setParameter('test_executed', true);
    }
}
