<?php
namespace Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
class PostResponseEvent extends KernelEvent
{
    private $response;
    public function __construct(HttpKernelInterface $kernel, Request $request, Response $response)
    {
        parent::__construct($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $this->response = $response;
    }
    public function getResponse()
    {
        return $this->response;
    }
}
