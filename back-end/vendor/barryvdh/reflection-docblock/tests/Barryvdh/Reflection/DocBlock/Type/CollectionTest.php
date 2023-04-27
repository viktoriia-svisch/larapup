<?php
namespace Barryvdh\Reflection\DocBlock\Type;
use Barryvdh\Reflection\DocBlock\Context;
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $collection = new Collection();
        $this->assertCount(0, $collection);
        $this->assertEquals('', $collection->getContext()->getNamespace());
        $this->assertCount(0, $collection->getContext()->getNamespaceAliases());
    }
    public function testConstructWithTypes()
    {
        $collection = new Collection(array('integer', 'string'));
        $this->assertCount(2, $collection);
    }
    public function testConstructWithNamespace()
    {
        $collection = new Collection(array(), new Context('\My\Space'));
        $this->assertEquals('My\Space', $collection->getContext()->getNamespace());
        $collection = new Collection(array(), new Context('My\Space'));
        $this->assertEquals('My\Space', $collection->getContext()->getNamespace());
        $collection = new Collection(array(), null);
        $this->assertEquals('', $collection->getContext()->getNamespace());
    }
    public function testConstructWithNamespaceAliases()
    {
        $fixture = array('a' => 'b');
        $collection = new Collection(array(), new Context(null, $fixture));
        $this->assertEquals(
            array('a' => '\b'),
            $collection->getContext()->getNamespaceAliases()
        );
    }
    public function testAdd($fixture, $expected)
    {
        $collection = new Collection(
            array(),
            new Context('\My\Space', array('Alias' => '\My\Space\Aliasing'))
        );
        $collection->add($fixture);
        $this->assertSame($expected, $collection->getArrayCopy());
    }
    public function testAddWithoutNamespace($fixture, $expected)
    {
        $collection = new Collection(
            array(),
            new Context(null, array('Alias' => '\My\Space\Aliasing'))
        );
        $collection->add($fixture);
        $this->assertSame($expected, $collection->getArrayCopy());
    }
    public function testAddMethodsAndProperties($fixture, $expected)
    {
        $collection = new Collection(
            array(),
            new Context(null, array('LinkDescriptor' => '\phpDocumentor\LinkDescriptor'))
        );
        $collection->add($fixture);
        $this->assertSame($expected, $collection->getArrayCopy());
    }
    public function testAddWithInvalidArgument()
    {
        $collection = new Collection();
        $collection->add(array());
    }
    public function provideTypesToExpand($method, $namespace = '\My\Space\\')
    {
        return array(
            array('', array()),
            array(' ', array()),
            array('int', array('int')),
            array('int ', array('int')),
            array('string', array('string')),
            array('DocBlock', array($namespace.'DocBlock')),
            array('DocBlock[]', array($namespace.'DocBlock[]')),
            array(' DocBlock ', array($namespace.'DocBlock')),
            array('\My\Space\DocBlock', array('\My\Space\DocBlock')),
            array('Alias\DocBlock', array('\My\Space\Aliasing\DocBlock')),
            array(
                'DocBlock|Tag',
                array($namespace .'DocBlock', $namespace .'Tag')
            ),
            array(
                'DocBlock|null',
                array($namespace.'DocBlock', 'null')
            ),
            array(
                '\My\Space\DocBlock|Tag',
                array('\My\Space\DocBlock', $namespace.'Tag')
            ),
            array(
                'DocBlock[]|null',
                array($namespace.'DocBlock[]', 'null')
            ),
            array(
                'DocBlock[]|int[]',
                array($namespace.'DocBlock[]', 'int[]')
            ),
            array(
                'LinkDescriptor::setLink()',
                array($namespace.'LinkDescriptor::setLink()')
            ),
            array(
                'Alias\LinkDescriptor::setLink()',
                array('\My\Space\Aliasing\LinkDescriptor::setLink()')
            ),
        );
    }
    public function provideTypesToExpandWithoutNamespace($method)
    {
        return $this->provideTypesToExpand($method, '\\');
    }
    public function provideTypesToExpandWithPropertyOrMethod($method)
    {
        return array(
            array(
                'LinkDescriptor::setLink()',
                array('\phpDocumentor\LinkDescriptor::setLink()')
            ),
            array(
                'phpDocumentor\LinkDescriptor::setLink()',
                array('\phpDocumentor\LinkDescriptor::setLink()')
            ),
            array(
                'LinkDescriptor::$link',
                array('\phpDocumentor\LinkDescriptor::$link')
            ),
            array(
                'phpDocumentor\LinkDescriptor::$link',
                array('\phpDocumentor\LinkDescriptor::$link')
            ),
        );
    }
}
