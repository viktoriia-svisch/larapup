<?php
namespace Symfony\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Stopwatch\Stopwatch;
class TraceableControllerResolver implements ControllerResolverInterface
{
    private $resolver;
    private $stopwatch;
    public function __construct(ControllerResolverInterface $resolver, Stopwatch $stopwatch)
    {
        $this->resolver = $resolver;
        $this->stopwatch = $stopwatch;
    }
    public function getController(Request $request)
    {
        $e = $this->stopwatch->start('controller.get_callable');
        $ret = $this->resolver->getController($request);
        $e->stop();
        return $ret;
    }
}
