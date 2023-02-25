<?php
namespace Symfony\Component\Translation\Tests\DependencyInjection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Translation\DependencyInjection\TranslationExtractorPass;
class TranslationExtractorPassTest extends TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $extractorDefinition = $container->register('translation.extractor');
        $container->register('foo.id')
            ->addTag('translation.extractor', ['alias' => 'bar.alias']);
        $translationDumperPass = new TranslationExtractorPass();
        $translationDumperPass->process($container);
        $this->assertEquals([['addExtractor', ['bar.alias', new Reference('foo.id')]]], $extractorDefinition->getMethodCalls());
    }
    public function testProcessNoDefinitionFound()
    {
        $container = new ContainerBuilder();
        $definitionsBefore = \count($container->getDefinitions());
        $aliasesBefore = \count($container->getAliases());
        $translationDumperPass = new TranslationExtractorPass();
        $translationDumperPass->process($container);
        $this->assertCount($definitionsBefore, $container->getDefinitions());
        $this->assertCount($aliasesBefore, $container->getAliases());
    }
    public function testProcessMissingAlias()
    {
        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')->disableOriginalConstructor()->getMock();
        $container = new ContainerBuilder();
        $container->register('translation.extractor');
        $container->register('foo.id')
            ->addTag('translation.extractor', []);
        $definition->expects($this->never())->method('addMethodCall');
        $translationDumperPass = new TranslationExtractorPass();
        $translationDumperPass->process($container);
    }
}
