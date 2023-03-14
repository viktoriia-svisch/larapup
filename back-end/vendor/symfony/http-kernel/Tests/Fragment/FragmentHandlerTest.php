<?php
namespace Symfony\Component\HttpKernel\Tests\Fragment;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
class FragmentHandlerTest extends TestCase
{
    private $requestStack;
    protected function setUp()
    {
        $this->requestStack = $this->getMockBuilder('Symfony\\Component\\HttpFoundation\\RequestStack')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->requestStack
            ->expects($this->any())
            ->method('getCurrentRequest')
            ->will($this->returnValue(Request::create('/')))
        ;
    }
    public function testRenderWhenRendererDoesNotExist()
    {
        $handler = new FragmentHandler($this->requestStack);
        $handler->render('/', 'foo');
    }
    public function testRenderWithUnknownRenderer()
    {
        $handler = $this->getHandler($this->returnValue(new Response('foo')));
        $handler->render('/', 'bar');
    }
    public function testDeliverWithUnsuccessfulResponse()
    {
        $handler = $this->getHandler($this->returnValue(new Response('foo', 404)));
        $handler->render('/', 'foo');
    }
    public function testRender()
    {
        $handler = $this->getHandler($this->returnValue(new Response('foo')), ['/', Request::create('/'), ['foo' => 'foo', 'ignore_errors' => true]]);
        $this->assertEquals('foo', $handler->render('/', 'foo', ['foo' => 'foo']));
    }
    protected function getHandler($returnValue, $arguments = [])
    {
        $renderer = $this->getMockBuilder('Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface')->getMock();
        $renderer
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'))
        ;
        $e = $renderer
            ->expects($this->any())
            ->method('render')
            ->will($returnValue)
        ;
        if ($arguments) {
            $e->with(...$arguments);
        }
        $handler = new FragmentHandler($this->requestStack);
        $handler->addRenderer($renderer);
        return $handler;
    }
}
