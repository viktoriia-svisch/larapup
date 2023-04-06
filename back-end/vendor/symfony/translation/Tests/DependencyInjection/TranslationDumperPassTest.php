<?php
namespace Symfony\Component\Translation\Tests\DependencyInjection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Translation\DependencyInjection\TranslationDumperPass;
class TranslationDumperPassTest extends TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $writerDefinition = $container->register('translation.writer');
        $container->register('foo.id')
            ->addTag('translation.dumper', ['alias' => 'bar.alias']);
        $translationDumperPass = new TranslationDumperPass();
        $translationDumperPass->process($container);
        $this->assertEquals([['addDumper', ['bar.alias', new Reference('foo.id')]]], $writerDefinition->getMethodCalls());
    }
    public function testProcessNoDefinitionFound()
    {
        $container = new ContainerBuilder();
        $definitionsBefore = \count($container->getDefinitions());
        $aliasesBefore = \count($container->getAliases());
        $translationDumperPass = new TranslationDumperPass();
        $translationDumperPass->process($container);
        $this->assertCount($definitionsBefore, $container->getDefinitions());
        $this->assertCount($aliasesBefore, $container->getAliases());
    }
}
