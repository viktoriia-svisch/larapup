<?php
namespace Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
class GetResponseForControllerResultEvent extends GetResponseEvent
{
    private $controllerResult;
    public function __construct(HttpKernelInterface $kernel, Request $request, int $requestType, $controllerResult)
    {
        parent::__construct($kernel, $request, $requestType);
        $this->controllerResult = $controllerResult;
    }
    public function getControllerResult()
    {
        return $this->controllerResult;
    }
    public function setControllerResult($controllerResult)
    {
        $this->controllerResult = $controllerResult;
    }
}
