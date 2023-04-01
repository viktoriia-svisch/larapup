<?php
namespace Symfony\Component\HttpKernel\Tests\DataCollector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\TimeDataCollector;
class TimeDataCollectorTest extends TestCase
{
    public function testCollect()
    {
        $c = new TimeDataCollector();
        $request = new Request();
        $request->server->set('REQUEST_TIME', 1);
        $c->collect($request, new Response());
        $this->assertEquals(0, $c->getStartTime());
        $request->server->set('REQUEST_TIME_FLOAT', 2);
        $c->collect($request, new Response());
        $this->assertEquals(2000, $c->getStartTime());
        $request = new Request();
        $c->collect($request, new Response());
        $this->assertEquals(0, $c->getStartTime());
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();
        $kernel->expects($this->once())->method('getStartTime')->will($this->returnValue(123456));
        $c = new TimeDataCollector($kernel);
        $request = new Request();
        $request->server->set('REQUEST_TIME', 1);
        $c->collect($request, new Response());
        $this->assertEquals(123456000, $c->getStartTime());
    }
}
