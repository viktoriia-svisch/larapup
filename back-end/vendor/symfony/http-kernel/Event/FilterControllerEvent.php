<?php
namespace Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
class FilterControllerEvent extends KernelEvent
{
    private $controller;
    public function __construct(HttpKernelInterface $kernel, callable $controller, Request $request, ?int $requestType)
    {
        parent::__construct($kernel, $request, $requestType);
        $this->setController($controller);
    }
    public function getController()
    {
        return $this->controller;
    }
    public function setController(callable $controller)
    {
        $this->controller = $controller;
    }
}
