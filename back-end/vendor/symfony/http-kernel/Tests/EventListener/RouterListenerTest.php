<?php
namespace Symfony\Component\HttpKernel\Tests\EventListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\EventListener\ValidateRequestListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\RequestContext;
class RouterListenerTest extends TestCase
{
    private $requestStack;
    protected function setUp()
    {
        $this->requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')->disableOriginalConstructor()->getMock();
    }
    public function testPort($defaultHttpPort, $defaultHttpsPort, $uri, $expectedHttpPort, $expectedHttpsPort)
    {
        $urlMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\UrlMatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $context = new RequestContext();
        $context->setHttpPort($defaultHttpPort);
        $context->setHttpsPort($defaultHttpsPort);
        $urlMatcher->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context));
        $listener = new RouterListener($urlMatcher, $this->requestStack);
        $event = $this->createGetResponseEventForUri($uri);
        $listener->onKernelRequest($event);
        $this->assertEquals($expectedHttpPort, $context->getHttpPort());
        $this->assertEquals($expectedHttpsPort, $context->getHttpsPort());
        $this->assertEquals(0 === strpos($uri, 'https') ? 'https' : 'http', $context->getScheme());
    }
    public function getPortData()
    {
        return [
            [80, 443, 'http:
            [80, 443, 'http:
            [80, 443, 'https:
            [80, 443, 'https:
        ];
    }
    private function createGetResponseEventForUri(string $uri): GetResponseEvent
    {
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
        $request = Request::create($uri);
        $request->attributes->set('_controller', null); 
        return new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
    }
    public function testInvalidMatcher()
    {
        new RouterListener(new \stdClass(), $this->requestStack);
    }
    public function testRequestMatcher()
    {
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
        $request = Request::create('http:
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $requestMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\RequestMatcherInterface')->getMock();
        $requestMatcher->expects($this->once())
                       ->method('matchRequest')
                       ->with($this->isInstanceOf('Symfony\Component\HttpFoundation\Request'))
                       ->will($this->returnValue([]));
        $listener = new RouterListener($requestMatcher, $this->requestStack, new RequestContext());
        $listener->onKernelRequest($event);
    }
    public function testSubRequestWithDifferentMethod()
    {
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
        $request = Request::create('http:
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $requestMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\RequestMatcherInterface')->getMock();
        $requestMatcher->expects($this->any())
                       ->method('matchRequest')
                       ->with($this->isInstanceOf('Symfony\Component\HttpFoundation\Request'))
                       ->will($this->returnValue([]));
        $context = new RequestContext();
        $listener = new RouterListener($requestMatcher, $this->requestStack, new RequestContext());
        $listener->onKernelRequest($event);
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
        $request = Request::create('http:
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);
        $listener->onKernelRequest($event);
        $this->assertEquals('GET', $context->getMethod());
    }
    public function testLoggingParameter($parameter, $log, $parameters)
    {
        $requestMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\RequestMatcherInterface')->getMock();
        $requestMatcher->expects($this->once())
            ->method('matchRequest')
            ->will($this->returnValue($parameter));
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $logger->expects($this->once())
            ->method('info')
            ->with($this->equalTo($log), $this->equalTo($parameters));
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
        $request = Request::create('http:
        $listener = new RouterListener($requestMatcher, $this->requestStack, new RequestContext(), $logger);
        $listener->onKernelRequest(new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST));
    }
    public function getLoggingParameterData()
    {
        return [
            [['_route' => 'foo'], 'Matched route "{route}".', ['route' => 'foo', 'route_parameters' => ['_route' => 'foo'], 'request_uri' => 'http:
            [[], 'Matched route "{route}".', ['route' => 'n/a', 'route_parameters' => [], 'request_uri' => 'http:
        ];
    }
    public function testWithBadRequest()
    {
        $requestStack = new RequestStack();
        $requestMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\RequestMatcherInterface')->getMock();
        $requestMatcher->expects($this->never())->method('matchRequest');
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new ValidateRequestListener());
        $dispatcher->addSubscriber(new RouterListener($requestMatcher, $requestStack, new RequestContext()));
        $dispatcher->addSubscriber(new ExceptionListener(function () {
            return new Response('Exception handled', 400);
        }));
        $kernel = new HttpKernel($dispatcher, new ControllerResolver(), $requestStack, new ArgumentResolver());
        $request = Request::create('http:
        $request->headers->set('host', '###');
        $response = $kernel->handle($request);
        $this->assertSame(400, $response->getStatusCode());
    }
    public function testNoRoutingConfigurationResponse()
    {
        $requestStack = new RequestStack();
        $requestMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\RequestMatcherInterface')->getMock();
        $requestMatcher
            ->expects($this->once())
            ->method('matchRequest')
            ->willThrowException(new NoConfigurationException())
        ;
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new RouterListener($requestMatcher, $requestStack, new RequestContext()));
        $kernel = new HttpKernel($dispatcher, new ControllerResolver(), $requestStack, new ArgumentResolver());
        $request = Request::create('http:
        $response = $kernel->handle($request);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertContains('Welcome', $response->getContent());
    }
    public function testRequestWithBadHost()
    {
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
        $request = Request::create('http:
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $requestMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\RequestMatcherInterface')->getMock();
        $listener = new RouterListener($requestMatcher, $this->requestStack, new RequestContext());
        $listener->onKernelRequest($event);
    }
}
