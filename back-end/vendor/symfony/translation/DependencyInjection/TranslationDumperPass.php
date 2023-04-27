<?php
namespace Symfony\Component\Translation\DependencyInjection;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
class TranslationDumperPass implements CompilerPassInterface
{
    private $writerServiceId;
    private $dumperTag;
    public function __construct(string $writerServiceId = 'translation.writer', string $dumperTag = 'translation.dumper')
    {
        $this->writerServiceId = $writerServiceId;
        $this->dumperTag = $dumperTag;
    }
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->writerServiceId)) {
            return;
        }
        $definition = $container->getDefinition($this->writerServiceId);
        foreach ($container->findTaggedServiceIds($this->dumperTag, true) as $id => $attributes) {
            $definition->addMethodCall('addDumper', [$attributes[0]['alias'], new Reference($id)]);
        }
    }
}
