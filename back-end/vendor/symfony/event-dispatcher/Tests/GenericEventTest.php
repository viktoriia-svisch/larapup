<?php
namespace Symfony\Component\EventDispatcher\Tests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;
class GenericEventTest extends TestCase
{
    private $event;
    private $subject;
    protected function setUp()
    {
        $this->subject = new \stdClass();
        $this->event = new GenericEvent($this->subject, ['name' => 'Event']);
    }
    protected function tearDown()
    {
        $this->subject = null;
        $this->event = null;
    }
    public function testConstruct()
    {
        $this->assertEquals($this->event, new GenericEvent($this->subject, ['name' => 'Event']));
    }
    public function testGetArguments()
    {
        $this->assertSame(['name' => 'Event'], $this->event->getArguments());
    }
    public function testSetArguments()
    {
        $result = $this->event->setArguments(['foo' => 'bar']);
        $this->assertAttributeSame(['foo' => 'bar'], 'arguments', $this->event);
        $this->assertSame($this->event, $result);
    }
    public function testSetArgument()
    {
        $result = $this->event->setArgument('foo2', 'bar2');
        $this->assertAttributeSame(['name' => 'Event', 'foo2' => 'bar2'], 'arguments', $this->event);
        $this->assertEquals($this->event, $result);
    }
    public function testGetArgument()
    {
        $this->assertEquals('Event', $this->event->getArgument('name'));
    }
    public function testGetArgException()
    {
        $this->event->getArgument('nameNotExist');
    }
    public function testOffsetGet()
    {
        $this->assertEquals('Event', $this->event['name']);
        $this->{method_exists($this, $_ = 'expectException') ? $_ : 'setExpectedException'}('InvalidArgumentException');
        $this->assertFalse($this->event['nameNotExist']);
    }
    public function testOffsetSet()
    {
        $this->event['foo2'] = 'bar2';
        $this->assertAttributeSame(['name' => 'Event', 'foo2' => 'bar2'], 'arguments', $this->event);
    }
    public function testOffsetUnset()
    {
        unset($this->event['name']);
        $this->assertAttributeSame([], 'arguments', $this->event);
    }
    public function testOffsetIsset()
    {
        $this->assertArrayHasKey('name', $this->event);
        $this->assertArrayNotHasKey('nameNotExist', $this->event);
    }
    public function testHasArgument()
    {
        $this->assertTrue($this->event->hasArgument('name'));
        $this->assertFalse($this->event->hasArgument('nameNotExist'));
    }
    public function testGetSubject()
    {
        $this->assertSame($this->subject, $this->event->getSubject());
    }
    public function testHasIterator()
    {
        $data = [];
        foreach ($this->event as $key => $value) {
            $data[$key] = $value;
        }
        $this->assertEquals(['name' => 'Event'], $data);
    }
}
