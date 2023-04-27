<?php
namespace Symfony\Component\HttpKernel\DependencyInjection;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Log\Logger;
class LoggerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setAlias(LoggerInterface::class, 'logger')
            ->setPublic(false);
        if ($container->has('logger')) {
            return;
        }
        $container->register('logger', Logger::class)
            ->setPublic(false);
    }
}
