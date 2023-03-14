<?php
namespace Symfony\Component\HttpKernel\Event;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
class KernelEvent extends Event
{
    private $kernel;
    private $request;
    private $requestType;
    public function __construct(HttpKernelInterface $kernel, Request $request, ?int $requestType)
    {
        $this->kernel = $kernel;
        $this->request = $request;
        $this->requestType = $requestType;
    }
    public function getKernel()
    {
        return $this->kernel;
    }
    public function getRequest()
    {
        return $this->request;
    }
    public function getRequestType()
    {
        return $this->requestType;
    }
    public function isMasterRequest()
    {
        return HttpKernelInterface::MASTER_REQUEST === $this->requestType;
    }
}
