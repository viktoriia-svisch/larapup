<?php
namespace Symfony\Component\HttpKernel\DependencyInjection;
use Symfony\Component\DependencyInjection\ContainerBuilder;
abstract class ConfigurableExtension extends Extension
{
    final public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadInternal($this->processConfiguration($this->getConfiguration($configs, $container), $configs), $container);
    }
    abstract protected function loadInternal(array $mergedConfig, ContainerBuilder $container);
}
