<?php
namespace Symfony\Component\EventDispatcher\Tests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;
class EventTest extends TestCase
{
    protected $event;
    protected function setUp()
    {
        $this->event = new Event();
    }
    protected function tearDown()
    {
        $this->event = null;
    }
    public function testIsPropagationStopped()
    {
        $this->assertFalse($this->event->isPropagationStopped());
    }
    public function testStopPropagationAndIsPropagationStopped()
    {
        $this->event->stopPropagation();
        $this->assertTrue($this->event->isPropagationStopped());
    }
}
