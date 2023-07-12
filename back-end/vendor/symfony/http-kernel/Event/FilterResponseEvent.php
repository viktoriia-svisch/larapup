<?php
namespace Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
class FilterResponseEvent extends KernelEvent
{
    private $response;
    public function __construct(HttpKernelInterface $kernel, Request $request, int $requestType, Response $response)
    {
        parent::__construct($kernel, $request, $requestType);
        $this->setResponse($response);
    }
    public function getResponse()
    {
        return $this->response;
    }
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}
