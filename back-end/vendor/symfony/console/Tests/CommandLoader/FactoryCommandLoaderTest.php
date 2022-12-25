<?php
namespace Symfony\Component\Console\Tests\CommandLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;
class FactoryCommandLoaderTest extends TestCase
{
    public function testHas()
    {
        $loader = new FactoryCommandLoader([
            'foo' => function () { return new Command('foo'); },
            'bar' => function () { return new Command('bar'); },
        ]);
        $this->assertTrue($loader->has('foo'));
        $this->assertTrue($loader->has('bar'));
        $this->assertFalse($loader->has('baz'));
    }
    public function testGet()
    {
        $loader = new FactoryCommandLoader([
            'foo' => function () { return new Command('foo'); },
            'bar' => function () { return new Command('bar'); },
        ]);
        $this->assertInstanceOf(Command::class, $loader->get('foo'));
        $this->assertInstanceOf(Command::class, $loader->get('bar'));
    }
    public function testGetUnknownCommandThrows()
    {
        (new FactoryCommandLoader([]))->get('unknown');
    }
    public function testGetCommandNames()
    {
        $loader = new FactoryCommandLoader([
            'foo' => function () { return new Command('foo'); },
            'bar' => function () { return new Command('bar'); },
        ]);
        $this->assertSame(['foo', 'bar'], $loader->getNames());
    }
}
