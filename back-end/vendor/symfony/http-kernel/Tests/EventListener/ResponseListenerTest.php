<?php
namespace Symfony\Component\HttpKernel\Tests\EventListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
class ResponseListenerTest extends TestCase
{
    private $dispatcher;
    private $kernel;
    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $listener = new ResponseListener('UTF-8');
        $this->dispatcher->addListener(KernelEvents::RESPONSE, [$listener, 'onKernelResponse']);
        $this->kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
    }
    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->kernel = null;
    }
    public function testFilterDoesNothingForSubRequests()
    {
        $response = new Response('foo');
        $event = new FilterResponseEvent($this->kernel, new Request(), HttpKernelInterface::SUB_REQUEST, $response);
        $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);
        $this->assertEquals('', $event->getResponse()->headers->get('content-type'));
    }
    public function testFilterSetsNonDefaultCharsetIfNotOverridden()
    {
        $listener = new ResponseListener('ISO-8859-15');
        $this->dispatcher->addListener(KernelEvents::RESPONSE, [$listener, 'onKernelResponse'], 1);
        $response = new Response('foo');
        $event = new FilterResponseEvent($this->kernel, Request::create('/'), HttpKernelInterface::MASTER_REQUEST, $response);
        $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);
        $this->assertEquals('ISO-8859-15', $response->getCharset());
    }
    public function testFilterDoesNothingIfCharsetIsOverridden()
    {
        $listener = new ResponseListener('ISO-8859-15');
        $this->dispatcher->addListener(KernelEvents::RESPONSE, [$listener, 'onKernelResponse'], 1);
        $response = new Response('foo');
        $response->setCharset('ISO-8859-1');
        $event = new FilterResponseEvent($this->kernel, Request::create('/'), HttpKernelInterface::MASTER_REQUEST, $response);
        $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);
        $this->assertEquals('ISO-8859-1', $response->getCharset());
    }
    public function testFiltersSetsNonDefaultCharsetIfNotOverriddenOnNonTextContentType()
    {
        $listener = new ResponseListener('ISO-8859-15');
        $this->dispatcher->addListener(KernelEvents::RESPONSE, [$listener, 'onKernelResponse'], 1);
        $response = new Response('foo');
        $request = Request::create('/');
        $request->setRequestFormat('application/json');
        $event = new FilterResponseEvent($this->kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);
        $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);
        $this->assertEquals('ISO-8859-15', $response->getCharset());
    }
}