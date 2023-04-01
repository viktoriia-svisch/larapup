<?php
namespace Symfony\Component\HttpKernel\Bundle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
interface BundleInterface extends ContainerAwareInterface
{
    public function boot();
    public function shutdown();
    public function build(ContainerBuilder $container);
    public function getContainerExtension();
    public function getName();
    public function getNamespace();
    public function getPath();
}
