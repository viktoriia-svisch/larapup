<?php
namespace Symfony\Component\Translation\DependencyInjection;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;
class TranslationExtractorPass implements CompilerPassInterface
{
    private $extractorServiceId;
    private $extractorTag;
    public function __construct(string $extractorServiceId = 'translation.extractor', string $extractorTag = 'translation.extractor')
    {
        $this->extractorServiceId = $extractorServiceId;
        $this->extractorTag = $extractorTag;
    }
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->extractorServiceId)) {
            return;
        }
        $definition = $container->getDefinition($this->extractorServiceId);
        foreach ($container->findTaggedServiceIds($this->extractorTag, true) as $id => $attributes) {
            if (!isset($attributes[0]['alias'])) {
                throw new RuntimeException(sprintf('The alias for the tag "translation.extractor" of service "%s" must be set.', $id));
            }
            $definition->addMethodCall('addExtractor', [$attributes[0]['alias'], new Reference($id)]);
        }
    }
}
