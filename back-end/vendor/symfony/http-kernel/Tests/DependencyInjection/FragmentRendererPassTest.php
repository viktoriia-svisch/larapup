<?php
namespace Symfony\Component\HttpKernel\Tests\DependencyInjection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\DependencyInjection\FragmentRendererPass;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
class FragmentRendererPassTest extends TestCase
{
    public function testContentRendererWithoutInterface()
    {
        $builder = new ContainerBuilder();
        $fragmentHandlerDefinition = $builder->register('fragment.handler');
        $builder->register('my_content_renderer', 'Symfony\Component\DependencyInjection\Definition')
            ->addTag('kernel.fragment_renderer', ['alias' => 'foo']);
        $pass = new FragmentRendererPass();
        $pass->process($builder);
        $this->assertEquals([['addRendererService', ['foo', 'my_content_renderer']]], $fragmentHandlerDefinition->getMethodCalls());
    }
    public function testValidContentRenderer()
    {
        $builder = new ContainerBuilder();
        $fragmentHandlerDefinition = $builder->register('fragment.handler')
            ->addArgument(null);
        $builder->register('my_content_renderer', 'Symfony\Component\HttpKernel\Tests\DependencyInjection\RendererService')
            ->addTag('kernel.fragment_renderer', ['alias' => 'foo']);
        $pass = new FragmentRendererPass();
        $pass->process($builder);
        $serviceLocatorDefinition = $builder->getDefinition((string) $fragmentHandlerDefinition->getArgument(0));
        $this->assertSame(ServiceLocator::class, $serviceLocatorDefinition->getClass());
        $this->assertEquals(['foo' => new ServiceClosureArgument(new Reference('my_content_renderer'))], $serviceLocatorDefinition->getArgument(0));
    }
}
class RendererService implements FragmentRendererInterface
{
    public function render($uri, Request $request = null, array $options = [])
    {
    }
    public function getName()
    {
        return 'test';
    }
}
