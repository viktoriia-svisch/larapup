<?php
namespace Symfony\Component\HttpKernel\Tests\Fixtures\ExtensionLoadedBundle\DependencyInjection;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
class ExtensionLoadedExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
    }
}
