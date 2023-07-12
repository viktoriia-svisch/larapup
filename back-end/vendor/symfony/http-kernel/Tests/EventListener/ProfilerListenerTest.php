<?php
namespace Symfony\Component\HttpKernel\Tests\EventListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\EventListener\ProfilerListener;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Profiler\Profile;
class ProfilerListenerTest extends TestCase
{
    public function testKernelTerminate()
    {
        $profile = new Profile('token');
        $profiler = $this->getMockBuilder('Symfony\Component\HttpKernel\Profiler\Profiler')
            ->disableOriginalConstructor()
            ->getMock();
        $profiler->expects($this->once())
            ->method('collect')
            ->will($this->returnValue($profile));
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
        $masterRequest = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $subRequest = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $response = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $requestStack = new RequestStack();
        $requestStack->push($masterRequest);
        $onlyException = true;
        $listener = new ProfilerListener($profiler, $requestStack, null, $onlyException);
        $listener->onKernelResponse(new FilterResponseEvent($kernel, $masterRequest, Kernel::MASTER_REQUEST, $response));
        $listener->onKernelException(new GetResponseForExceptionEvent($kernel, $subRequest, Kernel::SUB_REQUEST, new HttpException(404)));
        $listener->onKernelResponse(new FilterResponseEvent($kernel, $subRequest, Kernel::SUB_REQUEST, $response));
        $listener->onKernelTerminate(new PostResponseEvent($kernel, $masterRequest, $response));
    }
}
