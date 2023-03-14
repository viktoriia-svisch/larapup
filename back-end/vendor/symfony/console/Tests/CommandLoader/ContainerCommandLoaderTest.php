<?php
namespace Symfony\Component\Console\Tests\CommandLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Component\DependencyInjection\ServiceLocator;
class ContainerCommandLoaderTest extends TestCase
{
    public function testHas()
    {
        $loader = new ContainerCommandLoader(new ServiceLocator([
            'foo-service' => function () { return new Command('foo'); },
            'bar-service' => function () { return new Command('bar'); },
        ]), ['foo' => 'foo-service', 'bar' => 'bar-service']);
        $this->assertTrue($loader->has('foo'));
        $this->assertTrue($loader->has('bar'));
        $this->assertFalse($loader->has('baz'));
    }
    public function testGet()
    {
        $loader = new ContainerCommandLoader(new ServiceLocator([
            'foo-service' => function () { return new Command('foo'); },
            'bar-service' => function () { return new Command('bar'); },
        ]), ['foo' => 'foo-service', 'bar' => 'bar-service']);
        $this->assertInstanceOf(Command::class, $loader->get('foo'));
        $this->assertInstanceOf(Command::class, $loader->get('bar'));
    }
    public function testGetUnknownCommandThrows()
    {
        (new ContainerCommandLoader(new ServiceLocator([]), []))->get('unknown');
    }
    public function testGetCommandNames()
    {
        $loader = new ContainerCommandLoader(new ServiceLocator([
            'foo-service' => function () { return new Command('foo'); },
            'bar-service' => function () { return new Command('bar'); },
        ]), ['foo' => 'foo-service', 'bar' => 'bar-service']);
        $this->assertSame(['foo', 'bar'], $loader->getNames());
    }
}
